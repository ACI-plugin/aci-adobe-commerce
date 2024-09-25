<?php

namespace Aci\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Magento\Framework\UrlInterface;

/**
 * Class AciGenericConfigProvider - generic configuration class
 */
class AciGenericConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'aci_abstract';

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
     */
    public function __construct(
        AciGenericPaymentConfig         $paymentConfig,
        UrlInterface         $urlBuilder
    ) {
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
                'aci' => [
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
