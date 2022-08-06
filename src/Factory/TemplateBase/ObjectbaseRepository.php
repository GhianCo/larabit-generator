<?php

namespace App\Repository;

use App\Entity\Objectbase;
use App\Exception\ObjectbaseException;

final class ObjectbaseRepository extends BaseRepository
{
    public function __construct($bdQueryManager)
    {
        parent::__construct($bdQueryManager, 'objectbase');
    }

    public function checkAndGetObjectbaseOrFail($objectbaseId): Objectbase
    {
        $criteria = array(
            'whereParams' => array(
                array("field" => "objectbase_id", "value" => $objectbaseId, "operator" => "=")
            )
        );

        $objectbase = $this->fetchRowByCriteria($criteria);

        if (!$objectbase) {
            throw new ObjectbaseException('No se encontró el identificador ' . $objectbaseId . '.', 404);
        }
        return $objectbase;
    }
}
?>