<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Service\Album;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Album extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('music_album');
    }

    /**
     * @todo Add perms.
     *
     * @param mixed $iId
     *
     * @return mixed
     */
    public function getForEdit($iId)
    {
        $aAlbum = $this->database()->select('ma.*, mat.text, u.user_name')
            ->from($this->_sTable, 'ma')
            ->join(Phpfox::getT('music_album_text'), 'mat', 'mat.album_id = ma.album_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id')
            ->where('ma.album_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id'])) {
            return Phpfox_Error::display(_p('unable_to_find_the_album_you_want_to_edit'));
        }
        if (!empty($aAlbum['image_path'])) {
            $aAlbum['current_image'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aAlbum['server_id'],
                    'path'       => 'music.url_image',
                    'file'       => $aAlbum['image_path'],
                    'suffix'     => '_200_square',
                    'return_url' => true
                ]
            );
        }
        if (($aAlbum['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('music.can_edit_own_albums')) || Phpfox::getUserParam('music.can_edit_other_music_albums')) {
            return $aAlbum;
        }

        return Phpfox_Error::display(_p('unable_to_edit_this_album'));
    }

    public function getTracks($iUserId, $iId, $bCanViewAll = false)
    {
        static $aSongs = null;

        if ($aSongs === null) {
            $aSongs = \Phpfox::getService('music')->getSongs($iUserId, $iId, null, $bCanViewAll);
        }

        return $aSongs;
    }

    public function getForUpload($aCallback = null)
    {
        $sWhere = 'ma.view_id = 0 AND ma.user_id = ' . Phpfox::getUserId();
        if (isset($aCallback['module_id'])) {
            $sWhere .= ' AND ma.module_id = "' . $this->database()->escape($aCallback['module_id']) . '"';
        }
        if (isset($aCallback['item_id'])) {
            $sWhere .= ' AND ma.item_id = ' . (int)$aCallback['item_id'];
        }

        return $this->database()->select('ma.album_id, ma.name')
            ->from($this->_sTable, 'ma')
            ->where($sWhere)
            ->order('ma.name ASC')
            ->execute('getSlaveRows');
    }

    public function getForProfile($iUserId, $iLimit = 4)
    {
        return $this->database()->select('ma.name, ma.year, ma.image_path, ma.server_id, ma.total_track, ma.total_play, ma.time_stamp')
            ->from($this->_sTable, 'ma')
            ->where('ma.view_id = 0 AND ma.user_id = ' . (int)$iUserId)
            ->order('ma.time_stamp DESC')
            ->limit($iLimit)
            ->execute('getSlaveRows');
    }

    public function getAlbum($iAlbum)
    {
        if (Phpfox::isModule('like')) {
            $this->database()->select('lik.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'lik',
                    'lik.type_id = \'music_album\' AND lik.item_id = ma.album_id AND lik.user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('friend')) {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = ma.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        if (Phpfox::isModule('track')) {
            $sJoinQuery = Phpfox::isUser() ? 'pt.user_id = ' . Phpfox::getUserBy('user_id') : 'pt.ip_address = \'' . $this->database()->escape(Phpfox::getIp()) . '\'';
            $this->database()
                ->select('pt.item_id AS is_viewed, ')
                ->leftJoin(Phpfox::getT('track'), 'pt',
                'pt.item_id = ma.album_id AND pt.type_id=\'music_album\' AND ' . $sJoinQuery);

        }
        $aAlbum = $this->database()->select('ma.*, ' . (Phpfox::getParam('core.allow_html') ? 'mat.text_parsed' : 'mat.text') . ' AS text, u.user_name, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'ma')
            ->join(Phpfox::getT('music_album_text'), 'mat', 'mat.album_id = ma.album_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id')
            ->where('ma.album_id = ' . (int)$iAlbum)
            ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id'])) {
            return false;
        }

        $aAlbum['bookmark'] = \Phpfox_Url::instance()->permalink('music.album', $aAlbum['album_id'], $aAlbum['name']);

        return $aAlbum;
    }

    public function getNextSong($iAlbumId, $iLastSongId)
    {
        $aSongs = $this->database()->select('ms.song_id, ms.song_path')
            ->from(Phpfox::getT('music_song'), 'ms')
            ->where('ms.album_id = ' . (int)$iAlbumId)
            ->order('ms.ordering ASC, ms.time_stamp DESC')
            ->execute('getSlaveRows');

        $iNextSong = 0;
        foreach ($aSongs as $iKey => $aSong) {
            if ($aSong['song_id'] == $iLastSongId) {
                $iNextSong = ($iKey + 1);
            }
        }

        return (isset($aSongs[$iNextSong]) ? $aSongs[$iNextSong] : false);
    }

    public function getAlbums($aParentModule = null, $iLimit = 4, $sCond = '', $bForEdit = false)
    {
        $aCond = [];
        $aCond[] = $sCond;
        if (!$bForEdit) {
            $aCond[] = 'AND ma.view_id = 0 AND ma.privacy = 0 AND ma.total_track > 0';
            if (is_array($aParentModule)) {
                $aCond[] = 'AND ma.module_id = \'' . $this->database()->escape($aParentModule['module_id']) . '\' AND ma.item_id = ' . (int)$aParentModule['item_id'];
            } else if ($aParentModule === null) {
                $aCond[] = Phpfox::getService('music')->getConditionsForSettingPageGroup('ma');
            }
        }
        $aAlbums = $this->database()->select('ma.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'ma')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id')
            ->where($aCond)
            ->limit($iLimit)
            ->order('ma.time_stamp DESC')
            ->execute('getSlaveRows');
        return $aAlbums;
    }

    public function getFeaturedAlbums($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('music_album_featured');
        if (($sAlbumIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sAlbumIds = '';
            $sConds = 'ma.view_id = 0 AND ma.is_featured = 1 AND ma.total_track > 0';
            $sConds .= Phpfox::getService('music')->getConditionsForSettingPageGroup('ma');
            $aAlbumIds = $this->database()->select('ma.album_id')
                ->from($this->_sTable, 'ma')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id')
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
        $aAlbums = $this->database()->select('ma.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'ma')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id')
            ->where('ma.album_id IN (' . implode(',', $aAlbumIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        if (!is_array($aAlbums)) {
            return [];
        }

        shuffle($aAlbums);

        return $aAlbums;
    }

    /**
     * @param null $iUserId
     * @return int
     */
    public function getMyAlbumTotal($iUserId = null)
    {
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        $sWhere = 'user_id = ' . $iUserId;
        $aModules = ['user'];
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


    /**
     * @param $aRow
     */
    public function getPermissions(&$aRow)
    {
        $aRow['canEdit'] = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('music.can_edit_own_albums')) || user('music.can_edit_other_music_albums'));
        $aRow['canAddSong'] = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false)));
        $aRow['canDelete'] = $this->canDelete($aRow);

        $aRow['canSponsor'] = $aRow['canPurchaseSponsor'] = false;
        if (Phpfox::isAppActive('Core_BetterAds') && Phpfox::getUserBy('profile_page_id') == 0) {
            $aRow['canSponsor'] = (Phpfox::getUserParam('music.can_sponsor_album'));
            $bCanPurchaseSponsor = $this->canPurchaseSponsorItem($aRow['album_id'], 'music_album', 'music_album', 'album_id');
            $aRow['canPurchaseSponsor'] = ($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('music.can_purchase_sponsor_album') && $bCanPurchaseSponsor);
        }
        $aRow['canFeature'] = (Phpfox::getUserParam('music.can_feature_music_albums') && $aRow['view_id'] == 0);
        $aRow['hasPermission'] = ($aRow['canEdit'] || $aRow['canDelete'] || $aRow['canSponsor'] || $aRow['canFeature'] || $aRow['canPurchaseSponsor']);
    }

    public function canDelete($aRow)
    {
        $bCanDelete = (($aRow['user_id'] == Phpfox::getUserId() && user('music.can_delete_own_music_album')) || user('music.can_delete_other_music_albums'));
        if (!$bCanDelete && Phpfox::isModule($aRow['module_id'])) {
            if ($aRow['module_id'] == 'pages' && Phpfox::getService('pages')->isAdmin($aRow['item_id'])) {
                $bCanDelete = true; // is owner of page
            } else if ($aRow['module_id'] == 'groups' && Phpfox::getService('groups')->isAdmin($aRow['item_id'])) {
                $bCanDelete = true; // is owner of group
            }
        }
        return $bCanDelete;
    }

    public function getAll($iUserId, $sModule = false, $iItem = false)
    {
        (($sPlugin = Phpfox_Plugin::get('music.service_album_album_getall')) ? eval($sPlugin) : false);

        return db()->select('album_id, name')
            ->from($this->_sTable)
            ->where(($sModule === false ? 'module_id IS NULL AND item_id = 0 AND ' : 'module_id = \'' . $this->database()->escape($sModule) . '\' AND item_id = ' . (int)$iItem . ' AND ') . 'user_id = ' . (int)$iUserId)
            ->group('album_id', true)
            ->execute('getSlaveRows');
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
        $aAlbum = db()->select('album_id, module_id, item_id')->from($this->_sTable)->where('album_id = ' . (int)$iAlbumId)->execute('getRow');
        if (!$aAlbum) {
            return false;
        }
        if ($aAlbum['module_id'] && Phpfox::hasCallback($aAlbum['module_id'], 'isAdmin')) {
            return Phpfox::callback($aAlbum['module_id'] . '.isAdmin', $aAlbum['item_id']);
        }
        return false;
    }

    public function canCreateNewAlbum($iUserId = null, $bThrowError = true)
    {
        $iResult = Phpfox::getUserParam('music.can_add_music_album', $bThrowError);
        if (!$iResult) {
            return false;
        }
        $iMaxAlbum = (int)Phpfox::getUserParam('music.max_music_album_created');
        if (!$iMaxAlbum) {
            return true;
        }
        if (!$iUserId) {
            $iUserId = (int)Phpfox::getUserId();
        }
        $iTotalAlbum = $this->getMyAlbumTotal($iUserId);
        if ($iMaxAlbum <= $iTotalAlbum) {
            return $bThrowError ? Phpfox_Error::display(_p('you_have_reached_your_limit_you_are_currently_unable_to_create_new_music_album')) : false;
        }
        return true;
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
        if ($sPlugin = \Phpfox_Plugin::get('music.service_album_album__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}