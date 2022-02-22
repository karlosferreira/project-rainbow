<?php

namespace Apps\phpFox_Shoutbox\Block;

use Phpfox_Component;
use Phpfox;
use User_Service_User;
use Apps\phpFox_Shoutbox\Service\Shoutbox as sb;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Chat
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\phpFox_Shoutbox\Block
 */
class Chat extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (!Phpfox::getUserParam('shoutbox.shoutbox_can_view')) {
            return false;
        }
        $bIsAdmin = Phpfox::isAdmin();
        $bCanShare = Phpfox::getUserParam('shoutbox.shoutbox_can_share');
        $aParentModule = [];

        //On Pages or Groups
        if (defined("PHPFOX_PAGES_ITEM_TYPE")) {
            $aParentModule = $this->getParam('aParentModule');
            if (!$aParentModule) {
                return false;
            }
            $parentItemId = $aParentModule['item_id'];
            if (PHPFOX_PAGES_ITEM_TYPE == 'pages') {
                if (!Phpfox::isAppActive('Core_Pages') || !Phpfox::getParam('shoutbox.shoutbox_enable_pages')) {
                    return false;
                }
                if (!$bIsAdmin) {
                    if (Phpfox::getService('pages')->isAdmin($parentItemId)) {
                        $bIsAdmin = true;
                    }
                }
                //In pages, check can view shoutbox
                if (!Phpfox::getService('pages')->hasPerm($parentItemId, 'shoutbox.view_shoutbox')) {
                    return false;
                }
                //In pages, check can share shoutbox
                if (!Phpfox::getService('pages')->hasPerm($parentItemId, 'shoutbox.share_shoutbox')) {
                    $bCanShare = false;
                }
            } elseif (PHPFOX_PAGES_ITEM_TYPE == 'groups') {
                if (!Phpfox::isAppActive('PHPfox_Groups') || !Phpfox::getParam('shoutbox.shoutbox_enable_groups')) {
                    return false;
                }

                if (!$bIsAdmin) {
                    if (Phpfox::getService('groups')->isAdmin($parentItemId)
                    ) {
                        $bIsAdmin = true;
                    }
                }
                //In groups, check can view shoutbox
                if (!Phpfox::getService('groups')->hasPerm($parentItemId, 'shoutbox.view_shoutbox')) {
                    return false;
                }
                //In groups, check can share shoutbox
                if (!Phpfox::getService('groups')->hasPerm($parentItemId, 'shoutbox.share_shoutbox')) {
                    $bCanShare = false;
                }
            }

        } else {//On Index
            if (!Phpfox::getParam('shoutbox.shoutbox_enable_index')) {
                return false;
            }
        }
        $sModuleId = (isset($aParentModule['module_id'])) ? $aParentModule['module_id'] : 'index';
        $iItemId = (isset($aParentModule['item_id'])) ? $aParentModule['item_id'] : '0';
        $aShoutboxes = sb::get()->getShoutboxes($sModuleId, $iItemId);
        $aUser = Phpfox::getService('user')->getUser(Phpfox::getUserId());
        if (isset($aShoutboxes[0])) {
            Phpfox::removeCookie('last_shoutbox_id');
            Phpfox::setCookie('last_shoutbox_id', $aShoutboxes[0]['shoutbox_id']);
        } elseif (!Phpfox::isUser()) {
            return false;
        }
        $this->template()
            ->assign([
                'sHeader' => _p('shoutbox'),
                'aShoutboxes' => $aShoutboxes,
                'aUser' => $aUser,
                'aIsAdmin' => $bIsAdmin,
                'sModuleId' => $sModuleId,
                'iItemId' => $iItemId,
                'iUserId' => Phpfox::getUserId(),
                'bCanShare' => $bCanShare
            ]);
        return 'block';
    }
}