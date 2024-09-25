<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Checkout\Model\Session;
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
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * ItemsDataBuilder constructor.
     *
     * @param Session $checkoutSession
     * @param Utilities $utilities
     */
    public function __construct(
        Session      $checkoutSession,
        Utilities $utilities
    ) {
        $this->checkoutSession = $checkoutSession;
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
        /** @var Quote $quote */
        $quote = $buildSubject['quote'];
        $totalItemsCount = $quote->getItemsCount() ?: 0;
        if ($order->getItems() && $totalItemsCount > 0) {
            $quote = $this->checkoutSession->getQuote();
            $purchaseItems = $this->buildCartItems($quote);
            $shippingItems = $this->buildShippingItems($quote);
            $quoteItems =  array_merge($purchaseItems, $shippingItems);
            $itemsArray = $this->utilities->removeNullValues([
                Constants::KEY_CART_ITEMS => $quoteItems,
            ]);
            return $this->utilities->formatCartItemsArray($itemsArray);
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
            $totalAmount = $item->getBaseRowTotal() + $item->getTaxAmount() - $discountAmount;
            $itemPrice = $totalAmount / $item->getQty();
            $result[] = [
                Constants::KEY_CART_ITEM_NAME => $item->getName(),
                Constants::KEY_CART_ITEM_QUANTITY => $item->getQty(),
                Constants::KEY_CART_ITEM_SKU => $item->getSku(),
                Constants::KEY_CART_ITEM_PRICE => $this->utilities->formatNumber(
                    $itemPrice
                ),
                Constants::KEY_ACI_PAYMENT_CURRENCY => $quote->getQuoteCurrencyCode(),
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
        $result[] = [
            Constants::KEY_CART_ITEM_NAME => $quote->getShippingAddress()->getShippingMethod(),
            Constants::KEY_CART_ITEM_QUANTITY => Constants::VALUE_SHIPPING_QUANTITY,
            Constants::KEY_CART_ITEM_PRICE => $this->utilities->formatNumber(
                $quote->getShippingAddress()->getShippingAmount()
            ),
            Constants::KEY_ACI_PAYMENT_CURRENCY => $quote->getQuoteCurrencyCode(),
            Constants::KEY_CART_ITEM_DESC =>$quote->getShippingAddress()->getShippingDescription(),
            Constants::KEY_CART_ITEM_TOTAL_AMOUNT => $this->utilities->formatNumber(
                $quote->getShippingAddress()->getShippingAmount()
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
