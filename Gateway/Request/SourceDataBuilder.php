<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds source info
 */
class SourceDataBuilder implements BuilderInterface
{

    /**
     * Builds source info request
     *
     * @param array<string> $buildSubject
     * @return array<string>
     */
    public function build(array $buildSubject): array
    {
        return [
            Constants::KEY_SOURCE => Constants::VALUE_SOURCE
        ];
    }
}
