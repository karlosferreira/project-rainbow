<?php

namespace Apps\P_SavedItems\Api\Resource;

use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\P_SavedItems\Api\Form\SavedItemsSearchForm;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Phpfox;

class SavedItemsResource extends ResourceBase
{
    const RESOURCE_NAME = "saveditems";
    const SMART_TAB_BAR = 'smart_tab_bar';
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'saveditems';

    protected $idFieldName = 'saved_id';

    public $user;
    public $is_saved;
    public $item_type;
    public $item_id;
    public $title;
    public $additional_information;
    public $image;
    public $is_unopened;
    public $item_type_name;
    public $saved_id;
    public $belong_to_collection;
    public $statistic;
    public $default_collection_name;
    public $default_collection_id;
    public $in_collection;

    public function getMobileSettings($params = [])
    {
        $acl = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl();
        $permission = $acl->getPermissions();
        $searchFilter = (new SavedItemsSearchForm());
        $searchFilter->setLocal($this->getLocalization());
        $l = $this->getLocalization();

        return self::createSettingForResource([
            'acl' => $permission,
            'resource_name' => $this->resource_name,
            'schema' => [
                'definition' => [],
            ],
            'search_input' => [
                'placeholder' => $l->translate('saveditems_search_your_saved_items_dot'),
            ],
            'sort_menu' => [
                'title' => $l->translate('sort_by'),
                'options' => $searchFilter->getSortOptions()
            ],
            'filter_menu' => [
                'title' => $l->translate('filter_by'),
                'options' => [
                    ['value' => 'all-time', 'label' => $l->translate('all_time')],
                    ['value' => 'this-month', 'label' => $l->translate('this_month')],
                    ['value' => 'this-week', 'label' => $l->translate('this_week')],
                    ['value' => 'today', 'label' => $l->translate('today')],
                ]
            ],
            'list_view' => [
                'noItemMessage' => [
                    'image' => $this->getAppImage('no-item'),
                    'label' => $l->translate('saveditems_no_saved_items_found'),
                    'type' => 'small',
                ],
                'alignment' => 'left'
            ],
            'app_menu' => [
                [
                    'label' => $l->translate('saveditems_all_saved_items'),
                    'params' => [],
                    'ordering' => 1,
                    'module_name' => 'saveditems',
                    'navigate' => 'moduleHome',
                    'resource_name' => $this->getResourceName()
                ],
            ],
            'action_menu' => [
                [
                    'label' => $l->translate('share'),
                    'value' => Screen::ACTION_SHARE_ITEM,
                    'acl' => 'can_share'
                ],
                [
                    'label' => $l->translate('saveditems_mark_as_opened'),
                    'value' => 'saveditems/open',
                    'show' => 'is_unopened',
                ],
                [
                    'label' => $l->translate('saveditems_mark_as_unopened'),
                    'value' => 'saveditems/open',
                    'show' => '!is_unopened',
                ],
                [
                    'label' => $l->translate('saveditems_add_to_collections'),
                    'value' => 'saveditems/add_to_collections_in_saved_app',
                    'show' => 'is_saved'
                ],
                [
                    'label' => $l->translate('saveditems_unsave'),
                    'value' => 'saveditems/unsave',
                    'style' => 'danger',
                    'show' => '!belong_to_collection',
                    'acl' => 'can_save_item'
                ],
                [
                    'label' => $l->translate('saveditems_unsave'),
                    'value' => 'saveditems/unsave_confirmation',
                    'style' => 'danger',
                    'show' => 'belong_to_collection',
                    'acl' => 'can_save_item'
                ],
            ],
            'forms' => [
                'addToCollections' => [
                    'apiUrl' => 'mobile/saveditems/collection-form',
                    'headerTitle' => $l->translate('saveditems_add_to_collections'),
                    'navigateAction' => 'goBack',
                ],
            ]
        ]);
    }

    public function getSavedId()
    {
        if (empty($this->saved_id) && !empty($this->rawData['saved_id'])) {
            $this->saved_id = $this->rawData['saved_id'];
        }
        return $this->saved_id;
    }

    public function getDefaultCollectionName()
    {
        if (empty($this->default_collection_name) && isset($this->rawData['collections'])) {
            if (!empty($this->rawData['collections']['default'])) {
                $this->default_collection_name = $this->rawData['collections']['default']['name'];
                $this->default_collection_id = $this->rawData['collections']['default']['collection_id'];
            } else {
                $defaultCollection = array_shift($this->rawData['collections']);
                $this->default_collection_name = $defaultCollection['name'];
                $this->default_collection_id = $defaultCollection['collection_id'];
            }
        }

        return $this->default_collection_name;
    }

