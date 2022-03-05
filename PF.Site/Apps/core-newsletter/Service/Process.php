<?php

namespace Apps\Core_Newsletter\Service;

use Phpfox_Service;
use Phpfox_Plugin;
use Phpfox_Error;
use Phpfox;

class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('newsletter');
    }

    /**
     * @param $iId int
     * @param $cronJobId int
     */
    public function processNewsletter($iId, $cronJobId = 0)
    {
        // Remove old job
        if ($cronJobId) {
            Phpfox::getLib('queue')->instance()->deleteJob($cronJobId, null, true);
        }
        // Add to job
        $iJobId = Phpfox::getLib('queue')->instance()->addJob('core_newsletter_send_email_users', [
            'newsletter_id' => $iId,
            'last_id' => 0,
        ], null, 3600);

        db()->update($this->_sTable, ['job_id' => $iJobId, 'state' => CORE_NEWSLETTER_STATUS_IN_PROGRESS],
            'newsletter_id = ' . $iId);
    }

    /**
     * Adds a new job to send the newsletter, first there is no cron jobs/tabs so this function's return
     * directs the flow of the script (refresh) to process the batches.
     * Sets the errors using Phpfox_Error::set
     * @param array $aVals
     * @param integer $iUser
     * @return int Next round to process | false on error.
     */
    public function add($aVals, $iUser)
    {
        $aVals['type_id'] = CORE_NEWSLETTER_EXTERNAL_TYPE; // Internal newsletters are deprecated since 3.3.0 beta 1

        // insert the values in the database
        $aInsert = array(
            'subject' => $this->preParse()->clean($aVals['subject']),
            'round' => 0,
            'state' => CORE_NEWSLETTER_STATUS_DRAFT,
            'age_from' => (int)$aVals['age_from'],
            'age_to' => (int)$aVals['age_to'],
            'type_id' => (int)$aVals['type_id'], // 1 = Internal ; 2 = External
            'country_iso' => $this->preParse()->clean($aVals['country_iso']),
            'gender' => (int)$aVals['gender'],
            'user_group_id' => '',
            'total' => (int)$aVals['total'],
            'user_id' => (int)$iUser,
            'time_stamp' => PHPFOX_TIME,
            'archive' => (isset($aVals['archive'])) ? (int)$aVals['archive'] : 2, // 2 = delete, 1 = keep
            'privacy' => (isset($aVals['privacy'])) ? (int)$aVals['privacy'] : 2
        );

        if (isset($aVals['is_user_group']) && $aVals['is_user_group'] == 2) {
            $aGroups = array();
            $aUserGroups = Phpfox::getService('user.group')->get();
            if (isset($aVals['user_group'])) {
                foreach ($aUserGroups as $aUserGroup) {
                    if (in_array($aUserGroup['user_group_id'], $aVals['user_group'])) {
                        $aGroups[] = $aUserGroup['user_group_id'];
                    }
                }
            }
            $aInsert['user_group_id'] = (count($aGroups) ? serialize($aGroups) : null);
        }

        // ** when we implement the cron job this is the place to set the state differently
        $iId = db()->insert($this->_sTable, $aInsert);
        db()->insert(Phpfox::getT('newsletter_text'), array(
                'newsletter_id' => $iId,
                'text_plain' => $this->preParse()->clean($aVals['txtPlain']),
                'text_html' => $aVals['text']
            )
        );

        return $iId;
    }

    /**
     * Update an exits newsletter
     *
     * @param $aVals
     * @param $iNewsletterId
     * @return true
     */
    public function update($aVals, $iNewsletterId)
    {
        // insert the values in the database
        $aUpdate = array(
            'subject' => $this->preParse()->clean($aVals['subject']),
            'state' => CORE_NEWSLETTER_STATUS_DRAFT,
            'age_from' => (int)$aVals['age_from'],
            'age_to' => (int)$aVals['age_to'],
            'country_iso' => $this->preParse()->clean($aVals['country_iso']),
            'gender' => (int)$aVals['gender'],
            'user_group_id' => '',
            'total' => (int)$aVals['total'],
            'archive' => (isset($aVals['archive'])) ? (int)$aVals['archive'] : 0,
            'privacy' => (isset($aVals['privacy'])) ? (int)$aVals['privacy'] : 0
        );

        if (isset($aVals['is_user_group']) && $aVals['is_user_group'] == 2) {
            $aGroups = array();
            $aUserGroups = Phpfox::getService('user.group')->get();
            if (isset($aVals['user_group'])) {
                foreach ($aUserGroups as $aUserGroup) {
                    if (in_array($aUserGroup['user_group_id'], $aVals['user_group'])) {
                        $aGroups[] = $aUserGroup['user_group_id'];
                    }
                }
            }
            $aUpdate['user_group_id'] = (count($aGroups) ? serialize($aGroups) : null);
        }

        // ** when we implement the cron job this is the place to set the state differently
        db()->update($this->_sTable, $aUpdate, 'newsletter_id = ' . $iNewsletterId);
        db()->update(Phpfox::getT('newsletter_text'), array(
            'text_plain' => $this->preParse()->clean($aVals['txtPlain']),
            'text_html' => $aVals['text']
        ), 'newsletter_id = ' . $iNewsletterId);

        return true;
    }

    public function stop($iNewsletterId)
    {
        db()->update($this->_sTable, array('state' => CORE_NEWSLETTER_STATUS_COMPLETED), 'newsletter_id = ' . $iNewsletterId);
    }

    /**
     * @param $iId
     * @return bool
     */
    public function delete($iId)
    {
        if (!Phpfox::isAdmin()) {
            return false;
        }
        $iState = db()->select('state')
            ->from($this->_sTable)
            ->where('newsletter_id = ' . (int)$iId)
            ->execute('getSlaveField');
        if ($iState == 1) {
            db()->update(Phpfox::getT('user_field'), array('newsletter_state' => 0), 'newsletter_state != 0');
        }
        db()->delete($this->_sTable, 'newsletter_id = ' . (int)$iId);
        db()->delete(Phpfox::getT('newsletter_text'), 'newsletter_id = ' . (int)$iId);
        return true;
    }

    /**
     * @param $aVals
     * @param $aUser
     * @return bool
     */
    public function sendExternal($aVals, $aUser)
    {
        return $this->_sendExternal($aVals, $aUser);
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('newsletter.service_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        return Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }

    private function _sendExternal($aVals, $aUser)
    {
        $aVals['text_html'] = str_replace("\n", '', $aVals['text_html']);
        $aVals['text_html'] = str_replace(['<br />', '<br >', '<p>&nbsp;</p>'], '', $aVals['text_html']);
        $aVals['text_html'] = str_replace(['<p>', '</p>'], ['<div>', '</div>'], $aVals['text_html']);
        $aVals['text_html'] = Phpfox::getLib('parse.input')->prepare($aVals['text_html']);

        Phpfox::getLib('mail')->to($aVals)
            ->aUser($aUser)
            ->subject($aVals['subject'])
            ->message(nl2br($aVals['text_html']))
            ->messagePlain(strip_tags($aVals['text_plain']))
            ->fromName(Phpfox::getParam('core.mail_from_name'))
            ->sendToSelf(true)
            ->send();

        return true;
    }
}
