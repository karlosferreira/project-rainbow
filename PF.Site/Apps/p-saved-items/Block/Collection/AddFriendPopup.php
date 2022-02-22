<?php

namespace Apps\P_SavedItems\Block\Collection;

use Phpfox;

class AddFriendPopup extends \Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);

        $aCurrentValues = Phpfox::getService('saveditems.friend')->getFriendInCollection($this->request()->get('collection_id'));
        foreach ($aCurrentValues as $key => $aCurrentValue) {
            if ($aCurrentValue['user_id'] == $aCurrentValue['owner_id']) {
                unset($aCurrentValues[$key]);
            }
        }
        $this->template()->assign(array(
                'iId' => $this->request()->get('collection_id'),
                'sTitle' => $this->request()->get('title'),
                'sInputType' => 'multiple',
                'sInputName' => 'val[user_id]',
                'aCurrentValues' => $aCurrentValues,
            )
        );
    }
}