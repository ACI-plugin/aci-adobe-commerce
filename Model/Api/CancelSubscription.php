<?php

namespace Aci\Payment\Model\Api;

use Aci\Payment\Helper\Constants;

/**
 * Model class for CancelSubscription API
 */
class CancelSubscription extends \TryzensIgnite\Subscription\Model\Api\CancelSubscription
{
    /**
     * Get additional Uri params for CancelSubscription API
     *
     * @return array|mixed[]
     */
    public function getAdditionalUriParams(): array
    {
        return [
            /** @phpstan-ignore-next-line */
            Constants::KEY_ACI_PAYMENT_ENTITY_ID => $this->paymentConfig->getEntityId(),
            Constants::KEY_SOURCE => Constants::VALUE_SOURCE
        ];
    }

    /**
     * Get CancelSubscription Api URL
     *
     * @return string
     */
    public function getCancelSubscriptionApiUrl(): string
    {
        /** @phpstan-ignore-next-line */
        return $this->paymentConfig->getApiEndPoint() . Constants::END_POINT_SUBSCRIPTION;
    }
}
