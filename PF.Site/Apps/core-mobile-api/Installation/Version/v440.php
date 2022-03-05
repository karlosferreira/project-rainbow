<?php

namespace Apps\Core_MobileApi\Installation\Version;

use Phpfox;

class v440
{
    public function process()
    {
        $tableName = Phpfox::getT('mobile_api_menu_item');
        if (db()->tableExists($tableName)) {
            $helperMenu = db()->select('COUNT(*)')->from($tableName)->where(['item_type' => 'helper'])->executeField();
            if (!db()->isField($tableName, 'is_url')) {
                db()->addField([
                    'table'     => $tableName,
                    'field'     => 'is_url',
                    'type'      => 'tinyint',
                    'attribute' => '(1)',
                    'default'   => 0,
                ]);
            }
            if (!$helperMenu) {
                db()->changeField($tableName, 'item_type', [
                    'type'  => 'enum',
                    'extra' => '(\'item\',\'section-item\',\'header\',\'section-header\',\'footer\',\'section-footer\',\'section-helper\',\'helper\') DEFAULT \'item\''
                ]);
                $menus = [
                    [
                        'name'       => 'terms_and_privacy',
                        'item_type'  => 'section-helper',
                        'icon_name'  => '',
                        'icon_color' => '',
                        'path'       => '/',
                        'section_id' => 4,
                        'module_id'  => 'core',
                        'ordering'   => 1
                    ],
                    [
                        'name'       => 'terms_and_policies',
                        'item_type'  => 'helper',
                        'icon_name'  => 'address-book',
                        'icon_color' => '#686868',
                        'path'       => 'terms',
                        'section_id' => 4,
                        'module_id'  => 'core',
                        'is_url'     => 1,
                        'ordering'   => 2
                    ],
                    [
                        'name'       => 'privacy_shortcuts',
                        'item_type'  => 'helper',
                        'icon_name'  => 'lock',
                        'icon_color' => '#686868',
                        'path'       => 'policy',
                        'section_id' => 4,
                        'module_id'  => 'core',
                        'is_url'     => 1,
                        'ordering'   => 3
                    ],
                ];
                foreach ($menus as $menu) {
                    db()->insert($tableName, $menu);
                }
            }
        }
    }
}