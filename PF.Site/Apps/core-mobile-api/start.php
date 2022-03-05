<?php

namespace Apps\Core_MobileApi;

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Localization\PhpfoxLocalization;
use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Adapter\Parse\PhpfoxParse;
use Apps\Core_MobileApi\Adapter\Privacy\UserPrivacy;
use Apps\Core_MobileApi\Adapter\Privacy\UserPrivacyInterface;
use Apps\Core_MobileApi\Adapter\PushNotification\Firebase;
use Apps\Core_MobileApi\Adapter\PushNotification\PushNotificationInterface;
use Apps\Core_MobileApi\Adapter\Setting\PhpfoxSetting;
use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Service\CoreApi;
use Phpfox;
use Phpfox_Module;

Phpfox_Module::instance()
    ->addAliasNames('mobile', 'Core_MobileApi')
    ->addServiceNames([
        'mobile.api_version_resolver'    => Service\ApiVersionResolver::class,
        'mobile.helper.search'           => Service\Helper\SearchHelper::class,
        'mobile.helper.request'          => Service\Helper\RequestHelper::class,
        'mobile.helper.browse'           => Service\Helper\BrowseHelper::class,
        'mobile.helper.search.browse'    => Service\Helper\SearchBrowseHelper::class,
        'mobile.helper.feedPresentation' => Service\Helper\FeedAttachmentHelper::class,
        'mobile.helper.psrRequestHelper' => Service\Helper\PsrRequestHelper::class,
        'mobile.auth_api'                => Service\Auth\AuthenticationApi::class,
        'mobile.auth.storage'            => Service\Auth\Storage::class,
        'mobile.admincp.menu'            => Service\Admincp\MenuService::class,
        'mobile.admincp.setting'         => Service\Admincp\SettingService::class,
        'mobile.device'                  => Service\Device\DeviceService::class,
        "mobile.mobile_app_helper"       => Service\Helper\MobileAppHelper::class,
        'mobile.photo_browse_helper'     => Service\Helper\PhotoBrowseHelper::class,
        'mobile.event_browse_helper'     => Service\Helper\EventBrowseHelper::class,
        'mobile.ad-config'               => Service\Admincp\AdConfigService::class,
        LocalizationInterface::class     => PhpfoxLocalization::class,
        SettingInterface::class          => PhpfoxSetting::class,
        PushNotificationInterface::class => Firebase::class,
        ParseInterface::class            => PhpfoxParse::class,
        UserPrivacyInterface::class      => UserPrivacy::class
    ])
    ->addComponentNames('controller', [
        'mobile.docs'                       => Controller\DocsController::class,
        'mobile.admincp.menu-item'          => Controller\Admin\MenuItemController::class,
        'mobile.admincp.manage-information' => Controller\Admin\ManageInformationController::class,
        'mobile.admincp.add'                => Controller\Admin\AddController::class,
        'mobile.admincp.add-ad-config'      => Controller\Admin\AddAdmobConfigController::class,
        'mobile.admincp.manage-ads-config'  => Controller\Admin\ManageAdmobConfigController::class
    ])
    ->addTemplateDirs([
        'mobile' => PHPFOX_DIR_SITE_APPS . 'core-mobile-api' . PHPFOX_DS . 'views'
    ])
    ->addComponentNames('ajax', [
        'mobile.ajax' => Ajax\Ajax::class
    ])
    ->addComponentNames('block', [
        'mobile.admincp.menu-by-type' => Block\Admin\MenuByTypeBlock::class
    ]);

route('mobile/ping', function () {
    Phpfox::getService('mobile.core_api')->ping();
});

route('restful_api/mobile/docs', 'mobile.docs');

group('/mobile', function () {
    header('Accept-Api-Version: ' . Service\ApiVersionResolver::SUPPORT_API_VERSIONS);
    route('/build', function () {
        header('content-type:application/json');

        $resourceNaming = new Service\NameResource();

        $apiRoutes = $resourceNaming->generateRestfulRoute('mobile');

        exit(json_encode([
            'routes' => $apiRoutes,
        ], JSON_PRETTY_PRINT));
    });
    route('/token', function () {
        Phpfox::getService('mobile.auth_api')->handleTokenRequest();
    });

    route('/admincp', function () {
        auth()->isAdmin(true);
        Phpfox::getLib('module')->dispatch('mobile.admincp.menu-item');
        return 'controller';
    });


    route('/admincp/menu/order', function () {
        auth()->isAdmin(true);
        $ids = request()->get('ids');
        $ids = trim($ids, ',');
        $ids = explode(',', $ids);
        $values = [];
        foreach ($ids as $key => $id) {
            $values[$id] = $key + 1;
        }
        Phpfox::getService('core.process')->updateOrdering([
                'table'  => 'mobile_api_menu_item',
                'key'    => 'item_id',
                'values' => $values,
            ]
        );

        Phpfox::getService('mobile.admincp.menu')->clearCache();
        return true;
    });

    route('/gateway/callback-success/paypal', function () {
        $fileContent = @file_get_contents("php://input");
        Phpfox::startLog('Mobile Gateway Callback Started.');
        if (!empty($fileContent)) {
            Phpfox::log('Request: ' . var_export($fileContent, true));
            //Webhook callback
            (new CoreApi())->callbackBillingPlan(json_decode($fileContent, true));
        } else {
            Phpfox::log('Request: ' . var_export($_POST, true));
            //Notify_url callback
            (new CoreApi())->callbackPaymentApi($_POST);
        }
        return true;
    });
    route('/gateway/callback-fail/paypal', function () {
        //Do nothing
    });
});


