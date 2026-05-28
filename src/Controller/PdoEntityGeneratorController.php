<?php

declare(strict_types=1);

namespace App\Controller;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Handles rendering of the PDO entity generator page.
 *
 * @package App\Controller
 */
class PdoEntityGeneratorController extends AbstractController
{
    /**
     * Renders the PDO entity generator page.
     *
     * @return void
     * @throws LoaderError If the template cannot be found
     * @throws RuntimeError If a runtime error occurs during rendering
     * @throws SyntaxError If there is a syntax error in the template
     */
    public function indexAction(): void
    {
        $this->render('pdo_gen.html.twig');
    }
}
