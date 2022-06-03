<?php


namespace App\Controller;

class ErrorPageController extends AbstractController
{
    public function errorAction(): void
    {
        $this->render('error.html.twig');
    }
}