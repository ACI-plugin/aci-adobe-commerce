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
                type: 'aci_apm',
                component: 'Aci_Payment/js/view/payment/method-renderer/aci_apm'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
