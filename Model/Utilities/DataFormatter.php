<?php

namespace Aci\Payment\Model\Utilities;

use TryzensIgnite\Base\Model\Utilities\DataFormatter as BaseDataFormatter;

/**
 * Class Data Formatter - Formats Data
 */
class DataFormatter extends BaseDataFormatter
{
    /**
     * Method to override ignite formatted price
     *
     * @param float $price
     * @return float
     */
    public function getFormattedPrice(float $price): float
    {
        return $price;
    }
}
