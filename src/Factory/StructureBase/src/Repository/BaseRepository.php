<?php

namespace App\Repository;

use App\Exception\ConexionDB;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    protected function getDb()
    {
        return $this->database;
    }

    public function create(Model $model)
    {
        try {
            $model->save();
            return $model->refresh();
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    public function update(Model $model)
    {
        try {
            $model->exists = true;
            $model->update();
            return $model;
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    public function delete(Model $model)
    {
        try {
            $model->exists = true;
            return $model->delete();
        } catch (\Exception $exception) {
            throw new ConexionDB($exception->getMessage(), 500);
        }
    }

    protected function getResultsWithPagination($query, $page, $perPage, $params, $total)
    {
        return [
            'pagination' => [
                'totalRows' => $total,
                'totalPages' => ceil($total / $perPage),
                'currentPage' => $page,
                'perPage' => $perPage,
            ],
            'data' => $this->getResultByPage($query, $page, $perPage, $params),
        ];
    }

    protected function getResultByPage($query, $page, $perPage, $params)
    {
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ${perPage} OFFSET ${offset}";
        $data = $this->database::select($query, $params);
        return (array)$data;
    }
}
