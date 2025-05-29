<?php
namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;
use Magento\Payment\Gateway\Request\BuilderInterface;
use TryzensIgnite\Base\Model\Utilities\Config as UtilityConfig;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;

/**
 * Builds base request data for ACI.
 */
class BaseRequestDataBuilder implements BuilderInterface
{
    /**
     * @var UtilityConfig
     */
    private UtilityConfig $config;

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $paymentConfig;

    /**
     * BaseRequestDataBuilder constructor.
     *
     * @param UtilityConfig $config
     * @param AciGenericPaymentConfig $paymentConfig
     */
    public function __construct(
        UtilityConfig $config,
        AciGenericPaymentConfig $paymentConfig
    ) {
        $this->config = $config;
        $this->paymentConfig = $paymentConfig;
    }
    /**
     * Builds ENV request.
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject):array
    {
        if (!isset($buildSubject['quote'])) {
            throw new \InvalidArgumentException('Quote data object should be provided');
        }
        $requestData = [
            Constants::KEY_LOCALE => $this->config->getConfig(AciGenericPaymentConfig::XML_PATH_DEFAULT_LOCALE),
        ];
        $testMode = $this->paymentConfig->getTestMode();
        if ($testMode) {
            $requestData[Constants::KEY_TEST_MODE] = $testMode;
        }
        return $requestData;
    }
}
