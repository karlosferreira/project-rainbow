<?php

namespace Apps\Core_Comments\Service;

use Phpfox;
use Phpfox_Service;


class Tracking extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('comment_track');
    }

    /**
     * @param      $iUserId
     * @param      $sType
     * @param null $sFromTime
     * @param int  $iLimit
     *
     * @return array|int|string
     */
    public function getTracking($iUserId, $sType, $sFromTime = null, $iLimit = 100)
    {
        if ($sType == 'emoticon') {
            db()->select('ce.*, ')->join(':comment_emoticon', 'ce', 'ce.emoticon_id = ct.item_id');
        } else if ($sType == 'sticker') {
            db()->select('s.*, ')
                ->join(':comment_stickers', 's', 's.sticker_id = ct.item_id AND s.is_deleted = 0')
                ->join(':comment_sticker_set', 'ss', 'ss.set_id = s.set_id AND ss.is_active = 1');
        }
        return db()->select('ct.track_id')
            ->from($this->_sTable, 'ct')
            ->where('ct.track_type = \'' . $sType . '\' AND ct.user_id =' . (int)$iUserId . ($sFromTime != null ? ' AND ct.time_stamp >=' . (int)$sFromTime : ''))
            ->limit($iLimit)
            ->order('ct.time_stamp DESC')
            ->execute('getSlaveRows');
    }

    /**
     * @param $iCommentId
     * @param $iUserId
     * @param $iItemId
     * @param $sType
     *
     * @return bool
     */
    public function addTracking($iCommentId, $iUserId, $iItemId, $sType)
    {
        if (!in_array($sType, ['emoticon', 'sticker']) || !$iItemId) {
            return false;
        }
        $iTrack = db()->select('track_id')
            ->from($this->_sTable)
            ->where('item_id = ' . (int)$iItemId . ' AND user_id = ' . (int)$iUserId . ' AND track_type = \'' . $sType . '\'')
            ->execute('getField');
        if ($iTrack) {
            db()->update($this->_sTable, [
                'time_stamp' => PHPFOX_TIME,
                'comment_id' => $iCommentId
            ], 'track_id =' . (int)$iTrack);
        } else {
            db()->insert($this->_sTable, [
                'item_id'    => $iItemId,
                'user_id'    => $iUserId,
                'track_type' => $sType,
                'comment_id' => $iCommentId,
                'time_stamp' => PHPFOX_TIME
            ]);
        }
        return true;
    }
}