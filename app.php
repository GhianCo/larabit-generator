<?php

/*
 |--------------------------------------------------------------------
 | Crea la aplicación: Variables de entorno, conexión a base de datos
 | y demás los componente necesarios para exponer los servicios REST
 |--------------------------------------------------------------------
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/DotEnv.php';
require_once __DIR__ . '/config/CustomResponse.php';
require_once __DIR__ . '/config/ResponseFactory.php';

$app = require __DIR__ . '/config/Container.php';

(require __DIR__ . '/config/Database.php');


return $app;