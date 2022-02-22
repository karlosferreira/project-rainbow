<?php
namespace Apps\P_StatusBg\Service\Api;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\P_StatusBg\Api\Resource\PStatusBgBackgroundResource;
use Apps\P_StatusBg\Api\Resource\PStatusBgCollectionResource;

class PStatusBgCollectionApi extends AbstractResourceApi implements MobileAppSettingInterface
{

    private $statusbgService;

    private $processService;

    public function __construct()
    {
        parent::__construct();
        $this->statusbgService = \Phpfox::getService('pstatusbg');
        $this->processService = \Phpfox::getService('pstatusbg.process');
    }

    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $items = $this->statusbgService->getCollectionsList(true, false);
        $this->processRows($items);

        return $this->success($items);
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $item = $this->loadResourceById($id, false, false);
        if (!$item) {
            return $this->notFoundError();
        }
        //Get first 10 backgrounds of this collection
        $item['backgrounds'] = (new PStatusBgBackgroundApi())->getBackgroundsByCollection($id, 10);
        return $this->success(PStatusBgCollectionResource::populate($item)->toArray());
    }

    function create($params)
    {
        return null;
    }

    function update($params)
    {
        return null;
    }

    function patchUpdate($params)
    {
        return null;
    }

    function delete($params)
    {
        return null;
    }

    function form($params = [])
    {
        return null;
    }

    function approve($params)
    {
        return null;
    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
    }

    function loadResourceById($id, $returnResource = false, $bActive = true)
    {
        $item = db()->select('*')
            ->from(':pstatusbg_collections')
            ->where(array_merge([
                'collection_id' => (int)$id
            ], ($bActive ? ['is_active' => 1] : [])))
            ->execute('getRow');
        if (empty($item['collection_id'])) {
            return null;
        }
        if ($returnResource) {
            return PStatusBgCollectionResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        return PStatusBgCollectionResource::populate($item)->displayShortFields()->toArray();
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('pstatusbg', [
            'title' => $l->translate('status_background'),
            'home_view' => 'menu',
            'main_resource' => new PStatusBgBackgroundResource([]),
            'category_resource' => new PStatusBgCollectionResource([]),
        ]);
        return $app;
    }


}