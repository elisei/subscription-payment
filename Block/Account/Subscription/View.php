<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Block\Account\Subscription;

use Magento\Framework\App\Http\Context;
use O2TI\SubscriptionPayment\Model\Subscription;
use O2TI\SubscriptionPayment\Helper\Data as SubscriptionHelper;

/**
 * View - Details for subscription
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     *
     */
    protected $_template = 'O2TI_SubscriptionPayment::view.phtml';

    /**
     * @var Subscription
     */
    protected $subscription = null;

    /**
     * @var SubscriptionHelper
     */
    protected $subscriptionHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param Context                                           $httpContext
     * @param SubscriptionHelper                                $subscriptionHelper
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        SubscriptionHelper $subscriptionHelper,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->subscriptionHelper = $subscriptionHelper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Set title.
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Edit your subscription'));
    }

    /**
     * Set Subscription to the block
     *
     * @param \O2TI\SubscriptionPayment\Model\Subscription $subscription
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get customer subscription
     *
     * @return \O2TI\SubscriptionPayment\Model\Subscription
     */
    public function getSubscription()
    {
        return $this->subscription;
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
