<?php

namespace Apps\phpFox_Shoutbox\Service;

use Apps\phpFox_Shoutbox\Service\Shoutbox as sb;
use Phpfox;

class Process
{
    private $_sTable = '';

    public function __construct()
    {
        $this->_sTable = sb::sbTable();
    }

    public function processLike($shoutboxId, $type = 'like')
    {
        if (empty($shoutboxId)) {
            return false;
        }
        if (!($aShoutbox = sb::get()->getShoutbox($shoutboxId))) {
            return false;
        }
        if (Phpfox::isModule('like')) {
            if ($type == 'like') {
                db()->insert(':like', [
                    'type_id' => 'shoutbox',
                    'item_id' => (int)$shoutboxId,
                    'user_id' => Phpfox::getUserId(),
                    'time_stamp' => PHPFOX_TIME
                ]);

                if ($aShoutbox['user_id'] != Phpfox::getUserId() && Phpfox::isModule('notification')) {
                    $count = db()->select('COUNT(*)')
                        ->from(':notification')
                        ->where('type_id = "shoutbox_like" AND item_id = ' . (int)$shoutboxId . ' AND user_id = ' . $aShoutbox['user_id'] . ' AND owner_user_id = ' . Phpfox::getUserId())
                        ->execute('getSlaveField');
                    if (!$count) {
                        Phpfox::getService('notification.process')->add('shoutbox_like', $shoutboxId, $aShoutbox['user_id']);
                    }
                }
            } else {
                db()->delete(Phpfox::getT('like'), 'type_id = "shoutbox" AND item_id = ' . (int)$shoutboxId . ' AND user_id = ' . Phpfox::getUserId());
            }
            db()->updateCount('like', 'type_id = \'shoutbox\' AND item_id = ' . (int)$shoutboxId . '', 'total_like', 'shoutbox', 'shoutbox_id = ' . (int)$shoutboxId);
            return $type == 'like' ? (int)$aShoutbox['total_like'] + 1 : (int)$aShoutbox['total_like'] - 1;
        }
        return false;
    }

    /**
     * @param $iShoutboxId
     * @param $sText
     * @param bool $bKeepQuoted
     * @return bool|resource
     */
    public function update($iShoutboxId, $sText, $bKeepQuoted = true)
    {
        $quotedShoutboxId = 0;
        //replace unnecessary quote
        $countMatch = 0;
        $checkQuoted = db()->select('COUNT(*)')
            ->from(Phpfox::getT('shoutbox_quoted_message'))
            ->where('shoutbox_id = ' . (int)$iShoutboxId)
            ->execute('getSlaveField');
        $quoteMatch = null;
        $sText = preg_replace_callback("/\[quote=([\d]+)\]/", function ($match) use (&$countMatch, $checkQuoted, $bKeepQuoted, &$quoteMatch) {
            $countMatch++;
            if (((!$checkQuoted || !$bKeepQuoted) && $countMatch > 1) || ($checkQuoted && $bKeepQuoted && $countMatch > 0)) {
                return "";
            }
            $quoteMatch = $match;
            return $match[0];
        }, $sText);
        $sText = trim($sText);
        //check and add quote id
        if (isset($quoteMatch[0]) && isset($quoteMatch[1])) {
            $sText = trim(str_replace($quoteMatch[0], '', $sText));
            $quotedShoutboxId = (int)$quoteMatch[1];
        }
        $quotedText = db()->select('text, user_id')
            ->from(sb::sbTable())
            ->where('shoutbox_id = ' . (int)$quotedShoutboxId)
            ->execute('getSlaveRow');
        if (!$bKeepQuoted && empty($quotedText) && empty($sText)) {
            return false;
        }
        if (!$bKeepQuoted || !empty($quotedShoutboxId)) {
            db()->delete(Phpfox::getT('shoutbox_quoted_message'), 'shoutbox_id = ' . (int)$iShoutboxId);
        }
        if (!empty($quotedText)) {
            db()->insert(Phpfox::getT('shoutbox_quoted_message'), ['shoutbox_id' => (int)$iShoutboxId, 'text' => trim($quotedText['text']), 'user_id' => (int)$quotedText['user_id']]);
        }
        return db()->update($this->_sTable, ['text' => trim($sText), 'is_edited' => 1], 'shoutbox_id = ' . (int)$iShoutboxId);
    }

