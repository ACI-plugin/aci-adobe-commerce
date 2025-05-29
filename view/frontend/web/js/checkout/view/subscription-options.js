define([
        'uiComponent',
        'ko',
        'jquery',
        'Magento_Customer/js/model/customer',
        'Aci_Payment/js/checkout/model/subscription-options',
        'Magento_Checkout/js/model/quote',
    ], function (Component, ko, $, customer, subscriptionOptions, quote) {
        'use strict';

        let selectedRecurringOption = ko.observable(null);
        let recurringOption = ko.observable(null);
        let optionContent = ko.observable(null);
        let recurringOptions = ko.observableArray([]);

        return Component.extend({
            defaults: {
                template: 'Aci_Payment/checkout/view/subscription-options',
                configKey: 'recurring'
            },
            recurringOptions: recurringOptions,

            initObservable: function () {
                this._super().observe([
                    'selectedRecurringOption',
                    'recurringOption',
                    'recurringOptions',
                    'optionContent'
                ]);
                return this;
            },

            initialize: function () {
                let self = this;
                this._super();

                self.observeQuotePayment();

                let recurringOptionsData = window.checkoutConfig[this.configKey].frequency;
                self.recurringOptions(this.getRecurringOptions(recurringOptionsData));
            },

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

                        if (typeof methodCode === 'undefined'
                            || typeof allowedMethods === 'undefined'
                            || !allowedMethods.includes(methodCode)) {
                            self.setPaymentReloadedBySubscription(true);
                            self.disableSubscriptionOptions();
                        } else {
                            self.setPaymentReloadedBySubscription(false);
                            self.enableSubscriptionOptions();
                        }
                    });
                }
            },

            enableSubscriptionOptions: function () {
                if (customer.isLoggedIn()) {
                    $('input.recurring__subscribe-option').removeAttr('disabled');
                    $('.recurring-not-available').hide();
                }
            },

            disableSubscriptionOptions: function () {
                $('input.recurring__subscribe-option').attr('disabled', true);
                $('#recurring-none').click();
                $('.recurring-not-available').show()
            },

            getRecurringOptions: function(recurringOptionsData) {
                let self = this;

                return _.reduce(recurringOptionsData,
                    function (accumulator, optionData) {
                        let result = self.buildPaymentMethodComponentResult(optionData);

                        accumulator.push(result);
                        return accumulator;
                    }, []);
            },

            buildPaymentMethodComponentResult: function (optionData) {
                return {
                    recurringOption: optionData,

                    getDisplayName: function () {
                        return optionData.displayName;
                    },

                    getDescription: function () {
                        return optionData.description;
                    },

                    getCode: function () {
                        return optionData.displayName;
                    },

                    getOption: function () {
                        return optionData.displayName;
                    }
                };
            },

            /**
             * Is guest user and subscription enabled in admin
             *
             * @return {Boolean}
             */
            isActive: function () {
                return window.checkoutConfig[this.configKey].status;
            },

            /**
             * Get registration page url
             *
             * @return {string}
             */
            getRegisterUrl: function () {
                return window.checkoutConfig.registerUrl;
            },

            /**
             * Get account login page url
             *
             * @return {string}
             */
            getLoginUrl: function () {
                return window.checkoutConfig[this.configKey].loginUrl;
            },

            isLoggedIn: function () {
                return customer.isLoggedIn();
            },

            selectRecurringOption: function() {
                let self = this;
                optionContent(self.recurringOption.description);
                selectedRecurringOption(self.recurringOption.displayName);
                recurringOption(self.recurringOption);
                subscriptionOptions.recurringOption(self.recurringOption)
                return true;
            },

            resetRecurringOption: function() {
                let self = this;
                optionContent('');
                selectedRecurringOption('');
                recurringOption('');
                subscriptionOptions.recurringOption(self.recurringOption)
                return true;
            },

            getSelectedRecurringOption: ko.computed(function() {
                if (!recurringOption()) {
                    return null;
                }

                if (selectedRecurringOption()) {
                    return selectedRecurringOption();
                }
                return null;
            }),

            getSelectedOptionDesc: ko.computed(function() {
                if (!optionContent()) {
                    return null;
                }
                return optionContent();
            }),

            setPaymentReloadedBySubscription: function (val) {
                subscriptionOptions.setPaymentReloadedBySubscription(val);
            }
        });
    }
);
