<?php
if (!defined('PHPFOX_IS_PAGES_VIEW') && !empty($aReturn) && (defined('PHPFOX_CHECK_FOR_UPDATE_FEED') || defined('PHPFOX_CHECK_FEEDS_FOR_PAGES'))) {
    static $cachedPageMembers = [];
    static $cachedPageUserIds = [];

    $iPageId = !empty($aReturn['parent_user']['parent_profile_page_id']) ? $aReturn['parent_user']['parent_profile_page_id'] : 0;
    if (empty($iPageId)) {
        if (in_array($aReturn['type_id'], ['pages_photo', 'pages_cover_photo'])) {
            if (!isset($cachedPageUserIds[$aReturn['user_id']])) {
                $aPageUser = Phpfox::getService('user')->getUser($aReturn['user_id'], 'u.profile_page_id');
                $iPageId = $cachedPageUserIds[$aReturn['user_id']] = (int)$aPageUser['profile_page_id'];
            } else {
                $iPageId = $cachedPageUserIds[$aReturn['user_id']];
            }
        } elseif ($aReturn['type_id'] == 'pages_comment') {
            $iPageId = $aReturn['custom_data_cache']['parent_profile_page_id'];
        } elseif (!in_array($aReturn['type_id'], ['user_photo', 'user_cover', 'groups_photo', 'groups_cover_photo'])) {
            $iPageId = (int)db()->select('parent_user_id')
                ->from(':pages_feed')
                ->where([
                    'type_id' => $aReturn['type_id'],
                    'item_id' => $aReturn['item_id']
                ])->executeField();
        }
    }
    if (!empty($iPageId)) {
        $isFeedDetail = $this->request()->getInt('status-id')
            || $this->request()->getInt('comment-id')
            || $this->request()->getInt('link-id')
            || $this->request()->getInt('poke-id')
            || $this->request()->getInt('feed');

        if (!$isFeedDetail) {
            if (!isset($cachedPageMembers[$iPageId])) {
                $iItemType = (int)db()->select('item_type')
                    ->from(':pages')
                    ->where([
                        'page_id' => $iPageId,
                    ])->executeField();
                $cachedPageMembers[$iPageId] = $iItemType == 0 ? (Phpfox::getService('pages')->isMember($iPageId) ? 1 : 0) : -1;
            }
            if ($cachedPageMembers[$iPageId] == 0) {
                array_pop($aFeeds);
            }
        }
    }
}