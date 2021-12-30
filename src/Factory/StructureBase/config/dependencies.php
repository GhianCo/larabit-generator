<?php

use App\Handler\ApiError;
use Illuminate\Database\Capsule\Manager as Database;
use Psr\Container\ContainerInterface;

$container['db'] = static function (ContainerInterface $container) {
    $database = $container->get('settings')['db'];
    $pdo = new Database();
    $pdo->addConnection([
        'driver'    => 'mysql',
        'host'      => $database['host'],
        'database'  => $database['name'],
        'username'  => $database['user'],
        'password'  => $database['pass'],
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => ''
    ]);

    try {
        $pdo->setAsGlobal();
        $pdo->setFetchMode(PDO::FETCH_CLASS);
        $pdo->bootEloquent();
    } catch (Exception $e) {
        throw new \App\Exception\ConexionDB("Opps, problemas con la conexion de Base de datos", 500);
    }

    return $pdo;
};

$container['errorHandler'] = static function () {
    return new ApiError();
};

$container['notFoundHandler'] = static function () {
    return static function ($request, $response) {
        throw new \App\Exception\NotFound('Ruta no encontrada.', 404);
    };
};
