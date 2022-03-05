<?php

namespace Apps\PHPfox_Videos\Service;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_File;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

class Video extends Phpfox_Service
{
    private $_aCallback = false;
    private $_aAllowedTypes;
    private $_aThumbnailSizes;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('video');
        $this->_aAllowedTypes = [
            '3gp',
            'aac',
            'ac3',
            'ec3',
            'flv',
            'm4f',
            'mov',
            'mj2',
            'mkv',
            'mp4',
            'mxf',
            'ogg',
            'ts',
            'webm',
            'wmv',
            'avi'
        ];
        $this->_aThumbnailSizes = [500, 1024];
    }

    public function checkLimitation($userId = null)
    {
        empty($userId) && $userId = Phpfox::getUserId();

        static $permissions = [];

        if (isset($permissions[$userId])) {
            return $permissions[$userId];
        }

        $limit = trim(Phpfox::getUserParam('v.pf_video_total_videos_upload'));

        if (!isset($limit) || (!is_numeric($limit) && $limit == '')) {
            $permissions[$userId] = true;
        } elseif (is_numeric($limit) && (int)$limit === 0) {
            $permissions[$userId] = false;
        } else {
            $totalUserVideos = (int)db()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where([
                    'user_id' => $userId,
                ])->executeField(false);

            $permissions[$userId] = $totalUserVideos < $limit;
        }

        return $permissions[$userId];
    }

    /**
     * @param $aCallback
     * @return $this
     */
    public function callback($aCallback)
    {
        $this->_aCallback = $aCallback;

        return $this;
    }

    public function getVideoMetaTags($metaName = null, $getValuableTags = true)
    {
        $metaTags = [
            'CacheControl' => Phpfox::getParam('v.pf_video_s3_cache_control_meta'),
            'ContentDisposition' => Phpfox::getParam('v.pf_video_s3_content_disposition_meta'),
            'ContentEncoding' => Phpfox::getParam('v.pf_video_s3_content_encoding_meta'),
            'ContentLanguage' => Phpfox::getParam('v.pf_video_s3_content_language_meta'),
            'Expires' => Phpfox::getParam('v.pf_video_s3_expires_meta'),
            'WebsiteRedirectLocation' => Phpfox::getParam('v.pf_video_s3_website_redirect_location_meta'),
        ];

        if ($getValuableTags) {
            foreach ($metaTags as $key => $metaTag) {
                if (!isset($metaTag) || $metaTag == '') {
                    unset($metaTags[$key]);
                }
            }
        }

        if (!empty($metaName)) {
            return isset($metaTags[$metaName]) ? $metaTags[$metaName] : false;
        }

        return $metaTags;
    }

    /**
     * Build sub menu
     */
    public function buildMenu()
    {
        $aFilterMenu = [];
        if (!defined('PHPFOX_IS_USER_PROFILE')) {
            $iMyVideoTotal = Phpfox::getService('v.video')->getMyVideoTotal();
            $aFilterMenu = [
                _p('all_videos') => '',
                _p('my_videos') . ($iMyVideoTotal ? ('<span class="my count-item">' . (($iMyVideoTotal > 99) ? '99+' : $iMyVideoTotal) . '</span>') : '') => 'my'
            ];

            if (!Phpfox::getParam('core.friends_only_community') && Phpfox::isModule('friend') && !Phpfox::getUserBy('profile_page_id')) {
                $aFilterMenu[_p('friends_videos')] = 'friend';
            }

            if (user('pf_video_approve', 0)) {
                $iPendingTotal = Phpfox::getService('v.video')->getPendingTotal();
                if ($iPendingTotal) {
                    $aFilterMenu[_p('pending_videos') . (('<span id="video_pending" class="pending count-item">' . (($iPendingTotal > 99) ? '99+' : $iPendingTotal) . '</span>'))] = 'pending';
                }
            }
        }
        Phpfox::getLib('template')->buildSectionMenu('video', $aFilterMenu);
    }

    /**
     * @param $sVideo
     * @param $bDetail
     * @return array|int|string
     * @throws \Exception
     */
    public function getVideo($sVideo, $bDetail = false)
    {
        if (Phpfox::isModule('track')) {
            if (Phpfox::getUserId()) {
                db()->select("track.item_id AS video_is_viewed, ")->leftJoin(Phpfox::getT('track'), 'track',
                    'track.item_id = v.video_id AND track.user_id = ' . Phpfox::getUserId() . ' AND track.type_id="v"');
            } else {
                db()->select("track.item_id AS video_is_viewed, ")->leftJoin(Phpfox::getT('track'), 'track',
                    'track.item_id = v.video_id AND track.ip_address = \'' . db()->escape(Phpfox::getIp(true)) . '\' AND track.type_id="v"');
            }
        }

        if (Phpfox::isModule('friend')) {
            db()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = v.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }

        if (Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'v\' AND l.item_id = v.video_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aVideo = db()->select('v.*,' . (setting('core.allow_html') ? 'vt.text_parsed' : 'vt.text') . ' AS text, u.user_name, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->where('v.video_id = ' . (int)$sVideo)
            ->execute('getSlaveRow');

        if (!isset($aVideo['video_id'])) {
            return false;
        }
        if (!isset($aVideo['video_is_viewed'])) {
            $aVideo['video_is_viewed'] = 0;
        }
        if (!isset($aVideo['is_friend'])) {
            $aVideo['is_friend'] = 0;
        }
        if ($aVideo['view_id'] != '0') {
            if ($aVideo['view_id'] == '2' && ($aVideo['user_id'] == Phpfox::getUserId() || user('pf_video_approve'))) {
            } else {
                //check is admin of pages of groups
                $bIsAdmin = false;
                if ($aVideo['module_id'] == 'pages') {
                    $bIsAdmin = Phpfox::getService('pages')->isAdmin($aVideo['item_id']);
                } elseif ($aVideo['module_id'] == 'groups') {
                    $bIsAdmin = Phpfox::getService('groups')->isAdmin($aVideo['item_id']);
                }
                if (!$bIsAdmin) {
                    return false;
                }
            }
        }
        $aVideo['embed_code'] = '';
        if ($aVideo['is_stream']) {
            $aEmbedVideo = $this->database()->select('video_url, embed_code')
                ->from(Phpfox::getT('video_embed'))
                ->where('video_id = ' . $aVideo['video_id'])
                ->execute('getslaveRow');
            $aVideo['embed_code'] = isset($aEmbedVideo['embed_code']) ? $aEmbedVideo['embed_code'] : '';
            $aVideo['video_url'] = isset($aEmbedVideo['video_url']) ? $aEmbedVideo['video_url'] : '';
        }

        if (!isset($aVideo['is_friend'])) {
            $aVideo['is_friend'] = 0;
        }
        $aVideo['sHtmlCategories'] = Phpfox::getService('v.category')->getHtmlCategoryString($aVideo['video_id']);

        $aVideo = $this->compileVideo($aVideo, 360, 1024, $bDetail);
        $this->getPermissions($aVideo);
        $aVideo['link'] = Phpfox::permalink('video.play', $aVideo['video_id'], $aVideo['title']);

        $aVideo['location_latlng'] = json_decode($aVideo['location_latlng'], true);

        (($sPlugin = Phpfox_Plugin::get('video.service_video_getvideo')) ? eval($sPlugin) : null);

        return $aVideo;
    }

    /**
     * @param $iItemId
     * @return array
     */
    public function getInfoForNotification($iItemId)
    {
        $aRow = db()->select('v.video_id, v.title, v.item_id, v.module_id, v.status_info, u.full_name, u.user_id, u.gender, u.user_name, u.profile_page_id')
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.video_id = ' . (int)$iItemId)
            ->executeRow();
        if ($aRow) {
            $aRow['link'] = Phpfox_Url::instance()->permalink('video.play', $aRow['video_id'], $aRow['title']);
        }

        return $aRow;
    }

    /**
     * Get feed/detail link for notification/mail
     * @param $iItemId
     * @return array
     */
    public function getFeedLink($iItemId)
    {
        $video = $this->getInfoForNotification($iItemId);
        if (!$video) {
            return [null, null];
        }
        $feed = (Phpfox::isModule('feed') ? Phpfox::getService('feed')->getParentFeedItem('v', $iItemId) : null);
        if (!$feed || (empty($feed['user_name']))) {
            $link = $video['link'];
        } else {
            if (!empty($feed['parent_user_id'])) {
                $link = Phpfox::getService('user')->getLink($feed['parent_user_id']) . '?feed=' . $feed['feed_id'];
            } else {
                $link = Phpfox::getLib('url')->makeUrl($feed['user_name'], ['feed' => $feed['feed_id']]);
            }
        }
        $title = Phpfox::getLib('parse.output')->clean($video['title']);
        return [$title, $link];
    }

    /**
     * @param $iId
     * @return array|int|string
     */
    public function getForEdit($iId)
    {
        $aVideo = db()->select('v.*, vt.text, u.user_name')
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->where('v.video_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        return $aVideo;
    }

    /**
     * @param $iLimit
     * @param int $iCacheTime
     * @return array|int|string
     */
    public function getSponsored($iLimit, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('video_sponsored');
        Phpfox::getLib('cache')->group('video', $sCacheId);
        if (($aRows = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sWhere = 's.module_id= "v" AND s.is_active = 1 AND v.is_sponsor = 1 AND v.in_process = 0 AND v.view_id = 0 AND s.module_id = \'v\'';

            if (setting('pf_video_display_video_created_in_group') || setting('pf_video_display_video_created_in_page')) {
                $aModules = ['video'];
                if (setting('pf_video_display_video_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                    $aModules[] = 'groups';
                }
                if (setting('pf_video_display_video_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                    $aModules[] = 'pages';
                }
                $sWhere .= ' AND v.module_id IN ("' . implode('","', $aModules) . '")';
            } else {
                $sWhere .= ' AND v.item_id = 0';
            }

            $aRows = db()->select('v.video_id')
                ->from($this->_sTable, 'v')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = v.video_id AND s.is_custom = 3')
                ->where($sWhere)
                ->executeRows();

            $aRows = array_column($aRows, 'video_id');
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $aRows);
            }
        }

        // empty
        if (empty($aRows)) {
            return [];
        }
        shuffle($aRows);
        $aVideoIds = array_slice($aRows, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));

        $aRows = db()->select(Phpfox::getUserField() . ', v.*, v.total_view as total_view_video, v.module_id as v_module_id, v.item_id as v_item_id, s.*')
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = v.video_id AND s.is_custom = 3')
            ->where('s.module_id= "v" AND v.video_id IN (' . implode(',', $aVideoIds) . ')')
            ->group('v.video_id')
            ->limit($iLimit)
            ->executeRows();

        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aRows = Phpfox::getService('ad')->filterSponsor($aRows);
        }

        foreach ($aRows as &$aRow) {
            $aRow['total_view'] = $aRow['total_view_video'];
            $aRow['module_id'] = $aRow['v_module_id'];
            $aRow['item_id'] = $aRow['v_item_id'];
            $aRow['link'] = Phpfox::getLib('url')->makeUrl('ad.sponsor', ['view' => $aRow['sponsor_id']]);
            Phpfox::getService('v.video')->convertImagePath($aRow);
            if (Phpfox::isAppActive('Core_BetterAds')) {
                Phpfox::getService('ad.process')->addSponsorViewsCount($aRow['sponsor_id'], 'v');
            }
            $aRow['duration'] = Phpfox::getService('v.video')->getDuration($aRow['duration']);
        }
        shuffle($aRows);

        return $aRows;
    }

    /**
     * @param $iLimit
     * @param int $iCacheTime
     * @return array|int|string
     */
    public function getFeatured($iLimit, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('video_featured');
        Phpfox::getLib('cache')->group('video', $sCacheId);
        if (($aRows = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sWhere = 'v.is_featured = 1 AND v.in_process = 0 AND v.view_id = 0';
            if (setting('pf_video_display_video_created_in_group') || setting('pf_video_display_video_created_in_page')) {
                $aModules = ['video'];
                if (setting('pf_video_display_video_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                    $aModules[] = 'groups';
                }
                if (setting('pf_video_display_video_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                    $aModules[] = 'pages';
                }
                $sWhere .= ' AND v.module_id IN ("' . implode('","', $aModules) . '")';
            } else {
                $sWhere .= ' AND v.item_id = 0';
            }
            $aRows = db()->select('v.video_id')
                ->from(Phpfox::getT('video'), 'v')
                ->where($sWhere)
                ->executeRows();
            $aRows = array_column($aRows, 'video_id');
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $aRows);
            }
        }
        // empty
        if (empty($aRows)) {
            return [];
        }

        shuffle($aRows);
        $aRows = array_slice($aRows, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aOut = db()->select('v.*, ' . Phpfox::getUserField())
            ->from(':video', 'v')
            ->leftJoin(':user', 'u', 'v.user_id = u.user_id')
            ->where('v.video_id IN (' . implode(',', $aRows) . ')')
            ->limit($iLimit)
            ->executeRows();

        foreach ($aOut as &$aVideo) {
            $aVideo['link'] = Phpfox::permalink('video.play', $aVideo['video_id'], $aVideo['title']);
            Phpfox::getService('v.video')->convertImagePath($aVideo);
            $aVideo['duration'] = Phpfox::getService('v.video')->getDuration($aVideo['duration']);
        }
        shuffle($aOut);

        return $aOut;
    }

    /**
     * @param $iVideoId
     * @param int $iLimit
     * @return array
     */
    public function getRelatedVideos($iVideoId, $iLimit = 4)
    {
        $sCategory = Phpfox::getService('v.category')->getStringCategoryByVideoId($iVideoId);
        if (empty($sCategory)) {
            return [];
        }
        $iLimit && db()->limit($iLimit);

        $sWhere = ' v.in_process = 0 AND v.view_id = 0';
        if (setting('pf_video_display_video_created_in_group') || setting('pf_video_display_video_created_in_page')) {
            $aModules = ['video'];
            if (setting('pf_video_display_video_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                $aModules[] = 'groups';
            }
            if (setting('pf_video_display_video_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                $aModules[] = 'pages';
            }
            $sWhere .= ' AND v.module_id IN ("' . implode('","', $aModules) . '")';
        } else {
            $sWhere .= ' AND v.item_id = 0';
        }
        $sWhere .= ' AND vcd.category_id IN (' . $sCategory . ')';

        $aRows = db()->select('DISTINCT v.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->join(Phpfox::getT('video_category_data'), 'vcd',
                'vcd.video_id = v.video_id AND v.video_id <> ' . $iVideoId)
            ->where($sWhere)
            ->execute('getSlaveRows');

        $aOut = [];
        shuffle($aRows);
        foreach ($aRows as &$aRow) {
            // convert duration to h:i:s
            $aRow['duration'] = Phpfox::getService('v.video')->getDuration($aRow['duration']);
            $aRow['link'] = Phpfox::permalink('video.play', $aRow['video_id'], $aRow['title']);
            Phpfox::getService('v.video')->convertImagePath($aRow);
            $aOut[] = $aRow;
        }

        return $aOut;
    }

    /**
     * @return array|int|string
     */
    public function getPendingTotal()
    {
        $sWhere = 'view_id = 2';
        $aModules = [];
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (!Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        $sWhere .= ' AND module_id NOT IN ("' . implode('","', $aModules) . '")';

        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    /**
     * @return array|int|string
     */
    public function getMyVideoTotal()
    {
        $sWhere = 'user_id = ' . Phpfox::getUserId();
        $aModules = ['user'];
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (!Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        $sWhere .= ' AND module_id NOT IN ("' . implode('","', $aModules) . '")';

        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    /**
     * Get all videos count
     * @return int
     */
    public function getAllVideosCount()
    {
        (($sPlugin = Phpfox_Plugin::get('v.component_service_video_getallcount__start')) ? eval($sPlugin) : false);

        $sCacheId = $this->cache()->set('video_all_count');
        if (!$iAllVideosCount = $this->cache()->get($sCacheId, 1)) {
            $iAllVideosCount = db()->select("COUNT(*)")
                ->from($this->_sTable)
                ->where(['view_id' => 0])
                ->executeField();

            $this->cache()->save($sCacheId, $iAllVideosCount);
        }

        return $iAllVideosCount;
    }

    /**
     * Get friend videos count
     * @param null $iUserId
     * @return int
     */
    public function getFriendVideosCount($iUserId = null)
    {
        (($sPlugin = Phpfox_Plugin::get('v.component_service_video_getfriendvideoscount__start')) ? eval($sPlugin) : false);

        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }

        $sCacheId = $this->cache()->set('video_friend_count_' . (int)$iUserId);
        if (!$iFriendVideosCount = $this->cache()->get($sCacheId, 1)) {
            list(, $aFriends) = Phpfox::getService('friend')->get(['AND friend.user_id = ' . (int)Phpfox::getUserId()],
                '', '', false);

            if (empty($aFriends)) {
                $iFriendVideosCount = 0;
            } else {
                $iFriendVideosCount = db()->select("COUNT(*)")
                    ->from($this->_sTable)
                    ->where('view_id = 0 AND user_id IN (' . implode(',', array_column($aFriends, 'user_id')) . ')')
                    ->executeField();
            }

            $this->cache()->save($sCacheId, $iFriendVideosCount);
        }

        return $iFriendVideosCount;
    }

    /**
     * @param $aVideo
     * @param int $iEmbedHeight
     * @param int $iImageSize
     * @param bool $useStyle
     * @return mixed
     */
    public function compileVideo($aVideo, $iEmbedHeight = 360, $iImageSize = 500, $useStyle = false)
    {
        $iEmbedWidth = '100%';
        $allowWidth = true;
        $regex = "/http(?:s?):\/\/(?:www\.|web\.|m\.)?(facebook\.com)|(fb\.watch)/";
        if (preg_match($regex, $aVideo['video_url'])) {
            $aVideo['embed_code'] = '<div class="fb-video dont-unbind-children" crossorigin="anonymous" id="fb_v_'. (PHPFOX_TIME * rand(1, 100)) .'" data-video-id="'. $aVideo['video_id'] .'" data-href="'. $aVideo['video_url'] .'"></div>';
            $aVideo['is_new_embed'] = 1;
            $allowWidth = false;
            $aVideo['is_facebook_embed'] = 1;
        }
        if ($aVideo['is_stream'] && $aVideo['embed_code'] && $allowWidth) {
            $aVideo['embed_code'] = preg_replace('/width=\"(.*?)\"/i', 'width="' . $iEmbedWidth . '"',
                $aVideo['embed_code']);
            $aVideo['embed_code'] = preg_replace('/height=\"(.*?)\"/i', 'height="' . $iEmbedHeight . '"',
                $aVideo['embed_code']);
            $aVideo['embed_code'] = preg_replace_callback('/<object(.*?)>(.*?)<\/object>/is',
                [$this, '_embedWmode'], $aVideo['embed_code']);
            if (Phpfox::getParam('core.force_https_secure_pages')) {
                $aVideo['embed_code'] = str_replace('http://', 'https://', $aVideo['embed_code']);
            }
        }

        $this->convertImagePath($aVideo, $iImageSize);

        if (!empty($aVideo['destination'])) {
            if ($aVideo['server_id'] == -1) {
                $aVideo['destination'] = setting('pf_video_s3_url') . $aVideo['destination'];
            } elseif ($aVideo['server_id'] == -3) {
                $aVideo['destination'] = 'https://stream.mux.com/' . $aVideo['destination'];
            } else {
                $aVideo['destination'] = Phpfox::getParam('core.url_file') . 'video/' . sprintf($aVideo['destination'],
                        '');
                if ($aVideo['server_id'] > 0 && $aVideo['destination']) {
                    $aVideo['destination'] = Phpfox::getLib('cdn')->getUrl($aVideo['destination'],
                        $aVideo['server_id']);
                }
            }

            if (strpos($aVideo['destination'], '.m3u8')) {
                $aVideo['embed_code'] = '
				<video data-m3u8="true" data-resolution-x="'. $aVideo['resolution_x'] . '" data-resolution-y="'. $aVideo['resolution_y'] . '" data-src="'.$aVideo['destination'].'" data-poster="'.$aVideo['image_path'].'" data-video-id="' . $aVideo['video_id'] . '" '. ($useStyle ? ' style="width: ' . $iEmbedWidth .'; height: auto;"' : '') . ' width=" ' . $iEmbedWidth . '" height="' . $iEmbedHeight . '" controls 
				class="js-pf-video-embed js-pf-video-mux-embed" poster="' . $aVideo['image_path'] . '" preload="auto" data-setup="{}">
				    <source src="' . $aVideo['destination'] . '" type="application/x-mpegURL">
                </video>';
            } elseif (strpos($aVideo['destination'], '.mp4')) {
                $aVideo['embed_code'] = '
				<video data-resolution-x="'. $aVideo['resolution_x'] . '" data-resolution-y="'. $aVideo['resolution_y'] . '" data-src="'.$aVideo['destination'].'" data-poster="'.$aVideo['image_path'].'" data-video-id="' . $aVideo['video_id'] . '" '. ($useStyle ? ' style="width: ' . $iEmbedWidth .'; height: auto;"' : '') . ' width=" ' . $iEmbedWidth . '" height="' . $iEmbedHeight . '" controls 
				class="js-pf-video-embed" poster="' . $aVideo['image_path'] . '" preload="auto" data-setup="{}">
				    <source src="' . $aVideo['destination'] . '" type="video/mp4">
                </video>';
            } else {
                $aVideo['embed_code'] = '<div class="alert alert-danger">' . _p('the_video_does_not_supported_for_play') . '</div>';
            }
        }

        return $aVideo;
    }

    /**
     * @param $aMatches
     * @return string
     */
    private function _embedWmode($aMatches)
    {
        return '<object ' . $aMatches[1] . '><param name="wmode" value="transparent"></param>' . str_replace('<embed ',
                '<embed  wmode="transparent" ', $aMatches[2]) . '</object>';
    }

    /**
     * @param $aRow
     * @param int $iSize
     */
    public function convertImagePath(&$aRow, $iSize = 500)
    {
        if (isset($aRow['image_server_id']) && $aRow['image_server_id'] == -1 && !empty($aRow['image_path'])) {
            $aRow['image_path'] = (trim(setting('pf_video_s3_url'), '/') . '/') . $aRow['image_path'];
        } elseif (isset($aRow['image_server_id']) && $aRow['image_server_id'] == -2 && !empty($aRow['image_path'])) {
            $aRow['image_path'] = str_replace('dailymotion.com/thumbnail/160x120', 'dailymotion.com/thumbnail/640x360',
                $aRow['image_path']);
        } elseif (empty($aRow['image_path'])) {
            $aRow['image_path'] = Phpfox::getParam('video.default_video_photo');
        } elseif ($aRow['image_server_id'] == -3) {
            $aRow['image_path'] = 'https://image.mux.com/' . $aRow['image_path'];
        } else {
            if (strpos($aRow['image_path'], 'video/') !== 0) { // support V3 video
                $aRow['image_path'] = 'video/' . $aRow['image_path'];
            }
            $sImagePath = $aRow['image_path'];
            $aRow['image_path'] = Phpfox::getLib('image.helper')->display([
                'server_id' => $aRow['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $sImagePath,
                'suffix' => '_' . $iSize,
                'return_url' => true
            ]);
            $aRow['fallback_image_path'] = Phpfox::getLib('image.helper')->display([
                'server_id' => $aRow['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $sImagePath,
                'suffix' => '_500',
                'return_url' => true
            ]);
        }
    }

    public function getPendingSponsorItems()
    {
        $sCacheId = $this->cache()->set('v_pending_sponsor');
        if (false === ($aItems = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('v.video_id')
                ->from(Phpfox::getT('video'), 'v')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = v.video_id')
                ->where('v.is_sponsor = 0 AND s.is_custom = 2 AND s.module_id = "v"')
                ->execute('getSlaveRows');
            $aItems = array_column($aRows, 'video_id');
            $this->cache()->save($sCacheId, $aItems);
        }
        return $aItems;
    }

    public function canPurchaseSponsorItem($iItemId)
    {
        $aIds = $this->getPendingSponsorItems();
        return in_array($iItemId, $aIds) ? false : true;
    }


    /**
     * @param $aRow
     */
    public function getPermissions(&$aRow)
    {
        $aRow['canEdit'] = (($aRow['user_id'] == Phpfox::getUserId() && user('pf_video_edit_own_video')) || user('pf_video_edit_all_video'));
        $aRow['canDelete'] = (($aRow['user_id'] == Phpfox::getUserId() && user('pf_video_delete_own_video')) || user('pf_video_delete_all_video'));
        if (!$aRow['canDelete']) {
            if ($aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages') && Phpfox::getService('pages')->isAdmin($aRow['item_id'])) {
                $aRow['canDelete'] = true; // is owner of page
            } elseif ($aRow['module_id'] == 'groups' && Phpfox::isAppActive('PHPfox_Groups') && Phpfox::getService('groups')->isAdmin($aRow['item_id'])) {
                $aRow['canDelete'] = true; // is owner of group
            } elseif ($aRow['module_id'] == 'user' && Phpfox::getUserId() == $aRow['item_id']) {
                $aRow['canDelete'] = true; // is owner of group
            }
        }

        $aRow['canSponsorInFeed'] = $aRow['canSponsor'] = $aRow['canPurchaseSponsor'] = false;
        if (Phpfox::isAppActive('Core_BetterAds') && Phpfox::getUserBy('profile_page_id') == 0) {
            $aRow['canSponsorInFeed'] = $aRow['view_id'] == 0 && (Phpfox::isModule('feed') && (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('feed.can_purchase_sponsor')) || Phpfox::getUserParam('feed.can_sponsor_feed')) && (Phpfox::getService('feed')->canSponsoredInFeed('v', $aRow['video_id'])));
            $aRow['canSponsor'] = ($aRow['view_id'] == 0 && Phpfox::getUserParam('v.can_sponsor_v'));
            $bCanPurchaseSponsor = $this->canPurchaseSponsorItem($aRow['video_id']);
            $aRow['canPurchaseSponsor'] = ($aRow['view_id'] == 0 && $aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('v.can_purchase_sponsor') && $bCanPurchaseSponsor);
        }
        $aRow['iSponsorInFeedId'] = Phpfox::isModule('feed') && (Phpfox::getService('feed')->canSponsoredInFeed('v', $aRow['video_id']) === true);
        $aRow['canApprove'] = (user('pf_video_approve') && $aRow['view_id'] == 2);
        $aRow['canFeature'] = (user('pf_video_feature') && $aRow['view_id'] == 0);
        $aRow['hasPermission'] = ($aRow['canEdit'] || $aRow['canDelete'] || $aRow['canSponsor'] || $aRow['canApprove'] || $aRow['canFeature'] || $aRow['canPurchaseSponsor']);
    }

    /**
     * @return array|int|string
     */
    public function getCountConvertOldVideos()
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('feed'))
            ->where(['type_id' => 'PHPfox_Videos'])
            ->execute('getSlaveField');

        return $iCnt;
    }

    /**
     *
     */
    public function convertOldVideos()
    {
        //each time run in 600 seconds or 100 videos
        $start = time();

        //Get 100 old video
        $aFeeds = db()
            ->select('*')
            ->from(Phpfox::getT('feed'))
            ->where(['type_id' => 'PHPfox_Videos'])
            ->limit(100)
            ->execute('getSlaveRows');

        foreach ($aFeeds as $aFeed) {
            $video = json_decode($aFeed['content']);
            $bIsStream = (isset($video->embed_code)) ? 1 : 0;
            $sDestination = '';
            $iServerId = 0;
            $iImageServerId = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            $sFileExt = '';
            $sImagePath = '';

            // get module id and item id
            $sModuleId = 'video';
            $iItem_id = 0;
            if ($aFeed['parent_feed_id']) {
                $aPage = db()
                    ->select('p.page_id, p.item_type, pf.content')
                    ->from(Phpfox::getT('pages'), 'p')
                    ->join(Phpfox::getT('pages_feed'), 'pf', 'pf.parent_user_id = p.page_id')
                    ->where(['pf.feed_id' => $aFeed['parent_feed_id']])
                    ->execute('getSlaveRow');

                if (!$aPage) {
                    // delete feeds
                    db()->delete(Phpfox::getT('feed'), ['feed_id' => $aFeed['feed_id']]);
                    db()->delete(Phpfox::getT('pages_feed'), ['feed_id' => $aFeed['parent_feed_id']]);
                    // delete comments
                    db()->delete(Phpfox::getT('comment'), ['type_id' => 'app', 'item_id' => $aFeed['feed_id']]);
                    // delete likes
                    db()->delete(Phpfox::getT('like'), ['type_id' => 'app', 'item_id' => $aFeed['feed_id']]);
                    // delete likes cache
                    db()->delete(Phpfox::getT('like_cache'), ['type_id' => 'app', 'item_id' => $aFeed['feed_id']]);
                    // delete report
                    db()->delete(Phpfox::getT('report_data'), ['item_id' => 'PHPfox_Videos_' . $aFeed['feed_id']]);
                    // delete notifications
                    db()->delete(Phpfox::getT('notification'),
                        ['item_id' => $aFeed['feed_id'], 'type_id' => 'PHPfox_Videos/video_ready']);
                    db()->delete(Phpfox::getT('notification'),
                        ['item_id' => $aFeed['feed_id'], 'type_id' => 'PHPfox_Videos/__like']);
                    db()->delete(Phpfox::getT('notification'),
                        ['item_id' => $aFeed['feed_id'], 'type_id' => 'PHPfox_Videos/__comment']);
                    continue;
                }
                $sModuleId = (!empty($aPage['item_type']) ? 'groups' : 'pages');
                $iItem_id = (isset($aPage['page_id']) ? $aPage['page_id'] : 0);
                if (isset($aPage['content'])) {
                    $video = json_decode($aPage['content']);
                }
            }

            // check and download image to local server
            if (isset($video->embed_image) and $video->embed_image != '') {
                if (strpos($video->embed_image, 'dailymotion.com/thumbnail')) {
                    $sImagePath = $video->embed_image;
                    $iImageServerId = -2;
                } else {
                    list($sImagePath, $iPhotoSize) = Phpfox::getService('v.process')->downloadImage($video->embed_image);
                    if ($iPhotoSize > 0) {
                        Phpfox::getService('user.space')->update($aFeed['user_id'], 'photo', $iPhotoSize);
                    }
                }
            } else {
                if (isset($video->path) and $video->path != '') {
                    $sImagePath = $video->path . '.png/frame_0001.png';
                    $sDestination = $video->path . '.mp4';
                    $iServerId = -1;
                    $iImageServerId = -1;
                    $sFileExt = 'mp4';
                }
            }

            // get url video from embed code
            $sVideoUrl = '';
            if (!empty($video->embed_code)) {
                preg_match('/src="([^"]+)"/', $video->embed_code, $match);
                if (count($match) > 1) {
                    $sVideoUrl = $match[1];
                }
            }

            $iPageUserId = 0;
            if (in_array($sModuleId, ['pages', 'groups'])) {
                $iPageUserId = $this->getPageUserId($iItem_id);
            }
            // insert new video
            $iVideoId = db()->insert(Phpfox::getT('video'), [
                    'is_stream' => $bIsStream,
                    'module_id' => $sModuleId,
                    'item_id' => $iItem_id,
                    'title' => ($video->caption) ? $video->caption : $sVideoUrl,
                    'user_id' => $aFeed['user_id'],
                    'privacy' => $aFeed['privacy'],
                    'server_id' => $iServerId,
                    'destination' => $sDestination,
                    'file_ext' => $sFileExt,
                    'image_path' => $sImagePath,
                    'image_server_id' => $iImageServerId,
                    'total_comment' => (int)db()->select('COUNT(*)')->from(':comment')->where([
                        'type_id' => 'app',
                        'item_id' => $aFeed['feed_id'],
                        'feed_table' => 'feed'
                    ])->execute('getSlaveField'),
                    'total_like' => (int)db()->select('COUNT(*)')->from(':like')->where([
                        'type_id' => 'app',
                        'item_id' => $aFeed['feed_id'],
                        'feed_table' => 'feed'
                    ])->execute('getSlaveField'),
                    'total_view' => $aFeed['total_view'],
                    'time_stamp' => $aFeed['time_stamp'],
                    'page_user_id' => $iPageUserId
                ]
            );

            // update total video when module id is 'video'
            if ($sModuleId == 'video') {
                // Update user activity
                Phpfox::getService('user.activity')->update($aFeed['user_id'], 'v');
            }

            // insert video embed
            if (!empty($video->embed_code)) {
                db()->insert(Phpfox::getT('video_embed'), [
                        'video_url' => $sVideoUrl,
                        'video_id' => $iVideoId,
                        'embed_code' => $video->embed_code
                    ]
                );
            }

            // insert video text
            db()->insert(Phpfox::getT('video_text'), [
                    'text' => ($video->status_update) ? $video->status_update : '',
                    'video_id' => $iVideoId,
                    'text_parsed' => ($video->status_update) ? $video->status_update : ''
                ]
            );

            // insert category data
            $active = storage()->get('pf_video_c', $aFeed['feed_id']);
            if (!empty($active) && isset($active->value)) {
                $catMap = storage()->get('pf_video_categories_map', $active->value);
                if (!empty($catMap) && isset($catMap->value)) {
                    db()->insert(Phpfox::getT('video_category_data'), [
                            'category_id' => $catMap->value,
                            'video_id' => $iVideoId
                        ]
                    );
                }
            } elseif ($iItem_id) {
                $active = storage()->get('pf_video_pc', $iItem_id);
                if (!empty($active) && isset($active->value)) {
                    $catMap = storage()->get('pf_video_categories_map', $active->value);
                    if (!empty($catMap) && isset($catMap->value)) {
                        db()->insert(Phpfox::getT('video_category_data'), [
                                'category_id' => $catMap->value,
                                'video_id' => $iVideoId
                            ]
                        );
                    }
                }
            }

            // update feeds
            db()->update(Phpfox::getT('feed'), ['type_id' => 'v', 'item_id' => $iVideoId, 'content' => ''],
                ['feed_id' => $aFeed['feed_id']]);
            db()->update(Phpfox::getT('feed'), ['parent_module_id' => 'v', 'parent_feed_id' => $iVideoId],
                ['parent_module_id' => 'PHPfox_Videos', 'parent_feed_id' => $aFeed['feed_id']]);
            db()->update(Phpfox::getT('pages_feed'), ['type_id' => 'v', 'item_id' => $iVideoId, 'content' => ''],
                ['feed_id' => $aFeed['parent_feed_id']]);

            // update comments
            db()->update(Phpfox::getT('comment'), ['type_id' => 'v', 'item_id' => $iVideoId],
                ['type_id' => 'app', 'item_id' => $aFeed['feed_id']]);
            if ($iItem_id) {
                db()->update(Phpfox::getT('comment'),
                    ['type_id' => 'v', 'item_id' => $iVideoId, 'feed_table' => 'feed'],
                    ['type_id' => 'app', 'item_id' => $iItem_id, 'feed_table' => 'pages_feed']);
            }

            // update likes
            db()->update(Phpfox::getT('like'), ['type_id' => 'v', 'item_id' => $iVideoId],
                ['type_id' => 'app', 'item_id' => $aFeed['feed_id']]);
            if ($iItem_id) {
                db()->update(Phpfox::getT('like'), ['type_id' => 'v'],
                    ['type_id' => 'app', 'item_id' => $iItem_id, 'feed_table' => 'pages_feed']);
            }

            // update likes cache
            db()->update(Phpfox::getT('like_cache'), ['type_id' => 'v', 'item_id' => $iVideoId],
                ['type_id' => 'app', 'item_id' => $aFeed['feed_id']]);
            if ($iItem_id) {
                db()->update(Phpfox::getT('like_cache'), ['type_id' => 'v'],
                    ['type_id' => 'app', 'item_id' => $iItem_id]);
            }

            // update report
            db()->update(Phpfox::getT('report_data'), ['item_id' => 'v_' . $iVideoId],
                ['item_id' => 'PHPfox_Videos_' . $aFeed['feed_id']]);

            // update notification
            db()->update(Phpfox::getT('notification'), ['item_id' => $iVideoId, 'type_id' => 'v_ready'],
                ['item_id' => $aFeed['feed_id'], 'type_id' => 'PHPfox_Videos/video_ready']);
            if ($iItem_id) {
                db()->update(Phpfox::getT('notification'), ['item_id' => $iVideoId, 'type_id' => 'v_ready'],
                    ['item_id' => $iItem_id, 'type_id' => 'PHPfox_Videos/video_ready_p']);
            }
            db()->update(Phpfox::getT('notification'), ['item_id' => $iVideoId, 'type_id' => 'v_like'],
                ['item_id' => $aFeed['feed_id'], 'type_id' => 'PHPfox_Videos/__like']);
            db()->update(Phpfox::getT('notification'), ['item_id' => $iVideoId, 'type_id' => 'comment_v'],
                ['item_id' => $aFeed['feed_id'], 'type_id' => 'PHPfox_Videos/__comment']);

            //End process convert
            $end = time();
            if (($end - $start) >= 600) {
                break;
            }
        }
    }

    /**
     * @param $iPageId
     * @return array|int|string
     */
    public function getPageUserId($iPageId)
    {
        $iUser = db()->select('user_id')->from(Phpfox::getT('user'))->where('profile_page_id = ' . (int)$iPageId . ' AND view_id = 7')->execute('getSlaveField');

        return $iUser ? $iUser : 0;
    }

    /**
     * @return int
     */
    public function canCheckInInFeed()
    {
        return (!defined('PHPFOX_IS_PAGES_VIEW') && !defined('PHPFOX_IS_EVENT_VIEW') && Phpfox::getParam('feed.enable_check_in') && Phpfox::getParam('core.google_api_key')) ? 1 : 0;
    }

    /**
     * @param $value
     * @return string
     */
    public function getDuration($value)
    {
        $value = intval($value);

        if ($value <= 0) {
            return '';
        }

        $hour = floor($value / 3600);
        $min = floor(($value - $hour * 3600) / 60);
        $second = $value - $hour * 3600 - $min * 60;
        $result = [];

        if ($hour) {
            $result[] = str_pad($hour, 2, '0', STR_PAD_LEFT);
        }
        $result[] = str_pad($min, 2, '0', STR_PAD_LEFT);
        $result[] = str_pad($second, 2, '0', STR_PAD_LEFT);

        return implode(':', $result);
    }

    /**
     * Get upload video params, support dropzone
     *
     * @return array
     */
    public function getUploadVideoParams()
    {
        $iMaxFileSize = Phpfox::getUserParam('v.pf_video_file_size');
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);
        $sPreviewTemplate =
            '<div class="dz-preview dz-file-preview">
                <input class="dz-form-file-id js_upload_form_file_v" type="hidden" id="js_upload_form_file_v" />
                <div class="dz-upload-successfully-icon"><i class="ico ico-check-circle-alt"></i></div>
                <div class="dz-uploading-message">' . _p('your_video_is_being_uploaded_please_dont_close_this_tab') . '</div>
                <div class="dz-filename"><span data-dz-name ></span></div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                <div class="dz-upload-successfully">' . _p('your_video_is_being_processed_and_will_be_available_soon_after_shared') . '</div>
                <div class="dz-upload-again btn btn-primary">' . _p('browse_three_dot') . '</div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
            </div>';

        return [
            'max_size' => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'type_list' => $this->_aAllowedTypes,
            'style' => '',
            'label' => '',
            'first_description' => _p('drag_and_drop_video_file_here'),
            'type_description' => _p('you_can_upload_a_extensions'),
            'max_size_description' => _p('maximum_file_size_is_file_size',
                ['file_size' => Phpfox_File::filesize($iMaxFileSize * 1048576)]),
            'upload_url' => Phpfox_Url::instance()->makeUrl('video.upload'),
            'param_name' => 'ajax_upload',
            'type_list_string' => '',
            'upload_icon' => 'ico ico-videocam-o',
            'keep_form' => true,
            'preview_template' => $sPreviewTemplate,
            'use_browse_button' => true,
            'js_events' => [
                'success' => '$Core.Video.processUploadSuccess',
                'addedfile' => '$Core.Video.processAddedFile',
                'error' => '$Core.Video.processError',
            ],
            'extra_data' => [
                'not-show-remove-icon' => 'true',
                'remove-button-action' => '$Core.Video.processRemoveButton',
                'single-mode' => 'true',
                'error-message-outside' => 'true'
            ]
        ];
    }

    /**
     * Get upload photo params, support dropzone
     *
     * @return array
     */
    public function getUploadPhotoParams()
    {
        $iMaxFileSize = Phpfox::getUserParam('v.pf_video_max_file_size_photo_upload');
        $iMaxFileSize = $iMaxFileSize > 0 ? $iMaxFileSize / 1024 : 0;
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);

        return [
            'max_size' => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'type_list' => ['jpg', 'jpeg', 'gif', 'png'],
            'label' => _p('videos_image'),
            'upload_dir' => Phpfox::getParam('v.dir_image'),
            'upload_path' => Phpfox::getParam('v.url_image'),
            'thumbnail_sizes' => $this->_aThumbnailSizes,
            'no_square' => true
        ];
    }

    /**
     * Check if current user is admin of photo's parent item
     * @param $iVideoId
     * @return bool|mixed
     */
    public function isAdminOfParentItem($iVideoId)
    {
        $aVideo = db()->select('video_id, module_id, item_id')->from($this->_sTable)->where('video_id = ' . (int)$iVideoId)->execute('getRow');
        if (!$aVideo) {
            return false;
        }
        if ($aVideo['module_id'] && Phpfox::hasCallback($aVideo['module_id'], 'isAdmin')) {
            return Phpfox::callback($aVideo['module_id'] . '.isAdmin', $aVideo['item_id']);
        }
        return false;
    }
}
