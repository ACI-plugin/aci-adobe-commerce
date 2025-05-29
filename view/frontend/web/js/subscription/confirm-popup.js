define([
    'jquery',
    'Magento_Ui/js/modal/confirm'
    ],function($, confirmation) {
    $('.cancel-subscription').on('click', function (e){
        e.preventDefault();
        let subscriptionId = this.getAttribute('data-subscription-id');
        confirmation({
            title: 'Cancel Recurring Order',
            content: 'Are you sure you want to deactivate this Recurring Order?',
            actions: {
                confirm: function () {
                    window.location = BASE_URL + 'acipayment/recurring/cancel/subscription_id/' + subscriptionId;
                },

                cancel: function () {
                    return false;
                }
            }
        });
    });
});
