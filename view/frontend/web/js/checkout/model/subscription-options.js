define([
        'ko'
    ], function (ko) {
    'use strict';

    var recurringOption = ko.observable(null);
    var paymentReloadedBySubscription = ko.observable(false);

    return {
        recurringOption: recurringOption,
        paymentReloadedBySubscription: paymentReloadedBySubscription,

        getpaymentReloadedBySubscription : function () {
            return this.paymentReloadedBySubscription();
        },
        
        setPaymentReloadedBySubscription : function (val) {
            this.paymentReloadedBySubscription(val);
        }
    }
});
