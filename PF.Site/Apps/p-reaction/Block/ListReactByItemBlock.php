<?php

namespace Apps\P_Reaction\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class ListReactByItemBlock extends Phpfox_Component
{
    public function process()
    {
        $sType = $this->getParam('type');
        $iItemId = $this->getParam('item_id');
        $iReactId = $this->getParam('react_id', 0);

        if (empty($sType) || !$iItemId) {
            return false;
        }
        $sPrefix = $this->getParam('table_prefix');
        list($iTotalReacted, $aListReacted) = Phpfox::getService('preaction')->getMostReaction($sType, $iItemId, $sPrefix);
        $iTotalTabs = count($aListReacted);
        if($iTotalTabs == 1 && $iReactId == 0) {
            $iReactId = $aListReacted[0]['id'];
        }
        $this->template()->assign(array(
                'iCnt' => 0,
                'bIsPaging' => $this->getParam('ajax_paging', 0),
                'aListReacted' => $aListReacted,
                'iTotalReacted' => $iTotalReacted,
                'iTotalTabs' => $iTotalTabs,
                'iReactId' => $iReactId,
                'iItemId' => $iItemId,
                'sType' => $sType,
                'sPrefix' => $sPrefix
            )
        );
        return 'block';
    }
}