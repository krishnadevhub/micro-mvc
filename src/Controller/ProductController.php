<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\RouteCollection;

class ProductController extends AbstractController
{
    public function showAction(int $id)
    {
        self::render('product.html.twig', [
            'product' => [
                'title' => 'Product Tile',
                'description' => 'Descc',
            ]
        ]);
    }

}