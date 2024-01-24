define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    var ccMixin = {

        defaults: {
            active: false,
            template: 'O2TI_SubscriptionPayment/payment/cc-mixin',
            ccForm: 'PagBank_PaymentMagento/payment/cc-form',
            payerForm: 'PagBank_PaymentMagento/payment/payer-form',
            creditCardNumberToken: '',
            recurringType: 'INITIAL',
            recurringCycle: ''
        },

        initObservable: function () {
            if (typeof this._super === 'function') {
                this._super();
            }

            this.observe([
                'recurringType',
                'recurringCycle'
            ]);

            return this;
        },

        getData: function () {
            var data = wrapper.wrap(this._super, function (originalFunction) {
                var originalData = originalFunction();

                originalData['additional_data']['recurring_type'] = this.recurringType();
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
        }
    };

    return function (target) {
        return target.extend(ccMixin);
    };
});
