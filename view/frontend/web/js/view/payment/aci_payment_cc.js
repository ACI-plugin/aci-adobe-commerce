/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'onsite_cc',
                component: 'Aci_Payment/js/view/payment/method-renderer/aci_cc'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
