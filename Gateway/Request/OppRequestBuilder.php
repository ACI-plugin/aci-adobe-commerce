<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Builds OPP Request info
 */
class OppRequestBuilder implements BuilderInterface
{
    /**
     * Builds OPP info request
     *
     * @param array<string> $buildSubject
     * @return array<string>
     */
    public function build(array $buildSubject): array
    {
        return [
            Constants::KEY_PLUGIN_TYPE => Constants::VALUE_PLUGIN_TYPE
        ];
    }
}
