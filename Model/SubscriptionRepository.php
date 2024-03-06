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

use O2TI\SubscriptionPayment\Api\SubscriptionRepositoryInterface;
use O2TI\SubscriptionPayment\Api\Data\SubscriptionInterface;
use O2TI\SubscriptionPayment\Model\ResourceModel\Subscription\CollectionFactory;
use O2TI\SubscriptionPayment\Model\SubscriptionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * Construct.
     *
     * @param CollectionFactory $collectionFactory
     * @param SubscriptionFactory $subscriptionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SubscriptionFactory $subscriptionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    /**
     * Get List.
     */
    public function getList()
    {
        return $this->collectionFactory->create()->getItems();
    }

    /**
     * Get By Id.
     *
     * @param int $id
     *
     * @return SubscriptionFactory
     */
    public function getById($id)
    {
        $subscription = $this->subscriptionFactory->create()->load($id);
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('A assinatura não existe.'));
        }
        return $subscription;
    }

    /**
     * Delete.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $subscription = $this->getById($id);
        $subscription->delete();
        return true;
    }

    /**
     * Save.
     *
     * @param SubscriptionInterface $subscription
     *
     * @return $subscription
     */
    public function save(SubscriptionInterface $subscription)
    {
        $subscription->save();
        return $subscription;
    }
}
