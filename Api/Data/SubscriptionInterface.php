<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Api\Data;

interface SubscriptionInterface
{
    /**
     * Get id.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Id.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get State - 1 or 0
     *
     * @return bool
     */
    public function getState();

    /**
     * Set State.
     *
     * @param bool $state
     * @return $this
     */
    public function setState($state);

    /**
     * Get Order Id.
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set Order Id.
     *
     * @param string $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get Customer Id.
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set Customer Id.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get Additional Data.
     *
     * @return string|null
     */
    public function getAdditionalData();

    /**
     * Set Additional Data.
     *
     * @param string $additionalData
     * @return $this
     */
    public function setAdditionalData($additionalData);

    /**
     * Get Cycle.
     *
     * @return string|null
     */
    public function getCycle();

    /**
     * Set Cycle.
     *
     * @param string $cycle
     * @return $this
     */
    public function setCycle($cycle);

    /**
     * Get Payment Method.
     *
     * @return string|null
     */
    public function getPaymentMethod();

    /**
     * Set Payment Method.
     *
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get Has Error.
     *
     * @return bool
     */
    public function getHasError();

    /**
     * Set Has Error.
     *
     * @param bool $hasError
     * @return $this
     */
    public function setHasError($hasError);

    /**
     * Get Creation Time.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Creation Time.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Next Cycle Date.
     *
     * @return string|null
     */
    public function getNextCycle();

    /**
     * Set Next Cycle Date.
     *
     * @param string $nextCycle
     * @return $this
     */
    public function setNextCycle($nextCycle);
}
