<?php

declare(strict_types=1);

use App\Controller\ErrorPageController;
use App\Core\Application;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    Application::init();
} catch (Exception $exception) {
    dump($exception);
    (new ErrorPageController())->errorAction();

}







