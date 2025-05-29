<?php

namespace Aci\Payment\Model\Utilities;

use TryzensIgnite\Notification\Model\Utilities\Properties as IgniteBaseProperties;

/**
 * Holds a collection of constant values used throughout the application.
 */
class Properties extends IgniteBaseProperties
{
    /**
     * @var string[]
     */
    protected array $pspOrderStatus = [
        'authorized' => 'authorize',
        'captured' => 'capture',
        'refunded' => 'refund',
        'void' => 'void',
        'pradeep' => 'pradeep',
    ];
}
