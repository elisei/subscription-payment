/**
 * O2TI Flag Recurring Module.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

var config = {
    config: {
        mixins: {
            'PagBank_PaymentMagento/js/view/payment/method-renderer/cc': {
                'O2TI_SubscriptionPayment/js/view/payment/method-renderer/cc-mixin': true
            }
        }
    }
};