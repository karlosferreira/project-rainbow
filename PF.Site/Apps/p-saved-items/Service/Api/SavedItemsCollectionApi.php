<?php

namespace Apps\P_SavedItems\Service\Api;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\P_SavedItems\Api\Resource\SavedItemsCollectionResource;
use Apps\P_SavedItems\Api\Security\SavedItemsCollectionAccessControl;
use Apps\P_SavedItems\Api\Form\SavedItemsCollectionForm;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Phpfox;

class SavedItemsCollectionApi extends AbstractResourceApi
{
    private $processService;
    private $collectionService;

    public function __construct()
    {
        parent::__construct();
        $this->processService = Phpfox::getService('saveditems.collection.process');
        $this->collectionService = Phpfox::getService('saveditems.collection');
    }

    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $params = $this->resolver->setDefined(['limit', 'page', 'is_block', 'saved_id'])->setAllowedTypes('limit',
                'int', [
                    'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                    'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
                ])->setAllowedTypes('page', 'int')->setAllowedTypes('is_block', [1])->setAllowedTypes('saved_id',
                'int')->resolve($params)->getParameters();

        //update total items of user's collections after a limit time
        $this->collectionService->getTotalItemsForCollections();

        $browseParams = array(
            'module_id' => 'saveditems.collection',
            'alias' => 'collection',
            'field' => 'collection_id',
            'table' => Phpfox::getT('saved_collection'),
            'service' => 'saveditems.collection.browse',
        );

        if (!empty($params['saved_id'])) {
            $this->search()->setCondition('AND scd.saved_id = ' . (int)$params['saved_id']);
        }

        $this->search()->setCondition('AND collection.user_id = ' . Phpfox::getUserId());

        $isSlider = isset($params['is_block']) && !isset($params['view_all']);

        $this->search()->setLimit($isSlider ? 3 : (isset($params['limit']) ? $params['limit'] : $this->search()->getDisplay()))->setPage($params['page'])->setSort('collection.updated_time DESC');

        $this->browse()->params($browseParams)->execute();

        $items = $this->browse()->getRows();

        $this->processRows($items);

        return $this->success($items);
    }

    public function processRow($item)
    {
        $resource = $this->populateResource(SavedItemsCollectionResource::class, $item);
        return $resource->displayShortFields()->toArray();
    }

    function findOne($params)
    {
        $this->denyAccessUnlessGranted(SavedItemsCollectionAccessControl::IS_AUTHENTICATED);
        $params = $this->resolver->setDefined(['id'])->setAllowedValues('id',
                ['min' => 1])->resolve($params)->getParameters();

        $item = $this->loadResourceById($params['id']);
        if (empty($item['collection_id'])) {
            return $this->notFoundError();
        }

        $resource = SavedItemsCollectionResource::populate($item);
        return $this->success($resource->toArray());
    }

    public function createAccessControl()
    {
        $this->accessControl = new SavedItemsCollectionAccessControl($this->getSetting(), $this->getUser());
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(SavedItemsCollectionAccessControl::CREATE);

        $params = $this->resolver->setDefined(['name'])->setAllowedValues('name',
                'string')->resolve($params)->getParameters();

        $title = trim(urldecode($params['name']));

        if ($title == "" || mb_strlen($title) > 128) {
            return $this->error(_p('saveditems_collection_title_can_not_be_empty_and_maximum_128_character'));
        }

        if ($this->processService->add([
            'title' => $title,
            'privacy' => 0
        ])) {
            return $this->success([], [], _p('saveditems_collection_successfully_created'));
        }

        return $this->error($this->getErrorMessage());
    }

    function update($params)
    {
        $params = $this->resolver->setDefined(['name', 'id'])->setAllowedValues('name',
                'string')->setAllowedValues('id', ['min' => 1])->resolve($params)->getParameters();

        $item = $this->loadResourceById($params['id']);
        if (empty($item['collection_id'])) {
            return $this->notFoundError();
        }

        $resource = SavedItemsCollectionResource::populate($item);
        $this->denyAccessUnlessGranted(SavedItemsCollectionAccessControl::UPDATE, $resource);

        $title = trim(urldecode($params['name']));

        if ($title == "" || mb_strlen($title) > 128) {
            return $this->error(_p('saveditems_collection_title_can_not_be_empty_and_maximum_128_character'));
        }

        if ($this->processService->update(['id' => $item['collection_id'], 'title' => $title, 'privacy' => 0])) {
            return $this->success([
                'id' => $item['collection_id'],
                'resource_name' => $resource->getResourceName(),
                'module_name' => 'saveditems'
            ], [], _p('saveditems_collection_successfully_updated'));
        }

        return $this->error($this->getErrorMessage());
    }

    function patchUpdate($params)
    {
        return null;
    }

    function delete($params)
    {
        $params = $this->resolver->setDefined(['id'])->setAllowedValues('id',
                ['min' => 1])->resolve($params)->getParameters();

        $item = $this->loadResourceById($params['id']);
        if (empty($item['collection_id'])) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(SavedItemsCollectionAccessControl::DELETE,
            SavedItemsCollectionResource::populate($item));

        if ($this->processService->delete($item['collection_id'])) {
            return $this->success([], [], _p('saveditems_collection_successfully_deleted'));
        }

        return $this->error($this->getErrorMessage());
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        $isEdit = false;
        if (!empty($editId) && ($resource = $this->loadResourceById($editId, true))) {
            $this->denyAccessUnlessGranted(SavedItemsCollectionAccessControl::UPDATE, $resource);
            $isEdit = true;
        } else {
            $this->denyAccessUnlessGranted(SavedItemsCollectionAccessControl::CREATE);
        }

        if (empty($resource)) {
            return $this->notFoundError();
        }

        /** @var SavedItemsCollectionForm $form */
        $form = $this->createForm(SavedItemsCollectionForm::class, [
            'title' => 'saveditems_new_collection',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('saveditems-collection')
        ]);

        if ($isEdit) {
            $form->setMethod('put')->setTitle('saveditems_edit_collection')->setAction(UrlUtility::makeApiUrl('saveditems-collection/:id',
                    $editId))->assignValues($resource);
        }

        return $this->success($form->getFormStructure());
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

    function loadResourceById($id, $returnResource = false)
    {
        if (($item = $this->collectionService->getForEdit($id)) && !empty($item['collection_id'])) {
            return $returnResource ? SavedItemsCollectionResource::populate($item) : $item;
        }
        return false;
    }
}