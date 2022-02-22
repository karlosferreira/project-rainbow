<?php

namespace Apps\Core_Messages\Service;

use Phpfox;
use Phpfox_Cache;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mail_thread');
    }

    /**
     * update message with array
     * @param $aUpdate
     * @param $iMessage
     * @return bool
     */
    public function updateMessage($aUpdate, $iMessage)
    {
        db()->update(Phpfox::getT('mail_thread_text'), $aUpdate, 'message_id = ' . (int)$iMessage . ' AND user_id = ' . Phpfox::getUserId());
        return true;
    }

    /**
     * execute action (delete, hide) for multi messages
     * @param $sAction
     * @param $sId
     * @param $iThreadId
     * @return bool
     * @throws \Exception
     */
    public function processActionMultiple($sAction, $sId, $iThreadId)
    {
        $aIds = explode(',', $sId);
        if (empty($aIds)) {
            return Phpfox_Error::set(_p('mail_do_action_with_invalid_messages'));
        }

        if ($sAction == 'delete' && !Phpfox::getUserParam('mail.can_delete_others_messages')) {
            return Phpfox_Error::set(_p('mail_do_not_have_permission_to_delete_message'));
        }

        $sId = trim($sId, ',');

        db()->update(Phpfox::getT('mail_thread_text'), ($sAction == 'delete' ? ['is_deleted' => 1] : ['is_show' => 0]), 'message_id IN (' . $sId . ')');
        $iLastMessageId = db()->select('message_id')
            ->from(Phpfox::getT('mail_thread_text'))
            ->where('thread_id = ' . (int)$iThreadId . ' AND is_show = 1 AND is_deleted = 0')
            ->order('time_stamp DESC')
            ->limit(1)
            ->execute('getSlaveField');
        db()->update(Phpfox::getT('mail_thread'), ['last_id' => (!empty($iLastMessageId) ? $iLastMessageId : 0)], 'thread_id = ' . $iThreadId);
        if ($sAction == 'delete') {
            db()->update(Phpfox::getT('mail_thread'), ['last_id_for_admin' => (!empty($iLastMessageId) ? $iLastMessageId : 0)], 'thread_id = ' . $iThreadId);
        }

        return true;
    }

    /**
     * execute action(delete, hide) for a message
     * @param $iId
     * @param $sAction
     * @param $iThreadId
     * @return bool
     */
    public function processAction($iId, $sAction, $iThreadId)
    {
        if ($sAction == 'delete') {
            db()->update(Phpfox::getT('mail_thread_text'), ['is_deleted' => 1], 'message_id = ' . (int)$iId);
        } else {
            db()->update(Phpfox::getT('mail_thread_text'), ['is_show' => ($sAction == 'hide' ? 0 : 1)], 'message_id = ' . (int)$iId);
        }
        $iLastMessageId = db()->select('message_id')
            ->from(Phpfox::getT('mail_thread_text'))
            ->where('thread_id = ' . (int)$iThreadId . ' AND is_show = 1 AND is_deleted = 0')
            ->order('time_stamp DESC')
            ->execute('getSlaveField');

        db()->update(Phpfox::getT('mail_thread'), ['last_id' => (!empty($iLastMessageId) ? $iLastMessageId : 0)], 'thread_id = ' . (int)$iThreadId);
        if ($sAction == 'delete') {
            db()->update(Phpfox::getT('mail_thread'), ['last_id_for_admin' => (!empty($iLastMessageId) ? $iLastMessageId : 0)], 'thread_id = ' . (int)$iThreadId);
        }
        return true;
    }

    /**
     * @param $aVals
     * @return array|bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function changeGroupTitle($aVals)
    {
        $aVals['title'] = Phpfox::getLib('parse.input')->clean($aVals['title']);
        $aVals['title'] = trim(strip_tags($aVals['title']));
        $aVals['title'] = str_replace('&nbsp;', '', $aVals['title']);
        $aVals['title'] = db()->escape($aVals['title']);
        if (empty($aVals['title']) || !is_string($aVals['title'])) {
            return Phpfox_Error::set(_p('Invalid title. Please type correctly'));
        }
        $aRow = db()->select('*')
            ->from(Phpfox::getT('mail_thread_group_title'))
            ->where('thread_id = ' . (int)$aVals['thread_id'])
            ->execute('getSlaveRow');
        if (empty($aRow)) {
            $aInsert = [
                'thread_id' => $aVals['thread_id'],
                'title' => $aVals['title']
            ];
            db()->insert(Phpfox::getT('mail_thread_group_title'), $aInsert);
        } else {
            db()->update(Phpfox::getT('mail_thread_group_title'), ['title' => $aVals['title']], 'thread_id = ' . (int)$aVals['thread_id']);
        }

        $cache = Phpfox_Cache::instance();
        $sCacheId = $cache->set('core_messages_group_title');
        $aTitleCaches = $cache->get($sCacheId);
        $aTitleCaches[(int)$aVals['thread_id']] = $aVals['title'];
        $cache->save($sCacheId, $aTitleCaches);

        return [true, $aVals['title']];
    }

    /**
     * execute action (delete, spam, un-spam) for a conversation
     * @param $sAction
     * @param $iThreadId
     * @return bool
     */
    public function applyConversationAction($sAction, $iThreadId)
    {
        if ($sAction == 'spam') {
            db()->update(Phpfox::getT('mail_thread_user'), ['is_spam' => 1], 'user_id = ' . Phpfox::getUserId() . ' AND thread_id = ' . (int)$iThreadId);
        } else if ($sAction == 'delete') {
            $iLastMessageId = db()->select('MAX(message_id) AS last_message_id')
                ->from(Phpfox::getT('mail_thread_text'))
                ->where('thread_id = ' . $iThreadId)
                ->execute('getSlaveField');
            if ((int)$iLastMessageId > 0) {
                db()->update(Phpfox::getT('mail_thread_user'), ['last_message_id_for_delete' => $iLastMessageId], 'user_id = ' . Phpfox::getUserId() . ' AND thread_id = ' . $iThreadId);
            }
        } else if ($sAction == 'un-spam') {
            db()->update(Phpfox::getT('mail_thread_user'), ['is_spam' => 0], 'user_id = ' . Phpfox::getUserId() . ' AND thread_id = ' . (int)$iThreadId);
        } else if ($sAction == 'leave') {
            db()->delete(Phpfox::getT('mail_thread_user'), ['user_id' => Phpfox::getUserId(), 'thread_id' => (int)$iThreadId]);
            db()->delete(Phpfox::getT('mail_thread_user_compare'), ['user_id' => Phpfox::getUserId(), 'thread_id' => (int)$iThreadId]);
        }
        return true;
    }


    /**
     * create new conversation
     * @param $aVals
     * @param bool $forceSend
     * @return int
     * @throws \Exception
     */
    public function add($aVals, $forceSend = false)
    {
        if (isset($aVals['copy_to_self']) && $aVals['copy_to_self'] == 1) {
            $aVals['to'][] = Phpfox::getUserId();
            unset($aVals['copy_to_self']);
            return $this->add($aVals);
        }

        $oFilter = Phpfox::getLib('parse.input');
        $bIsThreadReply = false;
        $aPastThread = $aOriginal = [];
        if (!isset($aVals['to']) && !empty($aVals['thread_id']) && !isset($aVals['claim_page'])) {
            $bIsThreadReply = true;
            $aPastThread = $this->database()->select('mt.*')
                ->from(Phpfox::getT('mail_thread'), 'mt')
                ->join(Phpfox::getT('mail_thread_user'), 'mtu', 'mtu.thread_id = mt.thread_id AND mtu.user_id = ' . Phpfox::getUserId())
                ->where('mt.thread_id = ' . (int)$aVals['thread_id'])
                ->execute('getSlaveRow');

            if (!isset($aPastThread['thread_id'])) {
                return Phpfox_Error::set(_p('unable_to_find_this_conversation'));
            }

            if (empty($aVals['attachment']) && empty($aVals['has_attachment']) && empty($oFilter->prepare($aVals['message']))) {
                return Phpfox_Error::set(_p('provide_message'));
            }

            $aThreadUsers = $this->database()->select('*')
                ->from(Phpfox::getT('mail_thread_user'))
                ->where('thread_id = ' . (int)$aPastThread['thread_id'])
                ->execute('getSlaveRows');

            foreach ($aThreadUsers as $aThreadUser) {
                if ($aThreadUser['user_id'] == Phpfox::getUserId()) {
                    continue;
                }
                $aOriginal[] = $aThreadUser['user_id'];
            }
        }

        $bChatGroup = false;
        if (!$bIsThreadReply) {
            $aOriginal = $aVals['to'];
            $aVals['to'] = $aVals['to'][0];
        }

        if (!$bIsThreadReply) {
            $aDetails = Phpfox::getService('user')->getUser($aVals['to'], Phpfox::getUserField() . ', u.email, u.language_id, u.user_group_id', (is_numeric($aVals['to']) ? false : true));
            if (!isset($aDetails['user_id'])) {
                return false;
            }

            if ($aVals['to'] == Phpfox::getUserId()) {
                return Phpfox_Error::set(_p('you_cannot_message_yourself'));
            }

            if (!isset($aVals['claim_page']) && !Phpfox::getService('mail')->canMessageUser($aDetails['user_id'], $bChatGroup)) {
                return Phpfox_Error::set(_p('unable_to_send_a_private_message_to_full_name_as_they_have_disabled_this_option_for_the_moment', ['full_name' => $aDetails['full_name']]));
            }

            // check if user can send message to non friends
            if (Phpfox::getUserParam('mail.restrict_message_to_friends') && !$forceSend) {
                (($sPlugin = Phpfox_Plugin::get('mail.service_process_add_1')) ? eval($sPlugin) : false);
                if (isset($sPluginError)) {
                    return false;
                }
                if (Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aVals['to']))
                    return Phpfox_Error::set(_p('you_can_only_message_your_friends'));
            }

            $aVals = array_merge($aVals, $aDetails);
        }
        if (count($aOriginal) > 1) {
            $bChatGroup = true;
        }
        $bHasAttachments = (Phpfox::getUserParam('mail.can_add_attachment_on_mail') && !empty($aVals['attachment']) && Phpfox::isModule('attachment'));

        Phpfox::getService('ban')->checkAutomaticBan((isset($aVals['subject']) ? $aVals['subject'] : '') . ' ' . $aVals['message']);
        $aVals['subject'] = (isset($aVals['subject']) ? $oFilter->clean($aVals['subject'], 255) : null);

        $aUserInsert = array_merge([Phpfox::getUserId()], $aOriginal);

        sort($aUserInsert, SORT_NUMERIC);

        $sHashId = '';
        if (!$bIsThreadReply) {
            $aMixedIds = $aUserInsert;
            if($bChatGroup) {
                $aMixedIds[] = (int)$aVals['thread_id'];
            }
            $sHashId = md5(implode('', $aMixedIds));
            $aPastThread = $this->database()->select('*')
                ->from(Phpfox::getT('mail_thread'))
                ->where('hash_id = \'' . $this->database()->escape($sHashId) . '\'')
                ->execute('getSlaveRow');
        }

        $aThreadUsers = $this->database()->select(Phpfox::getUserField() . ', u.email, u.language_id, u.user_group_id')
            ->from(Phpfox::getT('mail_thread_user'), 'mtu')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtu.user_id')
            ->where('mtu.user_id IN(' . implode(', ', $aUserInsert) . ')')
            ->group('u.user_id', true)
            ->execute('getSlaveRows');

        foreach ($aThreadUsers as $aThreadUser) {
            if (!isset($aVals['claim_page']) && $aThreadUser['user_id'] != Phpfox::getUserId() && !Phpfox::getService('mail')->canMessageUser($aThreadUser['user_id'], $bChatGroup)) {
                return Phpfox_Error::set(_p('unable_to_send_a_private_message_to_full_name_as_they_have_disabled_this_option_for_the_moment', ['full_name' => $aThreadUser['full_name']]));
            }
        }

        if (isset($aPastThread['thread_id'])) {
            $iId = $aPastThread['thread_id'];
            $this->database()->update(Phpfox::getT('mail_thread'), [
                'time_stamp' => PHPFOX_TIME
            ], 'thread_id = ' . (int)$iId
            );

            $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_sent_update' => '0', 'is_read' => '0', 'is_archive' => '0'], 'thread_id = ' . (int)$iId);
            $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_read' => '1'], 'thread_id = ' . (int)$iId . ' AND user_id = ' . Phpfox::getUserId());
        } else {
            $iId = $this->database()->insert(Phpfox::getT('mail_thread'), [
                    'hash_id' => $sHashId,
                    'time_stamp' => PHPFOX_TIME,
                    'is_group' => (int)$bChatGroup
                ]
            );

            if($bChatGroup) {
                //update new hash_id = hash(combination of sorted user ids + thread id)
                $aMixedIds = $aUserInsert;
                $aMixedIds[] = $iId;
                $sHashId = md5(implode('', $aMixedIds));
                $this->database()->update(Phpfox::getT('mail_thread'), ['hash_id' => $sHashId], 'thread_id='.$iId);
            }

            foreach ($aUserInsert as $iUserId) {
                $this->database()->insert(Phpfox::getT('mail_thread_user'), [
                        'thread_id' => $iId,
                        'is_read' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                        'is_sent' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                        'is_sent_update' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                        'user_id' => (int)$iUserId
                    ]
                );
                db()->insert(Phpfox::getT('mail_thread_user_compare'), [
                    'thread_id' => $iId,
                    'user_id' => $iUserId
                ]);
            }
        }

        $iTextId = $this->database()->insert(Phpfox::getT('mail_thread_text'), [
                'thread_id' => $iId,
                'time_stamp' => PHPFOX_TIME,
                'user_id' => Phpfox::getUserId(),
                'text' => $oFilter->prepare($aVals['message']),
                'is_mobile' => '0'
            ]
        );

        $this->database()->update(Phpfox::getT('mail_thread'), ['last_id' => (int)$iTextId], 'thread_id = ' . (int)$iId);
        $this->database()->update(Phpfox::getT('mail_thread'), ['last_id_for_admin' => (int)$iTextId], 'thread_id = ' . (int)$iId);

        // Send the user an email
        $bIsMailQueue = Phpfox::getParam('core.mail_queue');
        if (!$bIsMailQueue) {
            Phpfox::getLib('setting')->setParam('core.mail_queue', 1);
        }
        $sLink = Phpfox_Url::instance()->makeUrl('mail');
        
        foreach ($aThreadUsers as $aThreadUser) {
            if ($aThreadUser['user_id'] == Phpfox::getUserId()) {
                continue;
            }
            (($sPlugin = Phpfox_Plugin::get('mail.service_process_add_2')) ? eval($sPlugin) : false);
            if (isset($bPluginSkip) && $bPluginSkip === true) {
                continue;
            }
            Phpfox::getLib('mail')->to($aThreadUser['user_id'])
                ->subject(['mail.full_name_sent_you_a_message_on_site_title', ['full_name' => Phpfox::getUserBy('full_name'), 'site_title' => Phpfox::getParam('core.site_title')], false, null, $aThreadUser['language_id']])
                ->message(['mail.full_name_sent_you_a_message_no_subject', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'message' => $oFilter->clean(strip_tags(Phpfox::getLib('parse.bbcode')->cleanCode(str_replace(['&lt;', '&gt;'], ['<', '>'], $aVals['message'])))),
                        'link' => $sLink
                    ]
                    ]
                )
                ->notification('mail.new_message')
                ->send();
        }

        if (!$bIsMailQueue) {
            Phpfox::getLib('setting')->setParam('core.mail_queue', 0);
        }

        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iTextId);
            $this->database()->update(Phpfox::getT('mail_thread_text'), ['total_attachment' => Phpfox::getService('attachment')->getCountForItem($iTextId, 'mail')], 'message_id = ' . (int)$iTextId);
        }

        if (isset($aVals['forward_thread_id']) && !empty($aVals['forwards'])) {
            $bHasForward = false;
            $aForwards = explode(',', $aVals['forwards']);
            foreach ($aForwards as $iForward) {
                $iForward = (int)trim($iForward);
                if (empty($iForward)) {
                    continue;
                }

                $bHasForward = true;
                $this->database()->insert(Phpfox::getT('mail_thread_forward'), [
                        'message_id' => $iTextId,
                        'copy_id' => $iForward
                    ]
                );
            }

            if ($bHasForward) {
                $this->database()->update(Phpfox::getT('mail_thread_text'), ['has_forward' => '1'], 'message_id = ' . (int)$iTextId);
            }
        }

        (($sPlugin = Phpfox_Plugin::get('mail.service_process_add')) ? eval($sPlugin) : false);

        if (\Core\Route\Controller::$isApi) {
            return isset($iTextId) ? $iTextId : 0;
        }

        return $iId;
    }

    /**
     * Delicate function, physically deletes a message from the mail and mail_text tables
     * @param int $iId
     * @return true
     */
    public function adminDelete($iId)
    {
        Phpfox::getUserParam('admincp.has_admin_access', true);
        Phpfox::getUserParam('mail.can_delete_others_messages', true);

        $aMail = $this->database()->select('thread_id')
            ->from(Phpfox::getT('mail_thread'))
            ->where('thread_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aMail['thread_id'])) {
            return false;
        }

        $this->database()->delete(Phpfox::getT('mail_thread'), 'thread_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('mail_thread_text'), 'thread_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('mail_thread_user'), 'thread_id = ' . (int)$iId);

        return true;
    }


    /**
     * mark conversation read
     * @param $iThreadId
     */
    public function threadIsRead($iThreadId)
    {
        $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_read' => '1'], 'thread_id = ' . (int)$iThreadId . ' AND user_id = ' . Phpfox::getUserId());
    }

    /**
     * mark conversation read or unread
     * @param $iThreadId
     * @return bool|null
     * @throws \Exception
     */
    public function toggleThreadIsRead($iThreadId)
    {
        $aMail = $this->database()->select('*')
            ->from(Phpfox::getT('mail_thread_user'))
            ->where('thread_id = ' . (int)$iThreadId . ' AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveRow');

        if (!isset($aMail['thread_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_message_you_are_trying_to_mark_as_read_unread'));
        }

        $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_read' => ($aMail['is_read'] ? '0' : '1')], 'thread_id = ' . (int)$aMail['thread_id'] . ' AND user_id = ' . Phpfox::getUserId());
        return null;
    }

    /**
     * mark conversation archived
     * @param $iThreadId
     * @param int $iArchive
     */
    public function archiveThread($iThreadId, $iArchive = 1)
    {
        $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_read' => '1', 'is_archive' => (int)$iArchive], 'thread_id = ' . (int)$iThreadId . ' AND user_id = ' . Phpfox::getUserId());
    }

    /**
     * mark all conversation read
     */
    public function markAllRead()
    {
        $aMessages = $this->database()->select('thread_id')
            ->from(Phpfox::getT('mail_thread_user'))
            ->where('user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveRows');
        $aMailId = array_column($aMessages, 'thread_id');
        if (!empty($aMailId)) {
            $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_read' => '1'], 'thread_id IN (' . implode(',', $aMailId) . ')');
        }
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('mail.service_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}