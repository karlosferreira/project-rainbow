<?php

namespace Apps\Phpfox_Videos\Service;

use Aws\S3\S3Client;
use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

class Callback extends Phpfox_Service
{

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('video');
    }

    /**
     * Process updating video info
     *
     * @param $data
     *
     * @return bool|void
     */
    public function updateFeedItemInfo($data)
    {
        $content = Phpfox::getLib('parse.input')->prepare($data['content'], false);
        $iItemId = $data['item_id'];
        $location = isset($data['location']) ? $data['location'] : null;
        $iPrivacy = $data['privacy'];

        // Update video info
        db()->update(Phpfox::getT('video'), [
            'status_info'     => $content,
            'privacy'         => $iPrivacy,
            'location_name'   => isset($location['location_name']) ? $location['location_name'] : null,
            'location_latlng' => isset($location['location_latlng']) ? $location['location_latlng'] : null,
        ], 'video_id = ' . (int)$iItemId);
    }

    /**
     * Process send notify to tagged users
     *
     * @param $params
     *
     * @return bool
     */
    public function sendNotifyToTaggedUsers($params)
    {
        $sFeedType = $params['feed_type'];
        $aTagged = $params['tagged_friend'];
        $iItemId = $params['item_id'];
        $iOwnerId = $params['owner_id'];
        $iFeedId = $params['feed_id'];
        $iPrivacy = $params['privacy'];
        $iParentUserId = (int)$params['parent_user_id'];
        $moduleId = isset($params['module_id']) ? $params['module_id'] : '';

        // check title and link of video
        list($title, $link) = Phpfox::getService('v.video')->getFeedLink($iItemId);
        if (empty($link)) {
            return false;
        }

        $aCurrentUser = Phpfox::getService('user')->getUser($iOwnerId);
        $sTagger = (isset($aCurrentUser['full_name']) && $aCurrentUser['full_name']) ? $aCurrentUser['full_name'] : $aCurrentUser['user_name'];

        //Send Mail and add feed
        foreach ($aTagged as $iUserId) {
            if (in_array($moduleId, ['', 'user', 'video'])) {
                if ($moduleId != 'video' && $iParentUserId == $iUserId) {
                    continue;
                }
                (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add($sFeedType, $iItemId, $iPrivacy, 0, $iUserId, $iOwnerId, 1, $iFeedId) : null);
            }
            if (empty($params['no_notification']) && Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('v_tagged', $iItemId, $iUserId, $iOwnerId, true);
            }
            if (empty($params['no_email'])) {
                Phpfox::getLib('mail')->to($iUserId)
                    ->notification('feed.tagged_in_post')
                    ->subject(['email_user_name_tagged_you_in_video_tittle', ['user_name' => $sTagger, 'title' => $title]])
                    ->message([
                        'user_name_tagged_you_in_video_tittle_link',
                        ['user_name' => $sTagger, 'link' => $link, 'title' => $title]
                    ])
                    ->send();
            }
        }
        return true;
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationTagged($aNotification)
    {
        // check link of video exist
        list($title, $link) = Phpfox::getService('v.video')->getFeedLink($aNotification['item_id']);
        if (empty($link)) {
            return false;
        }
        $title = Phpfox::getLib('parse.output')->shorten($title,
            setting('notification.total_notification_title_length'), '...');
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        return [
            'message' => _p('user_name_tagged_you_in_video_tittle', [
                'user_name' => $sUsers,
                'title'     => $title
            ]),
            'link'    => $link,
        ];
    }

    /**
     * @return bool
     */
    public function canShareItemOnFeed()
    {
        return true;
    }

    /**
     * @param $aParams
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function enableSponsor($aParams)
    {
        return Phpfox::getService('v.process')->sponsor((int)$aParams['item_id'], 1);
    }

    /**
     * Returns information related to a video for sponsoring purposes
     *
     * @param int $iId video_id
     *
     * @return array in the format:
     * array(
     *    'title' => 'item title',            <-- required
     *    'link'  => 'makeUrl()'ed link',            <-- required
     *    'paypal_msg' => 'message for paypal'        <-- required
     *    'item_id' => int                <-- required
     *    'error' => 'phrase if item doesnt exit'        <-- optional
     *    'extra' => 'description'            <-- optional
     *    'image' => 'path to an image',            <-- optional
     *    'image_dir' => 'photo.url_photo|...        <-- optional (required if image)
     *    'server_id' => 'value from DB'            <-- optional (required if image)
     * );
     */
    public function getToSponsorInfo($iId)
    {
        $aVideo = $this->database()->select('v.user_id, v.title, v.video_id as item_id, vt.text_parsed as extra,
		       v.image_path, v.image_server_id')
            ->from(Phpfox::getT('video'), 'v')
            ->leftjoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->where('v.video_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aVideo)) {
            return ['error' => _p('video_sponsor_error_not_found')];
        }

        $aVideo['link'] = Phpfox::permalink('video.play', $aVideo['item_id'], $aVideo['title']);
        $aVideo['paypal_msg'] = _p('video_sponsor_paypal_message',
            ['sVideoTitle' => $aVideo['title']]);//'Video Sponsor ' . $aVideo['title'];
        $aVideo['title'] = _p('video_sponsor_title', ['sVideoTitle' => $aVideo['title']]);
        $aVideo['image_dir'] = 'core.url_pic';
        Phpfox::getService('v.video')->convertImagePath($aVideo);
        $aVideo['full_image_path'] = $aVideo['image_path'];
        $aVideo['message_completed'] = _p('purchase_video_sponsor_completed');
        $aVideo['message_pending_approval'] = _p('purchase_video_sponsor_pending_approval');
        $aVideo['redirect_completed'] = 'video';
        $aVideo['redirect_pending_approval'] = 'video';

        return $aVideo;
    }

    /**
     * @param $aParams
     *
     * @return bool|string
     */
    public function getLink($aParams)
    {
        $aVideo = $this->database()->select('v.video_id, v.title')
            ->from(Phpfox::getT('video'), 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.video_id = ' . (int)$aParams['item_id'])
            ->execute('getSlaveRow');

        if (empty($aVideo)) {
            return false;
        }

        $sLink = Phpfox::permalink('video.play', (int)$aParams['item_id'], $aVideo['title']);

        return $sLink;
    }

    /**
     * @return array
     */
    public function getDashboardActivity()
    {
        if (!Phpfox::getUserParam('v.pf_video_view')) {
            return [];
        }
        $aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);
        return [
            _p('videos') => $aUser['activity_v']
        ];
    }

    /**
     * @param int $iStartTime
     * @param int $iEndTime
     *
     * @return array
     */
    public function getSiteStatsForAdmin($iStartTime, $iEndTime)
    {
        $aCond = [];
        $aCond[] = 'view_id = 0 AND in_process = 0';
        if ($iStartTime > 0) {
            $aCond[] = 'AND time_stamp >= \'' . db()->escape($iStartTime) . '\'';
        }
        if ($iEndTime > 0) {
            $aCond[] = 'AND time_stamp <= \'' . db()->escape($iEndTime) . '\'';
        }

        $iCnt = (int)db()->select('COUNT(video_id)')
            ->from($this->_sTable)
            ->where($aCond)
            ->execute('getSlaveField');

        return [
            'phrase' => 'videos',
            'total'  => $iCnt
        ];
    }

    /**
     * @return array
     */
    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        return [
            'phrase' => _p('videos'),
            'value'  => $this->database()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where('view_id = 0 AND in_process = 0 AND time_stamp >= ' . $iToday)
                ->execute('getSlaveField')
        ];
    }

    /**
     * @param      $aItem
     * @param null $aCallback
     * @param bool $bIsChildItem
     *
     * @return array|boolean
     * @throws \Exception
     */
    public function getActivityFeed($aItem, $aCallback = null, $bIsChildItem = false)
    {
        if (!user('pf_video_view')) {
            return false;
        }

        //Check in case the feed with tagging user is private
        if (!empty($aItem['parent_user_id'])
            && $aItem['parent_user_id'] == Phpfox::getUserId()
            && $aItem['user_id'] != $aItem['parent_user_id']
            && Phpfox::isModule('privacy')
            && !Phpfox::getService('privacy')->check($aItem['type_id'], $aItem['item_id'], $aItem['user_id'], $aItem['privacy'], null, true)) {
            return false;
        }

        if (Phpfox::isUser() && Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'v\' AND l.item_id = v.video_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if ($aCallback === null) {
            db()->select(Phpfox::getUserField('u', 'parent_') . ', ')->leftJoin(Phpfox::getT('user'), 'u',
                'u.user_id = v.parent_user_id');
        }

        if ($bIsChildItem) {
            db()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2',
                'u2.user_id = v.user_id');
        }

        $iItemId = $aItem['item_id'];
        $aRow = db()->select('v.location_latlng, v.location_name, v.user_id, v.video_id, v.title, v.status_info, v.time_stamp, v.total_comment, v.total_view as video_total_view, v.privacy, v.total_like, v.module_id, v.item_id, v.image_path, v.image_server_id, v.is_stream, v.server_id, v.destination, vt.text_parsed as text, ve.video_url, ve.embed_code, v.duration, v.resolution_x, v.resolution_y')
            ->from(Phpfox::getT('video'), 'v')
            ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->leftJoin(Phpfox::getT('video_embed'), 've', 've.video_id = v.video_id')
            ->where('v.video_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');
        if (!isset($aRow['video_id'])) {
            return false;
        }

        /**
         * Check active parent module
         */
        if (!empty($aRow['module_id']) && $aRow['module_id'] != 'video' && !Phpfox::isModule($aRow['module_id'])) {
            return false;
        }

        if ($bIsChildItem) {
            $aItem = array_merge($aRow, $aItem);
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && !Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->hasPerm(null,
                    'pf_video.view_browse_videos'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages') && !Phpfox::getService('pages')->hasPerm($aRow['item_id'],
                    'pf_video.view_browse_videos'))
            || ($aRow['module_id'] && Phpfox::isModule($aRow['module_id']) && Phpfox::hasCallback($aRow['module_id'],
                    'canShareOnMainFeed') && !Phpfox::callback($aRow['module_id'] . '.canShareOnMainFeed',
                    $aRow['item_id'], 'pf_video.view_browse_videos', $bIsChildItem))
        ) {
            return false;
        }

        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getactivityfeed__1')) ? eval($sPlugin) : false);

        $aRow['is_in_feed'] = true;

        Phpfox::getLib('template')->assign('aVideo', $aRow);
        Phpfox_Component::setPublicParam('custom_param_' . $aItem['feed_id'], $aRow);
        $aRow = Phpfox::getService('v.video')->compileVideo($aRow);

        $sTypeId = 'v';
        $iSponsorId = (Phpfox::isAppActive('Core_BetterAds') && isset($aItem['sponsor_feed_id']) && (int)$aItem['feed_id'] === (int)$aItem['sponsor_feed_id']) ? Phpfox::getService('ad.get')->getFeedSponsors($aItem['feed_id']) : 0;
        $aReturn = [
            'feed_title'        => $aRow['title'],
            'privacy'           => $aRow['privacy'],
            'feed_status'       => $aRow['status_info'],
            'feed_info'         => _p('shared_a_video'),
            'feed_link'         => ((int)$aItem['feed_id'] === (int)$aItem['sponsor_feed_id']) && $iSponsorId ? Phpfox::getLib('url')->makeUrl('ad.sponsor', ['view' => $iSponsorId]) : Phpfox::permalink('video.play', $aRow['video_id'], $aRow['title']),
            'total_comment'     => $aRow['total_comment'],
            'feed_total_like'   => $aRow['total_like'],
            'feed_is_liked'     => (isset($aRow['is_liked']) ? $aRow['is_liked'] : false),
            'feed_icon'         => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/video.png',
                'return_url' => true
            ]),
            'time_stamp'        => $aRow['time_stamp'],
            'enable_like'       => true,
            'comment_type_id'   => $sTypeId,
            'like_type_id'      => $sTypeId,
            'custom_data_cache' => $aRow,
            'feed_content'      => $aRow['text'],
            'video_total_view'  => $aRow['video_total_view']
        ];

        if ($bIsChildItem) {
            $aReturn = array_merge($aReturn, $aItem);
        }

        if (!empty($aRow['parent_user_id'])) {
            unset($aReturn['feed_info']);
        }
        if (!empty($aRow['location_name'])) {
            $aReturn['location_name'] = $aRow['location_name'];
        }
        if (!empty($aRow['location_latlng'])) {
            $aReturn['location_latlng'] = json_decode($aRow['location_latlng'], true);
        }
        // get tagged users
        $aReturn['total_friends_tagged'] = Phpfox::getService('feed.tag')->getTaggedUsers($iItemId, $sTypeId, true);
        if ($aReturn['total_friends_tagged']) {
            $aReturn['friends_tagged'] = Phpfox::getService('feed.tag')->getTaggedUsers($iItemId, $sTypeId, false, 1, 2);
        }

        if ($aCallback === null && !empty($aRow['parent_user_name'])) {
            $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
        }

        $aReturn['type_id'] = $sTypeId;
        $aReturn['load_block'] = 'v.feed_video';
        $aReturn['is_stream'] = isset($aRow['is_stream']) ? $aRow['is_stream'] : 0;
        $aReturn['video_id'] = isset($aRow['video_id']) ? $aRow['video_id'] : 0;
        $aReturn['embed_code'] = isset($aRow['embed_code']) ? $aRow['embed_code'] : "";
        $aReturn['embed_poster'] = isset($aRow['image_path']) ? $aRow['image_path'] : "";
        $aReturn['is_facebook_embed'] = isset($aRow['is_facebook_embed']) ? $aRow['is_facebook_embed'] : 0;

        if (!defined('PHPFOX_IS_PAGES_VIEW') && (($aRow['module_id'] == 'groups' && Phpfox::isAppActive('PHPfox_Groups')) || ($aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages')))) {
            $aPage = db()->select('p.*, pu.vanity_url, ' . Phpfox::getUserField('u', 'parent_'))
                ->from(':pages', 'p')
                ->join(':user', 'u', 'p.page_id=u.profile_page_id')
                ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = p.page_id')
                ->where('p.page_id=' . (int)$aRow['item_id'])
                ->execute('getSlaveRow');

            $aReturn['parent_user_name'] = Phpfox::getService($aRow['module_id'])->getUrl($aPage['page_id'],
                $aPage['title'], $aPage['vanity_url']);
            $aReturn['feed_table_prefix'] = 'pages_';
            if ($aRow['user_id'] != $aPage['parent_user_id']) {
                $aReturn['parent_user'] = Phpfox::getService('user')->getUserFields(true, $aPage, 'parent_');
                unset($aReturn['feed_info']);
            }
        }

        return $aReturn;
    }

    /**
     * @param      $iItemId
     * @param bool $bDoNotSendEmail
     *
     * @return bool|null
     */
    public function addLike($iItemId, $bDoNotSendEmail = false)
    {
        $aRow = db()->select('v.video_id, v.title, v.user_id, v.module_id, v.item_id, u.profile_page_id')
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('video_id = ' . (int)$iItemId)
            ->execute('getSlaveRow');

        if (!isset($aRow['video_id'])) {
            return false;
        }

        if ($aRow['profile_page_id'] && in_array($aRow['module_id'], ['pages', 'groups'])) {
            $aPage = Phpfox::getService($aRow['module_id'])->getPage($aRow['profile_page_id']);
            $aRow['user_id'] = $aPage['user_id'];
        }

        db()->updateCount('like', 'type_id = \'v\' AND item_id = ' . (int)$iItemId, 'total_like', 'video',
            'video_id = ' . (int)$iItemId);
        if (!$bDoNotSendEmail) {
            $sLink = Phpfox::permalink('video.play', $aRow['video_id'], $aRow['title']);

            Phpfox::getLib('mail')->to($aRow['user_id'])
                ->subject([
                    'full_name_liked_your_video_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                ])
                ->message([
                    'full_name_liked_your_video_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aRow['title']]
                ])
                ->notification('like.new_like')
                ->send();

            Phpfox::getService('notification.process')->add('v_like', $aRow['video_id'], $aRow['user_id']);

            if ($aRow['module_id'] == 'user') {
                Phpfox::getLib('mail')->to($aRow['item_id'])
                    ->subject([
                        'full_name_liked_a_video_title_on_your_wall',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aRow['title']]
                    ])
                    ->message([
                        'full_name_liked_a_video_title_on_your_wall_message',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'link'      => $sLink,
                            'title'     => $aRow['title']
                        ]
                    ])
                    ->notification('like.new_like')
                    ->send();

                Phpfox::getService('notification.process')->add('v_like', $aRow['video_id'], $aRow['item_id']);
            }
        }

        return null;
    }

    /**
     * @param int $iItemId
     */
    public function deleteLike($iItemId)
    {
        db()->updateCount('like', 'type_id = \'v\' AND item_id = ' . (int)$iItemId . '', 'total_like',
            'video', 'video_id = ' . (int)$iItemId);
    }

    /**
     * @param $aRow
     *
     * @return mixed
     */
    public function getNewsFeed($aRow)
    {
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getnewsfeed__start')) ? eval($sPlugin) : false);

        $oUrl = Phpfox::getLib('url');

        $aRow['text'] = _p('owner_full_name_added_a_new_video_title',
            [
                'owner_full_name' => $aRow['owner_full_name'],
                'title'           => Phpfox::getService('feed')->shortenTitle($aRow['content']),
                'user_link'       => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                'title_link'      => $aRow['link']
            ]
        );

        $aRow['icon'] = 'module/video.png';
        $aRow['enable_like'] = true;
        $aRow['comment_type_id'] = 'video';

        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getnewsfeed__end')) ? eval($sPlugin) : false);

        return $aRow;
    }

    /**
     * @param $aRow
     *
     * @return mixed
     */
    public function getCommentNewsFeed($aRow)
    {
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getcommentnewsfeed__start')) ? eval($sPlugin) : false);
        $oUrl = Phpfox::getLib('url');

        if ($aRow['owner_user_id'] == $aRow['item_user_id']) {
            $aRow['text'] = _p('user_added_a_new_comment_on_their_own_video', [
                    'user_name'  => $aRow['owner_full_name'],
                    'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                    'title_link' => $aRow['link']
                ]
            );
        } else if ($aRow['item_user_id'] == Phpfox::getUserBy('user_id')) {
            $aRow['text'] = _p('user_added_a_new_comment_on_your_video', [
                    'user_name'  => $aRow['owner_full_name'],
                    'user_link'  => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                    'title_link' => $aRow['link']
                ]
            );
        } else {
            $aRow['text'] = _p('user_name_added_a_new_comment_on_item_user_name_video', [
                    'user_name'      => $aRow['owner_full_name'],
                    'user_link'      => $oUrl->makeUrl('feed.user', ['id' => $aRow['user_id']]),
                    'title_link'     => $aRow['link'],
                    'item_user_name' => $aRow['viewer_full_name'],
                    'item_user_link' => $oUrl->makeUrl('feed.user', ['id' => $aRow['viewer_user_id']])
                ]
            );
        }

        $aRow['text'] .= Phpfox::getService('feed')->quote($aRow['content']);
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getcommentnewsfeed__end')) ? eval($sPlugin) : false);

        return $aRow;
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getCommentNotification($aNotification)
    {
        $aRow = Phpfox::getService('v.video')->getInfoForNotification((int)$aNotification['item_id']);

        if (!isset($aRow['video_id'])) {
            return false;
        }

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            setting('notification.total_notification_title_length'), '...');

        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users'])) {
            $sPhrase = _p('users_commented_on_gender_video_title', [
                'users'  => $sUsers,
                'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'  => $sTitle
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_commented_on_your_video_title', ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('users_commented_on_span_class_drop_data_user_row_full_name_video',
                ['users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        return [
            'link'    => $aRow['link'],
            'message' => $sPhrase,
            'icon'    => 'fa-video-camera'
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationLike($aNotification)
    {
        $aRow = Phpfox::getService('v.video')->getInfoForNotification((int)$aNotification['item_id']);

        if (!$aRow) {
            return false;
        }

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            setting('notification.total_notification_title_length'), '...');

        if ($aNotification['user_id'] == $aRow['user_id']) {
            $sPhrase = _p('users_liked_gender_own_video_title', [
                'users'  => $sUsers,
                'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1),
                'title'  => $sTitle
            ]);
        } else if ($aRow['user_id'] == Phpfox::getUserId()) {
            $sPhrase = _p('users_liked_your_video_title', ['users' => $sUsers, 'title' => $sTitle]);
        } else {
            $sPhrase = _p('users_liked_span_class_drop_data_user_row_full_name_s_span_video_title',
                ['users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle]);
        }

        return [
            'link'    => $aRow['link'],
            'message' => $sPhrase,
            'icon'    => 'fa-video-camera'
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationApproved($aNotification)
    {
        $aRow = Phpfox::getService('v.video')->getInfoForNotification((int)$aNotification['item_id']);
        if (!$aRow) {
            return false;
        }

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            setting('notification.total_notification_title_length'), '...');

        $sPhrase = _p('your_video_title_is_approved_by_sender', ['title' => $sTitle, 'sender' => $sUsers]);

        return [
            'link'    => $aRow['link'],
            'message' => $sPhrase,
            'icon'    => 'fa-video-camera'
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationFeatured($aNotification)
    {
        $aRow = Phpfox::getService('v.video')->getInfoForNotification((int)$aNotification['item_id']);

        if (!$aRow) {
            return false;
        }

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            setting('notification.total_notification_title_length'), '...');

        $sPhrase = _p('your_video_title_is_featured_by_sender', ['title' => $sTitle, 'sender' => $sUsers]);

        return [
            'link'    => $aRow['link'],
            'message' => $sPhrase,
            'icon'    => 'fa-video-camera'
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationReady($aNotification)
    {
        $aRow = Phpfox::getService('v.video')->getInfoForNotification((int)$aNotification['item_id']);

        if (!$aRow) {
            return false;
        }

        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'],
            setting('notification.total_notification_title_length'), '...');

        if ($sTitle) {
            $sPhrase = _p('your_video_title_is_ready', ['title' => $sTitle]);
        } else {
            $sPhrase = _p('video_is_ready');
        }

        return [
            'link'        => $aRow['link'],
            'message'     => $sPhrase,
            'custom_icon' => 'fa-video-camera'
        ];
    }

    /**
     * @return array
     */
    public function getPagePerms()
    {
        $aPerms = [
            'pf_video.share_videos'       => _p('who_can_share_videos'),
            'pf_video.view_browse_videos' => _p('who_can_view_videos')
        ];

        return $aPerms;
    }

    /**
     * @return array
     */
    public function getGroupPerms()
    {
        $aPerms = [
            'pf_video.share_videos' => _p('who_can_share_videos')
        ];

        return $aPerms;
    }

    /**
     * @param $aPage
     *
     * @return array|null
     */
    public function getPageMenu($aPage)
    {
        if (!Phpfox::getUserParam('v.pf_video_view')) {
            return null;
        }

        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'pf_video.view_browse_videos')) {
            return null;
        }

        $aMenus[] = [
            'phrase'  => _p('Videos'),
            'url'     => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']) . 'video/',
            'icon'    => 'module/video.png',
            'landing' => 'video'
        ];

        return $aMenus;
    }

    /**
     * @param $aPage
     *
     * @return array|null
     * @throws \Exception
     */
    public function getPageSubMenu($aPage)
    {
        if (!user('pf_video_share', '1')) {
            return null;
        }
        if (!Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'pf_video.share_videos')) {
            return null;
        }

        return [
            [
                'phrase' => _p('share_a_video'),
                'url'    => Phpfox::getLib('url')->makeUrl('video.share', [
                    'module' => 'pages',
                    'item'   => $aPage['page_id']
                ])
            ]
        ];
    }

    /**
     * @param $aPage
     *
     * @return array|null
     */
    public function getGroupMenu($aPage)
    {
        if (!Phpfox::getUserParam('v.pf_video_view')) {
            return null;
        }

        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'], 'pf_video.view_browse_videos')) {
            return null;
        }

        $aMenus[] = [
            'phrase'  => _p('Videos'),
            'url'     => Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']) . 'video/',
            'icon'    => 'module/video.png',
            'landing' => 'video'
        ];

        return $aMenus;
    }

    /**
     * @param $aPage
     *
     * @return array|null
     * @throws \Exception
     */
    public function getGroupSubMenu($aPage)
    {
        if (!user('pf_video_share', '1')) {
            return null;
        }
        if (!Phpfox::getService('groups')->hasPerm($aPage['page_id'], 'pf_video.share_videos')) {
            return null;
        }

        return [
            [
                'phrase' => _p('share_a_video'),
                'url'    => Phpfox::getLib('url')->makeUrl('video.share', [
                    'module' => 'groups',
                    'item'   => $aPage['page_id']
                ])
            ]
        ];
    }

    /**
     * @return array
     */
    public function getActivityPointField()
    {
        return [
            _p('Videos') => 'activity_video'
        ];
    }

    /**
     * @return array
     */
    public function pendingApproval()
    {
        return [
            'phrase' => _p('Videos'),
            'value'  => Phpfox::getService('v.video')->getPendingTotal(),
            'link'   => Phpfox::getLib('url')->makeUrl('video', ['view' => 'pending'])
        ];
    }

    public function getAdmincpAlertItems()
    {
        $iTotalPending = Phpfox::getService('v.video')->getPendingTotal();
        return [
            'target'  => '_blank',
            'message' => _p('you_have_total_pending_videos', ['total' => $iTotalPending]),
            'value'   => $iTotalPending,
            'link'    => Phpfox_Url::instance()->makeUrl('video', ['view' => 'pending'])
        ];
    }

    /**
     * @return array
     */
    public function getGlobalPrivacySettings()
    {
        return [
            'v.default_privacy_setting' => [
                'phrase' => _p('Videos')
            ]
        ];
    }

    /**
     * @param string $sQuery
     * @param bool   $bIsTagSearch
     *
     * @return array|null
     */
    public function globalSearch($sQuery, $bIsTagSearch = false)
    {
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_globalsearch__start')) ? eval($sPlugin) : false);
        $sCondition = 'v.in_process = 0 AND v.view_id = 0 AND v.privacy = 0 AND v.item_id = 0';
        if ($bIsTagSearch == false) {
            $sCondition .= ' AND (v.title LIKE \'%' . db()->escape($sQuery) . '%\' OR vt.text LIKE \'%' . db()->escape($sQuery) . '%\')';
        }

        $iCnt = db()->select('COUNT(*)')
            ->from($this->_sTable, 'v')
            ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->where($sCondition)
            ->execute('getSlaveField');

        $aRows = db()->select('v.title, v.time_stamp, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where($sCondition)
            ->limit(10)
            ->order('v.time_stamp DESC')
            ->execute('getSlaveRows');

        if (count($aRows)) {
            $aResults = [];
            $aResults['total'] = $iCnt;
            $aResults['menu'] = _p('Videos');

            if ($bIsTagSearch == true) {
                $aResults['form'] = '<div><input type="button" value="' . _p('view_more_videos') . '" class="search_button" onclick="window.location.href = \'' . Phpfox::getLib('url')->makeUrl('video',
                        ['tag', $sQuery]) . '\';" /></div>';
            } else {
                $aResults['form'] = '<form method="post" action="' . Phpfox::getLib('url')->makeUrl('video') . '"><div><input type="hidden" name="' . Phpfox::getTokenName() . '[security_token]" value="' . Phpfox::getService('log.session')->getToken() . '" /></div><div><input name="search[search]" value="' . Phpfox::getLib('parse.output')->clean($sQuery) . '" size="20" type="hidden" /></div><div><input type="submit" name="search[submit]" value="' . _p('view_more_videos') . '" class="search_button" /></div></form>';
            }
            foreach ($aRows as $iKey => $aRow) {
                $aResults['results'][$iKey] = [
                    'title'      => $aRow['title'],
                    'link'       => Phpfox::getLib('url')->makeUrl($aRow['user_name'], ['video', $aRow['title']]),
                    'image'      => Phpfox::getLib('image.helper')->display([
                            'server_id'  => $aRow['server_id'],
                            'title'      => $aRow['full_name'],
                            'path'       => 'core.url_user',
                            'file'       => $aRow['user_image'],
                            'suffix'     => '_500',
                            'max_width'  => 75,
                            'max_height' => 75
                        ]
                    ),
                    'extra_info' => _p('video_created_on_time_stamp_by_full_name', [
                            'link'       => Phpfox_Url::instance()->makeUrl('video'),
                            'time_stamp' => Phpfox::getTime(setting('core.global_update_time'),
                                $aRow['time_stamp']),
                            'user_link'  => Phpfox_Url::instance()->makeUrl($aRow['user_name']),
                            'full_name'  => $aRow['full_name']
                        ]
                    )
                ];
            }
            (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_globalsearch__return')) ? eval($sPlugin) : false);

            return $aResults;
        }
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_globalsearch__end')) ? eval($sPlugin) : false);

        return null;
    }

    /**
     * @param string $sSearch
     */
    public function globalUnionSearch($sSearch)
    {
        db()->select('v.video_id AS item_id, v.title AS item_title, v.time_stamp AS item_time_stamp, v.user_id AS item_user_id, \'v\' AS item_type_id, v.image_path AS item_photo, v.image_server_id AS item_photo_server')
            ->from(Phpfox::getT('video'), 'v')
            ->where(db()->searchKeywords('v.title',
                    $sSearch) . ' AND v.in_process = 0 AND v.view_id = 0 AND v.privacy = 0')
            ->union();
    }

    /**
     * @param array $aRow
     *
     * @return array
     */
    public function getSearchInfo($aRow)
    {
        $aRow['image_path'] = $aRow['item_photo'];
        $aRow['image_server_id'] = $aRow['item_photo_server'];
        Phpfox::getService('v.video')->convertImagePath($aRow);
        $aInfo = [];
        $aInfo['item_link'] = Phpfox::getLib('url')->permalink('video.play', $aRow['item_id'], $aRow['item_title']);
        $aInfo['item_name'] = _p('Videos');
        $aInfo['item_display_photo'] = '<img src="' . $aRow['image_path'] . '" class="image_deferred  built has_image">';

        return $aInfo;
    }

    /**
     * @return array
     */
    public function getSearchTitleInfo()
    {
        return [
            'name' => _p('Videos')
        ];
    }

    /**
     * @return array
     */
    public function updateCounterList()
    {
        $aList[] = [
            'name' => _p('users_video_count'),
            'id'   => 'video-total'
        ];

        return $aList;
    }

    /**
     * @param int $iId
     * @param int $iPage
     * @param int $iPageLimit
     *
     * @return int
     */
    public function updateCounter($iId, $iPage, $iPageLimit)
    {
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_updatecounter__start')) ? eval($sPlugin) : false);
        $iCnt = 0;
        if ($iId == 'video-total') {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('user'))
                ->execute('getSlaveField');

            $aRows = db()->select('u.user_id, u.user_name, u.full_name, COUNT(v.video_id) AS total_items')
                ->from(Phpfox::getT('user'), 'u')
                ->leftJoin($this->_sTable, 'v',
                    'v.module_id = \'video\' AND v.user_id = u.user_id AND v.view_id = 0 AND v.in_process = 0')
                ->limit($iPage, $iPageLimit, $iCnt)
                ->group('u.user_id')
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                db()->update(Phpfox::getT('user_field'), ['total_video' => $aRow['total_items']],
                    'user_id = ' . $aRow['user_id']);
            }
        }

        return $iCnt;
    }

    /**
     * @param $iId
     *
     * @return array|int|string
     * @throws \Exception
     */
    public function getCommentItem($iId)
    {
        $aRow = db()->select('video_id AS comment_item_id, privacy_comment, user_id AS comment_user_id')
            ->from($this->_sTable)
            ->where('video_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aRow['comment_view_id'] = '0';

        if (!Phpfox::getService('comment')->canPostComment($aRow['comment_user_id'], $aRow['privacy_comment'])) {
            Phpfox_Error::set(_p('unable_to_post_a_comment_on_this_item_due_to_privacy_settings'));

            unset($aRow['comment_item_id']);
        }

        return $aRow;
    }

    /**
     * @param      $aVals
     * @param null $iUserId
     * @param null $sUserName
     */
    public function addComment($aVals, $iUserId = null, $sUserName = null)
    {
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_addcomment__start')) ? eval($sPlugin) : false);

        $aVideo = Phpfox::getService('v.video')->getInfoForNotification($aVals['item_id']);

        if ($iUserId === null || empty($aVideo['video_id'])) {
            $iUserId = Phpfox::getUserId();
        }

        if ($aVideo['profile_page_id'] && in_array($aVideo['module_id'], ['pages', 'groups'])) {
            $aPage = Phpfox::getService($aVideo['module_id'])->getPage($aVideo['profile_page_id']);
            $aVideo['user_id'] = $aPage['user_id'];
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add($aVals['type'] . '_comment',
            $aVals['comment_id'], 0, 0, 0, $iUserId) : null);
        // Update the post counter if its not a comment put under moderation or if the person posting the comment is the owner of the item.
        if (empty($aVals['parent_id'])) {
            db()->updateCount('comment', 'type_id = \'v\' AND item_id = ' . (int)$aVals['item_id'], 'total_comment',
                'video', 'video_id = ' . (int)$aVals['item_id']);
        }
        $aChecked = [];
        $aMatches = Phpfox::getService('user.process')->getIdFromMentions($aVideo['status_info'], true);
        $aTaggedByWith = Phpfox::getService('feed.tag')->getTaggedUserIds($aVideo['video_id'], 'v');

        Phpfox::getService('feed.tag')->filterTaggedPrivacy($aMatches, $aTaggedByWith, $aVideo['video_id'], 'v');
        $aMatches = array_merge($aMatches, $aTaggedByWith);

        foreach ($aMatches as $iKey => $iUserId) {
            if (in_array($iUserId, $aChecked) || empty($iUserId)) {
                continue;
            }
            $aChecked[] = $iUserId;
        }
        $aSubject = (Phpfox::getUserId() == $aVideo['user_id'] ? [
            'full_name_commented_on_gender_video',
            [
                'full_name' => Phpfox::getUserBy('full_name'),
                'gender'    => Phpfox::getService('user')->gender($aVideo['gender'], 1)
            ]
        ] : [
            'full_name_commented_on_video_full_name_s_video',
            ['full_name' => Phpfox::getUserBy('full_name'), 'video_full_name' => $aVideo['full_name']]
        ]);
        $aMessage = (Phpfox::getUserId() == $aVideo['user_id'] ? [
            'full_name_commented_on_gender_video_message',
            [
                'full_name' => Phpfox::getUserBy('full_name'),
                'gender'    => Phpfox::getService('user')->gender($aVideo['gender'], 1),
                'link'      => $aVideo['link'],
                'title'     => $aVideo['title']
            ]
        ] : [
            'full_name_commented_on_video_full_name_s_video_message', [
                'full_name'       => Phpfox::getUserBy('full_name'),
                'video_full_name' => $aVideo['full_name'],
                'link'            => $aVideo['link'],
                'title'           => $aVideo['title']
            ]
        ]);
        $aExecutedUsers = [];
        foreach ($aChecked as $iUser) {
            Phpfox::getLib('mail')->to($iUser)
                ->subject($aSubject)
                ->message($aMessage)
                ->notification('comment.add_new_comment')
                ->send();
            if (Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('comment_v', $aVideo['video_id'],
                    $iUser);
            }
            $aExecutedUsers[] = $iUser;
        }
        // Send the user an email
        Phpfox::getService('comment.process')->notify([
                'user_id'            => $aVideo['user_id'],
                'item_id'            => $aVideo['video_id'],
                'owner_subject'      => [
                    'full_name_commented_on_your_video_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aVideo['title']]
                ],
                'owner_message'      => [
                    'full_name_commented_on_your_video_message',
                    [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'link'      => $aVideo['link'],
                        'title'     => $aVideo['title']
                    ]
                ],
                'owner_notification' => 'comment.add_new_comment',
                'notify_id'          => 'comment_v',
                'mass_id'            => 'v',
                'mass_subject'       => $aSubject,
                'mass_message'       => $aMessage,
                'exclude_users'      => $aExecutedUsers,
            ]
        );

        if ($aVideo['module_id'] == 'user') {
            // Send the user an email
            Phpfox::getService('comment.process')->notify([
                    'user_id'            => $aVideo['item_id'],
                    'item_id'            => $aVideo['video_id'],
                    'owner_subject'      => [
                        'full_name_commented_on_your_video_title',
                        ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aVideo['title']]
                    ],
                    'owner_message'      => [
                        'full_name_commented_on_your_video_message',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'link'      => $aVideo['link'],
                            'title'     => $aVideo['title']
                        ]
                    ],
                    'owner_notification' => 'comment.add_new_comment',
                    'notify_id'          => 'comment_v',
                    'mass_id'            => 'v',
                    'mass_subject'       => (Phpfox::getUserId() == $aVideo['item_id'] ? [
                        'full_name_commented_on_gender_video',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aVideo['gender'], 1)
                        ]
                    ] : [
                        'full_name_commented_on_video_full_name_s_video',
                        [
                            'full_name'       => Phpfox::getUserBy('full_name'),
                            'video_full_name' => $aVideo['full_name']
                        ]
                    ]),
                    'mass_message'       => (Phpfox::getUserId() == $aVideo['item_id'] ? [
                        'full_name_commented_on_gender_video_message',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'gender'    => Phpfox::getService('user')->gender($aVideo['gender'], 1),
                            'link'      => $aVideo['link'],
                            'title'     => $aVideo['title']
                        ]
                    ] : [
                        'full_name_commented_on_video_full_name_s_video_message', [
                            'full_name'       => Phpfox::getUserBy('full_name'),
                            'video_full_name' => $aVideo['full_name'],
                            'link'            => $aVideo['link'],
                            'title'           => $aVideo['title']
                        ]
                    ]),
                    'exclude_users'      => $aExecutedUsers,
                ]
            );
        }

        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_addcomment__end')) ? eval($sPlugin) : false);
    }

    /**
     * @param $iId
     *
     * @throws \Exception
     */
    public function deleteComment($iId)
    {
        user('pf_video_comment', 1, null, true);
        db()->updateCounter('video', 'total_comment', 'video_id', $iId, true);
    }

    /**
     * @param int $iId
     *
     * @return bool|string
     */
    public function getRedirectComment($iId)
    {
        return $this->getFeedRedirect($iId);
    }

    /**
     * @param $iId
     *
     * @return bool|string
     */
    public function getFeedRedirect($iId)
    {
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getfeedredirect__start')) ? eval($sPlugin) : false);

        $aVideo = Phpfox::getService('v.video')->getInfoForNotification($iId);

        if (!isset($aVideo['video_id'])) {
            return false;
        }

        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_getfeedredirect__end')) ? eval($sPlugin) : false);

        return Phpfox::permalink('video.play', $aVideo['video_id'], $aVideo['title']);
    }

    /**
     * @param int $iId
     *
     * @return bool|string
     */
    public function getReportRedirect($iId)
    {
        return $this->getFeedRedirect($iId);
    }

    /**
     * @return string
     */
    public function getCommentItemName()
    {
        return 'video';
    }

    public function getItemName($iId, $sName)
    {
        return _p('a_href_link_on_name_s_video_a',
            ['link' => Phpfox::getLib('url')->makeUrl('comment.view', ['id' => $iId]), 'name' => $sName]);
    }

    /**
     * @param $aUser
     *
     * @return array|bool
     */
    public function getProfileMenu($aUser)
    {
        if (!Phpfox::getUserParam('v.pf_video_view')) {
            return [];
        }
        if (!setting('profile.show_empty_tabs')) {
            if (!isset($aUser['total_video'])) {
                return false;
            }

            if (isset($aUser['total_video']) && (int)$aUser['total_video'] === 0) {
                return false;
            }
        }
        $aSubMenu = [];

        $aMenus[] = [
            'phrase'   => _p('Videos'),
            'url'      => 'profile.video',
            'total'    => (int)(isset($aUser['total_video']) ? $aUser['total_video'] : 0),
            'sub_menu' => $aSubMenu,
            'icon'     => 'feed/video.png'
        ];

        return $aMenus;
    }

    /**
     * @param int      $iId
     * @param null|int $iUserId
     */
    public function addTrack($iId, $iUserId = null)
    {
        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_addtrack__start')) ? eval($sPlugin) : false);

        if ($iUserId == null) {
            $iUserId = Phpfox::getUserId();
        }
        $this->database()->insert(Phpfox::getT('track'), [
            'type_id'    => 'v',
            'item_id'    => (int)$iId,
            'ip_address' => db()->escape(Phpfox::getIp(true)),
            'user_id'    => $iUserId,
            'time_stamp' => PHPFOX_TIME
        ]);

        (($sPlugin = Phpfox_Plugin::get('video.component_service_callback_addtrack__end')) ? eval($sPlugin) : false);
    }

    /**
     * @param int $iUserId
     *
     * @return array
     */
    public function getTotalItemCount($iUserId)
    {
        return [
            'field' => 'total_video',
            'total' => $this->database()->select('COUNT(*)')->from(Phpfox::getT('video'))
                ->where('user_id = ' . (int)$iUserId . ' AND module_id = \'video\' AND view_id = 0 AND in_process = 0')
                ->execute('getSlaveField')
        ];
    }

    /**
     * @return string
     */
    public function getProfileLink()
    {
        return 'profile.v';
    }

    /**
     * @return string
     */
    public function getAjaxCommentVar()
    {
        return 'pf_video_comment';
    }

    /**
     * @param $aNotification
     *
     * @return array
     */
    public function getNotificationConverted($aNotification)
    {
        return [
            'link'        => Phpfox::getLib('url')->makeUrl('video'),
            'message'     => _p("all_old_videos_feed_video_converted_new_videos"),
            'custom_icon' => 'fa-video-camera'
        ];

    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationNewItem_Groups($aNotification)
    {
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            return false;
        }
        $aItem = Phpfox::getService('v.video')->getInfoForNotification($aNotification['item_id']);
        if (empty($aItem) || empty($aItem['item_id']) || $aItem['module_id'] != 'groups') {
            return false;
        }
        $aGroup = Phpfox::getService('groups')->getPage($aItem['item_id']);
        if (!isset($aGroup['page_id'])) {
            return false;
        }

        if (!empty($aGroup['user_id']) && $aGroup['user_id'] == Phpfox::getUserId()) {
            // notification of owner
            $sPhrase = _p('users_added_a_video_on_your_group_title', [
                'users' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => Phpfox::getLib('parse.output')->shorten($aGroup['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        } else {
            // notification of admin
            $sPhrase = _p('users_added_a_video_in_the_group_title', [
                'users' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => Phpfox::getLib('parse.output')->shorten($aGroup['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        }

        return [
            'link'    => $aItem['link'],
            'message' => $sPhrase,
            'icon'    => 'feed/video.png'
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationNewItem_Pages($aNotification)
    {
        if (!Phpfox::isAppActive('Core_Pages')) {
            return false;
        }
        $aItem = Phpfox::getService('v.video')->getInfoForNotification($aNotification['item_id']);
        if (empty($aItem) || empty($aItem['item_id']) || $aItem['module_id'] != 'pages') {
            return false;
        }
        $aPage = Phpfox::getService('pages')->getPage($aItem['item_id']);
        if (!isset($aPage['page_id'])) {
            return false;
        }

        if (!empty($aPage['user_id']) && $aPage['user_id'] == Phpfox::getUserId()) {
            // notification of owner
            $sPhrase = _p('users_added_a_video_on_your_page_title', [
                'users' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => Phpfox::getLib('parse.output')->shorten($aPage['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        } else {
            // notification of admin
            $sPhrase = _p('users_added_a_video_in_the_page_title', [
                'users' => Phpfox::getService('notification')->getUsers($aNotification),
                'title' => Phpfox::getLib('parse.output')->shorten($aPage['title'],
                    Phpfox::getParam('notification.total_notification_title_length'), '...')
            ]);
        }

        return [
            'link'    => $aItem['link'],
            'message' => $sPhrase,
            'icon'    => 'feed/video.png'
        ];
    }

    /**
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationNewItem_Wall($aNotification)
    {
        $aItem = Phpfox::getService('v.video')->getInfoForNotification($aNotification['item_id']);
        if (empty($aItem) || empty($aItem['item_id']) || $aItem['module_id'] != 'user') {
            return false;
        }
        $aRow = Phpfox::getService('user')->getUser($aItem['item_id']);
        if (!isset($aRow['user_id'])) {
            return false;
        }
        $sPhrase = _p('users_posted_a_video_title_on_in_your_wall', [
            'users' => Phpfox::getService('notification')->getUsers($aNotification),
            'title' => Phpfox::getLib('parse.output')->shorten($aItem['title'],
                Phpfox::getParam('notification.total_notification_title_length'), '...')
        ]);

        return [
            'link'    => $aItem['link'],
            'message' => $sPhrase,
            'icon'    => 'feed/video.png'
        ];
    }

    /**
     * Action to take when user cancelled their account
     *
     * @param int $iUser
     *
     * @return void
     * @throws \Exception
     */
    public function onDeleteUser($iUser)
    {
        $aVideos = db()
            ->select('video_id')
            ->from($this->_sTable)
            ->where('(user_id = ' . (int)$iUser . ' OR page_user_id = ' . (int)$iUser . ' OR (module_id = \'user\' AND item_id = ' . (int)$iUser . '))')
            ->execute('getSlaveRows');
        if (count($aVideos)) {
            foreach ($aVideos as $aVideo) {
                Phpfox::getService('v.process')->delete($aVideo['video_id'], '', 0, true, true);
            }
        }
    }

    /**
     * Remove is_sponsor if sponsor item in ad is removed
     *
     * @param array $aSponsor
     */
    public function deleteSponsorItem($aSponsor)
    {
        Phpfox::getLib('database')->update(':video', ['is_sponsor' => 0], 'video_id=' . (int)$aSponsor['item_id']);
        $this->cache()->remove('video_sponsored');
    }

    public function getCommentNotificationTag($aNotification)
    {
        $aRow = $this->database()
            ->select('v.video_id, v.title, u.user_name, u.full_name')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('video'), 'v', 'v.video_id = c.item_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.comment_id = ' . (int)$aNotification['item_id'])
            ->execute('getSlaveRow');

        $sPhrase = _p('user_name_tagged_you_in_a_comment_in_a_video', ['user_name' => $aRow['full_name']]);

        return [
            'link'    => Phpfox_Url::instance()
                    ->permalink('video.play', $aRow['video_id'],
                        $aRow['title']) . 'comment_' . $aNotification['item_id'],
            'message' => $sPhrase,
            'icon'    => \Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * This callback will be called when a page or group be deleted
     *
     * @param $iId
     * @param $sType
     *
     * @throws \Exception
     */
    public function onDeletePage($iId, $sType)
    {
        $aVideos = db()->select('video_id')->from(':video')->where([
            'module_id' => $sType,
            'item_id'   => $iId
        ])->executeRows();
        foreach ($aVideos as $aVideo) {
            Phpfox::getService('v.process')->delete($aVideo['video_id'], '', 0, true, true);
        }
    }

    /**
     * @return array
     */
    public function getUploadParams()
    {
        return Phpfox::getService('v.video')->getUploadVideoParams();
    }

    /**
     * @return array
     */
    public function getUploadParamsEdit_Video()
    {
        return Phpfox::getService('v.video')->getUploadPhotoParams();
    }

    /**
     * Get statistic for each user
     *
     * @param $iUserId
     *
     * @return array|bool
     */
    public function getUserStatsForAdmin($iUserId)
    {
        if (!$iUserId) {
            return false;
        }

        $iTotalVideos = db()->select('COUNT(*)')
            ->from(':video')
            ->where('user_id =' . (int)$iUserId)
            ->executeField();

        return [
            'total_name'  => _p('videos'),
            'total_value' => $iTotalVideos,
            'type'        => 'item'
        ];
    }

    public function getActivityFeedComment($aRow)
    {
        if (Phpfox::isUser()) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aItem = $this->database()->select('v.video_id, v.title, v.module_id, v.item_id, v.time_stamp, v.total_comment, v.total_like, c.total_like, ct.text_parsed AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('video'), 'v', 'c.type_id = \'v\' AND c.item_id = v.video_id AND c.view_id = 0')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('c.comment_id = ' . (int)$aRow['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aItem['video_id'])) {
            return false;
        }
        
        if ($aItem['module_id'] == 'groups' && !Phpfox::getService('groups')->isMember($aItem['item_id'])) {
            return false;
        }

        $sLink = Phpfox::permalink('video.play', $aItem['video_id'], $aItem['title']);
        $sTitle = Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('parse.output')->clean($aItem['title']),
            (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : 50));
        $sUser = '<a href="' . Phpfox_Url::instance()->makeUrl($aItem['user_name']) . '">' . $aItem['full_name'] . '</a>';
        $sGender = Phpfox::getService('user')->gender($aItem['gender'], 1);

        if ($aRow['user_id'] == $aItem['user_id']) {
            $sMessage = _p('posted_a_comment_on_gender_video_a_href_link_title_a',
                ['gender' => $sGender, 'link' => $sLink, 'title' => $sTitle]);
        } else {
            $sMessage = _p('posted_a_comment_user_video',
                ['user_name' => $sUser, 'link' => $sLink, 'title' => $sTitle]);
        }

        return [
            'no_share'        => true,
            'feed_info'       => $sMessage,
            'feed_link'       => $sLink,
            'feed_status'     => $aItem['text'],
            'feed_total_like' => $aItem['total_like'],
            'feed_is_liked'   => isset($aItem['is_liked']) ? $aItem['is_liked'] : false,
            'feed_icon'       => Phpfox::getLib('image.helper')->display([
                'theme'      => 'module/video.png',
                'return_url' => true
            ]),
            'time_stamp'      => $aRow['time_stamp'],
            'like_type_id'    => 'feed_mini'
        ];
    }

    public function getNotificationSettings()
    {
        return [
            'v.email_notification' => [
                'phrase' => _p('videos_notifications'),
                'default' => 1
            ]
        ];
    }

    public function addScheduleItemToFeed($aVals) {
        $iId = Phpfox::getService('v.process')->addVideo($aVals);
        if (!$iId) {
            return false;
        }
        if (!empty($aVals['path'])) {
            //Only send notification if video is File Upload
            if (Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('v_ready', $iId, $aVals['user_id'], $aVals['user_id'], true);
            }

            $sTitle = (!empty($aVals['title']) ? Phpfox::getLib('parse.output')->clean($aVals['title'], 255) : _p('untitled_video'));
            Phpfox::getLib('mail')->to($aVals['user_id'])
                ->subject(['email_your_video_title_is_ready', ['title' => $sTitle]])
                ->message(['your_video_title_is_ready_click_on_link', ['title' => $sTitle, 'link' => Phpfox::permalink('video.play', $iId, $sTitle)]])
                ->notification('v.email_notification')
                ->send();
        }
        return true;
    }

    public function getAdditionalScheduleInfo($aRow) {
        $aInfo = [];
        $data = $aRow['data'];

        $aInfo['item_title'] = $data['status_info'];
        $aInfo['item_name'] = _p('video');
        if (isset($data['default_image'])) {
            if (isset($data['image_server_id'])) {
                //S3 and Mux services
                if ($data['image_server_id'] == -1) {
                    $images = (trim(setting('pf_video_s3_url'), '/') . '/') . $data['default_image'];
                } else {
                    $images = 'https://image.mux.com/' . $data['default_image'];
                }
            } else {
                $images = $data['default_image'];
            }
        } elseif (!empty($data['image_path'])) {
            $images = Phpfox::getLib('image.helper')->display([
                'server_id'  => $data['image_server_id'],
                'path'       => 'core.url_pic',
                'file'       => $data['image_path'],
                'suffix'     => '_500',
                'return_url' => true
            ]);
        } else {
            $images = Phpfox::getParam('video.default_video_photo');
        }
        $aInfo['item_images'] = [
            'remaining' => 0,
            'images' => [$images]
        ];

        return $aInfo;
    }

    public function getAdditionalEditBlock() {
        return 'v.block.edit-video-schedule';
    }

    public function getExtraScheduleData($aItem) {
        $aData = $aItem['data'];
        $iMethodUpload = setting('pf_video_method_upload');
        $bAllowVideoUploading = false;
        if (setting('pf_video_support_upload_video')
            && (setting('pf_video_allow_compile_on_storage_system')
                || ($iMethodUpload == 1 && setting('pf_video_key'))
                || ($iMethodUpload == 0 && setting('pf_video_ffmpeg_path'))
                || ($iMethodUpload == 2 && setting('pf_video_mux_token_id') && setting('pf_video_mux_token_secret'))
            )) {
            $bAllowVideoUploading = true;
        }

        $bIsUrlVid = !empty($aData['url']);
        $bUploadS3 = !$bIsUrlVid && isset($aData['image_server_id']) && in_array($aData['image_server_id'], [-1, -3]);
        $aItem['extra'] = [
            'bAllowVideoUploading' => $bAllowVideoUploading,
            'bIsUrlVid' => $bIsUrlVid,
            'iGroupId'  => isset($aData['group_id']) ? $aData['group_id'] : (isset($aData['feed_values']) ? $aData['feed_values']->group_id : ''),
        ];
        if ($bUploadS3) { //S3, Mux
            $aItem['extra']['sImageUrl'] = ($aData['image_server_id'] == -1 ? (trim(setting('pf_video_s3_url'), '/') . '/') : 'https://image.mux.com/') . $aData['default_image'];
        } elseif (!$bIsUrlVid) { //FFMPEG, External FFMPEG
            $aItem['extra']['aImage'] = [
                'server_id' => $aData['image_server_id'],
                'image_path' => substr($aData['image_path'], strpos($aData['image_path'], "/")+1),
            ];
        } else { //URL
            $aItem['extra']['sImageUrl'] = $aData['default_image'];
        }
        $aItem['data']['user_status'] = $aData['status_info'];
        if (!empty($aData['feed_values'])) {
            $aFeedValues = (array)$aData['feed_values'];
            $aLocation = (array)$aFeedValues['location'];
        } else {
            $aLocation = $aData['location'];
        }
        if(!empty($aLocation['latlng'])) {
            $aLatLng = explode(',', $aLocation['latlng']);
            $aItem['data']['location_latlng'] = [];
            $aItem['data']['location_latlng']['latitude'] = $aLatLng[0];
            $aItem['data']['location_latlng']['longitude'] = $aLatLng[1];
        }
        if(!empty($aLocation['name'])) {
            $aItem['data']['location_name'] = $aLocation['name'];
        }

        return $aItem;
    }

    public function onUpdateScheduleItem($data, $aVals) {
        $iScheduleId = (int)$aVals['schedule_id'];
        $data['status_info'] =  $aVals['user_status'];
        $data['tagged_friends'] =  $aVals['tagged_friends'];
        $data['privacy'] =  $aVals['privacy'];
        $data['privacy_list'] =  $aVals['privacy_list'];
        $data['location'] =  $aVals['location'];
        $data['location_name'] = (!empty($aVals['location']['name'])) ? Phpfox::getLib('parse.input')->clean($aVals['location']['name']) : null;
        if ((!empty($aVals['location']['latlng']))) {
            $aMatch = explode(',', $aVals['location']['latlng']);
            $aMatch['latitude'] = floatval($aMatch[0]);
            $aMatch['longitude'] = floatval($aMatch[1]);
            $data['location_latlng'] = json_encode([
                'latitude' => $aMatch['latitude'],
                'longitude' => $aMatch['longitude']
            ]);
        } else {
            $data['location_latlng'] = null;
        }
        $aVals['status_info'] = $aVals['user_status'];
        if (!empty($aVals['change_video'])) {
            if (isset($aVals['pf_video_id'])) { //handle case upload video
                if (empty($aVals['pf_video_id'])) {
                    Phpfox_Error::set(_p('we_could_not_find_a_video_there_please_try_again'));
                }
                $encoding = storage()->get('pf_video_' . $aVals['pf_video_id']);
                $iUserId = $data['user_id'];

                if (!empty($encoding->value->encoded)) { //video already encoded
                    $aVals = array_merge($aVals, [
                        'text'            => '',
                        'status_info'     => $aVals['status_info'],
                        'is_stream'       => 0,
                        'user_id'         => $iUserId,
                        'server_id'       => $encoding->value->server_id,
                        'path'            => $encoding->value->video_path,
                        'ext'             => $encoding->value->ext,
                        'default_image'   => isset($encoding->value->default_image) ? $encoding->value->default_image : '',
                        'image_path'      => isset($encoding->value->image_path) ? $encoding->value->image_path : '',
                        'image_server_id' => $encoding->value->image_server_id,
                        'duration'        => $encoding->value->duration,
                        'video_size'      => $encoding->value->video_size,
                        'photo_size'      => $encoding->value->photo_size,
                        'resolution_x'    => $encoding->value->resolution_x,
                        'resolution_y'    => $encoding->value->resolution_y,
                        'feed_values'     => $aVals
                    ]);

                    db()->update(':schedule', ['is_temp' => 0], ['schedule_id' => $iScheduleId]);

                    $file = PHPFOX_DIR_FILE . 'static/' . $encoding->value->id . '.' . $encoding->value->ext;
                    if (file_exists($file)) {
                        unlink($file);
                    }

                    storage()->del('pf_video_' . $aVals['pf_video_id']);
                } else { //video not yet encoded
                    db()->update(':schedule', ['is_temp' => 1], ['schedule_id' => $iScheduleId]);

                    if (Phpfox::getParam('v.pf_video_allow_compile_on_storage_system') && version_compare(Phpfox::getCurrentVersion(), '4.8.0', '>=')) {
                        $aStorageData = [
                            'is_ready'         => 1,
                            'encoding_id'      => '',
                            'id'               => $encoding->value->id,
                            'user_id'          => $iUserId,
                            'view_id'          => $encoding->value->view_id,
                            'path'             => $encoding->value->path,
                            'ext'              => $encoding->value->ext,
                            'privacy'          => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                            'privacy_list'     => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                            'callback_module'  => (isset($aVals['callback_module']) ? $aVals['callback_module'] : ''),
                            'callback_item_id' => (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : 0),
                            'parent_user_id'   => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                            'title'            => $aVals['title'],
                            'category'         => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                            'text'             => isset($aVals['text']) ? $aVals['text'] : '',
                            'status_info'      => $aVals['status_info'],
                            'feed_values'      => json_encode($aVals),
                            'tagged_friends'   => isset($aVals['tagged_friends']) ? $aVals['tagged_friends'] : null,
                            'is_scheduled'     => true,
                            'schedule_id'      => $iScheduleId,
                        ];
                        storage()->update('pf_video_' . $aVals['pf_video_id'], $aStorageData);
                    } elseif (setting('pf_video_method_upload') == 0 && setting('pf_video_ffmpeg_path')) {
                        $iJobId = \Phpfox_Queue::instance()->addJob('videos_ffmpeg_encode', []);
                        $aStorageData = [
                            'encoding_id'      => $iJobId,
                            'id'               => $encoding->value->id,
                            'user_id'          => $iUserId,
                            'view_id'          => $encoding->value->view_id,
                            'path'             => $encoding->value->path,
                            'ext'              => $encoding->value->ext,
                            'privacy'          => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                            'privacy_list'     => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                            'callback_module'  => (isset($aVals['callback_module']) ? $aVals['callback_module'] : ''),
                            'callback_item_id' => (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : 0),
                            'parent_user_id'   => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                            'title'            => $aVals['title'],
                            'category'         => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                            'text'             => isset($aVals['text']) ? $aVals['text'] : '',
                            'status_info'      => $aVals['status_info'],
                            'feed_values'      => json_encode($aVals),
                            'tagged_friends'   => isset($aVals['tagged_friends']) ? $aVals['tagged_friends'] : null,
                            'is_scheduled'     => true,
                            'schedule_id'      => $iScheduleId,
                        ];
                        storage()->set('pf_video_' . $iJobId, $aStorageData);
                        storage()->del('pf_video_' . $aVals['pf_video_id']);
                    } else {
                        $aStorageData = [
                            'privacy'          => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                            'privacy_list'     => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                            'callback_module'  => (isset($aVals['callback_module']) ? $aVals['callback_module'] : ''),
                            'callback_item_id' => (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : 0),
                            'parent_user_id'   => isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0,
                            'title'            => $aVals['title'],
                            'category'         => json_encode([]),
                            'text'             => '',
                            'status_info'      => $aVals['status_info'],
                            'updated_info'     => 1,
                            'user_id'          => $iUserId,
                            'feed_values'      => json_encode($aVals),
                            'tagged_friends'   => isset($aVals['tagged_friends']) ? $aVals['tagged_friends'] : null,
                            'is_scheduled'     => true,
                            'schedule_id'      => $iScheduleId,
                        ];
                        storage()->update('pf_video_' . $aVals['pf_video_id'], $aStorageData);
                    }
                }
                unset($data['url']);
                unset($data['embed_code']);
                unset($data['default_image']);
            } elseif (!empty($aVals['url'])) { //handle case upload video url
                if (isset($data['feed_values'])) {
                    $data['group_id'] = $data['feed_values']->group_id;
                    $data['action'] = $data['feed_values']->action;
                    $data['status_background_id'] = $data['feed_values']->status_background_id;
                    $data['location'] = [
                        'latlng' => $data['feed_values']->location->latlng,
                        'name'   => $data['feed_values']->location->name,
                    ];

                    $data['duration'] = null;
                    $data['resolution_x'] = null;
                    $data['resolution_y'] = null;

                    unset($data['callback_module']);
                    unset($data['callback_item_id']);
                    unset($data['privacy_list']);
                    unset($data['category']);
                    unset($data['is_stream']);
                    unset($data['view_id']);
                    unset($data['server_id']);
                    unset($data['path']);
                    unset($data['image_path']);
                    unset($data['ext']);
                    unset($data['image_server_id']);
                    unset($data['video_size']);
                    unset($data['photo_size']);
                    unset($data['feed_values']);
                }

                $sUrl = trim($aVals['url']);
                if (substr($sUrl, 0, 7) != 'http://' && substr($sUrl, 0, 8) != 'https://') {
                    Phpfox_Error::set(_p('please_provide_a_valid_url'));
                }
                if (preg_match('/dailymotion/', $sUrl) && substr($sUrl, 0, 8) == 'https://') {
                    $sUrl = str_replace('https', 'http', $sUrl);
                }
                if ($parsed = Phpfox::getService('link')->getLink($sUrl)) {
                    if (empty($parsed['embed_code'])) {
                        Phpfox_Error::set(_p('unable_to_load_a_video_to_embed'));
                    }
                    $embed_code = str_replace('http://player.vimeo.com/', 'https://player.vimeo.com/',
                        $parsed['embed_code']);
                    $data['url'] = $sUrl;
                    $data['title'] = $parsed['title'];
                    $data['text'] = str_replace("<br />", "\r\n", $parsed['description']);
                    $data['embed_code'] = $embed_code;
                    $data['default_image'] = $parsed['default_image'];
                    $data['duration'] = isset($parsed['duration']) ? $parsed['duration'] : 0;
                    $data['resolution_x'] = isset($parsed['width']) ? $parsed['width'] : null;
                    $data['resolution_y'] = isset($parsed['height']) ? $parsed['height'] : null;
                } else {
                    Phpfox_Error::set(_p('we_could_not_find_a_video_there_please_check_the_url_and_try_again'));
                }
            } else {
                return Phpfox_Error::set(_p('we_could_not_find_a_video_there_please_try_again'));
            }
        } else {
            $data['feed_values'] = $aVals;
        }
        return $data;
    }

    public function onDeleteScheduleItem($data) {
        if (empty($data['url'])) {
            if (!empty($data['image_path'])) {
                $iFileSize = 0;
                if ($data['image_server_id'] == -1) {
                    Phpfox::getService('v.process')->deleteThumbnailFromS3($data);
                } else {
                    $aSizes = ['_500', '_1024']; // Sizes now defined
                    if (strpos($data['image_path'], 'video/') !== 0) { // support V3 video
                        $aVideo['image_path'] = 'video/' . $data['image_path'];
                        $aSizes = ['_120'];
                    }
                    // Foreach size
                    foreach ($aSizes as $sSize) {
                        // Get the possible image
                        $sImage = Phpfox::getParam('core.dir_pic') . sprintf($data['image_path'], $sSize);
                        // if the image exists
                        if ($data['image_server_id'] == 0 && file_exists($sImage)) {
                            $iFileSize += filesize($sImage);
                        } else {
                            if ($data['image_server_id'] > 0) {
                                // Get the file size stored when the photo was uploaded
                                $sTempUrl = Phpfox::getLib('cdn')->getUrl(str_replace(Phpfox::getParam('core.dir_pic'),
                                    Phpfox::getParam('core.url_pic'), $sImage), $data['image_server_id']);
                                $aHeaders = get_headers($sTempUrl, true);
                                if (preg_match('/200 OK/i', $aHeaders[0])) {
                                    $iFileSize += (int)$aHeaders["Content-Length"];
                                }
                            }
                        }
                        // Do not check if filesize is greater than 0, or CDN file will not be deleted
                        Phpfox::getLib('file')->unlink($sImage);
                    }
                }

                if ($iFileSize > 0) {
                    Phpfox::getService('user.space')->update($data['user_id'], 'photo', $iFileSize, '-');
                }
            }

            // remove videos
            if (!empty($data['path'])) {
                $iFileSize = 0;
                if ($data['server_id'] == -1) {
                    $aHeaders = get_headers(setting('pf_video_s3_url') . $data['path'], true);
                    if (preg_match('/200 OK/i', $aHeaders[0])) {
                        $iFileSize += (int)$aHeaders["Content-Length"];
                    }
                    $sPath = str_replace('.mp4', '', $data['path']);
                    $oClient = new S3Client([
                        'region' => setting('pf_video_s3_region', 'us-east-2'),
                        'version' => 'latest',
                        'credentials' => [
                            'key' => setting('pf_video_s3_key'),
                            'secret' => setting('pf_video_s3_secret'),
                        ],
                    ]);
                    foreach (['.webm', '-low.mp4', '.ogg', '.mp4'] as $ext) {
                        $oClient->deleteObject([
                            'Bucket' => setting('pf_video_s3_bucket'),
                            'Key' => $sPath . $ext
                        ]);
                    }
                } else {
                    $sPathVideo = Phpfox::getParam('core.dir_file') . 'video/' . sprintf($data['path'], '');
                    if ($data['server_id'] == 0 && file_exists($sPathVideo)) {
                        $iFileSize += filesize($sPathVideo);
                    } else {
                        if ($data['server_id'] > 0) {
                            $sTempUrl = Phpfox::getLib('cdn')->getUrl(Phpfox::getParam('core.url_file') . 'video/' . sprintf($data['path'], ''), $data['server_id']);
                            $aHeaders = get_headers($sTempUrl, true);
                            if (preg_match('/200 OK/i', $aHeaders[0])) {
                                $iFileSize += (int)$aHeaders["Content-Length"];
                            }
                        }
                    }
                    Phpfox::getLib('file')->unlink($sPathVideo);
                }

                if ($iFileSize > 0) {
                    Phpfox::getService('user.space')->update($data['user_id'], 'video', $iFileSize, '-');
                }
            }
        }
    }
}
