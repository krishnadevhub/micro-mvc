<?php

declare(strict_types=1);

use App\Application;
use App\Controller\ErrorPageController;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    Application::init();
} catch (Exception $ex) {
    (new ErrorPageController())->errorAction();
}







