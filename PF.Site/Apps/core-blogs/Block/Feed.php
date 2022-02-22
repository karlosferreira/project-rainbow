<?php

namespace Apps\Core_Blogs\Block;

use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Feed
 * @package Apps\Core_Blogs\Block
 */
class Feed extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if ($iFeedId = $this->getParam('this_feed_id')) {
            $aAssign = $this->getParam('custom_param_blog_' . $iFeedId);
            $iSponsorFeedId = $this->getParam('sponsor_feed_id');
            if (Phpfox::isAppActive('Core_BetterAds') && (int)$iFeedId === (int)$iSponsorFeedId) {
                $iSponsorId = Phpfox::getService('ad.get')->getFeedSponsors($iFeedId);
                $aAssign['sLink'] = $iSponsorId ? Phpfox::getLib('url')->makeUrl('ad.sponsor', ['view' => $iSponsorId]) : $aAssign['sLink'];
            }

            if (!empty($aAssign)) {
                $this->template()->assign(
                    $aAssign
                );
            }
        }
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {

        (($sPlugin = Phpfox_Plugin::get('blog.component_block_feed_clean')) ? eval($sPlugin) : false);
    }
}
