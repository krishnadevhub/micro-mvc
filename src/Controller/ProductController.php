<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\TestInvoiceService;

class ProductController extends AbstractController
{
    public function __construct(
        private TestInvoiceService $invoiceService
    ) { }

    public function showAction(int $id, int $sid)
    {
        $isInvoiceProcessed = $this->invoiceService->process([], 25);

        self::render('product.html.twig', [
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