<?php

namespace Apps\P_SavedItems\Block;

use Phpfox_Component;

class OpenConfirmationPopup extends Phpfox_Component
{
    public function process()
    {
        $this->template()->assign([
            'feed_id' => $this->getParam('feed_id'),
            'type_id' => $this->getParam('type_id'),
            'item_id' => $this->getParam('item_id'),
            'link' => $this->getParam('link'),
        ]);
    }
}