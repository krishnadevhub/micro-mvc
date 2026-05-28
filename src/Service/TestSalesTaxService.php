<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Test service for calculating sales tax on a given amount.
 *
 * @package App\Service
 */
class TestSalesTaxService
{
    private const TAX_RATE_PERCENTAGE = 6.5;
    private const PERCENTAGE_DIVISOR = 100;

    /**
     * Calculates the sales tax for the given amount.
     *
     * @param float $amount The base amount to calculate tax on
     * @param array<string> $customers List of customer identifiers
     * @return float The calculated tax amount
     */
    public function calculate(float $amount, array $customers): float
    {
        return $amount * self::TAX_RATE_PERCENTAGE / self::PERCENTAGE_DIVISOR;
    }
}
