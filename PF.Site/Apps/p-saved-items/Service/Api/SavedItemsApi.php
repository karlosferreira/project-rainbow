<?php

namespace Apps\P_SavedItems\Service\Api;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\P_SavedItems\Api\Security\SavedItemsAccessControll;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\P_SavedItems\Api\Resource\SavedItemsResource;
use Apps\P_SavedItems\Api\Resource\SavedItemsCollectionResource;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\P_SavedItems\Api\Security\SavedItemsCollectionAccessControl;
use Apps\P_SavedItems\Api\Form\SavedItemsAddToCollectionForm;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Phpfox;

class SavedItemsApi extends AbstractResourceApi implements MobileAppSettingInterface
{
    private $processService;
    private $saveditemsService;

    public function __construct()
    {
        define('SEARCH_SAVED_ITEM_SCREEN_SETTING_NAME', 'searchSaveditems');
        parent::__construct();
        $this->processService = Phpfox::getService('saveditems.process');
        $this->saveditemsService = Phpfox::getService('saveditems');
    }

    public function __naming()
    {
        return [
            'saveditems/save' => [
                'post' => 'save'
            ],
            'saveditems/unsave' => [
                'delete' => 'unsave'
            ],
            'saveditems/open' => [
                'put' => 'open'
            ],
            'saveditems/collection-form' => [
                'get' => 'getAddToCollectionForm'
            ],
            'saveditems/collection' => [
                'put' => 'addToCollection'
            ],
            'saveitems/get-tab' => [
                'get' => 'getAllUserTab'
            ]
        ];
    }

    public function addToCollection($params)
    {
        $this->denyAccessUnlessGranted(SavedItemsAccessControll::ADD_COLLECTION);

        $params = $this->resolver->setDefined(['id', 'collection'])->setAllowedValues('id',
                ['min' => 1])->setAllowedTypes('collection', 'array')->resolve($params)->getParameters();

        if (empty($params['id'])) {
            return $this->notFoundError();
        }

        if ($this->processService->addItemToCollectionsForMobile($params['id'], $params['collection'])) {
            $item = $this->loadResourceById($params['id']);
            return $this->success(SavedItemsResource::populate($item)->toArray());
        }

        return $this->error($this->getErrorMessage());
    }

    public function getAddToCollectionForm($params)
    {
        $this->denyAccessUnlessGranted(SavedItemsAccessControll::IS_AUTHENTICATED);
        $params = $this->resolver->setDefined(['saved_id', 'item_type', 'item_id', 'in_feed'])->setAllowedTypes('saved_id',
                'int')->setAllowedTypes('item_id', 'int')->setAllowedTypes('item_type',
                'string')->setAllowedValues('in_feed', [1])
            ->resolve($params)->getParameters();

        $data = [];

        if (!empty($params['saved_id'])) {
            $data = ['id' => (int)$params['saved_id']];
        } elseif (!empty($params['item_type']) && !empty($params['item_id'])) {
            if(!empty($params['in_feed'])) {
                $params['item_id'] = $this->_getRealItemId($params['item_type'], $params['item_id']);
            }
            $id = $this->_getIdByType($params['item_type'], $params['item_id']);
            if (!empty($id)) {
                $data = ['id' => (int)$id];
            }
        }

        if (!empty($data)) {
            return $this->form($data);
        }

        return $this->error(_p('saveditems_item_not_found'));
    }

    public function open($params)
    {
        $params = $this->resolver->setDefined(['saved_id', 'is_unopened', 'no_message'])->setAllowedTypes('saved_id',
                'int', ['min' => 1])->setAllowedTypes('is_unopened', 'int', [1, 0])->setAllowedTypes('no_message',
                'int', [1])->resolve($params)->getParameters();

        $this->denyAccessUnlessGranted($params['is_unopened'] ? SavedItemsAccessControll::OPEN : SavedItemsAccessControll::UNOPEN,
            null, [
                'saved_id' => $params['saved_id'],
            ]);

        if (!$params['is_unopened']) {
            return $this->success([]);
        }
        if ($this->processService->processItemStatus($params['saved_id'], !$params['is_unopened'])) {
            return $this->success(['is_unopened' => !$params['is_unopened']], [],
                isset($params['no_message']) ? null : $this->getLocalization()->translate($params['is_unopened'] ? 'saveditems_item_successfully_marked_as_opened' : 'saveditems_item_successfully_marked_as_unopened'));
        }

        return $this->error($this->getErrorMessage());
    }

