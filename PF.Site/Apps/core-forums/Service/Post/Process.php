<?php

namespace Apps\Core_Forums\Service\Post;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');


class Process extends Phpfox_Service
{
    /**
     * @var bool
     */
    private $_bUpdateCounter = true;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('forum_post');
    }

    /**
     * @param array $aVals
     * @param bool $aCallback
     * @param array $aExtra
     *
     * @return bool|int
     */
    public function add($aVals, $aCallback = false, $aExtra = [])
    {

        $aThread = $this->database()->select('*')
            ->from(Phpfox::getT('forum_thread'))
            ->where('thread_id = ' . (int)$aVals['thread_id'])
            ->execute('getSlaveRow');

        if ($aThread['group_id'] > 0 && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups')) && ($sParentId = Phpfox::getPagesType($aThread['group_id'])) && Phpfox::isModule($sParentId)) {
            $aCallback = Phpfox::callback($sParentId . '.addForum', $aThread['group_id']);
        }

        $oParseInput = Phpfox::getLib('parse.input');

        $bHasAttachments = (Phpfox::getUserParam('forum.can_add_forum_attachments') && Phpfox::isModule('attachment') && isset($aVals['attachment']) && !empty($aVals['attachment']));

        $bApprovePost = Phpfox::getUserParam('forum.approve_forum_post');

        Phpfox::getService('ban')->checkAutomaticBan((isset($aVals['title']) && !empty($aVals['title']) ? $aVals['title'] : '') . ' ' . $aVals['text']);
        $aInsert = [
            'thread_id' => $aVals['thread_id'],
            'view_id' => ($bApprovePost ? '1' : '0'),
            'user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()),
            'title' => (empty($aVals['title']) ? '' : $oParseInput->clean($aVals['title'], 255)),
            'total_attachment' => 0,
            'time_stamp' => (isset($aExtra['user_id']) ? $aExtra['time_stamp'] : PHPFOX_TIME)
        ];

        $iId = $this->database()->insert(Phpfox::getT('forum_post'), $aInsert);

        $this->database()->insert(Phpfox::getT('forum_post_text'), [
                'post_id' => $iId,
                'text' => $oParseInput->clean($aVals['text']),
                'text_parsed' => $oParseInput->prepare($aVals['text'])
            ]
        );

        if (!$bApprovePost) {
            if ($aCallback === false) {
                if (empty($aVals['forum_id'])) {
                    $aVals['forum_id'] = $aThread['forum_id'];
                }

                foreach (Phpfox::getService('forum')->id($aVals['forum_id'])->getParents() as $iForumid) {
                    $this->database()->update(Phpfox::getT('forum'), [
                        'thread_id' => $aVals['thread_id'],
                        'post_id' => $iId,
                        'last_user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId())
                    ], 'forum_id = ' . $iForumid);

                    Phpfox::getService('forum.process')->updateCounter($iForumid, 'total_post');
                }
            }

            $this->database()->update(Phpfox::getT('forum_thread'), [
                'total_post' => ['= total_post +', 1],
                'post_id' => $iId,
                'time_update' => (isset($aExtra['user_id']) ? $aExtra['time_stamp'] : PHPFOX_TIME),
                'last_user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId())
            ], 'thread_id = ' . (int)$aVals['thread_id']);

            if ($this->_bUpdateCounter) {
                Phpfox::getService('user.field.process')->updateCounter(Phpfox::getUserId(), 'total_post');
            }
        }
        //support hashtag
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->add('forum_post', $iId,
                (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), $aVals['text'], true);
        }

        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'],
                (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), $iId);
        }

        if (!$bApprovePost) {
            // Update user activity
            Phpfox::getService('user.activity')->update((isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()),
                'forum');
        }

        if (isset($aVals['is_subscribed']) && $aVals['is_subscribed']) {
            Phpfox::getService('forum.subscribe.process')->add($aVals['thread_id'],
                (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()));
        } else {
            Phpfox::getService('forum.subscribe.process')->delete($aVals['thread_id'],
                (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()));
        }

        if (empty($aExtra) && !$bApprovePost) {
            Phpfox::getService('forum.subscribe')->sendEmails($aVals['thread_id'], $iId);

            $aThread = Phpfox::getService('forum.thread')->getActualThread($aVals['thread_id']);

            if (!Phpfox::getService('forum')->isPrivateForum($aThread['forum_id']) && $aThread['view_id'] == 0) {
                if (Phpfox::isModule('feed') && !defined('PHPFOX_SKIP_FEED_ENTRY') && Phpfox::getParam('forum.forum_allow_create_feed_when_add_new_item', 1)) {
                    Phpfox::getService('feed.process')->callback($aCallback)->add('forum_post', $iId, 0, 0,
                        ($aCallback === false ? 0 : $aCallback['item']));
                }
            }
        }
        if ($sPlugin = Phpfox_Plugin::get('forum.service_post_process_add_1')) {
            eval($sPlugin);
        }

        if ($bApprovePost) {
            return false;
        }

        return $iId;
    }

    /**
     * @param int $iPostId
     *
     * @return bool
     */
    public function approve($iPostId)
    {
        $aPost = $this->database()
            ->select('*')
            ->from(Phpfox::getT('forum_post'))
            ->where('post_id = ' . (int)$iPostId)
            ->execute('getSlaveRow');

        if (!isset($aPost['post_id'])) {
            return false;
        }

        if ($aPost['view_id'] == 0) {
            return true;
        }
        $aThread = Phpfox::getService('forum.thread')->getActualThread($aPost['thread_id']);
        $aCallback = false;
        if ($aThread['group_id'] > 0 && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups')) && ($sParentId = Phpfox::getPagesType($aThread['group_id'])) && Phpfox::isModule($sParentId)) {
            $aCallback = Phpfox::callback($sParentId . '.addForum', $aThread['group_id']);
        }
        $this->database()->update(Phpfox::getT('forum_post'), ['view_id' => '0'], 'post_id = ' . (int)$iPostId);
        if (!$aThread['group_id']) {
            foreach (Phpfox::getService('forum')->id($aThread['forum_id'])->getParents() as $iForumid) {
                $this->database()->update(Phpfox::getT('forum'),
                    [
                        'thread_id' => $aPost['thread_id'],
                        'post_id' => $iPostId,
                        'last_user_id' => $aPost['user_id']
                    ],
                    'forum_id = ' . $iForumid);

                Phpfox::getService('forum.process')->updateCounter($iForumid, 'total_post');
            }
        }
        $this->database()->update(Phpfox::getT('forum_thread'), [
            'total_post' => ['= total_post +', 1],
            'post_id' => $iPostId,
            'time_update' => PHPFOX_TIME,
            'last_user_id' => $aPost['user_id']
        ], 'thread_id = ' . (int)$aPost['thread_id']);

        Phpfox::getService('user.field.process')->updateCounter($aPost['user_id'], 'total_post');

        Phpfox::getService('user.activity')->update($aPost['user_id'], 'forum');

        Phpfox::getService('forum.subscribe')->sendEmails($aPost['thread_id'], $iPostId);

        (($sPlugin = Phpfox_Plugin::get('forum.service_post_process_approve__1')) ? eval($sPlugin) : false);

        if (!Phpfox::getService('forum')->isPrivateForum($aThread['forum_id']) && $aThread['view_id'] == 0) {
            if (Phpfox::isModule('feed') && !defined('PHPFOX_SKIP_FEED_ENTRY') && Phpfox::getParam('forum.forum_allow_create_feed_when_add_new_item', 1)) {
                Phpfox::getService('feed.process')->callback($aCallback)->add('forum_post', $iPostId, 0, 0,
                    ($aCallback === false ? 0 : $aCallback['item']), $aPost['user_id']);
            }
        }

        $sCurrentUrl = Phpfox_Url::instance()->permalink('forum.thread', $aThread['thread_id'], $aThread['title'],
            false, null, ['view' => $aPost['post_id']]);

        if (Phpfox::isModule('notification')) {
            Phpfox::getService('notification.process')->add('forum_post_approved', $iPostId,
                $aPost['user_id']);
        }
        Phpfox::getLib('mail')->to($aPost['user_id'])
            ->subject([
                'forum_post_approved_on_site_title',
                ['site_title' => Phpfox::getParam('core.site_title')]
            ])
            ->message([
                'your_post_that_is_part_of_the_forum_thread_title_on_site_title',
                [
                    'thread_title' => $aThread['title'],
                    'site_title' => Phpfox::getParam('core.site_title'),
                    'link' => $sCurrentUrl
                ]
            ])
            ->notification('forum.email_notification')
            ->send();

        return true;
    }

    /**
     * @param bool $bUpdate
     *
     * @return $this
     */
    public function counter($bUpdate)
    {
        $this->_bUpdateCounter = $bUpdate;
        return $this;
    }

    /**
     * @param int $iPostId
     *
     * @return bool
     */
    public function delete($iPostId)
    {
        $aPost = $this->database()->select('fp.post_id, fp.user_id, fp.thread_id, ft.post_id AS last_post_id, ft.forum_id, fp.view_id')
            ->from($this->_sTable, 'fp')
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
            ->where('fp.post_id = ' . (int)$iPostId)
            ->execute('getSlaveRow');

        if (!isset($aPost['post_id'])) {
            return false;
        }

        $this->database()->delete($this->_sTable, 'post_id = ' . (int)$iPostId);
        $this->database()->delete(Phpfox::getT('forum_post_text'), 'post_id = ' . (int)$iPostId);

        $iPostCount = $this->database()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('thread_id = ' . $aPost['thread_id'])
            ->execute('getSlaveField');

        if (!$iPostCount) {
            $this->database()->delete(Phpfox::getT('forum_thread'), 'thread_id = ' . (int)$aPost['thread_id']);

            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('forum',
                (int)$aPost['thread_id']) : null);

            $aLastThread = $this->database()->select('thread_id, post_id')
                ->from(Phpfox::getT('forum_thread'))
                ->where('forum_id = ' . $aPost['forum_id'])
                ->order('time_update DESC')
                ->execute('getSlaveRow');

            if (isset($aLastThread['thread_id'])) {
                $this->database()->update(Phpfox::getT('forum'),
                    ['thread_id' => $aLastThread['thread_id'], 'post_id' => $aLastThread['post_id']],
                    'thread_id = ' . $aPost['thread_id']);
            }

            if ($aPost['forum_id'] > 0) {
                foreach (Phpfox::getService('forum')->id($aPost['forum_id'])->getParents() as $iForumid) {
                    Phpfox::getService('forum.process')->updateCounter($iForumid, 'total_thread', true);
                    Phpfox::getService('forum.process')->updateLastPost($iForumid, $aPost['thread_id']);
                }
            }
        } else {
            if ($aPost['last_post_id'] == $aPost['post_id']) {
                $aLastPost = $this->database()->select('fp.post_id')
                    ->from($this->_sTable, 'fp')
                    ->where('fp.thread_id = ' . $aPost['thread_id'])
                    ->order('fp.time_stamp DESC')
                    ->execute('getSlaveRow');

                if (isset($aLastPost['post_id'])) {
                    $this->database()->update(Phpfox::getT('forum'), ['post_id' => $aLastPost['post_id']],
                        'post_id = ' . $aPost['post_id']);
                    $this->database()->update(Phpfox::getT('forum_thread'), ['post_id' => $aLastPost['post_id']],
                        'thread_id = ' . $aPost['thread_id']);
                }
            }
            $this->database()->update(Phpfox::getT('forum_thread'), ['total_post' => ['= total_post -', 1]],
                'thread_id = ' . $aPost['thread_id']);
        }

        if ($aPost['forum_id'] > 0) {
            foreach (Phpfox::getService('forum')->id($aPost['forum_id'])->getParents() as $iForumid) {
                Phpfox::getService('forum.process')->updateCounter($iForumid, 'total_post', true);
                Phpfox::getService('forum.process')->updateLastPost($iForumid, $aPost['thread_id']);
            }
        }

        Phpfox::getService('user.field.process')->updateCounter($aPost['user_id'], 'total_post', true);

        if ((int)$aPost['view_id'] == 0) {
            Phpfox::getService('user.activity')->update($aPost['user_id'], 'forum', '-');
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('forum_post', $iPostId) : null);

        (Phpfox::isModule('like') ? Phpfox::getService('like.process')->delete('forum_post', (int)$iPostId, 0,
            true) : null);
        (Phpfox::isModule('notification') ? Phpfox::getService('notification.process')->deleteAllOfItem(['forum_post_like'],
            (int)$iPostId) : null);

        return true;
    }


    /**
     * @param int $iId
     * @param int $iUserId
     * @param array $aVals
     *
     * @return bool
     */
    public function update($iId, $iUserId, $aVals)
    {
        $oParseInput = Phpfox::getLib('parse.input');
        Phpfox::getService('ban')->checkAutomaticBan((isset($aVals['title']) ? $aVals['title'] : '') . ' ' . $aVals['text']);

        $bHasAttachments = (Phpfox::getUserParam('forum.can_add_forum_attachments') && Phpfox::isModule('attachment') && !empty($aVals['attachment']) && $iUserId == Phpfox::getUserId());

        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], $iUserId, $iId);
        }

        $this->database()->update($this->_sTable, [
            'title' => (empty($aVals['title']) ? null : $oParseInput->clean($aVals['title'], 255)),
            'total_attachment' => Phpfox::isModule('attachment') ? Phpfox::getService('attachment')->getCountForItem($iId, 'forum') : 0,
            'update_time' => PHPFOX_TIME,
            'update_user' => substr(Phpfox::getUserBy('full_name'), 0, 100)
        ], 'post_id = ' . (int)$iId
        );

        $this->database()->update(Phpfox::getT('forum_post_text'), [
            'text' => $oParseInput->clean($aVals['text']),
            'text_parsed' => $oParseInput->prepare($aVals['text'])
        ], 'post_id = ' . (int)$iId
        );

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->update('forum_post', $iId, $iUserId, $aVals['text'], true);
        }
        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], $iUserId, $iId);
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('forum_post', $iId) : null);

        return true;
    }

    /**
     * @param int $iId
     * @param string $sText
     * @param array $aVals
     *
     * @return bool
     */
    public function updateText($iId, $sText, $aVals = [])
    {
        $oParseInput = Phpfox::getLib('parse.input');
        Phpfox::getService('ban')->checkAutomaticBan($sText);

        $bHasAttachments = (Phpfox::getUserParam('forum.can_add_forum_attachments') && Phpfox::isModule('attachment') && !empty($aVals['attachment']));

        $this->database()->update($this->_sTable, [
            'update_time' => PHPFOX_TIME,
            'update_user' => substr(Phpfox::getUserBy('full_name'), 0, 100),
            'total_attachment' => Phpfox::isModule('attachment') ? Phpfox::getService('attachment')->getCountForItem($iId,
                'forum') : 0
        ], 'post_id = ' . (int)$iId
        );

        $this->database()->update(Phpfox::getT('forum_post_text'), [
            'text' => $oParseInput->clean($sText),
            'text_parsed' => $oParseInput->prepare($sText)
        ], 'post_id = ' . (int)$iId
        );

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->update('forum_post', $iId, $aVals['user_id'], $aVals['text'], true);
        }
        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('forum_post', $iId) : null);

        return true;
    }

    /**
     * Adding "thanks" to a specific post from a member.
     *
     * @param int $iPostId Post ID# for the post we are thanking.
     *
     * @return bool FALSE if user already gave thanks to the post, TRUE if not.
     */
    public function thank($iPostId)
    {
        $aPost = $this->database()->select('post_id, user_id')
            ->from(Phpfox::getT('forum_post'))
            ->where('post_id = ' . (int)$iPostId)
            ->execute('getSlaveRow');

        if (!isset($aPost['post_id'])) {
            return false;
        }

        if ($aPost['user_id'] == Phpfox::getUserId()) {
            return false;
        }

        $iCheck = (int)$this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('forum_thank'))
            ->where('post_id = ' . (int)$iPostId . ' AND user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');

        if ($iCheck) {
            return Phpfox_Error::set(_p('you_have_already_given_your_thanks_for_this_post'));
        }

        $iThankId = $this->database()->insert(Phpfox::getT('forum_thank'), [
                'post_id' => (int)$iPostId,
                'user_id' => Phpfox::getUserId(),
                'time_stamp' => PHPFOX_TIME
            ]
        );
        // add notification for thank
        Phpfox::isModule('notification') && Phpfox::getService('notification.process')->add('forum_post_thank',
            $aPost['post_id'], $aPost['user_id']);

        (($sPlugin = Phpfox_Plugin::get('forum.service_post_process_thank')) ? eval($sPlugin) : false);

        return $iThankId;
    }

    /**
     * Delete a "Thank You" from the database. We run checks to make
     * sure the user deleting the "Thank You" either owns the item
     * or has a super admin power to delete it.
     *
     * @param int $iThankId Thank ID# provided by the auto_increment for the table "forum_thank".
     *
     * @return bool FALSE if user is not allowed to delete the item, TRUE if all went well.
     */
    public function deleteThanks($iThankId)
    {
        $aThank = $this->database()
            ->select('*')
            ->from(Phpfox::getT('forum_thank'))
            ->where('thank_id = ' . (int)$iThankId)
            ->execute('getSlaveRow');

        if (!isset($aThank['thank_id'])) {
            return Phpfox_Error::set(_p('the_thank_you_you_are_trying_to_delete_cannot_be_found'));
        }

        $aPost = $this->database()
            ->select('post_id, user_id')
            ->from(Phpfox::getT('forum_post'))
            ->where('post_id = ' . (int)$aThank['post_id'])
            ->execute('getSlaveRow');

        if (!isset($aPost['post_id'])) {
            return false;
        }

        $bCanDelete = $aThank['user_id'] == Phpfox::getUserId();
        if (!$bCanDelete && Phpfox::getUserParam('forum.can_delete_thanks_by_other_users')) {
            $bCanDelete = true;
        }

        if (!$bCanDelete) {
            return Phpfox_Error::set(_p('you_do_not_have_the_proper_permissions_to_delete_this_thank_you'));
        }

        $this->database()->delete(Phpfox::getT('forum_thank'), 'thank_id = ' . (int)$aThank['thank_id']);

        (($sPlugin = Phpfox_Plugin::get('forum.service_post_process_deletethanks')) ? eval($sPlugin) : false);

        return $aPost['post_id'];
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('forum.service_post_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}