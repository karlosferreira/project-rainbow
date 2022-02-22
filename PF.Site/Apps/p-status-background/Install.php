<?php
namespace Apps\P_StatusBg;

use Core\App;

class Install extends App\App
{
    private $_app_phrases = [

    ];

    protected function setId()
    {
        $this->id = 'P_StatusBg';
    }

    protected function setAlias()
    {
        $this->alias = 'pstatusbg';
    }

    protected function setName()
    {
        $this->name = _p('feed_status_background');
    }

    protected function setVersion()
    {
        $this->version = '4.1.2';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.7.5';
    }

    protected function setSettings()
    {

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
        $this->admincp_route = '/admincp/pstatusbg/manage-collections';

        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->admincp_menu = [
            _p('manage_collections') => '#',
        ];
        $this->admincp_action_menu = [
            '/admincp/pstatusbg/add-collection' => _p('add_new')
        ];
        $this->_apps_dir = 'p-status-background';
        $this->_admin_cp_menu_ajax = false;
        $this->database = [
            'Pstatusbg_Backgrounds',
            'Pstatusbg_Collections',
            'Pstatusbg_Status_Background'
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/pstatusbg/'
        ];
    }
}