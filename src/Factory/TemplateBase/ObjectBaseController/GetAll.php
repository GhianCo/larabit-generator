<?php 

namespace App\Controller\Objectbase;

final class GetAll extends Base
{
    public function __invoke($request, $response)
    {
        $page = $request->getQueryParam('page', null);
        $perPage = $request->getQueryParam('perPage', null);
     
        $Objectbase = $this->getServiceFindObjectbase()->getObjectbasesByPage((int)$page, (int)$perPage);
     
        return $this->jsonResponse($response, 'success', $Objectbase['data'], 200, $Objectbase['pagination']);
    }
}
?>