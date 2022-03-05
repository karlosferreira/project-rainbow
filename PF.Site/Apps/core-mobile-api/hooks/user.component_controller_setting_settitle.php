<?php

if (!empty($aGateways)) {
    foreach ($aGateways as $key => $gateway) {
        if ($gateway['gateway_id'] == 'paypal') {
            $mobileUserGateway = Phpfox::getService('api.gateway')->getUserGateways($aUser['user_id']);
            $userValue = '';
            if (isset($mobileUserGateway['paypal']) && !empty($mobileUserGateway['paypal']['gateway'])) {
                $userValue = isset($mobileUserGateway['paypal']['gateway']['merchant_id']) ? $mobileUserGateway['paypal']['gateway']['merchant_id'] : '';
            }
            $aGateways[$key]['custom']['merchant_id'] = [
                'phrase'      => _p('merchant_id'),
                'phrase_info' => _p('the_encrypted_id_of_your_paypal_account'),
                'value'       => '',
                'user_value'  => $userValue
            ];
        }
    }
}