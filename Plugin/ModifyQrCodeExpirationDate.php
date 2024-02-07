<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Plugin;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Model\InfoInterface;
use PagBank\PaymentMagento\Gateway\Request\QrCodeDataRequest;
use PagBank\PaymentMagento\Gateway\Request\QrCodeExpirationDateDataRequest;

class ModifyQrCodeExpirationDate
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * ModifyQrCodeExpirationDate constructor.
     * @param DateTime $date
     */
    public function __construct(DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * Around Plugin for modifying the expiration date value.
     *
     * @param QrCodeExpirationDateDataRequest  $subject
     * @param callable                         $proceed
     * @param array                            $buildSubject
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(
        QrCodeExpirationDateDataRequest $subject,
        callable $proceed,
        array $buildSubject
    ) {
        $buildResult = $proceed($buildSubject);

        /** @var PaymentDataObject $paymentDO **/
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var InfoInterface $payment **/
        $payment = $paymentDO->getPayment();

        if ($payment->getAdditionalInformation('recurring_type') === 'SUBSEQUENT') {
            $buildResult
            [QrCodeDataRequest::QR_CODES][0]
            [QrCodeExpirationDateDataRequest::EXPIRATION_DATE] =
                $this->date->gmtDate('Y-m-d\TH:i:s\Z', strtotime("+1 day"));
        }

        return $buildResult;
    }
}
