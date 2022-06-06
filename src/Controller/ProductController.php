<?php
declare(strict_types=1);

namespace App\Controller;

class ProductController extends AbstractController
{
    public function showAction(int $id, int $sid)
    {
        self::render('product.html.twig', [
            'product' => [
                'itemNo' => $id,
                'sid' => $sid,
                'title' => 'Product Tile',
                'description' => 'Descc',
            ]
        ]);
    }
}