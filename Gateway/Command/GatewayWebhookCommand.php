<?php
namespace Aci\Payment\Gateway\Command;

use Aci\Payment\Gateway\Http\WebhookTransferFactoryInterface;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\ResultInterface as CommandResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * GatewayCommand for payment
 */
class GatewayWebhookCommand implements CommandInterface
{
    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var HandlerInterface|null
     */
    protected ?HandlerInterface $handler;

    /**
     * @var ValidatorInterface|null
     */
    protected ?ValidatorInterface $validator;

    /**
     * @var ErrorMessageMapperInterface|null
     */
    private ?ErrorMessageMapperInterface $errorMessageMapper;

    /**
     * @var WebhookTransferFactoryInterface
     */
    protected WebhookTransferFactoryInterface $transferFactory;

    /**
     * @var BuilderInterface
     */
    protected BuilderInterface $requestBuilder;

    /**
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param WebhookTransferFactoryInterface $transferFactory
     * @param BuilderInterface $requestBuilder
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     * @param ErrorMessageMapperInterface|null $errorMessageMapper
     */
    public function __construct(
        ClientInterface $client,
        LoggerInterface $logger,
        WebhookTransferFactoryInterface $transferFactory,
        BuilderInterface $requestBuilder,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        ErrorMessageMapperInterface $errorMessageMapper = null
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->transferFactory = $transferFactory;
        $this->requestBuilder = $requestBuilder;
        $this->handler = $handler;
        $this->validator = $validator;
        $this->errorMessageMapper = $errorMessageMapper;
    }

    /**
     * Executes command based on business object
     *
     * @param array<mixed> $commandSubject
     * @return array<mixed>|CommandResultInterface|null
     * @throws ClientException
     * @throws CommandException
     * @throws ConverterException
     */
    public function execute(array $commandSubject): array|CommandResultInterface|null
    {
        $transferO = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        return $this->processRequest($commandSubject, $transferO);
    }

    /**
     * Tries to map error messages from validation result and logs processed message.
     *
     * Throws an exception with mapped message or default error.
     *
     * @param ResultInterface $result
     * @return void
     * @throws CommandException
     */
    protected function processErrors(ResultInterface $result):void
    {
        $messages = [];
        $errorCodeOrMessage = null;
        $errorsSource = array_merge($result->getErrorCodes(), $result->getFailsDescription());

        foreach ($errorsSource as $errorCodeOrMessage) {
            $errorCodeOrMessage = (string) $errorCodeOrMessage;

            // error messages mapper can be not configured if payment method doesn't have custom error messages.
            if ($this->errorMessageMapper !== null) {
                $mapped = (string) $this->errorMessageMapper->getMessage($errorCodeOrMessage);
                if (!empty($mapped)) {
                    $messages[] = $mapped;
                    $errorCodeOrMessage = $mapped;
                }
            }

            $this->logger->critical('Payment Error: ' . $errorCodeOrMessage);
        }

        $exceptionMessage = $errorCodeOrMessage ?: 'Transaction declined. Try again later.';

        throw new CommandException(
            !empty($messages)
                ? __(implode(PHP_EOL, $messages))
                : __($exceptionMessage)
        );
    }

    /**
     * Process request - Gateway command
     *
     * @param array<mixed> $commandSubject
     * @param TransferInterface $transferO
     * @return array<mixed>|CommandResultInterface|null
     * @throws CommandException
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    protected function processRequest(
        array $commandSubject,
        TransferInterface $transferO
    ): array|CommandResultInterface|null {
        $response = $this->client->placeRequest($transferO);

        if ($this->validator !== null) {
            $result = $this->validator->validate(
                array_merge($commandSubject, ['response' => $response])
            );
            if (!$result->isValid()) {
                $this->processErrors($result);
            }
        }

        $this->handler?->handle(
            $commandSubject,
            $response
        );

        return $response;
    }
}
