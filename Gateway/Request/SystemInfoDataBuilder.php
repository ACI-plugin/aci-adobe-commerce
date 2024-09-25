<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;
use TryzensIgnite\Common\Model\Utilities\Metadata;
use Aci\Payment\Helper\Utilities;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds system info
 */
class SystemInfoDataBuilder implements BuilderInterface
{
    /**
     * @var Metadata
     */
    private Metadata $data;

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @param Metadata $data
     * @param Utilities $utilities
     */
    public function __construct(
        Metadata $data,
        Utilities $utilities
    ) {
        $this->data = $data;
        $this->utilities = $utilities;
    }

    /**
     * Builds system info request
     *
     * @param array<string> $buildSubject
     * @return array<string>
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function build(array $buildSubject): array
    {
        $moduleName = Constants::VALUE_MODULE_NAME;
        $customParameters = [
            Constants::KEY_SYSTEM_NAME => Constants::VALUE_PLATFORM_NAME,
            Constants::KEY_SYSTEM_VERSION =>
                $this->data->getMagentoEdition().'-'.$this->data->getMagentoVersion(),
        Constants::KEY_MODULE_NAME => $moduleName,
        Constants::KEY_MODULE_VERSION => $this->data->getMagentoModuleVersion($moduleName)
        ];

        return $this->utilities->formatCustomParametersArray($customParameters);
    }
}
