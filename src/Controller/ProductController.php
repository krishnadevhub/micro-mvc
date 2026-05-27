<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\TestInvoiceService;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly TestInvoiceService $invoiceService
    ) { }

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