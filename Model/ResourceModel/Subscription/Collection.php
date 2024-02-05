<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Model\ResourceModel\Subscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection for Subscription.
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'subscription_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'o2ti_payment_subscription_subscription_collection';

    /**
     * Construct.
     */
    protected function _construct()
    {
        $this->_init(
            'O2TI\SubscriptionPayment\Model\Subscription',
            'O2TI\SubscriptionPayment\Model\ResourceModel\Subscription'
        );
    }
}
