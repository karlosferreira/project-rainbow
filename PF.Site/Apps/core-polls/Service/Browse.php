<?php
/**
 * [PHPFOX_HEADER]
 */
namespace Apps\Core_Polls\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');


class Browse extends \Phpfox_Service
{
    private $bIsApi;

    /**
     * Class constructor
     */
    public function __construct()
    {

    }

    public function query()
    {
    }

    public function isApi($value = true)
    {
        $this->bIsApi = $value;
    }

    /**
     * @param bool $bIsCount , deprecated, remove in 4.7.0
     * @param bool $bNoQueryFriend
     */
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Phpfox::getService('friend')->queryJoin($bNoQueryFriend)) {
            $this->database()->join(Phpfox::getT('friend'), 'friends',
                'friends.user_id = poll.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
    }

    public function processRows(&$aPolls2)
    {
        $aPolls = $aPolls2;
        $aPolls2 = [];
        $aTotalVotes = [];

        if (empty($aPolls)) {
            return;
        }

        $sPolls = implode(',', array_column($aPolls, 'poll_id'));     //If $sPolls empty next query will error
        if (empty($sPolls)) {
            $sPolls = 0;
        }
        $aAnswers = $this->database()->select('pa.*, pr.user_id as voted')
            ->from(Phpfox::getT('poll_answer'), 'pa')
            ->where('pa.poll_id IN(' . $sPolls . ')')
            ->leftJoin(Phpfox::getT('poll_result'), 'pr',
                'pr.answer_id = pa.answer_id AND pr.user_id = ' . Phpfox::getUserId())
            ->order('pa.ordering ASC')
            ->execute('getSlaveRows');

        // now merge both arrays by their poll_id and add the count for the total votes

        foreach ($aAnswers as $aAnswer) {
            if ($aAnswer['total_votes'] > 0) {
                if (isset($aTotalVotes[$aAnswer['poll_id']])) {
                    $aTotalVotes[$aAnswer['poll_id']] += intval($aAnswer['total_votes']);
                } else {
                    $aTotalVotes[$aAnswer['poll_id']] = intval($aAnswer['total_votes']);
                }
            }
        }

        foreach ($aPolls as $iKey => $aPoll) {
            $itemKey = $this->bIsApi ? $iKey : $aPoll['poll_id'];
            $aPolls2[$itemKey] = $aPoll;
            Phpfox::getService('poll')->getPermissions($aPolls2[$itemKey]);
            if (isset($aPoll['poll_id']['user_id']) && $aPoll['poll_id']['user_id'] == Phpfox::getUserId()) {
                $aPolls2[$itemKey]['user_voted_this_poll'] = 'true';
            } else {
                $aPolls2[$itemKey]['user_voted_this_poll'] = 'false';
            }

            if (!isset($aPolls2[$itemKey]['total_votes'])) {
                $aPolls2[$itemKey]['total_votes'] = 0;
            }

            if ($this->bIsApi && !empty($aPoll['image_path'])) {
                $aPolls2[$itemKey]['image_path'] = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aPoll['server_id'],
                    'path' => 'poll.url_image',
                    'file' => $aPoll['image_path'],
                    'suffix' => '_500',
                    'return_url' => true
                ]);
            }

            foreach ($aAnswers as &$aAnswer) { // we add the total votes for the poll

                if (!isset($aAnswer['vote_percentage'])) {
                    $aAnswer['vote_percentage'] = 0;
                }
                if (!isset($aAnswer['total_votes'])) {
                    $aAnswer['total_votes'] = 0;
                }
                // Normalize if user voted this answer or not
                if (isset($aAnswer['voted']) && $aAnswer['voted'] == Phpfox::getUserId()) {
                    $aAnswer['user_voted_this_answer'] = 1;
                } else {
                    $aAnswer['user_voted_this_answer'] = 2;
                }
                if ($aPoll['poll_id'] == $aAnswer['poll_id']) {
                    if ((isset($aTotalVotes[$aAnswer['poll_id']]) && $aTotalVotes[$aAnswer['poll_id']] > 0)) {
                        $aAnswer['vote_percentage'] = round(($aAnswer['total_votes'] / $aTotalVotes[$aAnswer['poll_id']]) * 100);
                    } else {
                        $aAnswer['vote_percentage'] = 0;
                    }

                    if ($this->bIsApi) {
                        $aPolls2[$itemKey]['answer'][] = $aAnswer;
                    } else {
                        $aPolls2[$itemKey]['answer'][$aAnswer['answer_id']] = $aAnswer;
                    }

                    $aPolls2[$itemKey]['total_votes'] += $aAnswer['total_votes'];
                }
            }

            if ($aPoll['randomize'] == 1 && !empty($aPolls2[$itemKey]['answer'])) {
                shuffle($aPolls2[$itemKey]['answer']);
            }
        }

        unset($aPolls);
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
        if ($sPlugin = Phpfox_Plugin::get('poll.service_browse__call')) {
            eval($sPlugin);
            return;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}