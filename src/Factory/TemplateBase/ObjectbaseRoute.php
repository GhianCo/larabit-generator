<?php

use App\Controller\Objectbase;
use App\Middleware\Auth;

$app->group('/objectbase', function () use ($app) {
    $app->get('', Objectbase\GetAll::class);
    $app->post('', Objectbase\Create::class);
    $app->get('/{id}', Objectbase\GetOne::class);
    $app->put('/{id}', Objectbase\Update::class);
    $app->delete('/{id}', Objectbase\Delete::class);
})->add(new Auth());
?>