    public function unsave($params)
    {
        $params = $this->resolver->setDefined([
                'item_type',
                'item_id',
                'like_type',
                'collection_id',
                'is_detail',
                'in_feed'
            ])->setAllowedTypes('item_id', 'int', ['min' => 1])->setAllowedTypes('collection_id', 'int',
                ['min' => 1])->setAllowedTypes('item_type', 'string')->setAllowedTypes('like_type',
                'string')->setAllowedValues('is_detail', [1])->setAllowedValues('in_feed',
                [1])->resolve($params)->getParameters();

        $itemType = isset($params['item_type']) ? $params['item_type'] : $params['like_type'];

        if (empty($itemType) && empty($params['item_id'])) {
            return $this->notFoundError();
        }

        if (isset($params['collection_id'])) {
            $this->denyAccessUnlessGranted(SavedItemsAccessControll::IS_AUTHENTICATED);
        } else {
            if(!empty($params['in_feed'])) {
                $params['item_id'] = $this->_getRealItemId($itemType, $params['item_id']);
            }

            $this->denyAccessUnlessGranted(SavedItemsAccessControll::UNSAVE, null, [
                'item_type' => $itemType,
                'item_id' => $params['item_id'],
                'in_feed' => !empty($params['in_feed'])
            ]);
        }



        if ($this->processService->save([
            'type_id' => $itemType,
            'item_id' => $params['item_id'],
            'is_save' => 0,
            'collection_id' => isset($params['collection_id']) ? (int)$params['collection_id'] : 0
        ])) {
            return $this->success(['is_saved' => 0, 'saved_id' => 0], [],
                isset($params['is_detail']) ? _p('saveditems_unsaved_item_successfully') : (isset($params['in_feed']) ? _p('saveditems_this_item_has_been_unsaved_successfully') : null));
        }

        return $this->error($this->getErrorMessage());
    }

    public function save($params)
    {
        $params = $this->resolver->setDefined([
                'item_type',
                'item_id',
                'link',
                'like_type',
                'is_detail',
                'in_feed'
            ])->setAllowedTypes('item_id', 'int', ['min' => 1])->setAllowedTypes('item_type',
                'string')->setAllowedTypes('link', 'string')->setAllowedTypes('like_type',
                'string')->setAllowedValues('is_detail', [1])->setAllowedValues('in_feed',
                [1])->resolve($params)->getParameters();

        $itemType = isset($params['item_type']) ? $params['item_type'] : $params['like_type'];

        if (empty($itemType) && empty($params['item_id'])) {
            return $this->notFoundError();
        }

        if(!empty($params['in_feed'])) {
            $params['item_id'] = $this->_getRealItemId($itemType, $params['item_id']);
        }

        $this->denyAccessUnlessGranted(SavedItemsAccessControll::SAVE, null, [
            'item_type' => $itemType,
            'item_id' => $params['item_id'],
        ]);

        if ($savedId = $this->processService->save([
            'type_id' => $itemType,
            'item_id' => $params['item_id'],
            'link' => isset($params['link']) ? $params['link'] : '',
            'is_save' => 1,
        ])) {
            return $this->success(['is_saved' => 1, 'saved_id' => $savedId], [],
                isset($params['is_detail']) ? _p('saveditems_saved_successfully') : (isset($params['in_feed']) ? _p('saveditems_this_item_has_been_saved_successfully') : null));
        }

        return $this->error($this->getErrorMessage());
    }

    public function createAccessControl()
    {
        $this->accessControl = new SavedItemsAccessControll($this->getSetting(), $this->getUser());
    }

    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(SavedItemsAccessControll::IS_AUTHENTICATED);

