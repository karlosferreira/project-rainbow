<?php
namespace Apps\Core_Subscriptions\Block;

use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

class RenewPaymentBlock extends Phpfox_Component
{
    public function process()
    {
        \Phpfox::isUser(true);
        if ((int)\Phpfox::getUserBy('user_group_id') == 1){
            //Admin can't use this feature
            return \Phpfox_Error::set(_p('admin_cant_use_this_feature'));
        }
        $iPackageId = $this->getParam('iPackageId');

        $aPaymentMethods = \Phpfox::getService('subscribe')->getVisiblePaymentMethods($iPackageId);

        $this->template()->assign([
            'iPackageId' => $iPackageId,
            'aPaymentMethods' => $aPaymentMethods
        ]);
        return 'block';
    }
}