<?php

namespace Aci\Payment\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use InvalidArgumentException;

class ManageSubscriptionFrequency
{
    /**
     * @var SessionManagerInterface
     */
    protected SessionManagerInterface $sessionManager;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;

    /**
     * @param SessionManagerInterface $sessionManager
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        TimezoneInterface $timezone
    ) {
        $this->sessionManager = $sessionManager;
        $this->timezone = $timezone;
    }

    /**
     * Store $subscriptionFrequency value in session
     *
     * @param int $subscriptionFrequency
     * @param string $subscriptionUnit
     * @return void
     */
    public function storeSubscriptionDataInSession(int $subscriptionFrequency, string $subscriptionUnit): void
    {
        $this->sessionManager->start();
        $cronExpression = $this->generateCronExpression($subscriptionUnit, $subscriptionFrequency);
        /** @phpstan-ignore-next-line */
        $this->sessionManager->setData('ignite_subscription_options', [
            'subscription_frequency' => $subscriptionFrequency,
            'subscription_unit' => $subscriptionUnit,
            'subscription_job_expression' => $cronExpression,
        ]);
    }

    /**
     * Generate a Quartz Cron expression based on the given unit and frequency.
     *
     * @param string $unit The unit of time (day, week, month, year).
     * @param int $frequency The frequency of the unit.
     * @return string The generated Cron expression.
     */
    public function generateCronExpression(string $unit, int $frequency): string
    {
        // Get the current date using TimezoneInterface
        $currentDate = $this->timezone->date();
        $dayOfMonth = $currentDate->format('j'); // 1 through 31
        $dayOfWeek = $currentDate->format('N');  // 1 (for Monday) through 7 (for Sunday)
        $month = $currentDate->format('n');      // 1 through 12
        return match (strtolower($unit)) {
            'day' => sprintf("0 0 0 */%d * ? *", $frequency),
            'week' => sprintf("0 0 0 ? * %d/%d *", $dayOfWeek, $frequency * 7),
            'month' => ($dayOfMonth > 28)
                ? sprintf("0 0 0 L 1/%d ? *", $frequency) // Last day of every N months
                : sprintf("0 0 0 %d 1/%d ? *", $dayOfMonth, $frequency),
            'year' => ($dayOfMonth > 28)
                ? sprintf("0 0 0 L %d ? */%d", $month, $frequency) // Last day of the month
                : sprintf("0 0 0 %d %d ? */%d", $dayOfMonth, $month, $frequency),
            default => throw new InvalidArgumentException("Invalid unit: " . $unit),
        };
    }

    /**
     * Retrieve subscription frequency value from session
     *
     * @return string|null
     */
    public function getSubscriptionFrequencyFromSession(): ?string
    {
        $this->sessionManager->start();
        /** @phpstan-ignore-next-line */
        $subscriptionData = $this->sessionManager->getData('ignite_subscription_options');
        return $subscriptionData['subscription_frequency'] ?? null;
    }

    /**
     * Retrieve subscription unit value from session
     *
     * @return string|null
     */
    public function getSubscriptionUnitFromSession(): ?string
    {
        $this->sessionManager->start();
        /** @phpstan-ignore-next-line */
        $subscriptionData = $this->sessionManager->getData('ignite_subscription_options');
        return $subscriptionData['subscription_unit'] ?? null;
    }

    /**
     * Retrieve subscription job expression value from session
     *
     * @return string|null
     */
    public function getSubscriptionJobExpressionFromSession(): ?string
    {
        $this->sessionManager->start();
        /** @phpstan-ignore-next-line */
        $subscriptionData = $this->sessionManager->getData('ignite_subscription_options');
        return $subscriptionData['subscription_job_expression'] ?? null;
    }

    /**
     * Clear subscription data from session
     *
     * @return void
     */
    public function clearSubscriptionDataFromSession(): void
    {
        $this->sessionManager->start();
        /** @phpstan-ignore-next-line */
        $this->sessionManager->unsetData('ignite_subscription_options');
    }
}
