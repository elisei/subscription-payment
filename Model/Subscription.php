<?php
namespace O2TI\SubscriptionPayment\Model;

use Magento\Framework\Model\AbstractModel;

class Subscription extends AbstractModel
{
    /**
     * @var string
     */
    protected $_idFieldName = 'subscription_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'o2ti_payment_subscription_subscription';

    /**
     * Construct.
     */
    protected function _construct()
    {
        $this->_init('O2TI\SubscriptionPayment\Model\ResourceModel\Subscription');
    }
}
