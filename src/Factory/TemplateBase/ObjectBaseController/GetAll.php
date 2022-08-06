<?php 

namespace App\Controller\Objectbase;

final class GetAll extends Base
{
    public function __invoke($request, $response)
    {
        $page = $request->getQueryParam('page', null);
        $perPage = $request->getQueryParam('perPage', null);
     
        $objectbase = $this->getServiceFindObjectbase()->getObjectbasesByPage((int)$page, (int)$perPage);
     
        return $this->jsonResponse($response, 'success', $objectbase['data'], 200, $objectbase['pagination']);
    }
}
?>