<?php

namespace App\Middleware;

use App\Exception\Auth;
use Firebase\JWT\JWT;

abstract class Base
{
    protected function checkToken($token)
    {
        try {
            return JWT::decode($token, $_SERVER['SECRET_KEY'], ['HS256']);
        } catch (\UnexpectedValueException $e) {
            throw new Auth('Acceso restringido: no tienes permisos para ver este recurso.', 403);
        }
    }
}
