<?php
namespace Aci\Payment\Model;

use TryzensIgnite\Base\Model\ImageHandler as IgniteImageHandler;

/**
 * Handles image upload functionality
 */
class ImageHandler extends IgniteImageHandler
{
    /**
     * @var array|string[]
     */
    protected array $allowedImageExtensions = ['jpg', 'jpeg', 'gif', 'png'];
}
