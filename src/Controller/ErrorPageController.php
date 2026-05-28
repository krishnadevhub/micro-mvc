<?php

declare(strict_types=1);

namespace App\Controller;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Handles rendering of error pages.
 *
 * @package App\Controller
 */
class ErrorPageController extends AbstractController
{
    /**
     * Renders the generic error page.
     *
     * @return void
     * @throws LoaderError If the template cannot be found
     * @throws RuntimeError If a runtime error occurs during rendering
     * @throws SyntaxError If there is a syntax error in the template
     */
    public function errorAction(): void
    {
        $this->render('error.html.twig');
    }
}
