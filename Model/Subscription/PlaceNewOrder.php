<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Model\Subscription;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFactory;
use Magento\Vault\Model\VaultPaymentInterface;
use O2TI\SubscriptionPayment\Model\AbstractModel;
use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionFactory;
use O2TI\SubscriptionPayment\Model\Email\EmailNotification;

/**
 * Place new Order subscription
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceNewOrder extends AbstractModel
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var PaymentTokenCollectionFactory
     */
    protected $vaultFactory;

    /**
     * @var NotifierPool
     */
    protected $notifier;

    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var EmailNotification
     */
    protected $emailNotification;

    /**
     * Construct.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param OrderFactory $orderFactory
     * @param OrderInterface $orderInterface
     * @param PaymentTokenCollectionFactory $vaultFactory
     * @param DateTime $dateTime
     * @param NotifierPool $notifier
     * @param SubscriptionFactory $subscriptionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param EmailNotification $emailNotification
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        OrderInterface $orderInterface,
        PaymentTokenCollectionFactory $vaultFactory,
        DateTime $dateTime,
        NotifierPool $notifier,
        SubscriptionFactory $subscriptionFactory,
        CustomerRepositoryInterface $customerRepository,
        EmailNotification $emailNotification
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->orderInterface = $orderInterface;
        $this->vaultFactory = $vaultFactory;
        $this->dateTime = $dateTime;
        $this->notifier = $notifier;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->customerRepository = $customerRepository;
        $this->emailNotification = $emailNotification;
    }

    /**
     * Create Order.
     *
     * @param string $incrementId
     *
     * @return void
     */
    public function createOrder($incrementId)
    {
        $order = $this->orderInterface->loadByIncrementId($incrementId);
        $quote = $this->createNewQuoteBasedOnOriginalOrder($order);
        $subscription = $this->getSubscription($incrementId);

        if ($quote->getId()) {
            
            if ($subscription->getPaymentMethod() === 'pagbank_paymentmagento_cc') {
                $this->setQuoteForCc($quote);
            }

            if ($subscription->getPaymentMethod() === 'pagbank_paymentmagento_pix') {
                $this->setQuoteForPix($quote);
            }

            if ($subscription->getPaymentMethod() === 'pagbank_paymentmagento_boleto') {
                $this->setQuoteForBoleto($quote);
            }

            try {
                $newOrder = $this->quoteManagement->submit($quote);
                $newOrder = $this->orderFactory->create()->load($newOrder->getId());
                $newOrder->addStatusHistoryComment(
                    __('Order renewal carried out for initial order: %1.', $incrementId)
                );

                $this->emailNotification->sendCustomEmail($newOrder);

                $newOrder->save();

            } catch (LocalizedException $exc) {
                $header = __('Error creating order %1', $incrementId);

                $msg = $exc->getMessage();

                $description = __($msg);

                $this->notifier->addCritical($header, $description);
                
                $this->updateSubscription($subscription, 0);

                $this->writeln('<error>'.$msg.'</error>');
                
                return 0;
            }
            
            $this->updateSubscription($subscription, 1);

            $message = __(
                'New Order %1 criada para o pedido recorrente inicial',
                $newOrder->getIncrementId()
            );
            $this->writeln('<info>'.$message.'</info>');

            return 1;
        }
    }

    /**
     * Create new quote based on original order.
     *
     * @param OrderInterface $originalOrder
     * @return QuoteFactory
     */
    public function createNewQuoteBasedOnOriginalOrder($originalOrder)
    {
        $customerId = $originalOrder->getCustomerId();
        $customerEmail = $originalOrder->getCustomerEmail();
        $customer = $this->customerRepository->getById($customerId);

        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($originalOrder->getStoreId());
        $quote->setCustomer($customer);
        $quote->setCustomerId($customerId);
        $quote->setCustomerEmail($customerEmail);

        foreach ($originalOrder->getAllItems() as $item) {
            $product = $item->getProduct();
            $quote->addProduct(
                $product,
                $item->getQtyOrdered()
            );
        }

        $shippingAddress = $originalOrder->getShippingAddress()->getData();
        $quote->getShippingAddress()->setData($shippingAddress);

        $billingAddress = $originalOrder->getBillingAddress()->getData();
        $quote->getBillingAddress()->setData($billingAddress);

        $shippingMethod = $originalOrder->getShippingMethod();
        $quote->getShippingAddress()->setShippingMethod($shippingMethod);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
    
        $quote->collectTotals();

        $payment = $quote->getPayment();
        $payment->setMethod($originalOrder->getPayment()->getMethod());

        $discountAmount = $originalOrder->getDiscountAmount();

        $quote->setSubtotalWithDiscount($quote->getSubtotalWithDiscount() - $discountAmount);
        $quote->setBaseSubtotalWithDiscount($quote->getBaseSubtotalWithDiscount() - $discountAmount);

        $quote->setSubtotal($quote->getSubtotal() - $discountAmount);
        $quote->setBaseSubtotal($quote->getBaseSubtotal() - $discountAmount);
        $quote->setGrandTotal($quote->getGrandTotal() - $discountAmount);
        $quote->setBaseGrandTotal($quote->getBaseGrandTotal() - $discountAmount);

        $quote->setPaymentMethod($payment);
        $quote->collectTotals();
        $quote->save();
        return $quote;
    }

    /**
     * Set Quote For Cc.
     *
     * @param CartRepositoryInterface $quote
     */
    public function setQuoteForCc($quote)
    {
        $paymentToken = null;
        $customerId = $quote->getCustomerId();

        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $vaultPaymentMethod = $this->getCustomerVaultPaymentMethod($customer);

            if ($vaultPaymentMethod) {
                $paymentToken = $vaultPaymentMethod->getPublicHash();
            }
        }

        $quote->setCustomerId($customerId);

        $quote->getPayment()->setMethod('pagbank_paymentmagento_cc_vault');

        $dataToPay = [
            'public_hash' => $paymentToken,
            'customer_id' => $customerId,
            'recurring_type' => 'SUBSEQUENT'
        ];

        $quote->getPayment()->setAdditionalInformation($dataToPay);

        $quote->getPayment()->save();
    }

    /**
     * Set Quote For Pix.
     *
     * @param CartRepositoryInterface $quote
     */
    public function setQuoteForPix($quote)
    {
        $customerId = $quote->getCustomerId();

        $quote->setCustomerId($customerId);

        $quote->getPayment()->setMethod('pagbank_paymentmagento_pix');

        $dataToPay = [
            'customer_id' => $customerId,
            'recurring_type' => 'SUBSEQUENT'
        ];

        $quote->getPayment()->setAdditionalInformation($dataToPay);

        $quote->getPayment()->save();
    }

    /**
     * Set Quote For Boleto.
     *
     * @param CartRepositoryInterface $quote
     */
    public function setQuoteForBoleto($quote)
    {
        $customerId = $quote->getCustomerId();

        $quote->setCustomerId($customerId);

        $quote->getPayment()->setMethod('pagbank_paymentmagento_boleto');

        $dataToPay = [
            'customer_id' => $customerId,
            'recurring_type' => 'SUBSEQUENT'
        ];

        $quote->getPayment()->setAdditionalInformation($dataToPay);

        $quote->getPayment()->save();
    }

    /**
     * Get the customer's saved payment method from the Vault.
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface|null
     */
    private function getCustomerVaultPaymentMethod($customer)
    {
        $customerId = $customer->getId();
        $paymentTokens = $this->vaultFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('is_visible', 1)
            ->addFieldToFilter('use_in_subscription', 1)
            ->addFieldToFilter('payment_method_code', 'pagbank_paymentmagento_cc')
            ->setOrder('created_at', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        if ($paymentTokens->getSize() > 0) {
            return $paymentTokens->getLastItem();
        }

        return null;
    }

    /**
     * Update current subscription.
     *
     * @param \O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\Collection $subscription
     * @param bool                                                                  $status
     */
    private function updateSubscription($subscription, $status)
    {
        $cycle = $this->convertTime($subscription->getCycle());
        
        if (!$status) {
            $cycle = '+ 1 day';
        }

        $currentDate = $this->dateTime->gmtTimestamp();
        $nextCycle = strtotime($cycle, $currentDate);

        $subscription->setNextCycle($nextCycle);
        $subscription->setHasError($status);

        $subscription->save();
    }

    /**
     * Get Subscription.
     *
     * @param string $incrementId
     * @return \O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\Collection $subscription
     */
    private function getSubscription($incrementId)
    {
        $collection = $this->subscriptionFactory->create();

        $collection->addFieldToFilter('order_id', $incrementId);
        $subscription = $collection->getFirstItem();

        return $subscription;
    }

    /**
     * Convert Time.
     *
     * @param string $time
     * @return string
     */
    public function convertTime($time)
    {
        if ($time === 'PT1H') {
            return '+1 hour';
        } elseif ($time === 'P1D') {
            return '+1 day';
        } elseif ($time === 'P14D') {
            return '+14 days';
        } elseif ($time === 'P1M') {
            return '+1 month';
        } elseif ($time === 'P3M') {
            return '+3 month';
        }

        return '+1 day';
    }
}
