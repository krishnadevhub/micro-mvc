<?php

declare(strict_types=1);

namespace App\Controller;

class ErrorPageController extends AbstractController
{
    public function errorAction(): void
    {
        $this->render('error.html.twig');
    }
}