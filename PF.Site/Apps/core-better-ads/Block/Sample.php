<?php

namespace Apps\Core_BetterAds\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Sample
 * @package Apps\Core_BetterAds\Block
 */
class Sample extends Phpfox_Component
{
    public function process()
    {
        $aPlans = Phpfox::getService('ad.get')->getPlans();
        $iBlockId = $this->getParam('block_id');

        foreach ($aPlans as $iKey => $aPlan) {
            if ($aPlan['is_active'] != 1 || $aPlan['block_id'] != $this->getParam('block_id')) {
                unset($aPlans[$iKey]);
                continue;
            }
            if (!empty($aPlan['cost']) && Phpfox::getLib('parse.format')->isSerialized($aPlan['cost'])) {
                $aCosts = unserialize($aPlan['cost']);
                $iLastCurrency = null;
                foreach ($aCosts as $sKey => $iCost) {
                    if (strtolower(Phpfox::getService('core.currency')->getDefault()) == strtolower($sKey)) {
                        $aPlans[$iKey]['default_cost'] = $aPlan['default_cost'] = $iCost;
                        $aPlans[$iKey]['default_currency_id'] = $aPlan['default_curency'] = $sKey;
                    }
                }
            }
            $aPlan = array(
                'block_id' => $iBlockId,
                'default_cost' => $aPlan['default_cost'],
                'd_width' => $aPlan['d_width'],
                'd_height' => $aPlan['d_height'],
                'is_cpm' => $aPlan['is_cpm'],
                'plan_id' => $aPlan['plan_id']
            );

            $aPlans[$iKey]['sSizes'] = '<a href="#" onclick="window.parent.$Core.Ads.setPlan(' . $this->getParam('block_id') . ', ' . $aPlan['plan_id'] . ','
                . $aPlan['default_cost'] . ','
                . $aPlan['d_width'] . ','
                . $aPlan['d_height'] . ','
                . $aPlan['is_cpm'] . ');">' . $aPlan['d_width'] . 'x' . $aPlan['d_height'] . '</a>';
        }

        if (empty($aPlans)) {
            return false;
        }

        $this->template()->assign(array(
            'aPlans' => $aPlans,
            'sBlockLocation' => $this->getParam('block_id')
        ));

        return 'block';
    }


    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_sample_clean')) ? eval($sPlugin) : false);
    }
}
