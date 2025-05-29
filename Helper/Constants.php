<?php

namespace Aci\Payment\Helper;

use TryzensIgnite\Base\Helper\Constants as BaseConstants;

/**
 * Helper class for requests
 */
class Constants extends BaseConstants
{
    public const API_METHOD_DELETE   = 'DELETE';
    public const ACI_PAYMENT_HEADER_CONTENT_TYPE = 'application/x-www-form-urlencoded';
    public const INITIALIZE_TRANSACTION     =   'initTransaction';
    public const END_POINT_CANCEL_SUBSCRIPTION  =   '/scheduling/v1/schedules';

    //Command end point constants
    public const END_POINT_INIT_TRANSACTION     =   'v1/checkouts';
    public const END_POINT_PAYMENT_WIDGET       =   '/v1/paymentWidgets.js';
    public const END_POINT_GET_STATUS           =   '/v1/checkouts/%s/payment';
    public const END_POINT_GET_WEBHOOK_PAYMENT_STATUS   =   '/v3/query/%s';

    public const END_POINT_BACKOFFICE_OPERATION     = '/v1/payments/';
    public const END_POINT_SUBSCRIPTION = '/scheduling/v1/schedules';

    //API Method constants
    public const API_METHOD_GET   = 'GET';
    public const API_METHOD_POST   = 'POST';

    //Aci Commands
    public const COMMAND_GET_STATUS   = 'getStatus';

    //Aci Service Types
    public const SERVICE_AUTHORIZATION      = 'authorization';
    public const SERVICE_AUTHORIZE          = 'authorize';
    public const SERVICE_CAPTURE            = 'Capture';
    public const SERVICE_REFUND             = 'refund';
    public const SERVICE_VOID               = 'void';

    //Aci Payment Types
    public const PAYMENT_TYPE_AUTH      = 'PA';
    public const PAYMENT_TYPE_SALE      = 'DB';
    public const PAYMENT_TYPE_CAPTURE   = 'CP';
    public const PAYMENT_TYPE_REFUND    = 'RF';
    public const PAYMENT_TYPE_CANCEL    = 'RV';

    //APM Constants
    public const DEFAULT_PAYMENT_TYPE_APM     = 'PA';
    public const APM_BRAND_NAME             = 'brand_name';

    //Return url param constants
    public const URL_PARAM_ID               =   'id';
    public const URL_PARAM_RESOURCE_PATH    =   'resourcePath';

    // Request/Response keys
    public const KEY_LOCALE                       = 'locale';
    public const KEY_TEST_MODE                    = 'testMode';
    public const KEY_CUSTOMER_EMAIL           = 'email';
    public const KEY_CHECKOUT_ID              = 'id';
    public const KEY_REFERENCED_ID            = 'referencedId';
    public const KEY_ACI_PAYMENT_ENTITY_ID    = 'entityId';
    public const KEY_CUSTOMER_PHONE           = 'phone';
    public const KEY_CUSTOMER_IP              = 'ip';
    public const KEY_FIRST_NAME               = 'givenName';
    public const KEY_LAST_NAME                = 'surname';
    public const KEY_CITY                     = 'city';
    public const KEY_COUNTRY_CODE             = 'country';
    public const KEY_POSTAL_CODE              = 'postcode';
    public const KEY_STATE                    = 'state';
    public const KEY_STREET_1                 = 'street1';
    public const KEY_STREET_2                 = 'street2';
    public const KEY_ACI_PAYMENT_CURRENCY     = 'currency';
    public const KEY_ACI_PAYMENT_MERCHANT_TRANSACTION_ID = 'merchantTransactionId';
    public const KEY_ACI_PAYMENT_PAYMENT_TYPE = 'paymentType';
    public const KEY_PAYMENT_REFERENCE_ID     = 'referencedId';
    public const KEY_ACI_PAYMENT_AMOUNT       = 'amount';
    public const KEY_ACI_PRESENTATION_AMOUNT  = 'presentationAmount';
    public const KEY_ACI_PRESENTATION_CURRENCY  = 'presentationCurrency';
    public const KEY_CART_ITEM_NAME           = 'name';
    public const KEY_CART_ITEM_QUANTITY       = 'quantity';
    public const KEY_CART_ITEM_SKU            = 'sku';
    public const KEY_CART_ITEM_PRICE          = 'price';
    public const KEY_CART_ITEM_DESC           = 'description';
    public const KEY_CART_ITEM_DISCOUNT       = 'discount';
    public const KEY_CART_ITEM_TAX            = 'tax';
    public const KEY_CART_ITEM_TOTAL_TAX      = 'totalTaxAmount';
    public const KEY_CART_ITEM_TOTAL_AMOUNT   = 'totalAmount';
    public const KEY_CART_ITEMS               = 'cart.items';
    public const KEY_SYSTEM_NAME              = 'plugin';
    public const KEY_SYSTEM_VERSION           = 'pluginVersion';
    public const KEY_SOURCE                   = 'source';
    public const KEY_MODULE_NAME              = 'moduleName';
    public const KEY_MODULE_VERSION           = 'moduleVersion';
    public const KEY_CUSTOMER_ID            = 'merchantCustomerId';
    public const KEY_PAYMENT_BRAND          = 'paymentBrand';
    public const KEY_REGISTRATION_ID        = 'registrationId';
    public const KEY_CAPTURE_REF_ID         = 'CP_RefId';
    public const KEY_REFUND_REF_ID          = 'RP_RefId';
    public const KEY_CITY_BA                = 'city';
    public const KEY_COUNTRY_CODE_BA        = 'countryId';
    public const KEY_POSTAL_CODE_BA         = 'postcode';
    public const KEY_STATE_BA               = 'regionCode';
    public const KEY_STREET_BA              = 'street';
    public const KEY_INTEGRITY              = 'integrity';

