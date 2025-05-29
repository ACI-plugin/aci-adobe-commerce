<?php

namespace Aci\Payment\Model\Http;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use TryzensIgnite\Base\Model\Utilities\Config as UtilityConfig;

/**
 * Create headers for API request
 */
class Headers implements HeaderInterface
{
    /**
     * @var UtilityConfig
     */
    public UtilityConfig $config;

    /**
     * @var AciGenericPaymentConfig
     */
    protected AciGenericPaymentConfig $aciGenericPaymentConfig;

    /**
     * @param UtilityConfig $config
     * @param AciGenericPaymentConfig $aciGenericPaymentConfig
     */
    public function __construct(
        UtilityConfig $config,
        AciGenericPaymentConfig $aciGenericPaymentConfig
    ) {
        $this->config = $config;
        $this->aciGenericPaymentConfig = $aciGenericPaymentConfig;
    }

    /**
     * Create headers for API request
     *
     * @return string[]
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type'  =>  'application/json',
            'Authorization' =>  'Bearer ' . $this->getApiKey()
        ];
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
