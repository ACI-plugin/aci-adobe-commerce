<?php

namespace Aci\Payment\Gateway\Http;

interface WebhookTransferFactoryInterface
{
    /**
     * Build gateway transfer object
     *
     * @param array<mixed> $request
     * @return mixed
     */
    public function create(array $request): mixed;
}
