<?php

namespace Apps\PHPfox_Groups\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Pages_Pages;
use Phpfox_Plugin;
use Phpfox_Template;
use Phpfox_Url;

/**
 * Class Groups
 *
 * @package Apps\PHPfox_Groups\Service
 */
class Groups extends Phpfox_Pages_Pages
{
    private $_aPhotoPicSizes = [50, 120, 200, 500, 1024];

    public function getFacade()
    {
        return Phpfox::getService('groups.facade');
    }

    public function getWidgetById($widgetId)
    {
        if (empty($widgetId)) {
            return false;
        }

        return db()->select('*')
            ->from(':pages_widget', 'pw')
            ->join(Phpfox::getT('pages_widget_text'), 'pwt', 'pwt.widget_id = pw.widget_id')
            ->where([
                'pw.widget_id' => (int)$widgetId
            ])->executeRow();
    }

    public function getIntegratedItems()
    {
        if ($items = storage()->get('groups_integrate')) {
            $items = (array)$items->value;
        } else {
            $items = [];
        }
        return $items;
    }

    public function isActiveIntegration($moduleId)
    {
        $integratedItems = $this->getIntegratedItems();
        return empty($integratedItems) || !isset($integratedItems[$moduleId]) || !empty($integratedItems[$moduleId]);
    }

    public function sendMailToTaggedUsers($allTags, $feedId, $groupId)
    {
        $aOwner = Phpfox::getService('user')->getUser(Phpfox::getUserId());
        $sTagger = (isset($aOwner['full_name']) && $aOwner['full_name']) ? $aOwner['full_name'] : $aOwner['user_name'];
        $iFeedCommentId = db()->select('item_id')
            ->from(':pages_feed')
            ->where([
                'feed_id' => $feedId,
            ])->executeField();
        $sLink = rtrim($this->getUrl($groupId), '/') . '/wall/comment-id_' . $iFeedCommentId . '/';
        foreach ($allTags as $iUserId) {
            $this->mailToTagged($iUserId, $sTagger, $sLink, $groupId);
        }
    }

    public function mailToTagged($userId, $userName, $link, $groupId)
    {
        if (empty($userId) || empty($groupId)) {
            return;
        }

        static $groupTitles = [];
        if (!isset($groupTitles[$groupId])) {
            $groupTitles[$groupId] = Phpfox::getLib('parse.output')->clean(db()->select('title')
                ->from(':pages')
                ->where([
                    'page_id' => $groupId
                ])->executeField());
        }

        Phpfox::getLib('mail')->to($userId)
            ->subject(['groups_full_name_tagged_you_in_a_post_in_group_title_no_html', ['full_name' => $userName, 'title' => $groupTitles[$groupId]]])
            ->message(['groups_user_name_tagged_you_in_a_post_in_group_title_check_it_out', ['full_name' => $userName, 'title' => $groupTitles[$groupId], 'link' => $link]])
            ->notification('feed.tagged_in_post')
            ->send();
    }

    public function getPerms($iPage)
    {
        $aCallbacks = Phpfox::massCallback('getGroupPerms');
        $aPerms = [];
        $aUserPerms = $this->getPermsForPage($iPage);
        if ($aIntegrate = storage()->get('groups_integrate')) {
            $aIntegrate = (array)$aIntegrate->value;
        }
        if(isset($aIntegrate['v'])) { // check special case
            $aIntegrate['pf_video'] = $aIntegrate['v'];
            unset($aIntegrate['v']);
        }
        foreach ($aCallbacks as $aCallback) {
            foreach ($aCallback as $sId => $sPhrase) {
                $sModule = current(explode('.', $sId));
                if ($aIntegrate && array_key_exists($sModule, $aIntegrate) && !$aIntegrate[$sModule]) {
                    continue;
                }

                $hasDefault = is_array($sPhrase) && isset($sPhrase['default']);
                $phrase = is_array($sPhrase) && isset($sPhrase['phrase']) ?  $sPhrase['phrase'] : $sPhrase;
                $default = $hasDefault ? $sPhrase['default'] : Phpfox::getParam('groups.groups_default_item_privacy', 1);
                $params = [
                    'id' => $sId,
                    'phrase' => $phrase,
                    'is_active' => (isset($aUserPerms[$sId]) ? $aUserPerms[$sId] : $default),
                ];
                if ($hasDefault) {
                    $params['has_default'] = $hasDefault;
                }

                $aPerms[] = $params;
            }
        }

        return $aPerms;
    }

    public function checkIfGroupUser($profileUserId)
    {
        return db()->select('COUNT(*)')
            ->from(Phpfox::getT('user'), 'u')
            ->join(Phpfox::getT('pages'), 'p', 'p.page_id = u.profile_page_id')
            ->where('u.profile_page_id = ' . (int)$profileUserId . ' AND p.item_type = 1')
            ->execute('getSlaveField');
    }

    /**
     * Get invitations of a group
     *
     * @param $iGroupId
     *
     * @return array
     */
    public function getCurrentInvitesForGroup($iGroupId)
    {
        $aRows = $this->database()->select('*')
            ->from(Phpfox::getT('pages_invite'))
            ->where('page_id = ' . (int)$iGroupId . ' AND type_id = 1 AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveRows');

        $aInvites = [];
        foreach ($aRows as $aRow) {
            $aInvites[$aRow['invited_user_id']] = $aRow;
        }

        return $aInvites;
    }


    public function isGroupAvaiable()
    {
        return !empty($this->_aPage);
    }

    public function getUrl($iPageId, $sTitle = null, $sVanityUrl = null, $bIsGroup = false)
    {
        if ($sTitle === null && $sVanityUrl === null) {
            $aPage = $this->getPage($iPageId);
            $sVanityUrl = $aPage['vanity_url'];
        }

        if (!empty($sVanityUrl)) {
            return Phpfox_Url::instance()->makeUrl($sVanityUrl);
        }

        return Phpfox_Url::instance()->makeUrl('groups', $iPageId);
    }

