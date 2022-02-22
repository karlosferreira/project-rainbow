<?php

namespace Apps\PHPfox_CDN_Service;

use Core\App;
use Core\App\Install\Setting;

/**
 * Class Install
 * @author  phpFox
 * @package Apps\PHPfox_CDN_Service
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    public $store_id = 1880;

    protected function setId()
    {
        $this->id = 'PHPfox_CDN_Service';
    }

    /**
     * Set start and end support version of your App.
     */
    protected function setSupportVersion()
    {
        $this->start_support_version = '4.6.0';
    }

    protected function setAlias()
    {
    }

    protected function setName()
    {
        $this->name = _p('cdn_app');
    }

    protected function setVersion()
    {
        $this->version = '4.6.0';
    }

    protected function setSettings()
    {
        $this->settings = [
            'pf_cdn_service_enabled' => [
                'var_name' => 'pf_cdn_service_enabled',
                'info' => 'Enable CDN Service',
                'type' => Setting\Site::TYPE_RADIO,
                'value' => '0',
            ],
            'pf_cdn_service_url' => [
                'var_name' => 'pf_cdn_service_url',
                'info' => 'CDN URL',
            ],
        ];
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
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->_apps_dir = "core-cdn-service";
        $this->admincp_help = 'https://docs.phpfox.com/display/FOX4MAN/Setting+Up+the+CDN+Service+App';
        $this->admincp_route = \Phpfox::getLib('url')->makeUrl('admincp.app.settings', ['id' => 'PHPfox_CDN_Service']);
    }
}