        $params = $this->resolver->setDefined([
                'type',
                'sort',
                'when',
                'q',
                'limit',
                'collection_id'
            ])->setAllowedValues('sort', ['latest', 'oldest'])->setAllowedValues('when',
                ['all-time', 'today', 'this-week', 'this-month'])->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])->setAllowedValues('collection_id', ['min' => 1])->resolve($params)->getParameters();

        $limit = isset($params['limit']) ? $params['limit'] : 10;

        list(, $items) = $this->saveditemsService->query($limit, $params);
        if (!empty($items) && $params['collection_id']) {
            foreach ($items as &$item) {
                $item['in_collection'] = $params['collection_id'];
            }
        }
        $this->processRows($items);

        return $this->success($items);
    }

    function findOne($params)
    {
        $this->denyAccessUnlessGranted(SavedItemsAccessControll::IS_AUTHENTICATED);

        $params = $this->resolver->setDefined(['id', 'saved_id', 'item_type', 'item_id', 'in_feed'])->setAllowedTypes('id', 'int',
                ['min' => 1])->setAllowedTypes('saved_id', 'int')->setAllowedTypes('item_id',
                'int')->setAllowedTypes('item_type', 'string')->setAllowedValues('in_feed', [1])
            ->resolve($params)->getParameters();
        $id = !empty($params['saved_id']) ? $params['saved_id'] : $params['id'];

        if (isset($params['item_type']) && isset($params['item_id'])) {
            if(!empty($params['in_feed'])) {
                $params['item_id'] = $this->_getRealItemId($params['item_type'], $params['item_id']);
            }

            $id = $this->_getIdByType($params['item_type'], $params['item_id']);
        }

        $resource = $this->loadResourceById($id, true);
        if (empty($resource)) {
            return $this->notFoundError();
        }

        return $this->success($resource->toArray());
    }

    public function processRow($item)
    {
        $resource = $this->populateResource(SavedItemsResource::class, $item);
        return $resource->displayShortFields()->toArray();
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
        $this->denyAccessUnlessGranted(SavedItemsAccessControll::IS_AUTHENTICATED);

        $id = $this->resolver->resolveId($params);

        if (empty($id)) {
            return $this->notFoundError();
        }


        $form = $this->createForm(SavedItemsAddToCollectionForm::class, [
            'title' => 'saveditems_add_to_collections',
            'method' => 'PUT',
            'action' => UrlUtility::makeApiUrl('saveditems/collection')
        ]);

        $form->setSavedId($id);

        $collectionsBelongToItem = $this->saveditemsService->getCollectionRelatedToSavedItem($id);
        if (!empty($collectionsBelongToItem[$id])) {
            $form->assignValues([
                'collection' => array_column($collectionsBelongToItem[$id], 'collection_id')
            ]);
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
        $item = $this->saveditemsService->getItem(['saved_id' => $id], true);
        if (empty($item['saved_id'])) {
            return false;
        }

        return $returnResource ? SavedItemsResource::populate($item) : $item;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        $collectionResource = SavedItemsCollectionResource::populate([])->getResourceName();
        return [
            'saveditems/save' => [
                'method' => 'post',
                'url' => 'mobile/saveditems/save/',
                'data' => 'item_type=:item_type, item_id=:item_id, link=:link, in_feed=1',
                'actionSuccess' => 'saveditems/add_to_collections_in_feed'
            ],
            'saveditems/save_detail_item' => [
                'method' => 'post',
                'url' => 'mobile/saveditems/save/',
                'data' => 'item_id=:item_id, link=:link, like_type=:like_type_id, is_detail=1',
            ],
            'saveditems/save_popup_detail_item' => [
                'method' => 'post',
                'url' => 'mobile/saveditems/save/',
                'data' => 'item_id=:item_id, link=:link, like_type=:like_type_id, is_detail=1',
                'actionSuccess' => 'saveditems/add_to_collections_in_detail'
            ],
            'saveditems/unsave' => [
                'method' => 'delete',
                'url' => 'mobile/saveditems/unsave/',
                'data' => 'item_type=:item_type, item_id=:item_id, collection_id=:collection_id',
                'actionFlow' => 'delete'
            ],
            'saveditems/unsave_detail_item' => [
                'method' => 'delete',
                'url' => 'mobile/saveditems/unsave/',
                'data' => 'item_id=:item_id, like_type=:like_type_id, is_detail=1',
            ],
            'saveditems/unsave_confirmation_detail_item' => [
                'method' => 'delete',
                'url' => 'mobile/saveditems/unsave/',
                'data' => 'item_id=:item_id, like_type=:like_type_id, is_detail=1',
                'actionSuccess' => [
                    [
                        "action" => "deleteItem",
                        "module_name" => "saveditems",
                        "resource_name" => SavedItemsResource::populate([])->getResourceName(),
                        "id" => ":saved_id"
                    ]
                ],
                'confirm_title' => _p('confirm'),
                'confirm_message' => _p('saveditems_unsave_from_collection_notice'),
            ],
            'saveditems/unsave_confirmation' => [
                'method' => 'delete',
                'url' => 'mobile/saveditems/unsave/',
                'data' => 'item_type=:item_type, item_id=:item_id, collection_id=:collection_id',
                'confirm_title' => _p('confirm'),
                'confirm_message' => _p('saveditems_unsave_from_collection_notice'),
                'actionFlow' => 'delete'
            ],
            'saveditems/unsave_in_feed' => [
                'method' => 'delete',
                'url' => 'mobile/saveditems/unsave/',
                'data' => 'item_type=:item_type, item_id=:item_id, like_type=:like_type_id, in_feed=1',
            ],
            'saveditems/open' => [
                'method' => 'put',
                'url' => 'mobile/saveditems/open/',
                'data' => 'saved_id=:saved_id, is_unopened=:is_unopened',
                'new_state' => 'is_unopened=!is_unopened',
            ],
            'saveditems/open_url' => [
                'method' => 'put',
                'url' => 'mobile/saveditems/open/',
                'data' => 'saved_id=:saved_id, is_unopened=:is_unopened, no_message=1',
                'new_state' => 'is_unopened=!is_unopened',
            ],
            'saveditems-collection/create_collection' => [
                'method' => 'post',
                'url' => 'mobile/saveditems-collection',
                'params' => [
                    'module_name' => 'saveditems',
                    'resource_name' => $collectionResource
                ],
                "prompt_title" => _p('saveditems_create_new_collection_uc_first'),
                "prompt_message" => _p('saveditems_enter_colllection_name'),
                "prompt_ok_label" => _p('create'),
                "prompt_cancel_label" => _p('cancel_uppercase'),
                "prompt_data_key" => "name",
                "actionSuccess" => [
                    [
                        "action" => "formCallback",
                        "formName" => "formAddItem",
                        "callbackName" => "reload"
                    ]
                ]
            ],
            'saveditems/add_to_collections_in_feed' => [
                'routeName' => 'formEdit',
                'params' => [
                    'module_name' => 'saveditems',
                    'resource_name' => SavedItemsResource::populate([])->getResourceName(),
                    'formType' => 'addToCollections',
                    'use_query' => [
                        'saved_id' => ':saved_id',
                        'item_type' => ':item_type',
                        'item_id' => ':item_id',
                        'in_feed' => 1,
                    ]
                ],
            ],
            'saveditems/add_to_collections_in_detail' => [
                'routeName' => 'formEdit',
                'params' => [
                    'module_name' => 'saveditems',
                    'resource_name' => SavedItemsResource::populate([])->getResourceName(),
                    'formType' => 'addToCollections',
                    'use_query' => [
                        'saved_id' => ':saved_id',
                        'item_type' => ':like_type_id',
                        'item_id' => ':item_id'
                    ]
                ],
            ],
            'saveditems/add_to_collections_in_saved_app' => [
                'routeName' => 'formEdit',
                'params' => [
                    'module_name' => 'saveditems',
                    'resource_name' => SavedItemsResource::populate([])->getResourceName(),
                    'formType' => 'addToCollections',
                    'use_query' => [
                        'saved_id' => ':saved_id',
                        'item_type' => ':item_type',
                        'item_id' => ':item_id'
                    ]
                ],
            ],
        ];
    }


    private function _getIdByType($typeId, $itemId)
    {
        return db()->select('saved_id')->from(Phpfox::getT('saved_items'))->where([
                'type_id' => $typeId,
                'item_id' => $itemId,
                'user_id' => Phpfox::getUserId(),
            ])->execute('getSlaveField');
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('saveditems', [
            'title' => $l->translate('module_saveditems'),
            'home_view' => 'menu',
            'main_resource' => new SavedItemsResource([]),
            'other_resources' => [
                new SavedItemsCollectionResource([]),
            ]
        ]);

        //Define custom templates
        $app->setTemplates([
            'saved-item' => $this->getLayoutTemplate('saved-item.jsx'),
            'saved-item-collection-block' => $this->getLayoutTemplate('saved-item-collection-block.jsx'),
            'saved-item-collection' => $this->getLayoutTemplate('saved-item-collection.jsx'),
            'create-collection' => $this->getLayoutTemplate('create-collection.jsx'),
        ]);

        $headerButtons = [];
        $savedItemsResource = SavedItemsResource::populate([])->getResourceName();
        $savedItemsCollectionResource = SavedItemsCollectionResource::populate([])->getResourceName();
        $savedItemsCollectionAccessControl = new SavedItemsCollectionAccessControl($this->getSetting(),
            $this->getUser());

        if ($savedItemsCollectionAccessControl->isGranted(SavedItemsCollectionAccessControl::CREATE)) {
            $headerButtons[$savedItemsResource][] = [
                'icon' => 'plus',
                'action' => "saveditems-collection/create_collection",
                'params' => ['resource_name' => $savedItemsCollectionResource]
            ];
            $headerButtons[$savedItemsCollectionResource][] = [
                'icon' => 'plus',
                'action' => "saveditems-collection/create_collection",
                'params' => ['resource_name' => $savedItemsCollectionResource]
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    private function getLayoutTemplate($name)
    {
        return file_get_contents(PHPFOX_DIR_SITE_APPS . "p-saved-items" . PHPFOX_DS . "views" . PHPFOX_DS . "mobile-templates" . PHPFOX_DS . $name);
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('saveditems', []);
        $resourceName = SavedItemsResource::populate([])->getResourceName();

        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'module_header',
            ],
            ScreenSetting::LOCATION_MAIN => [
                'component' => 'smart_resource_list',
                'embedComponents' => [
                    [
                        'component' => ScreenSetting::SIMPLE_LISTING_BLOCK,
                        'embedComponents' => [
                            'component' => 'custom_view',
                            'item_template' => 'saveditems.create-collection',
                            'position' => 'bottom'
                        ],
                        'title' => $l->translate('saveditems_recently_updated'),
                        'resource_name' => SavedItemsCollectionResource::populate([])->getResourceName(),
                        'module_name' => 'saveditems',
                        'item_template' => 'saveditems.saved-item-collection-block',
                        'query' => ['is_block' => 1],
                        'horizontal' => true,
                        'view_all_icon' => "angle-right"
                    ],
                ],
                "item_template" => "saveditems.saved-item",
                "initialQuery" => [
                    "type" => "all"
                ]
            ],
        ]);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);


        $collectionResourceName = SavedItemsCollectionResource::populate([])->getResourceName();

        $screenSetting->addSetting($collectionResourceName, ScreenSetting::MODULE_HOME, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'module_header',
            ],
            ScreenSetting::LOCATION_MAIN => [
                'component' => ScreenSetting::SMART_RESOURCE_LIST,
                'item_template' => 'saveditems.saved-item-collection',
            ],
            ScreenSetting::LOCATION_BOTTOM => [],
        ]);

        $screenSetting->addSetting($resourceName, SEARCH_SAVED_ITEM_SCREEN_SETTING_NAME, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'module_header',
            ],
            ScreenSetting::LOCATION_MAIN => [
                'component' => ScreenSetting::SMART_RESOURCE_LIST,
                'item_template' => 'saveditems.saved-item',
            ],
            ScreenSetting::LOCATION_BOTTOM => [
                'component' => 'sort_filter_fab'
            ],
        ]);

        $screenSetting->addSetting($collectionResourceName, ScreenSetting::MODULE_LISTING, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
            ],
            ScreenSetting::LOCATION_MAIN => [
                'component' => ScreenSetting::SMART_RESOURCE_LIST,
                'item_template' => 'saveditems.saved-item-collection',
            ],
            ScreenSetting::LOCATION_BOTTOM => [],
        ]);

        $screenSetting->addSetting($collectionResourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component' => 'item_header',
            ],
            ScreenSetting::LOCATION_BOTTOM => [],
            ScreenSetting::LOCATION_MAIN => [
                'component' => 'item_simple_detail',
                'embedComponents' => []
            ],
        ]);

        return $screenSetting;
    }

    private function _getRealItemId($itemType, $itemId)
    {
        switch ($itemType) {
            case 'forum':
                $itemId = db()->select('thread_id')
                    ->from(Phpfox::getT('forum_thread'))
                    ->where(['start_id' => $itemId])
                    ->execute('getSlaveField');
                break;
            default:
                break;
        }

        return $itemId;
    }

    public function getAllUserTab($params = [])
    {
        $this->denyAccessUnlessGranted(SavedItemsAccessControll::IS_AUTHENTICATED);
        $savedTypes = Phpfox::getService('saveditems')->getStatisticByType();
        if (!empty($savedTypes)){
            $aItems[] = [
                'title' => 'All',
                'query' => [
                    'type' => 'all'
                ]
            ];
        }
        foreach ($savedTypes as $type) {
            if (!empty($type['type_id'])) {
                $item = [
                    'title' => $type['type_name'],
                    'query' => [
                        'type' => $type['type_id'],
                    ]
                ];
                $aItems[] = $item;
            }
        }
        return $this->success($aItems);
    }
}