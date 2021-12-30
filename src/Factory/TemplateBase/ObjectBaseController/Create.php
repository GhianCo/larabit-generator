<?php 

namespace App\Controller\Objectbase;

final class Create extends Base
{
    public function __invoke($request, $response)
    {
        $input = (array)$request->getParsedBody();
        $objectbase = $this->getServiceCreateObjectbase()->create($input);
     
        return $this->jsonResponse($response, 'success', $objectbase, 201);
    }
}
?>