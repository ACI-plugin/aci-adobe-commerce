<?php

namespace Aci\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aci\Payment\Gateway\Config\AciCcPaymentConfig;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Aci\Payment\Helper\Data;

/**
 * Class ConfigProvider - Configuration class for card payment
 */
class AciCcConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'aci_cc';

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
     * @param AciCcPaymentConfig $config
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     */
    public function __construct(
        AciCcPaymentConfig         $config,
        StoreManagerInterface $storeManager,
        Data $dataHelper
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
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
        return [
            'payment' => [
                self::CODE => [
                    'methodCode' => self::CODE,
                    'config' => [
                        'active' => (bool)$this->config->getValue('active'),
                        'initPaymentUrl' => $store->getUrl(
                            'acipayment/payment/ccinitpayment',
                            ['_secure' => $store->isCurrentlySecure()]
                        ),
                        'scriptSrc' => $this->dataHelper->getPrepareCheckoutUrl(),
                        'logos' => $this->config->getLogos(),
                        'supportedCardTypes' => $this->config->getSupportedCardTypes(),
                        'savePaymentOption' => (bool)$this->config->getValue('save_payment_option')
                    ]
                ]
            ]
        ];
    }
}
