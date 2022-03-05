<?php

namespace Apps\PHPfox_Groups\Block;

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
        $aSponsoredGroups = Phpfox::getService('groups')->getSponsored($iLimit, $iCacheTime);

        if (!count($aSponsoredGroups)) {
            return false;
        }

        if (Phpfox::isAppActive('Core_BetterAds')) {
            foreach($aSponsoredGroups as $Sponsor)
            {
                Phpfox::getService('ad.process')->addSponsorViewsCount($Sponsor['sponsor_id'], 'groups');
            }
        }

        $this->template()->assign(array(
                'aSponsoredGroups' => $aSponsoredGroups,
                'sHeader' => _p('sponsored_groups'),
                'sDefaultCoverPath' => Phpfox::getParam('groups.default_cover_photo')
            )
        );

        if (Phpfox::getUserParam('groups.can_sponsor_groups') || Phpfox::getUserParam('groups.can_purchase_sponsor_groups')) {
            $this->template()
                ->assign([
                    'aFooter' => array(
                        _p('encourage_sponsor_groups') => $this->url()->makeUrl('groups', array('view' => 'my'))
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
                'info' => _p('Sponsored Groups Limit'),
                'description' => _p('Define the limit of how many sponsored groups can be displayed when viewing the group section. Set 0 will hide this block'),
                'value' => 4,
                'type' => 'integer',
                'var_name' => 'limit',
            ],
            [
                'info' => _p('Sponsored Groups Cache Time'),
                'description' => _p('Define how long we should keep the cache for the <b>Sponsored Groups</b> by minutes. 0 means we do not cache data for this block.'),
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
                'title' => _p('"Sponsored Groups Limit" must be greater than or equal to 0')
            ],
        ];
    }
    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('groups.component_block_sponsored_clean')) ? eval($sPlugin) : false);
    }
}