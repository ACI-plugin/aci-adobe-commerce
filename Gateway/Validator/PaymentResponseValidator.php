<?php

namespace Aci\Payment\Gateway\Validator;

use TryzensIgnite\Common\Helper\Constants;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Aci\Payment\Helper\Utilities;

/**
 * Payment Rest API Response Validator
 */
class PaymentResponseValidator extends AbstractValidator
{
    /**
     * @var string
     */
    private string $transactionType;

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @param Utilities $utilities
     * @param ResultInterfaceFactory $resultFactory
     * @param string $transactionType
     */
    public function __construct(
        Utilities $utilities,
        ResultInterfaceFactory $resultFactory,
        string $transactionType = ''
    ) {
        parent::__construct($resultFactory);
        $this->transactionType = $transactionType;
        $this->utilities = $utilities;
    }

    /**
     * Validates Capture/Refund/Cancel transaction response
     *
     * @param array<mixed> $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = SubjectReader::readResponse($validationSubject);
        $responseCode = '';
        if ($response && isset($response['result'])) {
            $responseResult = $response['result'];
            $responseCode = $responseResult['code'];
        }
        return match ($this->transactionType) {
            Constants::SERVICE_VOID,
            Constants::SERVICE_CAPTURE,
            Constants::SERVICE_REFUND =>  $this->validateResponse($responseCode),
            default =>
            $this->createResult(
                false,
                [
                    __(
                        'Something went wrong while processing the  %1 API request',
                        $this->transactionType
                    )
                ]
            )
        };
    }

    /**
     * Validate Responses
     *
     * @param string $status
     * @return ResultInterface
     */
    public function validateResponse(string $status): ResultInterface
    {
        if ($this->utilities->isSuccessResponse($status)) {
            return $this->createResult(true);
        }
        return $this->createResult(false);
    }
}
