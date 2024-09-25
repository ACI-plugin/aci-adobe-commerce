<?php
namespace Aci\Payment\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Class WebhookUrl - Returns value to pre-populate webhook configuration field with URL
 */
class WebhookUrl extends Value
{
    /**
     * @var UrlInterface
     */
    protected UrlInterface $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array<mixed> $data
     */
    public function __construct(
        UrlInterface         $urlBuilder,
        Context              $context,
        Registry             $registry,
        ScopeConfigInterface $config,
        TypeListInterface    $cacheTypeList,
        AbstractResource     $resource = null,
        AbstractDb           $resourceCollection = null,
        array                $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    protected function _afterLoad(): static
    {
        $this->setValue($this->getWebhookUrl());
        return parent::_afterLoad();
    }

    /**
     * Create Webhook URL including base url
     *
     * @return string
     */
    public function getWebhookUrl(): string
    {
        $baseUrl = $this->urlBuilder->getBaseUrl();
        $controllerPath = 'acipayment/process/notification';
        return $baseUrl . $controllerPath;
    }
}