    // Request/Response values
    public const VALUE_PLATFORM_NAME          = 'Magento';
    public const MODULE_NAME            = 'Aci_Payment';
    public const VALUE_SHIPPING_QUANTITY      = 1;

    //Request key prefix
    public const CUSTOMER_PREFIX              = 'customer';
    public const BILLING_ADDRESS_PREFIX       = 'billing';
    public const SHIPPING_ADDRESS_PREFIX      = 'shipping';

    // Config Keys
    public const KEY_PAYMENT_ACTION         = 'payment_action';
    public const KEY_TEST_ENTITY_ID         = 'test_entity_id';
    public const KEY_LIVE_ENTITY_ID         = 'live_entity_id';
    public const KEY_TEST_WEBHOOK_ENCRYPTION_SECRET = 'test_webhook_encryption_secret';
    public const KEY_LIVE_WEBHOOK_ENCRYPTION_SECRET = 'live_webhook_encryption_secret';
    public const PURCHASE_TOTALS = 'purchaseTotals';
    public const CHARGE_AMOUNT = 'chargeAmount';
    public const TAX_TOTAL = 'taxTotal';
    public const KEY_ACI_JAVASCRIPT         = 'aci_javascript';
    public const KEY_ACI_CSS                = 'aci_css';
    public const KEY_SUPPORTED_CARD_TYPES   = 'supported_card_types';
    public const KEY_CARD_TYPE_ICONS        = 'card_type_icons';
    public const KEY_TEST_MODE_CONFIG       = 'test_mode';
    public const KEY_ACTIVE                 = 'active';
    public const KEY_DEBUG                  = 'debug';

    //Saved cards fields
    public const MASKED_CARD_NUMBER         =   'last4Digits';
    public const CARD_EXPIRATION_MONTH      =   'expiryMonth';
    public const CARD_EXPIRATION_YEAR       =   'expiryYear';
    public const CARD_ISSUER                =   'cardIssuer';

    // Aci Response Validation Statuses
    public const SUCCESS                   = 'success';
    public const PENDING                   = 'pending';
    public const MANUAL_REVIEW             = 'manual_review';
    public const REJECTED                  = 'rejected';
    public const FAILED                    = 'failed';

    public const GET_TRANSACTION_RESPONSE   = 'get_transaction_response' ;

    // Notification Response Keys
    public const KEY_NOTIFICATION_PAYLOAD                   = 'payload';
    public const KEY_NOTIFICATION_RESULT                    = 'result';
    public const KEY_NOTIFICATION_CODE                      = 'code';
    public const NOTIFICATION_SERVICE_CAPTURE               = 'capture';
    public const KLARNA_PAYMENTS                            = 'KLARNA_PAYMENTS';
    public const KEY_CUSTOMER_MOBILE                        = 'mobile';
    public const KEY_SHIPPING_METHOD                        = 'method';
    public const KEY_SHIPPING_FIRSTNAME                     = 'firstname';
    public const KEY_SHIPPING_LASTNAME                      = 'lastname';
    public const KEY_SHIPPING_TELEPHONE                     = 'telephone';
    public const PATH_TO_SUPPORTED_PAYMENT_METHODS          = 'payment/aci_subscription/apm_subscription';

    // Registration/Scheduler Request keys
    public const KEY_CREATE_REGISTRATION = 'createRegistration';
    public const KEY_STANDING_INSTRUCTION_MODE = 'standingInstruction.mode';
    public const KEY_STANDING_INSTRUCTION_TYPE = 'standingInstruction.type';
    public const KEY_STANDING_INSTRUCTION_SOURCE = 'standingInstruction.source';
    public const KEY_STANDING_INSTRUCTION_RECURRING_TYPE = 'standingInstruction.recurringType';
    public const KEY_JOB_EXPRESSION = 'job.expression';

    // Registration/Scheduler Response keys
    public const REGISTRATION_STANDING_INSTRUCTION_MODE = 'INITIAL';
    public const STANDING_INSTRUCTION_TYPE = 'RECURRING';
    public const REGISTRATION_STANDING_INSTRUCTION_SOURCE = 'CIT';
    public const STANDING_INSTRUCTION_RECURRING_TYPE = 'SUBSCRIPTION';
    public const SCHEDULER_STANDING_INSTRUCTION_MODE = 'REPEATED';
    public const SCHEDULER_STANDING_INSTRUCTION_SOURCE = 'MIT';
    public const RESPONSE_TYPE_SCHEDULE = 'SCHEDULE';
    public const RESPONSE_TYPE_REGISTRATION = 'REGISTRATION';
    public const KEY_NOTIFICATION_TYPE = 'type';
    public const KEY_NOTIFICATION_SOURCE = 'source';
    public const KEY_NOTIFICATION_TYPE_PAYMENT = 'PAYMENT';
    public const KEY_NOTIFICATION_SOURCE_SCHEDULER = 'SCHEDULER';
    public const CHECKOUT_TRANSACTION_ID        =   'CheckoutTransactionId';
    public const TRANSACTION_ID                 =   'transaction_id';
    public const INVOICE_NUMBER                 =   'InvoiceNumber';
    public const GET_TRANSACTION_REQUEST        =   'OriginalRequest';
    public const TRANSACTION_TOTAL              =   'TransactionTotal';
    public const GET_TRANSACTION_ID             =   'TransactionId';
    public const GET_STATUS_TRANSACTION_TYPE    = 'getStatus';
    public const KEY_PLUGIN_TYPE    = 'pluginType';
    public const VALUE_PLUGIN_TYPE  = 'MAGTO';
}
