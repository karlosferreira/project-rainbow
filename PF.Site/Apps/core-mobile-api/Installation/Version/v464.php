<?php

namespace Apps\Core_MobileApi\Installation\Version;

use Phpfox;

class v464
{
    public function process()
    {
        $tableName = Phpfox::getT('mobile_api_menu_item');
        if (db()->tableExists($tableName)) {
            $helperMenu = db()->select('COUNT(*)')->from($tableName)->where(['module_id' => 'contact', 'path' => 'contact'])->executeField();
            if (!$helperMenu) {
                $menus = [
                    [
                        'name'       => 'mobile_contact_us',
                        'item_type'  => 'helper',
                        'icon_name'  => 'question-circle',
                        'icon_color' => '#686868',
                        'path'       => 'contact',
                        'section_id' => 4,
                        'module_id'  => 'contact',
                        'is_url'     => 1,
                        'ordering'   => 4
                    ]
                ];
                foreach ($menus as $menu) {
                    db()->insert($tableName, $menu);
                }
            }
        }
    }
}