<?php

namespace Apps\Core_Pages\Block;

use Phpfox;
use Phpfox_Plugin;
use Phpfox_Component;

class Sponsored extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return false;
        }
        $iLimit = $this->getParam('limit', 4);
        if(!(int)$iLimit)
        {
            return false;
        }
        $iCacheTime = $this->getParam('cache_time', 5);
        $aSponsoredPages = Phpfox::getService('pages')->getSponsored($iLimit, $iCacheTime);

        if (!count($aSponsoredPages)) {
            return false;
        }

        if (Phpfox::isAppActive('Core_BetterAds')) {
            foreach($aSponsoredPages as $Sponsor)
            {
                Phpfox::getService('ad.process')->addSponsorViewsCount($Sponsor['sponsor_id'], 'pages');
            }
        }

        $this->template()->assign(array(
                'aSponsoredPages' => $aSponsoredPages,
                'sHeader' => _p('sponsored_pages'),
                'sDefaultCoverPath' => Phpfox::getParam('pages.default_cover_photo')
            )
        );

        if (Phpfox::getUserParam('pages.can_sponsor_pages') || Phpfox::getUserParam('pages.can_purchase_sponsor_pages')) {
            $this->template()
                ->assign([
                    'aFooter' => array(
                        _p('encourage_sponsor_pages') => $this->url()->makeUrl('pages', array('view' => 'my'))
                    )
                ]);
        }
        return 'block';
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return [
            [
                'info' => _p('Sponsored Pages Limit'),
                'description' => _p('Define the limit of how many sponsored pages can be displayed when viewing the page section. Set 0 will hide this block'),
                'value' => 4,
                'type' => 'integer',
                'var_name' => 'limit',
            ],
            [
                'info' => _p('Sponsored Pages Cache Time'),
                'description' => _p('Define how long we should keep the cache for the <b>Sponsored Pages</b> by minutes. 0 means we do not cache data for this block.'),
                'value' => Phpfox::getParam('core.cache_time_default'),
                'options' => Phpfox::getParam('core.cache_time'),
                'type' => 'select',
                'var_name' => 'cache_time',
            ]
        ];
    }
    /**
     * @return array
     */
    public function getValidation()
    {
        return [
            'limit' => [
                'def' => 'int',
                'min' => 0,
                'title' => _p('"Sponsored Pages Limit" must be greater than or equal to 0')
            ],
        ];
    }
    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('pages.component_block_sponsored_clean')) ? eval($sPlugin) : false);
    }
}