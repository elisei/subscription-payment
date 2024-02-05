<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Sales\Api\OrderRepositoryInterface;

class BeforeSaveVaultCardPlugin
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Save data for recurring.
     *
     * @param \Magento\Vault\Api\PaymentTokenRepositoryInterface $subject
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface      $paymentToken
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Vault\Api\PaymentTokenRepositoryInterface $subject,
        \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken
    ) {
       
        $orderId = null;
        if ($this->checkoutSession->getLastOrderId()) {
            $orderId = $this->checkoutSession->getLastOrderId();
        }

        if ($orderId) {
            $order = $this->orderRepository->get($orderId);

            $payment = $order->getPayment();
            if ($payment) {
                $addInformation = $payment->getAdditionalInformation();
                if (isset($addInformation['recurring_cycle'])) {
                    $paymentToken->setUseInSubscription(1);
                }
            }
        }

        return [$paymentToken];
    }
}
