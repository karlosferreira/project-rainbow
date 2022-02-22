<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 24/7/18
 * Time: 9:54 AM
 */

namespace Apps\Core_MobileApi\Adapter\MobileApp;

/**
 * Interface MobileAppSettingInterface
 * @package Adapter\MobileApp
 *
 * Get specify setting of an App use on mobile application
 *
 */
interface MobileAppSettingInterface
{
    /**
     * @param $param
     *
     * @return MobileApp
     */
    public function getAppSetting($param);

    public function getScreenSetting($param);

}