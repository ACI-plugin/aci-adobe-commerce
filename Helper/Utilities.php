<?php

namespace Aci\Payment\Helper;

use Magento\Framework\Serialize\Serializer\Json as Serializer;

/**
 * Class Utilities - Utility functions
 */
class Utilities
{
    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @var array<mixed>|string[]
     */
    protected array $pendingPattern = [
        '/^(000\.200)/',
        '/^(800\.400\.5|100\.400\.500)/'
    ];

    public const PATTERN_MANUAL_REVIEW = '/^(000.400.0[^3]|000.400.100)/';
    public const PATTERN_SUCCESS = '/^(000.000.|000.100.1|000.[36]|000.400.[1][12]0)/';

    /**
     * Utilities constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(
        Serializer $serializer,
    ) {
        $this->serializer = $serializer;
    }
    /**
     * Format amount to 2 decimal points
     *
     * @param float|null $amount
     * @return string
     */
    public function formatNumber(?float $amount): string
    {
        return $amount ? number_format($amount, 2, '.', '') : '0.00';
    }

    /**
     * Unserialize Array
     *
     * @param string $dataArray
     * @return mixed
     */
    public function unSerialize(string $dataArray): mixed
    {
        return $this->serializer->unserialize($dataArray);
    }

    /**
     * Filter null values from array
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public function removeNullValues(array $data): array
    {
        return array_filter($data, function ($val) {
            return $val !== null && $val !== '';
        });
    }

    /**
     * Format cart items array to the requirement of API.
     *
     * @param array<mixed> $cartItems
     * @return array<mixed>
     */
    public function formatCartItemsArray(array $cartItems): array
    {
        $returnArray = [];
        foreach ($cartItems['cart.items'] as $index => $item) {
            foreach ($item as $key => $value) {
                $returnArray["cart.items[$index].$key"] = $value;
            }
        }
        return $returnArray;
    }

    /**
     * Format custom parameters array to the requirement of API.
     *
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public function formatCustomParametersArray(array $params): array
    {
        $customParameters = [];
        foreach ($params as $key => $value) {
            $formattedKey = 'customParameters['.$key.']';
            $customParameters[$formattedKey] = $value;
        }
        return $customParameters;
    }

    /**
     * Serialize Array
     *
     * @param array<mixed> $dataArray
     * @return string|bool
     */
    public function serialize(array $dataArray): string|bool
    {
        return $this->serializer->serialize($dataArray);
    }

    /**
     * Check Successful Response
     *
     * @param string $responseCode
     * @return false|int
     */
    public function isSuccessResponse(string $responseCode): int|false
    {
        return preg_match(self::PATTERN_SUCCESS, $responseCode);
    }

    /**
     * Check Pending Response
     *
     * @param string $responseCode
     * @return bool
     */
    public function isPendingResponse(string $responseCode): bool
    {
        $return = false;
        foreach ($this->pendingPattern as $pattern) {
            if (preg_match($pattern, $responseCode)) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Check Manual Review Response
     *
     * @param string $responseCode
     * @return false|int
     */
    public function isManualReviewResponse(string $responseCode): int|false
    {
        return preg_match(self::PATTERN_MANUAL_REVIEW, $responseCode);
    }

    /**
     * Check Rejected Response
     *
     * @param string $responseCode
     * @return bool
     */
    public function isRejectedResponse(string $responseCode): bool
    {
        $return = false;
        foreach ($this->pendingPattern as $pattern) {
            if (preg_match($pattern, $responseCode)) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Validate the responses
     *
     * @param string $responseCode
     * @return string
     */
    public function validateResponse(string $responseCode): string
    {
        if ($this->isSuccessResponse($responseCode)) {
            return Constants::SUCCESS;
        } elseif ($this->isPendingResponse($responseCode)) {
            return Constants::PENDING;
        } elseif ($this->isManualReviewResponse($responseCode)) {
            return Constants::MANUAL_REVIEW;
        } elseif ($this->isRejectedResponse($responseCode)) {
            return Constants::REJECTED;
        } else {
            return Constants::FAILED;
        }
    }
}
