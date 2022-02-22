<?php

namespace Apps\Core_Messages\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Template;
use Phpfox_Url;
use Phpfox_Ajax;

defined('PHPFOX') or exit('NO DICE!');

class Callback extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mail_thread');
    }

    public function addTrack($iId, $iUserId = null)
    {
        return $this->database()->insert(Phpfox::getT('track'), [
            'type_id' => 'mail',
            'item_id' => (int)$iId,
            'user_id' => ($iUserId === null ? Phpfox::getUserBy('user_id') : $iUserId),
            'ip_address' => '',
            'time_stamp' => PHPFOX_TIME
        ]);
    }

    public function getNotificationSettings()
    {
        return [
            'mail.new_message' => [
                'phrase' => _p('message_notifications'),
                'default' => 1
            ]
        ];
    }

    public function getProfileSettings()
    {
        return [
            'mail.send_message' => [
                'phrase' => _p('send_you_a_message'),
                'default' => '1',
                'anyone' => false
            ]
        ];
    }

    public function getNotificationLink($mId, $mTotal = null)
    {
        $sImage = '<img src="' . Phpfox_Template::instance()->getStyle('image', 'misc/email.png') . '" alt="" class="v_middle" />';
        if (is_array($mId) && $mTotal === null) {
            return _p('li_a_href_link_email_image_new_messages_messages_number_a_li', ['link' => Phpfox_Url::instance()->makeUrl('mail'), 'email_image' => $sImage, 'messages_number' => (isset($mId['mail']) ? $mId['mail'] : '0')]);
        } else {
            return '<li><a href="' . Phpfox_Url::instance()->makeUrl('mail') . '" class="js_nofitication_' . $mId . '">' . $sImage . ' ' . ($mTotal > 1 ? _p('total_new_messages', ['total' => $mTotal]) : _p('1_new_message')) . '</a></li>';
        }
    }

    public function getAttachmentField()
    {
        return [
            'mail_thread_text',
            'message_id'
        ];
    }

    public function getNotificationFeedSend($aRow)
    {
        return [
            'message' => _p('user_link_sent_you_a_message', [($aRow['user_id'] > 0 ? 'user' : 'user_link') => ($aRow['user_id'] > 0 ? $aRow : Phpfox::getParam('core.site_title'))]),
            'link' => Phpfox_Url::instance()->makeUrl('mail.view', ['id' => $aRow['item_id']])
        ];
    }

    public function getUserCountFieldSend()
    {
        return 'mail_new';
    }

    /**
     * Action to take when user cancelled their account
     * @param int $iUser
     */
    public function onDeleteUser($iUser)
    {
        $aThreads = $this->database()->select('thread_id')
            ->from(Phpfox::getT('mail_thread_user'))
            ->where('user_id = ' . (int)$iUser)
            ->execute('getSlaveRows');
        foreach ($aThreads as $aThread) {
            $iCount = $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('mail_thread_user'))
                ->where('thread_id = ' . (int)$aThread['thread_id'])
                ->execute('getSlaveField');
            if ($iCount > 2) {
                $this->database()->delete(Phpfox::getT('mail_thread_text'), 'user_id = ' . $iUser);
                $this->database()->delete(Phpfox::getT('mail_thread_user'), 'user_id = ' . $iUser);

                $aLastMess = $this->database()->select('message_id, user_id, time_stamp')
                    ->from(Phpfox::getT('mail_thread_text'))
                    ->where('thread_id = ' . (int)$aThread['thread_id'] . ' AND is_show = 1 AND is_deleted = 0')
                    ->order('time_stamp DESC')
                    ->execute('getSlaveRow');

                $this->database()->update(Phpfox::getT('mail_thread'), ['last_id' => !empty($aLastMess) ? $aLastMess['message_id'] : 0, 'time_stamp' => !empty($aLastMess) ? $aLastMess['time_stamp'] : 0], 'thread_id = ' . $aThread['thread_id']);
                if (!empty($aLastMess)) {
                    $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_read' => 1, 'is_sent' => 1, 'is_sent_update' => 1], 'thread_id = ' . $aThread['thread_id'] . ' AND user_id = ' . $aLastMess['user_id']);
                }

                $aLastMessForAdmin = $this->database()->select('message_id, user_id, time_stamp')
                    ->from(Phpfox::getT('mail_thread_text'))
                    ->where('thread_id = ' . (int)$aThread['thread_id'] . ' AND is_deleted = 0')
                    ->order('time_stamp DESC')
                    ->execute('getSlaveRow');

                $this->database()->update(Phpfox::getT('mail_thread'), ['last_id_for_admin' => !empty($aLastMessForAdmin) ? $aLastMessForAdmin['message_id'] : 0, 'time_stamp' => !empty($aLastMessForAdmin) ? $aLastMessForAdmin['time_stamp'] : 0], 'thread_id = ' . $aThread['thread_id']);

            } else {
                $this->database()->delete(Phpfox::getT('mail_thread_text'), 'thread_id = ' . $aThread['thread_id']);
                $this->database()->delete(Phpfox::getT('mail_thread_user'), 'thread_id = ' . $aThread['thread_id']);
                $this->database()->delete(Phpfox::getT('mail_thread'), 'thread_id = ' . $aThread['thread_id']);
            }
        }
    }

    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        return [
            'phrase' => _p('mesages_sent'),
            'value' => 0
        ];
    }

    public function getReportRedirect($iId)
    {
        return Phpfox_Url::instance()->makeUrl('admincp.mail.view', ['id' => $iId]);
    }

    public function updateCounterList()
    {
        $aList = [];

        $aList[] = [
            'name' => _p('update_mail_count'),
            'id' => 'mail-count'
        ];

        return $aList;
    }

    public function updateCounter($iId, $iPage, $iPageLimit)
    {
        if (db()->tableExists(Phpfox::getT('mail'))) {
            $iCnt = $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('user'))
                ->execute('getSlaveField');

            $aRows = $this->database()->select('u.user_id')
                ->from(Phpfox::getT('user'), 'u')
                ->limit($iPage, $iPageLimit, $iCnt)
                ->execute('getSlaveRows');

            foreach ($aRows as $aRow) {
                $iTotalNewMessages = $this->database()->select('COUNT(*)')
                    ->from(Phpfox::getT('mail'), 'm')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.owner_user_id')
                    ->where('m.viewer_user_id = ' . (int)$aRow['user_id'] . ' AND m.viewer_is_new = 1 AND m.viewer_type_id = 0')
                    ->execute('getSlaveField');

                $this->database()->update(Phpfox::getT('user_count'), ['mail_new' => $iTotalNewMessages], 'user_id = ' . (int)$aRow['user_id']);
            }

            return $iCnt;
        }
    }

    public function getGlobalNotifications()
    {
        $iTotal = Phpfox::getService('mail')->getUnseenTotal();

        if ($sPlugin = Phpfox_Plugin::get('mail.service_callback_getglobalnotifications')) {
            eval($sPlugin);
        }

        if ($iTotal > 0) {
            $iTotal = Phpfox::getService('core.helper')->shortNumberOver100($iTotal);
            Phpfox_Ajax::instance()->call('$(\'span#js_total_new_messages\').html(\'' . $iTotal . '\').css({display: \'block\'}).show();');
        }
    }

    /**
     * This function checks if the current user is either the sender or the receiver of iMailId
     * Used to validate who can download attachments
     * @param int $iMailId
     * @return bool
     */
    public function attachmentControl($iMailId)
    {
        $iThreadId = (int)$this->database()->select('thread_id')
            ->from(Phpfox::getT('mail_thread_text'))
            ->where('message_id = ' . (int)$iMailId)
            ->execute('getSlaveField');

        if ($iThreadId <= 0) {
            return false;
        }

        $iUserCheck = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_user'))
            ->where('thread_id = ' . (int)$iThreadId . ' AND user_id = ' . (int)Phpfox::getUserId())
            ->execute('getSlaveField');

        return ($iUserCheck > 0 ? true : false);
    }

    public function __call($sMethod, $aArguments)
    {
        if ($sPlugin = Phpfox_Plugin::get('mail.service_callback__call')) {
            eval($sPlugin);
            return null;
        }

        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}