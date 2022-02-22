<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Quizzes\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author        phpFox
 * @package        Quiz
 * @version        4.5.3
 */
class Browse extends \Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {

    }

    public function query()
    {
    }

    /**
     * @param bool $bIsCount deprecated, will be removed in 4.7.0
     * @param bool $bNoQueryFriend
     */
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Phpfox::getService('friend')->queryJoin($bNoQueryFriend)) {
            $this->database()->join(Phpfox::getT('friend'), 'friends',
                'friends.user_id = q.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
    }

    public function processRows(&$aQuizzes)
    {
        foreach ($aQuizzes as $iKey => $aQuiz) {
            Phpfox::getService('quiz')->getPermissions($aQuizzes[$iKey]);
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
        if ($sPlugin = Phpfox_Plugin::get('quiz.service_browse__call')) {
            eval($sPlugin);
            return;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}