<?php

namespace Apps\Core_Messages\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;
use Phpfox_Ajax;

defined('PHPFOX') or exit('NO DICE!');

class Helper extends Phpfox_Service
{
    public function hasAttachmentsOnSend($attachmentIds)
    {
        if (empty($attachmentIds) || !Phpfox::isModule('attachment')) {
            return false;
        }

        if (is_string($attachmentIds)) {
            $attachmentIds = explode(',', trim($attachmentIds, ','));
            $attachmentIds = array_filter($attachmentIds, function($value) {
                return is_numeric($value) && $value > 0;
            });
        }

        if (empty($attachmentIds)) {
            return false;
        }

        $realAttachmentIds = db()->select('attachment_id')
                            ->from(':attachment')
                            ->where([
                                'attachment_id' => ['in' => implode(',', $attachmentIds)],
                                'category_id' => 'mail',
                            ])->executeRows();
        $realAttachmentIds = array_column($realAttachmentIds,   'attachment_id');
        $sameAttachmentIds = array_intersect($attachmentIds, !empty($realAttachmentIds) ? $realAttachmentIds : []);

        return array_values($sameAttachmentIds);
    }

    /**
     * Check timestamp and return string name for this timestamp
     * @param int $iTimestamp
     * @return string
     */
    public function processDate($iTimestamp = PHPFOX_TIME)
    {
        if (Phpfox::getLib('date')->isToday($iTimestamp)) {
            return [Phpfox::getTime('m_d_Y', $iTimestamp), _p('today')];
        }

        if (Phpfox::getLib('date')->isToday($iTimestamp, 'yesterday')) {
            return [Phpfox::getTime('m_d_Y', $iTimestamp), _p('yesterday')];
        }

        if ((int)Phpfox::getTime('Y') > Phpfox::getTime('Y', $iTimestamp)) {
            return [Phpfox::getTime('m_d_Y', $iTimestamp), Phpfox::getTime('	F j, Y', $iTimestamp)];
        }

        $iCurrentWeek = (int)Phpfox::getTime('W');
        $iWeekOfTimestamp = (int)Phpfox::getTime('W', $iTimestamp);
        if ($iWeekOfTimestamp < $iCurrentWeek) {
            return [Phpfox::getTime('m_d_Y', $iTimestamp), Phpfox::getTime('F j', $iTimestamp)];
        } else {
            return [Phpfox::getTime('m_d_Y', $iTimestamp), Phpfox::getTime('D', $iTimestamp)];
        }

    }

    /**
     * Get default conversation status array
     * @return array
     */
    public function getDefaultFolder()
    {
        return [
            'all' => [
                'title' => _p('mail_all_mails'),
                'view' => ''
            ],
            'sent' => [
                'title' => _p('sent_messages'),
                'view' => 'sent'
            ],
            'unread' => [
                'title' => _p('mail_unread_mails'),
                'view' => 'unread'
            ],
            'trash' => [
                'title' => _p('archive'),
                'view' => 'trash'
            ],
            'spam' => [
                'title' => _p('mail_spam'),
                'view' => 'spam'
            ],
        ];
    }

    /**
     * Create conversation title for dynamic conversation load
     * @param $aThread
     * @param string $sView
     * @return string
     */
    public function createConversationTitle($aThread, $sView = '')
    {
        $sContentTitle = '<div class="core-messages__title-name fw-bold"> <span id="back-to-list-js" class="back-to-list hidden"><i class="ico ico-arrow-left-circle-o" aria-hidden="true"></i></span>' .
            (($aThread['is_group'] && $sView != 'spam' && $sView != 'trash') ? '<span class="js_group_conversation_title" id="js_group_title_' . $aThread['thread_id'] . '" data-id="' . $aThread['thread_id'] . '"><span class="js_group_conversation_title_text">' . $aThread['thread_name'] . '</span><input type="text" class="js_group_conversation_title_change" maxlength="128" value="' . $aThread['thread_name'] . '" style="display:none;"></span>' :
                '<span id="js_group_title_' . $aThread['thread_id'] . '">' . $aThread['thread_name'] . '</span>');
        $sContentTitle .= '</div>
                <div class="core-messages__title-action">
                    <div class="dropdown">
                        <span class="btn fz-16" data-toggle="dropdown">
                            <i class="ico ico-gear-o" aria-hidden="true"></i>
                        </span>
                        <ul class="dropdown-menu dropdown-menu-right">
                            ';
        if ($sView != 'spam' && $sView != 'trash') {
            $sContentTitle .= '<li>
                                <a onclick="$Core.jsConfirm({}, function () {
                                window.location.href = \'' . Phpfox_Url::instance()->makeUrl('mail', ['action' => 'spam', 'thread_id' => $aThread['thread_id']]) . '\';
                                }, function () {});return false;">
                                <i class="ico ico-flag-triangle" aria-hidden="true"></i>
                                ' . _p('mail_mark_as_spam') . '
                                </a>
                              </li>';
        }
        if ($sView == 'trash') {
            $sContentTitle .= '<li><a href="javascript:void(0)" onclick="$.ajaxCall(\'mail.markConversationUnarchive\',\'id=' . $aThread['thread_id'] . '\')"><i class="ico ico-inbox-o mr-1"></i>' . _p('mail_unarchive') . '</a></li>';
        }
        if ($sView == 'spam') {
            $sContentTitle .= '<li><a href="javascript:void(0)" onclick="$.ajaxCall(\'mail.markConversationUnspam\',\'id=' . $aThread['thread_id'] . '\')"><i class="ico ico-flag-triangle-o mr-1"></i>' . _p('mail_unspam') . '</a></li>';
        }

        if (!empty($aThread['is_group'])) {
            $sContentTitle .= '<li><a href="javascript:void(0)" onclick="tb_show(\'' . _p('mail_group_member') . '\',$.ajaxBox(\'mail.showGroupMembers\',\'height=300&width=400&id=' . $aThread['thread_id'] . '\'));"><i class="ico ico-user1-three mr-1"></i>' . _p('mail_group_member') . '</a></li>';
        }


        $sContentTitle .= '<li class="item_delete">
                                <a onclick="$Core.jsConfirm({}, function () {
                                window.location.href = \'' . Phpfox_Url::instance()->makeUrl('mail', ['action' => 'delete', 'thread_id' => $aThread['thread_id']]) . '\';
                                }, function () {});return false;">
                                    <i class="ico ico-trash-o" aria-hidden="true"></i>
                                    ' . _p('mail_delete_conversation') . '
                                </a>
                            </li>';

        if (!empty($aThread['is_group'])) {
            $sContentTitle .= '<li>
                                <a onclick="$Core.jsConfirm({}, function () {
                                window.location.href = \'' . Phpfox_Url::instance()->makeUrl('mail', ['action' => 'leave', 'thread_id' => $aThread['thread_id']]) . '\';
                                }, function () {});return false;">
                                <i class="ico ico-arrow-right" aria-hidden="true"></i>
                                ' . _p('mail_leave_conversation') . '
                                </a>
                            </li>';
        }

        $sContentTitle .= '</ul></div></div>';

        return $sContentTitle;
    }
}