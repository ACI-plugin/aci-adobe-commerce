<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Constants;

/**
 * Class IntegrityDataBuilder
 * Adds integrity parameter to init transaction
 */
class IntegrityDataBuilder implements BuilderInterface
{

    /**
     * Adds integrity parameter to init transaction
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        return [
            Constants::KEY_INTEGRITY => "true"
        ];
    }
}
