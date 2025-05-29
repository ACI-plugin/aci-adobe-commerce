<?php

namespace Aci\Payment\Model\Ui;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aci\Payment\Gateway\Config\AciCcPaymentConfig;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Aci\Payment\Helper\Data;
use TryzensIgnite\Onsite\Model\Ui\CcConfigProvider as IgniteCcConfigProvider;
use TryzensIgnite\Onsite\Gateway\Config\CcConfig;

/**
 * Class ConfigProvider - Configuration class for card payment
 */
class AciCcConfigProvider extends IgniteCcConfigProvider
{
    /**
     * @var AciCcPaymentConfig
     */
    private AciCcPaymentConfig $config;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Data
     */
    private Data $dataHelper;

    /**
     * @param CcConfig $ccConfig
     * @param StoreManagerInterface $storeManager
     * @param AciCcPaymentConfig $config
     * @param Data $dataHelper
     */
    public function __construct(
        CcConfig $ccConfig,
        StoreManagerInterface $storeManager,
        AciCcPaymentConfig         $config,
        Data $dataHelper
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        parent::__construct(
            $ccConfig,
            $storeManager
        );
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array<mixed>
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getConfig(): array
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $ccConfig = parent::getConfig();
        $aciConfig = [
            'payment' => [
                self::CODE => [
                    'config' => [
                        'initPaymentUrl' => $store->getUrl(
                            'acipayment/payment/ccinitpayment',
                            ['_secure' => $store->isCurrentlySecure()]
                        ),
                        'scriptSrc' => $this->dataHelper->getPrepareCheckoutUrl(),
                        'logos' => $this->config->getLogos(),
                        'supportedCardTypes' => $this->config->getSupportedCardTypes()
                    ]
                ]
            ]
        ];
        return array_merge($ccConfig, $aciConfig);
    }
}
