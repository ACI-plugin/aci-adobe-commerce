<?php
namespace Aci\Payment\Model\Data;

use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory as IgniteDateTimeFactory;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Vault\Model\PaymentTokenRepository;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;
use Magento\Vault\Model\ResourceModel\PaymentToken as PaymentTokenResourceModel;
use Aci\Payment\Helper\Constants;
use Aci\Payment\Helper\Utilities;
use TryzensIgnite\Base\Model\Data\SavedCard as IgniteSavedCard;
use TryzensIgnite\Base\Model\Utilities\Properties;

/**
 *
 * Get saved cards information of the customer
 */
class SavedCard extends IgniteSavedCard
{
    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $tokenCollectionFactory;

    /**
     * @var PaymentTokenRepository
     */
    private PaymentTokenRepository $paymentTokenRepository;

    /**
     * @var PaymentTokenFactoryInterface
     */
    private PaymentTokenFactoryInterface $paymentTokenFactory;

    /**
     * @var PaymentTokenResourceModel
     */
    protected PaymentTokenResourceModel $paymentTokenResourceModel;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @var DateTimeFactory
     */
    private DateTimeFactory $dateTimeFactory;

    /**
     * @param CustomerSession $customerSession
     * @param CollectionFactory $collectionFactory
     * @param PaymentTokenRepository $paymentTokenRepository
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param PaymentTokenResourceModel $paymentTokenResourceModel
     * @param EncryptorInterface $encryptor
     * @param Utilities $utilities
     * @param DateTimeFactory $dateTimeFactory
     * @param Serializer $serializer
     * @param IgniteDateTimeFactory $igniteDateTimeFactory
     * @param Properties $properties
     */
    public function __construct(
        CustomerSession $customerSession,
        CollectionFactory $collectionFactory,
        PaymentTokenRepository $paymentTokenRepository,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        PaymentTokenResourceModel $paymentTokenResourceModel,
        EncryptorInterface $encryptor,
        Utilities $utilities,
        DateTimeFactory $dateTimeFactory,
        Serializer $serializer,
        IgniteDateTimeFactory $igniteDateTimeFactory,
        Properties $properties
    ) {
        $this->customerSession = $customerSession;
        $this->tokenCollectionFactory = $collectionFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenResourceModel = $paymentTokenResourceModel;
        $this->encryptor = $encryptor;
        $this->utilities = $utilities;
        $this->dateTimeFactory = $dateTimeFactory;
        parent::__construct(
            $customerSession,
            $paymentTokenRepository,
            $paymentTokenFactory,
            $paymentTokenResourceModel,
            $encryptor,
            $serializer,
            $igniteDateTimeFactory,
            $properties
        );
    }

    /**
     * Get customer saved cards
     *
     * @param string $methodCode
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function getCustomerSavedCards(string $methodCode):array
    {
        try {
            $customerCards = [];
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId) {
                $collection = $this->tokenCollectionFactory->create();
                $collection
                    ->addFieldToFilter('customer_id', ['eq' => $customerId])
                    ->addFieldToFilter('payment_method_code', ['eq' => $methodCode])
                    ->addFieldToFilter('is_active', ['eq' => 1])
                    ->addFieldToFilter('is_visible', ['eq' => 1])
                    ->addFieldToFilter('expires_at', ['gt' => $this->dateTimeFactory->create(
                        'now',
                        new \DateTimeZone('UTC')
                    )->format('Y-m-d 00:00:00')]);
                if ($collection->getSize()) {
                    foreach ($collection->getItems() as $cardInfo) {
                        $cardDetails = $cardInfo->getData('details');
                        $cardValues = $this->utilities->unserialize($cardDetails);
                        $customerCards[] = [
                            'value' => $cardInfo->getData('gateway_token'),
                            'text' => $cardValues[Constants::MASKED_CARD_NUMBER] ?? ''
                        ];
                    }
                }
                return $customerCards;
            }
            return $customerCards;
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Save token details
     *
     * @param int|null $customerId
     * @param array<mixed> $data
     * @param string $methodCode
     * @return void
     */
    public function savePaymentToken(int|null $customerId, array $data, string $methodCode):void
    {
        if (!$customerId) {
            $customerId = $this->getCustomerId();
        }
        $cardIssuer = $data[Constants::KEY_PAYMENT_BRAND];
        $tokenInformation = $this->getTokenInformation($data);
        $token = $this->getTokenValue($data);
        $tokenInformation[Constants::CARD_ISSUER] = $cardIssuer;
        $tokenExpiry = $this->getTokenExpiry($tokenInformation);
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setCustomerId($customerId);
        $paymentToken->setPublicHash($this->encryptor->getHash($token));
        $paymentToken->setPaymentMethodCode($methodCode);
        $paymentToken->setType(strtolower('card'));
        $paymentToken->setExpiresAt($tokenExpiry);
        $paymentToken->setGatewayToken($token);
        $tokenDetails = $this->utilities->serialize($tokenInformation);
        $paymentToken->setTokenDetails((string)$tokenDetails);
        $this->paymentTokenRepository->save($paymentToken);
    }

    /**
     * Method to retrieve saved card token from response
     *
     * @param array<mixed> $data
     * @return string
     */
    public function getTokenValue(array $data):string
    {
        return $data[Constants::KEY_REGISTRATION_ID];
    }

    /**
     * Method to retrieve saved card information from response.
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public function getTokenInformation(array $data): array
    {
        return $data['card'];
    }

    /**
     * Method to retrieve card expiry.
     *
     * @param array<mixed> $tokenInformation
     * @return mixed
     */
    public function getTokenExpiry(array $tokenInformation): mixed
    {
        return $tokenInformation[Constants::CARD_EXPIRATION_YEAR]
            . '-'
            . $tokenInformation[Constants::CARD_EXPIRATION_MONTH];
    }
}
