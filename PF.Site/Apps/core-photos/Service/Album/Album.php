<?php

namespace Apps\Core_Photos\Service\Album;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

class Album extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('photo_album');
    }

    /**
     * Get random sponsored photo albums
     *
     * @param int $iLimit
     * @param int $iCacheTime
     *
     * @return array
     */
    public function getRandomSponsoredAlbum($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('photo_album_sponsored');
        if (($sAlbumIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sAlbumIds = '';
            $sConds = 'pa.view_id = 0 AND pa.is_sponsor = 1 AND pa.total_photo > 0 AND s.module_id = "photo_album" AND s.is_active = 1 AND s.is_custom = 3';
            $sConds .= Phpfox::getService('photo')->getConditionsForSettingPageGroup('pa');
            $aAlbumIds = $this->database()->select('pa.album_id')
                ->from(Phpfox::getT('photo_album'), 'pa')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = pa.album_id')
                ->where($sConds)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aAlbumIds as $key => $aId) {
                if ($key != 0) {
                    $sAlbumIds .= ',' . $aId['album_id'];
                } else {
                    $sAlbumIds = $aId['album_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sAlbumIds);
            }
        }
        if (empty($sAlbumIds)) {
            return [];
        }
        $aAlbumIds = explode(',', $sAlbumIds);
        shuffle($aAlbumIds);
        $aAlbumIds = array_slice($aAlbumIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aAlbums = $this->database()->select(Phpfox::getUserField() . ', s.*, pa.album_id, pa.total_photo, pa.name, pa.user_id, p.destination, p.server_id, p.mature, pa.profile_id, pa.cover_id, pa.timeline_id')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
            ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = pa.album_id AND s.module_id = \'photo_album\' AND s.is_active = 1 AND s.is_custom = 3')
            ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND p.is_cover = 1')
            ->where('pa.album_id IN (' . implode(',', $aAlbumIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        if (empty($aAlbums) || !is_array($aAlbums)) {
            return [];
        }
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aAlbums = Phpfox::getService('ad')->filterSponsor($aAlbums);
        }
        shuffle($aAlbums);

        return $aAlbums;
    }

    /**
     * Get random featured photo albums
     *
     * @param int $iLimit
     * @param int $iCacheTime
     *
     * @return array
     */
    public function getFeaturedAlbums($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('photo_album_featured');
        if (($sAlbumIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sAlbumIds = '';
            $sConds = 'pa.view_id = 0 AND pa.is_featured = 1 AND pa.total_photo > 0';
            $sConds .= Phpfox::getService('photo')->getConditionsForSettingPageGroup('pa');
            $aAlbumIds = $this->database()->select('pa.album_id')
                ->from($this->_sTable, 'pa')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
                ->where($sConds)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aAlbumIds as $key => $aId) {
                if ($key != 0) {
                    $sAlbumIds .= ',' . $aId['album_id'];
                } else {
                    $sAlbumIds = $aId['album_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sAlbumIds);
            }
        }
        if (empty($sAlbumIds)) {
            return [];
        }
        $aAlbumIds = explode(',', $sAlbumIds);
        shuffle($aAlbumIds);
        $aAlbumIds = array_slice($aAlbumIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aAlbums = $this->database()->select('p.destination, p.server_id, p.mature, pa.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'pa')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
            ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id  AND p.is_cover = 1')
            ->where('pa.album_id IN (' . implode(',', $aAlbumIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        if (!is_array($aAlbums)) {
            return [];
        }

        shuffle($aAlbums);

        return $aAlbums;
    }

    /**
     * Get all albums based on filters we passed via the params.
     *
     * @param array  $mConditions SQL Conditions
     * @param string $sOrder      SQL Ordering
     * @param mixed  $iPage       Current page we are on
     * @param mixed  $iPageSize   Define how many photos we can display at one time
     *
     * @return array Return an array of the total album count and the albums
     */
    public function get($mConditions = [], $sOrder = 'pa.time_stamp DESC', $iPage = '', $iPageSize = '')
    {
        $aAlbums = [];

        (($sPlugin = Phpfox_Plugin::get('photo.service_album_album_get_count')) ? eval($sPlugin) : false);

        $iCnt = db()->select('COUNT(DISTINCT pa.name)')
            ->from($this->_sTable, 'pa')
            ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id')
            ->where($mConditions)
            ->execute('getSlaveField');

        if ($iCnt) {
            (($sPlugin = Phpfox_Plugin::get('photo.service_album_album_get_query')) ? eval($sPlugin) : false);

            $aAlbums = db()->select('pa.*, p.destination, p.server_id, p.mature, ' . Phpfox::getUserField())
                ->from($this->_sTable, 'pa')
                ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND pa.view_id = 0 AND p.is_cover = 1')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
                ->where($mConditions)
                ->order($sOrder)
                ->limit($iPage, $iPageSize, $iCnt)
                ->execute('getSlaveRows');
        }

        return [$iCnt, $aAlbums];
    }

    /**
     * Get all the albums for a specific user.
     *
     * @param int     $iUserId User ID.
     * @param boolean $sModule
     * @param boolean $iItem
     *
     * @return array Return the array of albums.
     */
    public function getAll($iUserId, $sModule = false, $iItem = false)
    {
        (($sPlugin = Phpfox_Plugin::get('photo.service_album_album_getall')) ? eval($sPlugin) : false);

        return db()->select('album_id, name, profile_id, cover_id, timeline_id')
            ->from($this->_sTable)
            ->where(($sModule === false ? 'module_id IS NULL AND group_id = 0 AND ' : 'module_id = \'' . $this->database()->escape($sModule) . '\' AND group_id = ' . (int)$iItem . ' AND ') . 'user_id = ' . (int)$iUserId)
            ->execute('getSlaveRows');
    }

    /**
     * Get the total count of albums for a specific user.
     *
     * @param int $iUserId User ID.
     *
     * @return int Return the total number of albums.
     */
    public function getAlbumCount($iUserId)
    {
        (($sPlugin = Phpfox_Plugin::get('photo.service_album_album_getalbumcount')) ? eval($sPlugin) : false);
        $bIsDisplayProfile = Phpfox::getParam('photo.display_profile_photo_within_gallery');
        $bIsDisplayCover = Phpfox::getParam('photo.display_cover_photo_within_gallery');
        $bIsDisplayTimeline = Phpfox::getParam('photo.display_timeline_photo_within_gallery');
        $aReturn = db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('
			    view_id = 0' .
                ($bIsDisplayProfile ? '' : ' AND profile_id=0') .
                ($bIsDisplayCover ? '' : ' AND cover_id=0') .
                ($bIsDisplayTimeline ? '' : ' AND timeline_id=0') .
                ' AND user_id = ' . (int)$iUserId
            )
            ->execute('getSlaveField');
        return $aReturn;
    }

    /**
     * Get a specific album based on the user ID and album ID or album title.
     *
     * @param int     $iUserId User ID this album belongs to
     * @param mixed   $mId     Album ID or Album title
     * @param boolean $bUseId  True to use an album ID call or else it is an album title
     *
     * @return array Array of the album
     */
    public function getAlbum($iUserId, $mId, $bUseId = false)
    {
        (($sPlugin = Phpfox_Plugin::get('photo.service_album_album_getalbum')) ? eval($sPlugin) : false);

        return db()->select('pa.*, pai.*')
            ->from($this->_sTable, 'pa')
            ->join(Phpfox::getT('photo_album_info'), 'pai', 'pai.album_id = pa.album_id')
            ->where('pa.user_id = ' . (int)$iUserId . ' AND ' . ($bUseId === true ? 'pa.album_id = ' . (int)$mId : '1'))
            ->execute('getSlaveRow');
    }

    /**
     * @param      $iId
     * @param bool $bIsProfile
     *
     * @return array|bool|int|string
     */
    public function getForView($iId, $bIsProfile = false)
    {
        if (Phpfox::isModule('friend')) {
            db()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = pa.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }

        if (Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'photo_album\' AND l.item_id = pa.album_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aAlbum = db()->select('pa.*, pai.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'pa')
            ->join(Phpfox::getT('photo_album_info'), 'pai', 'pai.album_id = pa.album_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
            ->where(($bIsProfile ? 'pa.profile_id = ' . (int)$iId : 'pa.album_id = ' . (int)$iId))
            ->execute('getSlaveRow');
        if (!isset($aAlbum['album_id'])) {
            return false;
        }

        if (!isset($aAlbum['is_friend'])) {
            $aAlbum['is_friend'] = $aAlbum['privacy'] == 0;
        }
        if (!isset($aAlbum['is_liked'])) {
            $aAlbum['is_liked'] = false;
        }
        // Define to create right page/group url when using function display of image.helper
        switch ($aAlbum['module_id']) {
            case 'pages':
                $aAlbum['item_type'] = 0;
                break;
            case 'groups':
                $aAlbum['item_type'] = 1;
                break;
        }
        return $aAlbum;
    }

    /**
     * @param int $iId
     *
     * @return bool|array
     */
    public function getCoverForView($iId)
    {
        if (Phpfox::isModule('friend')) {
            db()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = pa.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }

        if (Phpfox::isModule('like')) {
            db()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'photo_album\' AND l.item_id = pa.album_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aAlbum = db()->select('pa.*, pai.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'pa')
            ->join(Phpfox::getT('photo_album_info'), 'pai', 'pai.album_id = pa.album_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
            ->where('pa.cover_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id'])) {
            return false;
        }

        if (!isset($aAlbum['is_friend'])) {
            $aAlbum['is_friend'] = $aAlbum['privacy'] == 0;
        }
        if (!isset($aAlbum['is_liked'])) {
            $aAlbum['is_liked'] = false;
        }

        // Define to create right page/group url when using function display of image.helper
        switch ($aAlbum['module_id']) {
            case 'pages':
                $aAlbum['item_type'] = 0;
                break;
            case 'groups':
                $aAlbum['item_type'] = 1;
                break;
        }

        return $aAlbum;
    }

    /**
     * @param      $iProfileId
     * @param bool $bForceCreation
     *
     * @return array|bool|int|string
     */
    public function getForProfileView($iProfileId, $bForceCreation = false, $bForcePublic = false)
    {
        $aAlbum = $this->getForView($iProfileId, true);
        if (!isset($aAlbum['album_id']) || $bForceCreation === true) {
            $aUser = db()->select(Phpfox::getUserField())
                ->from(Phpfox::getT('user'), 'u')
                ->where('u.user_id = ' . (int)$iProfileId)
                ->execute('getSlaveRow');
            $sModuleId = null;
            if ($aUser['profile_page_id']) {
                $iItemType = db()->select('item_type')->from(':pages')->where(['page_id' => $aUser['profile_page_id']])->executeField();
                $sModuleId = ($iItemType == 0) ? 'pages' : 'groups';
            }
            if (!isset($aUser['user_id'])) {
                return false;
            }
            if (!isset($aAlbum['album_id'])) {
                $aInsert = [
                    'privacy'         => '0',
                    'privacy_comment' => '0',
                    'user_id'         => $aUser['user_id'],
                    'name'            => "{_p var='profile_pictures'}",
                    'time_stamp'      => PHPFOX_TIME,
                    'profile_id'      => $aUser['user_id'],
                    'total_photo'     => 1
                ];
                if ($aUser['profile_page_id']) {
                    $aInsert['module_id'] = $sModuleId;
                    $aInsert['group_id'] = $aUser['profile_page_id'];
                }
                $iId = $this->database()->insert(Phpfox::getT('photo_album'), $aInsert);
                db()->insert(Phpfox::getT('photo_album_info'), ['album_id' => $iId]);
            } else {
                $iId = $aAlbum['album_id'];
            }

            $directlyPublic = $bForcePublic || !Phpfox::getUserParam('photo.photo_must_be_approved');
            $userDir = Phpfox::getParam('core.dir_user');
            if ($directlyPublic) {
                $userInfo  = [
                    'user_image' => !empty($aUser['user_image']) ? $aUser['user_image'] : null,
                    'server_id' => isset($aUser['user_server_id']) ? $aUser['user_server_id'] : null,
                ];
            } else {
                $tempCacheName = 'user_profile_photo_pending_temp_' . $iProfileId;
                $tempCache = storage()->get($tempCacheName);
                if (!empty($tempCache->value->user_image) && isset($tempCache->value->server_id)) {
                    $userInfo = [
                        'user_image' => $tempCache->value->user_image,
                        'server_id' => $tempCache->value->server_id,
                    ];
                    if (isset($tempCache->value->add_feed)) {
                        $userInfo['add_feed'] = true;
                    }
                    storage()->del($tempCacheName);
                }
            }

            if (!empty($userInfo['user_image']) && isset($userInfo['server_id']) && file_exists($userDir . sprintf($userInfo['user_image'], ''))) {
                $sFileName = $userInfo['user_image'];
                $userPhotoPath = $userDir . sprintf($sFileName, '');
                $aImage = getimagesize($userPhotoPath);

                @clearstatcache();
                $iFileSize = filesize($userPhotoPath);

                $aInsert = [
                    'album_id'         => $iId,
                    'title'            => date('F j, Y'),
                    'user_id'          => $aUser['user_id'],
                    'server_id'        => $userInfo['server_id'],
                    'time_stamp'       => PHPFOX_TIME,
                    'destination'      => $sFileName,
                    'view_id'          => $directlyPublic ? 0 : 1,
                ];

                // update cover of album to 0, new image is 1
                if ($directlyPublic) {
                    $this->database()->update(Phpfox::getT('photo'), ['is_cover' => '0'], 'album_id = ' . (int)$iId);
                    $aInsert = array_merge($aInsert, [
                        'is_cover'         => '1',
                        'is_profile_photo' => '1',
                    ]);
                }

                if (defined('PHPFOX_FORCE_PHOTO_VERIFY_EMAIL')) {
                    $aInsert['view_id'] = 3;
                }
                if ($aUser['profile_page_id']) {
                    $aInsert['module_id'] = $sModuleId;
                    $aInsert['group_id'] = $aUser['profile_page_id'];
                }
                $iPhotoInsert = db()->insert(Phpfox::getT('photo'), $aInsert);

                $aExts = preg_split("/[\/\\.]/", sprintf($sFileName, ''));
                $iCnt = count($aExts) - 1;
                $sExt = strtolower($aExts[$iCnt]);

                db()->insert(Phpfox::getT('photo_info'), [
                        'photo_id'  => $iPhotoInsert,
                        'file_name' => sprintf($sFileName, ''),
                        'mime_type' => $aImage['mime'],
                        'extension' => $sExt,
                        'width'     => $aImage[0],
                        'height'    => $aImage[1],
                        'file_size' => $iFileSize
                    ]
                );

                if ($directlyPublic) {
                    storage()->del('user/avatar/' . $iProfileId);
                    storage()->set('user/avatar/' . $iProfileId, $iPhotoInsert);
                } else {
                    $profileCachePrefix = 'user_profile_photo_pending_';
                    $cachedItems = db()->select('file_name')
                        ->from(':cache')
                        ->where([
                            'file_name' => ['like' => '%' . $profileCachePrefix . '%'],
                            'AND ((cache_data LIKE \'%"user_id":' . $iProfileId . '%\') OR (cache_data LIKE \'%"user_id":"' . $iProfileId . '"%\') OR (cache_data LIKE \'%"album_id":' . $iId . '%\') OR (cache_data LIKE \'%"album_id":"' . $iId . '"%\'))'
                        ])->executeRows(false);
                    if (!empty($cachedItems)) {
                        $oldProfilePhotoIds = [];
                        foreach ($cachedItems as $cacheItem) {
                            if (preg_match('/^' . $profileCachePrefix . '([\d]+)$/', $cacheItem['file_name'], $match) && is_numeric($match[1])) {
                                $oldProfilePhotoIds[] = $match[1];
                            }
                        }
                        if (!empty($oldProfilePhotoIds)
                            && db()->update(':photo', ['album_id' => $iId], ['photo_id' => ['in' => implode(',', $oldProfilePhotoIds)]])) {
                            foreach ($oldProfilePhotoIds as $tempPhotoId) {
                                storage()->del($profileCachePrefix . $tempPhotoId);
                                storage()->set('photo_no_feed_' . $tempPhotoId, 1);
                            }
                        }
                    }

                    $storageKey = $profileCachePrefix . $iPhotoInsert;
                    $params = array_merge($userInfo, [
                        'user_id' => (int)$iProfileId,
                        'album_id' => (int)$iId,
                    ]);
                    storage()->del($storageKey);
                    storage()->set($storageKey, $params);
                }

                // create photo dir
                $photoDir = Phpfox::getParam('photo.dir_photo');
                Phpfox::getLib('file')->getBuiltDir($photoDir); // build Y and M dir
                $photoPath = $photoDir . sprintf($sFileName, '');
                copy($userPhotoPath, $photoPath); // copy user photo to photo dir

                //push to cdn for user and photo
                Phpfox::getLib('cdn')->put($userPhotoPath);
                Phpfox::getLib('cdn')->put($photoPath);

                $oImage = Phpfox::getLib('image');
                foreach (Phpfox::getService('photo')->getPhotoPicSizes() as $iSize) {
                    // Create the thumbnail
                    if ($oImage->createThumbnail($userPhotoPath,
                            $photoDir . sprintf($sFileName, '_' . $iSize), $iSize, $iSize,
                            true,
                            false) === false
                    ) {
                        continue;
                    }

                }

                Phpfox::getService('user.activity')->update($aUser['user_id'], 'photo', '+', 1, false);
                if (Phpfox::isAppActive('Core_Activity_Points') && $directlyPublic) {
                    Phpfox::getService('activitypoint.process')->updatePoints($aUser['user_id'], 'user_uploadprofilephoto');
                }
            }

            $aAlbum = $this->getForView($iProfileId, true);
        }

        if ($bForceCreation && isset($iPhotoInsert)) {
            $aAlbum['photo_id'] = $iPhotoInsert;
        }

        // Define to create right page/group url when using function display of image.helper
        switch ($aAlbum['module_id']) {
            case 'pages':
                $aAlbum['item_type'] = 0;
                break;
            case 'groups':
                $aAlbum['item_type'] = 1;
                break;
        }

        return $aAlbum;
    }

    /**
     * @param int $iProfileId
     *
     * @return array|bool
     */
    public function getForCoverView($iProfileId)
    {
        $aAlbum = $this->getCoverForView($iProfileId);
        return $aAlbum;
    }

    /**
     * @param      $iId
     * @param bool $bForce
     *
     * @return array|bool|int|string
     */
    public function getForEdit($iId, $bForce = false)
    {
        (($sPlugin = Phpfox_Plugin::get('photo.service_album_album_getforedit')) ? eval($sPlugin) : false);

        $aAlbum = db()->select('pa.*, pai.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'pa')
            ->join(Phpfox::getT('photo_album_info'), 'pai', 'pai.album_id = pa.album_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
            ->where('pa.album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id'])) {
            return false;
        }

        if ($bForce || (Phpfox::getUserId() == $aAlbum['user_id'] && Phpfox::getUserParam('photo.can_edit_own_photo_album')) || Phpfox::getUserParam('photo.can_edit_other_photo_albums')) {
            return $aAlbum;
        }

        return false;
    }

    public function inThisAlbum($iAlbumId, $iLimit = 8, $iPage = 1)
    {
        $aRows = db()->select(Phpfox::getUserField())
            ->from(Phpfox::getT('photo_tag'), 'pt')
            ->innerJoin(Phpfox::getT('photo'), 'p', 'p.album_id = ' . (int)$iAlbumId)
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pt.tag_user_id')
            ->where('pt.photo_id = p.photo_id')
            ->group('u.user_id', true)
            ->limit($iPage, $iLimit)
            ->forCount()
            ->execute('getSlaveRows');

        $iCnt = db()->getCount();

        return [$iCnt, $aRows];
    }

    /**
     * @return int
     */
    public function getMyAlbumTotal()
    {
        $sWhere = 'user_id = ' . Phpfox::getUserId();
        $aModules = [];
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (!Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        if (count($aModules)) {
            $sWhere .= ' AND (module_id NOT IN ("' . implode('","', $aModules) . '") OR module_id IS NULL)';
        }
        $sWhere .= ' AND (((profile_id > 0 OR cover_id > 0 OR timeline_id > 0) AND total_photo > 0) OR (profile_id = 0 AND cover_id = 0 AND timeline_id = 0))';
        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    /**
     * @param $aRow
     */
    public function getPermissions(&$aRow)
    {
        $aRow['canEdit'] = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('photo.can_edit_own_photo_album')) || Phpfox::getUserParam('photo.can_edit_other_photo_albums'));
        $aRow['canUpload'] = ($aRow['user_id'] == Phpfox::getUserId() && $aRow['profile_id'] == '0' && $aRow['cover_id'] == '0' && $aRow['timeline_id'] == '0');
        $aRow['canDelete'] = $this->_canDelete($aRow);

        $aRow['canSponsor'] = $aRow['canSponsorPurchase'] = false;
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aRow['canSponsor'] = (Phpfox::getUserParam('photo.can_sponsor_album') && (int)$aRow['view_id'] == 0);
            $bCanPurchaseSponsor = Phpfox::getService('photo')->canPurchaseSponsorItem($aRow['album_id'], 'photo_album', 'photo_album', 'album_id');
            $aRow['canSponsorPurchase'] = (Phpfox::getUserParam('photo.can_purchase_sponsor_album') && (int)$aRow['view_id'] == 0 && (Phpfox::getUserId() == $aRow['user_id']) && $bCanPurchaseSponsor);
        }
        $aRow['canFeature'] = (Phpfox::getUserParam('photo.can_feature_photo_album') && (int)$aRow['view_id'] == 0);
        $aRow['hasPermission'] = ($aRow['canEdit'] || $aRow['canDelete'] || $aRow['canUpload'] || $aRow['canSponsor'] || $aRow['canSponsorPurchase'] || $aRow['canFeature']);
    }

    private function _canDelete($aRow)
    {
        $bCanDelete = ($aRow['profile_id'] == '0' && $aRow['cover_id'] == '0' && $aRow['timeline_id'] == '0' && (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('photo.can_delete_own_photo_album')) || Phpfox::getUserParam('photo.can_delete_other_photo_albums')));
        if (!$bCanDelete && Phpfox::isModule($aRow['module_id'])) {
            if ($aRow['module_id'] == 'pages' && Phpfox::getService('pages')->isAdmin($aRow['group_id'])) {
                $bCanDelete = true; // is owner of page
            } else if ($aRow['module_id'] == 'groups' && Phpfox::getService('groups')->isAdmin($aRow['group_id'])) {
                $bCanDelete = true; // is owner of group
            }
        }
        return $bCanDelete;
    }

    /**
     * @param $iAlbumId
     *
     * @return array|int|string
     */
    public function getTotalPendingInAlbum($iAlbumId)
    {
        return db()->select('COUNT(*)')
            ->from(':photo', 'p')
            ->join($this->_sTable, 'pa', 'pa.album_id = p.album_id')
            ->where('p.view_id = 1 AND pa.album_id =' . (int)$iAlbumId)
            ->execute('getField');
    }

    /**
     * Check if current user is admin of photo's parent item
     *
     * @param $iAlbumId
     *
     * @return bool|mixed
     */
    public function isAdminOfParentItem($iAlbumId)
    {
        $aAlbum = db()->select('album_id, module_id, group_id')->from($this->_sTable)->where('album_id = ' . (int)$iAlbumId)->execute('getRow');
        if (!$aAlbum) {
            return false;
        }
        if ($aAlbum['module_id'] && Phpfox::hasCallback($aAlbum['module_id'], 'isAdmin')) {
            return Phpfox::callback($aAlbum['module_id'] . '.isAdmin', $aAlbum['group_id']);
        }
        return false;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('photo.service_album__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}