<?php
/**
 * O2TI Flag Recurring Module.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class Flag Recurring Config Provider - Add Type for Recurring.
 */
class RecurringCycleConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $cart;

    /**
     * @var Json
     */
    protected $json;

    /**
     * Construct.
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param CartInterface         $cart
     * @param Json                  $json
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CartInterface  $cart,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cart = $cart;
        $this->json = $json;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->cart->getStoreId();

        $recurringCycle = $this->scopeConfig->getValue(
            'o2ti/payment_subscription_magento/cycle_option',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $result = $this->json->unserialize($recurringCycle);

        return [
            'o2ti_payment_subscription_magento' => [
                'cycle_options' => is_array($result) ? $result : [],
            ],
        ];
    }

}