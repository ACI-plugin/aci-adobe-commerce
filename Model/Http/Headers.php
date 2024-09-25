<?php

namespace Aci\Payment\Model\Http;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use TryzensIgnite\Common\Gateway\Config\Config as IgniteCommonConfig;

/**
 * Create headers for API request
 */
class Headers extends \TryzensIgnite\Common\Model\Http\Headers
{
    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $aciGenericPaymentConfig;

    /**
     * @param IgniteCommonConfig $igniteCommonConfig
     * @param AciGenericPaymentConfig $aciGenericPaymentConfig
     */
    public function __construct(
        IgniteCommonConfig $igniteCommonConfig,
        AciGenericPaymentConfig $aciGenericPaymentConfig
    ) {
        parent::__construct($igniteCommonConfig);
        $this->aciGenericPaymentConfig = $aciGenericPaymentConfig;
    }

    /**
     * Get Api Key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return (string)$this->aciGenericPaymentConfig->getApiKey();
    }
}
