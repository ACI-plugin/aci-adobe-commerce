<?php

namespace Aci\Payment\Gateway\Config;

use Aci\Payment\Model\ImageHandler;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Aci\Payment\Model\Ui\AciApmConfigProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use TryzensIgnite\Base\Gateway\Config\Config as PaymentConfig;

/**
 * Payment configuration class
 */
class AciApmPaymentConfig extends PaymentConfig
{
    public const KEY_APM_ADDITIONAL_SETTINGS = 'aci_apm_settings';

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Serializer $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Serializer  $serializer,
        ScopeConfigInterface $scopeConfig,
        string               $methodCode = AciApmConfigProvider::CODE,
        string               $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct(
            $scopeConfig,
            $serializer,
            $storeManager,
            $methodCode,
            $pathPattern
        );
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
    }

    /**
     * Get APM additional Settings
     *
     * @return array<mixed>
     * @throws NoSuchEntityException
     */
    public function getApmAdditionalSettings(): array
    {
        $configData = $this->getValue(
            self::KEY_APM_ADDITIONAL_SETTINGS
        );

        $paymentData = [];
        if ($configData) {
            $paymentData = $this->makeIndividualPaymentSettings($configData);
        }
        return $paymentData;
    }

    /**
     * Make APM settings to individual payment settings
     *
     * @param string $configData
     * @return array<mixed>
     * @throws NoSuchEntityException
     */
    private function makeIndividualPaymentSettings(string $configData): array
    {
        $configDataArray = (array)$this->serializer->unserialize($configData);
        $paymentData = [];
        foreach ($configDataArray as $key => $data) {
            if (isset($data['active']) &&
                $data['active'] && $data['payment_key'] && $data['title']) {
                $paymentData[$data['payment_key']] = [
                    'name' => $data['payment_key'],
                    'active' => $data['active'],
                    'title' => $data['title'],
                    'payment_action' => $data['payment_action'],
                    'icon' => isset($data['apm_icon']) && $data['apm_icon']
                        ? $this->getMediaUrl($data['apm_icon']) : ''
                ];
            }
        }
        return $paymentData;
    }

    /**
     * Get file url
     *
     * @param string $file
     * @return string
     * @throws NoSuchEntityException
     */
    public function getMediaUrl(string $file): string
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');

        /** @var Store $storeManager */
        $storeManager = $this->storeManager->getStore();
        return $storeManager->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . ImageHandler::FILE_DIR . '/' . $file;
    }

    /**
     * Get Payment Action for APM payment methods
     *
     * @param string $brandName
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentAction(string $brandName): string
    {
        $settings = $this->getApmAdditionalSettings();

        if (isset($settings[$brandName])) {
            return $settings[$brandName]['payment_action'] ?? '';
        }

        return '';
    }
}
