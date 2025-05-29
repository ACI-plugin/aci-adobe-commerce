<?php
namespace Aci\Payment\Model\Api;

use Aci\Payment\Helper\Constants;
use TryzensIgnite\Base\Model\Api\ResponseManager as IgniteResponseManager;
use Aci\Payment\Helper\Utilities;
use TryzensIgnite\Base\Model\Utilities\Properties;

/**
 *
 * Manage api response data
 */
class AciPaymentResponseManager extends IgniteResponseManager
{
    /**
     * @var Utilities
     */
    public Utilities $utilities;

    /**
     * @param Utilities $utilities
     * @param Properties $properties
     */
    public function __construct(
        Utilities $utilities,
        Properties $properties
    ) {
        $this->utilities = $utilities;
        parent::__construct($properties);
    }
    /**
     * Check the capture mode.
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isCaptureMode(array $response): bool
    {
        return $response[Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE] == Constants::PAYMENT_TYPE_SALE;
    }

    /**
     * Check if transaction is AUTH
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isAuthOnly(array $response): bool
    {
        return $response[Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE] == Constants::PAYMENT_TYPE_AUTH;
    }

    /**
     * Check if token information is available
     *
     * @param array<mixed> $response
     * @return bool
     */
    public function isTokenAvailable(array $response): bool
    {
        if (!$response) {
            return false;
        }
        $requestToken = isset($response['registrationId']) && !isset($response['recurringType']);
        $tokenInformation = $response['card'] ?? null;
        return $requestToken
            && is_array($tokenInformation)
            && $tokenInformation['bin']
            && $tokenInformation['last4Digits']
            && $tokenInformation['holder']
            && $tokenInformation['expiryMonth']
            && $tokenInformation['expiryYear'];
    }

    /**
     * Check if the response is success or not
     *
     * @param string $responseCode
     * @return bool
     */
    public function isSuccessResponse(string $responseCode): bool
    {
        return (bool)$this->utilities->isSuccessResponse($responseCode);
    }

    /**
     * Get Order IncrementId from response.
     *
     * @param array<mixed> $response
     * @return string|null
     */
    public function getOrderIncrementIdFromResponse(array $response): ?string
    {
        return $response[Constants::KEY_ACI_PAYMENT_MERCHANT_TRANSACTION_ID] ??
            $response[Constants::INVOICE_NUMBER];
    }

    /**
     * Get Payment Method Type from response
     *
     * @param array<mixed> $response
     * @return string|null
     */
    public function getPaymentMethodType(array $response): ?string
    {
        if (isset($response['payload']) && is_array($response['payload'])) {
            if (isset($response['payload']['paymentMethod'])) {
                return $response['payload']['paymentMethod'];
            }
        }
        return self::PAYMENT_TYPE_CARD;
    }

    /**
     * Get payment ID
     *
     * @param array<mixed> $response
     * @return string
     */
    public function getPaymentId(array $response): string
    {
        return $response['id'] ?? '';
    }
}
