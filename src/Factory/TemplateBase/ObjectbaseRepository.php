<?php

namespace App\Repository;

use App\Entity\Objectbase;
use App\Exception\Objectbase as ObjectbaseException;

final class ObjectbaseRepository extends BaseRepository
{
    public function checkAndGetObjectbaseOrFail($objectbaseId)
    {
        $objectbaseSql = Objectbase::select();
        $objectbaseSql->where('objectbase_id', $objectbaseId);
        $objectbase = $objectbaseSql->first();
        if ($objectbase) {
            return $objectbase;
        }
        throw new ObjectbaseException('No se encontró el identificador ' . $objectbaseId . '.', 404);

    }

    public function getAll()
    {
        return Objectbase::all()->toArray();

    }

    public function getObjectbasesByPage($page, $perPage)
    {
        return $this->getResultsWithPagination(
            new Objectbase(),
            array(),
            $page,
            $perPage
        );
    }
}
?>