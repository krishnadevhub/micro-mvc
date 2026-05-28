<?php

declare(strict_types=1);

namespace App\Controller;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Handles rendering of the 404 not found page.
 *
 * @package App\Controller
 */
class PageNotFoundController extends AbstractController
{
    /**
     * Renders the page not found template.
     *
     * @return void
     * @throws LoaderError If the template cannot be found
     * @throws RuntimeError If a runtime error occurs during rendering
     * @throws SyntaxError If there is a syntax error in the template
     */
    public function notFoundAction(): void
    {
        $this->render('page_not_found.html.twig');
    }
}
