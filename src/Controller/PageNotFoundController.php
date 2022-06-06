<?php

declare(strict_types=1);

namespace App\Controller;

class PageNotFoundController extends AbstractController
{
    public function notFoundAction(): void
    {
        $this->render('page_not_found.html.twig');
    }

}