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
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use O2TI\SubscriptionPayment\Model\Subscription;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Save edit.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class EditPost extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var Subscription
     */
    protected $subscription;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Construct.
     *
     * @param Context                                       $context
     * @param PageFactory                                   $resultPageFactory
     * @param FormKeyValidator                              $formKeyValidator
     * @param Subscription                                  $subscription
     * @param Session                                       $session
     * @param ManagerInterface                              $messageManager
     * @param DateTime                                      $dateTime
     * @param \Magento\Framework\Controller\ResultFactory   $resultFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        FormKeyValidator $formKeyValidator,
        Subscription $subscription,
        Session $session,
        ManagerInterface $messageManager,
        DateTime $dateTime,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->subscription = $subscription;
        $this->session = $session;
        $this->messageManager = $messageManager;
        $this->dateTime = $dateTime;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Execute save change.
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form key'));
            return $this->_redirect('*/*/');
        }

        $formData = $this->getRequest()->getPostValue();

        $customerId = $this->session->getCustomerId();
        $subscriptionId = $formData['subscription_id'];
        
        try {
            $subscription = $this->subscription->getSubscriptionById($subscriptionId);
            
            if ($subscription->getCustomerId() === $customerId) {
                $subscription->setState($formData['state']);
                $subscription->setCycle($formData['cycle']);
                
                if (isset($formData['use_now']) || (!$subscription->getState() && $formData['state'])) {
                    $currentDate = $this->dateTime->gmtTimestamp();
                    $nextCycle = strtotime($this->convertTime($formData['cycle']), $currentDate);
                    $subscription->setNextCycle($nextCycle);
                }
                
                $subscription->save();

                $this->messageManager->addSuccessMessage(__('Form data saved successfully'));
            }
            
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to save form data'));
            // Handle exception as needed
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
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
