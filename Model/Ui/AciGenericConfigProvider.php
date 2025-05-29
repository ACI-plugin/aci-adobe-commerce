<?php

namespace Aci\Payment\Model\Ui;

use TryzensIgnite\Base\Gateway\Config\Config as BaseGatewayConfig;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Magento\Framework\UrlInterface;
use TryzensIgnite\Base\Model\Ui\BaseConfigProvider;

/**
 * Class AciGenericConfigProvider - generic configuration class
 */
class AciGenericConfigProvider extends BaseConfigProvider
{

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $paymentConfig;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param AciGenericPaymentConfig $paymentConfig
     * @param UrlInterface $urlBuilder
     * @param BaseGatewayConfig $configProvider
     */
    public function __construct(
        AciGenericPaymentConfig         $paymentConfig,
        UrlInterface         $urlBuilder,
        BaseGatewayConfig $configProvider
    ) {
        parent::__construct(
            $configProvider
        );
        $this->paymentConfig = $paymentConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'shopperResultURL' => $this->getShopperResultURL(),
                    'customScript' => $this->paymentConfig->getCustomScript(),
                ]
            ]
        ];
    }

    /**
     * Create Shopper Result URL including base url
     *
     * @return string
     */
    public function getShopperResultURL(): string
    {
        $baseUrl = $this->urlBuilder->getBaseUrl();
        $controllerPath = 'acipayment/process/response';
        return $baseUrl . $controllerPath;
    }
}
