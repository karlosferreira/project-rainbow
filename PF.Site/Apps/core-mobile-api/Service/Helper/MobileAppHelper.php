<?php

namespace Apps\Core_MobileApi\Service\Helper;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Service\ApiVersionResolver;
use Apps\Core_MobileApi\Service\NotificationApi;
use Core\Request\Exception;
use Phpfox;

/**
 * Class MobileAppHelper
 *
 * @package Service\Helper
 *
 * This class help generate mobile app configuration
 *
 */
class MobileAppHelper
{

    /**
     * Generate resource and screen setting for each app
     *
     * @param $resources
     * @param $param
     *
     * @return array
     */
    public function getAppSettings($resources, $param)
    {
        $apps = [];

        foreach ($resources as $resourceName => $apiName) {
            try {
                $api = (new ApiVersionResolver())->getApiServiceWithVersion($resourceName, [
                    'api_version_name' => isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile'
                ]);

                if (!$api || !$api instanceof MobileAppSettingInterface) {
                    continue;
                }

                /** @var MobileApp $app */
                $app = $api->getAppSetting($param);

                if (!$app instanceof MobileApp) {
                    continue;
                }

                $apps[$app->getAppAlias()]['setting'] = $app->toSettings();
            } catch (Exception $exception) {

            }
        }
        $apps['notification']['setting'] = (new NotificationApi())->getAppSetting($param)->toSettings();

        return $apps;
    }

    /**
     * Generate resource and screen setting for each app
     *
     * @param $resources
     * @param $param
     *
     * @return array
     */
    public function getActions($resources, $param)
    {
        $exposeActions = [];

        foreach ($resources as $resourceName => $apiName) {
            $api = Phpfox::getService($apiName);
            if (!($api instanceof MobileAppSettingInterface)) {
                continue;
            }

            if (method_exists($api, 'getActions')) {
                foreach ($api->getActions($param) as $name => $action) {
                    $exposeActions[$name] = $action;
                }
            }

        }

        return $exposeActions;
    }

    /**
     * Generate screen setting for each app
     *
     * @param $resources
     * @param $param
     *
     * @return array
     */
    public function getScreenSettings($resources, $param)
    {
        $apps = [];

        foreach ($resources as $resourceName => $apiName) {
            try {
                $api = Phpfox::getService($apiName);

                if (!$api || !$api instanceof MobileAppSettingInterface) {
                    continue;
                }

                /** @var ScreenSetting $app */
                $app = $api->getScreenSetting($param);

                if (!$app instanceof ScreenSetting) {
                    continue;
                }

                $apps[$app->getAppAlias()] = $app->toSettings();
            } catch (Exception $exception) {

            }
        }
        return $apps;
    }
}