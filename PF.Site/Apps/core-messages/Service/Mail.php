<?php

namespace Apps\Core_Messages\Service;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Cache;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Plugin;
use Phpfox_Search;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Mail extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mail_thread');
    }


    public function getGroupConversationMembers($iThreadId)
    {
        $aUsers = db()->select(Phpfox::getUserField())
            ->from(Phpfox::getT('mail_thread_user'), 'mtu')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtu.user_id')
            ->where('mtu.thread_id = ' . (int)$iThreadId)
            ->execute('getSlaveRows');
        return $aUsers;
    }

    /**
     * Check if this conversation is belonged to user and archived or spamed
     * @param $iConversationId
     * @param $iUserId
     * @return int
     */
    public function checkStatusConversationForUser($iConversationId, $iUserId, $bIsArchived = true)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_user'), 'mtu')
            ->join($this->_sTable, 'mt', 'mt.thread_id = mtu.thread_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtu.user_id')
            ->where('mtu.user_id = ' . (int)$iUserId . ' AND mt.thread_id = ' . (int)$iConversationId . ($bIsArchived ? ' AND mtu.is_archive = 1' : ' AND mtu.is_spam = 1'))
            ->execute('getSlaveField');
        return $iCnt;
    }

    /**
     * Get message infomation
     * @param $iMessageId
     * @return array
     */
    public function getMessage($iMessageId)
    {
        $aMessage = db()->select('*')
            ->from(Phpfox::getT('mail_thread_text'))
            ->where('is_show = 1 AND is_deleted = 0 AND message_id = ' . $iMessageId)
            ->execute('getSlaveRow');
        return $aMessage;
    }

    /**
     * Get conversations by conditions
     * @param $aConds
     * @param $iPage
     * @param $iSize
     * @return array
     */
    public function getConversationForAdmin($aConds, $iPage, $iSize)
    {
        $aRows = [];
        $iCnt = db()->select('COUNT(DISTINCT(mt.thread_id))')
            ->from(Phpfox::getT('mail_thread'), 'mt')
            ->join(Phpfox::getT('mail_thread_text'), 'mtt', 'mtt.thread_id = mt.thread_id')
            ->where(!empty($aConds) ? $aConds : '')
            ->order('mt.time_stamp DESC')
            ->execute('getSlaveField');
        if ($iCnt) {
            $aRows = db()->select('mt.*')
                ->from(Phpfox::getT('mail_thread'), 'mt')
                ->join(Phpfox::getT('mail_thread_text'), 'mtt', 'mtt.thread_id = mt.thread_id')
                ->where(!empty($aConds) ? $aConds : '')
                ->order('mt.time_stamp DESC')
                ->group('mt.thread_id')
                ->limit($iPage, $iSize)
                ->execute('getSlaveRows');
            foreach ($aRows as $iKey => $aRow) {
                $aRows[$iKey]['thread_name'] = $this->getConversationName($aRow['thread_id'], $aRow['is_group'], ' & ');
            }
        }
        return [$iCnt, $aRows];
    }

    /**
     * @param int $threadId
     * @param bool $isGroup
     * @param null $users
     * @param string $separator
     * @return array|int|string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getConversationName($threadId = 0, $isGroup = null, $separator = ', ', $users = null)
    {
        $threadName = '';
        $parseOutput = Phpfox::getLib('parse.output');
        if ($isGroup == null) {
            $thread = $this->database()->select('mt.is_group')
                ->from(Phpfox::getT('mail_thread'), 'mt')
                ->where('mt.thread_id = ' . (int)$threadId)
                ->execute('getSlaveRow');

            if (isset($thread['is_group'])) {
                $isGroup = $thread['is_group'];
            }
        }

        if ($isGroup) {
            $title = $this->getGroupTitle($threadId);
            $threadName = !empty($title) ? $title : $this->getDefaultGroupTitle($threadId, $separator);
        } else {
            if ($users) {
                $iCntUser = 0;
                $iCut = 0;
                $totalUser = count($users);
                foreach ($users as $user) {
                    $sMore = $parseOutput->shorten($parseOutput->clean($user['full_name']), 30, '...');
                    if (strlen($threadName . $sMore) < 45) {
                        $threadName .= $sMore;
                        $iCut++;
                    }
                    $iCntUser++;
                    if ($iCntUser == $iCut && $totalUser > 1) {
                        $threadName .= $separator;
                    }
                }
                if ($iCntUser > $iCut) {
                    if (Phpfox::isPhrase('mail.and_number_other')) {
                        $threadName .= ' ' . _p('and_number_other', ['number' => ($iCntUser - $iCut)]) . ((($iCntUser - $iCut) > 1) ? 's' : '');
                    } else {
                        $threadName .= ' and ' . ($iCntUser - $iCut) . ' other' . ((($iCntUser - $iCut) > 1) ? 's' : '');
                    }
                }

            } else {
                $users = db()->select('u.full_name, u.user_id')
                    ->from(Phpfox::getT('mail_thread_user'), 'mtu')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtu.user_id')
                    ->where('mtu.thread_id = ' . $threadId)
                    ->execute('getSlaveRows');
                foreach ($users as $user) {
                    $threadName .= $parseOutput->clean($user['full_name']) . $separator;
                }
            }
            $threadName = rtrim(rtrim($threadName), $separator);
        }

        return $threadName;
    }

    /**
     * Get messages by conditions
     * @param $aConds
     * @param $iPage
     * @param $iSize
     * @return array
     */
    public function getMessagesForAdmin($aConds, $iPage, $iSize)
    {
        $aMessages = [];
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_text'), 'mtt')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtt.user_id')
            ->where($aConds)
            ->execute('getSlaveField');
        if ($iCnt) {
            $aMessages = db()->select('mtt.*, u.full_name')
                ->from(Phpfox::getT('mail_thread_text'), 'mtt')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtt.user_id')
                ->where($aConds)
                ->order('mtt.time_stamp DESC')
                ->limit($iPage, $iSize)
                ->execute('getSlaveRows');

            if (!empty($aMessages)) {
                foreach ($aMessages as $iKey => $aMail) {
                    if ($aMail['total_attachment'] > 0 && Phpfox::isModule('attachment')) {
                        list(, $aAttachments) = Phpfox::getService('attachment')->get(['AND attachment.item_id = ' . $aMail['message_id'] . ' AND attachment.category_id = \'mail\' AND is_inline = 0'], 'attachment.attachment_id DESC', false);

                        $aMessages[$iKey]['attachments'] = $aAttachments;
                    }

                    $aMessages[$iKey]['forwards'] = [];
                    if ($aMail['has_forward']) {
                        $aMessages[$iKey]['forwards'] = $this->database()->select('mtt.*, ' . Phpfox::getUserField())
                            ->from(Phpfox::getT('mail_thread_forward'), 'mtf')
                            ->join(Phpfox::getT('mail_thread_text'), 'mtt', 'mtt.message_id = mtf.copy_id')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtt.user_id')
                            ->where('mtf.message_id = ' . $aMail['message_id'])
                            ->execute('getSlaveRows');
                    }
                }
            }
        }
        return [$iCnt, $aMessages];
    }

    /**
     * Build Friends and Customlist for suggestion search
     * @return array
     */
    public function buildFriendsAndCustomList()
    {
        $aFriends = Phpfox::getService('friend')->getFromCache(false, false, false, null, true);
        $aList = Phpfox::getService('mail.customlist')->get();
        return [$aFriends, $aList];
    }


    /**
     * Get conversations of User
     * @param $iUserId
     * @param bool $bRemoveFieldName
     * @return array
     */
    public function getAllConversationOfUser($iUserId, $bRemoveFieldName = false)
    {
        $aRows = db()->select('*')
            ->from(Phpfox::getT('mail_thread_user'))
            ->where('user_id = ' . $iUserId)
            ->group('thread_id')
            ->execute('getSlaveRows');
        $aResult = [];
        foreach ($aRows as $aRow) {
            $aResult[$aRow['thread_id']] = $aRow;
        }
        if ($bRemoveFieldName) {
            $aIds = array_column($aResult, 'thread_id');
            return [$aResult, $aIds];
        }
        return $aResult;


    }

    /**
     * Get default group name if the group has not a new name yet
     * @param $iThreadId
     * @param string $separator
     * @return string
     */
    public function getDefaultGroupTitle($iThreadId, $separator = ', ')
    {
        $parseOutput = Phpfox::getLib('parse.output');
        $aUsers = $this->database()->select('th.is_read, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('mail_thread_user'), 'th')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
            ->where('th.thread_id = ' . (int)$iThreadId . ' AND th.user_id != ' . Phpfox::getUserId())
            ->execute('getSlaveRows');
        if (count($aUsers) < 2) {
            if(!empty($aUsers)){
                return $parseOutput->shorten($parseOutput->clean($aUsers[0]['full_name']), 30, '...');
            } else {
                $sUserFullName = $this->database()->select('full_name')
                    ->from(Phpfox::getT('user'))
                    ->where('user_id='.Phpfox::getUserId())
                    ->executeField();
                return $parseOutput->shorten($parseOutput->clean($sUserFullName), 30, '...');
            }
        }
        $sThreadName = '';
        $iCntUser = 0;
        $iCut = 0;

        foreach ($aUsers as $aUser) {
            $sMore = $parseOutput->shorten($parseOutput->clean($aUser['full_name']), 30, '...');
            if (strlen($sThreadName . $sMore) < 45) {
                $sThreadName .= $sMore;
                $iCut++;
            }
            $iCntUser++;
            if ($iCntUser == $iCut && count($aUsers) > 1) {
                $sThreadName .= $separator;
            }
        }

        if ($iCntUser > $iCut) {
            if (\Core\Lib::phrase()->isPhrase('mail.and_number_other')) {
                $sThreadName .= ' ' . _p('and_number_other', ['number' => ($iCntUser - $iCut)]) . ((($iCntUser - $iCut) > 1) ? 's' : '');
            } else {
                $sThreadName .= ' and ' . ($iCntUser - $iCut) . ' other' . ((($iCntUser - $iCut) > 1) ? 's' : '');
            }
        } else {
            $sThreadName = rtrim(rtrim($sThreadName), $separator) . _p(' and you');
        }
        return $sThreadName;

    }

    /**
     * Get group title if the user has changed default group name
     * @param $iThreadId
     * @return array|int|string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getGroupTitle($iThreadId)
    {
        $cache = Phpfox_Cache::instance();
        $sCacheId = $cache->set('core_messages_group_title');
        $aTitleCaches = $cache->get($sCacheId);
        if (!empty($aTitleCaches[$iThreadId])) {
            return $aTitleCaches[$iThreadId];
        }
        $sTitle = db()->select('title')
            ->from(Phpfox::getT('mail_thread_group_title'))
            ->where('thread_id = ' . (int)$iThreadId)
            ->execute('getSlaveField');
        $aTitleCaches[$iThreadId] = $sTitle;
        $cache->save($sCacheId, $aTitleCaches);
        return $sTitle;
    }

    /**
     * List messsages by day
     * @param $aMessages
     * @param bool $bGetTime
     * @return array
     */
    public function listDateForMessages($aMessages, $bGetTime = false)
    {
        $aTimes = [];
        $iTimeCnt = 0;
        foreach ($aMessages as $iKey => $aMessage) {
            $aDate = Phpfox::getService('mail.helper')->processDate($aMessage['time_stamp']);
            if (empty($aTimes)) {
                $aMessages[$iKey]['message_time_list'] = $aDate;
                $aTimes[$aDate[0]] = 1;
                $iTimeCnt++;
            } else {
                $bCheck = 0;
                foreach ($aTimes as $sTime => $iValue) {

                    if ($sTime == $aDate[0]) {
                        $bCheck = 1;
                        break;
                    }
                }
                if (!$bCheck) {
                    $aMessages[$iKey]['message_time_list'] = $aDate;
                    $aTimes[$aDate[0]] = 1;
                    $iTimeCnt++;
                }
            }
            $aMessages[$iKey]['timestamp_parsed'] = Phpfox::getTime(Phpfox::getParam('core.conver_time_to_string'),
                $aMessage['time_stamp']);
        }
        return ($bGetTime ? [$aMessages, $aTimes] : $aMessages);
    }


    /**
     * Get conversation infomation by hasd id which is unique
     * @param $sHashId
     * @return array
     */
    public function getMailThreadByHash($sHashId)
    {
        $aThread = $this->database()->select('*')
            ->from(Phpfox::getT('mail_thread'))
            ->where('hash_id = \'' . $this->database()->escape($sHashId) . '\'')
            ->execute('getSlaveRow');
        return $aThread;
    }

    /**
     * Get content of conversation
     * @param $iThreadId
     * @return string
     */
    public function getChatContentDefault($iThreadId)
    {
        Phpfox::getComponent('mail.thread', [
            'id' => $iThreadId
        ], 'controller');
        $sContent = Phpfox_Ajax::instance()->getContent(false);
        return $sContent;
    }

    /**
     * Get content of compose message
     * @return string
     */
    public function getMailComposeContent($bIsRealCustomlistMessage = false, $iCustomlistId)
    {
        $aParams = [];
        if ($bIsRealCustomlistMessage && !empty($iCustomlistId)) {
            $aParams['customlist_id'] = (int)$iCustomlistId;
        }
        Phpfox::getComponent('mail.compose', $aParams, 'controller');
        $sContent = Phpfox_Ajax::instance()->getContent(false);
        return $sContent;
    }

    /**
     * This function validates the permission to send a PM to another user, it
     * takes into account the user group setting: mail.can_compose_message
     * the privacy setting by the receiving user: mail.send_message
     * and if the receiving user is blocked by the sender user or viceversa
     * Also checks on other user group based restrictions
     * @param int $iUser The user id of the member trying to send a message
     * @return boolean true if its ok to send the message, false otherwise
     */
    public function canMessageUser($iUser, $bIsGroup = null)
    {
        (($sPlugin = Phpfox_Plugin::get('mail.service_mail_canmessageuser_1')) ? eval($sPlugin) : false);
        if (isset($bCanOverrideChecks)) {
            return true;
        }
        // 1. user group setting:
        if (!Phpfox::getUserParam('mail.can_compose_message')) {
            return false;
        }
        // 2. Privacy setting check if enable setting "Check privacy settings of recipients".
        if (Phpfox::getParam('mail.disallow_select_of_recipients')) {
            $iPrivacy = $this->database()->select('user_value')
                ->from(Phpfox::getT('user_privacy'))
                ->where('user_id = ' . (int)$iUser . ' AND user_privacy = "mail.send_message"')
                ->execute('getSlaveField');

            if (!empty($iPrivacy) && !Phpfox::isAdmin()) {
                if ($iPrivacy == 4) { // No one
                    return false;
                } else if ($iPrivacy == 1 && !Phpfox::isUser()) { // trivial case
                    return false;
                } else if ($iPrivacy == 2 && Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $iUser, false)) { // friends only
                    return false;
                }
            }
        }

        if(empty($bIsGroup)){
            // 3. Blocked users
            if (!Phpfox::isAdmin() && (Phpfox::getService('user.block')->isBlocked(Phpfox::getUserId(), $iUser) > 0 || Phpfox::getService('user.block')->isBlocked($iUser, Phpfox::getUserId()) > 0)) {
                return false;
            }

            // 4. User group setting (different from check 2 since that is user specific)
            if ((Phpfox::getUserParam('mail.restrict_message_to_friends') == true)
                && (Phpfox::isModule('friend') && Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $iUser, false) == false)) {
                if ($iUser != Phpfox::getUserId()) {
                    return false;
                }
            }
        }
        // then its ok
        return true;
    }

    /**
     * Get conversation for panel by conditions
     * @param array $aConds
     * @param string $sSort
     * @param string $iPage
     * @param string $iLimit
     * @param bool $bIsSentbox
     * @param bool $bIsTrash
     * @param bool $bIsSpam
     * @return array
     */
    public function get(
        $aConds = [],
        $sSort = 'm.time_updated DESC',
        $iPage = '',
        $iLimit = '',
        $bIsSentbox = false,
        $bIsTrash = false,
        $bIsSpam = false
    )
    {
        $aRows = [];
        $aInputs = [
            'unread',
            'read'
        ];

        $iArchiveId = ($bIsTrash ? 1 : 0);
        $iSpam = ($bIsSpam ? 1 : 0);
        $bIsTextSearch = false;
        if (!defined('PHPFOX_IS_PRIVATE_MAIL')) {
            $this->database()->select('COUNT(*)');
            if ($bIsSentbox) {
                $this->database()->where('th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = ' . (int)$iArchiveId . ' AND th.is_spam = ' . (int)$bIsSpam . ' AND th.is_sent = 1 AND (t.last_id > 0 AND (th.last_message_id_for_delete < t.last_id))');
            } else {
                $this->database()->where('th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = ' . (int)$iArchiveId . ' AND th.is_spam = ' . (int)$bIsSpam . ' AND (t.last_id > 0 AND (th.last_message_id_for_delete < t.last_id))');
            }
        } else {
            $this->database()->select('COUNT(DISTINCT t.thread_id)');
            $aNewCond = [];
            if (count($aConds)) {
                foreach ($aConds as $sCond) {
                    if (preg_match('/AND mt.text LIKE \'%(.*)%\'/i', $sCond, $aTextMatch)) {
                        $bIsTextSearch = true;
                        $aNewCond[] = $sCond;
                    }
                }
            }
        }

        if ($bIsTextSearch) {
            $iCnt = $this->database()->from(Phpfox::getT('mail_thread_text'), 'mt')
                ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = mt.thread_id')
                ->where(isset($aNewCond) ? $aNewCond : "true")
                ->execute('getSlaveField');
        } else {
            $iCnt = $this->database()->from(Phpfox::getT('mail_thread_user'), 'th')
                ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = th.thread_id')
                ->execute('getSlaveField');
        }

        if ($iCnt) {
            (($sPlugin = Phpfox_Plugin::get('mail.service_mail_get')) ? eval($sPlugin) : false);

            if (!defined('PHPFOX_IS_PRIVATE_MAIL')) {
                if ($bIsSentbox) {
                    $this->database()->where('th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = ' . (int)$iArchiveId . ' AND th.is_spam = ' . (int)$bIsSpam . ' AND th.is_sent = 1 AND (t.last_id > 0 AND (th.last_message_id_for_delete < t.last_id))');
                } else {
                    $this->database()->where('th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = ' . (int)$iArchiveId . ' AND th.is_spam = ' . (int)$bIsSpam . ' AND (t.last_id > 0 AND (th.last_message_id_for_delete < t.last_id))');
                }
            } else {
                if (isset($aNewCond) && count($aNewCond)) {
                    $this->database()->where($aNewCond);
                }
            }

            if ($bIsTextSearch) {
                $aRows = $this->database()->select('th.*, mt.text AS preview, mt.total_attachment, mt.time_stamp, mt.user_id AS last_user_id, t.is_group, u.full_name, t.hash_id')
                    ->from(Phpfox::getT('mail_thread_text'), 'mt')
                    ->join(Phpfox::getT('mail_thread_user'), 'th', 'th.user_id = mt.user_id')
                    ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = mt.thread_id')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = mt.user_id')
                    ->limit($iPage, $iLimit, $iCnt)
                    ->order('t.time_stamp DESC')
                    ->group('mt.thread_id', true)
                    ->execute('getSlaveRows');
            } else {
                $aRows = $this->database()->select('th.*, tt.text AS preview, tt.total_attachment, tt.time_stamp, tt.user_id AS last_user_id, t.is_group, u.full_name, t.hash_id')
                    ->from(Phpfox::getT('mail_thread_user'), 'th')
                    ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = th.thread_id')
                    ->leftJoin(Phpfox::getT('mail_thread_text'), 'tt', 'tt.message_id = t.last_id')
                    ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = tt.user_id')
                    ->limit($iPage, $iLimit, $iCnt)
                    ->order('t.time_stamp DESC')
                    ->group('th.thread_id', true)
                    ->execute('getSlaveRows');
            }

            $aFields = Phpfox::getService('user')->getUserFields();

            foreach ($aRows as $iKey => $aRow) {
                $sOriginalText = $aRows[$iKey]['preview'];
                $sPreview = strip_tags($aRows[$iKey]['preview']);
                $aRows[$iKey]['preview'] = !empty($sPreview) ? $sPreview : (!empty($sOriginalText) ? '<span class="mr-1 ico ico-photo"></span>' . ((int)substr_count(strip_tags($sOriginalText, '<img>'), '<img') == 1 ? _p('mail_sent_a_photo') : _p('mail_sent_photos', ['number' => (int)substr_count(strip_tags($sOriginalText, '<img>'), '<img')])) : '<span class="mr-1 ico ico-paperclip"></span>' . ((int)$aRow['total_attachment'] == 1 ? _p('mail_sent_an_attachment') : _p('mail_sent_attachments', ['number' => $aRow['total_attachment']])));
                $aRows[$iKey]['show_text_html'] = !empty($sPreview) ? false : true;


                $aRows[$iKey]['viewer_is_new'] = ($aRow['is_read'] ? false : true);
                $aRows[$iKey]['users'] = $this->database()->select('th.is_read, ' . Phpfox::getUserField())
                    ->from(Phpfox::getT('mail_thread_user'), 'th')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
                    ->where('th.thread_id = ' . (int)$aRow['thread_id'])
                    ->execute('getSlaveRows');

                $iUserCnt = 0;
                $aUserAvatarsForGroup = [];
                foreach ($aRows[$iKey]['users'] as $iUserKey => $aUser) {
                    if (!\Core\Route\Controller::$isApi && !defined('PHPFOX_IS_PRIVATE_MAIL') && $aUser['user_id'] == Phpfox::getUserId()) {
                        if(count($aRows[$iKey]['users']) > 1) {
                            unset($aRows[$iKey]['users'][$iUserKey]);
                            continue;
                        }
                    }

                    $iUserCnt++;

                    if ($aRow['is_group'] && $iUserCnt <= 4) {
                        $aUserAvatarsForGroup[] = $aUser;
                        if ($iUserCnt >= 2) {
                            $aRows[$iKey]['avatar_for_group'] = $aUserAvatarsForGroup;
                            if ($iUserCnt == 4) {
                                $aUserAvatarsForGroup = [];
                            }
                        } else {
                            $aRows[$iKey]['avatar_for_group'] = $aUserAvatarsForGroup[0];
                        }
                        $aRows[$iKey]['total_avatar'] = $iUserCnt;
                    }

                    if ($iUserCnt == 1 && !$aRow['is_group']) {
                        foreach ($aFields as $sField) {
                            if ($sField == 'server_id') {
                                $sField = 'user_server_id';
                            }
                            $aRows[$iKey][$sField] = $aUser[$sField];
                        }
                    }

                    if (!isset($aRows[$iKey]['users_is_read'])) {
                        $aRows[$iKey]['users_is_read'] = [];
                    }

                    if ($aUser['is_read']) {
                        $aRows[$iKey]['users_is_read'][] = $aUser;
                    }
                }

                if (!$iUserCnt) {
                    unset($aRows[$iKey]);
                }
            }
        }

        foreach ($aRows as $iKey => $aRow) {
            $aRows[$iKey]['thread_name'] = $this->getConversationName($aRow['thread_id'], $aRow['is_group'], ', ', $aRow['users']);
            $iLastMessageIdIfDelete = db()->select('last_message_id_for_delete AS last_message_id')
                ->from(Phpfox::getT('mail_thread_user'))
                ->where('thread_id = ' . $aRow['thread_id'] . ' AND user_id = ' . Phpfox::getUserId())
                ->execute('getSlaveField');
            $iLastMessage = db()->select('MAX(message_id) AS last_message_id')
                ->from(Phpfox::getT('mail_thread_text'))
                ->where('thread_id = ' . $aRow['thread_id'])
                ->execute('getSlaveField');
            $iLastMessageIdIfDelete = !empty($iLastMessageIdIfDelete) ? (int)$iLastMessageIdIfDelete : 0;
            if ($iLastMessage <= $iLastMessageIdIfDelete) {
                unset($aRows[$iKey]);
            }
        }

        return [$iCnt, $aRows, $aInputs];
    }

    /**
     * Get conversations for mail.index page by conditions
     * @param array $aSearch
     * @param string $iPage
     * @param string $iLimit
     * @param integer $iThreadId
     * @return array
     */
    public function getSearch(
        $aSearch = [],
        $iPage = '',
        $iLimit = '',
        $iThreadId = 0
    )
    {
        $bIsView = false;
        $bIsCustom = false;
        $bIsTitle = false;

        $aRows = [];

        $oSearch = Phpfox_Search::instance();

        if (!empty($aSearch['view'])) {
            $bIsView = true;
            $sView = $aSearch['view'];

            if ($sView == 'trash') {
                $oSearch->setCondition('AND th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = 1 AND th.is_spam = 0');
            } else if ($sView == 'spam') {
                $oSearch->setCondition('AND th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = 0 AND th.is_spam = 1');
            } else if ($sView == 'unread') {
                $oSearch->setCondition('AND th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = 0 AND th.is_spam = 0 AND th.is_read = 0');
            } else if ($sView == 'sent') {
                $oSearch->setCondition('AND th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = 0 AND th.is_spam = 0 AND th.is_sent = 1');
            }
        }

        if (!empty($aSearch['custom'])) {
            $bIsCustom = true;
            $oSearch->setCondition('AND (mf.folder_id = ' . $aSearch['custom'] . ' AND th.user_id = ' . Phpfox::getUserId() . ' AND th.is_archive = 0 AND th.is_spam = 0 AND t.is_group = 0)');
        }

        if (!empty($aSearch['title'])) {
            $bIsTitle = true;
            $aSearch['title'] = trim(strip_tags($aSearch['title']));
            $oSearch->setCondition(($bIsCustom) ? 'AND (u.full_name LIKE "%' . $aSearch['title'] . '%" AND tuc.user_id != ' . Phpfox::getUserId() . ' AND th.user_id = ' . Phpfox::getUserId() . ')' : 'AND ((t.is_group = 0 AND u.full_name LIKE "%' . $aSearch['title'] . '%" AND tuc.user_id != ' . Phpfox::getUserId() . ' AND th.user_id = ' . Phpfox::getUserId() . ') OR (t.is_group = 1 AND (tgt.title IS NULL OR tgt.title = "") AND u.full_name LIKE "%' . $aSearch['title'] . '%") OR (t.is_group = 1 AND tgt.title LIKE "%' . $aSearch['title'] . '%"))');
        }

        if (!$bIsTitle && !$bIsCustom && !$bIsView) {
            $oSearch->setCondition('AND th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = 0 AND th.is_spam = 0');
        }

        $oSearch->setCondition('AND (t.last_id > 0 AND (th.last_message_id_for_delete < t.last_id) AND mtt.is_show = 1 AND mtt.is_deleted = 0)');

        $aConds = $oSearch->getConditions();

        if ($bIsCustom) {
            db()->join(Phpfox::getT('mail_thread_custom_list'), 'cl', 'cl.user_id = tuc.user_id')
                ->join(Phpfox::getT('mail_thread_folder'), 'mf', 'mf.folder_id = cl.folder_id');
        } else {
            db()->leftJoin(Phpfox::getT('mail_thread_group_title'), 'tgt', 'tgt.thread_id = tuc.thread_id');
        }

        $aTotal = db()->select('th.*, t.is_group')
            ->from(Phpfox::getT('mail_thread_user_compare'), 'tuc')
            ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = tuc.thread_id')
            ->join(Phpfox::getT('mail_thread_user'), 'th', 'th.thread_id = tuc.thread_id')
            ->join(Phpfox::getT('mail_thread_text'), 'mtt', 'mtt.message_id = t.last_id')
            ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = tuc.user_id')
            ->where($aConds)
            ->group('tuc.thread_id', true)
            ->execute('getSlaveRows');

        foreach($aTotal as $key => $value) {
            if($value['is_group']) {
                $aRow = db()->select('*')
                    ->from(Phpfox::getT('mail_thread_user_compare'))
                    ->where('thread_id = '.$value['thread_id'].' AND user_id = '.Phpfox::getUserId())
                    ->executeRow();
                if(empty($aRow)) {
                    unset($aTotal[$key]);
                }
            }
        }

        $iCnt = !empty($aTotal) ? count($aTotal) : 0;

        if ($iCnt) {
            (($sPlugin = Phpfox_Plugin::get('mail.service_mail_get')) ? eval($sPlugin) : false);

            if ($bIsCustom) {
                db()->join(Phpfox::getT('mail_thread_custom_list'), 'cl', 'cl.user_id = tuc.user_id')
                    ->join(Phpfox::getT('mail_thread_folder'), 'mf', 'mf.folder_id = cl.folder_id');
            } else {
                db()->leftJoin(Phpfox::getT('mail_thread_group_title'), 'tgt', 'tgt.thread_id = tuc.thread_id');
            }

            $aRows = db()->select('th.*, t.is_group')
                ->from(Phpfox::getT('mail_thread_user_compare'), 'tuc')
                ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = tuc.thread_id')
                ->join(Phpfox::getT('mail_thread_user'), 'th', 'th.thread_id = tuc.thread_id')
                ->join(Phpfox::getT('mail_thread_text'), 'mtt', 'mtt.message_id = t.last_id')
                ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = tuc.user_id')
                ->where($aConds)
                ->order('t.time_stamp DESC')
                ->group('tuc.thread_id', true)
                ->limit($iPage, $iLimit, $iCnt)
                ->execute('getSlaveRows');

            foreach($aRows as $key => $value) {
                if($value['is_group']) {
                    $aRow = db()->select('*')
                        ->from(Phpfox::getT('mail_thread_user_compare'))
                        ->where('thread_id = '.$value['thread_id'].' AND user_id = '.Phpfox::getUserId())
                        ->executeRow();
                    if(empty($aRow)) {
                        unset($aRows[$key]);
                    }
                }
            }

            $aFields = Phpfox::getService('user')->getUserFields();

            foreach ($aRows as $iKey => $aRow) {
                $aLastText = db()->select('tt.text AS preview, tt.time_stamp, tt.user_id AS last_user_id, tt.total_attachment, u.full_name AS last_user_full_name')
                    ->from(Phpfox::getT('mail_thread'), 't')
                    ->join(Phpfox::getT('mail_thread_text'), 'tt', 't.last_id = tt.message_id')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = tt.user_id')
                    ->where('t.thread_id = ' . $aRow['thread_id'])
                    ->execute('getSlaveRow');
                foreach ($aLastText as $sKey => $sValue) {
                    $aRows[$iKey][$sKey] = $sValue;
                }
                $sOriginalText = $aRows[$iKey]['preview'];
                $sPreview = strip_tags($aRows[$iKey]['preview']);
                $aRows[$iKey]['preview'] = !empty($sPreview) ? $sPreview : (!empty($sOriginalText) ? '<span class="mr-1 ico ico-photo"></span>' . ((int)substr_count(strip_tags($sOriginalText, '<img>'), '<img') == 1 ? _p('mail_sent_a_photo') : _p('mail_sent_photos', ['number' => (int)substr_count(strip_tags($sOriginalText, '<img>'), '<img')])) : '<span class="mr-1 ico ico-paperclip"></span>' . ((int)$aLastText['total_attachment'] == 1 ? _p('mail_sent_an_attachment') : _p('mail_sent_attachments', ['number' => $aLastText['total_attachment']])));
                $aRows[$iKey]['show_text_html'] = !empty($sPreview) ? false : true;
                $aRows[$iKey]['viewer_is_new'] = ($aRow['is_read'] ? false : true);
                $aRows[$iKey]['users'] = $this->database()->select('th.is_read, ' . Phpfox::getUserField())
                    ->from(Phpfox::getT('mail_thread_user'), 'th')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
                    ->where('th.thread_id = ' . (int)$aRow['thread_id'])
                    ->execute('getSlaveRows');

                $iUserCnt = 0;
                $aUserAvatarsForGroup = [];
                foreach ($aRows[$iKey]['users'] as $iUserKey => $aUser) {
                    if (!\Core\Route\Controller::$isApi && $aUser['user_id'] == Phpfox::getUserId()) {
                        if(count($aRows[$iKey]['users']) > 1) {
                            unset($aRows[$iKey]['users'][$iUserKey]);
                            continue;
                        }
                    }
                    $iUserCnt++;

                    if ($aRow['is_group'] && $iUserCnt <= 4) {
                        $aUserAvatarsForGroup[] = $aUser;
                        if ($iUserCnt >= 2) {
                            $aRows[$iKey]['avatar_for_group'] = $aUserAvatarsForGroup;
                            if ($iUserCnt == 4) {
                                $aUserAvatarsForGroup = [];
                            }
                        } else {
                            if($iUserCnt > 0){
                                $aRows[$iKey]['avatar_for_group'] = $aUserAvatarsForGroup[0];
                            }
                        }
                        $aRows[$iKey]['total_avatar'] = $iUserCnt;
                    }

                    if ($iUserCnt == 1 && !$aRow['is_group']) {
                        foreach ($aFields as $sField) {
                            if ($sField == 'server_id') {
                                $sField = 'user_server_id';
                            }
                            $aRows[$iKey][$sField] = $aUser[$sField];
                        }
                    }

                    if (!isset($aRows[$iKey]['users_is_read'])) {
                        $aRows[$iKey]['users_is_read'] = [];
                    }

                    if ($aUser['is_read']) {
                        $aRows[$iKey]['users_is_read'][] = $aUser;
                    }

                }

                if (!$iUserCnt) {
                    unset($aRows[$iKey]);
                }
            }

            //thread name
            foreach ($aRows as $iKey => $aRow) {
                $aRows[$iKey]['thread_name'] = $this->getConversationName($aRow['thread_id'], $aRow['is_group'], ', ', $aRow['users']);
                if ($iThreadId && $iThreadId == $aRow['thread_id']) {
                    $aRows[$iKey]['is_select'] = true;
                } else {
                    $aRows[$iKey]['is_select'] = false;
                }
            }
            if (!$iThreadId && isset($aRows[0])) {
                $aRows[0]['is_select'] = true;
            }
            $aRows = array_values($aRows);
        }
        return [$iCnt, $aRows];
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param array $aArguments is the array of arguments of being passed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('mail.service_mail__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }

    /**
     * Get array of quantity of message status
     * @param $iUserId
     * @return array
     */
    public function getDefaultFoldersCount($iUserId)
    {
        $iCountInbox = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_user'), 'm')
            ->where('m.user_id = ' . Phpfox::getUserId() . ' AND m.is_archive = 0 AND m.is_sent = 0 AND m.is_spam = 0')
            ->execute('getSlaveField');

        $iCountSentbox = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_user'), 'm')
            ->where('m.user_id = ' . Phpfox::getUserId() . ' AND m.is_archive = 0 AND m.is_sent = 1 AND m.is_spam = 0')
            ->execute('getSlaveField');

        $iCountDeleted = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_user'), 'm')
            ->where('m.user_id = ' . Phpfox::getUserId() . ' AND m.is_archive = 1 AND m.is_spam = 0')
            ->execute('getSlaveField');
        $iCountSpam = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_user'), 'm')
            ->where('m.user_id = ' . Phpfox::getUserId() . ' AND m.is_spam = 1 AND m.is_archive = 0')
            ->execute('getSlaveField');
        return [
            'iCountInbox' => $iCountInbox,
            'iCountSentbox' => $iCountSentbox,
            'iCountDeleted' => $iCountDeleted,
            'iCountSpam' => $iCountSpam];
    }

    /**
     * @deprecated 4.7.0
     * @return int
     */
    public function getLegacyCount()
    {
        $iCnt = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('mail'), 'm')
            ->where('m.viewer_folder_id = 0 AND m.viewer_user_id = ' . Phpfox::getUserId() . ' AND m.viewer_type_id = 0')
            ->execute('getSlaveField');
        return $iCnt;
    }

    /**
     * Get number of unseen conversation with new message
     * @return int
     */
    public function getUnseenTotal()
    {
        $iCnt = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_user'), 'm')
            ->join(Phpfox::getT('mail_thread'), 'mt', 'mt.thread_id = m.thread_id')
            ->where('m.user_id = ' . Phpfox::getUserId() . ' AND m.is_read = 0 AND mt.last_id > 0  AND m.is_spam = 0 AND (m.last_message_id_for_delete < mt.last_id)')
            ->execute('getSlaveField');

        return $iCnt;
    }

    /**
     * @param $iThreadId
     * @return array|bool|int|string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getThread($iThreadId)
    {
        $aThread = $this->database()->select('mt.*, mtu.is_archive AS user_is_archive')
            ->from(Phpfox::getT('mail_thread'), 'mt')
            ->join(Phpfox::getT('mail_thread_user'), 'mtu', 'mtu.thread_id = mt.thread_id')
            ->where('mt.thread_id = ' . (int)$iThreadId)
            ->execute('getSlaveRow');

        if (!isset($aThread['thread_id'])) {
            return false;
        }
        $aThread['users'] = $this->database()->select(Phpfox::getUserField())
            ->from(Phpfox::getT('mail_thread_user'), 'th')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
            ->where('th.thread_id = ' . (int)$aThread['thread_id'])
            ->execute('getSlaveRows');

        if ($aThread['is_group']) {
            $sTitle = $this->getGroupTitle($aThread['thread_id']);
            $aThread['thread_name'] = !empty($sTitle) ? $sTitle : $this->getDefaultGroupTitle($aThread['thread_id']);
        } else {
            $aUsers = $aThread['users'];
            $sName = '';
            foreach ($aUsers as $iKey => $aUser) {
                if ($aUser['user_id'] == Phpfox::getUserId()) {
                    unset($aUsers[$iKey]);
                    continue;
                }
                $sName = Phpfox::getLib('parse.output')->clean($aUser['full_name']);
            }
            $aThread['thread_name'] = trim($sName);
        }
        $aThread['user_id'] = [];
        foreach ($aThread['users'] as $aUser) {
            $aThread['user_id'][] = $aUser['user_id'];
        }
        return $aThread;
    }

    /**
     * Get conversation infomation
     * @param $iThreadId
     * @param int $iPage
     * @param bool $getLatest
     * @param int $iOffset
     * @return array
     */
    public function getThreadedMail($iThreadId, $iPage = 0, $getLatest = false, $iOffset = 0)
    {
        $aThread = $this->database()->select('mt.*, mtu.is_archive AS user_is_archive')
            ->from(Phpfox::getT('mail_thread'), 'mt')
            ->join(Phpfox::getT('mail_thread_user'), 'mtu', 'mtu.thread_id = mt.thread_id')
            ->where('mt.thread_id = ' . (int)$iThreadId)
            ->execute('getSlaveRow');

        if (!isset($aThread['thread_id'])) {
            return [false, false];
        }

        $aThread['users'] = $this->database()->select(Phpfox::getUserField())
            ->from(Phpfox::getT('mail_thread_user'), 'th')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
            ->where('th.thread_id = ' . (int)$aThread['thread_id'])
            ->execute('getSlaveRows');

        if ($aThread['is_group']) {
            $sTitle = $this->getGroupTitle($aThread['thread_id']);
            $aThread['thread_name'] = !empty($sTitle) ? $sTitle : $this->getDefaultGroupTitle($aThread['thread_id']);
        } else {
            $aUsers = $aThread['users'];
            $sName = '';
            foreach ($aUsers as $iKey => $aUser) {
                if ($aUser['user_id'] == Phpfox::getUserId()) {
                    unset($aUsers[$iKey]);
                    continue;
                }
                $sName = Phpfox::getLib('parse.output')->clean($aUser['full_name']);
            }
            $aThread['thread_name'] = trim($sName);
        }

        $aThread['user_id'] = [];
        foreach ($aThread['users'] as $aUser) {
            $aThread['user_id'][] = $aUser['user_id'];
        }

        $iLimit = 10;
        if ($iOffset == 0)
            $iOffset = ($iPage * $iLimit);

        if ($getLatest) {
            $iLimit = 1;
        }

        $iLastMessageIdIfDelete = db()->select('last_message_id_for_delete AS last_message_id')
            ->from(Phpfox::getT('mail_thread_user'))
            ->where('thread_id = ' . $iThreadId . ' AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');
        $iLastMessage = db()->select('MAX(message_id) AS last_message_id')
            ->from(Phpfox::getT('mail_thread_text'))
            ->where('thread_id = ' . $iThreadId)
            ->execute('getSlaveField');
        $iLastMessageIdIfDelete = !empty($iLastMessageIdIfDelete) ? (int)$iLastMessageIdIfDelete : 0;
        $aMessages = [];

        if ($iLastMessage > $iLastMessageIdIfDelete) {
            $aMessages = $this->database()->select('mtt.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('mail_thread_text'), 'mtt')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtt.user_id')
                ->where('mtt.thread_id = ' . (int)$iThreadId . ' AND mtt.message_id > ' . $iLastMessageIdIfDelete . ' AND mtt.is_show = 1 AND mtt.is_deleted = 0')
                ->order('mtt.time_stamp DESC')
                ->limit($iOffset, $iLimit, null, false, true)
                ->execute('getSlaveRows');

            if ($getLatest) {
                if (!isset($aMessages[0])) {
                    throw error(_p('mail_message_not_found'));
                }
                return $aMessages[0];
            }

            $aMessages = array_reverse($aMessages);

            $iCheckUserId = 0;
            $iPostion = 0;
            $iCount = 0;
            $sDate = '';

            foreach ($aMessages as $iKey => $aMail) {
                if ($aMail['total_attachment'] > 0 && Phpfox::isModule('attachment')) {
                    list(, $aAttachments) = Phpfox::getService('attachment')->get(['AND attachment.item_id = ' . $aMail['message_id'] . ' AND attachment.category_id = \'mail\' AND is_inline = 0'], 'attachment.attachment_id DESC', false);

                    $aMessages[$iKey]['attachments'] = $aAttachments;
                }

                $aMessages[$iKey]['forwards'] = [];
                if ($aMail['has_forward']) {
                    $aMessages[$iKey]['forwards'] = $this->database()->select('mtt.*, ' . Phpfox::getUserField())
                        ->from(Phpfox::getT('mail_thread_forward'), 'mtf')
                        ->join(Phpfox::getT('mail_thread_text'), 'mtt', 'mtt.message_id = mtf.copy_id')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtt.user_id')
                        ->where('mtf.message_id = ' . $aMail['message_id'])
                        ->execute('getSlaveRows');
                }

                $aMessages[$iKey]['message_with_same_user'] = 4;
                $aMessages[$iKey]['class_with_same_user'] = '';
                $aMessages[$iKey]['remove_avatar_with_same_user'] = '';
                if ($iCheckUserId == 0 && $iCount == 0 && empty($sDate)) {
                    $iCheckUserId = $aMail['user_id'];
                    $sDate = Phpfox::getTime('m/d/Y', $aMail['time_stamp']);

                } elseif (((int)$iCheckUserId == (int)$aMail['user_id']) && ($sDate == Phpfox::getTime('m/d/Y', $aMail['time_stamp'])) && $iCount > 0) {
                    if ($iCount == 1) {
                        $aMessages[$iPostion - 1]['message_with_same_user'] = 1;
                        $aMessages[$iPostion - 1]['class_with_same_user'] = 'is_first_message';
                        $aMessages[$iPostion - 1]['remove_avatar_with_same_user'] = '';
                    }
                    $aMessages[$iKey]['message_with_same_user'] = 2;
                    $aMessages[$iKey]['class_with_same_user'] = 'is_middle_message';
                    $aMessages[$iKey]['remove_avatar_with_same_user'] = 'remove';
                } elseif ((((int)$iCheckUserId != (int)$aMail['user_id']) || ($sDate != Phpfox::getTime('m/d/Y', $aMail['time_stamp']))) && $iCount > 0) {
                    $iCheckUserId = $aMail['user_id'];
                    $sDate = Phpfox::getTime('m/d/Y', $aMail['time_stamp']);
                    if ($iCount > 1) {
                        $aMessages[$iPostion - 1]['message_with_same_user'] = 3;
                        $aMessages[$iPostion - 1]['class_with_same_user'] = 'is_last_message';
                    } else {
                        $aMessages[$iKey]['message_with_same_user'] = 4;
                        $aMessages[$iKey]['class_with_same_user'] = '';
                        $aMessages[$iKey]['remove_avatar_with_same_user'] = '';
                    }

                    $iCount = 0;
                }
                $iPostion++;
                $iCount++;
                $aMessages[$iKey]['message_with_order'] = $iCount;

                $sTempText = Phpfox::getLib('parse.output')->parse($aMessages[$iKey]['text']);
                if (empty(strip_tags($sTempText))) {
                    $bIsOnlyImageText = !empty(strip_tags($sTempText, '<img>')) ? ((int)substr_count(strip_tags($sTempText, '<img>'), '<img') == 1 ? true : false) : false;
                    $aMessages[$iKey]['is_only_image_text'] = $bIsOnlyImageText;
                    if ((int)substr_count(strip_tags($sTempText, '<img>'), '<img') > 1) {
                        $aMessages[$iKey]['is_mixed_text'] = true;
                    }
                } else {
                    $sCheckMixedText = strip_tags($sTempText, '<img>');
                    $aMessages[$iKey]['is_mixed_text'] = strip_tags($sCheckMixedText) != $sCheckMixedText ? true : false;
                }

                $aMessages[$iKey]['text'] = strip_tags($aMessages[$iKey]['text'], '<img>');
                $aMessages[$iKey]['message_with_same_date'] = Phpfox::getTime('m/d/Y', $aMessages[$iKey]['time_stamp']);
            }
            if (!empty($aMessages)) {
                if ($iCount > 1) {
                    $aMessages[$iPostion - 1]['message_with_same_user'] = 3;
                    $aMessages[$iPostion - 1]['class_with_same_user'] = 'is_last_message';
                } else {
                    $aMessages[$iKey]['message_with_same_user'] = 4;
                    $aMessages[$iKey]['class_with_same_user'] = '';
                    $aMessages[$iKey]['remove_avatar_with_same_user'] = '';
                }
            }
        }
        return [$aThread, $aMessages];
    }

    /**
     * Export conversations with XML
     * @param $aThreads
     * @return bool|string
     * @throws \Exception
     */
    public function getThreadsForExport($aThreads)
    {
        define('PHPFOX_XML_SKIP_STAMP', true);

        $sThreads = implode(',', $aThreads);

        if (empty($sThreads)) {
            return Phpfox_Error::set(_p('unable_to_export_your_messages'));
        }

        $aThreads = $this->database()->select('mt.*')
            ->from(Phpfox::getT('mail_thread'), 'mt')
            ->join(Phpfox::getT('mail_thread_user'), 'mtu', 'mtu.thread_id = mt.thread_id AND mtu.user_id = ' . Phpfox::getUserId())
            ->where('mt.thread_id IN(' . $sThreads . ')')
            ->execute('getSlaveRows');

        if (!count($aThreads)) {
            return Phpfox_Error::set(_p('unable_to_export_your_messages'));
        }

        $oXmlBuilder = Phpfox::getLib('xml.builder');
        $oXmlBuilder->addGroup('threads');

        foreach ($aThreads as $iKey => $aThread) {
            $aMessages = $this->database()->select('mtt.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('mail_thread_text'), 'mtt')
                ->join(Phpfox::getT('user'), 'u', 'mtt.user_id = u.user_id')
                ->where('thread_id = ' . (int)$aThread['thread_id'])
                ->execute('getSlaveRows');

            $aUsers = $this->database()->select('th.is_read, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('mail_thread_user'), 'th')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
                ->where('th.thread_id = ' . (int)$aThread['thread_id'])
                ->execute('getSlaveRows');

            $oXmlBuilder->addGroup('thread', [
                    'id' => $aThread['thread_id']
                ]
            );

            $iCnt = 0;
            $sUsers = '';
            foreach ($aUsers as $aUser) {
                $iCnt++;
                if ($iCnt != 1) {
                    $sUsers .= ',';
                }
                $sUsers .= Phpfox::getLib('parse.output')->clean($aUser['full_name']);
            }

            $oXmlBuilder->addTag('conversation', $sUsers);
            $oXmlBuilder->addTag('url', Phpfox_Url::instance()->makeUrl('mail.thread', ['id' => $aThread['thread_id']]));

            $oXmlBuilder->addGroup('messages');
            foreach ($aMessages as $aMessage) {
                $oXmlBuilder->addGroup('message', [
                        'id' => $aMessage['message_id']
                    ]
                );

                $oXmlBuilder->addTag('time', $aMessage['time_stamp']);
                $oXmlBuilder->addTag('user', Phpfox::getLib('parse.output')->clean($aMessage['full_name']));
                $oXmlBuilder->addTag('content', Phpfox::getLib('parse.output')->parse($aMessage['text']));
                $oXmlBuilder->closeGroup();
            }
            $oXmlBuilder->closeGroup();

            $oXmlBuilder->closeGroup();
        }

        $oXmlBuilder->closeGroup();

        $sFile = md5(Phpfox::getUserId() . uniqid()) . '.xml';

        Phpfox_File::instance()->writeToCache($sFile, $oXmlBuilder->output());

        return PHPFOX_DIR_CACHE . $sFile;
    }
}