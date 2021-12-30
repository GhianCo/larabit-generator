<?php

/*
 |--------------------------------------------------------------------
 | Crea la aplicación: Variables de entorno, conexión a base de datos
 | y demás los componente necesarios para exponer los servicios REST
 |--------------------------------------------------------------------
 */

$baseDir = __DIR__ . '/../';
$dotenv = Dotenv\Dotenv::createImmutable($baseDir);
$envFile = $baseDir . '.env';
if (file_exists($envFile)) {
    $dotenv->load();
}
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT']);
$settings = require __DIR__ . '/../config/settings.php';
$app = new \Slim\App($settings);
$app->add(new \CorsSlim\CorsSlim());
$container = $app->getContainer();
require __DIR__ . '/../config/dependencies.php';
require __DIR__ . '/../config/services.php';
require __DIR__ . '/../config/repositories.php';
require __DIR__ . '/../config/routes.php';