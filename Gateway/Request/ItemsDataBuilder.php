<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Constants;
use Magento\Quote\Model\Quote;
use Aci\Payment\Helper\Utilities;
use Magento\Catalog\Model\Product\Type;

/**
 * Builds items data
 */
class ItemsDataBuilder implements BuilderInterface
{

    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * @var float
     */
    private float $orderTotal = 0;

    /**
     * ItemsDataBuilder constructor.
     *
     * @param Utilities $utilities
     */
    public function __construct(
        Utilities $utilities
    ) {
        $this->utilities = $utilities;
    }

    /**
     * Builds items request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function build(array $buildSubject): array
    {
        $payment = SubjectReader::readPayment($buildSubject);
        $order = $payment->getOrder();
        $brand = '';
        /** @var Quote $quote */
        $quote = $buildSubject['quote'];
        $grandTotal = $quote->getBaseGrandTotal();
        $paymentMethod = $quote->getPayment()->getMethod();
        if ($paymentMethod == 'aci_apm') {
            $additionalInfo = $payment->getPayment()->getAdditionalInformation();
            if (!empty($additionalInfo[Constants::APM_BRAND_NAME])) {
                $brand = $additionalInfo[Constants::APM_BRAND_NAME];
            }
        }
        $totalItemsCount = $quote->getItemsCount() ?: 0;
        if ($order->getItems() && $totalItemsCount > 0) {
            $purchaseItems = $this->buildCartItems($quote);
            $shippingItems = $this->buildShippingItems($quote);
            $quoteItems =  array_merge($purchaseItems, $shippingItems);
            $itemsArray = $this->utilities->removeNullValues([
                Constants::KEY_CART_ITEMS => $quoteItems
            ]);
            $formattedCartItems = $this->utilities->formatCartItemsArray($itemsArray);
            $difference = [];
            if (strtoupper($brand) == 'PAYPAL') {
                $difference_in_amount = $grandTotal - floatval($this->orderTotal);
                if ($difference_in_amount > 0) {
                    $difference['shipping.cost'] = $this->utilities->formatNumber($difference_in_amount);
                } elseif ($difference_in_amount < 0) {
                    $difference['cart.payments[0].amount'] =
                        $this->utilities->formatNumber(abs($difference_in_amount));
                }
            }
            return array_merge($formattedCartItems, $difference);
        } else {
            return [];
        }
    }

    /**
     * Get Cart items from quote.
     *
     * @param Quote $quote
     * @return array<mixed>
     */
    public function buildCartItems(Quote $quote): array
    {
        $quoteItems = $quote->getAllVisibleItems();
        $result = [];
        foreach ($quoteItems as $item) {
            if ($item->getProductType() == Type::TYPE_BUNDLE) {
                $discountAmount = $this->getBundleProductDiscount($quote, (int)$item->getItemId());
            } else {
                $discountAmount = $item->getBaseDiscountAmount();
            }
            $totalAmount = $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $discountAmount;
            $quantity = $item->getQty();
            $itemPrice = $this->utilities->formatNumber($totalAmount / $quantity);
            $this->orderTotal = floatval($this->orderTotal) + ((float)$itemPrice * (float)$quantity);
            $result[] = [
                Constants::KEY_CART_ITEM_NAME => $item->getName(),
                Constants::KEY_CART_ITEM_QUANTITY => $quantity,
                Constants::KEY_CART_ITEM_SKU => $item->getSku(),
                Constants::KEY_CART_ITEM_PRICE => $itemPrice,
                Constants::KEY_ACI_PAYMENT_CURRENCY => $quote->getBaseCurrencyCode(),
                Constants::KEY_CART_ITEM_DESC =>$item->getDescription(),
                Constants::KEY_CART_ITEM_TOTAL_AMOUNT => $this->utilities->formatNumber(
                    $totalAmount
                ),
            ];
        }
        return $result;
    }

    /**
     * Get shipping details of cart items.
     *
     * @param Quote $quote
     * @return array<mixed>
     */
    public function buildShippingItems(Quote $quote): array
    {
        if (!$quote->getShippingAddress()->getCountryId()) {
            return [];
        }
        $shippingItemPrice = $this->utilities->formatNumber(
            $quote->getShippingAddress()->getBaseShippingAmount()
        );
        $this->orderTotal = floatval($this->orderTotal) + floatval($shippingItemPrice);
        $result[] = [
            Constants::KEY_CART_ITEM_NAME => $quote->getShippingAddress()->getShippingMethod(),
            Constants::KEY_CART_ITEM_QUANTITY => Constants::VALUE_SHIPPING_QUANTITY,
            Constants::KEY_CART_ITEM_PRICE => $shippingItemPrice,
            Constants::KEY_ACI_PAYMENT_CURRENCY => $quote->getBaseCurrencyCode(),
            Constants::KEY_CART_ITEM_DESC =>$quote->getShippingAddress()->getShippingDescription(),
            Constants::KEY_CART_ITEM_TOTAL_AMOUNT => $this->utilities->formatNumber(
                $quote->getShippingAddress()->getBaseShippingAmount()
            ),
        ];
        return $result;
    }

    /**
     * Get total discount amount of bundle product
     *
     * @param Quote $quote
     * @param int $itemId
     * @return float
     */
    public function getBundleProductDiscount(Quote $quote, int $itemId): float
    {
        $discount = 0;
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getParentItemId() != $itemId) {
                continue;
            }
            $discount += $quoteItem->getBaseDiscountAmount();
        }
        return $discount;
    }
}
