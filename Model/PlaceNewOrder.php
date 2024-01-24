<?php
namespace O2TI\SubscriptionPayment\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Vault\Model\VaultPaymentInterface;
use O2TI\SubscriptionPayment\Model\AbstractModel;

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
     * @var PaymentTokenCollectionFactory
     */
    protected $paymentTokenCollectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var String
     */
    protected $paymentMethod;

    /**
     * Construct.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param OrderFactory $orderFactory
     * @param PaymentTokenCollectionFactory $paymentTokenCollectionFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        PaymentTokenCollectionFactory $paymentTokenCollectionFactory,
        CustomerFactory $customerFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->paymentMethod = 'pagbank_paymentmagento_cc_vault';
    }

    /**
     * Create Order.
     *
     * @param int $orderId
     *
     * @return void
     */
    public function createOrder($orderId)
    {
        $paymentToken = null;
        $existingQuote = $this->cartRepository->get($orderId);

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

            $existingQuote->getPayment()->unsAdditionalInformation();

            $existingQuote->getPayment()->save();

            $dataToPay = [
                'public_hash' => $paymentToken,
                'customer_id' => $customerId,
                'payer_name' => 'Bruno Elisei',
                'payer_tax_id' => '34057603808',
                'payer_phone' => '34984427885',
                'recurring_type' => 'SUBSEQUENT'
            ];

            $existingQuote->getPayment()->setAdditionalInformation($dataToPay);

            $existingQuote->getPayment()->save();

            $newOrder = $this->quoteManagement->submit($existingQuote);

            $newOrder = $this->orderFactory->create()->load($newOrder->getId());

            $newOrder->save();

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
        $paymentTokens = $this->paymentTokenCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('payment_method_code', 'pagbank_paymentmagento_cc')
            ->setOrder('created_at', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);

        if ($paymentTokens->getSize() > 0) {
            return $paymentTokens->getLastItem();
        }

        return null;
    }
}
