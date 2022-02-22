<?php


namespace Apps\Core_MobileApi\Adapter\Setting;

interface SettingInterface
{
    /**
     * @alias \Phpfox::getParam()
     * @param      $varName
     * @param null $default
     *
     * @return mixed
     */
    function getAppSetting($varName, $default = null);

    /**
     * @alias \Phpfox::getUserParam()
     * @param $varName
     *
     * @return mixed
     */
    function getUserSetting($varName);

    /**
     * @alias \Phpfox::isApp()
     * @param $appId
     *
     * @return mixed
     */
    function isApp($appId);
}