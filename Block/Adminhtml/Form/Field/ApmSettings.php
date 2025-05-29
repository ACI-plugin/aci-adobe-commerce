<?php
namespace Aci\Payment\Block\Adminhtml\Form\Field;

use Aci\Payment\Block\Widget\ApmSettings as ApmSettingsWidget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\Serialize\SerializerInterface;
use TryzensIgnite\Base\Model\ImageHandler;

/**
 * Frontend template for APM Settings field
 */
class ApmSettings extends AbstractElement
{
    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    /**
     * @var ImageHandler
     */
    protected ImageHandler $imageHandler;

    /**
     * @param SerializerInterface $serializer
     * @param ImageHandler $imageHandler
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array<mixed> $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(
        SerializerInterface $serializer,
        ImageHandler $imageHandler,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
        $this->serializer = $serializer;
        $this->imageHandler = $imageHandler;
    }

    /**
     * Custom template for APM settings field
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getElementHtml(): string
    {
        /** @var ApmSettingsWidget $apmSettings  */
        /** @phpstan-ignore-next-line */
        $apmSettings = $this->getForm()->getParent()->getLayout()->createBlock(
            ApmSettingsWidget::class
        );

        /** @phpstan-ignore-next-line */
        $existingData = $this->getValue();
        if (!$existingData) {
            $existingData = '';
        } else {
            $existingData = (array)$this->serializer->unserialize($existingData);
            foreach ($existingData as $key => $data) {
                if (isset($data['apm_icon']) && $data['apm_icon']) {
                    $existingData[$key]['apm_icon_url'] = $this->imageHandler->getMediaUrl($data['apm_icon']);
                }
            }
        }

        $defaultData = [
            'active' => 0,
            'title' => '',
            'payment_action' => 'authorize',
            'payment_key' => '',
            'apm_icon' => ''
        ];
        $data = [
            'settings' => $this->serializer->serialize($existingData),
            'default' => $this->serializer->serialize($defaultData),
            'htmlId' => $this->getHtmlId()
        ];

        return $apmSettings->setData($data)->toHtml();
    }
}