    public function getStatistic()
    {
        $this->statistic = [
            'total_collection' => 0
        ];

        if (isset($this->rawData['collections'])) {
            if (!empty($this->rawData['collections']['default'])) {
                $this->statistic['total_collection'] = (!empty($this->rawData['collections']['other_collections']) ? count($this->rawData['collections']['other_collections']) : 0) + 1;
            } else {
                $this->statistic['total_collection'] = count($this->rawData['collections']);
            }
        }

        return $this->statistic;
    }

    public function getTitle()
    {
        $title = $this->parse->cleanOutput(!empty($this->rawData['item_title']) ? $this->rawData['item_title'] : $this->rawData['full_name']);
        return $title;
    }

    public function getImage()
    {
        $rawData = $this->rawData;
        if (empty($rawData['item_display_photo']) && !(($rawData['item_type_id'] == 'link' && !empty($rawData['item_photo'])) || !empty($rawData['user_image']))) {
            $image = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/default-images/user/no_image.png';
        } else {
            if ($rawData['item_type_id'] == 'link' && !empty($rawData['item_photo'])) {
                if (preg_match('/img.youtube.com/', $rawData['item_photo']) && !preg_match('/https/',
                        trim($rawData['item_photo'], '/'))) {
                    $rawData['item_photo'] = 'https://' . trim($rawData['item_photo'], '/');
                }
                $image = $rawData['item_photo'];
            } elseif (!empty($rawData['item_display_photo'])) {
                $image = $rawData['item_display_photo'];
            } else {
                $image = Phpfox::getLib('image.helper')->display([
                    'server_id' => $rawData['user_server_id'],
                    'path' => 'core.url_user',
                    'file' => $rawData['user_image'],
                    'suffix' => '_200_square',
                    'return_url' => true
                ]);
            }
        }
        return $image;
    }

    public function getItemType()
    {
        return $this->rawData['item_type_id'];
    }

    public function getIsUnopened()
    {
        return (int)$this->rawData['unopened'];
    }

    public function getItemTypeName()
    {
        return $this->rawData['item_name'];
    }

    public function getIsSaved()
    {
        if (!isset($this->is_saved)) {
            $this->is_saved = (int)Phpfox::getService('saveditems')->isSaved($this->rawData['item_type_id'],
                $this->rawData['item_id']);
        }
        return $this->is_saved;
    }

    public function getExtra()
    {
        return [
            'can_save_item' => Phpfox::getUserParam('saveditems.can_save_item') && Phpfox::getUserBy('profile_page_id') == 0,
            'can_share' => Phpfox::isModule('share') && Phpfox::getUserParam('share.can_share_items') && isset($this->rawData['item_privacy']) && $this->rawData['item_privacy'] == 0 && !Phpfox::getService('user.block')->isBlocked(null,
                    $this->rawData['item_user_id'])
        ];
    }

    public function getAdditionalInformation()
    {
        if (empty($this->additional_information) && !empty($this->rawData['extra']['additional_information'])) {
            $additionalInformation = $this->rawData['extra']['additional_information'];
            if ($additionalInformation['type'] == 'date_time' && is_numeric($additionalInformation['value'])) {
                $additionalInformation['value'] = Phpfox::getLib('date')->convertTime($additionalInformation['value']);
            } elseif ($additionalInformation['type'] == 'link') {
                $additionalInformation['title'] = isset($additionalInformation['title']) ? $additionalInformation['title'] : $additionalInformation['value'];
            } elseif (!in_array($additionalInformation['type'], ['price', 'link', 'date_time'])) {
                $value = str_replace(["\r", "\n"], '',
                    $this->parse->parseTwaEmoji($this->parse->cleanOutput(Phpfox::getLib('parse.bbcode')->stripCode(strip_tags($additionalInformation['value'])))));
                $additionalInformation = array_merge($additionalInformation, [
                    'value' => $value,
                    'type' => 'other'
                ]);
            }
            $this->additional_information = $additionalInformation;
        }
        return $this->additional_information;
    }

    public function getBelongToCollection()
    {
        if (!isset($this->belong_to_collection)) {
            $this->belong_to_collection = isset($this->rawData['belong_to_collection']) ? $this->rawData['belong_to_collection'] : isset($this->rawData['collections']);
        }
        return $this->belong_to_collection;
    }

    public function getLink()
    {
        $raw = $this->rawData;
        $this->link = !empty($raw['link']) ? $raw['link'] : (!empty($raw['item_link']) ? $raw['item_link'] : $raw['saved_link']);
        return $this->link;
    }
}