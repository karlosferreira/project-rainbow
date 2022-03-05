<?php

namespace Apps\Core_Pages\Service;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Component;
use Phpfox_Database;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Template;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Callback extends \Phpfox_Service
{
    public function __construct()
    {
        Phpfox::getService('pages')->setIsInPage();
    }

    /**
     * @return Facade|object
     */
    public function getFacade()
    {
        return Phpfox::getService('pages.facade');
    }

    public function updateCommentFeedType($params)
    {
        if (Phpfox::isModule('notification')) {
            db()->update(':notification', [
                'type_id' => $params['new_type'] == 'pages_comment' ? $params['new_type'] . '_feed' : 'comment_' . str_replace('_comment', '', $params['new_type']),
                'item_id' => $params['new_item_id']
            ], [
                'type_id' => $params['old_type'] == 'pages_comment' ? $params['old_type'] . '_feed' : 'comment_' . str_replace('_comment', '', $params['old_type']),
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
            if ($newCommentType == 'pages') {
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
        $pageId = db()->select('group_id')
            ->from(':photo')
            ->where([
                'photo_id' => $photoId,
            ])->executeField(false);
        if (empty($pageId)) {
            return false;
        }

        $aPage = Phpfox::getService('pages')->getPage($pageId);
        $iPageUserId = Phpfox::getService('pages')->getUserId($aPage['page_id']);
        if (!empty($aPage['image_path'])) {
            Phpfox::getService('pages.process')->deleteImage($aPage);
        }

        $pendingPhotoCacheKey = 'pages_profile_photo_pending_' . $aPage['page_id'];
        $pendingPhotoCache = storage()->get($pendingPhotoCacheKey);
        if (!empty($pendingPhotoCache) && !empty($pendingPhotoCache->value->image_path)) {
            db()->update(':pages', [
                'image_path' => $pendingPhotoCache->value->image_path,
                'image_server_id' => $pendingPhotoCache->value->image_server_id,
            ], ['page_id' => $aPage['page_id']]);
            storage()->del($pendingPhotoCacheKey);
        }

        // add feed after updating group's profile image
        if (Phpfox::isModule('feed') && Phpfox::getParam('photo.photo_allow_posting_user_photo_feed', 1) && ($oProfileImage = storage()->get('user/avatar/' . $iPageUserId))) {
            Phpfox::getService('feed.process')->callback([
                'table_prefix' => 'pages_',
                'module' => 'pages',
                'add_to_main_feed' => true,
                'has_content' => true
            ])->add('pages_photo', $oProfileImage->value, 0, 0, $aPage['page_id'], $iPageUserId);
        }

        Phpfox::getService('pages')->clearCachesForLoginAsPagesListing($pageId);

        if (!empty($pendingPhotoCache->value->temp_file)) {
            Phpfox::getService('core.temp-file')->delete($pendingPhotoCache->value->temp_file);
        }
    }

    public function approveCoverPhoto($photoId)
    {
        if (empty($photoId)) {
            return false;
        }

        $pageId = db()->select('group_id')
            ->from(':photo')
            ->where(['photo_id' => $photoId])
            ->executeField(false);
        if (empty($pageId)) {
            return false;
        }

        $pendingCacheKey = 'pages_cover_photo_pending_' . $pageId;
        $pendingCache = storage()->get($pendingCacheKey);
        if (empty($pendingCache) || $pendingCache->value->photo_id != $photoId) {
            return false;
        }
        db()->update(Phpfox::getT('pages'),
            ['cover_photo_position' => '', 'cover_photo_id' => (int)$photoId], 'page_id = ' . (int)$pageId);
        if (Phpfox::isModule('feed') && Phpfox::getParam('photo.photo_allow_posting_user_photo_feed', 1)) {
            // create feed after changing cover
            Phpfox::getService('feed.process')->callback([
                'table_prefix' => 'pages_',
                'module' => 'pages',
                'add_to_main_feed' => true,
                'has_content' => true
            ])->add('pages_cover_photo', $photoId, 0, 0, $pageId, Phpfox::getService('pages')->getUserId($pageId));
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
            Phpfox::getService('pages.process')->updateCoverPosition($pageId, $repositionCacheObject->value);
            storage()->del('photo_cover_reposition_' . $photoId);
        }
    }
    
    public function getProfileLink()
    {
        return 'profile.pages';
    }

    public function getProfileMenu($aUser)
    {
        if (!Phpfox::getUserParam('pages.can_view_browse_pages')) {
            return false;
        }

        if (Phpfox::getParam('profile.show_empty_tabs') == false) {
            if (!isset($aUser['total_pages'])) {
                return false;
            }

            if (isset($aUser['total_pages']) && (int)$aUser['total_pages'] === 0) {
                return false;
            }
        }

        $aMenus[] = [
            'phrase' => _p('pages'),
            'url' => 'profile.pages',
            'total' => (int)(isset($aUser['total_pages']) ? $aUser['total_pages'] : 0),
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
        $this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
            'u2.user_id = p.user_id');

        $aRow = $this->database()->select('p.time_stamp, p.privacy, p.page_id, p.type_id, p.category_id, p.cover_photo_id, p.total_like, p.title, pu.vanity_url, p.image_path, p.image_server_id, p_type.name AS parent_category_name, pg.name AS category_name')
            ->from(Phpfox::getT('pages'), 'p')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_category'), 'pg', 'pg.category_id = p.category_id')
            ->leftJoin(Phpfox::getT('pages_type'), 'p_type', 'p_type.type_id = pg.type_id')
            ->where('p.page_id = ' . $itemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $type = $this->getFacade()->getType()->getById($aRow['type_id']);
        if (empty($aRow['category_name'])) {
            $aRow['category_name'] = $type['name'];
            $aRow['category_link'] = Phpfox::permalink('pages.category', $aRow['type_id'], $type['name']);
        } else {
            $aRow['type_link'] = Phpfox::permalink('pages.category', $aRow['type_id'], $type['name']);
            $aRow['category_link'] = Phpfox::permalink('pages.sub-category', $aRow['category_id'],
                $aRow['category_name']);
        }

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
        $aRow['page_url'] = $sLink;

        $iTotalLikes = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'pages_created'")
            ->execute('getSlaveField');
        $iIsLikedFeed = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'pages_created'" . ' AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');

        $aRow['is_liked_page'] = Phpfox::getService('pages')->isMember($itemId);

        \Phpfox_Component::setPublicParam('custom_param_feed_page_' . $feedId, $aRow);

        if ($bIsChildItem) {
            $aItem = array_merge($aRow, ['feed_id' => $feedId]);
        }

        $aRow['full_name'] = Phpfox::getLib('parse.output')->clean($aRow['full_name']);
        $sFullName = Phpfox::getLib('parse.output')->shorten($aRow['full_name'], 0);

        $aReturn = [
            'feed_title' => $aRow['title'],
            'no_user_show' => true,
            'feed_link' => $sLink,
            'feed_info' => _p('pages_user_created_page', [
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
            'like_type_id' => 'pages_created',
            'like_item_id' => $feedId,
            'feed_total_like' => $iTotalLikes,
            'feed_is_liked' => (int)$iIsLikedFeed > 0,
            'load_block' => 'pages.page-feed'
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        return $aReturn;
    }

    /**
     * A tag B in a comment in a page, B will receive this notification
     * @param $aNotification
     * @return array|boolean
     */
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

        $sPhrase = _p('full_name_tagged_you_in_a_comment_in_page_title', [
            'full_name' => $aRow['full_name'],
            'title' => $aRow['title']
        ]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'],
                    $aRow['vanity_url']) . 'comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * A post to a page and tag B, B will receive this notification
     * @param $aNotification
     * @return array|boolean
     */
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

        $sPhrase = _p('full_name_tagged_you_in_a_post_in_page_title', [
            'full_name' => $aRow['full_name'],
            'title' => $aRow['title']
        ]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'],
                    $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getSiteStatsForAdmin($iStartTime, $iEndTime)
    {
        $aCond = [];
        $aCond[] = 'app_id = 0 AND view_id = 0 AND item_type = 0';
        if ($iStartTime > 0) {
            $aCond[] = 'AND time_stamp >= \'' . $this->database()->escape($iStartTime) . '\'';
        }
        if ($iEndTime > 0) {
            $aCond[] = 'AND time_stamp <= \'' . $this->database()->escape($iEndTime) . '\'';
        }

        $iCnt = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('pages'))
            ->where($aCond)
            ->execute('getSlaveField');

        return [
            'phrase' => 'pages.pages',
            'total' => $iCnt
        ];
    }

    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        return [
            'phrase' => _p('pages'),
            'value' => $this->database()->select('COUNT(*)')
                ->from(':pages')
                ->where('view_id = 0 AND item_type = 0 AND time_stamp >= ' . $iToday)
                ->executeField()
        ];
    }

    public function addPhoto($iId)
    {
        Phpfox::getService('pages')->setIsInPage();

        return [
            'module' => 'pages',
            'item_id' => $iId,
            'table_prefix' => 'pages_'
        ];
    }

    public function getDashboardActivity()
    {
        if (!Phpfox::getUserParam('pages.can_view_browse_pages')) {
            return [];
        }
        $aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);
        return [
            _p('pages') => $aUser['activity_pages']
        ];
    }

    public function getCommentNotification($aNotification)
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
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            Phpfox::getParam('notification.total_notification_title_length'), '...');

        if ($aNotification['user_id'] == $aRow['user_id']) {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users'])) {
                $sPhrase = _p('users_commented_on_full_name_comment',
                    ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
            } else {
                $sPhrase = _p('users_commented_on_gender_own_comment', [
                    'users' => $sUsers,
                    'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                    'title' => $sTitle
                ]);
            }
        } elseif ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_commented_on_one_of_your_comments', ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('users_commented_on_one_of_full_name_comments',
                ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'link' => $sLink . 'wall/comment-id_' . $aRow['feed_comment_id'],
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getPhotoDetails($aPhoto)
    {
        Phpfox::getService('pages')->setIsInPage();

        $aRow = Phpfox::getService('pages')->getPage($aPhoto['group_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        Phpfox::getService('pages')->setMode();

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('pages'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('pages'),
            'module_id' => 'pages',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'photo/',
            'theater_mode' => _p('in_the_page_link_title', ['link' => $sLink, 'title' => $aRow['title']]),
            'feed_table_prefix' => 'pages_'
        ];
    }

    public function getPhotoCount($iPageId)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('photo'))
            ->where("module_id = 'pages' AND group_id = " . $iPageId)
            ->execute('getSlaveField');

        return ($iCnt > 0) ? $iCnt : 0;
    }

    public function getAlbumCount($iPageId)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('photo_album'))
            ->where("module_id = 'pages' AND group_id = " . $iPageId)
            ->execute('getSlaveField');

        return ($iCnt > 0) ? $iCnt : 0;
    }

    public function uploadVideo($aVals)
    {
        Phpfox::getService('pages')->setIsInPage();

        return [
            'module' => 'pages',
            'item_id' => (is_array($aVals) && isset($aVals['callback_item_id']) ? $aVals['callback_item_id'] : (int)$aVals)
        ];
    }

    public function addLink($aVals)
    {
        return [
            'module' => 'pages',
            'item_id' => $aVals['callback_item_id'],
            'table_prefix' => 'pages_'
        ];
    }

    public function getFeedDisplay($pageId)
    {
        return [
            'module' => 'pages',
            'table_prefix' => 'pages_',
            'ajax_request' => 'pages.addFeedComment',
            'item_id' => $pageId,
            'disable_share' => Phpfox::getService('pages')->hasPerm($pageId, 'pages.share_updates')
        ];
    }

    public function getActivityFeedCustomChecksComment($aRow)
    {
        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null,
                    'pages.view_browse_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm($aRow['custom_data_cache']['page_id'],
                    'pages.view_browse_updates'))
            || (defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'pages.share_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm($aRow['custom_data_cache']['page_id'],
                    'pages.share_updates'))
        ) {
            return false;
        }

        if ($aRow['custom_data_cache']['reg_method'] == 2 &&
            (
                !Phpfox::getService('pages')->isMember($aRow['custom_data_cache']['page_id']) &&
                !Phpfox::getService('pages')->isAdmin($aRow['custom_data_cache']['page_id']) &&
                Phpfox::getService('user')->isAdminUser(Phpfox::getUserId())
            )
        ) {
            return false;
        }

        return $aRow;
    }

    public function getActivityFeedComment($aItem)
    {
        $aRow = $this->database()->select('fc.*, l.like_id AS is_liked, e.reg_method, e.page_id, e.title, e.app_id AS is_app, pu.vanity_url, ' . Phpfox::getUserField('u',
                'parent_'))
            ->from(Phpfox::getT('pages_feed_comment'), 'fc')
            ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
            ->join(Phpfox::getT('user'), 'u', 'u.profile_page_id = e.page_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
            ->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'pages_comment\' AND l.item_id = fc.feed_comment_id AND l.user_id = ' . Phpfox::getUserId())
            ->where('fc.feed_comment_id = ' . (int)$aItem['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null,
                    'pages.view_browse_updates'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm($aRow['page_id'],
                    'pages.view_browse_updates'))
        ) {
            return false;
        }

        if ($aRow['reg_method'] == 2 &&
            (
                !Phpfox::getService('pages')->isMember($aRow['page_id']) &&
                !Phpfox::getService('pages')->isAdmin($aRow['page_id']) &&
                Phpfox::getService('user')->isAdminUser(Phpfox::getUserId())
            )
        ) {
            return false;
        }

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'],
                $aRow['vanity_url']) . 'wall/comment-id_' . $aItem['item_id'] . '/';
        $aUser = Phpfox::getService('user')->getUser($aRow['user_id']);

        $aReturn = array_merge($aUser, [
            'feed_status' => htmlspecialchars($aRow['content']),
            'feed_link' => $sLink,
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'feed_icon' => Phpfox::getLib('image.helper')->display([
                'theme' => 'misc/comment.png',
                'return_url' => true
            ]),
            'feed_title' => '',
            'time_stamp' => $aRow['time_stamp'],
            'enable_like' => true,
            'comment_type_id' => 'pages',
            'like_type_id' => 'pages_comment',
            'is_custom_app' => $aRow['is_app'],
            'custom_data_cache' => $aRow
        ]);
        $aReturn['parent_user_name'] = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'],
            $aRow['vanity_url']);

        if ($aRow['user_id'] != $aRow['parent_user_id']) {
            if (!defined('PHPFOX_IS_PAGES_VIEW') && !defined('PHPFOX_PAGES_ADD_COMMENT')) {
                $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
            }
        }

        return $aReturn;
    }

    public function getActivityFeedItemLiked($aItem, $aCallback = null, $bIsChildItem = false)
    {
        $itemId = (int)$aItem['item_id'];
        $feedId = (int)$aItem['feed_id'];
        $this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
            'u2.user_id = p.user_id');
        $aRow = $this->database()->select('p.page_id, p.type_id, p.category_id, p.cover_photo_id, p.title, pu.vanity_url, p.total_like, p.image_path, p.image_server_id, p_type.name AS parent_category_name, pg.name AS category_name')
            ->from(Phpfox::getT('pages'), 'p')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_category'), 'pg', 'pg.category_id = p.category_id')
            ->leftJoin(Phpfox::getT('pages_type'), 'p_type', 'p_type.type_id = pg.type_id')
            ->where('p.page_id = ' . $itemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $type = $this->getFacade()->getType()->getById($aRow['type_id']);
        if (empty($aRow['category_name'])) {
            $aRow['category_name'] = $type['name'];
            $aRow['category_link'] = Phpfox::permalink('pages.category', $aRow['type_id'], $type['name']);
        } else {
            $aRow['type_link'] = Phpfox::permalink('pages.category', $aRow['type_id'], $type['name']);
            $aRow['category_link'] = Phpfox::permalink('pages.sub-category', $aRow['category_id'],
                $aRow['category_name']);
        }

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
        $aRow['page_url'] = $sLink;

        $iTotalLikes = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'pages_liked'")
            ->execute('getSlaveField');
        $iIsLikedFeed = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('like'))
            ->where('item_id = ' . $feedId . " AND type_id = 'pages_liked'" . ' AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');

        $aRow['is_liked_page'] = Phpfox::getService('pages')->isMember($itemId);

        \Phpfox_Component::setPublicParam('custom_param_feed_page_' . $feedId, $aRow);

        $aReturn = [
            'feed_title' => '',
            'feed_info' => _p('liked_the_page_link_title_title', [
                'link' => $sLink,
                'link_title' => Phpfox::getLib('parse.output')->clean($aRow['title']),
                'title' => Phpfox::getLib('parse.output')->clean(Phpfox::getLib('parse.output')->shorten($aRow['title'],
                    50, '...'))
            ]),
            'feed_link' => $sLink,
            'no_target_blank' => true,
            'feed_icon' => Phpfox::getLib('image.helper')->display([
                'theme' => 'misc/comment.png',
                'return_url' => true
            ]),
            'time_stamp' => $aItem['time_stamp'],
            'like_type_id' => 'pages_liked',
            'like_item_id' => $feedId,
            'feed_total_like' => $iTotalLikes,
            'feed_is_liked' => (int)$iIsLikedFeed > 0,
            'load_block' => 'pages.page-feed'
        ];

        if ($bIsChildItem) {
            $aRow['full_name'] = Phpfox::getLib('parse.output')->clean($aRow['full_name']);
            $sFullName = Phpfox::getLib('parse.output')->shorten($aRow['full_name'], 0);
            $aReturn['no_user_show'] = true;
            $aReturn['feed_info'] = _p('pages_user_created_page', [
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
        Phpfox::getService('pages')->setIsInPage();

        $aRow = Phpfox::getService('pages')->getPage($iItem);

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

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('pages'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('pages'),
            'module_id' => 'pages',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_pages' => $sLink . 'event/'
        ];
    }

    public function getFeedDetails($iItemId)
    {
        return [
            'module' => 'pages',
            'table_prefix' => 'pages_',
            'item_id' => $iItemId
        ];
    }

    public function deleteFeedItem($callbackData)
    {
        if (empty($callbackData['type_id']) || empty($callbackData['item_id'])) {
            return false;
        }

        // delete feed from main feed
        db()->delete(':feed', ['type_id' => $callbackData['type_id'], 'item_id' => $callbackData['item_id']]);
        if ($callbackData['type_id'] == 'pages_comment') {
            $aFeedComment = $this->database()->select('*')
                ->from(Phpfox::getT('pages_feed_comment'))
                ->where('feed_comment_id = ' . (int)$callbackData['item_id'])
                ->execute('getSlaveRow');

            if (empty($aFeedComment) || empty($aFeedComment['parent_user_id'])) {
                return true;
            }

            $iTotalComments = $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('pages_feed'))
                ->where('type_id = \'pages_comment\' AND parent_user_id = ' . $aFeedComment['parent_user_id'])
                ->execute('getSlaveField');

            $this->database()->update(Phpfox::getT('pages'), ['total_comment' => $iTotalComments],
                'page_id = ' . (int)$aFeedComment['parent_user_id']);
        }
        return true;
    }

    public function getNotificationInvite($aNotification)
    {
        $aRow = Phpfox::getService('pages')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('users_invited_you_to_check_out_the_page_title', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function deleteLike($iItemId, $iUserId = 0)
    {
        // Get the threads from this page
        if (db()->tableExists(\Phpfox::getT('forum_thread'))) {
            $aRows = $this->database()->select('thread_id')
                ->from(Phpfox::getT('forum_thread'))
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

        $aRow = Phpfox::getService('pages')->getPage($iItemId);
        if (!isset($aRow['page_id'])) {
            return false;
        }

        $totalLike = db()->select('total_like')
            ->from(Phpfox::getT('pages'))
            ->where('page_id = ' . (int)$iItemId)
            ->execute('getSlaveField');

        $this->database()->updateCount('like', 'type_id = \'pages\' AND item_id = ' . (int)$iItemId . '',
            'total_like',
            'pages', 'page_id = ' . (int)$iItemId);

        if (defined('PHPFOX_CANCEL_ACCOUNT') && PHPFOX_CANCEL_ACCOUNT) {
            db()->update(Phpfox::getT('pages'), ['total_like' => ['= total_like - ', 1]], 'page_id = ' . (int)$iItemId . ' AND total_like = ' . (int)$totalLike);
        }

        $iFriendId = (int)$this->database()->select('user_id')
            ->from(Phpfox::getT('user'))
            ->where('profile_page_id = ' . (int)$aRow['page_id'])
            ->execute('getSlaveField');

        $this->database()->delete(Phpfox::getT('friend'),
            'user_id = ' . (int)$iFriendId . ' AND friend_user_id = ' . ($iUserId > 0 ? $iUserId : Phpfox::getUserId()));
        $this->database()->delete(Phpfox::getT('friend'),
            'friend_user_id = ' . (int)$iFriendId . ' AND user_id = ' . ($iUserId > 0 ? $iUserId : Phpfox::getUserId()));

        // clear cache members
        $this->cache()->remove('pages_' . $iItemId . '_members');
        $this->cache()->remove('member_' . $iUserId . '_pages');

        if (Phpfox::getService('pages')->isAdmin($iItemId, $iUserId)) {
            db()->delete(Phpfox::getT('pages_admin'), 'page_id = ' . (int)$iItemId . ' AND user_id = ' . (int)$iUserId);
            $this->cache()->remove('pages_' . (int)$iItemId . '_admins');
        }

        if (!$iUserId) {
            $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);
            if (!defined('PHPFOX_CANCEL_ACCOUNT') || PHPFOX_CANCEL_ACCOUNT != true) {
                Phpfox_Ajax::instance()->call('window.location.href = \'' . $sLink . '\';');
            }
        }

        /* Remove invites */
        if ($iUserId != Phpfox::getUserId()) // Its not the user willingly leaving the page
        {
            $this->database()->delete(Phpfox::getT('pages_invite'),
                'page_id = ' . (int)$iItemId . ' AND invited_user_id =' . (int)$iUserId);
        }

        return true;
    }

    public function addLike($iItemId, $bDoNotSendEmail = false, $iUserId = null)
    {
        $aRow = Phpfox::getService('pages')->getPage($iItemId);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $this->database()->updateCount('like', 'type_id = \'pages\' AND item_id = ' . (int)$iItemId . '', 'total_like',
            'pages', 'page_id = ' . (int)$iItemId);
        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        if (!$bDoNotSendEmail && $iUserId != $aRow['user_id']) { // check has liked
            Phpfox::getLib('mail')->to($iUserId)
                ->subject(['pages.membership_accepted_to_title', ['title' => $aRow['title']]])
                ->message([
                    'pages.your_membership_to_the_page_link',
                    ['link' => $sLink, 'title' => $aRow['title']]
                ])
                ->notification('pages.email_notification')
                ->sendToSelf(true)
                ->send();

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'pages.full_name_liked_your_page_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ])
                ->message([
                    'pages.full_name_liked_your_page',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'link' => $sLink,
                        'title' => $aRow['title']
                    ]
                ])
                ->notification('pages.email_notification')
                ->send();
            Phpfox::getService('notification.process')->add('pages_like', $aRow['page_id'], $aRow['user_id']);

            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add('pages_itemLiked', $aRow['page_id']) : null);
        }

        $iFriendId = (int)$this->database()->select('user_id')
            ->from(Phpfox::getT('user'))
            ->where('profile_page_id = ' . (int)$aRow['page_id'])
            ->execute('getSlaveField');

        $bIsApprove = true;
        if ($iUserId === null) {
            $iUserId = Phpfox::getUserId();
            $bIsApprove = false;
        }

        $this->database()->insert(Phpfox::getT('friend'), [
                'is_page' => 1,
                'list_id' => 0,
                'user_id' => $iUserId,
                'friend_user_id' => $iFriendId,
                'time_stamp' => PHPFOX_TIME
            ]
        );

        $this->database()->insert(Phpfox::getT('friend'), [
                'is_page' => 1,
                'list_id' => 0,
                'user_id' => $iFriendId,
                'friend_user_id' => $iUserId,
                'time_stamp' => PHPFOX_TIME
            ]
        );

        // clear cache members
        $this->cache()->remove('pages_' . $iItemId . '_members');
        $this->cache()->remove('member_' . $iUserId . '_pages');

        if (!$bIsApprove) {
            Phpfox_Ajax::instance()->call('window.location.href = \'' . $sLink . '\';');
        }

        return null;
    }

    public function getVideoDetails($aItem)
    {
        Phpfox::getService('pages')->setIsInPage();

        $aRow = Phpfox::getService('pages')->getPage($aItem['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        Phpfox::getService('pages')->setMode();

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('pages'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('pages'),
            'module_id' => 'pages',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'video/',
            'theater_mode' => _p('in_the_page_link_title', ['link' => $sLink, 'title' => $aRow['title']])
        ];
    }

    public function onVideoPublished($aVideo)
    {
        if ($aVideo && isset($aVideo['item_id'])) {
            $aPage = Phpfox::getService('pages')->getPage($aVideo['item_id']);
            if(!$aPage) {
                return true;
            }
            $bForce = false;
            if(isset($aVideo['view_id'])) { // Approve called
                $bForce = true;
            }
            $sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
            $postedUser = Phpfox::getService('user')->getUser($aVideo['user_id'], 'u.full_name');
            $postedUserFullName = $postedUser['full_name'];

            // get all admins (include owner) and send notification
            $aAdmins = Phpfox::getService('pages')->getPageAdmins($aPage['page_id']);
            foreach ($aAdmins as $aAdmin) {
                if ($aAdmin['user_id'] == $aVideo['user_id']) { // is owner of video
                    continue;
                }

                if ($aPage['user_id'] == $aAdmin['user_id']) { // is owner of page
                    $varPhraseTitle = 'email_full_name_posted_a_video_on_your_page_title';
                    $varPhraseLink = 'full_name_posted_a_video_on_your_page_link';
                } else {
                    $varPhraseTitle = 'full_name_posted_a_video_on_page_title';
                    $varPhraseLink = 'full_name_posted_a_video_on_page_link';
                }

                Phpfox::getLib('mail')->to($aAdmin['user_id'])
                    ->subject([$varPhraseTitle, [
                        'full_name' => $postedUserFullName,
                        'title' => $aPage['title']
                    ]])
                    ->message([$varPhraseLink, [
                        'full_name' => $postedUserFullName,
                        'link' => $sLink,
                        'title' => $aPage['title']
                    ]])
                    ->notification('pages.email_notification')
                    ->send();

                if (Phpfox::isModule('notification') && $aPage['user_id'] != $aAdmin['user_id']) { // HAS ADDED NOTIFICATION ON VIDEO APP
                    Phpfox::getService('notification.process')->add('v_newItem_pages', $aVideo['video_id'], $aAdmin['user_id'], $aVideo['user_id'], $bForce);
                }
            }
        }
    }

    public function getMusicDetails($aItem)
    {
        Phpfox::getService('pages')->setIsInPage();

        $aRow = Phpfox::getService('pages')->getPage($aItem['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        Phpfox::getService('pages')->setMode();

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('pages'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('pages'),
            'module_id' => 'pages',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'music/',
            'theater_mode' => _p('in_the_page_link_title', ['link' => $sLink, 'title' => $aRow['title']])
        ];
    }

    public function getBlogDetails($aItem)
    {
        Phpfox::getService('pages')->setIsInPage();
        $aRow = Phpfox::getService('pages')->getPage($aItem['item_id']);
        if (!isset($aRow['page_id'])) {
            return false;
        }
        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'breadcrumb_title' => _p('pages'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('pages'),
            'module_id' => 'pages',
            'item_id' => $aRow['page_id'],
            'title' => $aRow['title'],
            'url_home' => $sLink,
            'url_home_photo' => $sLink . 'blog/',
            'theater_mode' => _p('in_the_page_link_title', ['link' => $sLink, 'title' => $aRow['title']])
        ];
    }

    public function uploadSong($iItemId)
    {
        Phpfox::getService('pages')->setIsInPage();

        return [
            'module' => 'pages',
            'item_id' => $iItemId,
            'table_prefix' => 'pages_'
        ];
    }

    public function getNotificationLike($aNotification)
    {
        $aRow = Phpfox::getService('pages')->getPage($aNotification['item_id']);

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
            $sPhrase = _p('users_liked_gender_own_page_title',
                ['users' => $sUsers, 'gender' => $sGender, 'title' => $sTitle]);
        } elseif ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_liked_your_page_title', ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('users_liked_full_names_page_title', [
                'users' => $sUsers,
                'full_name' => Phpfox::getLib('parse.output')->shorten($aRow['full_name'], 0),
                'title' => $sTitle
            ]);
        }

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function addForum($iId)
    {
        Phpfox::getService('pages')->setIsInPage();

        $aRow = Phpfox::getService('pages')->getPage($iId);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        return [
            'module' => 'pages',
            'item' => $aRow['page_id'],
            'group_id' => $aRow['page_id'],
            'url_home' => $sLink,
            'title' => $aRow['title'],
            'table_prefix' => 'pages_',
            'item_id' => $aRow['page_id'],
            'breadcrumb_title' => _p('pages'),
            'breadcrumb_home' => Phpfox_Url::instance()->makeUrl('pages')
        ];
    }

    public function getPagePerms()
    {
        $aPerms = [
            'pages.share_updates' => _p('who_can_share_a_post'),
            'pages.view_browse_updates' => _p('who_can_view_browse_comments'),
            'pages.view_browse_widgets' => _p('page_who_can_view_widgets'),
            'pages.view_admins' => _p('who_can_view_admins'),
            'pages.view_publish_date' => _p('pages_who_can_view_publish_date')
        ];

        return $aPerms;
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

        return Phpfox::getService('pages')->getUrl($aListing['page_id'], $aListing['title'],
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

        return Phpfox::getService('pages')->getUrl($aListing['page_id'], $aListing['title'],
                $aListing['vanity_url']) . 'comment-id_' . $aListing['item_id'] . '/';
    }

    public function getItemName($iId, $sName)
    {
        return '<a href="' . Phpfox_Url::instance()->makeUrl('comment.view', ['id' => $iId]) . '">' . _p('on_name_s_page_comment', ['name' => $sName]) . '</a>';
    }

    public function getCommentItem($iId)
    {
        $aRow = $this->database()->select('feed_comment_id AS comment_item_id, privacy_comment, user_id AS comment_user_id')
            ->from(Phpfox::getT('pages_feed_comment'))
            ->where('feed_comment_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = '0';

        if (!Phpfox::getService('comment')->canPostComment($aRow['comment_user_id'], $aRow['privacy_comment'])) {
            Phpfox_Error::set(_p('unable_to_post_a_comment_on_this_item_due_to_privacy_settings'));

            unset($aRow['comment_item_id']);
        }

        $aRow['parent_module_id'] = 'pages';

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
            $aPage = Phpfox::getService('pages')->getPage($aRow['profile_page_id']);
            $aRow['user_id'] = $aPage['user_id'];
        }

        // Send the user an email
        $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/';
        $sItemLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

        Phpfox::getService('comment.process')->notify([
                'user_id' => $aRow['user_id'],
                'item_id' => $aRow['feed_comment_id'],
                'owner_subject' => ['full_name_commented_on_a_comment_posted_on_the_page_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]],
                'owner_message' => ['full_name_commented_on_one_of_your_comments', [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'item_link' => $sItemLink,
                    'title' => $aRow['title'],
                    'link' => $sLink
                ]],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id' => 'pages_comment_feed',
                'mass_id' => 'pages',
                'mass_subject' => (Phpfox::getUserId() == $aRow['user_id'] ? ['full_name_commented_on_one_of_gender_page_comments',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)
                    ]] : ['full_name_commented_on_one_of_other_full_name_s_page_comments',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'other_full_name' => $aRow['full_name']]]),
                'mass_message' => (Phpfox::getUserId() == $aRow['user_id'] ? ['full_name_comment_on_one_of_gender',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                        'item_link' => $sItemLink,
                        'title' => $aRow['title'],
                        'link' => $sLink
                    ]] : ['full_name_commented_on_one_of_other_full_name', [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'other_full_name' => $aRow['full_name'],
                    'item_link' => $sItemLink,
                    'title' => $aRow['title'],
                    'link' => $sLink
                ]]),
                'exclude_users' => [$aRow['user_id']]
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

        $sPhrase = _p('users_commented_on_the_page_title', ['users' => $sUsers, 'title' => $sTitle]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'],
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
                $sPhrase = _p('users_commented_on_span_class_drop_data_user_full_name_s_span_comment_on_the_page_title',
                    ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
            } else {
                $sPhrase = _p('users_commented_on_gender_own_comment_on_the_page_title',
                    ['users' => $sUsers, 'gender' => $sGender, 'title' => $sTitle]);
            }
        } elseif ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_commented_on_one_of_your_comments_on_the_page_title',
                ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('users_commented_on_one_of_full_name',
                ['users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationComment_Link($aNotification)
    {
        $aLink = Phpfox::getService('link')->getLinkById($aNotification['item_id']);
        if (!$aLink) {
            return false;
        }

        $aPage = Phpfox::getService('pages')->getPage($aLink['item_id']);
        if (!$aPage) {
            return false;
        }

        if (!empty($aPage['user_id']) && $aPage['user_id'] == Phpfox::getUserId()) {
            // notification of owner
            $sPhrase = _p('full_name_posted_a_link_on_your_page_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aPage['title']
            ]);
        } else {
            // notification of admin
            $sPhrase = _p('full_name_posted_a_link_on_page_title', [
                'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => $aPage['title']
            ]);
        }

        return [
            'link' => $aLink['redirect_link'],
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getTotalItemCount($iUserId)
    {
        return [
            'field' => 'total_pages',
            'total' => $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('pages'), 'p')
                ->where('p.view_id = 0 AND p.user_id = ' . (int)$iUserId . ' AND p.app_id = 0 AND p.item_type = 0')
                ->execute('getSlaveField')
        ];
    }

    public function globalUnionSearch($sSearch)
    {
        $this->database()->select('item.page_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'pages\' AS item_type_id, item.image_path AS item_photo, item.image_server_id 	 AS item_photo_server')
            ->from(Phpfox::getT('pages'), 'item')
            ->where('item.view_id = 0 AND ' . $this->database()->searchKeywords('item.title',
                    $sSearch) . ' AND item.privacy = 0 AND item.item_type = 0')
            ->union();
    }

    public function getSearchInfo($aRow)
    {
        $aPage = $this->database()->select('p.page_id, p.title, pu.vanity_url, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('pages'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.profile_page_id = p.page_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
            ->where('p.page_id = ' . (int)$aRow['item_id'])
            ->execute('getSlaveRow');

        $aInfo = [];
        $aInfo['item_link'] = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        $aInfo['item_name'] = _p('page');
        $aInfo['profile_image'] = $aPage;

        return $aInfo;
    }

    public function getSearchTitleInfo()
    {
        return [
            'name' => _p('pages')
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

        $sPhrase = _p('your_page_has_been_approved', [
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
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
            return false;
        }

        $this->database()->updateCount('like', 'type_id = \'pages_comment\' AND item_id = ' . (int)$iItemId . '',
            'total_like', 'pages_feed_comment', 'feed_comment_id = ' . (int)$iItemId);

        if ($aRow['profile_page_id']) {
            $aPage = Phpfox::getService('pages')->getPage($aRow['profile_page_id']);
            $aRow['user_id'] = $aPage['user_id'];
        }

        if (!$bDoNotSendEmail) { // check has liked
            $sLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/';
            $sItemLink = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'pages.full_name_liked_a_comment_you_made_on_the_page_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ])
                ->message([
                    'pages.full_name_liked_a_comment_you_made_on_the_page_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'link' => $sLink,
                        'item_link' => $sItemLink,
                        'title' => $aRow['title']
                    ]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('pages_comment_like', $aRow['feed_comment_id'], $aRow['user_id']);
        }

        return true;
    }

    //It is posting feeds for comments made in a Page of type group set to registration method "invide only", this should not happen.
    public function deleteLikeComment($iItemId)
    {
        $this->database()->updateCount('like', 'type_id = \'pages_comment\' AND item_id = ' . (int)$iItemId . '',
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
            'name' => _p('users_pages_groups_count'),
            'id' => 'pages-total'
        ];

        return $aList;
    }

    public function updateCounter($iId, $iPage, $iPageLimit)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('user'))
            ->execute('getSlaveField');

        $aRows = $this->database()->select('u.user_id, u.user_name, u.full_name, COUNT(b.page_id) AS total_items')
            ->from(Phpfox::getT('user'), 'u')
            ->leftJoin(Phpfox::getT('pages'), 'b', 'b.user_id = u.user_id AND b.view_id = 0 AND b.app_id = 0')
            ->limit($iPage, $iPageLimit, $iCnt)
            ->group('u.user_id')
            ->execute('getSlaveRows');

        foreach ($aRows as $aRow) {
            $this->database()->update(Phpfox::getT('user_field'), ['total_pages' => $aRow['total_items']],
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
                $sPhrase = _p('users_liked_span_class_drop_data_user_row_full_name_s_span_comment_on_the_page_title',
                    [
                        'users' => Phpfox::getService('notification')->getUsers($aNotification, true),
                        'row_full_name' => $aRow['full_name'],
                        'title' => $sTitle
                    ]);
            } else {
                $sPhrase = _p('users_liked_gender_own_comment_on_the_page_title', [
                    'users' => $sUsers,
                    'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                    'title' => $sTitle
                ]);
            }
        } elseif ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_liked_one_of_your_comments_on_the_page_title',
                ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('users_liked_one_on_span_class_drop_data_user_row_full_name_s_span_comments_on_the_page_title',
                ['users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']) . 'wall/comment-id_' . $aRow['feed_comment_id'] . '/',
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
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

        $sPhrase = _p('you_have_been_invited_to_become_an_admin_of_page', ['page_name' => $aRow['title']]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /* Used to get a page when there is no certainty of the module */
    public function getItem($iId)
    {
        Phpfox::getService('pages')->setIsInPage();
        $aItem = $this->database()->select('*')->from(Phpfox::getT('pages'))->where('item_type = 0 AND page_id = ' . (int)$iId)->execute('getSlaveRow');
        if (empty($aItem)) {
            return false;
        }
        $aItem['module'] = 'pages';
        $aItem['module_title'] = _p('pages');
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
            ->where('user_id = ' . (int)$iUser . ' AND item_type = 0')
            ->execute('getSlaveRows');

        foreach ($aRows as $aRow) {
            Phpfox::getService('pages.process')->delete($aRow['page_id'], true, true);
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
        if ($sPlugin = Phpfox_Plugin::get('pages.service_callback__call')) {
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
        return Phpfox::getService('pages')->hasPerm($iId, $sName);
    }

    public function getReportRedirect($iId)
    {
        return Phpfox::getService('pages')->getUrl($iId);
    }

    /**
     * @discussion: callback to check permission to get feeds of a page
     * @param $iId
     *
     * @return bool
     */
    public function canGetFeeds($iId)
    {
        $aPage = Phpfox::getService('pages')->getPage($iId);
        if (!$aPage || empty($aPage['page_id'])) {
            return false;
        }

        return Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'pages.view_browse_updates');
    }

    /**
     * Return callback param for adding feed comment on page
     * @param $iId
     * @param $aVals
     * @return array|bool
     * @throws \Exception
     */
    public function getFeedComment($iId, $aVals)
    {
        //check permission
        Phpfox::isUser(true);

        $bPostAsPage = Phpfox_Request::instance()->get('custom_pages_post_as_page', 0);

        if ($bPostAsPage && $bPostAsPage != $iId) {
            Phpfox_Error::set(_p('Cannot post as page on others pages.'));

            return false;
        }

        if (!$bPostAsPage && !(Phpfox::getService('pages')->hasPerm($iId, 'pages.share_updates'))) {
            return false;
        }

        //validate data
        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status'])) {
            Phpfox_Error::set(_p('add_some_text_to_share'));

            return false;
        }

        $aPage = Phpfox::getService('pages')->getPage($iId);

        //check exists page
        if (!isset($aPage['page_id'])) {
            Phpfox_Error::set(_p('unable_to_find_the_page_you_are_trying_to_comment_on'));

            return false;
        }

        $sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        $aCallback = [
            'module' => 'pages',
            'table_prefix' => 'pages_',
            'link' => $sLink,
            'email_user_id' => $aPage['user_id'],
            'subject' => ['full_name_wrote_a_comment_on_your_page_title',
                ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title']]],
            'message' => ['full_name_wrote_a_comment_link',
                ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aPage['title']]],
            'notification' => ($bPostAsPage ? null : 'pages_comment'),
            'feed_id' => 'pages_comment',
            'item_id' => $aPage['page_id'],
            'add_tag' => true
        ];

        return $aCallback;
    }

    /**
     * @description: callback after a comment feed added on page
     * @param $iPageId
     */
    public function onAddFeedCommentAfter($iPageId)
    {
        Phpfox_Database::instance()->updateCounter('pages', 'total_comment', 'page_id', $iPageId);
    }

    /**
     * @description: check permission when add like for pages
     * @param $iId
     *
     * @return bool
     */
    public function canLikeItem($iId)
    {
        $aItem = Phpfox::getService('pages')->getForView($iId);
        if (empty($aItem) || empty($aItem['page_id'])) {
            return false;
        }
        if (($aItem['page_type'] == '1') && ($aItem['reg_method'] == 2 || $aItem['reg_method'] == 1)) {
            return false;
        }

        return true;
    }

    public function canShareOnMainFeed($iPageId, $sPerm, $bChildren)
    {
        return Phpfox::getService('pages')->hasPerm($iPageId, $sPerm);
    }

    /**
     * Login as pages comment
     *
     * @return string
     */
    public function getCommentItemName()
    {
        return 'pages';
    }


    /**
     * Check admin of page
     * @param $iPageId
     * @return bool
     * @throws \Exception
     */
    public function isAdmin($iPageId)
    {
        $aErrors = Phpfox_Error::get();
        $bIsAdmin = Phpfox::getService('pages')->isAdmin($iPageId);
        Phpfox_Error::reset();
        foreach ($aErrors as $sError) {
            Phpfox_Error::set($sError);
        }

        return $bIsAdmin;
    }

    /**
     * This function will add number of pending page to admin dashboard statistics
     * @return array
     */
    public function pendingApproval()
    {
        return [
            'phrase' => _p('pages_app'),
            'value' => Phpfox::getService('pages')->getPendingTotal(),
            'link' => Phpfox_Url::instance()->makeUrl('pages', ['view' => 'pending'])
        ];
    }

    public function getAdmincpAlertItems()
    {
        $iTotalPending = Phpfox::getService('pages')->getPendingTotal();
        return [
            'message' => _p('you_have_total_pending_pages', ['total' => $iTotalPending]),
            'value' => $iTotalPending,
            'link' => Phpfox_Url::instance()->makeUrl('pages', ['view' => 'pending'])
        ];
    }

    public function getNotificationDeny_Claim($aNotification)
    {
        $aPage = $this->getFacade()->getItems()->getPage($aNotification['item_id']);
        $sPhrase = _p('your_claim_has_been_denied',
            ['moderator' => $aNotification['full_name'], 'page' => $aPage['title']]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationApprove_Claim($aNotification)
    {
        $aPage = $this->getFacade()->getItems()->getPage($aNotification['item_id']);
        $sPhrase = _p('your_claim_has_been_approved',
            ['moderator' => $aNotification['full_name'], 'page' => $aPage['title']]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationRemove_Owner($aNotification)
    {
        $aPage = $this->getFacade()->getItems()->getPage($aNotification['item_id']);
        $sPhrase = _p('you_has_been_removed_as_owner_of_page',
            ['page' => $aPage['title'], 'moderator' => $aNotification['full_name']]);

        return [
            'link' => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationPost_Image($aNotification)
    {
        // get pages from photo id
        $aPhoto = Phpfox::getService('photo')->getPhotoItem($aNotification['item_id']);
        if (!$aPhoto) {
            return false;
        }

        $aPage = $this->getFacade()->getItems()->getPage($aPhoto['group_id']);
        if (!$aPage) {
            return false;
        }

        if (!empty($aPage['user_id']) && $aPage['user_id'] == Phpfox::getUserId()) {
            // notification of owner
            $sPhrase = _p('full_name_post_some_images_on_your_page_title', [
                'full_name' => $aNotification['full_name'],
                'title' => $aPage['title']
            ]);
        } else {
            // notification of admin
            $sPhrase = _p('full_name_post_some_images_on_page_title', [
                'full_name' => $aNotification['full_name'],
                'title' => $aPage['title']
            ]);
        }

        return [
            'link' => Phpfox::getService('photo.callback')->getLink(['item_id' => $aPhoto['photo_id']]),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Check if need to hide profile photos or cover photos
     * @param $iPageId
     * @return array
     */
    public function getHiddenAlbums($iPageId)
    {
        $aHiddenAlbums = [];
        if (!Phpfox::getParam('pages.display_pages_profile_photo_within_gallery', true)) {
            list($iCnt, $aProfileAlbums) = Phpfox::getService('photo.album')->get([
                'pa.module_id = \'pages\'',
                'AND pa.group_id = ' . $iPageId,
                'AND pa.profile_id != 0'
            ]);
            $iCnt && ($aHiddenAlbums[] = $aProfileAlbums[0]['album_id']);
        }
        if (!Phpfox::getParam('pages.display_pages_cover_photo_within_gallery', true)) {
            list($iCnt, $aCoverAlbums) = Phpfox::getService('photo.album')->get([
                'pa.module_id = \'pages\'',
                'AND pa.group_id = ' . $iPageId,
                'AND pa.cover_id != 0'
            ]);
            $iCnt && ($aHiddenAlbums[] = $aCoverAlbums[0]['album_id']);
        }

        return $aHiddenAlbums;
    }

    /**
     * This callback will be call if a photo of module pages deleted
     * @param $aPhoto
     */
    public function onDeletePhoto($aPhoto)
    {
        $canClearCache = false;

        $pageInfo = db()->select('p.cover_photo_id, u.user_id, p.image_path, p.image_server_id, u.user_image, u.server_id AS user_server_id')
                        ->from(':pages', 'p')
                        ->join(':user', 'u', 'u.profile_page_id = p.page_id')
                        ->where([
                            'p.page_id' => $aPhoto['group_id']
                        ])->executeRow(false);

        if ($pageInfo['cover_photo_id'] == $aPhoto['photo_id']) {
            db()->update(':pages', ['cover_photo_id' => null], ['page_id' => $aPhoto['group_id']]);
        }

        $profileImageObject = storage()->get('user/avatar/' . $pageInfo['user_id']);
        if (!empty($profileImageObject) && $profileImageObject->value == $aPhoto['photo_id']) {
            storage()->del('user/avatar/' . $pageInfo['user_id']);
            if (db()->update(':pages', [
                'image_path' => '',
                'image_server_id' => 0,
            ], ['page_id' => $aPhoto['group_id']])) {
                if (db()->update(':user', [
                    'user_image' => '',
                    'server_id' => 0,
                ], ['user_id' => $pageInfo['user_id']])) {
                    $this->_deleteUnusedImages(Phpfox::getParam('core.dir_user'), $pageInfo['user_image'], $pageInfo['user_server_id'], Phpfox::getService('user')->getUserThumbnailSizes());
                }
                $this->_deleteUnusedImages(Phpfox::getParam('pages.dir_image'), $pageInfo['image_path'], $pageInfo['image_server_id'], Phpfox::getService('pages')->getPhotoPicSizes());
            }
            $canClearCache = true;
        }

        if ($canClearCache) {
            Phpfox::getService('pages')->clearCachesForLoginAsPagesListing($aPhoto['group_id']);
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

    /**
     * @return array
     */
    public function getUploadParams($aExtraParams = [])
    {
        return Phpfox::getService('pages')->getUploadPhotoParams($aExtraParams);
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
        $aRow = $this->database()->select($sSelect . ' , p.destination, u.server_id, u.profile_page_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(':user', 'u', 'u.user_id=p.user_id')
            ->where([
                'p.photo_id' => (int)$aItem['item_id'],
                'p.is_profile_photo' => 1
            ])->execute('getSlaveRow');

        if (empty($aRow)) {
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
            'feed_info' => _p('updated_their_profile_photo'),
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
            'parent_user_id' => Phpfox::getService('pages')->getPageOwnerId($aRow['profile_page_id'])
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
        $aRow = $this->database()->select($sSelect . ' , p.destination, p.server_id, u.profile_page_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(':user', 'u', 'u.user_id=p.user_id')
            ->where([
                'p.photo_id' => (int)$aItem['item_id']
            ])->execute('getSlaveRow');

        if (empty($aRow)) {
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
            'feed_info' => _p('updated_their_cover_photo'),
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
            'parent_user_id' => Phpfox::getService('pages')->getPageOwnerId($aRow['profile_page_id'])
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

        $iTotalPages = db()->select('COUNT(*)')->from(':pages')->where(['user_id' => $iUserId, 'item_type' => 0])->executeField();

        return [
            'total_name' => _p('pages'),
            'total_value' => $iTotalPages,
            'type' => 'item'
        ];
    }

    public function showCoverInDetailItem($iPageId)
    {
        $aPage = Phpfox::getService('pages')->getForView($iPageId);
        Phpfox_Component::setPublicParam('show_page_cover', true);
        Phpfox_Component::setPublicParam('page_to_show_cover', $aPage);
    }

    /**
     * @param $aParams
     * @return bool
     */
    public function enableSponsor($aParams)
    {
        return Phpfox::getService('pages.process')->sponsor($aParams['item_id'], 1);
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
                'error' => _p('sorry_the_page_you_are_looking_for_no_longer_exists',
                    ['link' => Phpfox::getLib('url')->makeUrl('pages')])
            ];
        }

        $aPage['link'] = Phpfox::permalink('pages', $aPage['item_id']);
        $aPage['paypal_msg'] = _p('sponsor_paypal_message_page', ['sPageTitle' => $aPage['title']]);
        $aPage['image_dir'] = 'pages.url_image';
        $aPage['title'] = _p('sponsor_title_page', ['sPageTitle' => $aPage['title']]);

        $aPage = array_merge($aPage, [
            'redirect_completed' => 'pages',
            'message_completed' => _p('purchase_page_sponsor_completed'),
            'redirect_pending_approval' => 'pages',
            'message_pending_approval' => _p('purchase_page_sponsor_pending_approval')
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
        $this->cache()->remove('pages_sponsored');
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
        return Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
    }

    public function getNotificationReassign_Owner($aNotification)
    {
        $aRow = \Phpfox::getService('pages')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('full_name_just_assigned_you_as_owner_of_page_title', [
            'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => \Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
            'message' => $sPhrase,
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    public function getNotificationOwner_Changed($aNotification)
    {
        $aRow = Phpfox::getService('pages')->getPage($aNotification['item_id']);

        if (!isset($aRow['page_id'])) {
            return false;
        }

        $sPhrase = _p('full_name_just_transfer_your_page_title_to_another_user', [
            'full_name' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => \Phpfox::getLib('parse.output')->shorten($aRow['title'],
                \Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link' => \Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']),
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

        return Phpfox::getService('pages')->getUrl($aFeedComment['page_id'], $aFeedComment['title'],
                $aFeedComment['vanity_url']) . 'comment-id_' . $aFeedComment['feed_comment_id'] . '/';
    }

    public function getNotificationSettings()
    {
        return [
            'pages.email_notification' => [
                'phrase' => _p('pages_notifications'),
                'default' => 1
            ]
        ];
    }
}
