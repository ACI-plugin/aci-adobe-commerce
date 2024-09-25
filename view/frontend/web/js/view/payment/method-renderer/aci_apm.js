/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Aci_Payment/js/view/payment/method-renderer/aci_payment_abstract',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-payment-method',
        'mage/url',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/set-payment-information',
        'TryzensIgnite_Subscription/js/checkout/model/subscription-options'
    ],
    function (
        $,
        ko,
        Component,
        fullScreenLoader,
        $t,
        checkoutData,
        quote,
        selectPaymentMethodAction,
        urlBuilder,
        placeOrderAction,
        setPaymentInformationAction,
        subscriptionOptions
    ) {
        'use strict';

        var selectedAlternativePaymentMethodType = ko.observable(null);
        var paymentMethod = ko.observable(null);
        var hasError = ko.observable(null);

        return Component.extend({
            defaults: {
                template: 'Aci_Payment/payment/aci-apm-form',
                orderId: null,
                createTransactionId: null,
                paymentMethods: {}
            },

            applePayPaymentMethods: [
                'APPLEPAY',
                'APPLEPAYTKN'
            ],

            initObservable: function () {
                this._super().observe([
                    'hasError',
                    'selectedAlternativePaymentMethodType',
                    'paymentMethod',
                    'aciApmPaymentMethods'
                ]);
                return this;
            },

            initialize: function() {
                var self = this;
                this._super();
                $('.message.error').hide();
                self.hasError(false)
                fullScreenLoader.startLoader();
                let apmPaymentMethods = window.checkoutConfig.payment[this.getPaymentMethodCode()].paymentMethods
                self.aciApmPaymentMethods(this.getAciApmPaymentMethods(apmPaymentMethods));
                fullScreenLoader.stopLoader();
            },

            getAciApmPaymentMethods: function(paymentMethods) {
                var self = this;

                var paymentList = _.reduce(paymentMethods,
                    function(accumulator, paymentMethod) {
                        var result = self.buildPaymentMethodComponentResult(paymentMethod);

                        accumulator.push(result);
                        return accumulator;
                    }, []);

                return paymentList;
            },

            buildPaymentMethodComponentResult: function (paymentMethod) {
                var self = this;
                var result = {
                    isAvailable: ko.observable(true),

                    initQuoteSubscribe: function () {
                        let current = this;
                        quote.totals.subscribe(function (totals) {
                            current.triggerInitPayment();
                        });
                    },

                    triggerInitPayment: function () {
                        let current = this;
                        if (quote.paymentMethod()) {
                            if ((quote.paymentMethod().method === self.getCode()) && selectedAlternativePaymentMethodType) {
                                if (current.getAciApmValue() === selectedAlternativePaymentMethodType()) {
                                    let shippingAddress = quote.shippingAddress() ?? '';
                                    let billingAddress = quote.billingAddress() ?? '';
                                    current.initPayment(billingAddress, shippingAddress);
                                }
                            }
                        }
                    },

                    paymentMethod: paymentMethod,
                    method: self.item.method,
                    item: {
                        'title': paymentMethod.title,
                        'method': paymentMethod.name
                    },

                    /**
                     * Observable to enable and disable place order buttons for payment methods
                     * Default value is true to be able to send the real hpp requests that doesn't require any input
                     * @type {observable}
                     */
                    placeOrderAllowed: ko.observable(true),

                    showPaymentOption: function () {
                        if (self.applePayPaymentMethods.includes(paymentMethod.name)) {
                            if (window.ApplePaySession) {
                                return !!window.ApplePaySession.canMakePayments();
                            }
                        } else {
                            return true;
                        }
                    },

                    getIcon: function () {
                        return paymentMethod.icon
                    },

                    getTitle: function () {
                        return paymentMethod.title;
                    },

                    selectPaymentMethod: function () {
                        return self.selectPaymentMethod();
                    },

                    getWidgetWrapper: function () {
                        return 'aci_apm_widget_'+paymentMethod.name;
                    },

                    validate: function() {
                        return self.validate(paymentMethod.name);
                    },

                    getCode: function() {
                        return self.getCode();
                    },

                    getData: function() {
                        return {
                            'method': this.getCode(),
                            'additional_data': {
                                'brand_name': paymentMethod.name
                            }
                        };
                    },

                    getAciApmValue: function () {
                        return 'aci_'+paymentMethod.name;
                    },

                    getDataBrands: function () {
                        return paymentMethod.name.toUpperCase()
                    },

                    getPaymentMethodCode: function () {
                        return self.getPaymentMethodCode()
                    },

                    getInitEndPoint: function () {
                        return self.getInitEndPoint()
                    },

                    loadAciScript: function (paymentMethodCode, checkoutId) {
                        return self.loadAciScript(paymentMethodCode, checkoutId)
                    },

                    hasError: function (error) {
                        self.hasError(error)
                    },

                    getErrorMessage: function () {
                        return self.getErrorMessage();
                    },

                    getPaymentActionUrl: function () {
                        return self.getPaymentActionUrl();
                    },

                    isAppleDevice: function (paymentMethod) {
                        if (self.applePayPaymentMethods.includes(paymentMethod.name)) {
                            if (window.ApplePaySession) {
                                return window.ApplePaySession.canMakePayments();
                            }
                        }
                        return false;
                    },

                    initWpwlEvents: function (formKeyVal) {
                        let currentObject = this;
                        let onReadyEvent = '';
                        if (window.wpwlOptions.onReady) {
                            onReadyEvent = window.wpwlOptions.onReady;
                        }
                        window.wpwlOptions.onReady=function(e) {
                            if (onReadyEvent) {
                                onReadyEvent();
                            }
                            window.wpwlOptions.googlePay.onCancel=function (params) {
                                currentObject.triggerInitPayment();
                            };
                            if (currentObject.isAppleDevice(paymentMethod)) {
                                window.wpwlOptions.applePay.onCancel = function (params) {
                                    currentObject.triggerInitPayment();
                                };
                            }
                        };

                        return self.initWpwlEvents(formKeyVal, currentObject);
                    },
                    sendSubscriptionOptions: function () {
                        return self.sendSubscriptionOptions();
                    },

                    /**
                     * Load payment widget form
                     */
                    loadAciForm: function () {
                        var self = this;
                        let widgetWrapperId = self.getWidgetWrapper();
                        let formTag = document.createElement( 'form' );
                        formTag.className='paymentWidgets';
                        formTag.setAttribute('data-brands', this.getDataBrands());
                        formTag.action=self.getPaymentActionUrl()
                        document.getElementById(widgetWrapperId).appendChild(formTag);
                    },

                    /**
                     * Initialize API payment and create the payment form
                     */
                    initPayment: function(billingAddress = '', shippingAddress = '') {
                        let self = this;
                        let paymentMethodCode = self.getPaymentMethodCode();
                        let endpoint = self.getInitEndPoint();
                        let formKeyVal = $('input[name="form_key"]').val();
                        $('body').trigger('processStart');
                        $.ajax({
                            url: urlBuilder.build(endpoint),
                            type: 'POST',
                            data: {
                                form_key: formKeyVal,
                                billingAddress: billingAddress?JSON.stringify(billingAddress):'',
                                shippingAddress: shippingAddress?JSON.stringify(shippingAddress):''
                            },
                            dataType: 'json'
                        }).done(function (response) {
                            self.hasError(false);
                            if (response){
                                if (response.id) {
                                    window.unloadWidget();
                                    self.loadAciScript(paymentMethodCode, response.id);
                                    self.initWpwlEvents(formKeyVal);
                                    self.loadAciForm();
                                } else  {
                                    self.hasError(true);
                                    $('.message.error').html('<div>' + self.getErrorMessage() + '</div>');
                                }
                            } else{
                                self.hasError(true);
                            }
                            $('body').trigger('processStop');
                            fullScreenLoader.stopLoader();
                        }).fail(function (response) {
                            self.hasError(true);
                            $('body').trigger('processStop');
                            fullScreenLoader.stopLoader();
                        });
                    },
                };

                return result;
            },

            getPaymentMethodCode: function() {
                return 'aci_apm';
            },

            getCode: function() {
                let paymentMethodCode = this.getPaymentMethodCode();
                return window.checkoutConfig.payment[paymentMethodCode].methodCode;
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': null
                };
            },

            getDataBrands: function () {
                let paymentMethodCode = this.getPaymentMethodCode();
                return window.checkoutConfig.payment[paymentMethodCode].config.supportedCardTypes;
            },

            getErrorMessage: function() {
                return $t('Something went wrong while processing payment');
            },

            selectPaymentMethodType: function() {
                let self = this;

                // set payment method to adyen_hpp
                let data = {
                    'method': self.method,
                    'po_number': null,
                    'additional_data': {
                        brand_name: self.paymentMethod.name,
                    },
                };

                // set the payment method type
                selectedAlternativePaymentMethodType('aci_'+self.paymentMethod.name);

                // set payment method
                paymentMethod(self.method);

                setPaymentInformationAction(this.messageContainer, data)

                selectPaymentMethodAction(data);
                checkoutData.setSelectedPaymentMethod(self.method);

                return true;
            },

            getSelectedAlternativePaymentMethodType: ko.computed(function() {
                if (!quote.paymentMethod()) {
                    return null;
                }

                if (!paymentMethod()) {
                    return null;
                }

                if (quote.paymentMethod().method === paymentMethod()) {
                    return selectedAlternativePaymentMethodType();
                }
                return null;
            })
        });
    }
);
