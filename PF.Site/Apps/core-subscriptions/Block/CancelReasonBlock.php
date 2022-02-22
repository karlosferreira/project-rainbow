<?php
namespace Apps\Core_Subscriptions\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

class CancelReasonBlock extends Phpfox_Component
{
    public function process()
    {
        $iPurchaseId = $this->getParam('iPurchaseId');
        if(!($aPurchase = Phpfox::getService('subscribe.purchase')->getPurchase($iPurchaseId)))
        {
            return Phpfox_Error::display(_p('unable_to_find_this_invoice'));
        }
        if($aPurchase['status'] != "cancel")
        {
            return Phpfox_Error::display(_p('subscribe_purchase_is_not_cancelled'));
        }
        $aReason = Phpfox::getService('subscribe.reason')->getReason($iPurchaseId);
        $this->template()->assign([
            'aPurchase' => $aPurchase,
            'aReason' => $aReason
        ]);
    }

}