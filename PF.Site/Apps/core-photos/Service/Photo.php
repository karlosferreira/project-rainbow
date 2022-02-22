<?php

namespace Apps\Core_Photos\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Plugin;
use Phpfox_Service;

class Photo extends Phpfox_Service
{
    private $_bIsTagSearch = false;
    private $_aPhotoPicSizes = [75, 100, 150, 240, 500, 1024];

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('photo');
    }

    public function canRotate($sExtension)
    {
        if (empty($sExtension)) {
            return false;
        }

        return $sExtension != 'gif' || \Phpfox_Image::instance()->isSupportNextGenImg();
    }

    public function getTotalUploadingPhotos($userId = null)
    {
        empty($userId) && $userId = Phpfox::getUserId();

        $totalPhotos = (int)db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where([
                'user_id' => $userId,
                'is_profile_photo' => 0,
                'is_cover_photo' => 0,
            ])->executeField();

        //Count total photos were scheduled in feed
        $photoSchedules = storage()->get('photo/schedule/' . $userId);
        if (!empty($photoSchedules->value)) {
            $totalPhotos += (int)$photoSchedules->value;
        }
        return (int)$totalPhotos;
    }

    public function getTotalPhotosPerUploading($userId = null, $getBoolean = false, $iUploadedTotal = 0)
    {
        empty($userId) && $userId = Phpfox::getUserId();

        $totalRemainingUploadingPhotos = $this->getRemainingTotalUploadingPhotos($userId);

        if ($totalRemainingUploadingPhotos) {
            $maxFilesPerUpload = (int)Phpfox::getUserParam('photo.max_images_per_upload') - $iUploadedTotal;
            $maxFiles = $totalRemainingUploadingPhotos === true ? ($getBoolean ? true : (int)$maxFilesPerUpload) : ($totalRemainingUploadingPhotos >= $maxFilesPerUpload ? ($getBoolean ? true : (int)$maxFilesPerUpload) : (int)$totalRemainingUploadingPhotos);
        } else {
            $maxFiles = 0;
        }

        return $maxFiles;
    }

    public function getRemainingTotalUploadingPhotos($userId = null)
    {
        empty($userId) && $userId = Phpfox::getUserId();

        $limit = trim(Phpfox::getUserParam('photo.photo_total_photos_upload'));

        if ($limit == '') {
            return true;
        } elseif ((int)$limit == 0) {
            return 0;
        }

        $totalUploading = $this->getTotalUploadingPhotos($userId);
        $limit = (int)$limit;

        return $totalUploading >= $limit ? 0 : $limit - $totalUploading;
    }

    public function checkUploadPhotoLimitation($userId = null)
    {
        static $photoLimitations = [];

        empty($userId) && $userId = Phpfox::getUserId();

        if (isset($photoLimitations[$userId])) {
            return $photoLimitations[$userId];
        }

        $photoLimitations[$userId] = !!$this->getRemainingTotalUploadingPhotos($userId);

        return $photoLimitations[$userId];
    }

    public function getPendingSponsorItems($sTypeId, $sTable, $sFieldId)
    {
        $sCacheId = $this->cache()->set($sTypeId . '_pending_sponsor');
        if (false === ($aItems = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('m.' . $sFieldId)
                ->from(Phpfox::getT($sTable), 'm')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = m.' . $sFieldId)
                ->where('m.is_sponsor = 0 AND s.is_custom = 2 AND s.module_id = "' . $sTypeId . '"')
                ->execute('getSlaveRows');
            $aItems = array_column($aRows, $sFieldId);
            $this->cache()->save($sCacheId, $aItems);
        }
        return $aItems;
    }

    public function canPurchaseSponsorItem($iItemId, $sTypeId, $sTable, $sFieldId)
    {
        $aIds = $this->getPendingSponsorItems($sTypeId, $sTable, $sFieldId);
        return in_array($iItemId, $aIds) ? false : true;
    }


    public function getCoverPhoto($iPhotoId)
    {
        $aRow = db()->select('*')
            ->from(Phpfox::getT('photo'))
            ->where('photo_id = ' . (int)$iPhotoId)
            ->execute('getSlaveRow');
        if (isset($aRow['photo_id'])) {
            return $aRow;
        }
        return false;
    }

    public function isTagSearch($bIsTagSearch = false)
    {
        $this->_bIsTagSearch = $bIsTagSearch;

        return $this;
    }

    /**
     * Get all photos based on filters we passed via the params.
     *
     * @param array  $mConditions SQL Conditions
     * @param string $sOrder      SQL Ordering
     * @param mixed  $iPage       Current page we are on
     * @param mixed  $iPageSize   Define how many photos we can display at one time
     * @param array  $aCallback   remove in v4.6
     *
     * @return array Return an array of the total photo count and the photos
     */
    public function get(
        $mConditions = [],
        $sOrder = 'p.photo_id DESC',
        $iPage = '',
        $iPageSize = '',
        $aCallback = null
    )
    {
        $aPhotos = [];
        if ($this->_bIsTagSearch !== false) {
            db()->innerJoin(Phpfox::getT('tag'), 'tag', "tag.item_id = p.photo_id AND tag.category_id = 'photo'");
        }

        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('photo'), 'p')
            ->where($mConditions)
            ->execute('getSlaveField');

        if ($iCnt) {
            if ($this->_bIsTagSearch !== false) {
                db()->innerJoin(Phpfox::getT('tag'), 'tag', "tag.item_id = p.photo_id AND tag.category_id = 'photo'");
            }

            if (Phpfox::isModule('like')) {
                db()->select('l.like_id as is_liked, ')
                    ->leftJoin(Phpfox::getT('like'), 'l',
                        'l.type_id = "photo" AND l.item_id = p.photo_id AND l.user_id = ' . Phpfox::getUserId() . '');
            }

            $aPhotos = db()->select(Phpfox::getUserField() . ', p.*, pa.name AS album_url, pi.*, pa.profile_id, pa.cover_id, pa.timeline_id')
                ->from(Phpfox::getT('photo'), 'p')
                ->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
                ->where($mConditions)
                ->order($sOrder)
                ->limit($iPage, $iPageSize, $iCnt)
                ->execute('getSlaveRows');

            foreach ($aPhotos as $iKey => $aPhoto) {
                $sCategoryList = '';
                $aCategories = (array)db()->select('category_id')
                    ->from(Phpfox::getT('photo_category_data'))
                    ->where('photo_id = ' . (int)$aPhoto['photo_id'])
                    ->execute('getSlaveRows');

                foreach ($aCategories as $aCategory) {
                    $sCategoryList .= $aCategory['category_id'] . ',';
                }

                if (Phpfox::isModule('tag')) {
                    $aTags = Phpfox::getService('tag')->getTagsById('photo', $aPhoto['photo_id']);
                    if (isset($aTags[$aPhoto['photo_id']])) {
                        $aPhoto['tag_list'] = '';
                        foreach ($aTags[$aPhoto['photo_id']] as $aTag) {
                            $aPhoto['tag_list'] .= ' ' . $aTag['tag_text'] . ',';
                        }
                        $aPhoto['tag_list'] = trim(trim($aPhoto['tag_list'], ','));
                    }
                }

                $aPhoto['link'] = Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
                $aPhoto['category_list'] = rtrim($sCategoryList, ',');
                $aPhoto['destination'] = $this->getPhotoUrl($aPhoto);
                $isProfile = ($aPhoto['profile_id'] || $aPhoto['cover_id']);
                $aPhoto['can_add_mature'] = (Phpfox::getUserParam('photo.can_add_mature_images') && !$isProfile);
                $this->getPermissions($aPhoto);
                $aPhoto['type_id'] = !empty($aPhoto['timeline_id']) ? 1 : 0;
                $aPhotos[$iKey] = $aPhoto;
            }
        }

        return [$iCnt, $aPhotos];
    }

    public function getForEdit($iId)
    {
        $aPhoto = db()->select('p.*, pi.*, pa.name AS album_url, pa.name AS album_title, pa.profile_id, pa.cover_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.photo_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aPhoto['categories'] = Phpfox::getService('photo.category')->getCategoriesById($aPhoto['photo_id']);

        if (Phpfox::isModule('tag')) {
            $aTags = Phpfox::getService('tag')->getTagsById('photo', $aPhoto['photo_id']);
            if (isset($aTags[$aPhoto['photo_id']])) {
                $aPhoto['tag_list'] = '';
                foreach ($aTags[$aPhoto['photo_id']] as $aTag) {
                    $aPhoto['tag_list'] .= ' ' . $aTag['tag_text'] . ',';
                }
                $aPhoto['tag_list'] = trim(trim($aPhoto['tag_list'], ','));
            }
        }

        $sCategoryList = '';
        $aCategories = (array)db()->select('category_id')
            ->from(Phpfox::getT('photo_category_data'))
            ->where('photo_id = ' . (int)$aPhoto['photo_id'])
            ->execute('getSlaveRows');

        foreach ($aCategories as $aCategory) {
            $sCategoryList .= $aCategory['category_id'] . ',';
        }

        $aPhoto['category_list'] = rtrim($sCategoryList, ',');

        if (!empty($aPhoto['description'])) {
            $aPhoto['description'] = str_replace('<br />', "\n", $aPhoto['description']);
        }

        $isProfile = ($aPhoto['profile_id'] || $aPhoto['cover_id']);
        $aPhoto['can_add_mature'] = (Phpfox::getUserParam('photo.can_add_mature_images') && !$isProfile);

        return $aPhoto;
    }

    public function getForProcess($iId, $iUserId = 0)
    {
        return db()->select('user_id, photo_id, server_id, title, album_id, group_id, destination, privacy, privacy_comment, view_id')
            ->from($this->_sTable)
            ->where('photo_id = ' . (int)$iId . ' AND user_id = ' . ($iUserId ? $iUserId : Phpfox::getUserId()))
            ->execute('getSlaveRow');
    }

    public function getApprovalPhotosCount()
    {
        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('view_id = 1')
            ->execute('getSlaveField');
    }

    public function getPhotoByDestination($sName)
    {
        $aPhoto = db()->select('p.*, pi.*, pa.name AS album_title, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.destination = \'' . db()->escape($sName) . '\'')
            ->execute('getSlaveRow');

        if (!isset($aPhoto['photo_id'])) {
            return false;
        }

        return $aPhoto;
    }

    /**
     * @param $sId
     *
     * @return array|bool|int|string
     */
    public function getPhoto($sId)
    {
        if (Phpfox::isModule('like')) {
            db()->select('lik.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'lik',
                    'lik.type_id = \'photo\' AND lik.item_id = p.photo_id AND lik.user_id = ' . Phpfox::getUserId());
        }
        if (Phpfox::isModule('friend')) {
            db()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = p.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        if (Phpfox::isModule('track')) {
            $sJoinQuery = Phpfox::isUser() ? 'pt.user_id = ' . Phpfox::getUserBy('user_id') : 'pt.ip_address = \'' . $this->database()->escape(Phpfox::getIp()) . '\'';
            db()->select('pt.item_id AS is_viewed, ')
                ->leftJoin(Phpfox::getT('track'), 'pt',
                    'pt.item_id = p.photo_id AND pt.type_id=\'photo\' AND ' . $sJoinQuery);
        }
        db()->where('p.photo_id = ' . (int)$sId);

        $aPhoto = db()->select('' . Phpfox::getUserField() . ', p.*, pi.*, pa.name AS album_url, pa.name AS album_title, pa.profile_id AS album_profile_id, pa.cover_id AS album_cover_id, pa.timeline_id AS album_timeline_id')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->execute('getSlaveRow');
        if (!isset($aPhoto['photo_id'])) {
            return false;
        }

        if (!isset($aPhoto['is_liked'])) {
            $aPhoto['is_liked'] = false;
        }
        if (!isset($aPhoto['is_viewed'])) {
            $aPhoto['is_viewed'] = 0;
        }
        if (!isset($aPhoto['is_friend'])) {
            $aPhoto['is_friend'] = 0;
        }
        if (Phpfox::isModule('tag')) {
            $aTags = Phpfox::getService('tag')->getTagsById('photo', $aPhoto['photo_id']);
            if (isset($aTags[$aPhoto['photo_id']])) {
                $aPhoto['tag_list'] = $aTags[$aPhoto['photo_id']];
            }
        }

        $aPhoto['categories'] = Phpfox::getService('photo.category')->getCategoriesById($aPhoto['photo_id']);
        $aPhoto['category_list'] = Phpfox::getService('photo.category')->getCategoryIds($aPhoto['photo_id']);

        if (empty($aPhoto['album_id'])) {
            $aPhoto['album_url'] = 'view';
        }

        $aPhoto['original_destination'] = $aPhoto['destination'];
        $aPhoto['destination'] = $this->getPhotoUrl($aPhoto);

        if ($aPhoto['album_id'] > 0) {
            if ($aPhoto['album_profile_id'] > 0) {
                $aPhoto['album_title'] = _p('user_profile_pictures', ['full_name' => $aPhoto['full_name']]);;
                $aPhoto['album_url'] = Phpfox::permalink('photo.album.profile', $aPhoto['user_id'],
                    $aPhoto['user_name']);
            }
            if ($aPhoto['album_cover_id'] > 0) {
                $aPhoto['album_title'] = _p('user_cover_photo', ['full_name' => $aPhoto['full_name']]);
                $aPhoto['album_url'] = Phpfox::permalink('photo.album.cover', $aPhoto['user_id'], $aPhoto['user_name']);
            }
            if ($aPhoto['album_timeline_id'] > 0) {
                $aPhoto['album_title'] = _p('user_timeline_photos', ['full_name' => $aPhoto['full_name']]);
                $aPhoto['album_url'] = Phpfox::permalink('photo.album', $aPhoto['album_id'], $aPhoto['album_title']);
            } else {
                $aPhoto['album_url'] = Phpfox::permalink('photo.album', $aPhoto['album_id'], $aPhoto['album_title']);
            }
        }
        $aPhoto['location_latlng'] = json_decode($aPhoto['location_latlng'], true);
        $aPhoto['link'] = Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
        if (!empty($aPhoto['profile_page_id'])) {
            $aPhoto['item_type'] = $aPhoto['module_id'] == 'groups' ? 1 : 0;
        }
        if (!empty($aPhoto['description']) && Phpfox::isModule('feed')) {
            $aPhoto['description'] = Phpfox::getService('feed.tag')->stripContentHashTag($aPhoto['description'], $aPhoto['photo_id'], 'photo');
        }
        $this->getPermissions($aPhoto);

        return $aPhoto;
    }

    /**
     * @param $sId
     *
     * @return \Phpfox_Database_Dba
     */
    public function getPhotoItem($sId)
    {
        $aPhoto = db()->select('p.*,  pi.*, pa.profile_id, pa.cover_id')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.photo_id = ' . (int)$sId)
            ->execute('getSlaveRow');
        if (count($aPhoto)) {
            $aPhoto['original_destination'] = $aPhoto['destination'];
            $aPhoto['destination'] = $this->getPhotoUrl($aPhoto);
            $isProfile = ($aPhoto['profile_id'] || $aPhoto['cover_id']);
            $aPhoto['can_add_mature'] = (Phpfox::getUserParam('photo.can_add_mature_images') && !$isProfile);
        }
        return $aPhoto;
    }

    /**
     * Get feed/detail link for notification/mail
     *
     * @param $iItemId
     *
     * @return string
     */
    public function getFeedLink($iItemId)
    {
        $photo = $this->getPhotoItem($iItemId);
        if (!$photo) {
            return null;
        }
        $title = Phpfox::getLib('parse.output')->clean($photo['title']);
        $feed = (Phpfox::isModule('feed') ? Phpfox::getService('feed')->getParentFeedItem('photo', $iItemId) : null);
        if (!$feed || (empty($feed['user_name']))) {
            $link = Phpfox::getLib('url')->permalink('photo', $iItemId, Phpfox::getParam('photo.photo_show_title', 1) ? $title : null);
        } else {
            $link = Phpfox::getLib('url')->makeUrl($feed['user_name'], ['feed' => $feed['feed_id']]);
        }
        return $link;
    }

    /**
     * We get and return the latest images we uploaded. The reason we run
     * this check is so we only return images that belong to the user that is loggeed in
     * and not someone else images.
     *
     * @param int   $iUserId User ID of the user the images belong to.
     * @param array $aIds    Array of photo IDS
     *
     * @return array Array of user images.
     */
    public function getNewImages($iUserId, $aIds)
    {
        // We run an INT check just in case someone is trying to be funny.
        $sIds = '';
        foreach ($aIds as $iKey => $sId) {
            if (!is_numeric($sId)) {
                continue;
            }
            $sIds .= $sId . ',';
        }
        $sIds = rtrim($sIds, ',');

        // Lets the new images and return them.
        return db()->select('p.photo_id, p.album_id, p.destination, p.server_id, p.view_id, pa.privacy, p.title')
            ->from($this->_sTable, 'p')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.photo_id IN(' . $sIds . ') AND p.user_id = ' . (int)$iUserId)
            ->order('p.photo_id DESC')
            ->execute('getSlaveRows');
    }

    public function getRandomSponsored($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('photo_sponsored');
        if (($sPhotoIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sPhotoIds = "";
            $sWhere = 'p.view_id = 0 AND p.is_sponsor = 1 AND s.module_id = \'photo\' AND s.is_active = 1 AND s.is_custom = 3';
            $sWhere .= $this->getConditionsForSettingPageGroup();

            $aPhotoIds = db()->select('p.photo_id')
                ->from($this->_sTable, 'p')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = p.photo_id')
                ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
                ->where($sWhere)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aPhotoIds as $key => $aId) {
                if ($key != 0) {
                    $sPhotoIds .= ',' . $aId['photo_id'];
                } else {
                    $sPhotoIds = $aId['photo_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sPhotoIds);
            }
        }
        if (empty($sPhotoIds)) {
            return [];
        }
        $aPhotoIds = explode(',', $sPhotoIds);
        shuffle($aPhotoIds);
        $aPhotoIds = array_slice($aPhotoIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aRows = db()->select('s.*, pi.width, pi.height, u.user_name, p.mature, p.total_like, p.total_view as total_view_photo, p.time_stamp, pi.file_size, p.photo_id, p.destination, p.server_id, p.title, p.album_id')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->join(Phpfox::getT('better_ads_sponsor'), 's',
                's.item_id = p.photo_id AND s.module_id = \'photo\' AND s.is_active = 1 AND s.is_custom = 3')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.photo_id IN (' . implode(',', $aPhotoIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        if (!isset($aRows[0]) || empty($aRows[0])) {
            return [];
        }

        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aRows = Phpfox::getService('ad')->filterSponsor($aRows);
        }
        shuffle($aRows);

        $aOut = [];
        for ($i = 0; ($i < $iLimit) && !empty($aRows); ++$i) {
            $aRow = array_pop($aRows);
            if (Phpfox::isAppActive('Core_BetterAds')) {
                Phpfox::getService('ad.process')->addSponsorViewsCount($aRow['sponsor_id'], 'photo');
            }

            $aRow['details'] = [
                _p('submitted')  => Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aRow['time_stamp']),
                _p('file_size')  => Phpfox_File::filesize($aRow['file_size']),
                _p('resolution') => $aRow['width'] . '×' . $aRow['height'],
                _p('views')      => $aRow['total_view_photo']
            ];
            $aRow['total_view'] = $aRow['total_view_photo'];
            $aRow['can_view'] = true;
            if ($aRow['user_id'] != Phpfox::getUserId() && $aRow['mature'] != 0 && Phpfox::getUserParam([
                    'photo.photo_mature_age_limit' => [
                        '>',
                        (int)Phpfox::getUserBy('age')
                    ]
                ])
            ) {
                $aRow['can_view'] = false;
            }
            $aRow['link'] = Phpfox::getLib('url')->makeUrl('ad.sponsor') . 'view_' . $aRow['sponsor_id'];
            $aOut[] = $aRow;
        }

        return $aOut;
    }

    /**
     * @param $iPhotoId
     *
     * @return bool
     */
    public function isSponsoredInFeed($iPhotoId)
    {
        if (!Phpfox::isAppActive('Core_BetterAds') || !Phpfox::isModule('feed')) {
            return false;
        }
        //Get Feed ID of Photo
        $iFeedId = db()->select('feed_id')
            ->from(':feed')
            ->where('type_id="photo" AND item_id=' . (int)$iPhotoId)
            ->execute('getSlaveField');
        if (!$iFeedId) {
            return false;
        }
        $iCnt = db()->select('DISTINCT item_id')
            ->from(Phpfox::getT('better_ads_sponsor'))
            ->where('module_id = "feed" AND item_id=' . (int)$iFeedId)
            ->execute('getSlaveField');
        return ($iCnt) ? false : true;
    }

    /**
     * @param int $iLimit
     *
     * @return array|int|string
     */
    public function getNew($iLimit = 3)
    {
        $aPhotos = db()->select('p.destination, p.server_id, p.title, p.photo_id, p.mature, p.album_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.view_id = 0 AND p.group_id = 0 AND p.privacy = 0')
            ->order('p.photo_id DESC')
            ->limit($iLimit)
            ->execute('getSlaveRows');

        foreach ($aPhotos as $iKey => $aPhoto) {
            $aPhotos[$iKey]['link'] = Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
        }

        return $aPhotos;
    }

    /* This function is used in the converting controller to get the image that needs to have its thumbnails created.*/
    public function getForConverting($iUserId, $iLimit = 1)
    {
        $aPhoto = db()->select('p.photo_id, p.destination, p.server_id, p.title, p.mature, p.album_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.view_id = 0 AND p.user_id = ' . (int)$iUserId)
            ->order('p.photo_id DESC')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        return $aPhoto;
    }

    public function getForProfile($iUserId, $iLimit = 3)
    {
        $aPhotos = db()->select(Phpfox::getUserField() . ',p.*, p.destination, p.server_id, p.title, p.mature, p.album_id, pa.name AS album_name')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.view_id = 0 AND p.group_id = 0 AND p.user_id = ' . (int)$iUserId . ((!Phpfox::getParam('photo.display_profile_photo_within_gallery')) ? ' AND p.is_profile_photo IN (0)' : '') . ((!Phpfox::getParam('photo.display_cover_photo_within_gallery')) ? ' AND p.is_cover_photo IN (0)' : '') . ((!Phpfox::getParam('photo.display_timeline_photo_within_gallery')) ? ' AND type_id = 0' : ''))
            ->order('p.photo_id DESC')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        foreach ($aPhotos as $iKey => $aPhoto) {
            $aPhotos[$iKey]['link'] = Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
            if (!Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $iUserId, $aPhoto['privacy'], null,
                true)
            ) {
                unset($aPhotos[$iKey]);
            } else {
                $aPhotos[$iKey]['can_view'] = true;
                if ($aPhoto['user_id'] != Phpfox::getUserId() && $aPhoto['mature'] != 0 && Phpfox::getUserParam([
                        'photo.photo_mature_age_limit' => [
                            '>',
                            (int)Phpfox::getUserBy('age')
                        ]
                    ])
                ) {
                    $aPhotos[$iKey]['can_view'] = false;
                }
            }
        }

        return $aPhotos;
    }

    /**
     * @param $iGroupId
     * @param $sGroupUrl remove in v4.6
     *
     * @return array|int|string
     */
    public function getForGroup($iGroupId, $sGroupUrl)
    {
        $aPhotos = db()->select('p.destination, p.server_id, p.title, p.mature, p.album_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.view_id = 0 AND p.group_id = ' . $iGroupId . ' AND p.privacy = 0')
            ->order('p.photo_id DESC')
            ->limit(3)
            ->execute('getSlaveRows');

        foreach ($aPhotos as $iKey => $aPhoto) {
            $aPhotos[$iKey]['link'] = Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
        }

        return $aPhotos;
    }

    /**
     * Return the featured time stamp in milliseconds
     *
     * @return int Time stamp in milliseconds
     */
    public function getFeaturedRefreshTime()
    {
        // Get the refresh setting
        $sTime = Phpfox::getUserParam('photo.refresh_featured_photo');

        // Match the minutes or seconds
        preg_match("/(.*?)(min|sec)$/i", $sTime, $aMatches);

        // Make sure we have a match
        if (isset($aMatches[1]) && isset($aMatches[2])) {
            // Trim the matched time stamp
            $aMatches[2] = trim($aMatches[2]);

            // If we want to work with minutes
            if ($aMatches[2] == 'min') {
                // Convert to milliseconds
                return (int)$aMatches[1] * 60000;
            } // If we want to work with seconds
            else if ($aMatches[2] == 'sec') {
                // Convert to milliseconds
                return (int)$aMatches[1] * 1000;
            }
        }

        // Return the default value (60 seconds)
        return 60000;
    }

    /**
     * @return int
     */
    public function getMyPhotoTotal()
    {
        $sWhere = '(type_id = 0 OR (type_id = 1 AND (parent_user_id = 0 OR group_id))) AND user_id = ' . Phpfox::getUserId();
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

        if (!Phpfox::getParam('photo.display_profile_photo_within_gallery')) {
            $sWhere .= ' AND is_profile_photo IN (0)';
        }
        if (!Phpfox::getParam('photo.display_cover_photo_within_gallery')) {
            $sWhere .= ' AND is_cover_photo IN (0)';
        }
        if (!Phpfox::getParam('photo.display_timeline_photo_within_gallery')) {
            $sWhere .= ' AND (type_id = 0 OR (type_id = 1 AND group_id != 0))';
        }

        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    /**
     * @return int
     */
    public function getPendingTotal()
    {
        $sWhere = 'view_id = 1';
        $aModules = [];
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (!Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        $sWhere .= ' AND (module_id NOT IN ("' . implode('","', $aModules) . '") OR module_id is NULL)';

        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    public function getPhotoUrl($aPhoto)
    {
        $sUrl = $aPhoto['destination'];
        return $sUrl;
    }

    /**
     * @param int $iLimit
     * @param int $iCacheTime
     *
     * @return array
     */
    public function getFeatured($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('photo_featured');
        if (($sPhotoIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sPhotoIds = "";
            $sWhere = 'p.view_id = 0 AND p.is_featured = 1';
            $sWhere .= $this->getConditionsForSettingPageGroup();
            if (!Phpfox::getParam('photo.display_profile_photo_within_gallery')) {
                $sWhere .= ' AND p.is_profile_photo IN (0)';
            }

            if (!Phpfox::getParam('photo.display_cover_photo_within_gallery')) {
                $sWhere .= ' AND p.is_cover_photo IN (0)';
            }

            if (!Phpfox::getParam('photo.display_timeline_photo_within_gallery')) {
                $sWhere .= ' AND (p.type_id = 0 OR (p.type_id = 1 AND p.group_id != 0))';
            }
            $aPhotoIds = db()->select('p.photo_id')
                ->from(Phpfox::getT('photo'), 'p')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->where($sWhere)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');

            foreach ($aPhotoIds as $key => $aId) {
                if ($key != 0) {
                    $sPhotoIds .= ',' . $aId['photo_id'];
                } else {
                    $sPhotoIds = $aId['photo_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sPhotoIds);
            }
        }
        if (empty($sPhotoIds)) {
            return [0, []];
        }
        $aPhotoIds = explode(',', $sPhotoIds);
        shuffle($aPhotoIds);
        $aPhotoIds = array_slice($aPhotoIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aRows = db()->select('p.*, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('photo'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.photo_id IN (' . implode(',', $aPhotoIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        foreach ($aRows as $key => $aRow) {
            $aRows[$key]['can_view'] = true;
            if ($aRow['user_id'] != Phpfox::getUserId() && $aRow['mature'] != 0 && Phpfox::getUserParam([
                    'photo.photo_mature_age_limit' => [
                        '>',
                        (int)Phpfox::getUserBy('age')
                    ]
                ])
            ) {
                $aRows[$key]['can_view'] = false;
            }
            $aRows[$key]['link'] = Phpfox::getLib('url')->permalink('photo', $aRow['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aRow['title'] : null);
        }
        shuffle($aRows);

        return [count($aRows), $aRows];
    }

    /**
     * Build sub menu
     */
    public function buildMenu()
    {
        $aFilterMenu = [];
        if (!defined('PHPFOX_IS_USER_PROFILE')) {
            $sAllPhotosKey = _p('all_photos');

            $iMyPhotosTotal = $this->getMyPhotoTotal();
            $sMyPhotosKey = _p('my_photos') . ($iMyPhotosTotal ? '<span class="my count-item">' . ($iMyPhotosTotal > 99 ? '99+' : $iMyPhotosTotal) . '</span>' : '');
            $sFriendPhotosKey = _p('friends_photos');
            $sAllAlbumsKey = _p('all_albums');
            $iMyAlbumsTotal = Phpfox::getService('photo.album')->getMyAlbumTotal();
            $sMyAlbumsKey = _p('my_albums') . ($iMyAlbumsTotal ? '<span class="my count-item">' . ($iMyAlbumsTotal > 99 ? '99+' : $iMyAlbumsTotal) . '</span>' : '');

            if (Phpfox::getParam('photo.in_main_photo_section_show',
                    'photos') == 'albums' && Phpfox::getUserParam('photo.can_view_photo_albums')
            ) {
                $aFilterMenu[$sAllAlbumsKey] = '';
                $aFilterMenu[$sMyAlbumsKey] = 'photo.albums.view_myalbums';
                $aFilterMenu[] = true;
            }

            if (Phpfox::getParam('core.friends_only_community') || !Phpfox::isModule('friend')) {
                $aFilterMenu[$sAllPhotosKey] = 'photos';
                $aFilterMenu[$sMyPhotosKey] = 'my';
            } else {
                if (Phpfox::getParam('photo.in_main_photo_section_show', 'photos') == 'albums') {
                    $aFilterMenu[$sAllPhotosKey] = 'photo.view_photos';
                } else {
                    $aFilterMenu[$sAllPhotosKey] = '';
                }
                $aFilterMenu[$sMyPhotosKey] = 'my';
                $aFilterMenu[$sFriendPhotosKey] = 'friend';
            }

            if (Phpfox::getUserParam('photo.can_approve_photos')) {
                $iPendingTotal = $this->getPendingTotal();
                if ($iPendingTotal) {
                    $aFilterMenu[_p('pending_photos') . '<span id="photo_pending" class="pending count-item">' . ($iPendingTotal > 99 ? '99+' : $iPendingTotal) . '</span>'] = 'pending';
                }
            }

            if (Phpfox::getParam('photo.in_main_photo_section_show', 'photos') != 'albums' && Phpfox::getUserParam('photo.can_view_photo_albums')
            ) {
                $aFilterMenu[] = true;
                $aFilterMenu[$sAllAlbumsKey] = 'photo.albums';
                $aFilterMenu[$sMyAlbumsKey] = 'photo.albums.view_myalbums';
            }
        }
        Phpfox::getLib('template')->buildSectionMenu('photo', $aFilterMenu);
    }

    /**
     * @param        $iFeedId
     * @param null   $iLimit
     * @param string $sFeedTablePrefix
     *
     * @return array|int|string
     */
    public function getFeedPhotos($iFeedId, $iLimit = null, $sFeedTablePrefix = '')
    {
        $aFeed = Phpfox::getService('feed')->getFeed($iFeedId, $sFeedTablePrefix);
        if (!$aFeed) {
            return [];
        }

        if ($iLimit) {
            db()->limit($iLimit);
        }

        $conditions = ['AND ((pfeed.feed_id = ' . $iFeedId . ' AND pfeed.feed_time = 0 AND pfeed.feed_table = \'' . $sFeedTablePrefix . 'feed\') OR p.photo_id = ' . $aFeed['item_id'] . ')'];
        if ($aFeed['user_id'] != Phpfox::getUserId()) {
            Phpfox::getService('user')->get($aFeed['user_id']);
            $oUserObject = \Phpfox::getService('user')->getUserObject($aFeed['user_id']);
            $publicPrivacyValues = [0,6];
            if (isset($oUserObject->is_friend) && $oUserObject->is_friend) {
                $privacyValues = [1,2];
            } elseif (isset($oUserObject->is_friend_of_friend) && $oUserObject->is_friend_of_friend) {
                $privacyValues = [2];
            }
            $conditions[] = 'AND p.privacy IN(' . implode(',', !empty($privacyValues) ? array_merge($privacyValues, $publicPrivacyValues) : $publicPrivacyValues) . ')';
        }

        $aPhotos = db()
            ->select('p.photo_id, p.album_id, p.user_id, p.title, p.server_id, p.destination, p.mature')
            ->from(Phpfox::getT('photo'), 'p')
            ->leftJoin(Phpfox::getT('photo_feed'), 'pfeed', 'p.photo_id = pfeed.photo_id')
            ->where($conditions)
            ->order('p.photo_id DESC')
            ->execute('getSlaveRows');

        foreach ($aPhotos as $key => $aPhoto) {
            $aPhotos[$key]['html'] = '<span style="background-image: url(\'' .
                Phpfox::getLib('image.helper')->display([
                        'server_id'  => $aPhoto['server_id'],
                        'path'       => 'photo.url_photo',
                        'file'       => $this->getPhotoUrl($aPhoto),
                        'suffix'     => '_500',
                        'userid'     => $aPhoto['user_id'],
                        'return_url' => true
                    ]
                ) . '\')";></span>';
            $aPhotos[$key]['link'] = rtrim(Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null), '/') . '/feed_' . $iFeedId . '/';
            $aPhotos[$key]['class'] = '';
            if ($aPhoto['user_id'] != Phpfox::getUserId() && $aPhoto['mature'] != 0 && Phpfox::getUserParam([
                    'photo.photo_mature_age_limit' => [
                        '>',
                        (int)Phpfox::getUserBy('age')
                    ]
                ])
            ) {
                $aPhotos[$key]['class'] = 'photo-mature';
            }
        }
        return $aPhotos;
    }

    /**
     * @param int $iAlbumId
     *
     * @return array
     */
    public function getPhotos($iAlbumId = 0)
    {
        $sQuery = '';
        if ($iAlbumId > 0) {
            $sQuery .= ' AND photo.album_id = ' . (int)$iAlbumId;
        }
        $aPhotos = $this->_getPhotos($sQuery, 'DESC');

        foreach ($aPhotos as $key => $aPhoto) {
            $aPhotos[$key]['html'] = '<span style="background-image: url(\'' .
                Phpfox::getLib('image.helper')->display([
                        'server_id'  => $aPhoto['server_id'],
                        'path'       => 'photo.url_photo',
                        'file'       => $this->getPhotoUrl($aPhoto),
                        'suffix'     => '_500',
                        'userid'     => $aPhoto['user_id'],
                        'return_url' => true
                    ]
                ) . '\')";></span>';
            $aPhotos[$key]['link'] = Phpfox::permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
            if ($iAlbumId) {
                $aPhotos[$key]['link'] = rtrim($aPhotos[$key]['link'], '/') . '/album_' . $iAlbumId . '/';
            }
            $aPhotos[$key]['class'] = '';
            if ($aPhoto['user_id'] != Phpfox::getUserId() && $aPhoto['mature'] != 0 && Phpfox::getUserParam([
                    'photo.photo_mature_age_limit' => [
                        '>',
                        (int)Phpfox::getUserBy('age')
                    ]
                ])
            ) {
                $aPhotos[$key]['class'] = 'photo-mature';
            }
        }
        return $aPhotos;
    }

    /**
     * @param $sCondition
     * @param $sOrder
     *
     * @return array
     */
    private function _getPhotos($sCondition, $sOrder)
    {
        $aBrowseParams = [
            'module_id' => 'photo',
            'alias'     => 'photo',
            'field'     => 'photo_id',
            'table'     => Phpfox::getT('photo'),
            'hide_view' => ['pending', 'my']
        ];

        $this->search()->set([
                'type'    => 'photo',
                'filters' => [
                    'display' => ['type' => 'option', 'default' => '500'],
                    'sort'    => ['type' => 'option', 'default' => 'photo_id'],
                    'sort_by' => ['type' => 'option', 'default' => $sOrder]
                ]
            ]
        );
        if (!empty($sCondition)) {
            $this->search()->setCondition($sCondition);
        }
        $this->search()->browse()->params($aBrowseParams)->execute();
        $aPhotos = $this->search()->browse()->getRows();
        $this->search()->browse()->reset();

        return $aPhotos;
    }

    /**
     * @param $sDes
     *
     * @return null
     */
    public function cropMaxWidth($sDes, $bUseCdn = true)
    {
        $oImage = Phpfox::getLib('image');
        list($width, $height, ,) = @getimagesize($sDes);
        if ($width == 0 || $height == 0) {
            return null;
        }
        $iWidth = (int)Phpfox::getUserParam('photo.maximum_image_width_keeps_in_server');
        if ($iWidth < $width) {
            $bIsCropped = $oImage->createThumbnail($sDes, $sDes, $iWidth, $height, true, !$bUseCdn);
            if ($bIsCropped !== false) {
                $oImage->addMark($sDes);
            }
        }
    }

    /**
     * @description: check permission to view a photo
     *
     * @param int  $iId
     * @param bool $bReturnItem
     *
     * @return array|bool|int|string
     */
    public function canViewItem($iId, $bReturnItem = false)
    {
        if (!Phpfox::getUserParam('photo.can_view_photos')) {
            Phpfox_Error::set(_p('You don\'t have permission to {{ action }} {{ items }}.',
                ['action' => _p('view__l'), 'items' => _p('photos__l')]));
            return false;
        }

        $aPhoto = $this->getPhoto($iId);

        // No photo founds lets get out of here
        if (!isset($aPhoto['photo_id'])) {
            Phpfox_Error::set(_p('This {{ item }} cannot be found.', ['item' => _p('photo__l')]));
            return false;
        }

        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $aPhoto['user_id'])) {
            Phpfox_Error::set(_p('Sorry, this content isn\'t available right now'));
            return false;
        }

        if (Phpfox::isModule('privacy')) {
            if (!Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $aPhoto['user_id'],
                $aPhoto['privacy'], $aPhoto['is_friend'], true)
            ) {
                return false;
            }
        }

        if ($aPhoto['mature'] != 0) {
            if (Phpfox::getUserId()) {
                if ($aPhoto['user_id'] != Phpfox::getUserId()) {
                    if ($aPhoto['mature'] == 2 && Phpfox::getUserParam([
                            'photo.photo_mature_age_limit' => [
                                '>',
                                (int)Phpfox::getUserBy('age')
                            ]
                        ])
                    ) {
                        return Phpfox_Error::display(_p('sorry_this_photo_can_only_be_viewed_by_those_older_than_the_age_of_limit',
                            ['limit' => Phpfox::getUserParam('photo.photo_mature_age_limit')]));
                    }
                }
            } else {
                Phpfox_Error::set(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                    ['action' => _p('view__l'), 'item' => _p('photo__l')]));
                return false;
            }
        }
        if (!empty($aPhoto['module_id']) && $aPhoto['module_id'] != 'photo') {
            if ($aCallback = Phpfox::callback($aPhoto['module_id'] . '.getPhotoDetails', $aPhoto)) {
                if (Phpfox::isModule($aPhoto['module_id']) && Phpfox::hasCallback($aPhoto['module_id'],
                        'checkPermission')
                ) {
                    if (!Phpfox::callback($aPhoto['module_id'] . '.checkPermission', $aCallback['item_id'],
                        'photo.view_browse_photos')
                    ) {
                        Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
                        return false;
                    }
                }
            }
        }

        if (!$bReturnItem) {
            return true;
        }

        $aPhoto['bookmark_url'] = Phpfox::getLib('url')->permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);
        $aPhoto['photo_url'] = Phpfox::getLib('image.helper')->display([
            'server_id'  => $aPhoto['server_id'],
            'path'       => 'photo.url_photo',
            'file'       => $aPhoto['destination'],
            'suffix'     => '_1024',
            'return_url' => true
        ]);

        return $aPhoto;
    }

    /**
     * @param $aRow
     */
    public function getPermissions(&$aRow)
    {
        $aRow['can_view'] = true;
        if ($aRow['user_id'] != Phpfox::getUserId() && $aRow['mature'] != 0 && Phpfox::getUserParam([
                'photo.photo_mature_age_limit' => [
                    '>',
                    (int)Phpfox::getUserBy('age')
                ]
            ])
        ) {
            $aRow['can_view'] = false;
        }
        $aRow['canEdit'] = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('photo.can_edit_own_photo')) || Phpfox::getUserParam('photo.can_edit_other_photo'));
        $aRow['canDelete'] = $this->canDelete($aRow);
        $aRow['iSponsorInFeedId'] = Phpfox::isModule('feed') && (Phpfox::getService('feed')->canSponsoredInFeed('photo', $aRow['photo_id']) === true);

        $aRow['canSponsorInFeed'] = $aRow['canSponsor'] = $aRow['canPurchaseSponsor'] = false;
        if (Phpfox::isAppActive('Core_BetterAds') && Phpfox::getUserBy('profile_page_id') == 0) {
            $aRow['canSponsorInFeed'] = (Phpfox::isModule('feed') && (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('feed.can_purchase_sponsor')) || Phpfox::getUserParam('feed.can_sponsor_feed')) && Phpfox::getService('feed')->canSponsoredInFeed('photo', $aRow['photo_id']));
            $aRow['canSponsor'] = $aRow['view_id'] == 0 && (Phpfox::getUserParam('photo.can_sponsor_photo'));
            $bCanPurchaseSponsor = $this->canPurchaseSponsorItem($aRow['photo_id'], 'photo', 'photo', 'photo_id');
            $aRow['canPurchaseSponsor'] = $aRow['view_id'] == 0 && ($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('photo.can_purchase_sponsor')) && $bCanPurchaseSponsor;
        }
        $aRow['canApprove'] = (Phpfox::getUserParam('photo.can_approve_photos') && $aRow['view_id'] == 1);
        $aRow['canFeature'] = (Phpfox::getUserParam('photo.can_feature_photo') && $aRow['view_id'] == 0);

        $aRow['canSetCover'] = false;
        if ($aRow['module_id'] == 'pages' || $aRow['module_id'] == 'groups') {
            if ($aRow['module_id'] == 'pages' && Phpfox::getUserParam('pages.can_add_cover_photo_pages') && (Phpfox::getService('pages.facade')->getItems()->isAdmin($aRow['group_id']) || Phpfox::isAdmin() || Phpfox::getService('pages.facade')->getUserParam('can_edit_all_pages'))) {
                $aRow['canSetCover'] = true;
            } else if ($aRow['module_id'] == 'groups' && Phpfox::getUserParam('groups.pf_group_add_cover_photo') && (Phpfox::getService('groups.facade')->getItems()->isAdmin($aRow['group_id']) || Phpfox::isAdmin() || Phpfox::getService('groups.facade')->getUserParam('can_edit_all_pages'))) {
                $aRow['canSetCover'] = true;
            }
        }
        $aRow['hasPermission'] = ($aRow['canEdit'] || $aRow['canDelete'] || $aRow['canSponsorInFeed'] || $aRow['canSponsor'] || $aRow['canApprove'] || $aRow['canFeature'] || $aRow['canPurchaseSponsor'] || $aRow['canSetCover']);
    }

    private function canDelete($aRow)
    {
        $bCanDelete = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('photo.can_delete_own_photo')) || Phpfox::getUserParam('photo.can_delete_other_photos'));
        if (!$bCanDelete) {
            $aErrors = Phpfox_Error::get();

            if ($aRow['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages') && Phpfox::getService('pages')->isAdmin($aRow['group_id'])) {
                $bCanDelete = true; // is owner of page
            } else if ($aRow['module_id'] == 'groups' && Phpfox::isAppActive('PHPfox_Groups') && Phpfox::getService('groups')->isAdmin($aRow['group_id'])) {
                $bCanDelete = true; // is owner of group
            } else if ($aRow['type_id'] == 1 && Phpfox::getUserId() == $aRow['parent_user_id'] && $aRow['parent_user_id'] != 0 && $aRow['group_id'] == 0) {
                $bCanDelete = true; // is owner of profile
            }

            Phpfox_Error::reset();
            foreach ($aErrors as $sError) {
                Phpfox_Error::set($sError);
            }
        }

        return $bCanDelete;
    }

    /**
     * @return array
     */
    public function getPhotoPicSizes()
    {

        (($sPlugin = Phpfox_Plugin::get('photo.service_photo_getphotopicsizes')) ? eval($sPlugin) : false);

        return $this->_aPhotoPicSizes;
    }

    /**
     * @param bool $bInFeed
     * @param bool $bIsSchedule
     * @params array $aParams
     * @return array
     */
    public function getUploadParams($bInFeed = false, $bIsSchedule = false, $aParams = [])
    {
        $iMaxFileSize = Phpfox::getUserParam('photo.photo_max_upload_size');
        $iMaxFileSize = $iMaxFileSize > 0 ? $iMaxFileSize / 1024 : 0;
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);
        $iUploadedTotal = 0;
        if (isset($aParams['schedule_id']) && !empty($aParams['aFileData'])) {
            $iUploadedTotal = count($aParams['aFileData']);
        }
        $iMaxFiles = $this->getTotalPhotosPerUploading(null, false, $iUploadedTotal);

        $aEvents = $bInFeed ? [
            'sending'       => '$Core.Photo.dropzoneOnSendingInFeed',
            'success'       => '$Core.Photo.dropzoneOnSuccessInFeed',
            'queuecomplete' => '$Core.Photo.dropzoneOnCompleteInFeed',
            'removedfile'   => '$Core.Photo.dropzoneOnRemovedFileInFeed',
            'addedfile'     => '$Core.Photo.dropzoneOnAddedFileInFeed',
            'error'         => '$Core.Photo.dropzoneOnErrorInFeed'
        ] : [
            'sending'       => '$Core.Photo.dropzoneOnSending',
            'success'       => '$Core.Photo.dropzoneOnSuccess',
            'removedfile'   => '$Core.Photo.dropzoneOnRemovedFileInFeed',
            'queuecomplete' => '$Core.Photo.dropzoneOnComplete',
            'addedfile'     => '$Core.Photo.dropzoneOnAddedFile',
            'error'         => '$Core.Photo.dropzoneOnError'
        ];
        if ($bIsSchedule) {
            $aEvents['queuecomplete'] = '$Core.Photo.dropzoneOnCompleteInEditSchedule';
        }
        return [
            'max_size'          => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'upload_url'        => Phpfox::getLib('url')->makeUrl('photo.frame-drag-drop'),
            'component_only'    => true,
            'max_file'          => $iMaxFiles,
            'js_events'         => $aEvents,
            'upload_now'        => $bIsSchedule ? "true" : "false",
            'submit_button'     => $bInFeed ? '#activity_feed_submit' : '',
            'first_description' => _p('drag_n_drop_multi_photos_here_to_upload'),
            'upload_dir'        => Phpfox::getParam('photo.dir_photo'),
            'upload_path'       => Phpfox::getParam('photo.url_photo'),
            'update_space'      => true,
            'type_list'         => ['jpg', 'gif', 'png'],
            'on_remove'         => $bInFeed ? '' : 'photo.removePhoto',
            'style'             => '',
            'extra_description' => [
                _p('maximum_number_of_images_you_can_upload_each_time_is') . ' ' . $iMaxFiles
            ]
        ];
    }

    /**
     * Apply settings show photo of pages / groups
     *
     * @param $sPrefix
     *
     * @return string
     */
    public function getConditionsForSettingPageGroup($sPrefix = 'p')
    {
        $aModules = [];
        if (Phpfox::getParam('photo.display_photo_album_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (Phpfox::getParam('photo.display_photo_album_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }

        if (count($aModules)) {
            return ' AND (' . $sPrefix . '.module_id IN ("' . implode('","', $aModules) . '") OR ' . $sPrefix . '.module_id is NULL)';
        } else {
            return ' AND ' . $sPrefix . '.module_id is NULL';
        }
    }

    /**
     * Check if current user is admin of photo's parent item
     *
     * @param $iPhotoId
     *
     * @return bool|mixed
     */
    public function isAdminOfParentItem($iPhotoId)
    {
        $aPhoto = db()->select('photo_id, module_id, group_id')->from($this->_sTable)->where('photo_id = ' . (int)$iPhotoId)->execute('getRow');
        if (!$aPhoto) {
            return false;
        }
        if ($aPhoto['module_id'] && Phpfox::hasCallback($aPhoto['module_id'], 'isAdmin')) {
            return Phpfox::callback($aPhoto['module_id'] . '.isAdmin', $aPhoto['group_id']);
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
        if ($sPlugin = Phpfox_Plugin::get('photo.service_photo__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}