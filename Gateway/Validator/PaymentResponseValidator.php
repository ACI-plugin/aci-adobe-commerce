<?php

namespace Aci\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Aci\Payment\Helper\Utilities;
use TryzensIgnite\Base\Gateway\Validator\PaymentResponseValidator as IgnitePaymentResponseValidator;

/**
 * Payment Rest API Response Validator
 */
class PaymentResponseValidator extends IgnitePaymentResponseValidator
{
    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @param Utilities $utilities
     * @param ResultInterfaceFactory $resultFactory
     * @param string $endPoint
     */
    public function __construct(
        Utilities $utilities,
        ResultInterfaceFactory $resultFactory,
        string $endPoint = ''
    ) {
        parent::__construct($resultFactory, $endPoint);
        $this->utilities = $utilities;
    }

    /**
     * Validates BackOffice Transaction Response
     *
     * @param array<mixed> $response
     * @return ResultInterface
     */
    public function validateBackofficeTransaction(array $response): ResultInterface
    {
        $responseCode = '';
        if ($response && isset($response['result'])) {
            $responseResult = $response['result'];
            $responseCode = $responseResult['code'];
        }

        if ($this->utilities->isSuccessResponse($responseCode)) {
            return $this->createResult(true);
        }
        return $this->createResult(false);
    }
}
