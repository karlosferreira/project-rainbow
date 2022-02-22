<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Polls\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class Poll extends \Phpfox_Service
{
    const NO_THUMBNAIL_TIME = 1512098279;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('poll');
    }

    /**
     *    checks the format of the array and default answers and empty values
     *
     * @param array $aVals input from the user
     *
     * @return boolean true on success | array(string) on error
     */
    public function checkStructure($aVals)
    {

        $aErrors = [];
        // check the question so its not empty
        if (empty($aVals['question']) || ($aVals['question'] == '') || (mb_strlen($aVals['question']) > 255)) {
            $aErrors[0] = _p('maximum_length_for_the_question_is_255_characters_and_it_cannot_be_empty');
        }

        $iTotalPass = 0;
        foreach ($aVals['answer'] as $aAnswer) {
            if (!Phpfox::getLib('parse.format')->isEmpty($aAnswer['answer'])) {
                $iTotalPass++;
            }

            if ((strpos(strtolower($aAnswer['answer']), 'answer') === false) || (strpos($aAnswer['answer'],
                        '...') === false)
            ) {
                continue;
            }
            // default answers format is "Answer X[Y]..."
            if (is_numeric($aAnswer['answer'])) {
                continue;
            }

            $sAnswer = str_replace('answer', '', strtolower($aAnswer['answer']));
            $sAnswer = trim(str_replace('...', '', $sAnswer));
            if (is_numeric($sAnswer)) {
                $aErrors[1] = _p('we_dont_allow_default_answers_answer', ['answer' => $aAnswer['answer']]);
                continue;
            }

            if (strlen($aAnswer['answer']) > 150) {
                $aErrors[2] = _p('maximum_length_for_the_answers_is_150_characters');
                continue;
            }
        }

        if ($iTotalPass < 2) {
            $aErrors[3] = _p('you_need_to_write_at_least_2_answers');
        }

        if (!is_array($aVals['answer']) || empty($aVals['answer']) || count($aVals['answer']) < 2) {
            $aErrors[3] = _p('you_need_to_write_at_least_2_answers');
        }

        if (!empty($aErrors)) {
            return $aErrors;
        }
        return true;

    }

    /**
     * Gets one poll from the database given its id
     *
     * @param int $iPollId poll id
     * @param int $iUser   , deprecated, remove in 4.7.0
     *
     * @return array
     */
    public function getPollById($iPollId, $iUser = null)
    {
        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getpollbyid_start')) ? eval($sPlugin) : false);

        $aPoll = $this->getPollByUrl($iPollId);

        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getpollbyid_end')) ? eval($sPlugin) : false);

        return $aPoll;
    }

    /**
     * Gets one poll given its question_url
     *
     * @param string  $sUrl      question_url
     * @param boolean $iPage     , deprecated, remove in 4.7.0
     * @param boolean $iPageSize , deprecated, remove in 4.7.0
     * @param boolean $bIsView
     *
     * @param bool    $bShowAnswer
     *
     * @return array
     */
    public function getPollByUrl($sUrl, $iPage = false, $iPageSize = false, $bIsView = false, $bShowAnswer = false)
    {
        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getpollbyurl_start')) ? eval($sPlugin) : false);

        if (Phpfox::isModule('track') && $bIsView) {
            $sJoinQuery = Phpfox::isUser() ? 'track.user_id = ' . Phpfox::getUserBy('user_id') : 'track.ip_address = \'' . $this->database()->escape(Phpfox::getIp()) . '\'';
            $this->database()->select('track.item_id AS poll_is_viewed, ')->leftJoin(Phpfox::getT('track'), 'track',
                'track.item_id = p.poll_id AND track.type_id=\'poll\' AND ' . $sJoinQuery);
        }

        if (Phpfox::isModule('friend') && Phpfox::isUser()) {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'),
                'f', "f.user_id = p.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        } else {
            $this->database()->select('false as is_friend, ');
        }

        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l',
                    'l.type_id = \'poll\' AND l.item_id = p.poll_id AND l.user_id = ' . Phpfox::getUserId());
        }

        $aPoll = $this->database()->select(Phpfox::getUserField() . ', p.*,' . (Phpfox::getParam('core.allow_html') ? 'p.description_parsed' : 'p.description') . ' AS description, pd.background, pd.percentage, pd.border, pr.user_id as voted, pr.answer_id')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->leftJoin(Phpfox::getT('poll_design'), 'pd', 'pd.poll_id = p.poll_id')
            ->leftJoin(Phpfox::getT('poll_result'), 'pr',
                'pr.poll_id = p.poll_id AND pr.user_id = ' . Phpfox::getUserId())
            ->where('p.poll_id = ' . (int)$sUrl)
            ->execute('getSlaveRow');

        // Control
        if (empty($aPoll)) {
            return false;
        }
        if (!isset($aPoll['poll_is_viewed'])) {
            $aPoll['poll_is_viewed'] = 0;
        }
        if (!isset($aPoll['is_liked'])) {
            $aPoll['is_liked'] = 0;
        }
        $aPoll['user_voted_this_poll'] = false;

        $aAnswers = $this->database()->select('pa.*,pr.user_id as voted')
            ->from(Phpfox::getT('poll_answer'), 'pa')
            ->leftJoin(Phpfox::getT('poll_result'), 'pr',
                'pr.answer_id = pa.answer_id AND pr.user_id =' . Phpfox::getUserId())
            ->where('pa.poll_id = ' . (int)$aPoll['poll_id'])
            ->order('voted DESC, pa.ordering ASC')
            ->execute('getSlaveRows');

        $iTotalVotes = 0;
        foreach ($aAnswers as $aAnswer) {
            $iTotalVotes += $aAnswer['total_votes'];
        }
        foreach ($aAnswers as $iKeyAnswer => $aAnswer) {
            if (isset($aAnswer['total_votes']) && $aAnswer['total_votes'] > 0) {
                $aAnswers[$iKeyAnswer]['vote_percentage'] = round(($aAnswer['total_votes'] * 100) / $iTotalVotes);
            } else {
                $aAnswers[$iKeyAnswer]['vote_percentage'] = 0;
            }
            if ($bShowAnswer) {
                $aAnswers[$iKeyAnswer]['some_votes'] = $this->getVotesByAnswer($aAnswer['answer_id'], null, 2);
            }
        }

        $aPoll['total_votes'] = $iTotalVotes;

        // check if we should randomize the answers
        if ($aPoll['randomize'] == 1) {
            shuffle($aAnswers);
        }
        $aPoll['bookmark'] = \Phpfox_Url::instance()->permalink('poll', $aPoll['poll_id'], $aPoll['question']);
        $aPoll['answer'] = $aAnswers;
        if (!empty($aPoll['answer_id'])) {
            $aPoll['user_voted_this_poll'] = true;
        }
        if (!isset($aPoll['is_friend'])) {
            $aPoll['is_friend'] = 0;
        }
        if ($aPoll['close_time']) {
            $aPoll['enable_close'] = 1;
        }
        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getpollbyurl_end')) ? eval($sPlugin) : false);

        return $aPoll;
    }

    public function getVotesByAnswer($iAnswerId, $iPage = 0, $iPageSize = 5, &$iCount = 0)
    {

        $iCount = db()->select('COUNT(*)')
            ->from(':poll_result', 'pr')
            ->join(':poll_answer', 'pa', 'pr.answer_id = pa.answer_id')
            ->join(':user', 'u', 'u.user_id = pr.user_id')
            ->where('pa.answer_id = ' . (int)$iAnswerId)
            ->execute('getField');
        $aVoteData = [];
        if ($iCount) {
            $aVoteData = db()->select('pa.answer, pa.total_votes, pr.*, ' . Phpfox::getUserField())
                ->from(':poll_result', 'pr')
                ->join(':poll_answer', 'pa', 'pr.answer_id = pa.answer_id')
                ->join(':user', 'u', 'u.user_id = pr.user_id')
                ->where('pa.answer_id = ' . (int)$iAnswerId)
                ->limit($iPage, $iPageSize, $iCount)
                ->execute('getSlaveRows');
        }
        return $aVoteData;
    }

    /**
     *    returns the ids of the answers that $iUser has voted
     *
     * @param int $iUser
     * @param int $iPoll
     *
     * @return array
     */
    public function getVotedAnswersByUser($iUser, $iPoll = null)
    {
        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getVotedAnswersByUser_start')) ? eval($sPlugin) : false);
        $aReturns = $this->database()->select('pr.answer_id')
            ->from(Phpfox::getT('poll_result'), 'pr')
            ->where('pr.user_id = ' . (int)$iUser . (isset($iPoll) ? ' AND pr.poll_id = ' . (int)$iPoll : ''))
            ->execute('getSlaveRows');
        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getVotedAnswersByUser_end')) ? eval($sPlugin) : false);
        return $aReturns;
    }

    /**
     * Returns if a poll is being moderated
     *
     * @param integer $iPoll
     *
     * @return boolean
     */
    public function isModerated($iPoll)
    {
        $iModerated = $this->database()->select('view_id')
            ->from($this->_sTable)
            ->where('poll_id = ' . (int)$iPoll)
            ->execute('getSlaveField');

        return (is_numeric($iModerated) && $iModerated == 1);
    }

    /**
     * Gets answers specific to one poll
     *
     * @param integer $iPoll phpfox_poll.poll_id
     *
     * @return array
     */
    public function getAnswers($iPoll)
    {
        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getanswers_start')) ? eval($sPlugin) : false);
        $aAnswers = $this->database()->select('pa.*, pc.*')
            ->from(Phpfox::getT('poll_answer'), 'pa')
            ->leftJoin(Phpfox::getT('poll_design'), 'pc', 'pc.poll_id = ' . $iPoll)
            ->where('pa.poll_id = ' . (int)$iPoll)
            ->execute('getSlaveRows');

        // total votes
        $iTotalVotes = 0;
        foreach ($aAnswers as $aAnswer) {
            $iTotalVotes = $iTotalVotes + $aAnswer['total_votes'];
        }
        foreach ($aAnswers as &$aAnswer) {
            $aAnswer['vote_percentage'] = ($aAnswer['total_votes'] > 0 ? round($aAnswer['total_votes'] * 100 / $iTotalVotes) : 0);
        }

        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getanswers_end')) ? eval($sPlugin) : false);
        return $aAnswers;

    }

    /**
     * Gets the newer polls available
     *
     * @param integer $iLimit How many polls to fetch
     *
     * @return array
     */
    public function getNew($iLimit = 4)
    {
        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getnew_start')) ? eval($sPlugin) : false);

        $aResult = $this->database()->select('p.poll_id, p.time_stamp, p.question, p.question_url, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('' . \Phpfox_Database::instance()->isNull('p.module_id') . ' AND p.view_id = 0 AND p.privacy = 1')
            ->limit($iLimit)
            ->order('p.time_stamp DESC')
            ->execute('getSlaveRows');

        (($sPlugin = Phpfox_Plugin::get('poll.service_poll_getnew_end')) ? eval($sPlugin) : false);
        return $aResult;
    }

    /**
     * Used for paging, mostly an ajax call
     *
     * @param integer $iPollid
     * @param int     $iPage
     * @param int     $iPageSize
     * @param int     $iCount
     *
     * @return array
     */
    public function getVotes($iPollid, $iPage = 0, $iPageSize = 0, &$iCount = 0)
    {
        $aUsers = db()->select('pr.user_id')
            ->from(Phpfox::getT('poll_result'), 'pr')
            ->join($this->_sTable, 'p', 'pr.poll_id = p.poll_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pr.user_id')
            ->where('p.poll_id = ' . (int)$iPollid)
            ->group('pr.user_id')
            ->execute('getRows');
        $iCount = count($aUsers);
        $aResult = [];
        $userId = Phpfox::getUserId();
        if ($iCount) {
            $aUserVotes = db()->select(Phpfox::getUserField() . ', p.hide_vote, p.user_id as poll_user_id')
                ->from(Phpfox::getT('poll_result'), 'pr')
                ->join($this->_sTable, 'p', 'p.poll_id = ' . (int)$iPollid)
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pr.user_id')
                ->order('pr.time_stamp DESC')
                ->limit($iPage, $iPageSize, $iCount)
                ->where('pr.poll_id = p.poll_id')
                ->group('pr.user_id')
                ->execute('getSlaveRows');

            if (count($aUserVotes)) {
                foreach ($aUserVotes as $aUserVote) {
                    //If user isn't the own poll and the Vote's poll isn't public then not append data
                    if (!(
                        ((Phpfox::getUserParam('poll.can_view_user_poll_results_own_poll') && $aUserVote['poll_user_id'] == $userId) || Phpfox::getUserParam('poll.can_view_user_poll_results_other_poll'))
                        && (Phpfox::getUserParam('privacy.can_view_all_items') || !$aUserVote['hide_vote'] || ($aUserVote['hide_vote'] && $userId == $aUserVote['poll_user_id']))
                    )) {
                        continue;
                    }
                    $aVoted = db()->select('pr.*, pa.answer')
                        ->from(':poll_result', 'pr')
                        ->join($this->_sTable, 'p', 'p.poll_id = pr.poll_id')
                        ->join(':poll_answer', 'pa', 'pr.answer_id = pa.answer_id')
                        ->where('pr.user_id =' . $aUserVote['user_id'] . ' AND p.poll_id = ' . (int)$iPollid)
                        ->execute('getRows');
                    $aResult[$aUserVote['user_id']] = $aUserVote;
                    $aResult[$aUserVote['user_id']]['total_votes'] = 0;
                    $sAnswer = '';
                    foreach ($aVoted as $vote) {
                        $aResult[$aUserVote['user_id']]['total_votes']++;
                        $aResult[$aUserVote['user_id']]['time_stamp'] = $vote['time_stamp'];
                        $sAnswer .= '"' . Phpfox::getLib('parse.output')->clean($vote['answer']) . '", ';
                    }
                    $aResult[$aUserVote['user_id']]['answer'] = rtrim($sAnswer, ', ');
                }
            }
        }
        return $aResult;
    }

    /**
     * Checks for permissions on editing a poll. This function doesnt call the database
     *
     * @param integer $iUser The user id to check for
     *
     * @return boolean
     */
    public function bCanEdit($iUser)
    {
        return ($iUser == Phpfox::getUserId() && Phpfox::getUserParam('poll.poll_can_edit_own_polls')) || Phpfox::getUserParam('poll.poll_can_edit_others_polls');
    }

    /**
     * Checks for permissions on deleting a poll. This function does'nt call the database
     *
     * @param integer $iUser The user id to check for
     *
     * @return boolean
     */
    public function bCanDelete($iUser)
    {
        if ($iUser == Phpfox::getUserId()) {
            return Phpfox::getUserParam('poll.poll_can_delete_own_polls') || Phpfox::getUserParam('poll.poll_can_delete_others_polls');
        } else {
            return Phpfox::getUserParam('poll.poll_can_delete_others_polls');
        }
    }

    public function getInfoForAction($aItem)
    {
        if (is_numeric($aItem)) {
            $aItem = ['item_id' => $aItem];
        }
        $aRow = $this->database()->select('p.poll_id, p.question as title, p.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('poll'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.poll_id = ' . (int)$aItem['item_id'])
            ->execute('getSlaveRow');

        if (empty($aRow)) {
            d($aRow);
            d($aItem);
        }

        $aRow['link'] = \Phpfox_Url::instance()->permalink('poll', $aRow['poll_id'], $aRow['title']);
        return $aRow;
    }

    public function getPendingSponsorItems()
    {
        $sCacheId = $this->cache()->set('poll_pending_sponsor');
        if (false === ($aItems = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('poll_id')
                ->from(Phpfox::getT('poll'), 'm')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = m.poll_id')
                ->where('m.is_sponsor = 0 AND s.is_custom = 2 AND s.module_id = "poll"')
                ->execute('getSlaveRows');
            $aItems = array_column($aRows, 'poll_id');
            $this->cache()->save($sCacheId, $aItems);
        }
        return $aItems;
    }

    public function canPurchaseSponsorItem($iItemId)
    {
        $aIds = $this->getPendingSponsorItems();
        return in_array($iItemId, $aIds) ? false : true;
    }


    /**
     * @param $aRow
     */
    public function getPermissions(&$aRow)
    {
        $aRow['canEdit'] = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('poll.poll_can_edit_own_polls')) || Phpfox::getUserParam('poll.poll_can_edit_others_polls'));
        $aRow['canDelete'] = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('poll.poll_can_delete_own_polls')) || Phpfox::getUserParam('poll.poll_can_delete_others_polls'));
        $aRow['iSponsorInFeedId'] = Phpfox::isModule('feed') && (Phpfox::getService('feed')->canSponsoredInFeed('poll', $aRow['poll_id']) === true);

        $aRow['canSponsor'] = $aRow['canSponsorInFeed'] = $aRow['canPurchaseSponsor'] = false;
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aRow['canSponsor'] = (Phpfox::getUserParam('poll.can_sponsor_poll') && $aRow['view_id'] == 0 && $aRow['item_id'] == 0);
            $aRow['canSponsorInFeed'] = (Phpfox::isModule('feed') && (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('feed.can_purchase_sponsor')) || Phpfox::getUserParam('feed.can_sponsor_feed')) && Phpfox::getService('feed')->canSponsoredInFeed('poll', $aRow['poll_id']));
            $bCanPurchaseSponsor = $this->canPurchaseSponsorItem($aRow['poll_id']);
            $aRow['canPurchaseSponsor'] = ($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('poll.can_purchase_sponsor_poll') && $aRow['view_id'] == 0 && $aRow['item_id'] == 0 && $bCanPurchaseSponsor);
        }

        $aRow['canApprove'] = (Phpfox::getUserParam('poll.poll_can_moderate_polls') && $aRow['view_id'] == 1);
        $aRow['canFeature'] = (Phpfox::getUserParam('poll.can_feature_poll') && $aRow['view_id'] == 0 && $aRow['item_id'] == 0);
        $aRow['hasPermission'] = ($aRow['canEdit'] || $aRow['canDelete'] || $aRow['canSponsor'] || $aRow['canApprove'] || $aRow['canFeature'] || $aRow['canPurchaseSponsor'] || $aRow['canSponsorInFeed']);
        $aRow['canVotesWithCloseTime'] = $aRow['close_time'] == 0 || $aRow['close_time'] > PHPFOX_TIME;
    }

    public function buildMenu()
    {
        if (!defined('PHPFOX_IS_USER_PROFILE')) {
            $iMyTotal = Phpfox::getService('poll')->getMyTotal();

            $aFilterMenu = [
                _p('all_polls')                                                                                                    => '',
                _p('my_polls') . ($iMyTotal ? '<span class="count-item">' . ($iMyTotal > 99 ? '99+' : $iMyTotal) . '</span>' : '') => 'my'
            ];
            if (Phpfox::isModule('friend') && !Phpfox::getParam('core.friends_only_community')) {
                $aFilterMenu[_p('friends_polls')] = 'friend';
            }

            if (Phpfox::getUserParam('poll.poll_can_moderate_polls')) {
                $iPendingTotal = \Phpfox::getService('poll')->getPendingTotal();

                if ($iPendingTotal) {
                    $aFilterMenu[_p('pending_polls') . ' <span id="poll_pending" class="pending count-item">' . ($iPendingTotal > 99 ? '99+' : $iPendingTotal) . '</span>'] = 'pending';
                }
            }

            Phpfox::getLib('template')->buildSectionMenu('poll', $aFilterMenu);
        }
    }

    /**
     * @return int
     */
    public function getMyTotal()
    {
        $sWhere = 'user_id = ' . Phpfox::getUserId();
        $aModules = ['user'];
        $sWhere .= ' AND (module_id NOT IN ("' . implode('","', $aModules) . '") OR module_id is NULL)';

        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    /**
     * Gets the total number of polls pending approval
     * @return int
     */
    public function getPendingTotal()
    {
        return (int)$this->database()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('view_id = 1')
            ->execute('getSlaveField');
    }

    public function getConditionsForSettingPageGroup($sPrefix = 'p')
    {
        $aModules = [];
        // Apply settings show poll of pages / groups
        if (Phpfox::getParam('poll.display_polls_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (Phpfox::getParam('poll.display_polls_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        if (count($aModules)) {
            return ' AND (' . $sPrefix . '.module_id IN (\'' . implode('\',\'',
                    $aModules) . '\') OR ' . $sPrefix . '.module_id IS NULL)';
        } else {
            return ' AND ' . $sPrefix . '.module_id IS NULL';
        }
    }

    public function getFeatured($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('poll_featured');
        if (($sPollIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sPollIds = '';
            $sExtraCond = $this->getConditionsForSettingPageGroup();
            $aPollIds = $this->database()->select('p.poll_id')
                ->from($this->_sTable, 'p')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->where('p.view_id = 0 AND p.is_featured = 1' . $sExtraCond)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aPollIds as $key => $aId) {
                if ($key != 0) {
                    $sPollIds .= ',' . $aId['poll_id'];
                } else {
                    $sPollIds = $aId['poll_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sPollIds);
            }
        }
        if (empty($sPollIds)) {
            return [];
        }
        $aPollIds = explode(',', $sPollIds);
        shuffle($aPollIds);
        $aPollIds = array_slice($aPollIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));

        $aPolls = $this->database()->select('p.*, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.poll_id IN (' . implode(',', $aPollIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        if (!isset($aPolls[0]) || empty($aPolls[0])) {
            return [];
        }
        foreach ($aPolls as $iKey => $aPoll) {
            $aPolls[$iKey]['url'] = \Phpfox_Url::instance()->permalink('poll', $aPoll['poll_id'], $aPoll['question']);
        }

        shuffle($aPolls);

        $sPolls = implode(',', array_map(function ($item) {
            return $item['poll_id'];
        }, $aPolls));
        $aAnswers = $this->database()->select('pa.*, pr.user_id as voted')
            ->from(Phpfox::getT('poll_answer'), 'pa')
            ->where('pa.poll_id IN(' . $sPolls . ')')
            ->leftJoin(Phpfox::getT('poll_result'), 'pr',
                'pr.answer_id = pa.answer_id AND pr.user_id = ' . Phpfox::getUserId())
            ->order('pa.ordering ASC')
            ->execute('getSlaveRows');

        $aTotalVotes = [];
        foreach ($aAnswers as $aAnswer) {
            if ($aAnswer['total_votes'] > 0) {
                if (isset($aTotalVotes[$aAnswer['poll_id']])) {
                    $aTotalVotes[$aAnswer['poll_id']] += intval($aAnswer['total_votes']);
                } else {
                    $aTotalVotes[$aAnswer['poll_id']] = intval($aAnswer['total_votes']);
                }
            }
        }

        foreach ($aPolls as $key => $aPoll) {
            $aPolls[$key]['total_votes'] = isset($aTotalVotes[$aPoll['poll_id']]) ? $aTotalVotes[$aPoll['poll_id']] : 0;
        }

        return $aPolls;
    }

    /**
     * @param int $iLimit
     * @param int $iCacheTime
     *
     * @return array
     */
    public function getSponsored($iLimit = 4, $iCacheTime = 5)
    {
        $sCacheId = $this->cache()->set('poll_sponsored');
        if (($sPollIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sPollIds = '';
            $sExtraCond = $this->getConditionsForSettingPageGroup();
            $aPollIds = $this->database()->select('p.poll_id')
                ->from($this->_sTable, 'p')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = p.poll_id')
                ->where('p.view_id = 0 AND p.is_sponsor = 1 AND s.module_id = \'poll\' AND s.is_active = 1 AND s.is_custom = 3' . $sExtraCond)
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aPollIds as $key => $aId) {
                if ($key != 0) {
                    $sPollIds .= ',' . $aId['poll_id'];
                } else {
                    $sPollIds = $aId['poll_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sPollIds);
            }

        }
        if (empty($sPollIds)) {
            return [];
        }
        $aPollIds = explode(',', $sPollIds);
        shuffle($aPollIds);
        $aPollIds = array_slice($aPollIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aPolls = $this->database()->select(Phpfox::getUserField() . ',p.*, p.total_view as total_view_poll, s.* ')
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = p.poll_id AND s.module_id = \'poll\' AND s.is_active = 1 AND s.is_custom = 3')
            ->where('p.poll_id IN (' . implode(',', $aPollIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');

        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aPolls = Phpfox::getService('ad')->filterSponsor($aPolls);
        }

        if (empty($aPolls)) {
            return [];
        }

        $sPolls = implode(',', array_map(function ($item) {
            return $item['poll_id'];
        }, $aPolls));

        $aAnswers = $this->database()->select('pa.*, pr.user_id as voted')
            ->from(Phpfox::getT('poll_answer'), 'pa')
            ->where('pa.poll_id IN(' . $sPolls . ')')
            ->leftJoin(Phpfox::getT('poll_result'), 'pr',
                'pr.answer_id = pa.answer_id AND pr.user_id = ' . Phpfox::getUserId())
            ->order('pa.ordering ASC')
            ->execute('getSlaveRows');

        $aTotalVotes = [];
        foreach ($aAnswers as $aAnswer) {
            if ($aAnswer['total_votes'] > 0) {
                if (isset($aTotalVotes[$aAnswer['poll_id']])) {
                    $aTotalVotes[$aAnswer['poll_id']] += intval($aAnswer['total_votes']);
                } else {
                    $aTotalVotes[$aAnswer['poll_id']] = intval($aAnswer['total_votes']);
                }
            }
        }
        foreach ($aPolls as $key => $aPoll) {
            $aPolls[$key]['total_votes'] = isset($aTotalVotes[$aPoll['poll_id']]) ? $aTotalVotes[$aPoll['poll_id']] : 0;
            $aPolls[$key]['total_view'] = $aPoll['total_view_poll'];
        }
        shuffle($aPolls);

        return $aPolls;
    }

    public function isVoted($iAnswerId, $iUserId = null)
    {
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }

        return db()->select('COUNT(*)')
            ->from(':poll_result')
            ->where([
                'answer_id' => (int)$iAnswerId,
                'user_id'   => (int)$iUserId
            ])
            ->execute('getField');
    }

    /**
     * @return array
     */
    public function getUploadParams()
    {
        $iMaxFileSize = Phpfox::getUserParam('poll.poll_max_upload_size');
        $iMaxFileSize = $iMaxFileSize > 0 ? $iMaxFileSize / 1024 : 0;
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);
        $aCallback = [
            'max_size'        => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'type_list'       => ['jpg', 'jpeg', 'gif', 'png'],
            'upload_dir'      => Phpfox::getParam('poll.dir_image'),
            'upload_path'     => Phpfox::getParam('poll.url_image'),
            'thumbnail_sizes' => Phpfox::getParam('poll.thumbnail_sizes'),
            'label'           => _p('display_photo'),
            'is_required'     => Phpfox::getParam('poll.is_image_required'),
            'no_square'       => true
        ];
        return $aCallback;
    }

    public function getAnswersById($iAnswerId)
    {
        if (!$iAnswerId) {
            return false;
        }
        return db()->select('pa.*')
            ->from(':poll_answer', 'pa')
            ->join(':poll', 'p', 'p.poll_id = pa.poll_id')
            ->where('pa.answer_id =' . (int)$iAnswerId)
            ->execute('getRow');
    }

    public function checkLimitation($userId = null)
    {
        empty($userId) && $userId = Phpfox::getUserId();

        static $permissions = [];

        if (isset($permissions[$userId])) {
            return $permissions[$userId];
        }

        $limit = trim(Phpfox::getUserParam('poll.poll_total_items_can_create'));

        if (!isset($limit) || $limit == '') {
            $permissions[$userId] = true;
        } elseif (is_numeric($limit) && (int)$limit == 0) {
            $permissions[$userId] = false;
        } else {
            $total = (int)db()->select('COUNT(*)')
                ->from($this->_sTable)
                ->where([
                    'user_id' => $userId
                ])->executeField(false);
            $permissions[$userId] = $total < $limit;
        }

        return $permissions[$userId];
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
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('poll.service_poll__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}