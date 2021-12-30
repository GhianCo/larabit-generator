<?php

namespace App\Controller;

abstract class BaseController
{
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param array|object|null $message
     */
    protected function jsonResponse($response, $status, $data, $code, $pagination = null)
    {
        $result = [
            'code' => $code,
            'status' => $status,
            'data' => $data,
        ];

        if ($pagination) {
            $result['pagination'] = $pagination;
        }

        return $response->withJson($result, $code, JSON_PRETTY_PRINT);
    }
}
