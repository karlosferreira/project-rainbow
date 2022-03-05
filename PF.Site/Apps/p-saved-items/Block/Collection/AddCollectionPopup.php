<?php

namespace Apps\P_SavedItems\Block\Collection;

use Phpfox;
use Phpfox_Component;

class AddCollectionPopup extends Phpfox_Component
{
    public function process()
    {
        $this->template()->assign([
            'collections' => Phpfox::getService('saveditems.collection')->getMyCollections(),
            'savedId' => $this->getParam('savedId'),
            'keepPopup' => true
        ]);
    }
}