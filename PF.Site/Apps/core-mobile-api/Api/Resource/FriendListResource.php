<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;

class FriendListResource extends ResourceBase
{
    public $resource_name = 'friend_list';

    public $module_name = 'friend';
    protected $idFieldName = 'list_id';
    public $name;

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();

        return self::createSettingForResource([
            'resource_name'    => $this->getResourceName(),
            'urls.base'        => 'mobile/friend/list',
            'can_filter'       => false,
            'can_sort'         => false,
            'can_search'       => false,
            'fab_buttons'      => false,
            'list_view'        => [
                'item_view' => 'friend_list',
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_items_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action'    => [
                        'resource_name' => $this->getModuleName(),
                        'module_name'   => $this->getModuleName(),
                        'value'         => '@friend/add-list',
                        'label'         => $l->translate('add_new_list')
                    ]
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ]
            ],
            'forms'            => [
                'edit_name' => [
                    'apiUrl'      => 'mobile/friend/list/form/:id',
                    'headerTitle' => $l->translate('edit'),
                ]
            ],
            'detail_view'      => [
                'component_name' => 'friend_list_detail',
            ],
            'app_menu'         => [
                ['label' => $l->translate('friend_lists')],
            ],
            'friend_list_item' => [
                'item_view'     => 'friend_list_item',
                'noItemMessage' => $l->translate('no_friends_found'),
            ],
            'action_menu'      => [
                ['label' => $l->translate('edit_name'), 'value' => '@friend/edit-list'],
                ['label' => $l->translate('add_more_friends'), 'value' => '@friend/add-to-list'],
                ['label' => $l->translate('delete_list'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger'],
            ],
        ]);
    }
}