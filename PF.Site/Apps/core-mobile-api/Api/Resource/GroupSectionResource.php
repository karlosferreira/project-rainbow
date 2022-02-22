<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;


class GroupSectionResource extends ResourceBase
{
    public $resource_name = 'group_section';

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();

        return self::createSettingForResource([
            'resource_name' => $this->resource_name,
            'urls.base'     => 'mobile/group-home',
            'fab_buttons'   => false,
            'search_input'  => [
                'placeholder'   => $l->translate('search_groups'),
                'resource_name' => 'groups'
            ],
            'schema'        => [
                'extras'     => ['item_resource_name' => 'groups'],
                'definition' => [
                    'main'  => 'group_type',
                    'items' => 'groups[]',
                ]
            ],
            'list_view'     => [
                'item_view'  => 'section_item',
                'item_props' => ['child_item_view' => 'groups_item_card_view'],
                'layout'     => Screen::LAYOUT_LIST_CARD_VIEW,
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_groups_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action'    => [
                        'resource_name' => 'groups',
                        'module_name'   => 'groups',
                        'value'         => Screen::ACTION_ADD,
                        'label'         => $l->translate('add_new_item')
                    ]
                ],
            ],
            'app_menu'      => [
                ['label' => $l->translate('groups')],
            ]
        ]);
    }
}