<?php
defined('PHPFOX') or exit('NO DICE!');
$requestObject = Phpfox::getLib('request');
$req1 = $requestObject->get('req1');
$req2 = $requestObject->get('req2');

$apiCondition = ($req1 == 'restful_api') || ($req1 == 'mobile' && $req2 == 'token');
$bLogout = ($req1 == 'logout') || (($req1 == 'user') && ($req2 == 'logout')) || (($req1 == 'subscribe')) || $apiCondition || $requestObject->get('payment-process-executing');
if (!PHPFOX_IS_AJAX && !$bLogout && Phpfox::isAppActive('Core_Subscriptions')) {
    $mRedirectId = Phpfox::getService('subscribe.purchase')->getRedirectId();
    if (is_numeric($mRedirectId) && $mRedirectId > 0) {
        $aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($mRedirectId, true);
        if ((int)$aPurchase['recurring_period'] > 0) {
            Phpfox_Url::instance()->send('subscribe.renew-method', ['id' => $mRedirectId], _p('subscribe_please_choose_renew_method'));
        } else {
            Phpfox_Url::instance()->send('subscribe.register', ['id' => $mRedirectId], _p('please_complete_your_purchase'));
        }
    }

    $mRedirectId = Phpfox::getService('subscribe.purchase')->isCompleteSubscribe();
    if (is_numeric($mRedirectId) && $mRedirectId > 0) {
        $aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($mRedirectId, true);
        if ((int)$aPurchase['recurring_period'] > 0) {
            Phpfox_Url::instance()->send('subscribe.renew-method', ['id' => $mRedirectId], _p('subscribe_please_choose_renew_method'));
        } else {
            Phpfox_Url::instance()->send('subscribe.register', ['id' => $mRedirectId], _p('please_complete_your_purchase'));
        }
    }
}