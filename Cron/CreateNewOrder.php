<?php
namespace O2TI\SubscriptionPayment\Cron;

use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\CollectionFactory;
use O2TI\SubscriptionPayment\Model\PlaceNewOrder;

class CreateNewOrder
{
    /**
     * @var CollectionFactory
     */
    protected $subscriptionCollection;

    /**
     * @var PlaceNewOrder
     */
    protected $newOrder;

    /**
     * Construct.
     *
     * @param CollectionFactory $subscriptionCollection
     * @param PlaceNewOrder $newOrder
     */
    public function __construct(
        CollectionFactory $subscriptionCollection,
        PlaceNewOrder $newOrder
    ) {
        $this->subscriptionCollection = $subscriptionCollection;
        $this->newOrder = $newOrder;
    }

    /**
     * Find all orders in next cycle.
     */
    public function execute()
    {
        $currentTime = date('Y-m-d H:i:s');
        $nextHourTime = date('Y-m-d H:i:s', strtotime('+2 hour', strtotime($currentTime)));

        $subscriptionCollection = $this->subscriptionCollection->create();
        $subscriptionCollection->addFieldToFilter('next_cycle', ['gteq' => $currentTime])
                            ->addFieldToFilter('next_cycle', ['lteq' => $nextHourTime]);

        $orderIds = $subscriptionCollection->getColumnValues('order_id');

        // foreach ($orderIds as $orderId) {
        //     $this->newOrder->createOrder($orderId);
        // }
    }
}
