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

use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\Serializer\Json;
use O2TI\SubscriptionPayment\Model\SubscriptionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class OrderPlugin
{
    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Construct.
     *
     * @param SubscriptionFactory   $subscriptionFactory
     * @param Json                  $json
     * @param DateTime              $dateTime
     */
    public function __construct(
        SubscriptionFactory $subscriptionFactory,
        Json $json,
        DateTime $dateTime
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->json = $json;
        $this->dateTime = $dateTime;
    }

    /**
     * Add subscription data in order.
     *
     * @param Order $order
     * @return Order
     */
    public function afterPlace(\Magento\Sales\Model\Order $order)
    {
        $orderId = $order->getIncrementId();
        $customerId = $order->getCustomerId();
        $currentState = 1;
        $payment = $order->getPayment();
        $addInformation = $payment->getAdditionalInformation();
        
        if (isset($addInformation['recurring_cycle'])) {
            if ($addInformation['recurring_type'] === "INITIAL") {
                $cycle = $addInformation['recurring_cycle'];
                $currentDate = $this->dateTime->gmtTimestamp();
                $nextCycle = strtotime($this->convertTime($cycle), $currentDate);

                $subscription = $this->subscriptionFactory->create();
                $subscription->setOrderId($orderId);
                $subscription->setCustomerId($customerId);
                $subscription->setAdditionalData($this->json->serialize($addInformation));
                $subscription->setState($currentState);
                $subscription->setCycle($cycle);
                $subscription->setPaymentMethod($payment->getMethod());
                $subscription->setNextCycle($nextCycle);
                $subscription->save();
            }
        }

        return $order;
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
