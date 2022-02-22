<?php
if (Phpfox::isAppActive('Core_MobileApi') && $bLogged) {
    $refreshToken = Phpfox::getLib('request')->get('user_refresh_token');
    $deviceToken = Phpfox::getLib('request')->get('user_device_token');
    $accessToken = Phpfox::getLib('request')->get('user_access_token');
    $isMobileApi = defined('PHPFOX_IS_MOBILE_API_CALL') && PHPFOX_IS_MOBILE_API_CALL;

    db()->delete(':mobile_api_device_token', 'user_id = ' . db()->escape($iUserId) . ($isMobileApi && $deviceToken ? ' AND token <> "' . db()->escape($deviceToken) . '"' : ''));
    db()->delete(':oauth_refresh_tokens', 'user_id = ' . db()->escape($iUserId) . ($isMobileApi && $refreshToken ? ' AND refresh_token <> "' . db()->escape($refreshToken) . '"' : ''));
    db()->delete(':oauth_access_tokens', 'user_id = ' . db()->escape($iUserId) . ($isMobileApi && $accessToken ? ' AND access_token <> "' . db()->escape($accessToken) . '"' : ''));
}