<?php

namespace Apps\P_SavedItems\Service;

use Phpfox;
use Phpfox_Plugin;
use Phpfox_Service;

/**
 * Class Callback
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Service
 */
class Callback extends Phpfox_Service
{
    /**
     * It is used to save item from other apps
     * @param $params
     * @return bool
     */
    public function saveItem($params)
    {
        if (!Phpfox::getUserParam('saveditems.can_save_item') || empty($params['type_id']) || empty($params['item_id'])) {
            return false;
        }

        return Phpfox::getService('saveditems.process')->save($params);
    }

    /**
     * It is used to update some information of saved item
     * @param $params
     * @return bool
     */
    public function updateSavedItem($params)
    {
        if (empty($params['current_type_id']) || empty($params['current_item_id'])) {
            return false;
        }
        $update = [
            'link' => !empty($params['link']) ? $params['link'] : '',
        ];

        if (!empty($params['new_type_id']) && !empty($params['new_item_id'])) {
            $update = array_merge($update, [
                'type_id' => $params['type_id'],
                'item_id' => $params['item_id'],
            ]);
        }

        (($sPlugin = Phpfox_Plugin::get('saveditems.service_callback_updatesaveditem_start')) ? eval($sPlugin) : false);

        db()->update(Phpfox::getT('saved_items'), $update,
            'type_id = "' . $params['current_type_id'] . '" AND item_id = ' . (int)$params['current_item_id'] . ' AND user_id = ' . (!empty($params['user_id']) ? (int)$params['user_id'] : Phpfox::getUserId()));

        (($sPlugin = Phpfox_Plugin::get('saveditems.service_callback_updatesaveditem_end')) ? eval($sPlugin) : false);

        return true;
    }

    public function getProfileMenu($aUser)
    {
        list($iCntCollection) = Phpfox::getService('saveditems.collection')->getCallbackCollections($aUser);
        if (Phpfox::getParam('profile.show_empty_tabs') == false && $iCntCollection == 0) {
            return false;
        }

        $aMenus[] = [
            'phrase' => _p('saveditems_saved_collections'),
            'url' => 'profile.saveditems',
            'total' => $iCntCollection,
            'icon' => 'feed/blog.png',
            'icon_class' => 'ico ico-bookmark-o'
        ];

        return $aMenus;
    }

    public function getNotificationCollection_Addfriend($aNotification)
    {
        $aCols = Phpfox::getService('saveditems.collection')->get('collection_id = ' . $aNotification['item_id']);
        $aCollection = array_shift($aCols);
        if (!empty($aCollection['collection_id'])) {
            return [
                'link' => Phpfox::getLib('url')->makeUrl('saved.collection.' . $aNotification['item_id']),
                'message' => _p("saveditems_full_name_add_you_to_saved_item_collection_name", [
                    'full_name' => $aNotification['full_name'],
                    'name' => $aCollection['name']
                ]),
                'icon' => ''
            ];
        }
        return [];
    }

    public function getNotificationSettings()
    {
        return [
            'saveditems.enable_email_notification' => [
                'phrase' => _p('saveditems_notification_for_saveditems'),
                'default' => 1
            ]
        ];
    }

    public function getDashboardActivity()
    {
        list($iCntCollection) = Phpfox::getService('saveditems.collection')->getCallbackCollections(['user_id' => Phpfox::getUserId()]);
        return [
            _p('saveditems_saved_collections') => $iCntCollection
        ];
    }
}