<?php

namespace Apps\Core_BetterAds\Installation\Version;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v420
 * @package Apps\Core_BetterAds\Installation\Version
 */
class v420
{
    public function process()
    {
        if (!db()->select('count(*)')->from(':module')->where([
            'module_id' => 'ad',
            'phrase_var_name' => 'module_apps'
        ])->executeField()) {
            $this->_updateSettings();
            $this->_updateAlias();
            $this->_updateAds();
            $this->_removeActionMenu();
            $this->_removeBlocks();
            $this->_updateMenu();
        }
    }

    private function _updateAds()
    {
        $aDeleteFields = [
            Phpfox::getT('better_ads') => [
                'disallow_controller',
                'user_group',
            ]
        ];

        foreach ($aDeleteFields as $sTable => $aFields) {
            foreach ($aFields as $sField) {
                if (db()->isField($sTable, $sField)) {
                    db()->dropField($sTable, $sField);
                }
            }
        }

        // use multi genders
        $genderType = db()->select('DATA_TYPE')->from('INFORMATION_SCHEMA.COLUMNS')->where([
            'table_name' => Phpfox::getT('better_ads'),
            'COLUMN_NAME' => 'gender'
        ])->executeField();

        if ($genderType == 'tinyint') {
            db()->changeField(':better_ads', 'gender', [
                'type' => 'VCHAR',
                'null' => true
            ]);
        }
    }

    private function _updateSettings()
    {
        // remove user group setting
        db()->delete(':user_group_setting', ['module_id' => 'ad', 'name' => 'better_ads_sponsor_items_price']);
    }

    private function _updateAlias()
    {
        // update betterad to ad
        db()->update(':module', ['phrase_var_name' => 'module_apps', 'menu' => ''],
            ['module_id' => 'ad']);
    }

    private function _removeActionMenu()
    {
        db()->delete(':menu', ['m_connection' => 'ad']);
    }

    private function _removeBlocks()
    {
        $aOldBlocks = [
            [
                'm_connection' => 'groups.index',
                'component' => 'sponsored_groups',
            ],
            [
                'm_connection' => 'pages.index',
                'component' => 'sponsored_pages',
            ],
            [
                'm_connection' => 'photo.index',
                'component' => 'sponsored_photo',
            ],
            [
                'm_connection' => 'blog.index',
                'component' => 'sponsored_blog',
            ],
            [
                'm_connection' => 'event.index',
                'component' => 'sponsored_event',
            ],
            [
                'm_connection' => 'marketplace.index',
                'component' => 'sponsored_marketplace',
            ],
            [
                'm_connection' => 'poll.index',
                'component' => 'sponsored_poll',
            ],
            [
                'm_connection' => 'quiz.index',
                'component' => 'sponsored_quiz',
            ],
        ];

        foreach ($aOldBlocks as $aOldBlock) {
            db()->delete(Phpfox::getT('block'), [
                'm_connection' => $aOldBlock['m_connection'],
                'component' => $aOldBlock['component']
            ]);
        }
    }

    private function _updateMenu()
    {
        db()->update(':menu', ['url_value' => 'ad.manage'], ['m_connection' => 'footer', 'module_id' => 'ad']);
    }
}
