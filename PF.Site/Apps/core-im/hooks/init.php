<?php
// THIS HOOK USE FOR CHECKING HOSTING SERVICE
$server_chat = setting('pf_im_chat_server', 'nodejs');
$no_host = storage()->get('im_no_host');
if ($server_chat == 'nodejs' && !$no_host) {
    $package_key = 'im_host_package';
    $package = storage()->get($package_key);
    if (request()->get('im-reset-cache')) {
        storage()->del($package_key);
    }
    $package_id = 0;
    if (!$package || !$package->value->package_id || defined('PF_IM_DEBUG_URL')) {
        if (!defined('PHPFOX_TRIAL_MODE') && defined('PHPFOX_LICENSE_ID') && PHPFOX_LICENSE_ID) {
            $home = new Core\Home(PHPFOX_LICENSE_ID, PHPFOX_LICENSE_KEY);
            $hosted = $home->im();
            if (isset($hosted->license_id)) {
                $package_id = $hosted->package_id;
            }
        }
        if ($package_id) {
            storage()->del($package_key);
            storage()->set($package_key, [
                'package_id' => $package_id
            ]);
        }
    } else {
        $package = (array)$package->value;
        $package_id = $package['package_id'];
    }

    if ($package_id && request()->segment(2) != 'hosting') {
        if (!defined('PF_IM_PACKAGE_ID')) {
            define('PF_IM_PACKAGE_ID', $package_id);
        }

        $status_key = 'im_host_status';
        $status = storage()->get($status_key);
        if (PF_IM_PACKAGE_ID && (!$status || $status->value == 'on')) {
            $url = (defined('PF_IM_DEBUG_URL') ? PF_IM_DEBUG_URL : 'https://im-node.phpfox.com/');
            setting()->set('pf_im_node_server', $url);

            // support push notification on Mobile API
            if (Phpfox::isAppActive('Core_MobileApi')) {
                $firebase_updated_key = 'im_host_firebase_updated';
                $firebaseUpdated = storage()->get($firebase_updated_key);
                $firebaseSettings = [
                    'serverKey' => Phpfox::getParam('mobile.mobile_firebase_server_key'),
                    'senderId' => Phpfox::getParam('mobile.mobile_firebase_sender_id'),
                    'host' => Phpfox::getParam('core.host')
                ];
                if (!$firebaseUpdated
                    || $firebaseUpdated->value->serverKey != $firebaseSettings['serverKey']
                    || $firebaseUpdated->value->senderId != $firebaseSettings['senderId']
                    || $firebaseUpdated->value->host != $firebaseSettings['host']) {

                    $ch = curl_init('https://im-node.phpfox.com/socket.io/?' . http_build_query($firebaseSettings));

                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);

                    curl_exec($ch);
                    curl_close($ch);

                    storage()->del($firebase_updated_key);
                    storage()->set($firebase_updated_key, $firebaseSettings);
                }
            }

            // check and update status
            $token = null;
            $token_key = 'im_host_token';
            $tokenData = storage()->get($token_key);
            if ($tokenData) {
                $aTokenData = (array)$tokenData->value;
                if (isset($aTokenData['expired']) && $aTokenData['expired'] > time()) {
                    $token = $aTokenData['token'];
                }
            }
            if (!$token) { // no token or token expired
                $token_checked_time_key = 'im_host_token_checked_time';
                $token_checked_time = storage()->get($token_checked_time_key);
                if (!$token_checked_time || $token_checked_time->value < time() - 3600) {
                    $token = (array)(new Core\Home(PHPFOX_LICENSE_ID, PHPFOX_LICENSE_KEY))->im_token();
                    $isFailed = empty($token['token']);
                    $expired_key = 'im_host_expired';
                    storage()->del($expired_key);
                    storage()->set($expired_key, $isFailed);

                    storage()->del($token_checked_time_key);
                    storage()->set($token_checked_time_key, time());
                    if (!$isFailed) {
                        storage()->del($token_key);
                        storage()->set($token_key, [
                            'token' => $token,
                            'expired' => time() + 86400,
                        ]);
                    }
                }
            }
            $token = (object)$token;
            define('PHPFOX_IM_TOKEN', isset($token->token) ? $token->token : 'failed');
            if (!$status && isset($token->token)) {
                storage()->del($status_key);
                storage()->set($status_key, 'on');
            }
        }
    }
}

