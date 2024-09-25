<?php
namespace Aci\Payment\Model\Api;

use Aci\Payment\Helper\Constants;
use TryzensIgnite\Common\Model\Api\ResponseManager as IgniteResponseManager;
use Aci\Payment\Helper\Utilities;

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
     */
    public function __construct(
        Utilities $utilities
    ) {
        $this->utilities = $utilities;
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
        $requestToken = is_array($response)
        && isset($response['registrationId']) && !isset($response['recurringType']);
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
}
