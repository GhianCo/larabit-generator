<?php 

namespace App\Controller\Objectbase;

use App\Controller\BaseController;

abstract class Base extends BaseController
{
    protected function getServiceFindObjectbase()
    {
        return $this->container->get('find_objectbase_service');
    }
    protected function getServiceCreateObjectbase()
    {
        return $this->container->get('create_objectbase_service');
    }
    protected function getServiceUpdateObjectbase()
    {
        return $this->container->get('update_objectbase_service');
    }
    protected function getServiceDeleteObjectbase()
    {
        return $this->container->get('delete_objectbase_service');
    }
}
?>