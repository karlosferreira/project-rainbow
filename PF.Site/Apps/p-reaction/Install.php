<?php
namespace Apps\P_Reaction;

use Core\App;

/**
 * Class Install
 * @version 4.01
 * @package Apps\P_Reaction
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    protected function setId()
    {
        $this->id = 'P_Reaction';
    }

    protected function setAlias()
    {
        $this->alias = 'preaction';
    }

    protected function setName()
    {
        $this->name = _p('Reaction');
    }

    protected function setVersion()
    {
        $this->version = '4.1.1';
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
        $this->admincp_route = '/admincp/preaction/manage-reactions';

        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->admincp_menu = [
            _p('manage_reactions') => '#',
        ];
        $this->admincp_action_menu = [
            '/admincp/preaction/add-reaction' => _p('add_new_reaction')
        ];
        $this->_apps_dir = 'p-reaction';
        $this->_admin_cp_menu_ajax = false;
        $this->database = [
            'Preaction_Reactions',
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/preaction/'
        ];
    }
}