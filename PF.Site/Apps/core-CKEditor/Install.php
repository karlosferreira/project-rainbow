<?php

namespace Apps\phpFox_CKEditor;

use Core\App;

/**
 * Class Install
 * @package Apps\phpFox_CKEditor
 */
class Install extends App\App
{

    public $store_id = 1655;

    protected function setId()
    {
        $this->id = 'phpFox_CKEditor';
    }

    protected function setAlias()
    {
        $this->alias = 'pckeditor';
    }

    protected function setName()
    {
        $this->name = _p('phpfox_ckeditor');
    }

    protected function setVersion()
    {
        $this->version = '4.2.5';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.6.1';
    }

    protected function setSettings()
    {
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'ckeditor_package' => [
                'var_name' => 'ckeditor_package',
                'info' => 'CKEditor package',
                'description' => 'Select a CKEditor package. <br /> For more detail, please check <a href="https://ckeditor.com/ckeditor-4/download/#ckeditor4" target="_blank">here</a>',
                'type' => 'select',
                'value' => [
                    '1' => 'standard',
                    '2' => 'standard',
                    '3' => 'standard',
                    '4' => 'standard',
                    '5' => 'standard'
                ],
                'options' => [
                    'basic' => 'Basic Package',
                    'standard' => 'Standard Package',
                    'full' => 'Full Package'
                ],
                'ordering' => 1,
            ],
        ];
    }

    protected function setComponent()
    {
    }

    protected function setComponentBlock()
    {
    }

    protected function setPhrase()
    {
        $this->phrase = [

        ];
    }

    protected function setOthers()
    {
        $this->admincp_menu = [
            'CKEditor' => '#'
        ];
        $this->admincp_route = "/ckeditor/admincp";
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'https://store.phpfox.com/';
        $this->_apps_dir = 'core-CKEditor';
        $this->_admin_cp_menu_ajax = false;
    }
}

