<?php 

namespace App\Controller\Objectbase;

final class Update extends Base
{
    public function __invoke($request, $response, array $args)
    {
        $input = (array)$request->getParsedBody();
        $objectbase = $this->getServiceUpdateObjectbase()->update($input, (int)$args['id']);
     
        return $this->jsonResponse($response, 'success', $objectbase, 200);
    }
}
?>