<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TestInvoiceService;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Handles product-related page rendering.
 *
 * @package App\Controller
 */
class ProductController extends AbstractController
{
    /**
     * @param TestInvoiceService $invoiceService Service for processing invoices
     */
    public function __construct(
        private readonly TestInvoiceService $invoiceService
    ) {
    }

    /**
     * Renders the product detail page.
     *
     * @param int $id The product item number
     * @param int $sid The product sub-identifier
     * @return void
     * @throws Exception If invoice processing fails
     * @throws LoaderError If the template cannot be found
     * @throws RuntimeError If a runtime error occurs during rendering
     * @throws SyntaxError If there is a syntax error in the template
     */
    public function showAction(int $id, int $sid): void
    {
        $isInvoiceProcessed = $this->invoiceService->process([], 25);

        $this->render('product.html.twig', [
            'product' => [
                'itemNo' => $id,
                'sid' => $sid,
                'title' => 'Product Tile',
                'description' => 'Descc',
            ],
            'isInvoiceProcessed' => $isInvoiceProcessed,
        ]);
    }
}
