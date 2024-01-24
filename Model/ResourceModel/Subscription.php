<?php

namespace O2TI\SubscriptionPayment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Subscription extends AbstractDb
{
    /**
     * Construct.
     */
    protected function _construct()
    {
        $this->_init('o2ti_payment_subscription', 'subscription_id');
    }
}
