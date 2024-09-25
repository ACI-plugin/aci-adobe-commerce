define([
    'jquery',
    'Magento_Checkout/js/model/quote',
], function ($, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({

            getSubscriptionEnabledMethods: function () {
                return window.checkoutConfig[this.configKey].payment_methods;
            },

            observeQuotePayment: function () {
                let self = this;
                if (self.isActive()) {
                    quote.paymentMethod.subscribe(function (value) {
                        let methodCode = value.method;
                        let brandName = value?.additional_data?.brand_name;
                        if (typeof brandName !== 'undefined' && brandName) {
                            methodCode = brandName;
                        }
                        if (typeof brandName === 'undefined' && methodCode === 'aci_apm') {
                            self.setPaymentReloadedBySubscription(false);
                            self.enableSubscriptionOptions();
                            return;
                        }

                        let allowedMethods = self.getSubscriptionEnabledMethods();
                        if (methodCode && !allowedMethods.includes(methodCode)) {
                            self.setPaymentReloadedBySubscription(true);
                            self.disableSubscriptionOptions();
                        } else {
                            self.setPaymentReloadedBySubscription(false);
                            self.enableSubscriptionOptions();
                        }
                    });
                }
            }
        });
    }
});
