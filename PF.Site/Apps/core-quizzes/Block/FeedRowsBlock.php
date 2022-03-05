<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Quizzes\Block;

use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox;

defined('PHPFOX') or exit('NO DICE!');


class FeedRowsBlock extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if ($this_feed_id = $this->getParam('this_feed_id')) {
            $custom = $this->getParam('custom_param_' . $this_feed_id);
            $aQuestions = Phpfox::getService('quiz')->getQuestionsByQuizId($custom['quiz_id']);
            $aInitQuestions = array_slice($aQuestions, 0, 2);
            $iSponsorFeedId = $this->getParam('sponsor_feed_id');
            $this->template()->assign([
                'aQuiz' => $custom,
                'bIsFeedSponsor' => (int)$this_feed_id === (int)$iSponsorFeedId,
                'iSponsorId' => \Phpfox::isAppActive('Core_BetterAds') && ((int)$this_feed_id === (int)$iSponsorFeedId) ? \Phpfox::getService('ad.get')->getFeedSponsors($this_feed_id) : 0,
                'aInitQuestions' => $aInitQuestions,
                'iRestQuestion' => count($aInitQuestions) < count($aQuestions) ? count($aQuestions) - count($aInitQuestions) : 0,
                'sUrl' => $this->url()->makeUrl('quiz.'. $custom['quiz_id']. '.'. $custom['title']),
                'iHasTaken' => Phpfox::getService('quiz')->hasTakenQuiz(Phpfox::getUserId(), $custom['quiz_id'])
            ]);
        }
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('quiz.component_block_feed_rows')) ? eval($sPlugin) : false);
    }
}