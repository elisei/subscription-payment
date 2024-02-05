<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Controller\Subscription;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use O2TI\SubscriptionPayment\Model\Subscription;

class View extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Subscription
     */
    protected $subscription;

    /**
     * @var int
     */
    protected $customerId;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Construct.
     *
     * @param Context       $context
     * @param Registry      $registry
     * @param PageFactory   $resultPageFactory
     * @param Session       $customerSession
     * @param Subscription  $subscription
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PageFactory $resultPageFactory,
        Session $customerSession,
        Subscription $subscription
    ) {
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->subscription = $subscription;
        $this->customerId = $this->customerSession->getCustomerId();
        parent::__construct($context);
    }

    /**
     * Create page
     *
     * @return PageFactory
     */
    public function execute()
    {
        
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }

        $subscriptionId = $this->getRequest()->getParam('subscription_id');

        if (!$subscriptionId) {
            $this->_redirect('o2ti/account/subscription');
            return;
        }

        $resultPage = $this->resultPageFactory->create();

        $block = $resultPage->getLayout()->getBlock('o2ti_subscription_view');

        $subscription = $this->getSubscription($subscriptionId);
      
        if (!$subscription) {
            $this->_redirect('o2ti/account/subscription');
            return;
        }
    
        if ($block) {
            $block->setSubscription($subscription);
        }

        return $resultPage;
    }

    /**
     * Get customer subscription
     *
     * @param int $subscriptionId
     *
     * @return \O2TI\SubscriptionPayment\Model\Subscription
     */
    public function getSubscription($subscriptionId)
    {
        $subscription = $this->subscription->getSubscriptionById($subscriptionId);

        if ($subscription->getCustomerId() === $this->customerId) {
            $this->registry->register('current_subscription', $subscription);
            return $subscription;
        }
        
        return null;
    }
}
