<?php

namespace App\Middleware;

use App\Exception\Auth as AuthException;


final class Auth extends Base
{
    public function __invoke($request, $response, $next)
    {
        $jwtHeader = $request->getHeaderLine('Authorization');
        $jwt_GET = isset($_GET["token"]) ? $_GET["token"] : false;
        if (!$jwtHeader && !$jwt_GET) {
            throw new AuthException('El token de autenticación es requerido.', 401);
        }
        if (!$jwtHeader && $jwt_GET) {
            $jwtHeader = 'Bearer ' . $jwt_GET;
        }
        $jwt = explode('Bearer ', $jwtHeader);
        if (!isset($jwt[1])) {
            throw new AuthException('El token de autenticación es inválido.', 401);
        }
        $decoded = $this->checkToken($jwt[1]);
        $object = (array)$request->getParsedBody();
        $object['decoded'] = $decoded;

        return $next($request->withParsedBody($object), $response);
    }
}
