<?php

namespace Aci\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aci\Payment\Gateway\Config\AciApmPaymentConfig;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Aci\Payment\Helper\Data;

/**
 * Class AciApmConfigProvider - Configuration class for APM
 */
class AciApmConfigProvider implements ConfigProviderInterface
{
    public const CODE = 'aci_apm';

    /**
     * @var AciApmPaymentConfig
     */
    private AciApmPaymentConfig $apmConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Data
     */
    private Data $dataHelper;

    /**
     * @param AciApmPaymentConfig $apmConfig
     * @param StoreManagerInterface $storeManager
     * @param Data $dataHelper
     */
    public function __construct(
        AciApmPaymentConfig $apmConfig,
        StoreManagerInterface $storeManager,
        Data $dataHelper
    ) {
        $this->apmConfig = $apmConfig;
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
                        'active' => (bool)$this->apmConfig->getValue('active'),
                        'initPaymentUrl' => $store->getUrl(
                            'acipayment/payment/apminitpayment',
                            ['_secure' => $store->isCurrentlySecure()]
                        ),
                        'scriptSrc' => $this->dataHelper->getPrepareCheckoutUrl(),
                    ],
                    'paymentMethods' => $this->apmConfig->getApmAdditionalSettings()
                ]
            ]
        ];
    }
}
