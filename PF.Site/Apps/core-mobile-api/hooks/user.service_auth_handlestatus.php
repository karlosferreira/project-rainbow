<?php

$mobileReq1 = \Phpfox_Request::instance()->get('req1');
$mobileReq2 = \Phpfox_Request::instance()->get('req2');
$mobileReq3 = \Phpfox_Request::instance()->get('req3');

if (($mobileReq1 == 'mobile' && $mobileReq2 == 'token') || ($mobileReq1 == 'restful_api' && in_array($mobileReq3, ['user', 'me']))) {
    if (
        (Phpfox::isUser() && Phpfox::getUserBy('status_id') == 1 && Phpfox::getParam('user.logout_after_change_email_if_verify') && !isset($bEmailVerification)) ||
        (Phpfox::getParam('core.enable_register_with_phone_number') && !isset($bPhoneVerification)) ||
        (Phpfox::isUser() && in_array(Phpfox::getUserBy('view_id'), [2, 1]))
    ) {
        $bEmailVerification = true;
        $bPhoneVerification = true;
    }
}