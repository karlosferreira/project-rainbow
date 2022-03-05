<?php

namespace Apps\P_SavedItems\Installation\Version;

use Phpfox;

class v411
{
    public function process()
    {
        $this->_removeMenu();
        $this->_addMenuForMobile();
        $this->_addPrivacyField();
        $this->_addAddedUserField();
    }

    private function _removeMenu()
    {
        $menuTable = Phpfox::getT('menu');
        $menuId = db()->select('menu_id')->from($menuTable)->where([
                'module_id' => 'saveditems',
                'm_connection' => 'main',
                'url_value' => 'saved'
            ])->execute('getSlaveField');
        if ($menuId) {
            db()->delete($menuTable, ['menu_id' => (int)$menuId]);
        }
    }

    private function _addMenuForMobile()
    {
        // Mobile API Installing Support
        if (db()->tableExists(Phpfox::getT('mobile_api_menu_item'))) {
            $menuExist = db()->select("item_id")->from(Phpfox::getT('mobile_api_menu_item'))->where(['path' => 'saveditems'])->execute('getField');

            if (!$menuExist) {
                db()->insert(Phpfox::getT("mobile_api_menu_item"), [
                    "section_id" => 2,
                    "name" => "Saved Items",
                    "item_type" => 1,
                    "is_active" => 1,
                    "icon_name" => 'bookmark-o',
                    "icon_family" => 'Lineficon',
                    "icon_color" => '#ff564a',
                    "path" => 'saveditems',
                    "is_system" => 1,
                    "module_id" => 'saveditems',
                    "ordering" => 17
                ]);
            } else {
                db()->update(Phpfox::getT("mobile_api_menu_item"), [
                    "module_id" => 'saveditems'
                ], ['path' => 'saveditems']);
            }
        }
    }

    private function _addPrivacyField()
    {
        if (db()->tableExists(Phpfox::getT('saved_collection'))) {
            if (!db()->isField(':saved_collection', 'privacy')) {
                db()->query("ALTER TABLE  `" . Phpfox::getT('saved_collection') . "` ADD `privacy` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT '0'");
            }
        }
    }

    public function _addAddedUserField()
    {
        if (db()->tableExists(Phpfox::getT('saved_collection_data'))) {
            if (!db()->isField(':saved_collection_data', 'user_id')) {
                db()->query("ALTER TABLE  `" . Phpfox::getT('saved_collection_data') . "` ADD `user_id` INT( 11 ) UNSIGNED NOT NULL");
            }
        }
    }
}