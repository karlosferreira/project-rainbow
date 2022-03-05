<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 10:05 AM
 */

namespace Apps\Core_MobileApi\Adapter\Setting;


use Phpfox;

class PhpfoxSetting implements SettingInterface
{
    function getAppSetting($varName, $default = null)
    {
        return Phpfox::getParam($varName, $default);
    }

    function getUserSetting($varName)
    {
        return Phpfox::getUserParam($varName);
    }

    /**
     * @alias \Phpfox::isApp()
     * @param $appId
     *
     * @return mixed
     */
    function isApp($appId)
    {
        return Phpfox::isApps($appId);
    }
}