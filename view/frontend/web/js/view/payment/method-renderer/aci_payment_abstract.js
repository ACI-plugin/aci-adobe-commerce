/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Aci_Payment/js/checkout/model/subscription-options',
        'mage/translate'
    ],
    function (
        $,
        ko,
        Component,
        fullScreenLoader,
        quote,
        urlBuilder,
        subscriptionOptions,
        $t
    ) {
        'use strict';

        window.unloadWidget = function() {
            if (window.wpwl !== undefined && window.wpwl.unload !== undefined) {
                window.wpwl.unload();
                let divToRemove = $('.wpwl-form.wpwl-form-registrations.wpwl-clearfix');
                if (divToRemove) {
                    divToRemove.remove();
                }
                let wpwlContainer = $('.wpwl-container');
                if (wpwlContainer.length) {
                    wpwlContainer.remove();
                }
                $('script').each(function () {
                    if (this.src.indexOf('static.min.js') !== -1 || this.src.indexOf('paymentWidgets.js?checkoutId=') !== -1) {
                        $(this).remove();
                    }
                });
            }
        };

        let subscriptionOptionSendFlag= false;

        return Component.extend({
            defaults: {
                template: '',
                totalsValidationUrl: 'base/payment/quotetotals',
                quote_grand_total: null
            },

            initialize: function () {
                this._super();
                if (!subscriptionOptionSendFlag) {
                    const self = this;
                    subscriptionOptionSendFlag = true;
                    let currentSubscriptionOption = '';
                    subscriptionOptions.recurringOption.subscribe(function (recurringOption) {
                        if (currentSubscriptionOption !== recurringOption) {
                            currentSubscriptionOption = recurringOption;
                            self.sendSubscriptionOptions();
                        }
                    });
                }
            },

            loadAciScript: function (paymentMethodCode, checkoutId, integrity) {
                if (checkoutId) {
                    let script = document.createElement("script");
                    let scriptSrc = window.checkoutConfig.payment[paymentMethodCode].config.scriptSrc
                    script.src = scriptSrc+'?checkoutId='+checkoutId;
                    script.integrity = integrity;
                    script.crossOrigin = "anonymous";
                    script.type = 'text/javascript';
                    document.body.appendChild(script);
                }
            },

            /**
             * Load payment widget form
             */
            loadAciForm: function () {
                const self = this;
                let widgetWrapperId = self.getWidgetWrapper();
                let formTag = document.createElement( 'form' );
                formTag.className='paymentWidgets';
                formTag.setAttribute('data-brands', this.getDataBrands());
                formTag.action=this.getPaymentActionUrl()
                document.getElementById(widgetWrapperId).appendChild(formTag);
            },

            /**
             * Get shopper result url configured in the admin
             */
            getPaymentActionUrl: function () {
                let code = this.getAciGenericCode();
                return window.checkoutConfig.payment[code].shopperResultURL;
            },

            getAciGenericCode: function() {
                return 'tryzensignite';
            },

            getCode: function() {
                let paymentMethodCode = this.getPaymentMethodCode();
                return window.checkoutConfig.payment[paymentMethodCode].methodCode;
            },

            getInitEndPoint: function () {
                let paymentMethodCode = this.getPaymentMethodCode();
                return window.checkoutConfig.payment[paymentMethodCode].config.initPaymentUrl;
            },

            sendSubscriptionOptions: function() {
                let self = this;
                var recurringOption = subscriptionOptions.recurringOption();
                $.ajax({
                    url: urlBuilder.build('acipayment/recurring/recurringorder'),
                    type: 'POST',
                    contentType: 'application/json',
                    async: false,
                    data: JSON.stringify({
                        recurring_options: recurringOption
                    }),
                    dataType: 'json'
                }).done(function (response) {
                    self.triggerInitPayment();
                });

            },

            validateTotals: function (formKeyVal) {
                let totalsUnchangedFlag = false;
                $.ajax({
                    url: urlBuilder.build(this.totalsValidationUrl),
                    type: 'POST',
                    async: false,
                    data: {
                        form_key: formKeyVal,
                    },
                    dataType: 'json'
                }).done(function (responseValue) {
                    if (responseValue.total){
                        let totals = quote.totals();
                        if(self.quote_grand_total == null) {
                            self.quote_grand_total = totals.grand_total
                        }
                        if (responseValue.total == self.quote_grand_total) {
                            totalsUnchangedFlag = true;
                        } else {
                            self.quote_grand_total = responseValue.total;
                            totalsUnchangedFlag = false;
                        }
                    }
                });
                return totalsUnchangedFlag;
            },

            handleBeforeSubmit: async function (formKeyVal, parentObject = null) {
                const self = this;
                if (!quote.billingAddress()) {
                   if (parentObject) {
                       parentObject.initPayment();
                   } else {
                       self.initPayment();
                   }
                   self.hasError(true);
                   $('.message.error').html('<div>' +  self.getBillingAddressErrorMessage() + '</div>');
                   return false;
                 }
                let validateTotals = self.validateTotals(formKeyVal);
                if (validateTotals) {
                    return await self.customPlaceOrder();
                } else {
                    let shippingAddress = quote.shippingAddress() ?? '';
                    let billingAddress = quote.billingAddress() ?? '';
                    if (parentObject) {
                        parentObject.initPayment(billingAddress, shippingAddress);
                    } else {
                        self.initPayment(billingAddress, shippingAddress);
                    }
                }
                return false;
            },

            initWpwlOnReadyEvent: function (formKeyVal, parentObject = null) {
                const _self = this;

                let onReadyEvent = '';
                if (window.wpwlOptions.onReady) {
                    onReadyEvent = window.wpwlOptions.onReady;
                }
                window.wpwlOptions.onReady=function(e) {
                    if (onReadyEvent) {
                        onReadyEvent();
                    }
                    window.wpwlOptions.paypal.onApprove=function (event) {
                        return _self.handleBeforeSubmit(formKeyVal, parentObject).then((result) => {
                            return result;
                        }).catch((error) => {
                            return false;
                        });
                    };
                };
            },

            initWpwlEvents: function (formKeyVal, parentObject = null) {
                const _self = this;
                _self.initWpwlOnReadyEvent(formKeyVal, parentObject);

                /**
                 * Event triggers before card payment / Googlepay submission.
                 *
                 * @param event
                 * @returns {*}
                 */
                window.wpwlOptions.onBeforeSubmitCard=function(event) {
                    if (_self.getCode() === 'aci_apm') {
                        return _self.handleBeforeSubmit(formKeyVal, parentObject).then((result) => {
                            return result;
                        }).catch((error) => {
                            return false;
                        });
                    } else {
                        return _self.handleBeforeSubmit(formKeyVal).then((result) => {
                            return result;
                        }).catch((error) => {
                            return false;
                        })
                    }
                };

                /**
                 * Event triggers before saved card payment submission.
                 *
                 * @param event
                 * @returns {*|boolean}
                 */
                window.wpwlOptions.onBeforeSubmitOneClickCard=function(event) {
                    return _self.handleBeforeSubmit(formKeyVal).then((result) => {
                        return result;
                    }).catch((error) => {
                        return false;
                    });
                };

                /**
                 * Event triggers before SOFORT / IDEAL payment submission.
                 *
                 * @param event
                 * @returns {*}
                 */
                window.wpwlOptions.onBeforeSubmitOnlineTransfer=function (event) {
                    return _self.handleBeforeSubmit(formKeyVal, parentObject).then((result) => {
                        return result;
                    }).catch((error) => {
                        return false;
                    });
                };

                if (_self.getCode() === 'aci_apm') {
                    /**
                     * Event triggers before Paypal/Klarna payment submission.
                     * @param event
                     * @returns {*}
                     */
                    window.wpwlOptions.onBeforeSubmitVirtualAccount=function (event) {
                        return _self.handleBeforeSubmit(formKeyVal, parentObject).then((result) => {
                            return result;
                        }).catch((error) => {
                            return false;
                        });
                    };
                }

                $(document).off('click', 'button.wpwl-button:submit').on('click','button.wpwl-button:submit',function(event) {
                    event.preventDefault();
                    let targetElement = $(this).closest('form.wpwl-form').parent('div.wpwl-container');
                    if (targetElement && targetElement.length) {
                        let targetClass = $(targetElement).attr('class');
                        window.wpwl.executePayment(targetClass.trim().replace(/\s/g, "."));
                    }
                });
            },

            isSubscriptionEnabled: function () {
                return  window.checkoutConfig.recurring.status;
            },

            /**
             * Place order in Magento before transaction
             */
            customPlaceOrder: function () {
                fullScreenLoader.startLoader();
                this.getPlaceOrderDeferredObject()
                    .then((result)=>{
                        fullScreenLoader.stopLoader();
                        return !!result;
                    })
                    .fail(
                        function() {
                            fullScreenLoader.stopLoader();
                            return false;
                        })
            },

            /**
             * Get generic error message
             */
            getBillingAddressErrorMessage: function() {
                return $t('Please enter billing address to continue');
            },
        });
    }
);
