<?php

namespace Apps\Core_MobileApi\Installation\Version;

use Phpfox;

class v410
{

    private $defaultMenu;

    public function __construct()
    {
        $this->defaultMenu = [
            [
                'name'       => '',
                'item_type'  => 'section-header',
                'icon_name'  => '',
                'icon_color' => '',
                'path'       => '/',
                'section_id' => 1,
                'module_id'  => 'core'
            ],
            [
                'name'       => 'system_settings',
                'item_type'  => 'header',
                'icon_name'  => 'gear-o',
                'icon_color' => '#686868',
                'path'       => 'settings',
                'section_id' => 1,
                'module_id'  => 'core'
            ],
            [
                'name'       => 'FAVOURITES',
                'item_type'  => 'section-item',
                'icon_name'  => '',
                'icon_color' => '',
                'path'       => '/',
                'section_id' => 2,
                'module_id'  => 'core'
            ],
            [
                'name'       => 'Friends',
                'item_type'  => 'item',
                'icon_name'  => 'user1-two',
                'icon_color' => '#2681d5',
                'path'       => 'friend',
                'section_id' => 2,
                'module_id'  => 'core'
            ],
            [
                'name'       => 'Videos',
                'item_type'  => 'item',
                'icon_name'  => 'videocam',
                'icon_color' => '#ff564a',
                'path'       => 'video',
                'section_id' => 2,
                'module_id'  => 'v'
            ],
            [
                'name'       => 'Pages',
                'item_type'  => 'item',
                'icon_name'  => 'flag-waving',
                'icon_color' => '#ff891f',
                'path'       => 'pages',
                'section_id' => 2,
                'module_id'  => 'pages'
            ],
            [
                'name'       => 'Marketplace',
                'item_type'  => 'item',
                'icon_name'  => 'shopbasket',
                'icon_color' => '#a1560f',
                'path'       => 'marketplace',
                'section_id' => 2,
                'module_id'  => 'marketplace'
            ],
            [
                'name'       => 'Quizzes',
                'item_type'  => 'item',
                'icon_name'  => 'check-square-o3',
                'icon_color' => '#0e5bb4',
                'path'       => 'quiz',
                'section_id' => 2,
                'module_id'  => 'quiz'
            ],
            [
                'name'       => 'Forums',
                'item_type'  => 'item',
                'icon_name'  => 'comment-square',
                'icon_color' => '#ffba27',
                'path'       => 'forum',
                'section_id' => 2,
                'module_id'  => 'forum'
            ],
            [
                'name'       => 'Blogs',
                'item_type'  => 'item',
                'icon_name'  => 'newspaper-alt',
                'icon_color' => '#0097fc',
                'path'       => 'blog',
                'section_id' => 2,
                'module_id'  => 'blog'
            ],
            [
                'name'       => 'Members',
                'item_type'  => 'item',
                'icon_name'  => 'user1-three',
                'icon_color' => '#1d6f96',
                'path'       => 'user',
                'section_id' => 2,
                'module_id'  => 'user'
            ],
            [
                'name'       => 'Photos',
                'item_type'  => 'item',
                'icon_name'  => 'photos-alt',
                'icon_color' => '#00b7f4',
                'path'       => 'photo',
                'section_id' => 2,
                'module_id'  => 'photo'
            ],
            [
                'name'       => 'Events',
                'item_type'  => 'item',
                'icon_name'  => 'calendar',
                'icon_color' => '#ff5319',
                'path'       => 'event',
                'section_id' => 2,
                'module_id'  => 'event'
            ],
            [
                'name'       => 'Groups',
                'item_type'  => 'item',
                'icon_name'  => 'user3-three',
                'icon_color' => '#b132fb',
                'path'       => 'groups',
                'section_id' => 2,
                'module_id'  => 'groups'
            ],
            [
                'name'       => 'Polls',
                'item_type'  => 'item',
                'icon_name'  => 'barchart',
                'icon_color' => '#00d475',
                'path'       => 'poll',
                'section_id' => 2,
                'module_id'  => 'poll'
            ],
            [
                'name'       => 'Music',
                'item_type'  => 'item',
                'icon_name'  => 'music-album',
                'icon_color' => '#ff4986',
                'path'       => 'music',
                'section_id' => 2,
                'module_id'  => 'music'
            ],
            [
                'name'       => '',
                'item_type'  => 'section-footer',
                'icon_name'  => '',
                'icon_color' => '',
                'path'       => '/',
                'section_id' => 3,
                'module_id'  => 'core'
            ],
            [
                'name'       => 'sign_out',
                'item_type'  => 'footer',
                'icon_name'  => 'signout',
                'icon_color' => '#686868',
                'path'       => 'logout',
                'section_id' => 3,
                'module_id'  => 'core'
            ],
        ];
    }

    public function process()
    {
        if (!db()->isField(':temp_file', 'extra_info')) {
            db()->addField([
                'table'   => Phpfox::getT('temp_file'),
                'field'   => 'extra_info',
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
            ]);
        }
        $iCnt = db()->select('COUNT(*)')
            ->from(':mobile_api_menu_item')
            ->execute('getField');
        if (!$iCnt) {
            $order = 1;
            foreach ($this->defaultMenu as $menu) {
                db()->insert(':mobile_api_menu_item', array_merge($menu, ['ordering' => $order]));
                $order++;
            }
        }

        //Insert default clients
        if (db()->tableExists(Phpfox::getT('oauth_clients'))) {
            $iCnt = db()->select('COUNT(*)')
                ->from(':oauth_clients')
                ->where('client_id = \'mobileapi\' AND client_secret = \'738ab5b83c902a7b81860e05811fd5cd65e95f72\'')
                ->execute('getField');
            if (!$iCnt) {
                db()->insert(':oauth_clients', [
                    'client_id'     => 'mobileapi',
                    'client_name'   => 'client_name',
                    'client_secret' => '738ab5b83c902a7b81860e05811fd5cd65e95f72',
                    'redirect_uri'  => 'http://localhost/',
                    'time_stamp'    => PHPFOX_TIME
                ]);
            }
        }
    }
}