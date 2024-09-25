<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Constants;
use Aci\Payment\Helper\Utilities;
use Magento\Quote\Model\Quote;

/**
 * Class AddressDataBuilder
 * Class to build address data
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @param Utilities $utilities
     */
    public function __construct(
        Utilities $utilities
    ) {
        $this->utilities = $utilities;
    }

    /**
     * Builds address data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $order = $payment->getOrder();
        $quote = $buildSubject['quote'];

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $billingAddressCheckout = $buildSubject['billingAddress'] ?? null;
        if ($billingAddressCheckout) {
            $billingArray = $this->getAddressDataFromArray(
                $this->utilities->unSerialize($billingAddressCheckout),
                Constants::BILLING_ADDRESS_PREFIX
            );
        } else {
            $billingArray = $billingAddress && $billingAddress->getCountryId() ? $this->getAddressData(
                $billingAddress,
                Constants::BILLING_ADDRESS_PREFIX
            ) : [];
        }

        $shippingAddressCheckout = $buildSubject['shippingAddress'] ?? null;
        if ($shippingAddressCheckout) {
            $shippingArray = $this->getAddressDataFromArray(
                $this->utilities->unSerialize($shippingAddressCheckout),
                Constants::SHIPPING_ADDRESS_PREFIX
            );
        } else {
            $shippingArray = $shippingAddress && $shippingAddress->getCountryId() ? $this->getAddressData(
                $shippingAddress,
                Constants::SHIPPING_ADDRESS_PREFIX
            ) : [];
        }

        $klarnaSpecificAddress = [];
        if (!empty($brandName = $payment->getPayment()->getAdditionalInformation(Constants::APM_BRAND_NAME))) {
            if (str_contains($brandName, Constants::KLARNA_PAYMENTS)) {
                $klarnaSpecificAddress = $this->getKlarnaSpecificAddress(
                    $shippingAddress,
                    $billingAddress,
                    $quote,
                    $shippingAddressCheckout
                );
            }
        }

        return array_merge($billingArray, $shippingArray, $klarnaSpecificAddress);
    }

    /**
     * Get address data
     *
     * @param AddressAdapterInterface $address
     * @param string $addressType
     * @return array<mixed>
     */
    public function getAddressData(AddressAdapterInterface $address, string $addressType): array
    {
        $prefix = $addressType . '.';
        return [
            $prefix . Constants::KEY_CITY         => $address->getCity(),
            $prefix . Constants::KEY_COUNTRY_CODE => $address->getCountryId(),
            $prefix . Constants::KEY_POSTAL_CODE  => $address->getPostcode(),
            $prefix . Constants::KEY_STATE        => $address->getRegionCode(),
            $prefix . Constants::KEY_STREET_1     => $address->getStreetLine1(),
            $prefix . Constants::KEY_STREET_2     => $address->getStreetLine2(),
        ];
    }

    /**
     * Get address data from Checkout new billing address
     *
     * @param array<mixed> $address
     * @param string $addressType
     * @return array<mixed>
     */
    public function getAddressDataFromArray(array $address, string $addressType): array
    {
        $prefix = $addressType . '.';
        return [
            $prefix . Constants::KEY_CITY         => $address[Constants::KEY_CITY] ?? '',
            $prefix . Constants::KEY_COUNTRY_CODE => $address[Constants::KEY_COUNTRY_CODE_BA] ?? '',
            $prefix . Constants::KEY_POSTAL_CODE  => $address[Constants::KEY_POSTAL_CODE] ?? '',
            $prefix . Constants::KEY_STATE        => $address[Constants::KEY_STATE_BA] ?? '',
            $prefix . Constants::KEY_STREET_1     => $address[Constants::KEY_STREET_BA][0] ?? '',
            $prefix . Constants::KEY_STREET_2     => $address[Constants::KEY_STREET_BA][1] ?? '',
        ];
    }

    /**
     * Prepare Klarna Related Params
     *
     * @param AddressAdapterInterface|null $address
     * @param AddressAdapterInterface|null $billingAddress
     * @param Quote $quote
     * @param string $shippingAddressCheckout
     * @return string[]
     */
    public function getKlarnaSpecificAddress(
        AddressAdapterInterface|null $address,
        AddressAdapterInterface|null $billingAddress,
        Quote $quote,
        string $shippingAddressCheckout
    ): array {
        $prefix = Constants::SHIPPING_ADDRESS_PREFIX . '.' . Constants::CUSTOMER_PREFIX. '.';
        $shippingMethod = Constants::SHIPPING_ADDRESS_PREFIX. '.' . Constants::KEY_SHIPPING_METHOD;

        $checkoutAddress = $this->utilities->unSerialize($shippingAddressCheckout);
        $firstName = $address?->getFirstname();
        $lastName = $address?->getLastname();
        $email = $billingAddress?->getEmail();
        $mobile = $address?->getTelephone();

        return [
            $prefix . Constants::KEY_FIRST_NAME         => $checkoutAddress[Constants::KEY_SHIPPING_FIRSTNAME]
                                                            ?? $firstName,
            $prefix . Constants::KEY_LAST_NAME          => $checkoutAddress[Constants::KEY_SHIPPING_LASTNAME]
                                                            ?? $lastName,
            $prefix . Constants::KEY_CUSTOMER_EMAIL     => $email,
            $prefix . Constants::KEY_CUSTOMER_MOBILE    => $checkoutAddress[Constants::KEY_SHIPPING_TELEPHONE]
                                                            ?? $mobile,
            $shippingMethod                             => $quote->getShippingAddress()->getShippingMethod()
        ];
    }
}
