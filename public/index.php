<?php

declare(strict_types=1);

use App\Controller\ErrorPageController;
use App\Core\Application;
use App\Service\AppLogger;

require dirname(__DIR__).'/vendor/autoload.php';

try {
    Application::init();
} catch (Exception $ex) {
    (new AppLogger())->getLogger()->error($ex);
    (new ErrorPageController())->errorAction();
}







