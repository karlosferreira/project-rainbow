<?php
namespace Apps\Core_Forums\Service\Subscribe;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');


class Subscribe extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('forum_subscribe');
    }

    /**
     * @param int $iThreadId
     * @param null|int $iPostId
     *
     * @return void
     */
    public function sendEmails($iThreadId, $iPostId = null)
    {
        $aPost = $this->database()
            ->select('fp.*, u.full_name')
            ->from(Phpfox::getT('forum_post'), 'fp')
            ->join(':user', 'u', 'u.user_id = fp.user_id')
            ->where('fp.post_id = ' . (int)$iPostId)
            ->execute('getSlaveRow');

        $blockedUserIds = !empty($aPost['post_id']) ? Phpfox::getService('forum')->getBlockedUserIds($aPost['user_id']) : null;

        $aUsers = $this->database()->select('fs.user_id, ft.forum_id, ft.thread_id, ft.group_id, ft.title, f.name AS forum_name')
            ->from($this->_sTable, 'fs')
            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fs.thread_id')
            ->leftJoin(Phpfox::getT('forum'), 'f', 'f.forum_id = ft.forum_id')
            ->where('fs.thread_id = ' . (int)$iThreadId . (!empty($blockedUserIds) ? ' AND fs.user_id NOT IN (' . implode(',', $blockedUserIds) . ')' : ''))
            ->execute('getSlaveRows');

        if (count($aUsers)) {
            foreach ($aUsers as $aUser) {
                $sLink = Phpfox_Url::instance()->permalink('forum.thread', $aUser['thread_id'],
                        $aUser['title']) . 'view_' . $iPostId . '/';

                if ($aUser['user_id'] != $aPost['user_id']) {
                    Phpfox::getService('notification.process')->add('forum_subscribed_post', $iPostId,
                        $aUser['user_id'], $aPost['user_id']);
                }
                Phpfox::getLib('mail')->to($aUser['user_id'])
                    ->subject(array('reply_to_thread_title', array('title' => $aUser['title'])))
                    ->message(array(
                        'full_name_has_just_replied_to_the_thread_title',
                        array(
                            'full_name' => !empty($aPost['post_id']) ? $aPost['full_name'] : Phpfox::getUserBy('full_name'),
                            'title' => $aUser['title'],
                            'link' => $sLink
                        )
                    ))
                    ->notification('forum.subscribe_new_post')
                    ->send();
            }
        }
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
        if ($sPlugin = Phpfox_Plugin::get('forum.service_subscribe_subscribe__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}