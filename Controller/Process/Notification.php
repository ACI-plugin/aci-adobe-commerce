<?php
declare(strict_types=1);

namespace Aci\Payment\Controller\Process;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use TryzensIgnite\Base\Helper\Constants;
use TryzensIgnite\Notification\Controller\Process\Notification as IgniteNotification;
use Aci\Payment\Logger\AciLogger;
use TryzensIgnite\Notification\Model\NotificationManager;

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
     * @var Request
     */
    private Request $request;

    /**
     * @var NotificationManager
     */
    private NotificationManager $notificationManager;

    /**
     * @var Http
     */
    private Http $response;

    /**
     * @param Serializer $serializer
     * @param Request $request
     * @param Http $response
     * @param AciLogger $logger
     * @param AciGenericPaymentConfig $paymentConfig
     * @param NotificationManager $notificationManager
     */
    public function __construct(
        Serializer                      $serializer,
        Request                         $request,
        Http                            $response,
        AciLogger                       $logger,
        AciGenericPaymentConfig         $paymentConfig,
        NotificationManager             $notificationManager,
    ) {
        $this->logger = $logger;
        $this->paymentConfig = $paymentConfig;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->notificationManager = $notificationManager;
        $this->response = $response;
        parent::__construct(
            $serializer,
            $request,
            $notificationManager,
            $response,
        );
    }

    /**
     * Process notification response from PSP
     *
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Http
    {
        $response = null;
        if ($this->request->getMethod() == Constants::API_METHOD_GET) {
            return $this->response->setStatusCode(200);
        }
        $body = $this->request->getContent();
        if (!$body) {
            return $this->response->setStatusCode(500);
        }
        $result = $this->decryptNotification($body);
        if (!$result) {
            return $this->response->setStatusCode(200);
        }
        $notificationContent = $this->serializer->unSerialize($result);
        if (is_array($notificationContent) && $notificationContent) {
            $testNotification = $this->isTestNotification($notificationContent);
            if ($testNotification) {
                return $this->response->setStatusCode(200);
            }
            $response = $this->notificationManager->saveNotification($notificationContent);
        }
        if ($response) {
            return $this->response->setStatusCode(200);
        } else {
            return $this->response->setStatusCode(500);
        }
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
            $this->logger->error(
                __('Required value missing from received notification.'),
                [
                    'IV from HTTP Header' => $ivFromHttpHeader,
                    'Auth tag from HTTP Header' => $authTagFromHttpHeader
                ]
            );
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
}
