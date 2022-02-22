<?php

namespace Apps\Core_Comments\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Comment Callbacks
 *
 * @copyright       [PHPFOX_COPYRIGHT]
 * @author          phpFox LLC
 * @package         App_Core_Comments
 */
class Callback extends Phpfox_Service
{
    const FEED_STATUS_MAX_LENGTH = 200;

    const FEED_STATUS_MAX_SPLIT_LENGTH = 55;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('comment');
    }

    public function handleCommentFeedStatus($status)
    {
        if (!isset($status) || $status == '') {
            return '';
        }

        $oParseOutput = Phpfox::getLib('parse.output');
        $status = $oParseOutput->parse(strip_tags($status), true, [
            'parse_url_options' => ['attr' => ['class' => 'comment_parsed_url']]
        ]);

        $aEmojis = Phpfox::getService('comment.emoticon')->getAll();
        $corePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-comments';
        foreach ($aEmojis as $aEmoji) {
            $status = str_replace($aEmoji['code'], '<span class="item-tag-emoji"><img class="comment_content_emoji" title="' . $aEmoji['title'] . '" src="' . $corePath . '/assets/images/emoticons/' . $aEmoji['image'] . '"  alt="' . $aEmoji['image'] . '"/></span>', $status);
        }

        $status = html_entity_decode($status, ENT_QUOTES, 'UTF-8');
        $status = $oParseOutput->appendSuffixForShorten(Phpfox::getService('comment')->truncateCommentFeedStatus($status, self::FEED_STATUS_MAX_LENGTH), $status, 'feed.view_more', self::FEED_STATUS_MAX_LENGTH, true);
        $status = $oParseOutput->split($status, self::FEED_STATUS_MAX_SPLIT_LENGTH);

        return $status;
    }

    /**
     * Get Comment Stats in a period time
     *
     * @param int $iStartTime
     * @param int $iEndTime
     *
     * @return array
     */
    public function getSiteStatsForAdmin($iStartTime, $iEndTime)
    {
        $aCond = [];
        $aCond[] = 'view_id = 0';
        if ($iStartTime > 0) {
            $aCond[] = 'AND time_stamp >= \'' . $this->database()->escape($iStartTime) . '\'';
        }
        if ($iEndTime > 0) {
            $aCond[] = 'AND time_stamp <= \'' . $this->database()->escape($iEndTime) . '\'';
        }

        $iCnt = (int)$this->database()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($aCond)
            ->execute('getSlaveField');

        return [
            'phrase' => 'comment.comment_on_items',
            'total'  => $iCnt
        ];
    }

    /**
     * @param int $iId
     *
     * @return bool|string false or url
     */
    public function getRedirectRequest($iId)
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_service_callback_getredirectrequest__start')) ? eval($sPlugin) : false);

        $aItem = $this->database()->select('comment_id, type_id, item_id')
            ->from($this->_sTable)
            ->where('comment_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aItem['item_id'])) {
            return false;
        }

        if (!Phpfox::hasCallback($aItem['type_id'], 'getRedirectComment')) {
            return Phpfox::permalink('', 0);
        }

        $url = Phpfox::callback($aItem['type_id'] . '.getRedirectComment', $aItem['item_id']);
        if (strpos($url, '?')) {
            $url .= '&';
        } else {
            $url .= '?';
        }
        $url .= 'comment=' . $aItem['comment_id'];

        return $url;
    }

    /**
     * @return array
     */
    public function getNotificationSettings()
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_service_callback_getnotificationsettings__start')) ? eval($sPlugin) : false);

        return [
            'comment.add_new_comment'     => [
                'phrase'  => _p('new_comments'),
                'default' => 1
            ],
            'comment.approve_new_comment' => [
                'phrase'  => _p('comments_for_approval'),
                'default' => 1
            ]
        ];
    }

    /**
     * @param int $iId
     *
     * @return bool|string
     */
    public function getReportRedirect($iId)
    {
        return $this->getRedirectRequest($iId);
    }

    /**
     * Action to take when user cancelled their account
     *
     * @param int $iUser
     */
    public function onDeleteUser($iUser)
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_service_callback_ondeleteuser__start')) ? eval($sPlugin) : false);

        $aComments = $this->database()
            ->select('comment_id, type_id, item_id, parent_id')
            ->from($this->_sTable)
            ->where('user_id = ' . (int)$iUser)
            ->execute('getSlaveRows');
        foreach ($aComments as $aComment) {
            Phpfox::getService('comment.process')->delete($aComment['comment_id']);
            // update total comment of item
            if (empty($aComment['parent_id']) && Phpfox::hasCallback($aComment['type_id'], 'deleteComment')) {
                Phpfox::callback($aComment['type_id'] . '.deleteComment', $aComment['item_id']);
            }
        }
    }

    /**
     * @return array
     */
    public function spamCheck()
    {
        return [
            'phrase' => _p('comment_title'),
            'value'  => Phpfox::getService('comment')->getSpamTotal(),
            'link'   => Phpfox_Url::instance()->makeUrl('admincp.comment.spam-comments')
        ];
    }

    /**
     * @return array
     */
    public function reparserList()
    {
        return [
            'name'       => _p('comments_text'),
            'table'      => 'comment_text',
            'original'   => 'text',
            'parsed'     => 'text_parsed',
            'item_field' => 'comment_id'
        ];
    }

    /**
     * @return array
     */
    public function getDashboardActivity()
    {
        $aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);

        return [
            _p('comments_activity') => $aUser['activity_comment']
        ];
    }

    /**
     * @return array
     */
    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        return [
            'phrase' => _p('new_comments_stats'),
            'value'  => $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('comment'))
                ->where('view_id = 0 AND time_stamp >= ' . $iToday)
                ->execute('getSlaveField')
        ];
    }

    /**
     * @return array
     */
    public function updateCounterList()
    {
        $aList = [];
        (($sPlugin = Phpfox_Plugin::get('comment.component_service_callback_updatecounterlist__start')) ? eval($sPlugin) : false);
        return $aList;
    }

    /**
     * @param $iId
     * @param $iPage
     * @param $iPageLimit
     *
     * @return array|bool|int|string
     * @throws \Exception
     */
    public function updateCounter($iId, $iPage, $iPageLimit)
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_service_callback_updatecounter__start')) ? eval($sPlugin) : false);

        $sTable = Phpfox::getT('comment');
        if (!db()->tableExists($sTable)) {
            return Phpfox_Error::set(_p('the_database_table_table_does_not_exist', ['table' => $sTable]));
        }

        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('comment'))
            ->where('type_id = \'profile\'')
            ->execute('getSlaveField');

        $aRows = $this->database()->select('m.comment_id, i.user_id AS owner_user_id')
            ->from(Phpfox::getT('comment'), 'm')
            ->join($sTable, 'oc', 'oc.cid = m.upgrade_item_id')
            ->join(Phpfox::getT('user'), 'i', 'i.upgrade_user_id = oc.itemid')
            ->where('type_id = \'profile\'')
            ->limit($iPage, $iPageLimit, $iCnt)
            ->execute('getSlaveRows');

        foreach ($aRows as $aRow) {
            $this->database()->update(Phpfox::getT('comment'), ['owner_user_id' => $aRow['owner_user_id']], 'comment_id = ' . (int)$aRow['comment_id']);
        }

        (($sPlugin = Phpfox_Plugin::get('comment.component_service_callback_updatecounter__end')) ? eval($sPlugin) : false);

        return $iCnt;
    }

    /**
     * @return array
     */
    public function getActivityPointField()
    {
        return [
            _p('comments_activity') => 'activity_comment'
        ];
    }

    /**
     * @return array
     */
    public function pendingApproval()
    {
        return [
            'phrase' => _p('comments_approve'),
            'value'  => $this->getPendingTotal(),
            'link'   => Phpfox_Url::instance()->makeUrl('admincp.comment.pending-comments')
        ];
    }

    /**
     * @return array|int|string
     */
    public function getPendingTotal()
    {
        return $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('comment'))->where('view_id = 1')
            ->execute('getSlaveField');
    }

    /**
     * @return array
     */
    public function getAdmincpAlertItems()
    {
        $iTotalPending = $this->getPendingTotal();
        return [
            'message' => _p('you_have_total_pending_comments', ['total' => $iTotalPending]),
            'value'   => $iTotalPending,
            'link'    => Phpfox_Url::instance()->makeUrl('admincp.comment.pending-comments')
        ];
    }

    /**
     * @return string to parse to url
     */
    public function getAjaxProfileController()
    {
        return 'comment.profile';
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        if (preg_match("/^getNewsFeed(.*?)$/i", $sMethod, $aMatches)) {
            $sModuleId = (isset($aMatches[1])) ? strtolower(explode('_', $aMatches[1])[0]) : false;

            //Check module is enable or not
            if ($sMethod === false || !Phpfox::isModule($sModuleId)) {
                return false;
            }

            return Phpfox::hasCallback(strtolower($aMatches[1]),'getCommentNewsFeed') ? Phpfox::callback(strtolower($aMatches[1]) . '.getCommentNewsFeed', $aArguments[0],
                (isset($aArguments[1]) ? $aArguments[1] : null)) : false;
        } else if (preg_match("/^getFeedRedirect(.*?)$/i", $sMethod, $aMatches)) {
            return Phpfox::callback(strtolower($aMatches[1]) . '.getFeedRedirect', $aArguments[0], $aArguments[1]);
        } else if (preg_match("/^getNotificationFeed(.*?)$/i", $sMethod, $aMatches)) {
            if (empty($aMatches[1])) {
                $aMatches[1] = 'feed';
            }
            $aMatches[1] = trim($aMatches['1'], '_');

            return Phpfox::hasCallback(strtolower($aMatches[1]),'getCommentNotificationFeed') ? Phpfox::callback(strtolower($aMatches[1]) . '.getCommentNotificationFeed', $aArguments[0]) : false;
        } else if (preg_match("/^getNotificationDeny_Comment_(.+)$/i", $sMethod, $aMatches)) {
            if (count($aMatches) < 1) {
                return false;
            }

            $sModuleId = strtolower($aMatches[1]);

            if (Phpfox::hasCallback($sModuleId, "getRedirectComment")) {
                $sLink = Phpfox::callback("$sModuleId.getRedirectComment", $aArguments[0]['item_id']);
            } else {
                $sLink = '#';
            }

            return [
                'link'             => $sLink,
                'message'          => _p('your_comment_has_been_denied'),
                'icon'             => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
                'no_profile_image' => true
            ];
        } else if (preg_match("/^getNotification(.*?)$/i", $sMethod, $aMatches)) {
            $sModuleId = (isset($aMatches[1])) ? strtolower(explode('_', $aMatches[1])[0]) : false;

            // Check module is enable or not
            if ($sMethod === false || !Phpfox::isModule($sModuleId) || !Phpfox::hasCallback(strtolower($aMatches[1]), 'getCommentNotification')) {
                return false;
            }
            return Phpfox::callback(strtolower($aMatches[1]) . '.getCommentNotification', $aArguments[0]);
        } else if (preg_match("/^getAjaxCommentVar(.*?)$/i", $sMethod, $aMatches)) {
            return Phpfox::hasCallback(strtolower($aMatches[1]), 'getAjaxCommentVar')
                ? Phpfox::callback(strtolower($aMatches[1]) . '.getAjaxCommentVar') : false;
        } else if (preg_match("/^getCommentItem(.*?)$/i", $sMethod, $aMatches)) {
            return Phpfox::hasCallback(strtolower($aMatches[1]), 'getCommentItem')
                ? Phpfox::callback(strtolower($aMatches[1]) . '.getCommentItem', $aArguments[0]) : false;
        } else if (preg_match("/^addComment(.*?)$/i", $sMethod, $aMatches)) {
            return Phpfox::hasCallback(strtolower($aMatches[1]), 'addComment')
                ? Phpfox::callback(strtolower($aMatches[1]) . '.addComment', $aArguments[0],
                (isset($aArguments[1]) ? $aArguments[1] : null), (isset($aArguments[2]) ? $aArguments[2] : null)) : false;
        }

        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('comment.service_callback__call')) {
            return eval($sPlugin);
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);

        return null;
    }

    /**
     * @param $iUserId
     *
     * @return array|bool
     */
    public function getUserStatsForAdmin($iUserId)
    {
        if (!$iUserId) {
            return false;
        }

        $iTotal = db()->select('COUNT(*)')
            ->from(':comment')
            ->where('user_id =' . (int)$iUserId)
            ->execute('getField');

        return [
            'total_name'  => _p('comments'),
            'total_value' => $iTotal,
            'type'        => 'item'
        ];
    }

    /**
     * @param null $aParams
     *
     * @return array
     */
    public function getUploadParams($aParams = null)
    {
        return Phpfox::getService('comment')->getUploadParams($aParams);
    }

    /**
     * @param null $aParams
     *
     * @return array
     */
    public function getUploadParamsComment($aParams = null)
    {
        return Phpfox::getService('comment')->getUploadParamsComment($aParams);
    }
}