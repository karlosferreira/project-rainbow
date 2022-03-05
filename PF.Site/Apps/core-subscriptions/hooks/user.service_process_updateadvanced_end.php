<?php
defined('PHPFOX') or exit('NO DICE!');

$currentUserId = Phpfox::getUserId();
if(Phpfox::isAppActive('Core_Subscriptions') && ((int)$currentUserId == (int)ADMIN_USER_ID) && isset($aVals['user_group_id']) && isset($iUserid)) {
    $isUserSignUp = db()->select('subscribe_id')
        ->from(Phpfox::getT('user_field'))
        ->where('user_id = '. (int)$iUserid)
        ->execute('getSlaveField');
    if((int)$isUserSignUp > 0)
    {
        $aInfo = db()->select('package.user_group_id, purchase.user_id')
            ->from(Phpfox::getT('subscribe_purchase'),'purchase')
            ->join(Phpfox::getT('subscribe_package'),'package','package.package_id = purchase.package_id')
            ->where('purchase.purchase_id = '. (int)$isUserSignUp)
            ->execute('getSlaveRow');
        if(!empty($aInfo) && ((int)$aInfo['user_id'] == (int)$iUserid)  && ((int)$aVals['user_group_id'] == (int)$aInfo['user_group_id']))
        {
            db()->update(Phpfox::getT('user_field'),['subscribe_id' => 0], 'user_id = '. (int)$iUserid);
            db()->delete(Phpfox::getT('subscribe_purchase'),'purchase_id = '. (int)$isUserSignUp);
        }
    }
}