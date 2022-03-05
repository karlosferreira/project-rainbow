<?php

namespace Apps\PHPfox_Groups\Service;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Template;
use Phpfox_Url;

/**
 * Class Callback
 *
 * @package Apps\PHPfox_Groups\Service
 */
class Callback extends \Phpfox_Service
{
    public function __construct()
    {
        \Phpfox::getService('groups')->setIsInPage();
    }

    public function getFacade()
    {
        return Phpfox::getService('groups.facade');
    }

    public function updateCommentFeedType($params)
    {
        if (Phpfox::isModule('notification')) {
            db()->update(':notification', [
                'type_id' => $params['new_type'] == 'groups_comment' ? $params['new_type'] . '_feed' : 'comment_' . str_replace('_comment', '', $params['new_type']),
                'item_id' => $params['new_item_id']
            ], [
                'type_id' => $params['old_type'] == 'groups_comment' ? $params['old_type'] . '_feed' : 'comment_' . str_replace('_comment', '', $params['old_type']),
                'item_id' => $params['old_item_id']
            ]);
        }

        $newCommentType = str_replace('_comment', '', $params['new_type']);
        $oldCommentType = str_replace('_comment', '', $params['old_type']);

        db()->update(':comment', [
            'type_id' => $newCommentType,
            'item_id' => $params['new_item_id']
        ], [
            'type_id' => $oldCommentType,
            'item_id' => $params['old_item_id']
        ]);

        $totalComments = db()->select('COUNT(*)')
            ->from(':comment')
            ->where([
                'type_id' => $newCommentType,
                'item_id' => $params['new_item_id'],
                'parent_id' => 0
            ])->executeField(false);

        if ($totalComments) {
            if ($newCommentType == 'groups') {
                db()->update(':pages_feed_comment', ['total_comment' => $totalComments], ['feed_comment_id' => $params['new_item_id']]);
            } else {
                switch ($newCommentType) {
                    case 'link':
                        $table = 'link';
                        $field = 'link_id';
                        break;
                }
                if ($table && $field) {
                    db()->update(':' . $table, ['total_comment' => $totalComments], [$field => $params['new_item_id']]);
                }
            }
        }
    }

    public function updateLikeFeedType($params)
    {
        db()->update(':like', [
            'type_id' => $params['new_type'],
            'item_id' => $params['new_item_id'],
        ], [
            'type_id' => $params['old_type'],
            'item_id' => $params['old_item_id'],
        ]);
    }

    public function updateNotificationFeedType($params)
    {
        db()->update(':notification', [
            'type_id' => $params['new_type'] . '_like',
            'item_id' => $params['new_item_id']
        ], [
            'type_id' => $params['old_type'] . '_like',
            'item_id' => $params['old_item_id'],
        ]);
    }

    public function approveProfilePhoto($photoId)
    {
        Phpfox::getService('user.process')->processAfterProfilePhotoApprovedFromPhotoApp($photoId);
        $groupId = db()->select('group_id')
            ->from(':photo')
            ->where([
                'photo_id' => $photoId,
            ])->executeField(false);
        if (empty($groupId)) {
            return false;
        }

        $aGroup = Phpfox::getService('groups')->getPage($groupId);
        $iGroupUserId = Phpfox::getService('groups')->getUserId($aGroup['page_id']);
        if (!empty($aGroup['image_path'])) {
            Phpfox::getService('groups.process')->deleteImage($aGroup);
        }

        $pendingPhotoCacheKey = 'groups_profile_photo_pending_' . $aGroup['page_id'];
        $pendingPhotoCache = storage()->get($pendingPhotoCacheKey);
        if (!empty($pendingPhotoCache) && !empty($pendingPhotoCache->value->image_path)) {
            db()->update(':pages', [
                'image_path' => $pendingPhotoCache->value->image_path,
                'image_server_id' => $pendingPhotoCache->value->image_server_id,
            ], ['page_id' => $aGroup['page_id']]);
            storage()->del($pendingPhotoCacheKey);
        }

        // add feed after updating group's profile image
        if (Phpfox::isModule('feed') && Phpfox::getParam('photo.photo_allow_posting_user_photo_feed', 1) && ($oProfileImage = storage()->get('user/avatar/' . $iGroupUserId))) {
            Phpfox::getService('feed.process')->callback([
                'table_prefix' => 'pages_',
                'module' => 'groups',
                'add_to_main_feed' => true,
                'has_content' => true
            ])->add('groups_photo', $oProfileImage->value, 0, 0, $aGroup['page_id'], $iGroupUserId);
        }
    }

    public function approveCoverPhoto($photoId)
    {
        if (empty($photoId)) {
            return false;
        }

        $groupId = db()->select('group_id')
                    ->from(':photo')
                    ->where(['photo_id' => $photoId])
                    ->executeField(false);
        if (empty($groupId)) {
            return false;
        }

        $pendingCacheKey = 'groups_cover_photo_pending_' . $groupId;
        $pendingCache = storage()->get($pendingCacheKey);
        if (empty($pendingCache) || $pendingCache->value->photo_id != $photoId) {
            return false;
        }
        db()->update(Phpfox::getT('pages'),
            ['cover_photo_position' => '', 'cover_photo_id' => (int)$photoId], 'page_id = ' . (int)$groupId);
        if (Phpfox::isModule('feed') && Phpfox::getParam('photo.photo_allow_posting_user_photo_feed', 1)) {
            // create feed after changing cover
            Phpfox::getService('feed.process')->callback([
                'table_prefix' => 'pages_',
                'module' => 'groups',
                'add_to_main_feed' => true,
                'has_content' => true
            ])->add('groups_cover_photo', $photoId, 0, 0, $groupId, Phpfox::getService('groups')->getUserId($groupId));

        }

        if (!empty($pendingCache->value->album_id)) {
            db()->update(':photo', ['is_cover' => 0], 'album_id=' . (int)$pendingCache->value->album_id);
            db()->update(':photo', [
                'album_id'         => $pendingCache->value->album_id,
                'is_cover'         => 1,
                'is_profile_photo' => 0,
                'view_id' => 0,
            ], 'photo_id=' . (int)$photoId);
            Phpfox::getService('photo.album.process')->updateCounter((int)$pendingCache->value->album_id, 'total_photo');
        }

        storage()->del($pendingCacheKey);

        $repositionCacheObject = storage()->get('photo_cover_reposition_' . $photoId);
        if (is_object($repositionCacheObject) && isset($repositionCacheObject->value) && $repositionCacheObject->value != '') {
            Phpfox::getService('groups.process')->updateCoverPosition($groupId, $repositionCacheObject->value);
            storage()->del('photo_cover_reposition_' . $photoId);
        }
    }

    public function getGroupPerms()
    {
        $aPerms = [
            'groups.share_updates' => _p('who_can_share_a_post'),
            'groups.view_admins' => _p('who_can_view_admins'),
            'groups.view_publish_date' => _p('groups_who_can_view_publish_date'),
            'groups.view_browse_widgets' => _p('groups_who_can_view_widgets'),
        ];

        return $aPerms;
    }

