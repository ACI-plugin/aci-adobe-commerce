<?php
declare(strict_types=1);

namespace Aci\Payment\Controller\Process;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use TryzensIgnite\Notification\Controller\Process\Notification as IgniteNotification;
use TryzensIgnite\Notification\Helper\Constants as IgniteConstants;
use TryzensIgnite\Notification\Model\NotificationEvents;
use Aci\Payment\Logger\AciLogger;
use Aci\Payment\Helper\Constants;

/**
 * Notification - Process notification
 */
class Notification extends IgniteNotification
{
    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var AciLogger
     */
    private AciLogger $logger;

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $paymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param Serializer $serializer
     * @param Request $request
     * @param NotificationEvents $notificationEvents
     * @param Http $response
     * @param AciLogger $logger
     * @param AciGenericPaymentConfig $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Serializer                      $serializer,
        Request                         $request,
        NotificationEvents              $notificationEvents,
        Http                            $response,
        AciLogger                       $logger,
        AciGenericPaymentConfig         $paymentConfig,
        ScopeConfigInterface            $scopeConfig,
    ) {
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $serializer,
            $request,
            $notificationEvents,
            $response,
            $scopeConfig,
        );
    }

    /**
     * Decrypt the notification received.
     *
     * @param string $body
     * @return false|string
     */
    public function decryptNotification(string $body): false|string
    {
        $headers = apache_request_headers();
        $keyFromConfiguration = $this->paymentConfig->getWebhookEncryptionSecret();
        $ivFromHttpHeader = $headers['X-Initialization-Vector'] ?? null;
        $authTagFromHttpHeader = $headers['X-Authentication-Tag'] ?? null;
        $body = $this->serializer->unSerialize($body);
        $httpBody = $body['encryptedBody'] ?? null;
        if (!$keyFromConfiguration || !$ivFromHttpHeader || !$authTagFromHttpHeader || !$httpBody) {
            $this->logger->error(__('Required value missing from received notification.
            Key from Configuration- '. $keyFromConfiguration.
                'IV from HTTP Header- '.$ivFromHttpHeader.
                'Auth tag from HTTP Header- '.$authTagFromHttpHeader));
            return false;
        }
        $key = (string)hex2bin($keyFromConfiguration);
        $iv = (string)hex2bin($ivFromHttpHeader);
        $authTag = (string)hex2bin($authTagFromHttpHeader);
        $cipherText = (string)hex2bin($httpBody);
        try {
            $result = openssl_decrypt(
                $cipherText,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $authTag
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $result = false;
        }
        return $result;
    }

    /**
     * Check if notification is test notification.
     *
     * @param array<mixed> $paramsArray
     * @return bool
     */
    public function isTestNotification(array $paramsArray): bool
    {
        if (isset($paramsArray['action']) && $paramsArray['action'] == 'webhook activation') {
            return true;
        }
        return false;
    }

    /**
     * If the webhook response belongs to schedule creation transaction
     *
     * @param array<mixed> $paramsArray
     * @return bool
     */
    public function isScheduleCreationTransaction(array $paramsArray): bool
    {
        if ($this->scopeConfig->getValue(IgniteConstants::KEY_SUBSCRIPTION_ENABLED)
            && isset($paramsArray['standingInstruction']['recurringType'])) {
            if ($paramsArray['standingInstruction']['recurringType'] ==
                Constants::STANDING_INSTRUCTION_RECURRING_TYPE) {
                return true;
            }
        }
        return false;
    }
}
