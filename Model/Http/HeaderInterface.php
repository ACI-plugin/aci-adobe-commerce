<?php
namespace Aci\Payment\Model\Http;

/**
 * @api
 * @since 100.0.2
 */
interface HeaderInterface
{
    /**
     * Get API Key
     *
     * @return string
     */
    public function getApiKey(): string;
}