    public function getNotificationInvite($aNotification)
    {
        $aRow = \Phpfox::getService('groups')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('{{ users }} invited you to check out the group "{{ title }}"', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => \Phpfox::getLib('parse.output')->shorten($aRow['title'],
                \Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getProfileLink()
    {
        return 'profile.groups';
    }

    public function getProfileMenu($aUser)
    {
        if (!Phpfox::getUserParam('groups.pf_group_browse')) {
            return false;
        }

        if (\Phpfox::getParam('profile.show_empty_tabs') == false) {
            if (!isset($aUser['total_groups'])) {
                return false;
            }

            if (isset($aUser['total_groups']) && (int)$aUser['total_groups'] === 0) {
                return false;
            }
        }

        $iTotal = (int)(isset($aUser['total_groups']) ? $aUser['total_groups'] : 0);
        if (!(Phpfox::getUserParam('core.can_view_private_items') || $aUser['user_id'] == Phpfox::getUserId())) {
            $iSecretCount = $this->database()->select('COUNT(*)')
                ->from(\Phpfox::getT('pages'), 'p')
                ->where('p.user_id = ' . $aUser['user_id'] . ' AND p.reg_method = 2')
                ->execute('getSlaveField');
            $iTotal -= $iSecretCount;
        }
        $aMenus[] = [
            'phrase' => _p('Groups'),
            'url' => 'profile.groups',
            'total' => $iTotal,
            'icon' => 'feed/blog.png'
        ];

        return $aMenus;
    }

    public function canShareItemOnFeed()
    {
    }

    public function getActivityFeed($aItem, $aCallback = null, $bIsChildItem = false)
    {
        $itemId = (int)$aItem['item_id'];
        $feedId = (int)$aItem['feed_id'];
        $this->database()->select(\Phpfox::getUserField('u2') . ', ')->join(\Phpfox::getT('user'), 'u2',
            'u2.user_id = p.user_id');
        $aRow = $this->database()->select('p.reg_method, p.privacy, p.time_stamp, p.page_id, p.type_id, p.category_id, p.total_like, p.cover_photo_id, p.title, pu.vanity_url, p.image_path, p.image_server_id, p_type.name AS parent_category_name, pg.name AS category_name, pt.text_parsed')
            ->from(Phpfox::getT('pages'), 'p')
            ->join(Phpfox::getT('pages_text'), 'pt', 'pt.page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_category'), 'pg', 'pg.category_id = p.category_id')
            ->leftJoin(Phpfox::getT('pages_type'), 'p_type', 'p_type.type_id = pg.type_id')
            ->where('p.page_id = ' . $itemId . ' AND p.item_type = 1')
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }
        
        if ((!\Phpfox::getService('groups')->isMember($aRow['page_id']) && in_array((int)$aRow['reg_method'], [2]))) {
            return false;
        }

        if ($bIsChildItem) {
            $aItem = array_merge($aRow, ['feed_id' => $aItem['feed_id']]);
        }

        $type = $this->getFacade()->getType()->getById($aRow['type_id']);

        if (empty($aRow['category_name'])) {
            $aRow['category_name'] = ucfirst(\Core\Lib::phrase()->isPhrase($type['name']) ? _p($type['name']) : $type['name']);
            $aRow['category_link'] = Phpfox::permalink('groups.category', $aRow['type_id'], $type['name']);
        } else {
            $aRow['category_name'] = \Core\Lib::phrase()->isPhrase($aRow['category_name']) ? _p($aRow['category_name']) : $aRow['category_name'];
            $aRow['type_link'] = Phpfox::permalink('groups.category', $aRow['type_id'], $type['name']);
            $aRow['category_link'] = Phpfox::permalink('groups.sub-category', $aRow['category_id'],
                \Core\Lib::phrase()->isPhrase($aRow['category_name']) ? _p($aRow['category_name']) : $aRow['category_name']);
        }

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
        $aRow['group_url'] = $sLink;

        $iTotalLikes = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'groups_created'")
            ->execute('getSlaveField');
        $iIsLikedFeed = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'groups_created'" . ' AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');

        $aRow['is_joined_group'] = Phpfox::getService('groups')->isMember($itemId);

        \Phpfox_Component::setPublicParam('custom_param_feed_group_' . $feedId, $aRow);

        $aRow['full_name'] = Phpfox::getLib('parse.output')->clean($aRow['full_name']);
        $sFullName = Phpfox::getLib('parse.output')->shorten($aRow['full_name'], 0);
        $aReturn = [
            'feed_title' => $aRow['title'],
            'no_user_show' => true,
            'feed_link' => $sLink,
            'feed_info' => _p('groups_user_created_group', [
                'title' => '<a href="' . $sLink . '" title="' . \Phpfox::getLib('parse.output')->clean($aRow['title']) . '">' . \Phpfox::getLib('parse.output')->clean(\Phpfox::getLib('parse.output')->shorten($aRow['title'],
                        50, '...')) . '</a>',
                'full_name' => '<span class="user_profile_link_span" id="js_user_name_link_' . $aRow['user_name'] . '">' . (Phpfox::getService('user.block')->isBlocked(null, $aRow['user_id']) ? '' : '<a href="' . Phpfox::getLib('url')->makeUrl('profile', [$aRow['user_name']])) . '">' . $sFullName . '</a></span>'
            ]),
            'feed_icon' => Phpfox::getLib('image.helper')->display([
                'theme' => 'module/marketplace.png',
                'return_url' => true
            ]),
            'return_url' => true,
            'time_stamp' => $aRow['time_stamp'],
            'like_type_id' => 'groups_created',
            'like_item_id' => $feedId,
            'feed_total_like' => $iTotalLikes,
            'feed_is_liked' => (int)$iIsLikedFeed > 0,
            'load_block' => 'groups.feed-group',
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        return $aReturn;
    }

    public function getCommentNotificationTag($aNotification)
    {
        $aRow = $this->database()->select('b.page_id, b.title, pu.vanity_url, u.full_name, fc.feed_comment_id')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('pages_feed_comment'), 'fc', 'fc.feed_comment_id = c.item_id')
            ->join(Phpfox::getT('pages'), 'b', 'b.page_id = fc.parent_user_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = b.page_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!$aRow) {
            return false;
        }

        $sPhrase = _p('full_name_tagged_you_on_a_group', ['full_name' => Phpfox::getService('notification')->getUsers($aRow)]);

        return [
            'link' => Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'],
                    $aRow['vanity_url']) . 'comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationPost_Tag($aNotification)
    {
        $aRow = $this->database()->select('p.page_id, p.title, pu.vanity_url, u.full_name, fc.feed_comment_id')
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('pages'), 'p', 'p.page_id = fc.parent_user_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->where('fc.feed_comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!$aRow) {
            return false;
        }

        $sPhrase = _p('full_name_tagged_you_in_a_group_group_name_post', [
            'full_name' => $aRow['full_name'],
            'group_name' => $aRow['title']
        ]);

        return [
            'link' => Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'],
                    $aRow['vanity_url']) . 'comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Site statistics
     * @param $iStartTime
     * @param $iEndTime
     * @return array
     */
    public function getSiteStatsForAdmin($iStartTime, $iEndTime)
    {
        $aCond = [];
        $aCond[] = 'app_id = 0 AND view_id = 0 AND item_type = 1';
        if ($iStartTime > 0) {
            $aCond[] = 'AND time_stamp >= \'' . $this->database()->escape($iStartTime) . '\'';
        }
        if ($iEndTime > 0) {
            $aCond[] = 'AND time_stamp <= \'' . $this->database()->escape($iEndTime) . '\'';
        }

        $iCnt = (int)$this->database()->select('COUNT(*)')
            ->from(\Phpfox::getT('pages'))
            ->where($aCond)
            ->execute('getSlaveField');

        return [
            'phrase' => 'groups',
            'total' => $iCnt
        ];
    }

    public function addPhoto($iId)
    {
        \Phpfox::getService('groups')->setIsInPage();

        return [
            'module' => 'groups',
            'item_id' => $iId,
            'table_prefix' => 'pages_',
            'add_to_main_feed' => true
        ];
    }

    public function getDashboardActivity()
    {
        if (!Phpfox::getUserParam('groups.pf_group_browse')) {
            return [];
        }
        $aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);
        return [
            _p('Groups') => $aUser['activity_groups']
        ];
    }

    public function getCommentNotification($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.page_id, e.title, pu.vanity_url')
            ->from(\Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(\Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->join(\Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->leftJoin(\Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->where('fc.feed_comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['feed_comment_id'])) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id'] && isset($aNotification['extra_users']) && count($aNotification['extra_users'])) {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification, true);
        } else {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        }
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            \Phpfox::getParam('notification.total_notification_title_length'), '...');

        if ($aNotification['user_id'] == $aRow['user_id']) {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users'])) {
                $sPhrase = _p('groups:users_commented_on_full_name_comment',
                    ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
            } else {
                $sPhrase = _p('groups:users_commented_on_gender_own_comment', [
                    'users' => $sUsers,
                    'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                    'title' => $sTitle
                ]);
            }
        } elseif ($aRow['user_id'] == \Phpfox::getUserId()) {
            $sPhrase = _p('groups:users_commented_on_one_of_your_comments',
                ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('groups:users_commented_on_one_of_full_name_comments',
                ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'link' => $sLink . 'wall/comment-id_' . $aRow['feed_comment_id'],
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getPhotoDetails($aPhoto)
    {
        \Phpfox::getService('groups')->setIsInPage();

        $aRow = \Phpfox::getService('groups')->getPage($aPhoto['group_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        \Phpfox::getService('groups')->setMode();

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('Groups'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('groups'),
            'module_id' => 'groups',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'photo/',
            'theater_mode' => _p('In the group <a href="{{ link }}">{{ title }}</a>',
                ['link' => $sLink, 'title' => $aRow['title']]),
            'set_default_phrase' => _p('Set as Group\'s Cover Photo'),
            'feed_table_prefix' => 'pages_'
        ];
    }

    public function getPhotoCount($iPageId)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(\Phpfox::getT('photo'))
            ->where("module_id = 'pages' AND group_id = " . $iPageId)
            ->execute('getSlaveField');

        return ($iCnt > 0) ? $iCnt : 0;
    }

    public function getAlbumCount($iPageId)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(\Phpfox::getT('photo_album'))
            ->where("module_id = 'pages' AND group_id = " . $iPageId)
            ->execute('getSlaveField');

        return ($iCnt > 0) ? $iCnt : 0;
    }


    public function addLink($aVals)
    {
        return [
            'module' => 'groups',
            'add_to_main_feed' => true,
            'item_id' => $aVals['callback_item_id'],
            'table_prefix' => 'pages_'
        ];
    }

    public function getFeedDisplay($iGroup)
    {
        $aGroup = \Phpfox::getService('groups')->getPage($iGroup);
        if (!$aGroup) {
            return false;
        }
        $bDisableShare = ($aGroup['reg_method'] == 0) ? false : true;
        if (!$bDisableShare) {
            $bDisableShare = !\Phpfox::getService('groups')->hasPerm($iGroup, 'groups.share_updates');
        }

        return [
            'module' => 'groups',
            'table_prefix' => 'pages_',
            'ajax_request' => 'groups.addFeedComment',
            'item_id' => $iGroup,
            'disable_share' => $bDisableShare
        ];
    }

    public function getActivityFeedCustomChecksComment($aRow)
    {
        if ((defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm(null,
                    'groups.view_browse_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm($aRow['custom_data_cache']['page_id'],
                    'groups.view_browse_updates'))
            || (defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm(null,
                    'groups.share_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm($aRow['custom_data_cache']['page_id'],
                    'groups.share_updates'))
        ) {
            return false;
        }

        if ($aRow['custom_data_cache']['reg_method'] == 2 &&
            (
                !\Phpfox::getService('groups')->isMember($aRow['custom_data_cache']['page_id']) &&
                !\Phpfox::getService('groups')->isAdmin($aRow['custom_data_cache']['page_id']) &&
                Phpfox::getService('user')->isAdminUser(Phpfox::getUserId())
            )
        ) {
            return false;
        }

        return $aRow;
    }

    public function getActivityFeedComment($aItem, $aCallBack = null, $bIsChildItem = false)
    {
        $aRow = $this->database()->select('fc.*, l.like_id AS is_liked, e.reg_method, e.page_id, e.title, e.app_id AS is_app, pu.vanity_url, ' . \Phpfox::getUserField('u',
                'parent_'))
            ->from(\Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(\Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->join(\Phpfox::getT('user'), 'u', 'u.profile_page_id = e.page_id')
            ->leftJoin(\Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->leftJoin(\Phpfox::getT('like'), 'l',
                'l.type_id = \'groups_comment\' AND l.item_id = fc.feed_comment_id AND l.user_id = ' . \Phpfox::getUserId())
            ->where('fc.feed_comment_id = ' . (int)$aItem['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm(null,
                    'groups.view_browse_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm($aRow['page_id'],
                    'groups.view_browse_updates'))
        ) {
            return false;
        }

        if ($aRow['reg_method'] == 2 &&
            (
                !\Phpfox::getService('groups')->isMember($aRow['page_id']) &&
                !\Phpfox::getService('groups')->isAdmin($aRow['page_id']) &&
                Phpfox::getService('user')->isAdminUser(Phpfox::getUserId())
            )
        ) {
            return false;
        }

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'],
                $aRow['vanity_url']) . 'wall/comment-id_' . $aItem['item_id'] . '/';

        $aReturn = [
            'feed_status' => htmlspecialchars($aRow['content']),
            'feed_link' => $sLink,
            'feed_title' => '',
            'total_comment' => $aRow['total_comment'],
            'feed_icon' => Phpfox::getLib('image.helper')->display([
                'theme' => 'misc/comment.png',
                'return_url' => true
            ]),
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'groups',
            'like_type_id' => 'groups_comment',
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'is_custom_app' => $aRow['is_app'],
            'custom_data_cache' => $aRow
        ];

        if ($aRow['reg_method'] != 0) {
            $aReturn['no_share'] = true;
        }

        $aReturn['parent_user_name'] = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'],
            $aRow['vanity_url']);

        if ($aRow['user_id'] != $aRow['parent_user_id']) {
            if (!defined('PHPFOX_IS_PAGES_VIEW') && !defined('PHPFOX_PAGES_ADD_COMMENT')) {
                $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
            }
        }

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        return $aReturn;
    }

    public function getActivityFeedItemLiked($aItem, $aCallback = null, $bIsChildItem = false)
    {
        $itemId = (int)$aItem['item_id'];
        $feedId = (int)$aItem['feed_id'];
        $this->database()->select(\Phpfox::getUserField('u2') . ', ')->join(\Phpfox::getT('user'), 'u2',
            'u2.user_id = p.user_id');
        $aRow = $this->database()->select('p.page_id, p.title, p.total_like, pu.vanity_url, p.image_path, p.image_server_id')
            ->from(\Phpfox::getT('pages'), 'p')
            ->leftJoin(\Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->where('p.page_id = ' . $itemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
        $iTotalLikes = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'groups_liked'")
            ->execute('getSlaveField');
        $iIsLikedFeed = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'groups_liked'" . ' AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');

        \Phpfox_Component::setPublicParam('custom_param_feed_group_' . $feedId, $aRow);

        $aReturn = [
            'feed_title' => '',
            'feed_info' => _p('joined the group "<a href="{{ link }}" title="{{ link_title }}">{{ title }}</a>".',
                [
                    'link' => $sLink,
                    'link_title' => \Phpfox::getLib('parse.output')->clean($aRow['title']),
                    'title' => \Phpfox::getLib('parse.output')->clean(\Phpfox::getLib('parse.output')->shorten($aRow['title'],
                        50, '...'))
                ]),
            'feed_link' => $sLink,
            'no_target_blank' => true,
            'feed_icon' => \Phpfox::getLib('image.helper')->display([
                'theme' => 'misc/comment.png',
                'return_url' => true
            ]),
            'time_stamp' => $aItem['time_stamp'],
            'like_type_id' => 'groups_liked',
            'feed_total_like' => $iTotalLikes,
            'feed_is_liked' => (int)$iIsLikedFeed > 0,
            'like_item_id' => $feedId
        ];

        if (!empty($aRow['image_path'])) {
            $sImage = \Phpfox::getLib('image.helper')->display([
                    'server_id' => $aRow['image_server_id'],
                    'path' => 'pages.url_image',
                    'file' => $aRow['image_path'],
                    'suffix' => '_120_square',
                    'max_width' => 120,
                    'max_height' => 120
                ]
            );

            $aReturn['feed_image'] = $sImage;
        }

        if ($bIsChildItem) {
            $aRow['full_name'] = Phpfox::getLib('parse.output')->clean($aRow['full_name']);
            $sFullName = Phpfox::getLib('parse.output')->shorten($aRow['full_name'], 0);
            $aReturn['no_user_show'] = true;
            $aReturn['feed_info'] = _p('groups_user_created_group', [
                'title' => '<a href="' . $sLink . '" title="' . \Phpfox::getLib('parse.output')->clean($aRow['title']) . '">' . \Phpfox::getLib('parse.output')->clean(\Phpfox::getLib('parse.output')->shorten($aRow['title'],
                        50, '...')) . '</a>',
                'full_name' => '<span class="user_profile_link_span" id="js_user_name_link_' . $aRow['user_name'] . '">' . (Phpfox::getService('user.block')->isBlocked(null, $aRow['user_id']) ? '' : '<a href="' . Phpfox::getLib('url')->makeUrl('profile', [$aRow['user_name']])) . '">' . $sFullName . '</a></span>'
            ]);
            $aReturn = array_merge($aReturn, $aItem);
        }

        return $aReturn;

    }

    public function addEvent($iItem)
    {
        \Phpfox::getService('groups')->setIsInPage();

        $aRow = \Phpfox::getService('groups')->getPage($iItem);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        return $aRow;
    }

    public function viewEvent($iItem)
    {
        $aRow = $this->addEvent($iItem);

        if (!$aRow) {
            return false;
        }

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('Groups'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('groups'),
            'module_id' => 'groups',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_pages' => $sLink . 'event/'
        ];
    }

    public function getFeedDetails($iItemId)
    {
        return [
            'module' => 'groups',
            'table_prefix' => 'pages_',
            'item_id' => $iItemId,
            'add_to_main_feed' => true
        ];
    }

    public function deleteFeedItem($callbackData)
    {
        if (empty($callbackData['type_id']) || empty($callbackData['item_id'])) {
            return false;
        }

        // delete feed from main feed
        db()->delete(':feed', ['type_id' => $callbackData['type_id'], 'item_id' => $callbackData['item_id']]);
        if ($callbackData['type_id'] == 'groups_comment') {
            $aFeedComment = $this->database()->select('*')
                ->from(\Phpfox::getT('pages_feed_comment'))
                ->where('feed_comment_id = ' . (int)$callbackData['item_id'])
                ->execute('getSlaveRow');

            if (empty($aFeedComment) || empty($aFeedComment['parent_user_id'])) {
                return;
            }

            $iTotalComments = $this->database()->select('COUNT(*)')
                ->from(\Phpfox::getT('pages_feed'))
                ->where('type_id = \'groups_comment\' AND parent_user_id = ' . $aFeedComment['parent_user_id'])
                ->execute('getSlaveField');

            $this->database()->update(\Phpfox::getT('pages'), ['total_comment' => $iTotalComments],
                'page_id = ' . (int)$aFeedComment['parent_user_id']);
        }
    }

    public function deleteLike($iItemId, $iUserId = 0)
    {
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        // Get the threads from this page
        if (db()->tableExists(Phpfox::getT('forum_thread'))) {
            $aRows = $this->database()->select('thread_id')
                ->from(\Phpfox::getT('forum_thread'))
                ->where('group_id = ' . (int)$iItemId)
                ->execute('getSlaveRows');

            $aThreads = [];
            foreach ($aRows as $sKey => $aRow) {
                $aThreads[] = $aRow['thread_id'];
            }
            if (!empty($aThreads)) {
                $this->database()->delete(Phpfox::getT('forum_subscribe'),
                    'user_id = ' . Phpfox::getUserId() . ' AND thread_id IN (' . implode(',', $aThreads) . ')');
            }
        }

        $aRow = Phpfox::getService('groups')->getPage($iItemId);
        if (!isset($aRow['page_id'])) {
            return false;
        }

        $totalLike = db()->select('total_like')
            ->from(Phpfox::getT('pages'))
            ->where('page_id = ' . (int)$iItemId)
            ->execute('getSlaveField');

        $this->database()->updateCount('like', 'type_id = \'groups\' AND item_id = ' . (int)$iItemId . '', 'total_like', 'pages', 'page_id = ' . (int)$iItemId);

        if (defined('PHPFOX_CANCEL_ACCOUNT') && PHPFOX_CANCEL_ACCOUNT) {
            db()->update(Phpfox::getT('pages'), ['total_like' => ['= total_like - ', 1]], 'page_id = ' . (int)$iItemId . ' AND total_like = ' . (int)$totalLike);
        }

        $iFriendId = (int)$this->database()->select('user_id')
            ->from(Phpfox::getT('user'))
            ->where('profile_page_id = ' . (int)$aRow['page_id'])
            ->execute('getSlaveField');

        $this->database()->delete(Phpfox::getT('friend'), 'user_id = ' . (int)$iFriendId . ' AND friend_user_id = ' . (int)$iUserId);
        $this->database()->delete(Phpfox::getT('friend'), 'friend_user_id = ' . (int)$iFriendId . ' AND user_id = ' . (int)$iUserId);

        // clear cache members
        $this->cache()->remove('groups_' . $iItemId . '_members');
        $this->cache()->remove('member_' . $iUserId . '_secret_groups');
        $this->cache()->remove('member_' . $iUserId . '_groups');

        if (Phpfox::getService('groups')->isAdmin($iItemId, $iUserId)) {
            db()->delete(Phpfox::getT('pages_admin'), 'page_id = ' . (int)$iItemId . ' AND user_id = ' . (int)$iUserId);
            $this->cache()->remove('groups_' . $iItemId . '_admins');
        }

        if ($iUserId == Phpfox::getUserId()) {
            $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
            if (!defined('PHPFOX_CANCEL_ACCOUNT') || PHPFOX_CANCEL_ACCOUNT != true) {
                Phpfox_Ajax::instance()->call('window.location.href = \'' . $sLink . '\';');
                return true;
            }
        } else {  /* Remove invites */ // Its not the user willingly leaving the page
            $this->database()->delete(\Phpfox::getT('pages_invite'), 'page_id = ' . (int)$iItemId . ' AND invited_user_id =' . (int)$iUserId);
        }

        return true;
    }

    public function addLike($iItemId, $bDoNotSendEmail = false, $iUserId = null)
    {
        $aRow = \Phpfox::getService('groups')->getPage($iItemId);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $this->database()->updateCount('like', 'type_id = \'groups\' AND item_id = ' . (int)$iItemId . '', 'total_like', 'pages', 'page_id = ' . (int)$iItemId);
        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
        $bIsApprove = true;
        if ($iUserId === null) {
            $bIsApprove = false;
            if (Phpfox::isUser()) {
                $iUserId = Phpfox::getUserId();
                \Phpfox_Queue::instance()->addJob('groups_member_join_notifications', [
                    'aGroup' => $aRow,
                    'iUserId' => $iUserId
                ]);
            }
        } else {
            \Phpfox::getLib('mail')->to($iUserId)
                ->subject(['Membership accepted to "{{ title }}"', ['title' => $aRow['title']]])
                ->message(['Your membership to the group "<a href="{{ link }}">{{ title }}</a>" has been accepted. To view this group follow the link below: <a href="{{ link }}">{{ link }}</a>',
                    ['link' => $sLink, 'title' => $aRow['title']]])
                ->sendToSelf(true)
                ->notification('groups.email_notification')
                ->send();

            Phpfox::getService('notification.process')->add('groups_joined', $aRow['page_id'], $iUserId, $aRow['user_id'], true);
        }

        $iFriendId = (int)$this->database()->select('user_id')
            ->from(\Phpfox::getT('user'))
            ->where('profile_page_id = ' . (int)$aRow['page_id'])
            ->execute('getSlaveField');

        if ($iUserId) {
            $this->database()->insert(\Phpfox::getT('friend'), [
                    'is_page' => 1,
                    'list_id' => 0,
                    'user_id' => $iUserId,
                    'friend_user_id' => $iFriendId,
                    'time_stamp' => PHPFOX_TIME
                ]
            );

            $this->database()->insert(\Phpfox::getT('friend'), [
                    'is_page' => 1,
                    'list_id' => 0,
                    'user_id' => $iFriendId,
                    'friend_user_id' => $iUserId,
                    'time_stamp' => PHPFOX_TIME
                ]
            );

            // clear cache members
            $this->cache()->remove('groups_' . $iItemId . '_members');
            $this->cache()->remove('member_' . $iUserId . '_secret_groups');
            $this->cache()->remove('member_' . $iUserId . '_groups');
        }

        if (!$bIsApprove) {
            \Phpfox_Ajax::instance()->call('window.location.href = \'' . $sLink . '\';');
        }

        return true;
    }

    public function getMusicDetails($aItem)
    {
        \Phpfox::getService('groups')->setIsInPage();

        $aRow = \Phpfox::getService('groups')->getPage($aItem['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        \Phpfox::getService('groups')->setMode();

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('Groups'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('groups'),
            'module_id' => 'groups',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'music/',
            'theater_mode' => _p('In the group <a href="{{ link }}">{{ title }}</a>',
                ['link' => $sLink, 'title' => $aRow['title']])
        ];
    }

    public function getBlogDetails($aItem)
    {
        \Phpfox::getService('groups')->setIsInPage();
        $aRow = \Phpfox::getService('groups')->getPage($aItem['item_id']);
        if (!isset($aRow['page_id'])) {
            return false;
        }
        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('Groups'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('groups'),
            'module_id' => 'groups',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'blog/',
            'theater_mode' => _p('In the group <a href="{{ link }}">{{ title }}</a>',
                ['link' => $sLink, 'title' => $aRow['title']])
        ];
    }

    public function getVideoDetails($aItem)
    {
        $groupService = Phpfox::getService('groups');

        $aRow = $groupService->getPage($aItem['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $groupService->setMode();

        $sLink = $groupService->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('groups'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('groups'),
            'module_id' => 'groups',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'video/',
            'theater_mode' => _p('in_the_page_link_title', ['link' => $sLink, 'title' => $aRow['title']])
        ];
    }

    public function uploadVideo($aVals)
    {
        \Phpfox::getService('groups')->setIsInPage();
        return [
            'module' => 'groups',
            'item_id' => (is_array($aVals) && isset($aVals['callback_item_id']) ? $aVals['callback_item_id'] : (int)$aVals)
        ];
    }

    public function uploadSong($iItemId)
    {
        \Phpfox::getService('groups')->setIsInPage();

        return [
            'module' => 'groups',
            'item_id' => $iItemId,
            'table_prefix' => 'pages_',
            'add_to_main_feed' => true
        ];
    }

    public function getNotificationJoined($aNotification)
    {
        $aRow = \Phpfox::getService('groups')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => _p('Your membership has been accepted to join the group "{{ title }}".', [
                'title' => \Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    \Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]),
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationRegister($aNotification)
    {
        $aRow = $this->database()->select('p.*, pu.vanity_url, ' . \Phpfox::getUserField())
            ->from(\Phpfox::getT('pages_signup'), 'ps')
            ->join(\Phpfox::getT('pages'), 'p', 'p.page_id = ps.page_id')
            ->join(\Phpfox::getT('user'), 'u', 'u.user_id = ps.user_id')
            ->leftJoin(\Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->where('ps.signup_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        return [
            // 'no_profile_image' => true,
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => _p('full_name_is_requesting_to_join_your_group_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aRow),
                'title' => \Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    \Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]),
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationLike($aNotification)
    {
        $aRow = \Phpfox::getService('groups')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        if (!isset($aRow['gender'])) {
            $sGender = 'their';
        } else {
            $sGender = Phpfox::getService('user')->gender($aRow['gender'], 1);
        }
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            Phpfox::getParam('notification.total_notification_title_length'), '...');

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('{{ users }} joined {{ gender }} own group "{{ title }}"',
                ['users' => $sUsers, 'gender' => $sGender, 'title' => $sTitle]);
        } elseif ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('user_joined_your_group_title', ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $aOwner = Phpfox::getService('groups')->getPageOwner($aRow['page_id']);
            $sPhrase = _p('user_joined_full_name_group_title',
                [
                    'users' => $sUsers,
                    'full_name' => \Phpfox::getLib('parse.output')->shorten($aOwner['full_name'], 0),
                    'title' => $sTitle
                ]);
        }

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function addForum($iId)
    {
        \Phpfox::getService('groups')->setIsInPage();

        $aRow = \Phpfox::getService('groups')->getPage($iId);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'module' => 'groups',
            'module_title' => _p('Groups'),
            'item' => $aRow['page_id'],
            'group_id' => $aRow['page_id'],
            'url_home' => $sLink,
            'title' => $aRow['title'],
            'table_prefix' => 'pages_',
            'item_id' => $aRow['page_id'],
            'add_to_main_feed' => true,
            'breadcrumb_title' => _p('Groups'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('groups'),
        ];
    }

    public function checkFeedShareLink()
    {
        return false;
    }

    public function getAjaxCommentVar()
    {
        return null;
    }

    public function getRedirectComment($iId)
    {
        $aListing = $this->database()->select('pfc.feed_comment_id AS comment_item_id, pfc.privacy_comment, pfc.user_id AS comment_user_id, m.*, pu.vanity_url, pfc.parent_user_id AS item_id')
            ->from(Phpfox::getT('pages_feed_comment'), 'pfc')
            ->join(Phpfox::getT('pages'), 'm', 'm.page_id = pfc.parent_user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = m.page_id')
            ->where('pfc.feed_comment_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aListing['page_id'])) {
            return false;
        }

        return \Phpfox::getService('groups')->getUrl($aListing['page_id'], $aListing['title'],
                $aListing['vanity_url']) . 'comment-id_' . $aListing['comment_item_id'] . '/';
    }

    public function getFeedRedirect($iId, $iChild = 0)
    {
        $aListing = $this->database()->select('m.page_id, m.title, pu.vanity_url, pf.item_id')
            ->from(Phpfox::getT('pages_feed'), 'pf')
            ->join(Phpfox::getT('pages'), 'm', 'm.page_id = pf.parent_user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = m.page_id')
            ->where('pf.feed_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aListing['page_id'])) {
            return false;
        }

        return \Phpfox::getService('groups')->getUrl($aListing['page_id'], $aListing['title'],
                $aListing['vanity_url']) . 'comment-id_' . $aListing['item_id'] . '/';
    }

    public function getItemName($iId, $sName)
    {
        return '<a href="' . Phpfox_Url::instance()->makeUrl('comment.view', ['id' => $iId]) . '">' . _p('on_name_s_group_comment', ['name' => $sName]) . '</a>';
    }

    public function getCommentItem($iId)
    {
        $aRow = $this->database()->select('feed_comment_id AS comment_item_id, privacy_comment, user_id AS comment_user_id')
            ->from(Phpfox::getT('pages_feed_comment'))
            ->where('feed_comment_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = '0';

        if (!Phpfox::getService('comment')->canPostComment($aRow['comment_user_id'], $aRow['privacy_comment'])) {
            Phpfox_Error::set(_p('Unable to post a comment on this item due to privacy settings.'));

            unset($aRow['comment_item_id']);
        }

        $aRow['parent_module_id'] = 'groups';

        return $aRow;
    }

    public function addComment($aVals, $iUserId = null, $sUserName = null)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, fc.user_id, e.page_id, e.title, u.full_name, u.gender, pu.vanity_url, u.profile_page_id')
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->where('fc.feed_comment_id = ' . (int)$aVals['item_id'])
            ->execute('getSlaveRow');

        // Update the post counter if its not a comment put under moderation or if the person posting the comment is the owner of the item.
        if (empty($aVals['parent_id'])) {
            $this->database()->updateCounter('pages_feed_comment', 'total_comment', 'feed_comment_id',
                $aRow['feed_comment_id']);
        }

        if ($aRow['profile_page_id']) {
            $aGroup = Phpfox::getService('groups')->getPage($aRow['profile_page_id']);
            $aRow['user_id'] = $aGroup['user_id'];
        }

        // Send the user an email
        $sLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'],
                $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/';
        $sItemLink = \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        Phpfox::getService('comment.process')->notify([
                'user_id' => $aRow['user_id'],
                'item_id' => $aRow['feed_comment_id'],
                'owner_subject' => ['{{ full_name }} commented on a comment posted on the group "{{ title }}".',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]],
                'owner_message' => ['{{ full_name }} commented on one of your comments you posted on the group "<a href="{{ item_link }}">{{ title }}</a>". To see the comment thread, follow the link below: <a href="{{ link }}">{{ link }}</a>',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'item_link' => $sItemLink,
                        'title' => $aRow['title'],
                        'link' => $sLink
                    ]],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id' => 'groups_comment_feed',
                'mass_id' => 'groups',
                'mass_subject' => (Phpfox::getUserId() == $aRow['user_id'] ? ['{{ full_name }} commented on one of {{ gender }} group comments.',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)
                    ]] : ['{{ full_name }} commented on one of {{ other_full_name }}\'s group comments.',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'other_full_name' => $aRow['full_name']]]),
                'mass_message' => (Phpfox::getUserId() == $aRow['user_id'] ? ['{{ full_name }} commented on one of {{ gender }} own comments on the group "<a href="{{ item_link }}">{{ title }}</a>". To see the comment thread, follow the link: <a href="{{ link }}">{{ link }}</a>',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                        'item_link' => $sItemLink,
                        'title' => $aRow['title'],
                        'link' => $sLink
                    ]] : ['{{ full_name }} commented on one of {{ other_full_name }}\'s comments on the group "<a href="{{ item_link }}">{{ title }}</a>". To see the comment thread, follow the link: <a href="{{ link }}">{{ link }}</a>',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'other_full_name' => $aRow['full_name'],
                        'item_link' => $sItemLink,
                        'title' => $aRow['title'],
                        'link' => $sLink
                    ]])
            ]
        );
    }

    public function getNotificationComment($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.page_id, e.title, pu.vanity_url')
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->where('fc.feed_comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['feed_comment_id'])) {
            return false;
        }

        if ($aNotification['item_user_id'] == $aRow['user_id'] && isset($aNotification['extra_users']) && count($aNotification['extra_users'])) {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification, true);
        } else {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        }
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            Phpfox::getParam('notification.total_notification_title_length'), '...');

        $sPhrase = _p('user_shared_a_post_on_the_group_title', ['users' => $sUsers, 'title' => $sTitle]);

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'],
                    $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationComment_Feed($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.page_id, e.title, pu.vanity_url')
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->where('fc.feed_comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['feed_comment_id'])) {
            return false;
        }

        if ($aNotification['user_id'] == $aRow['user_id'] && isset($aNotification['extra_users']) && count($aNotification['extra_users'])) {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification, true);
        } else {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        }
        $sGender = Phpfox::getService('user')->gender($aRow['gender'], 1);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            Phpfox::getParam('notification.total_notification_title_length'), '...');

        if ($aNotification['user_id'] == $aRow['user_id']) {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users'])) {
                $sPhrase = _p('{{ users }} commented on <span class="drop_data_user">{{ full_name }}\'s</span> comment on the group "{{ title }}"',
                    ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
            } else {
                $sPhrase = _p('{{ users }} commented on {{ gender }} own comment on the group "{{ title }}"',
                    ['users' => $sUsers, 'gender' => $sGender, 'title' => $sTitle]);
            }
        } elseif ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('{{ users }} commented on one of your comments on the group "{{ title }}"',
                ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('{{ users }} commented on one of <span class="drop_data_user">{{ full_name }}\'s</span> comments on the group "{{ title }}"',
                ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'],
                    $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getTotalItemCount($iUserId)
    {
        return [
            'field' => 'total_groups',
            'total' => $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('pages'), 'p')
                ->where('p.view_id = 0 AND p.user_id = ' . (int)$iUserId . ' AND p.app_id = 0 AND p.item_type = 1')
                ->execute('getSlaveField')
        ];
    }

    public function globalUnionSearch($sSearch)
    {
        if (Phpfox::isAdmin()) {
            $this->database()->select('item.page_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'groups\' AS item_type_id, item.image_path AS item_photo, item.image_server_id AS item_photo_server')
                ->from(Phpfox::getT('pages'), 'item')
                ->where('item.view_id = 0 AND ' . $this->database()->searchKeywords('item.title',
                        $sSearch) . ' AND item.privacy = 0 AND item.item_type = 1')
                ->union();
        } else {
            $this->database()->select('item.page_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'groups\' AS item_type_id, item.image_path AS item_photo, item.image_server_id AS item_photo_server')
                ->from(Phpfox::getT('pages'), 'item')
                ->where('item.view_id = 0 AND ' . $this->database()->searchKeywords('item.title',
                        $sSearch) . ' AND item.privacy = 0 AND item.item_type = 1 AND item.reg_method <> 2')
                ->union();
            if (Phpfox::isUser()) {
                $this->database()->select('item.page_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'groups\' AS item_type_id, item.image_path AS item_photo, item.image_server_id AS item_photo_server')
                    ->from(Phpfox::getT('pages'), 'item')
                    ->join(Phpfox::getT('like'), 'l',
                        'l.type_id = \'groups\' AND l.item_id = item.page_id AND l.user_id = ' . Phpfox::getUserId())
                    ->where('item.view_id = 0 AND ' . $this->database()->searchKeywords('item.title',
                            $sSearch) . ' AND item.privacy = 0 AND item.item_type = 1 AND item.reg_method = 2')
                    ->union();
            }
        }
    }

    public function getSearchInfo($aRow)
    {
        $aPage = $this->database()->select('p.page_id, p.item_type, p.title, pu.vanity_url, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('pages'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.profile_page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->where('p.page_id = ' . (int)$aRow['item_id'])
            ->execute('getSlaveRow');

        $aInfo = [];
        $aInfo['item_link'] = \Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'],
            $aPage['vanity_url']);
        $aInfo['item_name'] = _p('Groups');
        $aInfo['profile_image'] = $aPage;

        return $aInfo;
    }

    public function getSearchTitleInfo()
    {
        return [
            'name' => _p('Groups')
        ];
    }

    public function getNotificationApproved($aNotification)
    {
        $aRow = $this->database()->select('v.page_id, v.title, v.user_id, u.gender, u.full_name, pu.vanity_url')
            ->from(Phpfox::getT('pages'), 'v')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = v.page_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.page_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('Your group "{{ title }}" has been approved.', [
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog'),
            'no_profile_image' => true
        ];
    }

    public function addLikeComment($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, fc.content, fc.user_id, e.page_id, e.title, pu.vanity_url, u.profile_page_id')
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->where('fc.feed_comment_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['feed_comment_id'])) {
            return;
        }

        if ($aRow['profile_page_id']) {
            $aGroup = Phpfox::getService('groups')->getPage($aRow['profile_page_id']);
            $aRow['user_id'] = $aGroup['user_id'];
        }

        $this->database()->updateCount('like', 'type_id = \'groups_comment\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'pages_feed_comment', 'feed_comment_id = ' . (int)$iItemId);

        if (!$bDoNotSendEmail) { // check has liked
            $sLink = Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/';
            $sItemLink = Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'groups_full_name_liked_a_comment_you_made_on_the_group_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ])
                ->message([
                    'groups_full_name_liked_a_comment_you_made_on_the_group_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'link' => $sLink,
                        'item_link' => $sItemLink,
                        'title' => $aRow['title']
                    ]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('groups_comment_like', $aRow['feed_comment_id'], $aRow['user_id']);
        }
    }

    //It is posting feeds for comments made in a Page of type group set to registration method "invite only", this should not happen.
    public function deleteLikeComment($iItemId)
    {
        $this->database()->updateCount('like', 'type_id = \'groups_comment\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'pages_feed_comment', 'feed_comment_id = ' . (int)$iItemId);
    }

    public function deleteComment($iId)
    {
        $this->database()->updateCounter('pages_feed_comment', 'total_comment', 'feed_comment_id', $iId, true);
    }

    public function updateCounterList()
    {
        $aList = [];

        $aList[] = [
            'name' => _p('Users Groups Count'),
            'id' => 'groups-total'
        ];

        return $aList;
    }

    public function updateCounter($iId, $iPage, $iPageLimit)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('user'))
            ->execute('getSlaveField');

        $subQuery = db()->select('g.page_id, g.user_id')
            ->from(':pages', 'g')
            ->join(':pages_type', 'pt', 'pt.type_id = g.type_id AND pt.item_type = 1')
            ->where([
                'g.view_id' => 0,
                'g.app_id' => 0,
                'g.item_type' => 1,
            ])->execute();

        $aRows = $this->database()->select('u.user_id, u.user_name, u.full_name, COUNT(b.page_id) AS total_items')
            ->from(Phpfox::getT('user'), 'u')
            ->leftJoin('(' . $subQuery . ')', 'b',
                'b.user_id = u.user_id')
            ->limit($iPage, $iPageLimit, $iCnt)
            ->group('u.user_id')
            ->execute('getSlaveRows');

        foreach ($aRows as $aRow) {
            $this->database()->update(Phpfox::getT('user_field'), ['total_groups' => $aRow['total_items']],
                'user_id = ' . $aRow['user_id']);
        }

        return $iCnt;
    }

    public function getNotificationComment_Like($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.page_id, e.title, pu.vanity_url')
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->where('fc.feed_comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            Phpfox::getParam('notification.total_notification_title_length'), '...');

        if ($aNotification['user_id'] == $aRow['user_id']) {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users'])) {
                $sPhrase = _p('{{ users }} liked <span class="drop_data_user">{{ row_full_name }}\'s</span> comment on the group "{{ title }}"',
                    [
                        'users' => Phpfox::getService('notification')->getUsers($aNotification, true),
                        'row_full_name' => $aRow['full_name'],
                        'title' => $sTitle
                    ]);
            } else {
                $sPhrase = _p('{{ users }} liked {{ gender }} own comment on the group "{{ title }}"', [
                    'users' => $sUsers,
                    'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                    'title' => $sTitle
                ]);
            }
        } elseif ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('{{ users }} liked one of your comments on the group "{{ title }}"',
                ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('{{ users }} liked one on <span class="drop_data_user">{{ row_full_name }}\'s</span> comments on the group "{{ title }}"',
                ['users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        return [
            'link' => Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /* Used to get a page when there is no certainty of the module */
    public function getItem($iId)
    {
        \Phpfox::getService('groups')->setIsInPage();
        $aItem = $this->database()->select('*')->from(Phpfox::getT('pages'))->where('item_type = 1 AND page_id = ' . (int)$iId)->execute('getSlaveRow');
        if (empty($aItem)) {
            return false;
        }
        $aItem['module'] = 'groups';
        $aItem['module_title'] = _p('Groups');
        $aItem['item_id'] = $iId;

        return $aItem;
    }

    /**
     * @param $iUser
     * @throws \Exception
     */
    public function onDeleteUser($iUser)
    {
        $aRows = $this->database()->select('*')
            ->from(Phpfox::getT('pages'))
            ->where('user_id = ' . (int)$iUser . ' AND item_type = 1')
            ->execute('getSlaveRows');

        foreach ($aRows as $aRow) {
            Phpfox::getService('groups.process')->delete($aRow['page_id'], true, true);
        }
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('groups.service_callback__call')) {
            eval($sPlugin);

            return;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }

    public function checkPermission($iId, $sName)
    {
        return \Phpfox::getService('groups')->hasPerm($iId, $sName);
    }

    public function addItemNotification($aParams)
    {
        \Phpfox_Queue::instance()->addJob('groups_member_notifications', $aParams);
    }

    public function getNotificationStatus_NewItem_Groups($aNotification)
    {
        $aItem = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.page_id, e.title, pu.vanity_url')
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->where('fc.feed_comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['feed_comment_id'])) {
            return false;
        }

        $sPhrase = _p('{{ users }} add a new comment in the group "{{ title }}"', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aItem['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aItem['page_id'], $aItem['title'],
                    $aItem['vanity_url']) . 'wall/comment-id_' . $aItem['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];

    }

    public function getNotificationConverted($aNotification)
    {
        return [
            'link' => Phpfox::getLib('url')->makeUrl('groups'),
            'message' => _p("All old groups (page type) converted new groups"),
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];

    }

    public function canShareOnMainFeed($iPageId, $sPerm, $bChildren)
    {
        return \Phpfox::getService('groups')->hasPerm($iPageId, $sPerm);
    }

    public function getExtraBrowseConditions($sPrefix = 'pages')
    {
        $sCondition = " AND ({$sPrefix}.user_id = " . Phpfox::getUserId() . " OR {$sPrefix}.reg_method <> 2";
        if (Phpfox::getUserParam('core.can_view_private_items')) {
            $sCondition .= " OR {$sPrefix}.reg_method = 2";
        } else {
            $aGroupIds = Phpfox::getService('groups')->getAllSecretGroupIdsOfMember();
            if (count($aGroupIds)) {
                $sCondition .= " OR {$sPrefix}.page_id IN (" . implode(',', $aGroupIds) . ")";
            }
        }
        $sCondition .= ') ';

        return $sCondition;
    }

    public function getReportRedirect($iId)
    {
        return Phpfox::getService('groups')->getUrl($iId);
    }

    /**
     * @description: callback to check permission to get feed of a group
     * @param $iId
     *
     * @return bool
     */
    public function canGetFeeds($iId)
    {
        $aGroup = \Phpfox::getService('groups')->getPage($iId);
        if (!$aGroup || empty($aGroup['page_id'])) {
            return false;
        }

        //return false if user isn't admin/member want to get  feed of a closed/secret group
        if (!\Phpfox::getService('groups')->isAdmin($aGroup['page_id']) && !\Phpfox::getService('groups')->isMember($aGroup['page_id']) && in_array($aGroup['reg_method'],
                [1, 2])) {
            return false;
        }

        return \Phpfox::getService('groups')->hasPerm($aGroup['page_id'], 'groups.view_browse_updates');
    }

    /**
     * @description: return callback param for adding feed comment on group
     * @param $iId
     * @param $aVals
     *
     * @return array|bool
     */
    public function getFeedComment($iId, $aVals)
    {
        //check permission
        Phpfox::isUser(true);

        if (!\Phpfox::getService('groups')->hasPerm($iId, 'groups.share_updates')) {
            return false;
        }

        if (\Phpfox::getLib('parse.format')->isEmpty($aVals['user_status'])) {
            Phpfox_Error::set(_p('add_some_text_to_share'));

            return false;
        }

        $aGroup = \Phpfox::getService('groups')->getPage($iId);

        //check group is exists
        if (!isset($aGroup['page_id'])) {
            Phpfox_Error::set(_p('Unable to find the page you are trying to comment on.'));

            return false;
        }

        $sLink = \Phpfox::getService('groups')->getUrl($aGroup['page_id'], $aGroup['title'], $aGroup['vanity_url']);
        $aCallback = [
            'module' => 'groups',
            'table_prefix' => 'pages_',
            'link' => $sLink,
            'email_user_id' => $aGroup['user_id'],
            'subject' => ['full_name_wrote_a_comment_on_your_group_tile_email_subject',
                ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aGroup['title']]],
            'message' => ['full_name_wrote_a_comment_on_your_group_tile_email_content_link',
                ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aGroup['title']]],
            'notification' => null,
            'feed_id' => 'groups_comment',
            'item_id' => $aGroup['page_id'],
            'add_to_main_feed' => true,
            'add_tag' => true
        ];

        return $aCallback;
    }

    /**
     * @description: callback after a comment feed added on event
     * @param $iId
     */
    public function onAddFeedCommentAfter($iId)
    {
        \Phpfox_Database::instance()->updateCounter('pages', 'total_comment', 'page_id', $iId);
    }

    /**
     * @description: check permission when add like for group
     * @param $iId
     *
     * @return bool
     */
    public function canLikeItem($iId)
    {
        $aItem = \Phpfox::getService('groups')->getForView($iId);
        if (empty($aItem) || empty($aItem['page_id'])) {
            return false;
        }

        $bIsAdmin = Phpfox::getService('groups')->isAdmin($iId) || Phpfox::isAdmin();
        if (!$bIsAdmin && ($aItem['reg_method'] == 2 || $aItem['reg_method'] == 1)) {
            return \Phpfox::getService('groups')->checkCurrentUserInvited($iId);
        }

        return true;
    }

    public function getNotificationInvite_Admin($aNoti)
    {
        $aRow = $this->database()->select('v.page_id, v.title, v.user_id, u.gender, u.full_name, pu.vanity_url')
            ->from(Phpfox::getT('pages'), 'v')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = v.page_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.page_id = ' . (int)$aNoti['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('you_have_been_invited_to_become_an_admin_of_group', ['page_name' => $aRow['title']]);

        return [
            'link' => Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Check if need to hide profile photos or cover photos
     * @param $iGroupId
     * @return array
     */
    public function getHiddenAlbums($iGroupId)
    {
        $aHiddenAlbums = [];
        if (!Phpfox::getParam('groups.display_groups_profile_photo_within_gallery', true)) {
            list($iCnt, $aProfileAlbums) = Phpfox::getService('photo.album')->get([
                'pa.module_id = \'groups\'',
                'AND pa.group_id = ' . $iGroupId,
                'AND pa.profile_id != 0'
            ]);
            $iCnt && ($aHiddenAlbums[] = $aProfileAlbums[0]['album_id']);
        }
        if (!Phpfox::getParam('groups.display_groups_cover_photo_within_gallery', true)) {
            list($iCnt, $aCoverAlbums) = Phpfox::getService('photo.album')->get([
                'pa.module_id = \'groups\'',
                'AND pa.group_id = ' . $iGroupId,
                'AND pa.cover_id != 0'
            ]);
            $iCnt && ($aHiddenAlbums[] = $aCoverAlbums[0]['album_id']);
        }

        return $aHiddenAlbums;
    }

    /**
     * This function will add number of pending groups to admin dashboard statistics
     * @return array
     */
    public function pendingApproval()
    {
        return [
            'phrase' => _p('Groups'),
            'value' => Phpfox::getService('groups')->getPendingTotal(),
            'link' => Phpfox_Url::instance()->makeUrl('groups', ['view' => 'pending'])
        ];
    }

    public function getAdmincpAlertItems()
    {
        $iTotalPending = Phpfox::getService('groups')->getPendingTotal();
        return [
            'message' => _p('you_have_total_pending_groups', ['total' => $iTotalPending]),
            'value' => $iTotalPending,
            'link' => Phpfox_Url::instance()->makeUrl('groups', ['view' => 'pending'])
        ];
    }

    /**
     * Check if user is admin of group
     * @param $iGroupId
     * @return bool
     * @throws \Exception
     */
    public function isAdmin($iGroupId)
    {
        $aErrors = Phpfox_Error::get();
        $bIsAdmin = Phpfox::getService('groups')->isAdmin($iGroupId);
        Phpfox_Error::reset();
        foreach ($aErrors as $sError) {
            Phpfox_Error::set($sError);
        }

        return $bIsAdmin;
    }

    /**
     * Show notification when someone post an image on group,
     * notifications will be sent to group's owner and admins
     *
     * @param $aNotification
     * @return array|bool
     */
    public function getNotificationPost_Image($aNotification)
    {
        // get pages from photo id
        $aPhoto = Phpfox::getService('photo')->getPhotoItem($aNotification['item_id']);
        if (!$aPhoto) {
            return false;
        }

        $aGroup = Phpfox::getService('groups')->getPage($aPhoto['group_id']);
        if (!$aGroup) {
            return false;
        }

        if (!empty($aGroup['user_id']) && $aGroup['user_id'] == Phpfox::getUserId()) {
            // notification of owner
            $sPhrase = _p('full_name_post_some_images_on_your_group_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aGroup['title']
            ]);
        } else {
            // notification of admin
            $sPhrase = _p('full_name_post_some_images_on_group_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aGroup['title']
            ]);
        }

        return [
            'link' => Phpfox::getService('photo.callback')->getLink(['item_id' => $aPhoto['photo_id']]),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Show notification when someone post a link on group,
     * notifications will be sent to group's owner and admins
     *
     * @param $aNotification
     * @return array|bool
     */
    public function getNotificationComment_Link($aNotification)
    {
        $aLink = Phpfox::getService('link')->getLinkById($aNotification['item_id']);
        if (!$aLink) {
            return false;
        }

        $aGroup = Phpfox::getService('groups')->getPage($aLink['item_id']);
        if (!$aGroup) {
            return false;
        }

        if (!empty($aGroup['user_id']) && $aGroup['user_id'] == Phpfox::getUserId()) {
            // notification of owner
            $sPhrase = _p('full_name_posted_a_link_on_your_group_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aGroup['title']
            ]);
        } else {
            // notification of admin
            $sPhrase = _p('full_name_posted_a_link_on_group_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aGroup['title']
            ]);
        }

        return [
            'link' => $aLink['redirect_link'],
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Get notification of posted video
     *
     * @param $aNotification
     * @return array|bool
     */
    public function getNotificationPosted_Video($aNotification)
    {
        if (!Phpfox::isAppActive('PHPfox_Videos')) {
            return false;
        }
        $aVideo = Phpfox::getService('v.video')->getForEdit($aNotification['item_id']);
        if (!$aVideo) {
            return false;
        }

        $aGroup = Phpfox::getService('groups')->getPage($aVideo['item_id']);
        if (!$aGroup) {
            return false;
        }

        if (!empty($aGroup['user_id']) && $aGroup['user_id'] == Phpfox::getUserId()) {
            // notification of owner
            $sPhrase = _p('full_name_posted_a_video_on_your_group_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aGroup['title']
            ]);
        } else {
            // notification of admin
            $sPhrase = _p('full_name_posted_a_video_on_group_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aGroup['title']
            ]);
        }

        return [
            'link' => Phpfox::permalink('video.play', $aVideo['video_id'], $aVideo['title']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        return [
            'phrase' => _p('groups'),
            'value' => $this->database()->select('COUNT(*)')
                ->from(':pages')
                ->where('view_id = 0 AND item_type = 1 AND time_stamp >= ' . $iToday)
                ->executeField()
        ];
    }

    /**
     * @return array
     */
    public function getUploadParams($aExtraParams = [])
    {
        return Phpfox::getService('groups')->getUploadPhotoParams($aExtraParams);
    }

    public function getActivityFeedPhoto($aItem, $aCallback = null, $bIsChildItem = false)
    {
        $sSelect = 'p.*, p.server_id AS photo_server_id';
        if (Phpfox::isModule('like')) {
            $sSelect .= ', count(l.like_id) as total_like';
            $this->database()->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'photo\' AND l.item_id = p.photo_id');

            $this->database()->group('p.photo_id');

            $sSelect .= ', l2.like_id AS is_liked';
            $this->database()->leftJoin(Phpfox::getT('like'), 'l2',
                'l2.type_id = \'photo\' AND l2.item_id = p.photo_id AND l2.user_id = ' . Phpfox::getUserId());
        }
        $aRow = $this->database()->select($sSelect . ' , p.destination, u.server_id, u.full_name, u.profile_page_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(':user', 'u', 'u.user_id=p.user_id')
            ->where([
                'p.photo_id' => (int)$aItem['item_id'],
                'p.is_profile_photo' => 1
            ])->execute('getSlaveRow');

        if (empty($aRow)) {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm(null, 'groups.view_browse_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm($aRow['group_id'], 'groups.view_browse_updates'))
        ) {
            return false;
        }

        $aGroup = Phpfox::getService('groups')->getPage($aRow['group_id']);
        if ($aGroup['reg_method'] == 2 && (!\Phpfox::getService('groups')->isMember($aGroup['page_id']) &&
                !\Phpfox::getService('groups')->isAdmin($aGroup['page_id']) && Phpfox::getService('user')->isAdminUser(Phpfox::getUserId()))
        ) {
            return false;
        }

        $sImage = Phpfox::getLib('image.helper')->display([
            'server_id' => $aRow['photo_server_id'],
            'path' => 'photo.url_photo',
            'file' => $aRow['destination'],
            'suffix' => '_500',
            'class' => 'photo_holder',
            'defer' => true
        ]);
        $aReturn = [
            'feed_title' => '',
            'feed_info' => _p('groups_updated_their_profile_photo'),
            'feed_link' => Phpfox_Url::instance()->permalink('photo', $aRow['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aRow['title'] : null),
            'feed_image' => $sImage,
            'feed_icon' => Phpfox::getLib('image.helper')->display([
                'theme' => 'misc/report_user.png',
                'return_url' => true
            ]),
            'time_stamp' => $aItem['time_stamp'],
            'feed_total_like' => $aRow['total_like'],
            'like_type_id' => 'photo',
            'enable_like' => true,
            'feed_is_liked' => isset($aRow['is_liked']) ? $aRow['is_liked'] : false,
            'total_comment' => $aRow['total_comment'],
            'comment_type_id' => 'photo',
            'parent_user_id' => Phpfox::getService('groups')->getPageOwnerId($aRow['profile_page_id']),
            'item_type' => 1
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        return $aReturn;
    }

    public function getActivityFeedCover_Photo($aItem, $aCallback = null, $bIsChildItem = false)
    {
        $sSelect = 'p.*';
        if (Phpfox::isModule('like')) {
            $sSelect .= ', count(l.like_id) as total_like';
            $this->database()->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'photo\' AND l.item_id = p.photo_id');

            $this->database()->group('p.photo_id');

            $sSelect .= ', l2.like_id AS is_liked';
            $this->database()->leftJoin(Phpfox::getT('like'), 'l2',
                'l2.type_id = \'photo\' AND l2.item_id = p.photo_id AND l2.user_id = ' . Phpfox::getUserId());
        }
        $aRow = $this->database()->select($sSelect . ' , p.destination, p.server_id, u.full_name, u.profile_page_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(':user', 'u', 'u.user_id=p.user_id')
            ->where([
                'p.photo_id' => (int)$aItem['item_id']
            ])->execute('getSlaveRow');

        if (empty($aRow)) {
            return false;
        }

        $aGroup = Phpfox::getService('groups')->getPage($aRow['group_id']);
        if ((defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm(null, 'groups.view_browse_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !\Phpfox::getService('groups')->hasPerm($aGroup['page_id'], 'groups.view_browse_updates'))
        ) {
            return false;
        }

        if (!empty($aGroup['reg_method']) && $aGroup['reg_method'] == 2 && (!\Phpfox::getService('groups')->isMember($aGroup['page_id']) &&
                !\Phpfox::getService('groups')->isAdmin($aGroup['page_id']) && Phpfox::getService('user')->isAdminUser(Phpfox::getUserId()))
        ) {
            return false;
        }

        $sImage = Phpfox::getLib('image.helper')->display([
            'server_id' => $aRow['server_id'],
            'path' => 'photo.url_photo',
            'file' => $aRow['destination'],
            'suffix' => '_1024',
            'class' => 'photo_holder',
            'defer' => true
        ]);
        $aReturn = [
            'feed_title' => '',
            'feed_info' => _p('groups_updated_their_cover_photo'),
            'feed_link' => Phpfox_Url::instance()->permalink('photo', $aRow['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aRow['title'] : null),
            'feed_image' => $sImage,
            'feed_icon' => Phpfox::getLib('image.helper')->display([
                'theme' => 'misc/report_user.png',
                'return_url' => true
            ]),
            'time_stamp' => $aItem['time_stamp'],
            'feed_total_like' => $aRow['total_like'],
            'like_type_id' => 'photo',
            'enable_like' => true,
            'feed_is_liked' => isset($aRow['is_liked']) ? $aRow['is_liked'] : false,
            'total_comment' => $aRow['total_comment'],
            'comment_type_id' => 'photo',
            'parent_user_id' => Phpfox::getService('groups')->getPageOwnerId($aRow['profile_page_id']),
            'item_type' => 1
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        return $aReturn;
    }

    /**
     * Get statistic for each user
     *
     * @param $iUserId
     * @return array|bool
     */
    public function getUserStatsForAdmin($iUserId)
    {
        if (!$iUserId) {
            return false;
        }

        $iTotalPages = db()->select('COUNT(*)')->from(':pages')->where(['user_id' => $iUserId, 'item_type' => 1])->executeField();

        return [
            'total_name' => _p('groups'),
            'total_value' => $iTotalPages,
            'type' => 'item'
        ];
    }

    public function showCoverInDetailItem($iGroupId)
    {
        $aGroup = Phpfox::getService('groups')->getForView($iGroupId);
        Phpfox_Component::setPublicParam('show_group_cover', true);
        Phpfox_Component::setPublicParam('group_to_show_cover', $aGroup);
    }

    /**
     * @param $aParams
     * @return bool
     */
    public function enableSponsor($aParams)
    {
        return Phpfox::getService('groups.process')->sponsor($aParams['item_id'], 1);
    }

    public function getToSponsorInfo($iId)
    {
        $aPage = db()->select('p.user_id, p.title, p.page_id as item_id, p.image_server_id as server_id, p.image_path as image, u.user_name')
            ->from(':pages', 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.page_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aPage)) {
            return [
                'error' => _p('sorry_the_group_you_are_looking_for_no_longer_exists',
                    ['link' => Phpfox::getLib('url')->makeUrl('groups')])
            ];
        }

        $aPage['link'] = Phpfox::permalink('groups', $aPage['item_id']);
        $aPage['paypal_msg'] = _p('sponsor_paypal_message_group', ['sGroupTitle' => $aPage['title']]);
        $aPage['image_dir'] = 'pages.url_image';
        $aPage['title'] = _p('sponsor_title_group', ['sGroupTitle' => $aPage['title']]);

        $aPage = array_merge($aPage, [
            'redirect_completed' => 'groups',
            'message_completed' => _p('purchase_group_sponsor_completed'),
            'redirect_pending_approval' => 'groups',
            'message_pending_approval' => _p('purchase_group_sponsor_pending_approval')
        ]);
        return $aPage;
    }

    /**
     * This callback will be called when admin delete a sponsor in admincp
     * @param $aParams
     */
    public function deleteSponsorItem($aParams)
    {
        db()->update(':pages', ['is_sponsor' => 0], ['page_id' => $aParams['item_id']]);
        $this->cache()->remove('groups_sponsored');
    }

    /**
     * @param $aParams
     * @return bool|string
     */
    public function getLink($aParams)
    {
        $aPage = db()->select('p.title, p.page_id, pu.vanity_url')
            ->from(Phpfox::getT('pages'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(':pages_url', 'pu', 'pu.page_id = p.page_id')
            ->where('p.page_id = ' . (int)$aParams['item_id'])
            ->execute('getSlaveRow');
        if (empty($aPage)) {
            return false;
        }
        return Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
    }

    public function getNotificationReassign_Owner($aNotification)
    {
        $aRow = \Phpfox::getService('groups')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('full_name_just_assigned_you_as_owner_of_group_title', [
            'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => \Phpfox::getLib('parse.output')->shorten($aRow['title'],
                \Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationOwner_Changed($aNotification)
    {
        $aRow = \Phpfox::getService('groups')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('full_name_just_transfer_your_group_title_to_other_user', [
            'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => \Phpfox::getLib('parse.output')->shorten($aRow['title'],
                \Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => \Phpfox::getService('groups')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getReportRedirectComment($iId)
    {
        return $this->getFeedRedirectComment($iId);
    }

    public function getFeedRedirectComment($iId, $iChild = 0)
    {
        $aFeedComment = $this->database()->select('m.page_id, m.title, pu.vanity_url, pf.feed_comment_id')
            ->from(Phpfox::getT('pages_feed_comment'), 'pf')
            ->join(Phpfox::getT('pages'), 'm', 'm.page_id = pf.parent_user_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = m.page_id')
            ->where('pf.feed_comment_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aFeedComment['page_id'])) {
            return false;
        }

        return Phpfox::getService('groups')->getUrl($aFeedComment['page_id'], $aFeedComment['title'],
                $aFeedComment['vanity_url']) . 'comment-id_' . $aFeedComment['feed_comment_id'] . '/';
    }

    public function getNotificationSettings()
    {
        return [
            'groups.email_notification' => [
                'phrase' => _p('groups_notifications'),
                'default' => 1
            ]
        ];
    }

    /**
     * This callback will be call if a photo of module groups deleted
     * @param $aPhoto
     */
    public function onDeletePhoto($aPhoto)
    {
        $groupInfo = db()->select('p.cover_photo_id, u.user_id, p.image_path, p.image_server_id, u.user_image, u.server_id AS user_server_id')
            ->from(':pages', 'p')
            ->join(':user', 'u', 'u.profile_page_id = p.page_id')
            ->where([
                'p.page_id' => $aPhoto['group_id']
            ])->executeRow(false);

        if ($groupInfo['cover_photo_id'] == $aPhoto['photo_id']) {
            db()->update(':pages', ['cover_photo_id' => null], ['page_id' => $aPhoto['group_id']]);
        }

        $profileImageObject = storage()->get('user/avatar/' . $groupInfo['user_id']);
        if (!empty($profileImageObject) && $profileImageObject->value == $aPhoto['photo_id']) {
            storage()->del('user/avatar/' . $groupInfo['user_id']);
            if (db()->update(':pages', [
                'image_path' => '',
                'image_server_id' => 0,
            ], ['page_id' => $aPhoto['group_id']])) {
                if (db()->update(':user', [
                   'user_image' => '',
                   'server_id' => 0,
                ], ['user_id' => $groupInfo['user_id']])) {
                    $this->_deleteUnusedImages(Phpfox::getParam('core.dir_user'), $groupInfo['user_image'], $groupInfo['user_server_id'], Phpfox::getService('user')->getUserThumbnailSizes());
                }
                $this->_deleteUnusedImages(Phpfox::getParam('pages.dir_image'), $groupInfo['image_path'], $groupInfo['image_server_id'], Phpfox::getService('groups')->getPhotoPicSizes());
            }
        }
    }

    private function _deleteUnusedImages($localFolder, $imagePath, $serverId, $thumbnailSizes = null)
    {
        $deletedFilePath = $localFolder . sprintf($imagePath, '');
        @unlink($deletedFilePath);
        if ($serverId > 0) {
            Phpfox::getLib('storage')->get($serverId)->remove(str_replace("\\", '/', str_replace(PHPFOX_DIR, '', $deletedFilePath)));
        }

        if (!empty($thumbnailSizes)) {
            foreach ($thumbnailSizes as $iSize) {
                @unlink($localFolder . sprintf($imagePath, '_' . $iSize));
                @unlink($localFolder . sprintf($imagePath, '_' . $iSize . '_square'));
                if ($serverId > 0) {
                    Phpfox::getLib('storage')->get($serverId)->remove(str_replace("\\", '/', str_replace(PHPFOX_DIR, '', $localFolder . sprintf($imagePath, '_' . $iSize))));
                    Phpfox::getLib('storage')->get($serverId)->remove(str_replace("\\", '/', str_replace(PHPFOX_DIR, '', $localFolder . sprintf($imagePath, '_' . $iSize . '_square'))));
                }
            }
        }
    }
}
