<?php

namespace O2TI\SubscriptionPayment\Plugin;

use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\Serializer\Json;
use O2TI\SubscriptionPayment\Model\SubscriptionFactory;

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
     * Construct.
     *
     * @param SubscriptionFactory   $subscriptionFactory
     * @param Json                  $json
     */
    public function __construct(
        SubscriptionFactory $subscriptionFactory,
        Json $json
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->json = $json;
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
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        
        $cycle = $additionalInformation['recurring_cycle'];
        $currentDate = new \DateTime();
        $currentDate->add(new \DateInterval($cycle));
        $nextCycle = $currentDate->getTimestamp();

        $subscription = $this->subscriptionFactory->create();
        $subscription->setOrderId($orderId);
        $subscription->setCustomerId($customerId);
        $subscription->setAdditionalData($this->json->serialize($additionalInformation));
        $subscription->setState($currentState);
        $subscription->setCycle($cycle);
        $subscription->setNextCycle($nextCycle);
        $subscription->save();

        return $order;
    }
}
