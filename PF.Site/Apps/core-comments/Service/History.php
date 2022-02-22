<?php

namespace Apps\Core_Comments\Service;

use Phpfox;


defined('PHPFOX') or exit('NO DICE!');

class History extends \Phpfox_Service
{
    protected $_sTable;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('comment_previous_versions');
    }

    public function addEditHistory($iCommentId, $aOldVersion, $aVals, $aAction)
    {
        $oFilter = Phpfox::getLib('parse.input');
        $sTxt = $oFilter->clean($aVals['text']);
        $bHasUpdate = false;

        if ($sTxt != $aOldVersion['text'] || !empty($aVals['attach_changed'])) {
            $bHasUpdate = true;
        }
        if ($bHasUpdate) {
            if (!$aOldVersion['update_time']) {
                //Add first version when edit first time
                $sUpdateText = $aOldVersion['text'];
                $sAttachmentText = '';
                if (isset($aOldVersion['extra_type'])) {
                    switch ($aOldVersion['extra_type']) {
                        case 'photo':
                            $sAttachmentText = 'added_photo_attachment';
                            break;
                        case 'sticker':
                            $sAttachmentText = 'added_sticker_attachment';
                            break;
                        case 'preview':
                            $sAttachmentText = 'added_link_attachment';
                            break;
                    }
                }
                $aInsert = [
                    'comment_id'      => $iCommentId,
                    'time_update'     => $aOldVersion['time_stamp'],
                    'user_id'         => $aOldVersion['user_id'],
                    'text'            => $oFilter->clean($sUpdateText),
                    'text_parsed'     => $oFilter->prepare($sUpdateText, false, ['comment' => $iCommentId]),
                    'attachment_text' => $sAttachmentText
                ];
                db()->insert($this->_sTable, $aInsert);
            }
            $sUpdateText = $aVals['text'];
            $sAttachmentText = '';
            if (count($aAction)) {
                switch ($aAction['type']) {
                    case 'photo':
                        if ($aAction['action'] == 'delete') {
                            $sAttachmentText = 'deleted_photo_attachment';
                        } else if ($aAction['action'] == 'add') {
                            $sAttachmentText = 'added_photo_attachment';
                        } else if ($aAction['action'] == 'update') {
                            $sAttachmentText = 'updated_photo_attachment';
                        }
                        break;
                    case 'sticker':
                        if ($aAction['action'] == 'delete') {
                            $sAttachmentText = 'deleted_sticker_attachment';
                        } else if ($aAction['action'] == 'add') {
                            $sAttachmentText = 'added_sticker_attachment';
                        } else if ($aAction['action'] == 'update') {
                            $sAttachmentText = 'updated_sticker_attachment';
                        }
                        break;
                    case 'preview':
                        if ($aAction['action'] == 'delete') {
                            $sAttachmentText = 'deleted_link_attachment';
                        } else if ($aAction['action'] == 'add') {
                            $sAttachmentText = 'added_link_attachment';
                        } else if ($aAction['action'] == 'update') {
                            $sAttachmentText = 'updated_link_attachment';
                        }
                        break;
                }
            }
            $aInsert = [
                'comment_id'      => $iCommentId,
                'time_update'     => $aVals['update_time'],
                'user_id'         => Phpfox::getUserId(),
                'text'            => $oFilter->clean($sUpdateText),
                'text_parsed'     => $oFilter->prepare($sUpdateText, false, ['comment' => $iCommentId]),
                'attachment_text' => $sAttachmentText
            ];
            db()->insert($this->_sTable, $aInsert);
            return 1;
        }
        return 2;
    }

    public function getEditHistory($iCommentId)
    {
        return db()->select('pv.*, ' . (Phpfox::getParam('core.allow_html') ? "pv.text_parsed" : "pv.text") . ' AS text,' . Phpfox::getUserField())
            ->from(':comment_previous_versions', 'pv')
            ->join(':user', 'u', 'u.user_id = pv.user_id')
            ->where('pv.comment_id = ' . (int)$iCommentId)
            ->order('pv.time_update DESC')
            ->execute('getSlaveRows');
    }
}