<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Model\Email;

use Magento\Framework\Exception\MailException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Magento\Framework\DataObject;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer;
use O2TI\SubscriptionPayment\Model\Email\Container\SubscriptionIdentity as Identity;

class EmailNotification extends Sender
{
    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var Identity
     */
    protected $identity;

    /**
     * Constructor.
     *
     * @param Template $templateContainer
     * @param Identity $identity
     * @param \Psr\Log\LoggerInterface $logger
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        Template $templateContainer,
        Identity $identity,
        \Psr\Log\LoggerInterface $logger,
        SenderBuilderFactory $senderBuilderFactory,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper
    ) {
        parent::__construct($templateContainer, $identity, $senderBuilderFactory, $logger, $addressRenderer);
        $this->paymentHelper = $paymentHelper;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * Send custom email.
     *
     * @param Order $order
     * @return void
     * @throws MailException
     */
    public function sendCustomEmail(\Magento\Sales\Model\Order $order)
    {
        $order->setSendEmail($this->identityContainer->isEnabled());
        $this->checkAndSend($order);
    }

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $requirePayment = true;
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();

        if ($paymentMethod === 'pagbank_paymentmagento_cc'
            || $paymentMethod === 'pagbank_paymentmagento_cc_vault'
        ) {
            $requirePayment = false;
        }
        
        $transport = [
            'order' => $order,
            'order_id' => $order->getId(),
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'created_at_formatted' => $order->getCreatedAtFormatted(2),
            'order_data' => [
                'customer_name' => $order->getCustomerName(),
                'is_not_virtual' => $order->getIsNotVirtual(),
                'email_customer_note' => $order->getEmailCustomerNote(),
                'frontend_status_label' => $order->getFrontendStatusLabel(),
                'required_payment' => $requirePayment
            ]
        ];
        $transportObject = new DataObject($transport);

        $this->templateContainer->setTemplateVars($transportObject->getData());

        parent::prepareTemplate($order);
    }

    /**
     * Get payment info block as html
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $order->getStoreId()
        );
    }
}

