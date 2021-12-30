<?php 

namespace App\Controller\Objectbase;

final class Delete extends Base
{
    public function __invoke($request, $response, $args)
    {
        $this->getServiceDeleteObjectbase()->delete((int)$args['id']);
     
        return $this->jsonResponse($response, 'success', null, 200);
    }
}
?>