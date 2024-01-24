<?php
namespace O2TI\SubscriptionPayment\Block\Account;

use Magento\Framework\View\Element\Template\Context;
use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\CollectionFactory;
use Magento\Customer\Model\Session;
use O2TI\SubscriptionPayment\Model\RecurringCycleConfigProvider as RecurringConfig;

class Subscription extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $subsCollectionFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $subscriptions;

    /**
     * @var RecurringConfig
     */
    protected $recurringConfig;

    /**
     * @param Context           $context
     * @param CollectionFactory $subsCollectionFactory
     * @param Session           $customerSession
     * @param RecurringConfig   $recurringConfig
     * @param array             $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $subsCollectionFactory,
        Session $customerSession,
        RecurringConfig $recurringConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->subsCollectionFactory = $subsCollectionFactory;
        $this->customerSession = $customerSession;
        $this->recurringConfig = $recurringConfig;
    }

    /**
     * Set subscriptions to the block
     *
     * @param \O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\Collection $subscriptions
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * Get customer subscriptions
     *
     * @return \O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\Collection
     */
    public function getSubscriptions()
    {
        if ($this->subscriptions === null) {
            $customerId = $this->customerSession->getCustomerId();

            $collection = $this->subsCollectionFactory->create();
            $collection->addFieldToFilter('customer_id', 4);

            $this->subscriptions = $collection;
        }

        return $this->subscriptions;
    }

    /**
     * Get Cycle Label
     *
     * @param string $cycle
     * @return string
     */
    public function getCycleLabel($cycle)
    {
        $configs = $this->recurringConfig->getConfig();
        $options = $configs['o2ti_payment_subscription_magento']['cycle_options'];
        
        foreach ($options as $option) {
            if ($option['value'] === $cycle) {
                return $option['label'];
            }
        }
        return __('Time');
    }

    /**
     * Get State Label
     *
     * @param string $state
     * @return string
     */
    public function getStateLabel($state)
    {
        if ($state) {
            return __('Enable');
        }
        return __('Disabled');
    }
}
