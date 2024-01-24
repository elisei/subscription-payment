<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Gateway\Request\CreditCard;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\InfoInterface;
use PagBank\PaymentMagento\Gateway\Config\Config;
use PagBank\PaymentMagento\Gateway\Config\ConfigCc;
use PagBank\PaymentMagento\Gateway\Request\ChargesDataRequest;

/**
 * Class Payment Cc Data Request - Structure of payment for Credit Card.
 */
class RecurringDataRequest implements BuilderInterface
{
    /**
     * Recurring block name.
     */
    public const RECURRING = 'recurring';

    /**
     * Recurring Type block name.
     */
    public const RECURRING_TYPE = 'type';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ConfigCc
     */
    protected $configCc;

    /**
     * @param Config   $config
     * @param ConfigCc $configCc
     */
    public function __construct(
        Config $config,
        ConfigCc $configCc
    ) {
        $this->config = $config;
        $this->configCc = $configCc;
    }

    /**
     * Build.
     *
     * @param array $buildSubject
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function build(array $buildSubject)
    {
        $result = [];

        /** @var PaymentDataObject $paymentDO * */
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var InfoInterface $payment * */
        $payment = $paymentDO->getPayment();

        $recurringType = $payment->getAdditionalInformation('recurring_type');

        if ($recurringType) {
            $result[ChargesDataRequest::CHARGES][] = [
                self::RECURRING => [
                    self::RECURRING_TYPE => strtoupper($recurringType),
                ]
            ];
        }

        return $result;
    }
}
