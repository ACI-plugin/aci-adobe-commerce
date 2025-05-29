<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Helper\Constants;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * TransferFactory for AUTH transaction commands
 */
class AuthTransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private TransferBuilder $transferBuilder;

    /**
     * AuthorizeCaptureTransferFactory constructor.
     *
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array<mixed> $request
     * @return TransferInterface
     */
    public function create(array $request):TransferInterface
    {
        return $this->transferBuilder
            ->setBody($request)
            ->setMethod(Constants::API_METHOD_POST)
            ->setHeaders(
                [
                    Constants::GET_TRANSACTION_ID => $request[Constants::GET_TRANSACTION_ID]
                ]
            )
            ->build();
    }
}
