<?php
namespace Aci\Payment\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Aci\Payment\Model\ImageHandler;

/**
 * Config Model for APM Payment Field
 */
class ApmSettings extends ConfigValue
{
    /**
     * @var ImageHandler
     */
    protected ImageHandler $imageHandler;

    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * ShippingMethods constructor
     *
     * @param SerializerInterface $serializer
     * @param ImageHandler $imageHandler
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array<mixed> $data
     */
    public function __construct(
        SerializerInterface $serializer,
        ImageHandler $imageHandler,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->imageHandler = $imageHandler;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return ApmSettings
     * @throws FileSystemException
     */
    public function beforeSave(): ApmSettings
    {
        /** @var array<mixed> $value */
        $value = $this->getValue();
        unset($value['__empty']);

        if ($value) {
            $temp = 0;
            foreach ($value as $key => $apmSettingData) {
                if (isset($apmSettingData['apm_icon']) && $apmSettingData['apm_icon']) {
                    $temp++;
                    if ($apmSettingData['apm_icon']['name']) {
                        $uploadLogo = $this->imageHandler->saveImageToMediaFolder($apmSettingData['apm_icon']);
                        if (isset($uploadLogo['error']) && $uploadLogo['error']) {
                            throw new LocalizedException(
                                __('Invalid file size/type.')
                            );
                        }
                        if (isset($uploadLogo['file']) && $uploadLogo['file']) {
                            $value[$temp]['apm_icon'] = $uploadLogo['file'];
                        }
                    }
                    unset($value[$key]);

                    if ((isset($value[$temp]['apm_icon_delete']) && $value[$temp]['apm_icon_delete'])
                        && (isset($value[$temp]['apm_icon']) && $value[$temp]['apm_icon'])) {
                        $this->imageHandler->deleteImageFromMediaFolder($value[$temp]['apm_icon_delete']);
                    } elseif ((isset($value[$temp]['apm_icon_delete']) && $value[$temp]['apm_icon_delete'])
                        && !(isset($value[$temp]['apm_icon']) && $value[$temp]['apm_icon'])) {
                        $value[$temp]['apm_icon'] = $value[$temp]['apm_icon_delete'];
                    } else {
                        $value[$temp]['apm_icon'] = isset($value[$temp]['apm_icon']) ? $value[$temp]['apm_icon'] : "";
                    }

                    unset($value[$temp]['apm_icon_delete']);
                }
            }
        }

        $encodedValue = $this->serializer->serialize($value);
        $this->setValue((string)$encodedValue);

        return $this;
    }
}
