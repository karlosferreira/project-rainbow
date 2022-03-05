<?php
/**
 * Auto login user
 */

if (Phpfox::isAppActive('Core_MobileApi')) {
    Phpfox_Module::instance()->addServiceNames((new Apps\Core_MobileApi\Service\NameResource())->getApiNames());
}
if (isset($_SERVER['x-access-token']) || isset($_SERVER['HTTP_X_ACCESS_TOKEN']) || isset($_REQUEST['token'])) {
    $token = ((isset($_SERVER['x-access-token']) ? $_SERVER['x-access-token'] : (isset($_REQUEST['token']))) ? $_REQUEST['token'] : $_SERVER['HTTP_X_ACCESS_TOKEN']);
    Phpfox::getService('mobile.auth_api')->setUserFromToken($token);
}