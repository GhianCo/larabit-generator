<?php

namespace App\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ApiError extends \Slim\Handlers\Error
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        $statusCode = $this->getStatusCode($exception);
        $className = new \ReflectionClass($exception);
        $data = [
            'message' => $exception->getMessage(),
            'class' => $className->getName(),
            'status' => 'error',
            'code' => $statusCode,
        ];
        $body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $response->getBody()->write((string)$body);

        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-type', 'application/problem+json');
    }

    private function getStatusCode(\Exception $exception)
    {
        $statusCode = 500;
        if (is_int($exception->getCode()) &&
            $exception->getCode() >= 400 &&
            $exception->getCode() <= 500
        ) {
            $statusCode = $exception->getCode();
        }

        return $statusCode;
    }
}
