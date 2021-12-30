<?php 

namespace App\Controller\Objectbase;

final class GetOne extends Base
{
    public function __invoke($request, $response, array $args)
    {
        $objectbase = $this->getServiceFindObjectbase()->getObjectbase((int)$args['id']);
     
        return $this->jsonResponse($response, 'success', $objectbase, 200);
    }
}
?>