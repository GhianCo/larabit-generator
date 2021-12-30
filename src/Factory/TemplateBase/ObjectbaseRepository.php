<?php

namespace App\Repository;
use App\Entity\Objectbase;

final class ObjectbaseRepository extends BaseRepository
{
    public function checkAndGetObjectbase($objectbaseId)
    {
        $query = 'SELECT * FROM `objectbase` WHERE `objectbase_id` = ?';
        $objectbase = $this->getDb()::selectOne($query, [$objectbaseId]);
        if (!$objectbase) {
            throw new \App\Exception\Objectbase('No se encontró el identificador ' . $objectbaseId . '.', 404);
        }
        return new Objectbase(get_object_vars($objectbase));
    }

    public function getAll()
    {
        $query = 'SELECT * FROM `objectbase` ORDER BY `objectbase_id`';
        $allObjectbases = $this->getDb()::select($query);
        return (array)$allObjectbases;
    }

    public function getQueryObjectbasesByPage()
    {
        return "
            SELECT *
            FROM `objectbase`
            ORDER BY `objectbase_id`
        ";
    }

    public function getObjectbasesByPage($page, $perPage)
    {
        $params = array();
        $query = $this->getQueryObjectbasesByPage();
        $this->database::select($query, $params);
        $total = $this->database::selectOne('SELECT FOUND_ROWS() AS totalCount')->totalCount;
        return $this->getResultsWithPagination(
            $query,
            $page,
            $perPage,
            $params,
            $total
        );
    }
}
?>