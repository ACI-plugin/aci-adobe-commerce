<?php
namespace Aci\Payment\Model\Api;

/**
 * @api
 * @since 100.0.2
 */
interface CancelSubscriptionInterface
{
    /**
     * Get the additional uri params for cancel subscription API
     *
     * @return array<mixed>
     */
    public function getAdditionalUriParams(): array;
}
