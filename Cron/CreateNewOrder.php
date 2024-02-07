<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Cron;

use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\CollectionFactory;
use O2TI\SubscriptionPayment\Model\PlaceNewOrder;

class CreateNewOrder
{
    /**
     * @var CollectionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var PlaceNewOrder
     */
    protected $newOrder;

    /**
     * Construct.
     *
     * @param CollectionFactory $subscriptionFactory
     * @param PlaceNewOrder $newOrder
     */
    public function __construct(
        CollectionFactory $subscriptionFactory,
        PlaceNewOrder $newOrder
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->newOrder = $newOrder;
    }

    /**
     * Find all orders in next cycle.
     */
    public function execute()
    {
        $currentTime = date('Y-m-d H:i:s');
        $nextHourTime = date('Y-m-d H:i:s', strtotime('+ 30 min', strtotime($currentTime)));

        $subscriptionFactory = $this->subscriptionFactory->create();
        $subscriptionFactory->addFieldToFilter('state', 1)
                            ->addFieldToFilter('next_cycle', ['gteq' => $currentTime])
                            ->addFieldToFilter('next_cycle', ['lteq' => $nextHourTime]);

        $orderIds = $subscriptionFactory->getColumnValues('order_id');

        foreach ($orderIds as $orderId) {
            $this->newOrder->createOrder($orderId);
        }
    }

    /**
     * Find all orders in error.
     */
    public function recovery()
    {
        $subscriptionFactory = $this->subscriptionFactory->create();
        $subscriptionFactory->addFieldToFilter('state', 1)
                            ->addFieldToFilter('has_error', 1);

        $orderIds = $subscriptionFactory->getColumnValues('order_id');

        foreach ($orderIds as $orderId) {
            $this->newOrder->createOrder($orderId);
        }
    }
}
