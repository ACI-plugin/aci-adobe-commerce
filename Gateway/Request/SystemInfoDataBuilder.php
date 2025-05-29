<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;
use TryzensIgnite\Base\Model\Utilities\Metadata;
use Aci\Payment\Helper\Utilities;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use TryzensIgnite\Base\Gateway\Request\SystemInfoDataBuilder as BaseSystemInfoDataBuilder;

/**
 * Builds system info
 */
class SystemInfoDataBuilder extends BaseSystemInfoDataBuilder
{

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @param Metadata $data
     * @param Utilities $utilities
     * @param string $moduleName
     */
    public function __construct(
        Metadata $data,
        Utilities $utilities,
        string $moduleName = ''
    ) {
        $this->utilities = $utilities;

        parent::__construct($data, $moduleName);
    }

    /**
     * Override the properties in parent child class
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->requestKeys = [
            'system_name' => Constants::KEY_SYSTEM_NAME,
            'system_version' => Constants::KEY_SYSTEM_VERSION,
            'middleware_name' => Constants::KEY_MODULE_NAME,
            'middleware_version' => Constants::KEY_MODULE_VERSION
        ];
    }

    /**
     * Build system information data
     *
     * @return array<mixed>
     * @throws ValidatorException
     * @throws FileSystemException
     */
    public function getRequestData(): array
    {
        $systemInfo = parent::getRequestData();
        return $this->utilities->formatCustomParametersArray($systemInfo);
    }
}
