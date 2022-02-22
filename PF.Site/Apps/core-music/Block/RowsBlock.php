<?php

namespace Apps\Core_Music\Block;

defined('PHPFOX') or exit('NO DICE!');

class RowsBlock extends \Phpfox_Component
{

    public function process()
    {
        if ($this_feed_id = $this->getParam('this_feed_id')) {
            $custom = $this->getParam('custom_param_' . $this_feed_id);
            $iSponsorFeedId = $this->getParam('sponsor_feed_id');

            $this->template()->assign([
                'aSongs'         => $custom,
                'bIsSponsorFeed' => (int)$this_feed_id === (int)$iSponsorFeedId,
                'iSponsorId'     => \Phpfox::isAppActive('Core_BetterAds') && ((int)$this_feed_id === (int)$iSponsorFeedId) ? \Phpfox::getService('ad.get')->getFeedSponsors($this_feed_id) : 0
            ]);
        }
        $this->template()->assign([
            'bIsInFeed' => true,
        ]);
    }
}