    /**
     * @param $aVals
     * @return bool|int
     */
    public function add($aVals)
    {
        Phpfox::isUser(true);
        $iBloodControlTime = Phpfox::getUserParam('shoutbox.shoutbox_waiting_time');

        if ($iBloodControlTime > 0) {
            $iLatestMessageTimestamp = Phpfox::getLib('database')->select('timestamp')
                ->from(':shoutbox')
                ->where('user_id=' . Phpfox::getUserId())
                ->order('timestamp DESC')
                ->executeField();
            if ((PHPFOX_TIME - (int)$iLatestMessageTimestamp) <= $iBloodControlTime) {
                return _p('shoutbox_you_are_typing_too_fast');
            }
        }

        $aValidField = [
            'parent_module_id' => 'index',
            'parent_item_id' => 0,
            'user_id' => Phpfox::getUserId(),
            'text' => '',
            'timestamp' => PHPFOX_TIME
        ];
        foreach ($aValidField as $sKey => $value) {
            if (in_array($sKey, ['user_id', 'timestamp'])) {
                //Do not allow change these fields from form
                continue;
            }
            if (isset($aVals[$sKey])) {
                $aValidField[$sKey] = $aVals[$sKey];
            }
        }
        //Begin check permission
        $bCanShare = Phpfox::getUserParam('shoutbox.shoutbox_can_share');
        if ($aValidField['parent_module_id'] == 'pages') {
            if (!Phpfox::isAppActive('Core_Pages') || !Phpfox::getParam('shoutbox.shoutbox_enable_pages')) {
                return _p('shoutbox_you_cannot_send_message_at_this_time');
            }
            //In pages, check can share shoutbox
            if (!Phpfox::getService('pages')->hasPerm($aValidField['parent_item_id'], 'shoutbox.share_shoutbox')) {
                $bCanShare = false;
            }
        } elseif ($aValidField['parent_module_id'] == 'groups') {
            if (!Phpfox::isAppActive('PHPfox_Groups') || !Phpfox::getParam('shoutbox.shoutbox_enable_groups')) {
                return _p('shoutbox_you_cannot_send_message_at_this_time');
            }
            //In Groups, check can share shoutbox
            if (!Phpfox::getService('groups')->hasPerm($aValidField['parent_item_id'], 'shoutbox.share_shoutbox')) {
                $bCanShare = false;
            }
        } elseif ($aValidField['parent_module_id'] == 'index') {
            if (!Phpfox::getParam('shoutbox.shoutbox_enable_index')) {
                return _p('shoutbox_you_cannot_send_message_at_this_time');
            }
        }

        //end check permission
        if (!$bCanShare) {
            return false;
        }

        $quotedText = [];
        //check and add quote id
        preg_match("/\[quote=([\d]+)\]/", $aValidField['text'], $match, PREG_OFFSET_CAPTURE, 0);
        if (isset($match[0][0]) && $match[1][0]) {
            $aValidField['text'] = trim(str_replace($match[0][0], '', $aValidField['text']));
            $quotedShoutboxId = (int)$match[1][0];
            if($quotedShoutboxId) {
                $quotedText = db()->select('text, user_id')
                    ->from(sb::sbTable())
                    ->where('shoutbox_id = ' . (int)$quotedShoutboxId)
                    ->execute('getSlaveRow');
                if(!$quotedText) {
                    return _p('quote_not_found');
                }
            }
        }

        $iId = db()->insert(sb::sbTable(), $aValidField);
        if (!empty($quotedText)) {
            db()->insert(Phpfox::getT('shoutbox_quoted_message'), ['shoutbox_id' => $iId, 'text' => $quotedText['text'], 'user_id' => $quotedText['user_id']]);
        }
        return $iId;
    }

    /**
     * @param $aShoutbox
     * @return bool
     */
    public function delete($aShoutbox)
    {
        if (!$aShoutbox['canDeleteOwn'] && !$aShoutbox['canDeleteAll']) {
            return false;
        }
        db()->delete($this->_sTable, 'shoutbox_id=' . (int)$aShoutbox['shoutbox_id']);
        db()->delete(Phpfox::getT('shoutbox_quoted_message'), 'shoutbox_id = ' . (int)$aShoutbox['shoutbox_id']);
        (Phpfox::isModule('like') ? Phpfox::getService('like.process')->delete('shoutbox', (int)$aShoutbox['shoutbox_id'], 0, true) : null);
        (Phpfox::isModule('notification') ? Phpfox::getService('notification.process')->deleteAllOfItem([
            'shoutbox_like',
        ], (int)$aShoutbox['shoutbox_id']) : null);

    }

    /**
     * Delete old messages via cron
     */
    public function cronDeleteOldMessages()
    {
        $iDays = Phpfox::getParam('shoutbox.shoutbox_day_to_delete_messages');
        if ($iDays > 0) {
            $iTimeToDelete = PHPFOX_TIME - ($iDays * 24 * 3600);
            $aShoutboxs = db()->select('s.shoutbox_id')
                ->from($this->_sTable, 's')
                ->where('s.timestamp <= ' . (int)$iTimeToDelete)
                ->execute('getSlaveRows');
            foreach ($aShoutboxs as $aShoutbox) {
                $aShoutbox['canDeleteOwn'] = $aShoutbox['canDeleteAll'] = true;
                $this->delete($aShoutbox);
            }
        }
    }
}