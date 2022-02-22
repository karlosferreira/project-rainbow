<?php

namespace Apps\Core_BetterAds\Block;

use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Sponsored
 * @package Apps\Core_BetterAds\Block
 */
class Sponsored extends Phpfox_Component
{
    public function process()
    {
        $this->template()->assign(array(
                'sHeader' => _p('better_ads_sponsored'),
            )
        );

        if (user('better_can_create_ad_campaigns')) {
            $this->template()->assign(array(
                    'aFooter' => array(
                        _p('better_ads_create_an_ad') => $this->url()->makeUrl('ad.add'),
                    )
                )
            );
        }

        return 'block';
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_sponsored_clean')) ? eval($sPlugin) : false);
    }
}
