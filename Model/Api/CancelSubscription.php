<?php

namespace Aci\Payment\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Aci\Payment\Model\Http\Request;
use Aci\Payment\Helper\Constants;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Calls CancelSubscription API
 */
class CancelSubscription implements CancelSubscriptionInterface
{
    /**
     * @var Request
     */
    public Request $request;

    /**
     * @var ConfigInterface
     */
    public ConfigInterface $paymentConfig;

    /**
     * @param Request $request
     * @param ConfigInterface $paymentConfig
     */
    public function __construct(
        Request $request,
        ConfigInterface $paymentConfig
    ) {
        $this->request = $request;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Calls CancelSubscription API
     *
     * @param string $subscriptionId
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function cancelSubscription(string $subscriptionId): array
    {
        $cancelSubscriptionUrl = $this->getCancelSubscriptionApiUrl();
        $cancelSubscriptionUrl = $cancelSubscriptionUrl . '/' . $subscriptionId;

        $additionalUriParams = $this->getAdditionalUriParams();
        if ($additionalUriParams) {
            $additionalUriParams = http_build_query($additionalUriParams);
            $cancelSubscriptionUrl = $cancelSubscriptionUrl . '?' . $additionalUriParams;
        }
        return $this->request->sendRequest(Constants::API_METHOD_DELETE, $cancelSubscriptionUrl);
    }

    /**
     * Get CancelSubscription Api URL
     *
     * @return string
     */
    public function getCancelSubscriptionApiUrl(): string
    {
        /** @phpstan-ignore-next-line */
        return $this->paymentConfig->getApiEndPoint() . Constants::END_POINT_CANCEL_SUBSCRIPTION;
    }

    /**
     * Get additional Uri params for CancelSubscription API
     *
     * @return array|mixed[]
     */
    public function getAdditionalUriParams(): array
    {
        return [
            /** @phpstan-ignore-next-line */
            Constants::KEY_ACI_PAYMENT_ENTITY_ID => $this->paymentConfig->getEntityId()
        ];
    }
}
