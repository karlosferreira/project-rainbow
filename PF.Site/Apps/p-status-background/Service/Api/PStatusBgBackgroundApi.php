<?php
namespace Apps\P_StatusBg\Service\Api;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\P_StatusBg\Api\Resource\PStatusBgBackgroundResource;

class PStatusBgBackgroundApi extends AbstractResourceApi
{
    private $statusbgService;

    private $processService;

    private $feedProcessService;

    public function __construct()
    {
        parent::__construct();
        $this->statusbgService = \Phpfox::getService('pstatusbg');
        $this->processService = \Phpfox::getService('pstatusbg.process');
        if (\Phpfox::isModule('feed')) {
            $this->feedProcessService = \Phpfox::getService('feed.process');
        }
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setRequired(['collection_id'])->resolve($params)->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $items = $this->getBackgroundsByCollection($params['collection_id']);

        return $this->success($items);
    }

    public function getBackgroundsByCollection($collectionId, $limit = null)
    {
        $items = $this->statusbgService->getImagesByCollection((int)$collectionId, $limit);

        $this->processRows($items);

        return $items;
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $item = $this->loadResourceById($id, true, false);
        if (!$item) {
            return $this->notFoundError();
        }
        return $this->success($item->toArray());
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
            ->from(':pstatusbg_backgrounds')
            ->where(array_merge([
                'background_id' => (int)$id
            ], ($bActive ? ['is_deleted' => 0] : [])))
            ->execute('getRow');
        if (empty($item['background_id'])) {
            return null;
        }
        if ($returnResource) {
            return PStatusBgBackgroundResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        return PStatusBgBackgroundResource::populate($item)->displayShortFields()->toArray();
    }
}