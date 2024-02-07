/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

var config = {
    config: {
        mixins: {
            'PagBank_PaymentMagento/js/view/payment/method-renderer/cc': {
                'O2TI_SubscriptionPayment/js/view/payment/method-renderer/cc-mixin': true
            },
            'PagBank_PaymentMagento/js/view/payment/method-renderer/pix': {
                'O2TI_SubscriptionPayment/js/view/payment/method-renderer/pix-mixin': true
            },
            'PagBank_PaymentMagento/js/view/payment/method-renderer/boleto': {
                'O2TI_SubscriptionPayment/js/view/payment/method-renderer/boleto-mixin': true
            }
        }
    }
};