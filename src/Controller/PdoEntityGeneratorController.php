<?php
declare(strict_types=1);

namespace App\Controller;

class PdoEntityGeneratorController extends AbstractController
{
    public function indexAction(): void
    {
        $this->render('pdo_gen.html.twig');
    }
}