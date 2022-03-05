<?php

namespace Apps\P_Reaction\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

class ReactionListMiniBlock extends Phpfox_Component
{
    public function process()
    {
        $sType = $this->getParam('type_id');
        $iItemId = $this->getParam('item_id');
        if ($sType != 'feed_mini' || !$iItemId) {
            return false;
        }
        $sPrefix = $this->getParam('table_prefix');
        list($iTotalReact, $aMostReacted) = Phpfox::getService('preaction')->getMostReaction($sType, $iItemId,
            $sPrefix);
        if (!$aMostReacted) {
            return false;
        }
        $this->template()->assign([
            'sType' => $sType,
            'sPrefix' => $sPrefix,
            'iItemId' => $iItemId,
            'aMostReacted' => $aMostReacted,
            'iTotalReactType' => count($aMostReacted),
            'iTotalReact' => $iTotalReact
        ]);
         $this->template()->clean(array(
             'sHeader'
         ));
        return 'block';
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('preaction.component_block_reaction_list_mini_clean')) ? eval($sPlugin) : false);

        $this->template()->clean('aLike');
    }
}