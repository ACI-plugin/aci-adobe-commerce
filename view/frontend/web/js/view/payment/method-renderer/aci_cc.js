/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Aci_Payment/js/view/payment/method-renderer/aci_payment_abstract',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
        'mage/translate',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'TryzensIgnite_Subscription/js/checkout/model/subscription-options'
    ],
    function (
        $,
        ko,
        Component,
        fullScreenLoader,
        urlBuilder,
        $t,
        quote,
        customer,
        subscriptionOptions
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Aci_Payment/payment/aci-cc-form',
                orderId: null,
                checkoutId: null
            },

            initObservable: function () {
                let self = this;
                this._super()
                    .observe({
                        hasError: ko.observable(false)
                    });

                quote.totals.subscribe(function (totals) {
                    self.triggerInitPayment();
                });

                return this;
            },

            triggerInitPayment: function () {
                let self = this;
                if (quote.paymentMethod()) {
                    if (quote.paymentMethod().method === self.getCode()) {
                        let shippingAddress = quote.shippingAddress() ?? '';
                        let billingAddress = quote.billingAddress() ?? '';
                        self.initPayment(billingAddress, shippingAddress);
                    }
                }
            },

            initialize: function() {
                this._super();
                $('.message.error').hide();
            },

            selectPaymentMethod: function () {
                $('.message.error').hide();
                this.hasError(false);
                return this._super();
            },

            getLogos: function () {
                let paymentMethodCode = this.getPaymentMethodCode();
                return window.checkoutConfig.payment[paymentMethodCode].config.logos;
            },

            getPaymentMethodCode: function() {
                return 'aci_cc';
            },

            getCode: function() {
                let paymentMethodCode = this.getPaymentMethodCode();
                return window.checkoutConfig.payment[paymentMethodCode].methodCode;
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'poNumber': null,
                    'additional_data': null
                };
            },

            /**
             * Get supported card types configured in the admin
             */
            getDataBrands: function () {
                let paymentMethodCode = this.getPaymentMethodCode();
                return window.checkoutConfig.payment[paymentMethodCode].config.supportedCardTypes;
            },

            /**
             * Initialize API payment and create the payment form
             */
            initPayment: function(billingAddress = '', shippingAddress = '') {
                window.unloadWidget();
                let self = this;
                let endpoint = this.getInitEndPoint();
                let formKeyVal = $('input[name="form_key"]').val();
                let isLoggedIn = customer.isLoggedIn();
                let paymentMethodCode = this.getPaymentMethodCode()
                let savePaymentOption = window.checkoutConfig.payment[paymentMethodCode].config.savePaymentOption;
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
                            self.loadAciScript(paymentMethodCode, response.id);
                            if (savePaymentOption && isLoggedIn) {
                                window.wpwlOptions.registrations = Object.assign(
                                    {},
                                    window.wpwlOptions.registrations,
                                    {requireCvv:true}
                                );
                                let onReadyEvent = '';
                                if (window.wpwlOptions.onReady) {
                                    onReadyEvent = window.wpwlOptions.onReady;
                                }
                                window.wpwlOptions.onReady=function(e) {
                                    if (onReadyEvent) {
                                        onReadyEvent();
                                    }
                                    var createRegistrationHtml = '<div id ="saved_cards_option"><div class="customLabel">Save my payment details for future purchases</div><div class="customInput"><input type="checkbox" name="createRegistration" value="true"></div></div>';
                                    $('form.wpwl-form-card').find('#saved_cards_option').remove();
                                    $('form.wpwl-form-card').find('.wpwl-button').before(createRegistrationHtml);
                                };
                            }
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

            /**
             * Stop loader
             */
            stopLoader: function () {
                $('body').trigger('processStop');
                fullScreenLoader.stopLoader();
            },

            /**
             * Get generic error message
             */
            getErrorMessage: function() {
                return $t('Something went wrong while processing payment');
            },

            getWidgetWrapper: function () {
                return 'aci-widget-wrapper';
            }
        });
    }
);
