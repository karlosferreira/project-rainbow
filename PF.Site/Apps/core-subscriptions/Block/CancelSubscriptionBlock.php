<?php
namespace Apps\Core_Subscriptions\Block;

use Phpfox;
use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

class CancelSubscriptionBlock extends Phpfox_Component
{
    public function process()
    {
        $iPurchaseId = $this->getParam('iPurchaseId');
        $aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($iPurchaseId);
        $aReasons = Phpfox::getService('subscribe.reason')->getReasonForCancelSubscription();
        $this->template()->assign([
            'sContent' => _p("subscribe_cancel_title_block"),
            'sWarning' => !empty((int)$aPurchase['recurring_period']) && $aPurchase['payment_method'] == 'paypal' && $aPurchase['renew_type'] == 1 ? _p("subscribe_remember_cancel_paypal" ) : '',
            'aPurchase' => $aPurchase,
            'sDefaultPhoto' => Phpfox::getParam('subscribe.default_photo_package'),
            'aReasons' => $aReasons
        ]);
    }
}