    /**
     * @param int|array $iPage
     * @param string    $sPerm
     *
     * @return bool
     */
    public function hasPerm($iPage, $sPerm)
    {
        if (defined('PHPFOX_IS_PAGES_VIEW') && $sPerm == 'pf_video.share_videos' && ($aIntegrate = storage()->get('groups_integrate'))) {
            $aIntegrate = (array)$aIntegrate->value;
            if (array_key_exists('v', $aIntegrate) && !$aIntegrate['v']) {
                return false;
            }
        }
        if (Phpfox::isAdmin()) {
            return true;
        }
        if (defined('PHPFOX_IS_PAGES_VIEW') && Phpfox::getUserParam('core.can_view_private_items')) {
            return true;
        }

        if (defined('PHPFOX_POSTING_AS_PAGE')) {
            return true;
        }


        if (is_array($iPage) && isset($iPage['page_id'])) {
            $aPage = $iPage;
        } else {
            $aPage = $this->getPage($iPage);
        }
        $aPerms = $this->getPermsForPage($aPage['page_id']);
        if (!isset($aPerms[$sPerm]) && !empty($aDefaultPerms = $this->getPerms($aPage['page_id']))) {
            foreach ($aDefaultPerms as $aDefaultPerm) {
                if ($aDefaultPerm['id'] == $sPerm) {
                    $aPerms[$sPerm] = (int)$aDefaultPerm['is_active'];
                    break;
                }
            }
        }

        if ($isAdmin = $this->isAdmin($aPage['page_id'])) {
            return true;
        }

        if (isset($aPerms[$sPerm])) {
            switch ((int)$aPerms[$sPerm]) {
                case 1:
                    if (!$this->isMember($aPage['page_id'])) {
                        return false;
                    }
                    break;
                case 2:
                    if (!$isAdmin) {
                        return false;
                    }
                    break;
            }
        }

        //If don't set in Permission list, Use groups permission
        if ($aPage['reg_method'] == 0 || $this->isMember($aPage['page_id'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getCountConvertibleGroups()
    {
        return $this->database()->select('COUNT(*)')
            ->from(':pages', 'p')
            ->join(':pages_category', 'pc', 'p.category_id=pc.category_id')
            ->where('pc.page_type=1')
            ->execute('getSlaveField');
    }

    public function convertOldGroups()
    {
        //each time run in 300 seconds or 1000 groups
        $start = time();

        //Map old groups Category to new
        $aCategories = $this->database()->select('*')
            ->from(':pages_category')
            ->where('page_type=1')
            ->execute('getRows');
        foreach ($aCategories as $aCategory) {
            // check name exists
            if (!$this->database()->select('type_id')
                ->from(':pages_type')
                ->where('item_type = 1 AND name = \'' . $aCategory['name'] . '\'')
                ->limit(1)
                ->execute('getSlaveRow')) {
                $aTypeInsert = [
                    'is_active'  => 1,
                    'item_type'  => 1,//1 mean groups
                    'name'       => $aCategory['name'],
                    'time_stamp' => PHPFOX_TIME,
                    'ordering'   => $aCategory['ordering'],
                ];
                $this->database()->insert(':pages_type', $aTypeInsert);
            }
        }

        //Get 1000 old groups
        $aOldGroups = $this->database()->select('p.page_id, p.category_id')
            ->from(':pages', 'p')
            ->join(':pages_category', 'pc', 'p.category_id=pc.category_id')
            ->where('pc.page_type=1')
            ->limit(1000)
            ->execute('getSlaveRows');
        $group_type_id = $this->database()->select('type_id')
            ->from(':pages_type')
            ->where('item_type=1')
            ->limit(1)
            ->execute('getSlaveField');
        foreach ($aOldGroups as $aGroup) {
            //Get new groups type
            $new_groups_type_id = $this->database()->select('pt.type_id')
                ->from(':pages_type', 'pt')
                ->join(':pages_category', 'pc', 'pc.name=pt.name')
                ->where('pc.category_id=' . (int)$aGroup['category_id'] . ' AND pt.item_type=1')
                ->limit(1)
                ->execute('getSlaveField');
            $group_type_id = ($new_groups_type_id > 0) ? $new_groups_type_id : $group_type_id;
            $this->database()->update(':pages', [
                'type_id'     => $group_type_id,
                'category_id' => 0,//We do not have default groups category
                'item_type'   => 1
            ], 'page_id=' . (int)$aGroup['page_id']);
            //Update blog data
            $this->database()->update(':blog', [
                'module_id' => 'groups'
            ], 'item_id=' . (int)$aGroup['page_id']);

            //Update event data
            $this->database()->update(':event', [
                'module_id' => 'groups'
            ], 'item_id=' . (int)$aGroup['page_id']);

            //Forum: do nothing

            //Update music album
            $this->database()->update(':music_album', [
                'module_id' => 'groups'
            ], 'item_id=' . (int)$aGroup['page_id']);
            //Update music song
            $this->database()->update(':music_song', [
                'module_id' => 'groups'
            ], 'item_id=' . (int)$aGroup['page_id']);

            //Update photo
            $this->database()->update(':photo', [
                'module_id' => 'groups'
            ], 'group_id=' . (int)$aGroup['page_id']);
            //Update photo album
            $this->database()->update(':photo_album', [
                'module_id' => 'groups'
            ], 'group_id=' . (int)$aGroup['page_id']);

            //Update groups comment
            $this->database()->update(':pages_feed', [
                'type_id' => 'groups_comment'
            ], 'type_id="pages_comment" AND parent_user_id=' . (int)$aGroup['page_id']);

            //Update comments on groups
            $this->database()->update(':comment', [
                'type_id' => 'groups'
            ], 'type_id="pages" AND item_id=' . (int)$aGroup['page_id']);

            //Update likes on groups
            db()->update(Phpfox::getT('like'), ['type_id' => 'REPLACE(type_id, \'pages\', \'groups\')'],
                'type_id LIKE \'pages%\' AND item_id=' . (int)$aGroup['page_id'], false);
            //Video not yet integrate with pages on 4.2.2

            //Update link data
            $this->database()->update(':link', [
                'module_id' => 'groups'
            ], 'item_id=' . (int)$aGroup['page_id']);

            //Update Home Feed
            db()->update(Phpfox::getT('feed'), ['type_id' => 'REPLACE(type_id, \'pages\', \'groups\')'],
                'type_id LIKE \'pages%\' AND item_id=' . (int)$aGroup['page_id'], false);

            //Update Notification
            db()->update(Phpfox::getT('notification'), ['type_id' => 'REPLACE(type_id, \'pages\', \'groups\')'],
                'type_id LIKE \'pages%\' AND item_id=' . (int)$aGroup['page_id'], false);
            //----------------------//
            //End process convert
            $end = time();
            if (($end - $start) >= 300) {
                break;
            }
        }
    }

    /**
     * Return prepared params for generating main menu of groups app
     *
     * @return array
     */
    public function getSectionMenu()
    {
        $aFilterMenu = [];
        if (!defined('PHPFOX_IS_USER_PROFILE')) {
            $iMyGroupsCount = Phpfox::getService('groups')->getMyPages(true, true);
            $iJoinedGroupIds = Phpfox::getService('groups')->getAllGroupIdsOfMember();
            $iJoinedGroupCount = count($iJoinedGroupIds);
            $aFilterMenu = [
                _p('All Groups')                                                                                                                                          => '',
                _p('joined_groups') . ($iJoinedGroupCount ? '<span class="my count-item">' . (($iJoinedGroupCount >= 100) ? '99+' : $iJoinedGroupCount) . '</span>' : '') => 'joined',
                _p('My Groups') . ($iMyGroupsCount ? '<span class="my count-item">' . (($iMyGroupsCount >= 100) ? '99+' : $iMyGroupsCount) . '</span>' : '')              => 'my'
            ];

            if (!Phpfox::getParam('core.friends_only_community') && Phpfox::isModule('friend') && !Phpfox::getUserBy('profile_page_id')) {
                $aFilterMenu[_p('Friends\' Groups')] = 'friend';
            }

            if (Phpfox::getService('groups.facade')->getUserParam('can_approve_pages')) {
                $iPendingTotal = Phpfox::getService('groups')->getPendingTotal();
                if ($iPendingTotal) {
                    $aFilterMenu[_p('Pending Groups') . '<span class="count-item">' . (($iPendingTotal >= 100) ? '99+' : $iPendingTotal) . '</span>'] = 'pending';
                }
            }
        }

        return $aFilterMenu;
    }

    public function getMenu($aPage)
    {
        $sGroupUrl = $this->getFacade()->getItems()->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        $aMenus = $this->_getMenu($aPage, $sGroupUrl);

        if ($this->isAdmin($aPage)) {
            $iTotalPendingMembers = $this->getPendingUsers($aPage['page_id'], true);
            if ($iTotalPendingMembers > 0) {
                Phpfox_Template::instance()->assign('aSubPagesMenus', [
                    [
                        'url'   => $sGroupUrl . 'members?tab=pending',
                        'title' => $this->getFacade()->getPhrase('pending_memberships') . '<span class="pending">&nbsp;(' . $iTotalPendingMembers . ')</span>'
                    ]
                ]);
            }
        }

        $sLanding = Phpfox::getService('groups')->isPage($this->request()->get('req1')) ? $this->request()->get('req2') : $this->request()->get('req3');
        if (empty($sLanding) && !empty($aPage['landing_page'])) {
            $sLanding = $aPage['landing_page'];
        }
        $sLanding = Phpfox_Url::instance()->doRewrite($sLanding);
        $bActive = false;
        foreach ($aMenus as $key => $aMenu) {
            if (empty($aMenu['menu_icon'])) {
                $aMenus[$key]['menu_icon'] = materialParseIcon($aMenu['landing']);
            }
            $sMenuLandingRewrite = \Phpfox_Url::instance()->doRewrite($aMenu['landing']);
            if ($sMenuLandingRewrite !== $aMenu['landing']) {
                $aMenus[$key]['url'] = $sGroupUrl . $sMenuLandingRewrite . '/';
            }
            if (!$bActive && (
                    (empty($sLanding) && $aMenu['landing'] == 'home')
                    || (!empty($sLanding) && $sLanding == $sMenuLandingRewrite)
                )
            ) {
                $bActive = $aMenus[$key]['is_active'] = true;
            }
        }

        if ($sPlugin = Phpfox_Plugin::get('groups.service_pages_getmenu')) {
            eval($sPlugin);
        }

        return $aMenus;
    }

    public function _getMenu($aPage, $sGroupUrl, $bForEdit = false)
    {
        $aMenus = [
            [
                'phrase'  => $this->getFacade()->getPhrase('home'),
                'url'     => $sGroupUrl . (empty($aPage['landing_page']) ? '' : 'wall/'),
                'icon'    => 'misc/comment.png',
                'landing' => 'home'
            ],
            [
                'phrase'  => $this->getFacade()->getPhrase('members'),
                'url'     => $sGroupUrl . 'members',
                'icon'    => 'misc/comment.png',
                'landing' => 'members'
            ]
        ];

        $aModuleCalls = Phpfox::massCallback('getGroupMenu', $aPage);
        if ($aIntegrate = storage()->get('groups_integrate')) {
            $aIntegrate = (array)$aIntegrate->value;
        }

        foreach ($aModuleCalls as $sModule => $aModuleCall) {
            if (!is_array($aModuleCall)) {
                continue;
            }
            if ($aIntegrate && array_key_exists($sModule, $aIntegrate) && !$aIntegrate[$sModule]) {
                continue;
            }
            $aMenus[] = $aModuleCall[0];
        }

        if ($bForEdit) {
            $this->getMenuWidgets($aPage['page_id'], $aMenus);
        } else {
            if (count($this->_aWidgetMenus)) {
                $aMenus = array_merge($aMenus, $this->_aWidgetMenus);
            }
        }

        $sCacheId = $this->cache()->set('groups_' . $aPage['page_id'] . '_menus');
        if (($aGroupMenus = $this->cache()->get($sCacheId)) === false) {
            $aGroupMenus = $this->getGroupMenu($aPage['page_id']);
            $this->cache()->save($sCacheId, $aGroupMenus);
        }

        if (!empty($aGroupMenus)) {
            $aGroupMenuName = array_column($aGroupMenus, 'menu_name');

            foreach ($aMenus as $key => &$aMenu) {
                $index = array_search($aMenu['landing'], $aGroupMenuName);
                if ($bForEdit && $index !== false) {
                    $aMenu = array_merge($aMenu, $aGroupMenus[$index]);
                }

                if (!$bForEdit && $index !== false) {
                    if ($aGroupMenus[$index]['is_active'] == 1) {
                        $aMenu = array_merge($aMenu, ['ordering' => $aGroupMenus[$index]['ordering']]);
                    } else {
                        unset($aMenus[$key]);
                    }
                }
            }

            $aOrdering = array_column($aMenus, 'ordering');
            if (count($aMenus) == count($aOrdering)) {
                array_multisort($aOrdering, SORT_ASC, $aMenus);
            }
        }

        return $aMenus;
    }

    public function getGroupMenu($iPageId)
    {
        return db()->select('*')
            ->from(Phpfox::getT('pages_menu'))
            ->where(['page_id' => $iPageId])
            ->order('ordering ASC')
            ->executeRows();
    }

    /**
     * Get number of items in a category
     *
     * @param        $iCategoryId
     * @param        $bIsSub
     * @param        $iItemType
     * @param int    $iUserId
     * @param bool   $bGetCount
     * @param string $sView
     *
     * @return array|int|string
     */
    public function getItemsByCategory($iCategoryId, $bIsSub, $iItemType, $iUserId = 0, $bGetCount = false, $sView = '')
    {
        $sConds = 'pages.item_type = ' . $iItemType;

        if ($bIsSub) {
            $sConds .= ' AND pages.category_id = ' . $iCategoryId;
        } else {
            $sConds .= ' AND pages.type_id = ' . $iCategoryId;
        }

        switch ($sView) {
            case '':
                $sConds .= ' AND pages.view_id = 0';
                break;
            case 'my':
                $sConds .= ' AND pages.user_id = ' . $iUserId;
                break;
            case 'friend':
                $sConds .= ' AND pages.view_id = 0';
                $aFriends = (Phpfox::isModule('friend') ? Phpfox::getService('friend')->getFromCache() : []);
                if ($aFriends) {
                    $sFriendsList = implode(',', array_column($aFriends, 'user_id'));
                    $sConds .= ' AND pages.user_id IN (' . $sFriendsList . ')';
                } else {
                    $sConds .= ' AND pages.user_id IN (0)';
                }
                break;
            case 'pending':
                $sConds .= ' AND pages.view_id = 1';
                break;
            case 'joined':
                $sConds .= ' AND pages.view_id = 0';
                $aGroupIds = $this->getAllGroupIdsOfMember();
                if (count($aGroupIds)) {
                    $sConds .= " AND pages.page_id IN (" . implode(',', $aGroupIds) . ")";
                } else {
                    $sConds .= ' AND pages.page_id IN (0)';
                }
                break;
            default:
                break;
        }

        if (($iUserId != Phpfox::getUserId() || $iUserId === null) && Phpfox::hasCallback('groups', 'getExtraBrowseConditions')
        ) {
            $sConds .= Phpfox::callback('groups.getExtraBrowseConditions', 'pages');
        }

        if ($bGetCount) {
            return db()->select('COUNT(*)')
                ->from(':pages', 'pages')
                ->where($sConds)
                ->executeField();
        } else {
            return db()->select('*')
                ->from(':pages', 'pages')
                ->where($sConds)
                ->executeRows();
        }
    }

    /**
     * check if current user have one of 3 permissions: edit all, delete all, approve
     * @return bool
     */
    public function canModerate()
    {
        $oGroupsFacade = Phpfox::getService('groups.facade');

        return $oGroupsFacade->getUserParam('can_edit_all_pages') || $oGroupsFacade->getUserParam('can_delete_all_pages') || $oGroupsFacade->getUserParam('can_approve_pages');
    }

    public function canPurchaseSponsorItem($iItemId)
    {
        $aSponsorIds = $this->getPendingSponsor();
        return in_array($iItemId, $aSponsorIds) ? false : true;
    }

    public function getPendingSponsor()
    {
        $cacheId = $this->cache()->set('groups_pending_sponsor');
        if (false === ($aSponsorIds = $this->cache()->get($cacheId))) {
            $aSponsors = db()->select('m.page_id')
                ->from(Phpfox::getT('pages'), 'm')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = m.page_id')
                ->where('m.item_type = 1 AND m.is_sponsor = 0 AND s.is_custom = 2 AND s.module_id = \'groups\'')
                ->execute('getSlaveRows');
            $aSponsorIds = array_column($aSponsors, 'page_id');
            $this->cache()->save($cacheId, $aSponsorIds);
        }
        return $aSponsorIds;
    }

    public function getActionsPermission(&$aPage, $sView = '')
    {
        $aPage['bCanApprove'] = $sView == 'pending' && $aPage['view_id'] == 1 && Phpfox::getUserParam('groups.can_approve_groups');
        $aPage['bCanEdit'] = Phpfox::getService('groups')->isAdmin($aPage) || Phpfox::getUserParam('groups.can_edit_all_groups');
        $aPage['bCanDelete'] = Phpfox::getUserId() == $aPage['user_id'] || Phpfox::getUserParam('groups.can_delete_all_groups');
        $aPage['bCanSponsor'] = $aPage['bCanPurchaseSponsor'] = false;
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aPage['bCanSponsor'] = $aPage['view_id'] == 0 && Phpfox::getUserParam('groups.can_sponsor_groups');
            $bCanPurchaseSponsor = $this->canPurchaseSponsorItem($aPage['page_id']);
            $aPage['bCanPurchaseSponsor'] = $aPage['view_id'] == 0 && $aPage['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('groups.can_purchase_sponsor_groups') && $bCanPurchaseSponsor;
        }
        $aPage['bCanFeature'] = $aPage['view_id'] == 0 && Phpfox::getUserParam('groups.can_feature_group');
        $aPage['bShowItemActions'] = $aPage['bCanApprove'] || $aPage['bCanEdit'] || $aPage['bCanDelete'] || $aPage['bCanSponsor'] || $aPage['bCanPurchaseSponsor'] || $aPage['bCanFeature'];
    }

    public function getForView($mId)
    {
        if ($this->_aPage !== null) {
            $mId = $this->_aPage['page_id'];
        }

        $pageUserId = Phpfox::getUserId();

        if (Phpfox::isModule('friend')) {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = p.user_id AND f.friend_user_id = " . $pageUserId);
        }

        $this->_aRow = $this->database()->select('p.*, u.user_image as image_path, p.image_path as pages_image_path, u.user_id as page_user_id, p.use_timeline, pc.claim_id, pu.vanity_url, pg.name AS category_name, p_type.type_id as parent_category_id, pg.page_type, pt.text, pt.text_parsed, u.full_name, ts.style_id AS designer_style_id, ts.folder AS designer_style_folder, t.folder AS designer_theme_folder, t.total_column, ts.l_width, ts.c_width, ts.r_width, t.parent_id AS theme_parent_id, p_type.name AS parent_category_name, ' . Phpfox::getUserField('u2', 'owner_'))
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('pages_text'), 'pt', 'pt.page_id = p.page_id')
            ->join(Phpfox::getT('user'), 'u', 'u.profile_page_id = p.page_id')
            ->join(Phpfox::getT('user'), 'u2', 'u2.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_category'), 'pg', 'pg.category_id = p.category_id')
            ->leftJoin(Phpfox::getT('pages_type'), 'p_type', 'p_type.type_id = pg.type_id')
            ->leftJoin(Phpfox::getT('theme_style'), 'ts', 'ts.style_id = p.designer_style_id')
            ->leftJoin(Phpfox::getT('theme'), 't', 't.theme_id = ts.theme_id')
            ->leftJoin(Phpfox::getT('pages_claim'), 'pc',
                'pc.page_id = p.page_id AND pc.user_id = ' . Phpfox::getUserId())
            ->where('p.page_id = ' . (int)$mId . ' AND p.item_type = 1')
            ->execute('getSlaveRow');

        if (!isset($this->_aRow['page_id'])) {
            return false;
        }

        $this->_aRow['is_page'] = true;
        $this->_aRow['is_admin'] = $this->isAdmin($this->_aRow);
        $this->_aRow['is_liked'] = $this->isMember($this->_aRow['page_id']);
        $this->_aRow['link'] = $this->getFacade()->getItems()->getUrl($this->_aRow['page_id'], $this->_aRow['title'],
            $this->_aRow['vanity_url']);

        if (($this->_aRow['page_type'] == '1' || $this->_aRow['item_type'] != '0') && $this->_aRow['reg_method'] == '1') {
            $this->_aRow['is_reg'] = $this->joinGroupRequested($this->_aRow['page_id'], Phpfox::getUserId());
        }

        if ($this->_aRow['reg_method'] == '2' && Phpfox::isUser()) {
            $this->_aRow['is_invited'] = (int)$this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('pages_invite'))
                ->where('page_id = ' . (int)$this->_aRow['page_id'] . ' AND invited_user_id = ' . Phpfox::getUserId())
                ->execute('getSlaveField');

            if (!$this->_aRow['is_invited']) {
                unset($this->_aRow['is_invited']);
            }
        }

        if ($this->_aRow['page_id'] == Phpfox::getUserBy('profile_page_id')) {
            $this->_aRow['is_liked'] = true;
        }

        // Issue with like/join button
        // Still not defined
        if (!isset($this->_aRow['is_liked'])) {
            // make it false: not liked or joined yet
            $this->_aRow['is_liked'] = false;
        }

        if ($this->_aRow['app_id']) {
            if ($this->_aRow['aApp'] = Phpfox::getService('apps')->getForPage($this->_aRow['app_id'])) {
                $this->_aRow['is_app'] = true;
                $this->_aRow['title'] = $this->_aRow['aApp']['app_title'];
                $this->_aRow['category_name'] = 'App';
            }
        } else {
            $this->_aRow['is_app'] = false;
        }

        $oUrl = Phpfox_Url::instance();
        if ($this->_aRow['type_id']) {
            $aType = $this->getFacade()->getType()->getById($this->_aRow['type_id']);
        }
        if (!empty($aType)) {
            $this->_aRow['parent_category_name'] = $aType['name'];
        }
        if ($this->_aRow['parent_category_name']) {
            $this->_aRow['parent_category_link'] = $oUrl->makeUrl('groups.category.' . $this->_aRow['type_id'] . '.' . $oUrl->cleanTitle(_p($this->_aRow['parent_category_name'])));
        }
        if ($this->_aRow['category_name']) {
            $this->_aRow['category_link'] = $oUrl->makeUrl('groups.category.' . $this->_aRow['parent_category_id'] . '.' . $oUrl->cleanTitle(_p($this->_aRow['category_name'])));
        }

        return $this->_aRow;
    }

    /**
     * Get widget by ordering ASC
     *
     * @param      $iPageId
     * @param bool $bIsBlock
     *
     * @return array
     */
    public function getWidgetsOrdering($iPageId, $bIsBlock = true)
    {
        return db()->select('*')->from(':pages_widget')->where([
            'page_id'  => $iPageId,
            'is_block' => intval($bIsBlock)
        ])->order('ordering ASC')->executeRows();
    }

    /**
     * Update item order
     *
     * @param $sTable
     * @param $sIdName
     * @param $iId
     * @param $iOrder
     */
    public function updateItemOrder($sTable, $sIdName, $iId, $iOrder)
    {
        db()->update($sTable, ['ordering' => $iOrder], [$sIdName => $iId]);
    }

    /**
     * Build widgets
     *
     * @param $iId
     */
    public function buildWidgets($iId)
    {
        if (!$this->getFacade()->getItems()->hasPerm($iId,
            $this->getFacade()->getItemType() . '.view_browse_widgets')) {
            return;
        }

        $sCacheId = $this->cache()->set('groups_' . $iId . '_widgets');
        if (($aWidgets = $this->cache()->get($sCacheId)) === false) {
            $aWidgets = $this->database()->select('pw.*, pwt.text_parsed AS text')
                ->from(Phpfox::getT('pages_widget'), 'pw')
                ->join(Phpfox::getT('pages_widget_text'), 'pwt', 'pwt.widget_id = pw.widget_id')
                ->where('pw.page_id = ' . (int)$iId)
                ->order('pw.ordering ASC')
                ->execute('getSlaveRows');
            $this->cache()->save($sCacheId, $aWidgets);
        }

        foreach ($aWidgets as $aWidget) {
            $this->_aWidgetEdit[] = [
                'widget_id'  => $aWidget['widget_id'],
                'title'      => $aWidget['title'],
                'is_block'   => $aWidget['is_block'],
                'menu_title' => $aWidget['menu_title'],
                'url_title'  => $aWidget['url_title']
            ];

            if (!$aWidget['is_block']) {
                $this->_aWidgetMenus[] = [
                    'phrase'      => $aWidget['menu_title'],
                    'url'         => $this->getUrl($aWidget['page_id'], $this->_aRow['title'],
                            $this->_aRow['vanity_url']) . $aWidget['url_title'] . '/',
                    'landing'     => $aWidget['url_title'],
                    'icon_pass'   => (empty($aWidget['image_path']) ? false : true),
                    'icon'        => $aWidget['image_path'],
                    'icon_server' => $aWidget['image_server_id']
                ];
            }

            $this->_aWidgetUrl[$aWidget['url_title']] = $aWidget['widget_id'];

            if ($aWidget['is_block']) {
                $this->_aWidgetBlocks[] = $aWidget;
            } else {
                $this->_aWidgets[$aWidget['url_title']] = $aWidget;
            }
        }
    }

    public function getMenuWidgets($iId, &$aMenus)
    {
        if (!$this->getFacade()->getItems()->hasPerm($iId,
            $this->getFacade()->getItemType() . '.view_browse_widgets')) {
            return;
        }

        $sCacheId = $this->cache()->set('groups_' . $iId . '_menu_widgets');
        if (($aWidgets = $this->cache()->get($sCacheId)) === false) {
            $aWidgets = $this->database()->select('pw.*, pwt.text_parsed AS text')
                ->from(Phpfox::getT('pages_widget'), 'pw')
                ->join(Phpfox::getT('pages_widget_text'), 'pwt', 'pwt.widget_id = pw.widget_id')
                ->where([
                    'pw.page_id'  => (int)$iId,
                    'pw.is_block' => 0
                ])
                ->execute('getSlaveRows');
            $this->cache()->save($sCacheId, $aWidgets);
        }

        foreach ($aWidgets as $aWidget) {
            $aMenus[] = [
                'phrase'      => Phpfox::getLib('parse.output')->clean($aWidget['menu_title']),
                'url'         => $this->getUrl($aWidget['page_id']) . $aWidget['url_title'] . '/',
                'landing'     => $aWidget['url_title'],
                'icon_pass'   => (empty($aWidget['image_path']) ? false : true),
                'icon'        => $aWidget['image_path'],
                'icon_server' => $aWidget['image_server_id'],
                'widget_id'   => $aWidget['widget_id'],
            ];
        }
    }

    public function getAllGroupIdsOfMember($iUserId = 0)
    {
        if (!Phpfox::isModule('like')) {
            return false;
        }
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        if (!$iUserId) {
            return [];
        }
        $sCacheId = $this->cache()->set('member_' . $iUserId . '_groups');
        if (false === ($aMemberGroupIds = $this->cache()->get($sCacheId))) {
            $aMemberGroups = $this->database()->select('p.page_id')
                ->from(Phpfox::getT('like'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->join(Phpfox::getT('pages'), 'p', 'p.page_id = l.item_id AND p.item_type = 1')
                ->where('l.type_id = \'groups\' AND l.user_id = ' . $iUserId)
                ->group('p.page_id')
                ->executeRows();
            $aMemberGroupIds = array_column($aMemberGroups, 'page_id');
            $this->cache()->save($sCacheId, $aMemberGroupIds);
        }
        return $aMemberGroupIds;
    }

    public function getAllSecretGroupIdsOfMember($iUserId = 0)
    {
        if (!Phpfox::isModule('like')) {
            return false;
        }
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        if (!$iUserId) {
            return [];
        }
        $sCacheId = $this->cache()->set('member_' . $iUserId . '_secret_groups');
        if (false === ($aMemberGroupIds = $this->cache()->get($sCacheId))) {
            $aMemberGroups = $this->database()->select('p.page_id')
                ->from(Phpfox::getT('like'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->join(Phpfox::getT('pages'), 'p', 'p.page_id = l.item_id')
                ->where('l.type_id = \'groups\' AND p.reg_method = 2 AND l.user_id = ' . $iUserId)
                ->group('p.page_id')
                ->executeRows();
            $aMemberGroupIds = array_column($aMemberGroups, 'page_id');
            $this->cache()->save($sCacheId, $aMemberGroupIds);
        }
        return $aMemberGroupIds;
    }

    public function getMembers($iGroupId, $iLimit = null, $iPage = 1, $sSearch = null)
    {
        if (!Phpfox::isModule('like')) {
            return false;
        }
        $sCacheId = $this->cache()->set('groups_' . $iGroupId . '_members');
        if (false === ($aGroupMembers = $this->cache()->getLocalFirst($sCacheId)) || $iLimit || $sSearch) {
            $aWhere = [
                'l.type_id' => 'groups',
                'l.item_id' => intval($iGroupId)
            ];

            $iOwnerId = $this->getPageOwnerId($iGroupId);

            $iCnt = $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('like'), 'l')
                ->where($aWhere)
                ->execute('getSlaveField');

            if ($sSearch) {
                $aWhere['u.full_name'] = ['LIKE' => "%$sSearch%"];
            }

            if ($iLimit) {
                $this->database()->limit($iPage, $iLimit);
            }

            $aGroupMembers = $this->database()->select('uf.total_friend, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('like'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->join(Phpfox::getT('user_field'), 'uf', 'u.user_id = uf.user_id')
                ->leftJoin(Phpfox::getT('pages'), 'p', 'p.user_id = u.user_id')
                ->where($aWhere)
                ->order('field(p.user_id,' . (int)$iOwnerId . ') DESC, u.full_name ASC')
                ->group('u.user_id')
                ->executeRows();

            if ($iLimit == null && $sSearch == null) {
                $this->cache()->saveBoth($sCacheId, $aGroupMembers);
            }
            return [$iCnt, $aGroupMembers];
        }

        return [count($aGroupMembers), $aGroupMembers];
    }

    public function getPageAdmins($iGroupId = null, $iPage = 1, $iLimit = null, $sSearch = null)
    {
        if (!$iGroupId) {
            return [];
        }
        $sCacheId = $this->cache()->set('groups_' . $iGroupId . '_admins');
        if (false === ($groupAdmins = $this->cache()->getLocalFirst($sCacheId)) || $iLimit || $sSearch) {
            $aOwnerAdmin = [];
            if ($iPage == 1) {
                $aOwnerAdmin[] = $this->getPageOwner($iGroupId);
                $iLimit && $iLimit--;
            }

            if ($sSearch && !empty($aOwnerAdmin) && stristr($aOwnerAdmin[0]['full_name'], $sSearch) === false) {
                $aOwnerAdmin = [];
            }

            if ($iLimit) {
                $this->database()->limit($iPage, $iLimit);
            }

            $aWhere = ['pa.page_id' => $iGroupId];
            if ($sSearch) {
                $aWhere['u.full_name'] = ['LIKE' => "%$sSearch%"];
            }
            if (count($aOwnerAdmin) && isset($aOwnerAdmin[0]['user_id'])) {
                $aWhere[] = 'AND pa.user_id != ' . $aOwnerAdmin[0]['user_id'];
            }

            $aPageAdmins = $this->database()->select(Phpfox::getUserField())
                ->from(Phpfox::getT('pages_admin'), 'pa')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
                ->where($aWhere)
                ->executeRows();

            $groupAdmins = array_merge($aOwnerAdmin, $aPageAdmins);
            if ($iLimit == null && $sSearch == null) {
                $this->cache()->saveBoth($sCacheId, $groupAdmins);
            }
        }
        return $groupAdmins;
    }

    public function getGroupAdminsCount($iGroupId)
    {
        $sCacheId = $this->cache()->set('groups_' . $iGroupId . '_admins');
        if (false === ($aGroupAdmins = $this->cache()->get($sCacheId))) {
            $aGroup = $this->getPage($iGroupId);
            return db()->select('COUNT(*)')->from(':pages_admin')->where([
                    'page_id' => $iGroupId,
                    ' AND user_id != ' . $aGroup['user_id']
                ])->executeField() + 1;
        }
        return count($aGroupAdmins);
    }

    public function getPendingUsers($iGroupId, $bIsCount = false, $iPage = 1, $iLimit = null, $sSearch = null)
    {
        $sCacheId = $this->cache()->set('groups_' . $iGroupId . '_pending_users');
        if (false === ($PendingUsers = $this->cache()->get($sCacheId)) || $iLimit || $sSearch || $bIsCount) {
            $this->database()
                ->from(Phpfox::getT('pages_signup'), 'ps')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = ps.user_id')
                ->where(array_merge([
                    'ps.page_id' => intval($iGroupId)
                ], $sSearch ? ['u.full_name' => ['LIKE' => "%$sSearch%"]] : []));

            if ($bIsCount) {
                if ($PendingUsers) {
                    $this->database()->clean();
                    return count($PendingUsers);
                }
                return $this->database()->select('count(*)')->executeField();
            } else {
                if ($iLimit) {
                    $this->database()->limit($iPage, $iLimit);
                }
                $PendingUsers = $this->database()->select('ps.*, ' . Phpfox::getUserField())->executeRows();

                if ($iLimit == null && $sSearch == null) {
                    $this->cache()->save($sCacheId, $PendingUsers);
                }
            }
        }
        return $PendingUsers;
    }

    /**
     * Move items to another category
     *
     * @param $iOldCategoryId
     * @param $iNewCategoryId
     * @param $bOldIsSub , true if old category is sub category
     * @param $bNewIsSub , true if new category is sub category
     * @param $iItemType
     */
    public function moveItemsToAnotherCategory($iOldCategoryId, $iNewCategoryId, $bOldIsSub, $bNewIsSub, $iItemType)
    {
        $aItems = Phpfox::getService('groups')->getItemsByCategory($iOldCategoryId, $bOldIsSub, $iItemType, 0, false, 'move');
        if ($bNewIsSub) {
            // get type id
            $parentCategory = Phpfox::getService('groups.category')->getById($iNewCategoryId);
            $iTypeId = $parentCategory ? $parentCategory['type_id'] : 0;
            $aUpdates = [
                'type_id'     => $iTypeId,
                'category_id' => $iNewCategoryId
            ];
        } else {
            $aUpdates = [
                'type_id' => $iNewCategoryId
            ];
        }
        foreach ($aItems as $aItem) {
            db()->update(Phpfox::getT('pages'), $aUpdates, 'page_id = ' . $aItem['page_id']);
        }
    }

    /**
     * Get page for profile index
     *
     * @param        $iUserId
     * @param int    $iLimit
     * @param bool   $bNoCount
     * @param string $sConds
     *
     * @return array
     */
    public function getForProfile($iUserId, $iLimit = 0, $bNoCount = false, $sConds = '')
    {
        $iCnt = 0;
        if ($bNoCount == false) {
            $iCnt = $this->database()->select('p.page_id')
                ->from(Phpfox::getT('like'), 'l')
                ->join(Phpfox::getT('pages'), 'p', 'p.page_id = l.item_id AND p.view_id = 0 AND p.item_type = 1')
                ->join(Phpfox::getT('user'), 'u', 'u.profile_page_id = p.page_id')
                ->where('l.type_id = \'groups\' AND l.user_id = ' . (int)$iUserId . $sConds)
                ->group('p.page_id', true)// fixes displaying duplicate pages if there are duplicate likes
                ->execute('getSlaveRows');
            $iCnt = count($iCnt);
        }

        if ($iLimit) {
            $this->database()->limit($iLimit);
        }

        $aPages = $this->database()->select('p.*, pt.name as type_name, pc.name as category_name, ph.destination as cover_image_path, ph.server_id as cover_image_server_id, pu.vanity_url, u.server_id, ptxt.text_parsed, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('like'), 'l')
            ->join(Phpfox::getT('pages'), 'p',
                'p.page_id = l.item_id AND p.view_id = 0 AND p.item_type = 1')
            ->join(Phpfox::getT('user'), 'u', 'u.profile_page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->leftJoin(':photo', 'ph', 'ph.photo_id = p.cover_photo_id')
            ->leftJoin(':pages_type', 'pt', 'p.type_id = pt.type_id')
            ->leftJoin(':pages_text', 'ptxt', 'ptxt.page_id= p.page_id')
            ->leftJoin(':pages_category', 'pc', 'p.category_id = pc.category_id')
            ->where('l.type_id = \'' . $this->getFacade()->getItemType() . '\' AND l.user_id = ' . (int)$iUserId . $sConds)
            ->group('p.page_id', true)// fixes displaying duplicate pages if there are duplicate likes
            ->order('l.time_stamp DESC')
            ->execute('getSlaveRows');

        foreach ($aPages as $iKey => &$aPage) {
            $aPage['is_app'] = false;
            $aPage['is_user_page'] = true;
            $aPage['user_image'] = sprintf($aPage['image_path'], '_200_square');
            $aPage['url'] = $this->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
            $this->getActionsPermission($aPage);
        }

        return [$iCnt, $aPages];
    }

    /**
     * Get group info
     *
     * @param      $iGroupId
     * @param bool $bGetParsed
     *
     * @return string
     */
    public function getInfo($iGroupId, $bGetParsed = false)
    {
        if ($bGetParsed) {
            return db()->select('text_parsed')->from(':pages_text')->where(['page_id' => $iGroupId])->executeField();
        }

        return db()->select('text')->from(':pages_text')->where(['page_id' => $iGroupId])->executeField();
    }

    /**
     * Get upload photo params, support dropzone
     *
     * @return array
     */
    public function getUploadPhotoParams($aExtraParams = [])
    {
        $iMaxFileSize = Phpfox::getUserParam('groups.pf_group_max_upload_size');
        $iMaxFileSize = $iMaxFileSize > 0 ? $iMaxFileSize / 1024 : 0;
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);
        $aUploadParams = [
            'max_size'        => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'type_list'       => ['jpg', 'jpeg', 'gif', 'png'],
            'upload_dir'      => Phpfox::getParam('pages.dir_image'),
            'upload_path'     => Phpfox::getParam('pages.url_image'),
            'thumbnail_sizes' => isset($aExtraParams['from_profile']) ? [] : $this->getPhotoPicSizes(),
        ];

        if (isset($aExtraParams['from_profile'])) {
            $sPreviewTemplate =
                '<div class="dz-preview dz-file-preview">
                <div class="dz-image"><img data-dz-thumbnail /></div>
                <div class="dz-uploading-message">' . _p('uploading_your_photo_three_dot') . '</div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                <div class="dz-upload-successfully">' . _p('drag_to_reposition_photo') . '</div>
                <div class="dz-error-message"><span data-dz-errormessage></span> <a role="button" class="dz-upload-again" id="profile-image-upload-again">' . _p('change_photo') . '</a></div>
            </div>';
            $urlParams = [
                'is_temp' => 1,
            ];
            if (!empty($aExtraParams['url_params'])) {
                $urlParams = array_merge($urlParams, $aExtraParams['url_params']);
            }
            $aUploadParams = array_merge($aUploadParams, [
                'preview_template' => $sPreviewTemplate,
                'js_events' => [
                    'success' => '$Core.Groups.profilePhoto.onSuccessUpload',
                    'addedfile' => '$Core.Groups.profilePhoto.onAddedFile',
                    'error' => '$Core.Groups.profilePhoto.onError'
                ],
                'upload_url' => Phpfox::getLib('url')->makeUrl('groups.photo', $urlParams),
                'label' => '',
                'style' => 'mini',
                'first_description' => '',
                'type_description' => '',
                'max_size_description' => '',
                'extra_description' => '',
            ]);
        }

        return $aUploadParams;
    }

    /**
     * Get photo sizes
     *
     * @return array
     */
    public function getPhotoPicSizes()
    {
        (($sPlugin = Phpfox_Plugin::get('groups.service_groups_getphotopicsizes')) ? eval($sPlugin) : false);

        return $this->_aPhotoPicSizes;
    }

    /**
     * @param      $iGroupId
     * @param null $iUserId
     *
     * @return bool
     */
    public function joinGroupRequested($iGroupId, $iUserId = null)
    {
        $iUserId === null && $iUserId = Phpfox::getUserId();
        $sCacheId = $this->cache()->set('groups_' . $iGroupId . '_pending_users');
        if (false === ($PendingUsers = $this->cache()->get($sCacheId))) {
            $this->getPendingUsers($iGroupId);
            return !!db()->select('*')->from(':pages_signup')->where([
                'page_id' => $iGroupId,
                'user_id' => $iUserId
            ])->executeField();
        }
        return in_array($iUserId, array_column($PendingUsers, 'user_id'));
    }

    /**
     * Get pages in the same category
     *
     * @param     $iPageid
     * @param int $iLimit
     *
     * @return array|bool
     */
    public function getSameCategoryPages($iPageid, $iLimit = 0)
    {
        $aPage = db()->select('type_id, category_id')->from($this->_sTable)->where(['page_id' => $iPageid])->executeRow();
        if (!$aPage) {
            return false;
        }

        $iPageid && db()->limit($iLimit);

        return db()->select('p.*, pc.name as category, pu.vanity_url, u.*, pt.destination as cover_image_path, pt.server_id as cover_image_server_id')
            ->from($this->_sTable, 'p')
            ->leftJoin(':pages_category', 'pc', 'p.category_id = pc.category_id')
            ->leftJoin(':pages_url', 'pu', 'p.page_id = pu.page_id')
            ->leftJoin(':user', 'u', 'u.profile_page_id = p.page_id')
            ->leftJoin(':photo', 'pt', 'pt.photo_id = p.cover_photo_id')
            ->where("p.page_id != $iPageid AND p.type_id = $aPage[type_id]")
            ->order('rand()')
            ->executeRows();
    }

    public function getForEdit($iId, $bSetErrorMessage = true)
    {
        $aRow = $this->database()->select('p.*, pu.vanity_url, pt.text, pc.page_type, p_type.item_type')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('pages_text'), 'pt', 'pt.page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_category'), 'pc', 'p.category_id = pc.category_id')
            ->leftJoin(Phpfox::getT('pages_type'), 'p_type', 'p_type.type_id = pc.type_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->where('p.page_id = ' . (int)$iId . ' AND p.item_type = 1')
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return $bSetErrorMessage ? Phpfox_Error::set($this->getFacade()->getPhrase('unable_to_find_the_page_you_are_trying_to_edit')) : false;
        }

        if (!$this->isAdmin($aRow) && !Phpfox::getUserParam('groups.can_edit_all_groups')) {
            return $bSetErrorMessage ? Phpfox_Error::set($this->getFacade()->getPhrase('you_are_unable_to_edit_this_page')) : false;
        }

        $this->_aRow = $aRow;

        $this->getFacade()->getItems()->buildWidgets($aRow['page_id']);

        $aRow['admins'] = $this->database()->select(Phpfox::getUserField())
            ->from(Phpfox::getT('pages_admin'), 'pa')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
            ->where('pa.page_id = ' . (int)$aRow['page_id'])
            ->execute('getSlaveRows');

        $aRow['admin_ids'] = [];
        foreach ($aRow['admins'] as $aAdmin) {
            $aRow['admin_ids'][] = $aAdmin['user_id'];
        }

        $aRow['admin_ids'] = json_encode($aRow['admin_ids']);

        $aMenus = $this->getMenu($aRow);
        foreach ($aMenus as $iKey => $aMenu) {
            $aMenus[$iKey]['is_selected'] = false;
        }
        if (!empty($aRow['landing_page'])) {
            foreach ($aMenus as $iKey => $aMenu) {
                if ($aMenu['landing'] == $aRow['landing_page']) {
                    $aMenus[$iKey]['is_selected'] = true;
                }
            }
        }

        $aRow['landing_pages'] = $aMenus;

        if ($aRow['app_id']) {
            if ($aRow['aApp'] = Phpfox::getService('apps')->getForPage($aRow['app_id'])) {
                $aRow['is_app'] = true;
                $aRow['title'] = $aRow['aApp']['app_title'];
            }
        } else {
            $aRow['is_app'] = false;
        }
        if ($sPlugin = Phpfox_Plugin::get($this->getFacade()->getItemType() . '.service_pages_getforedit_1')) {
            eval($sPlugin);
            if (isset($mReturnFromPlugin)) {
                return $mReturnFromPlugin;
            }
        }

        defined('PHPFOX_PAGES_EDIT_ID') or define('PHPFOX_PAGES_EDIT_ID', $aRow['page_id']);
        $aRow['image_path_200'] = Phpfox::getLib('image.helper')->display([
            'file'       => $aRow['image_path'],
            'path'       => 'pages.url_image',
            'server_id'  => $aRow['image_server_id'],
            'return_url' => true,
            'suffix'     => '_200_square'
        ]);

        return $aRow;
    }

    public function isInvited($iPageId)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(':pages_invite')
            ->where('page_id = ' . (int)$iPageId . ' AND type_id = 1 AND invited_user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');
        return ($iCnt) ? true : false;
    }

    /**
     * Check if user is invited
     *
     * @param        $pageId
     * @param string $email
     *
     * @return bool
     */
    public function checkCurrentUserInvited($pageId, $email = '')
    {
        if (empty($pageId)) {
            return false;
        }
        $sWhere = 'page_id = ' . (int)$pageId . ' AND type_id = ' . $this->getFacade()->getItemTypeId();
        if (Phpfox::isUser()) {
            $email = Phpfox::getUserBy('email');
            $sWhere .= ' AND ((invited_user_id = ' . Phpfox::getUserId() . ' AND (invited_email = "" OR invited_email IS NULL)) OR (invited_user_id = 0 AND invited_email = "' . $email . '"))';
        } else {
            $sWhere .= ' AND (invited_user_id = 0 AND invited_email = "' . $email . '")';
        }
        $count = db()->select('COUNT(*)')
            ->from(':pages_invite')
            ->where($sWhere)
            ->execute('getSlaveField');
        return (bool)$count;
    }

    public function getForEditWidget($iId)
    {
        $aWidget = $this->database()->select('pw.*, pwt.text_parsed AS text')
            ->from(Phpfox::getT('pages_widget'), 'pw')
            ->join(Phpfox::getT('pages_widget_text'), 'pwt', 'pwt.widget_id = pw.widget_id')
            ->where('pw.widget_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aWidget['widget_id'])) {
            return false;
        }

        $aPage = $this->getPage($aWidget['page_id']);

        if (!isset($aPage['page_id'])) {
            return false;
        }

        if (!$this->isAdmin($aPage) && !$this->getFacade()->getUserParam('can_moderate_pages') && !Phpfox::getUserParam('groups.can_edit_all_groups')) {
            return false;
        }

        return $aWidget;
    }

    public function getFeatured($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('groups_featured');
        if (($sPageIds = $this->cache()->get($sCacheId, $iCacheTime)) === false || !$iCacheTime) {
            $sPageIds = '';
            $sWhere = 'p.view_id = 0 AND p.is_featured = 1 AND p.item_type = 1';
            $aPageIds = db()->select('p.page_id')
                ->from($this->_sTable, 'p')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->where($sWhere)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');

            foreach ($aPageIds as $key => $aId) {
                if ($key != 0) {
                    $sPageIds .= ',' . $aId['page_id'];
                } else {
                    $sPageIds = $aId['page_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sPageIds);
            }
        }
        if (empty($sPageIds)) {
            return [];
        }
        $aPageIds = explode(',', $sPageIds);
        shuffle($aPageIds);
        $aPageIds = array_slice($aPageIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aPages = $this->database()->select('p.*, pt.name as type_name, pc.name as category_name, ph.destination as cover_image_path, ph.server_id as cover_image_server_id, pu.vanity_url, u.server_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(':user', 'u', 'u.profile_page_id = p.page_id')
            ->leftJoin(':pages_url', 'pu', 'pu.page_id = p.page_id')
            ->leftJoin(':photo', 'ph', 'ph.photo_id = p.cover_photo_id')
            ->leftJoin(':pages_type', 'pt', 'p.type_id = pt.type_id')
            ->leftJoin(':pages_category', 'pc', 'p.category_id = pc.category_id')
            ->where('p.page_id IN (' . implode(',', $aPageIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        foreach ($aPages as $iKey => $aPage) {
            $aPages[$iKey]['is_app'] = false;
            $aPages[$iKey]['is_user_page'] = true;
            $aPages[$iKey]['user_image'] = sprintf($aPage['image_path'], '_200_square');
            $aPages[$iKey]['url'] = $this->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        }

        shuffle($aPages);

        return $aPages;
    }

    public function getSponsored($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('groups_sponsored');
        if (($sPageIds = $this->cache()->get($sCacheId, $iCacheTime)) === false || !$iCacheTime) {
            $sPageIds = '';
            $sWhere = 'p.view_id = 0 AND p.is_sponsor = 1 AND s.module_id = \'groups\' AND s.is_active = 1 AND s.is_custom = 3';
            $aPageIds = db()->select('p.page_id')
                ->from($this->_sTable, 'p')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = p.page_id')
                ->where($sWhere)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aPageIds as $key => $aId) {
                if ($key != 0) {
                    $sPageIds .= ',' . $aId['page_id'];
                } else {
                    $sPageIds = $aId['page_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sPageIds);
            }
        }
        if (empty($sPageIds)) {
            return [];
        }

        $aPageIds = explode(',', $sPageIds);
        shuffle($aPageIds);
        $aPageIds = array_slice($aPageIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aPages = $this->database()->select('p.*, pt.name as type_name, pc.name as category_name, ph.destination as cover_image_path, ph.server_id as cover_image_server_id, pu.vanity_url, u.server_id, ' . Phpfox::getUserField() . ', s.*')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.profile_page_id = p.page_id')
            ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = p.page_id AND s.module_id = \'groups\' AND s.is_active = 1 AND s.is_custom = 3')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->leftJoin(':photo', 'ph', 'ph.photo_id = p.cover_photo_id')
            ->leftJoin(':pages_type', 'pt', 'p.type_id = pt.type_id')
            ->leftJoin(':pages_category', 'pc', 'p.category_id = pc.category_id')
            ->where('p.page_id IN (' . implode(',', $aPageIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');

        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aPages = Phpfox::getService('ad')->filterSponsor($aPages);
        }
        foreach ($aPages as $iKey => $aPage) {
            $aPages[$iKey]['is_app'] = false;
            $aPages[$iKey]['is_user_page'] = true;
            $aPages[$iKey]['user_image'] = sprintf($aPage['image_path'], '_200_square');
            $aPages[$iKey]['url'] = Phpfox::getLib('url')->makeUrl('ad.sponsor', ['view' => $aPage['sponsor_id']]);
        }

        shuffle($aPages);

        return $aPages;
    }

    public function canUserCreateNewGroup($iUserId = null, $bThrowError = true)
    {
        $iResult = Phpfox::getUserParam('groups.pf_group_add', $bThrowError);
        if (!$iResult) {
            return false;
        }
        $iMaxGroups = (int)Phpfox::getUserParam('groups.max_groups_created');
        if (!$iMaxGroups) {
            return true;
        }
        if (!$iUserId) {
            $iUserId = (int)Phpfox::getUserId();
        }
        $iTotalGroup = $this->database()->select('COUNT(p.page_id)')
            ->from(':pages', 'p')
            ->join(':user', 'u', 'u.user_id = p.user_id')
            ->where([
                'p.item_type' => 1,
                'p.user_id' => $iUserId
            ])
            ->executeField();
        if ($iMaxGroups <= $iTotalGroup) {
            return $bThrowError ? Phpfox_Error::set(_p('you_have_reached_your_limit_you_are_currently_unable_to_create_new_group')) : false;
        }
        return true;
    }

    public function canViewItem($iGroupId, $bReturnItem = false)
    {
        if (!Phpfox::getUserParam('groups.pf_group_browse')) {
            Phpfox_Error::set(_p('You don\'t have permission to {{ action }} {{ items }}.',
                ['action' => _p('view__l'), 'items' => _p('Group')]));
            return false;
        }
        $aGroup = Phpfox::getService('groups')->getForView($iGroupId);
        if (empty($aGroup) || ($aGroup['view_id'] != '0' &&
                !(Phpfox::getUserParam('groups.can_approve_groups') || Phpfox::getUserParam('groups.can_edit_all_groups') ||
                    Phpfox::getUserParam('groups.can_delete_all_groups') || $aGroup['is_admin'])
            )) {
            Phpfox_Error::set(_p('the_group_you_are_looking_for_cannot_be_found'));
            return false;
        }
        if ($aGroup['view_id'] == '2') {
            Phpfox_Error::set(_p('the_group_you_are_looking_for_cannot_be_found'));
            return false;
        }
        if (!Phpfox::getService('groups')->isMember($aGroup['page_id']) && Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy')) {
            if (!Phpfox::getService('privacy')->check('pages', $aGroup['page_id'], $aGroup['user_id'], $aGroup['privacy'],
                (isset($aGroup['is_friend']) ? $aGroup['is_friend'] : 0), true, true)) {
                return false;
            }
        }
        $this->extraGroupInformation($aGroup);
        return $bReturnItem ? $aGroup : true;
    }

    public function extraGroupInformation(&$aGroup)
    {
        $aGroup['avatar'] = $aGroup['cover'] = $aGroup['location'] = $aGroup['category'] = null;
        if (!empty($aGroup['pages_image_path'])) {
            $aGroup['avatar'] = Phpfox::getLib('image.helper')->display([
                'server_id' => $aGroup['image_server_id'],
                'path' => 'pages.url_image',
                'file' => $aGroup['pages_image_path'],
                'suffix' => '_500_square',
                'return_url' => true
            ]);
        }
        if (!empty($aGroup['location_latitude']) && !empty($aGroup['location_longitude']) && isset($aGroup['location_name'])) {
            $aGroup['location'] = [
                'latitude' => $aGroup['location_latitude'],
                'longitude' => $aGroup['location_longitude'],
                'location_name' => $aGroup['location_name']
            ];
        }
        if (!empty($aGroup['category_id'])) {
            $aGroup['sub_category'] = [
                'category_id' => $aGroup['category_id'],
                'name' => $aGroup['category_name'],
                'link' => isset($aGroup['category_link']) ? $aGroup['category_link'] : Phpfox::getLib('url')->permalink('groups.sub-category', $aGroup['category_id'], $aGroup['category_name'])
            ];
        }
        $aCover = ($aGroup['cover_photo_id'] ? Phpfox::getService('photo')->getCoverPhoto($aGroup['cover_photo_id']) : false);
        if (!empty($aCover)) {
            $aGroup['cover'] = [
                'url' => Phpfox::getLib('image.helper')->display([
                    'server_id' => $aCover['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => $aCover['destination'],
                    'suffix' => '',
                    'return_url' => true
                ]),
                'position' => $aGroup['cover_photo_position']
            ];
        }
        if (!empty($aGroup['type_id'])) {
            $aGroup['category'] = [
                'type_id' => $aGroup['type_id'],
                'name' => $aGroup['parent_category_name'],
                'link' => isset($aGroup['type_link']) ? $aGroup['type_link'] : Phpfox::getLib('url')->permalink('groups.category', $aGroup['type_id'], $aGroup['parent_category_name'])
            ];
        }
        $aGroup['info'] = $this->getInfo($aGroup['page_id'], true);
    }
}
