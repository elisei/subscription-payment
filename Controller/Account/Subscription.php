<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\CollectionFactory;

class Subscription extends \Magento\Customer\Controller\AbstractAccount implements HttpGetActionInterface
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
     * @var CollectionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var int
     */
    protected $customerId;

    /**
     * Construct.
     *
     * @param Context           $context
     * @param PageFactory       $resultPageFactory
     * @param Session           $customerSession
     * @param CollectionFactory $subscriptionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        CollectionFactory $subscriptionFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->customerId = $this->customerSession->getCustomerId();
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

        $resultPage = $this->resultPageFactory->create();

        $block = $resultPage->getLayout()->getBlock('o2ti_account_subscription');

        if ($block) {
            $block->setSubscriptions($this->getSubscriptions());
        }

        return $resultPage;
    }

    /**
     * Get customer subscriptions
     *
     * @return \O2TI\SubscriptionPayment\Model\Subscription
     */
    private function getSubscriptions()
    {
        $collection = $this->subscriptionFactory->create();
        $collection->addFieldToFilter('customer_id', $this->customerId);

        return $collection;
    }
}
