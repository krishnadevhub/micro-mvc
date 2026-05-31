<?php

declare(strict_types=1);

namespace App\Controller;

class ProfileController extends AbstractController
{
    public function indexAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $this->render('profile.html.twig', [
            'user' => $_SESSION['user'],
        ]);
    }
}
