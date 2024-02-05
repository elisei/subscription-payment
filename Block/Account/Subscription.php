<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Block\Account;

use Magento\Framework\View\Element\Template\Context;
use O2TI\SubscriptionPayment\Helper\Data as SubscriptionHelper;
use O2TI\SubscriptionPayment\Model\RecurringCycleConfigProvider as RecurringConfig;

class Subscription extends \Magento\Framework\View\Element\Template
{
    /**
     * @var RecurringConfig
     */
    protected $recurringConfig;

    /**
     * @var \O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\Collection
     */
    protected $subscriptions;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 101.0.0
     */
    protected $httpContext;

    /**
     * @var SubscriptionHelper
     */
    protected $subscriptionHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param Context                                           $httpContext
     * @param RecurringConfig                                   $recurringConfig
     * @param SubscriptionHelper                                $subscriptionHelper
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        RecurringConfig $recurringConfig,
        SubscriptionHelper $subscriptionHelper,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->recurringConfig = $recurringConfig;
        $this->subscriptionHelper = $subscriptionHelper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get Details Url
     *
     * @param int $subscriptionId
     * @return string
     */
    public function getDetailsUrl($subscriptionId)
    {
        return $this->getUrl('*/subscription/view', ['subscription_id' => $subscriptionId]);
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
        return $this->subscriptions;
    }

    /**
     * Get subscription helper
     *
     * @return SubscriptionHelper
     */
    public function getSubscriptionHelper()
    {
        return $this->subscriptionHelper;
    }
}
