<?php

declare(strict_types=1);

namespace App\Controller;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Handles rendering of the homepage.
 *
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * Renders the homepage.
     *
     * @return void
     * @throws LoaderError If the template cannot be found
     * @throws RuntimeError If a runtime error occurs during rendering
     * @throws SyntaxError If there is a syntax error in the template
     */
    public function indexAction(): void
    {
        $this->render('home.html.twig');
    }
}
