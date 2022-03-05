<?php

namespace Apps\Core_MobileApi;

use Core\App;

/**
 * Class Install
 *
 * @copyright [PHPFOX_COPYRIGHT]
 * @author    phpFox LLC
 * @version   4.5.0
 * @package   Apps\Core_MobileApi
 */
class Install extends App\App
{
    private $_app_phrases = [];

    protected function setId()
    {
        $this->id = 'Core_MobileApi';
    }

    protected function setAlias()
    {
        $this->alias = 'mobile';
    }

    protected function setName()
    {
        $this->name = _p('mobile_api');
    }

    protected function setVersion()
    {
        $this->version = '4.6.9';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.4';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'mobile_limit_menu_show_first'          => [
                'var_name'    => 'mobile_limit_menu_show_first',
                'info'        => 'How many menus show first?',
                'description' => 'The number of menus to show by default. Others will be hidden until click "See More". Set 0 if you want to show them all',
                'type'        => 'integer',
                'value'       => '0',
                'ordering'    => $iIndex++,
            ],
            'mobile_firebase_sender_id'             => [
                'var_name'    => 'mobile_firebase_sender_id',
                'info'        => 'Firebase Sender ID',
                'description' => 'The server sender id of your Firebase project.<br/>You can found your sender id in your <b>Firebase App console > Project Settings > Cloud Messaging</b>',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_firebase_server_key'            => [
                'var_name'    => 'mobile_firebase_server_key',
                'info'        => 'Firebase Server Key',
                'description' => 'The server key of your Firebase project.<br/>You can found your server key in your <b>Firebase App console > Project Settings > Cloud Messaging</b>',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_android_admob_banner_uid'       => [
                'var_name'    => 'mobile_android_admod_banner_uid',
                'info'        => 'Android app - AdMob Banner Unit ID',
                'description' => 'Fill your Ad Unit ID of this type for Android app.<br/><i>Not have an Ad Unit ID? <a target="_blank" href="https://admob.google.com/home/">Start here</a></i>',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_android_admob_interstitial_uid' => [
                'var_name'    => 'mobile_android_admob_interstitial_uid',
                'info'        => 'Android app - AdMob Interstitial Unit ID',
                'description' => 'Fill your Ad Unit ID of this type for Android app.',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_android_admob_rewarded_uid'     => [
                'var_name'    => 'mobile_android_admob_rewarded_uid',
                'info'        => 'Android app - AdMob Rewarded Unit ID',
                'description' => 'Fill your Ad Unit ID of this type for Android app.',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_ios_admob_banner_uid'           => [
                'var_name'    => 'mobile_ios_admob_banner_uid',
                'info'        => 'iOS app - AdMob Banner Unit ID',
                'description' => 'Fill your Ad Unit ID of this type for iOS app.',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_ios_admob_interstitial_uid'     => [
                'var_name'    => 'mobile_ios_admob_interstitial_uid',
                'info'        => 'iOS app - AdMob Interstitial Unit ID',
                'description' => 'Fill your Ad Unit ID of this type for iOS app.',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_ios_admob_rewarded_uid'         => [
                'var_name'    => 'mobile_ios_admob_rewarded_uid',
                'info'        => 'iOS app - AdMob Rewarded Unit ID',
                'description' => 'Fill your Ad Unit ID of this type for iOS app.',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_paypal_client_id'               => [
                'var_name'    => 'mobile_paypal_client_id',
                'info'        => 'PayPal Client ID',
                'description' => 'Fill Client ID of your PayPal REST API apps. <a href="https://developer.paypal.com/docs/integration/admin/manage-apps" target="_blank">How to create a PayPal REST API app?</a>',
                'type'        => 'string',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_paypal_secret_id'               => [
                'var_name'    => 'mobile_paypal_client_id',
                'info'        => 'PayPal Secret',
                'description' => 'Fill Secret of your PayPal REST API apps.',
                'type'        => 'password',
                'value'       => '',
                'ordering'    => $iIndex++
            ],
            'mobile_enable_apple_login' => [
                'var_name' => 'mobile_enable_apple_login',
                'info' => 'Enable Login with Apple on iOS',
                'description' => 'Set "Yes" to allow users login by Apple ID on iOS app.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => $iIndex++,
            ],
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {
    }

    protected function setComponent()
    {
    }

    protected function setComponentBlock()
    {

    }

    protected function setPhrase()
    {
        $this->phrase = $this->_app_phrases;
    }

    protected function setOthers()
    {
        $this->admincp_route = '/mobile/admincp';
        $this->admincp_menu = [
            'Manage Menus'       => '#',
            'Manage Information' => 'mobile.manage-information',
            'Manage Ads Config'  => 'mobile.manage-ads-config',
            'Add New Ad Config'  => 'mobile.add-ad-config'
        ];
        $this->map = [];
        $this->_apps_dir = 'core-mobile-api';
        $this->database = [
            'MenuItem',
            'DeviceToken',
            'AdsConfigs',
            'AdsConfigsScreen',
            'PushNotificationSetting'
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/mobile/'
        ];
        $this->_admin_cp_menu_ajax = false;
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
    }
}