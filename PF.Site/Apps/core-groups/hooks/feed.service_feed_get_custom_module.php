<?php
if (!defined('PHPFOX_IS_PAGES_VIEW') && !empty($aReturn) && (defined('PHPFOX_CHECK_FOR_UPDATE_FEED') || defined('PHPFOX_CHECK_FEEDS_FOR_GROUPS'))) {
    static $cachedGroupMembers = [];
    static $cachedGroupUserIds = [];

    $iGroupId = !empty($aReturn['parent_user']['parent_profile_page_id']) ? $aReturn['parent_user']['parent_profile_page_id'] : 0;
    if (empty($iGroupId)) {
        if (in_array($aReturn['type_id'], ['groups_photo', 'groups_cover_photo'])) {
            if (!isset($cachedGroupUserIds[$aReturn['user_id']])) {
                $aGroupUser = Phpfox::getService('user')->getUser($aReturn['user_id'], 'u.profile_page_id');
                $iGroupId = $cachedGroupUserIds[$aReturn['user_id']] = (int)$aGroupUser['profile_page_id'];
            } else {
                $iGroupId = $cachedGroupUserIds[$aReturn['user_id']];
            }
        } elseif ($aReturn['type_id'] == 'groups_comment') {
            $iGroupId = $aReturn['custom_data_cache']['parent_profile_page_id'];
        } elseif (!in_array($aReturn['type_id'], ['user_photo', 'user_cover', 'pages_photo', 'pages_cover_photo'])) {
            $iGroupId = (int)db()->select('parent_user_id')
                ->from(':pages_feed')
                ->where([
                    'type_id' => $aReturn['type_id'],
                    'item_id' => $aReturn['item_id']
                ])->executeField();
        }
    }
    if (!empty($iGroupId)) {
        $isFeedDetail = $this->request()->getInt('status-id')
            || $this->request()->getInt('comment-id')
            || $this->request()->getInt('link-id')
            || $this->request()->getInt('poke-id')
            || $this->request()->getInt('feed');

        if (!$isFeedDetail) {
            if (!isset($cachedGroupMembers[$iGroupId])) {
                $iItemType = (int)db()->select('item_type')
                    ->from(':pages')
                    ->where([
                        'page_id' => $iGroupId,
                    ])->executeField();
                $cachedGroupMembers[$iGroupId] = $iItemType == 1 ? (Phpfox::getService('groups')->isMember($iGroupId) ? 1 : 0) : -1;
            }
            if ($cachedGroupMembers[$iGroupId] == 0) {
                array_pop($aFeeds);
            }
        }
    }
}