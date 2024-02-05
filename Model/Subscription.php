<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Model;

use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription as SubscriptionResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

class Subscription extends AbstractModel
{
    /**
     * Construct.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(SubscriptionResourceModel::class);
    }

    /**
     * Get subscription by ID.
     *
     * @param int $subscriptionId
     * @return \O2TI\SubscriptionPayment\Model\Subscription
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubscriptionById($subscriptionId)
    {
        $subscription = $this->load($subscriptionId);
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with ID %1" does not exist.', $subscriptionId));
        }
        return $subscription;
    }
}
