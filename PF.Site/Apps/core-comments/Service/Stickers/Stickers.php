<?php

namespace Apps\Core_Comments\Service\Stickers;

use Phpfox;
use Phpfox_Service;


class Stickers extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('comment_sticker_set');
    }

    public function getForAdmin()
    {
        $sCacheId = $this->cache()->set('comment_sticker_set_admin');
        $this->cache()->group('comment', $sCacheId);
        if (!($aRows = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('ss.*,s.image_path,s.server_id')
                ->from($this->_sTable, 'ss')
                ->leftJoin(':comment_stickers', 's', 'ss.thumbnail_id = s.sticker_id')
                ->order('ss.ordering ASC')
                ->execute('getSlaveRows');
            $this->cache()->save($sCacheId, $aRows);
        }
        foreach ($aRows as $key => $aRow) {
            if (!empty($aRow['thumbnail_id'])) {
                $this->getStickerImage($aRows[$key]);
            }
        }
        return $aRows;
    }

    /**
     * @param $iId
     *
     * @return array|int|string
     */
    public function countStickers($iId)
    {
        return db()->select('total_sticker')
            ->from($this->_sTable)
            ->where('set_id =' . (int)$iId)
            ->execute('getField');
    }

    /**
     * @param $iId
     *
     * @return array|int|string
     */
    public function getForEdit($iId)
    {
        return db()->select('*')
            ->from($this->_sTable)
            ->where('set_id =' . (int)$iId)
            ->execute('getRow');
    }

    /**
     * @return array|int|string
     */
    public function countDefaultSet()
    {
        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('is_default = 1')
            ->execute('getField');
    }

    /**
     * @param $iUserId
     *
     * @return array|int|string
     */
    public function getRecentSticker($iUserId)
    {
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        //Get 40 recent stickers
        $aRecent = Phpfox::getService('comment.tracking')->getTracking($iUserId, 'sticker', null, 40);
        foreach ($aRecent as $iKey => $aSet) {
            $this->getStickerImage($aRecent[$iKey]);
        }
        return $aRecent;
    }

    /**
     * @param      $iUserId
     * @param null $iLimitSticker
     *
     * @return array|int|string
     */
    public function getAllStickerSetByUser($iUserId, $iLimitSticker = null)
    {
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        $aCache = storage()->get('comment_user_sticker_set_' . $iUserId);
        $bFirstTime = empty($aCache);
        $sCacheId = $this->cache()->set('comment_user_sticker_set_' . $iUserId);
        $this->cache()->group('comment', $sCacheId);
        if (($aSets = $this->cache()->get($sCacheId)) === false) {
            $iCnt = db()->select('COUNT(*)')
                ->from(':comment_user_sticker_set', 'uss')
                ->join($this->_sTable, 'ss', 'ss.set_id = uss.set_id')
                ->where('uss.user_id = ' . (int)$iUserId)
                ->execute('getField');
            //If first time, get default sticker
            if (!$iCnt && $bFirstTime) {
                $aSets = db()->select('ss.*, s.image_path, s.server_id')
                    ->from($this->_sTable, 'ss')
                    ->join(':comment_stickers', 's', 's.sticker_id = ss.thumbnail_id AND s.is_deleted = 0')
                    ->where('ss.is_default = 1 AND ss.is_active = 1')
                    ->order('ss.ordering ASC')
                    ->execute('getSlaveRows');
                storage()->set('comment_user_sticker_set_' . $iUserId, $iUserId);
            } else {
                $aSets = db()->select('ss.*, s.image_path, s.server_id')
                    ->from(':comment_user_sticker_set', 'uss')
                    ->join($this->_sTable, 'ss', 'ss.set_id = uss.set_id AND ss.is_active = 1')
                    ->join(':comment_stickers', 's', 's.sticker_id = ss.thumbnail_id AND s.is_deleted = 0')
                    ->where('uss.user_id = ' . (int)$iUserId)
                    ->order('uss.time_stamp DESC')
                    ->execute('getSlaveRows');
            }
            $this->cache()->set($sCacheId, $aSets);
        }
        if (count($aSets)) {
            foreach ($aSets as $iKey => $aSet) {
                if ($bFirstTime) {
                    db()->insert(':comment_user_sticker_set', [
                        'user_id' => $iUserId,
                        'set_id'  => $aSet['set_id']
                    ]);

                }
                if ($aSet['thumbnail_id']) {
                    $this->getStickerImage($aSets[$iKey]);
                }
                $aSets[$iKey]['stickers'] = $this->getStickersBySet($aSet['set_id'], $iLimitSticker);
                $aSets[$iKey]['is_my'] = true;
                $aSets[$iKey]['is_added'] = true;
                if (!count($aSets[$iKey]['stickers'])) {
                    unset($aSet[$iKey]);
                }
            }
        }
        return $aSets;
    }

    /**
     * @param      $iId
     * @param null $iLimit
     *
     * @return array|int|string
     */
    public function getStickersBySet($iId, $iLimit = null)
    {
        $sCacheId = $this->cache()->set('comment_set_stickers_' . $iId . ($iLimit !== null ? '_' . $iLimit : ''));
        $this->cache()->group('comment', $sCacheId);
        if (!($aStickers = $this->cache()->get($sCacheId))) {
            if ($iLimit != null) {
                db()->limit($iLimit);
            }
            $aStickers = db()->select('*')
                ->from(':comment_stickers')
                ->where('is_deleted = 0 AND set_id =' . (int)$iId)
                ->order('ordering ASC')
                ->execute('getSlaveRows');
            if ($aStickers) {
                foreach ($aStickers as $key => $aSticker) {
                    $this->getStickerImage($aStickers[$key]);
                }
            }
        }
        return $aStickers;
    }

    public function getStickerById($iId)
    {
        if (!$iId) {
            return false;
        }
        $aSticker = db()->select('*')
            ->from(':comment_stickers')
            ->where('sticker_id =' . (int)$iId)
            ->order('ordering ASC')
            ->execute('getRow');
        if ($aSticker) {
            $this->getStickerImage($aSticker);
        }
        return $aSticker;
    }

    public function getAllSticker($iUserId, $iLimitSticker = null)
    {
        $sCacheId = $this->cache()->set('comment_sticker_set_browse');
        $this->cache()->group('comment', $sCacheId);
        if (!($aRows = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('ss.*,s.image_path,s.server_id')
                ->from($this->_sTable, 'ss')
                ->join(':comment_stickers', 's', 'ss.thumbnail_id = s.sticker_id AND s.is_deleted = 0')
                ->where('ss.is_active = 1')
                ->order('ss.ordering ASC')
                ->execute('getSlaveRows');
            $this->cache()->save($sCacheId, $aRows);
        }
        if (count($aRows)) {
            foreach ($aRows as $iKey => $aSet) {
                $this->getStickerImage($aRows[$iKey]);
                $aRows[$iKey]['stickers'] = $this->getStickersBySet($aSet['set_id'], $iLimitSticker);
                $aRows[$iKey]['is_added'] = $this->checkIsAddedSet($aSet['set_id'], $iUserId);
            }
        }
        return $aRows;
    }

    public function checkIsAddedSet($iSetId, $iUserId)
    {
        return db()->select('COUNT(*)')
            ->from(':comment_user_sticker_set', 'uss')
            ->where('uss.user_id = ' . (int)$iUserId . ' AND uss.set_id = ' . (int)$iSetId)
            ->execute('getField');
    }

    public function getStickerSetById($iSetId, $iLimitSticker = null)
    {
        if (!$iSetId) {
            return false;
        }
        $aSet = db()->select('ss.*,s.image_path,s.server_id')
            ->from($this->_sTable, 'ss')
            ->join(':comment_stickers', 's', 'ss.thumbnail_id = s.sticker_id AND s.is_deleted = 0')
            ->where('ss.set_id =' . (int)$iSetId)
            ->order('ss.ordering ASC')
            ->execute('getRow');
        if ($aSet) {
            $this->getStickerImage($aSet);
            $aSet['stickers'] = $this->getStickersBySet($iSetId, $iLimitSticker);
        }
        return $aSet;
    }

    public function countActiveStickerSet()
    {
        $sCacheId = $this->cache()->set('comment_sticker_active_count');
        $this->cache()->group('comment', $sCacheId);
        $iCount = $this->cache()->get($sCacheId);
        if ($iCount === false) {
            $iCount = db()->select('COUNT(*) as total')
                ->from(':comment_sticker_set', 'uss')
                ->where('uss.is_active = 1')
                ->execute('getSlaveField');
            $this->cache()->save($sCacheId, $iCount);
        }
        return $iCount;
    }

    public function getStickerImage(&$aSticker, $noCanvas = false)
    {
        if (!empty($aSticker['image_path'])) {
            if ($aSticker['view_only']) {
                $aSticker['full_path'] = '<img src="' . Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-comments/assets/images/stickers/set_' . $aSticker['set_id'] . '/' . $aSticker['image_path'] . '" class="' . ($noCanvas ? '' : 'core_comment_gif') . '"/>';
            } else {
                $aSticker['full_path'] = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aSticker['server_id'],
                    'path'      => 'core.url_pic',
                    'file'      => 'comment/' . $aSticker['image_path'],
                    'suffix'    => '',
                    'class'     => ($noCanvas ? '' : 'core_comment_gif')
                ]);
            }
        }
    }
}