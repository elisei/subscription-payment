<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use O2TI\SubscriptionPayment\Model\RecurringCycleConfigProvider;

class Data extends AbstractHelper
{
    /**
     * @var RecurringCycleConfigProvider
     */
    protected $recurringConfig;

    /**
     * @param RecurringCycleConfigProvider $recurringConfig
     */
    public function __construct(
        RecurringCycleConfigProvider $recurringConfig
    ) {
        $this->recurringConfig = $recurringConfig;
    }

    /**
     * Get Cycle Label
     *
     * @param string $cycle
     * @return \Magento\Framework\Phrase
     */
    public function getCycleLabel($cycle)
    {
        $configs = $this->recurringConfig->getConfig();
        $options = $configs['o2ti_payment_subscription_magento']['cycle_options'];
        
        foreach ($options as $option) {
            if ($option['value'] === $cycle) {
                return $option['label'];
            }
        }
        return __('Time');
    }

    /**
     * Get Dropmenu Cycle
     *
     * @return array
     */
    public function getOptionsForCycle()
    {
        $configs = $this->recurringConfig->getConfig();
        $options = $configs['o2ti_payment_subscription_magento']['cycle_options'];

        return $options;
    }

    /**
     * Get Dropmenu State
     *
     * @return array
     */
    public function getOptionsForState()
    {
        return [
            [
                'value' => 1,
                'label' => __('Enable')
            ],
            [
                'value' => 0,
                'label' => __('Disabled')
            ]
        ];
    }

    /**
     * Get State Label
     *
     * @param string $state
     * @return \Magento\Framework\Phrase
     */
    public function getStateLabel($state)
    {
        if ($state) {
            return __('Enable');
        }
        return __('Disabled');
    }
}
