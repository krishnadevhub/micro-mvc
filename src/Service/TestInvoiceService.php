<?php

declare(strict_types=1);

namespace App\Service;

use Exception;

/**
 * Test service for simulating invoice processing with tax calculation and email dispatch.
 *
 * @package App\Service
 */
class TestInvoiceService
{
    /**
     * @param TestSalesTaxService $salesTaxService Service for calculating sales tax
     * @param TestEmailService $emailService Service for sending emails
     * @param AppLogger $logger Application logger
     */
    public function __construct(
        protected readonly TestSalesTaxService $salesTaxService,
        protected readonly TestEmailService $emailService,
        protected readonly AppLogger $logger,
    ) {
    }

    /**
     * Processes an invoice by calculating tax and sending a receipt email.
     *
     * @param array<string> $customers List of customer identifiers
     * @param float $amount The invoice amount
     * @return bool Whether the receipt email was sent successfully
     * @throws Exception If tax calculation fails
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
