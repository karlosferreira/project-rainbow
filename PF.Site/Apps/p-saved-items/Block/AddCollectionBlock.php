<?php

namespace Apps\P_SavedItems\Block;

use Phpfox;
use Phpfox_Component;

/**
 * Class AddCollectionBlock
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Block
 */
class AddCollectionBlock extends Phpfox_Component
{
    public function process()
    {
        if (($collectionId = $this->getParam('collectionId')) && ($collection = Phpfox::getService('saveditems.collection')->getForEdit($collectionId))) {
            $collection['name'] = Phpfox::getLib('parse.output')->clean($collection['name']);
            $this->template()->assign([
                'aForms' => $collection,
                'isCollectionDetail' => $this->getParam('detail')
            ]);
        }
        return 'block';
    }
}