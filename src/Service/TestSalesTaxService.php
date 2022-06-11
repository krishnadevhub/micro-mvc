<?php

declare(strict_types = 1);

namespace App\Service;

class TestSalesTaxService
{
    public function calculate(float $amount, array $customers): float
    {
        return $amount * 6.5 / 100;
    }
}
