<?php

namespace Apps\P_SavedItems\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Phpfox;

class SavedItemsCollectionResource extends ResourceBase
{
    const RESOURCE_NAME = "saveditems-collection";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'saveditems';

    protected $idFieldName = 'collection_id';

    public $name;
    public $image;
    public $total_item;
    public $user;
    public $collection_id;

    public function getExtra()
    {
        if (empty($this->extra)) {
            $isOwner = (int)$this->rawData['user_id'] == Phpfox::getUserId();
            $canEdit = $canDelete = false;
            if ($isOwner) {
                $canEdit = Phpfox::getUserParam('saveditems.can_edit_collection');
                $canDelete = Phpfox::getUserParam('saveditems.can_delete_collection');
            }

            $this->extra = [
                'can_edit' => Phpfox::getUserParam('saveditems.can_edit_collection') && $isOwner,
                'can_delete' => Phpfox::getUserParam('saveditems.can_delete_collection') && $isOwner,
                'can_action' => $canEdit || $canDelete
            ];
        }
        return $this->extra;
    }

    public function getImage()
    {
        if (empty($this->image)) {
            if (!empty($this->rawData['image_path'])) {
                $rawData = $this->rawData;
                $this->image = Phpfox::getLib('image.helper')->display([
                    'path' => 'saveditems.url_pic',
                    'server_id' => $rawData['image_server_id'],
                    'file' => $rawData['image_path'],
                    'return_url' => true
                ]);
            } else {
                $this->image = Phpfox::getParam('saveditems.default_collection_photo');
            }
        }
        return $this->image;
    }

    public function getLink()
    {
        if (empty($this->link)) {
            $this->link = Phpfox::permalink('saved.collection', $this->id, $this->name);
        }
        return $this->link;
    }

    public function getCreationDate()
    {
        if (empty($this->creation_date)) {
            $this->creation_date = $this->convertDatetime($this->rawData['created_time']);
        }
        return $this->creation_date;
    }

    public function getModificationDate()
    {
        if (empty($this->modification_date)) {
            $this->modification_date = $this->convertDatetime($this->rawData['updated_time']);
        }
        return $this->modification_date;
    }

    public function getMobileSettings($params = [])
    {
        $acl = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl();
        $permission = $acl->getPermissions();
        $l = $this->getLocalization();

        return self::createSettingForResource([
            'acl' => $permission,
            'resource_name' => $this->resource_name,
            'schema' => [
                'definition' => [],
            ],
            'search_input' => [
                'can_search' => false
            ],
            'sort_menu' => [],
            'filter_menu' => [],
            'list_view' => [
                'noItemMessage' => [
                    'image' => $this->getAppImage('no-item'),
                    'label' => $l->translate('saveditems_start_adding_new_items_by_create_new_stuffs'),
                    'action' => [
                        'value' => 'saveditems-collection/create_collection',
                        'add_new_items' => 'saveditems_add_new_items'
                        ],
                    ],
                'numColumns' => 2,
                'layout' => 'grid_view'
            ],
            'app_menu' => [
                [
                    'label' => $l->translate('saveditems_my_collections'),
                    'params' => [],
                    'ordering' => 2,
                    'module_name' => 'saveditems',
                    'resource_name' => $this->getResourceName(),
                    'navigate' => 'moduleHome',
                ]
            ],
            'action_menu' => [
                [
                    'label' => $l->translate('edit'),
                    'value' => Screen::ACTION_EDIT_ITEM,
                    'acl' => 'can_edit'
                ],
                [
                    'label' => $l->translate('delete'),
                    'value' => Screen::ACTION_DELETE_ITEM,
                    'acl' => 'can_delete'
                ],
            ],
            'forms' => [
                'editItem' => [
                    'headerTitle' => $l->translate('saveditems_edit_collection'),
                    'apiUrl' => UrlUtility::makeApiUrl('saveditems-collection/form/:id'),
                ],
            ],
        ]);
    }
}