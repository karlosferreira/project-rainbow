<?php

namespace Apps\P_SavedItems;

use Core\App;

/**
 * Class Install
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    protected function setId()
    {
        $this->id = 'P_SavedItems';
    }

    protected function setAlias()
    {
        $this->alias = 'saveditems';
    }

    protected function setName()
    {
        $this->name = _p('module_saveditems');
    }

    protected function setVersion()
    {
        $this->version = '4.1.1';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.7.2';
    }

    protected function setSettings()
    {
        $this->settings = [
            'open_popup_in_item_detail' => [
                'var_name' => 'open_popup_in_item_detail',
                'info' => 'Turn on Saved Item Popup in detail page',
                'description' => 'Turn on this setting if you want to save to specific collections. Turn off to save an item quickly, no need to select collections.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => 1,
            ],
            'open_confirmation_in_item_detail' => [
                'var_name' => 'open_confirmation_in_item_detail',
                'info' => 'Turn on Unsaved Item Popup in detail page',
                'description' => 'Turn on this setting if you want to display confirmation popup. Turn off to hide confirmation popup.',
                'type' => 'boolean',
                'value' => '0',
                'ordering' => 2,
            ],
        ];
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'can_save_item' => [
                'var_name' => 'can_save_item',
                'info' => 'Can members of this user group save items?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 1,
                    5 => 0,
                ],
            ],
            'can_create_collection' => [
                'var_name' => 'can_create_collection',
                'info' => 'Can members of this user group create a collection?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 1,
                    5 => 0,
                ],
            ],
            'can_edit_collection' => [
                'var_name' => 'can_edit_collection',
                'info' => 'Can members of this user group edit a collection?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 1,
                    5 => 0,
                ],
            ],
            'can_delete_collection' => [
                'var_name' => 'can_delete_collection',
                'info' => 'Can members of this user group delete a collection?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 1,
                    5 => 0,
                ],
            ],
        ];
    }

    protected function setComponent()
    {
        $this->component = [
            'block' => [
                'category' => '',
                'collection.recent-update' => '',
            ],
            'controller' => [
                'index' => 'saveditems.index',
                'collections' => 'saveditems.collections',
            ]
        ];
    }

    protected function setComponentBlock()
    {
        $this->component_block = [
            'Category' => [
                'type_id' => '0',
                'm_connection' => 'saveditems.index',
                'component' => 'category',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Recently Updated' => [
                'type_id' => '0',
                'm_connection' => 'saveditems.index',
                'component' => 'collection.recent-update',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '2',
            ],
        ];
    }

    protected function setPhrase()
    {
        $this->phrase = $this->_app_phrases;
    }

    protected function setOthers()
    {
        $this->menu = [];

        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->database = ['Saved_Items', 'Collection', 'CollectionData', 'CollectionFriend'];

        $this->_apps_dir = "p-saved-items";
        $this->_admin_cp_menu_ajax = false;
    }
}