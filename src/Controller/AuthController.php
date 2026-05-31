<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function loginAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            header('Location: /profile');
            exit;
        }

        $errors = [];
        $request = Request::createFromGlobals();

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));
            $password = $request->request->get('password', '');

            if ($email === '' || $password === '') {
                $errors[] = 'All fields are required.';
            }

            if (empty($errors)) {
                $user = $this->userRepository->findByEmail($email);

                if ($user === null || !password_verify($password, $user->getPassword())) {
                    $errors[] = 'Invalid email or password.';
                } else {
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id' => $user->getId(),
                        'first_name' => $user->getFirstName(),
                        'surname' => $user->getSurname(),
                        'email' => $user->getEmail(),
                    ];

                    header('Location: /profile');
                    exit;
                }
            }
        }

        $this->render('login.html.twig', [
            'errors' => $errors,
            'email' => $email ?? '',
        ]);
    }

    public function registerAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user'])) {
            header('Location: /profile');
            exit;
        }

        $errors = [];
        $request = Request::createFromGlobals();

        if ($request->isMethod('POST')) {
            $firstName = trim($request->request->get('first_name', ''));
            $surname = trim($request->request->get('surname', ''));
            $email = trim($request->request->get('email', ''));
            $password = $request->request->get('password', '');

            if ($firstName === '') {
                $errors[] = 'First Name is required.';
            }

            if ($surname === '') {
                $errors[] = 'Surname is required.';
            }

            if ($email === '') {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }

            if ($password === '') {
                $errors[] = 'Password is required.';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters long.';
            }

            if (empty($errors)) {
                $existingUser = $this->userRepository->findByEmail($email);

                if ($existingUser !== null) {
                    $errors[] = 'An account with this email already exists.';
                }
            }

            if (empty($errors)) {
                $user = new User();
                $user->setFirstName($firstName);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

                $this->userRepository->insert($user);

                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'first_name' => $user->getFirstName(),
                    'surname' => $user->getSurname(),
                    'email' => $user->getEmail(),
                ];

                header('Location: /profile');
                exit;
            }
        }

        $this->render('register.html.twig', [
            'errors' => $errors,
            'first_name' => $firstName ?? '',
            'surname' => $surname ?? '',
            'email' => $email ?? '',
        ]);
    }

    public function logoutAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: /login');
        exit;
    }
}
