<?php

namespace Apps\Core_Comments\Service\Stickers;

use Phpfox;
use Phpfox_Error;
use Phpfox_Service;

class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('comment_sticker_set');
    }

    public function addStickerSet($aVals, $bIsUpdate = false)
    {
        if (empty($aVals['title'])) {
            Phpfox_Error::set(_p('sticker_set_title_is_required'));
        }
        if (!$bIsUpdate) {
            $aInsert = [
                'title'      => $this->preParse()->clean($aVals['title'], 255),
                'is_default' => 0,
                'is_active'  => 1,
            ];
            $iId = db()->insert($this->_sTable, $aInsert);
        } else {
            $iId = $aVals['id'];
            db()->update($this->_sTable, ['title' => $this->preParse()->clean($aVals['title'], 255)], 'set_id = ' . (int)$iId);
        }
        $this->cache()->removeGroup('comment');
        return $iId;
    }

    /**
     * @param $aParams
     * @param $iSetId
     *
     * @return bool
     */
    public function updateStickersOrdering($aParams, $iSetId)
    {
        $iCnt = 0;
        foreach ($aParams['values'] as $mKey => $mOrdering) {
            if ($iCnt == 0) {
                db()->update($this->_sTable, ['thumbnail_id' => $mKey], 'set_id =' . $iSetId);
            }
            $iCnt++;
            db()->update(':comment_stickers', ['ordering' => $iCnt],
                'sticker_id =' . $mKey . ' AND set_id =' . $iSetId);
        }
        $this->cache()->removeGroup('comment');
        return true;
    }

    /**
     * @param $iSetId
     * @param $iActive
     */
    public function toggleActiveStickerSet($iSetId, $iActive)
    {
        Phpfox::isUser(true);
        Phpfox::isAdmin(true);

        $iActive = (int)$iActive;
        $this->database()->update($this->_sTable, [
            'is_active' => ($iActive == 1 ? 1 : 0)
        ], 'set_id = ' . (int)$iSetId);

        $this->cache()->removeGroup('comment');
    }

    public function deleteStickerSet($iSetId)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);
        if (!$iSetId) {
            return false;
        }
        $aSet = Phpfox::getService('comment.stickers')->getForEdit($iSetId);
        if (!$aSet || $aSet['is_default'] || $aSet['view_only']) {
            return false;
        }
        //Remove set from user set
        db()->delete(':comment_user_sticker_set', 'set_id = ' . (int)$iSetId);
        //Remove sticker
        $aSticker = Phpfox::getService('comment.stickers')->getStickersBySet($iSetId);
        if ($aSticker) {
            foreach ($aSticker as $aStick) {
                $this->deleteSticker($aStick['sticker_id'], $aSet, false);
            }
        }
        db()->delete($this->_sTable, 'set_id =' . (int)$iSetId);
        $this->cache()->removeGroup('comment');
        return true;
    }

    public function deleteSticker($iStickerId, $aStickerSet = null, $bCheckThumbnail = true)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);
        $aSticker = db()->select('*')
            ->from(':comment_stickers')
            ->where('sticker_id =' . (int)$iStickerId)
            ->execute('getRow');
        if (!$aSticker) {
            return false;
        }
        db()->delete(':comment_track', 'track_type = \'sticker\' AND item_id = ' . (int)$iStickerId);
        if ($aStickerSet == null) {
            $aStickerSet = db()->select('ss.*')
                ->from($this->_sTable, 'ss')
                ->join(':comment_stickers', 's', 's.set_id = ss.set_id')
                ->where('s.sticker_id =' . (int)$iStickerId)
                ->execute('getRow');
        }
        if (!$aStickerSet) {
            return false;
        }
        //Update thumbnail for set
        if ($aStickerSet['thumbnail_id'] == $iStickerId && $bCheckThumbnail) {
            $iOtherSticker = db()->select('sticker_id')
                ->from(':comment_stickers')
                ->where('set_id = ' . $aStickerSet['set_id'] . ' AND sticker_id <>' . (int)$iStickerId)
                ->order('ordering ASC')
                ->execute('getField');
            db()->update($this->_sTable, ['thumbnail_id' => $iOtherSticker ? $iOtherSticker : 0],
                'set_id =' . $aStickerSet['set_id']);
        }
        //Mark sticker is deleted
        db()->update(':comment_stickers', ['is_deleted' => 1], 'sticker_id = ' . $aSticker['sticker_id']);

        db()->updateCounter('comment_sticker_set', 'total_sticker', 'set_id', $aStickerSet['set_id'], true);
        $this->cache()->removeGroup('comment');

        return true;
    }

    public function setDefaultSet($iSetId, $bUnMark = false)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);
        if (!$iSetId) {
            return false;
        }
        $iTotalDefault = Phpfox::getService('comment.stickers')->countDefaultSet();
        if (!$bUnMark) {
            if ($iTotalDefault >= 2) {
                return false;
            }
        } else {
            if ($iTotalDefault == 1) {
                return Phpfox_Error::set(_p('failed_need_at_least_one_default_sticker_set'));
            }
        }
        db()->update($this->_sTable, ['is_default' => ($bUnMark ? 0 : 1)], 'set_id =' . (int)$iSetId);
        $this->cache()->removeGroup('comment');
        return true;
    }

    public function updateMyStickerSet($iSetId, $iUserId, $bIsAdd)
    {
        if (!$iSetId) {
            return false;
        }
        if ($bIsAdd) {
            db()->insert(':comment_user_sticker_set', [
                'set_id'     => (int)$iSetId,
                'user_id'    => (int)$iUserId,
                'time_stamp' => PHPFOX_TIME
            ]);
            return true;
        } else {
            return db()->delete(':comment_user_sticker_set',
                'set_id =' . (int)$iSetId . ' AND user_id =' . (int)$iUserId);
        }
    }
}