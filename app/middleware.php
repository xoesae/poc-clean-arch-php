<?php

declare(strict_types=1);

use App\Application\Middlewares\SessionMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(SessionMiddleware::class);
};
