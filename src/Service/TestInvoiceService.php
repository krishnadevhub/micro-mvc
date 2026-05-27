<?php

declare(strict_types=1);

namespace App\Service;

use Exception;

class TestInvoiceService
{
    public function __construct(
        protected readonly TestSalesTaxService $salesTaxService,
        protected readonly TestEmailService $emailService,
        protected readonly AppLogger $logger,
    ) { }

    /**
     * @throws Exception
     */
    public function process(array $customers, float $amount): bool
    {
        try {
            $tax = $this->salesTaxService->calculate($amount, $customers);
        } catch (Exception $ex) {
            $this->logger->getLogger()->error($ex);
            throw $ex;
        }

        return $this->emailService->send($customers, 'receipt');
    }
}
