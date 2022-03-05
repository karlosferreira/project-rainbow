<?php
namespace Apps\P_StatusBg\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

/**
 * Class CollectionsListBlock
 * @package Apps\P_StatusBg\Block
 */
class CollectionsListBlock extends Phpfox_Component
{
    public function process()
    {
        $aCollections = Phpfox::getService('pstatusbg')->getCollectionsList();
        if (!$aCollections) {
            return false;
        }
        $this->template()->assign([
            'aCollections' => $aCollections,
            'iTotalCollection' => count($aCollections)
        ]);
        return 'block';
    }
}