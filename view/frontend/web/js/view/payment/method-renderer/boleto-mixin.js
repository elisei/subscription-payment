/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    var boletoMixin = {

        defaults: {
            active: false,
            template: 'O2TI_SubscriptionPayment/payment/boleto-mixin',
            boletoForm: 'PagBank_PaymentMagento/payment/boleto-form',
            payerForm: 'PagBank_PaymentMagento/payment/payer-form',
            recurringActive: true,
            recurringType: 'INITIAL',
            recurringCycle: ''
        },

        initObservable: function () {
            if (typeof this._super === 'function') {
                this._super();
            }

            this.observe([
                'recurringActive',
                'recurringType',
                'recurringCycle'
            ]);

            return this;
        },

        getData: function () {
            var data = wrapper.wrap(this._super, function (originalFunction) {
                var originalData = originalFunction();
                
                originalData['additional_data']['recurring_type'] = this.recurringActive() ? this.recurringType() : null;
                originalData['additional_data']['recurring_cycle'] = this.recurringCycle();

                return originalData;
            });

            return data.call(this);
        },

        /**
         * Get Cycle Options
         * @returns {Array}
         */
        getCycleOptions: function () {
            var options = window.checkoutConfig.o2ti_payment_subscription_magento.cycle_options;
            return _.map(options, (option) => {
                return {
                    'value': option.value,
                    'label': option.label
                };
            });
        },

        /**
         * Get Subscription Enable
         * @returns {Boolean}
         */
        getSubscriptionEnable: function () {
            return window.checkoutConfig.o2ti_payment_subscription_magento.hasOwnProperty('enable') ?
            window.checkoutConfig.o2ti_payment_subscription_magento.enable
            : false;
        }
    };

    return function (target) {
        return target.extend(boletoMixin);
    };
});
