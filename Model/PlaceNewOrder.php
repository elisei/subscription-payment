<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Vault\Model\VaultPaymentInterface;
use O2TI\SubscriptionPayment\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

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
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var String
     */
    protected $paymentMethod;

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
     * Construct.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param OrderFactory $orderFactory
     * @param OrderInterface $orderInterface
     * @param PaymentTokenCollectionFactory $vaultFactory
     * @param CustomerFactory $customerFactory
     * @param DateTime $dateTime
     * @param NotifierPool $notifier
     * @param SubscriptionFactory $subscriptionFactory
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
        CustomerFactory $customerFactory,
        DateTime $dateTime,
        NotifierPool $notifier,
        SubscriptionFactory $subscriptionFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->orderInterface = $orderInterface;
        $this->vaultFactory = $vaultFactory;
        $this->customerFactory = $customerFactory;
        $this->dateTime = $dateTime;
        $this->notifier = $notifier;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->paymentMethod = 'pagbank_paymentmagento_cc_vault';
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
        $quoteId = $order->getQuoteId();
        $paymentToken = null;
        $existingQuote = $this->cartRepository->get($quoteId);

        if ($existingQuote->getId()) {
            $customerId = $existingQuote->getCustomerId();

            if ($customerId) {
                $customer = $this->customerFactory->create()->load($customerId);
                $vaultPaymentMethod = $this->getCustomerVaultPaymentMethod($customer);

                if ($vaultPaymentMethod) {
                    $paymentToken = $vaultPaymentMethod->getPublicHash();
                }
            }

            $existingQuote->setCustomerId($customerId);

            $existingQuote->getPayment()->setMethod('pagbank_paymentmagento_cc_vault');

            $payerName = $existingQuote->getPayment()->getAdditionalInformation('payer_name');
            $payerTaxId = $existingQuote->getPayment()->getAdditionalInformation('payer_tax_id');
            $payerPhone = $existingQuote->getPayment()->getAdditionalInformation('payer_phone');

            $existingQuote->getPayment()->unsAdditionalInformation();

            $existingQuote->getPayment()->save();

            $dataToPay = [
                'public_hash' => $paymentToken,
                'customer_id' => $customerId,
                'payer_name' => $payerName,
                'payer_tax_id' => $payerTaxId,
                'payer_phone' => $payerPhone,
                'recurring_type' => 'SUBSEQUENT'
            ];

            $existingQuote->getPayment()->setAdditionalInformation($dataToPay);

            $existingQuote->getPayment()->save();

            try {
                $newOrder = $this->quoteManagement->submit($existingQuote);

                $newOrder = $this->orderFactory->create()->load($newOrder->getId());
                $newOrder->addStatusHistoryComment(
                    __('Order renewal carried out for initial order: %1.', $incrementId)
                );
    
                $newOrder->save();
            } catch (LocalizedException $exc) {
                $header = __('Error creating order %1', $incrementId);

                $description = __($exc->getMessage());

                $this->notifier->addCritical($header, $description);
                
                $this->updateSubscription($incrementId, 0);
                
                return 0;
            }
            
            $this->updateSubscription($incrementId, 1);

            $message = __(
                'New Order %1 criada para o pedido recorrente inicial',
                $newOrder->getIncrementId()
            );
            $this->writeln('<info>'.$message.'</info>');

            return 1;
        }

        return 0;
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
     * @param string $incrementId
     * @param bool   $status
     */
    private function updateSubscription($incrementId, $status)
    {
        $collection = $this->subscriptionFactory->create();

        $collection->addFieldToFilter('order_id', $incrementId);
        $subscription = $collection->getFirstItem();
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
