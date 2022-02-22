<?php
namespace Apps\phpFox_Shoutbox;

use Core\App;
use Core\App\Install\Setting;
use Phpfox;

/**
 * Class Install
 * @author  phpFox
 * @version 4.2.0
 * @package Apps\phpFox_Shoutbox
 */
class Install extends App\App
{
    public $store_id = 1654;

    protected function setId()
    {
        $this->id = 'phpFox_Shoutbox';
    }
    
    protected function setAlias()
    {
        $this->alias = 'shoutbox';
    }
    
    protected function setName()
    {
        $this->name = _p('shoutbox_app');
    }
    
    protected function setVersion()
    {
        $this->version = '4.3.4';
    }
    
    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.0';
    }
    
    protected function setSettings()
    {
        $this->settings = [
            'shoutbox_enable_index'     => [
                'var_name'    => "shoutbox_enable_index",
                'info'        => "Enable shoutbox on home page",
                'type'        => Setting\Site::TYPE_RADIO,
                "value"       => 1,
            ],
            'shoutbox_enable_pages'     => [
                'var_name'    => "shoutbox_enable_pages",
                'info'        => "Enable shoutbox on Pages detail",
                'type'        => Setting\Site::TYPE_RADIO,
                "value"       => 1,
            ],
            'shoutbox_enable_groups'     => [
                'var_name'    => "shoutbox_enable_groups",
                'info'        => "Enable shoutbox on Groups detail",
                'type'        => Setting\Site::TYPE_RADIO,
                "value"       => 1,
            ],
            'shoutbox_polling_max_request_time'     => [
                'var_name'    => "shoutbox_polling_max_request_time",
                'info'        => "Time (in second) to check for new messages. Accepted value (2-15). If you use other value, it will become 5",
                'type'        => Setting\Site::TYPE_TEXT,
                "value"       => 7,
            ],
            'shoutbox_day_to_delete_messages' => [
                'var_name' => "shoutbox_day_to_delete_messages",
                'info' => "Delete old messages",
                'description' => "Messages older than this value days will be removed. 0 means does not delete",
                'type' => Setting\Site::TYPE_TEXT,
                "value" => 30,
            ],
        ];
    }
    
    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'shoutbox_can_view' => [
                'var_name' => 'shoutbox_can_view',
                'info'     => 'Can view shoutbox',
                'type'     => Setting\Groups::TYPE_RADIO,
                'value'    => [
                    "1" => "1",
                    "2" => "1",
                    "3" => "1",
                    "4" => "1",
                    "5" => "0"
                ],
                'options'  => Setting\Groups::$OPTION_YES_NO
            ],
            'shoutbox_can_share' => [
                'var_name' => 'shoutbox_can_share',
                'info'     => 'Can share new messages',
                'type'     => Setting\Groups::TYPE_RADIO,
                'value'    => [
                    "1" => "1",
                    "2" => "1",
                    "3" => "0",
                    "4" => "1",
                    "5" => "0"
                ],
                'options'  => Setting\Groups::$OPTION_YES_NO
            ],
            'shoutbox_waiting_time' => [
                'var_name' => 'shoutbox_waiting_time',
                'info' => 'Control how many seconds user can post new message (0 means does not wait)',
                'type' => Setting\Groups::TYPE_TEXT,
                'value' => [
                    "1" => "0",
                    "2" => "10",
                    "3" => "3",
                    "4" => "60",
                    "5" => "060"
                ],
            ],
            'shoutbox_can_edit_own_message' => [
                'var_name' => 'shoutbox_can_edit_own_message',
                'info' => 'Allow user editing their own messages',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    "1" => "1",
                    "2" => "1",
                    "3" => "0",
                    "4" => "1",
                    "5" => "0"
                ],
            ],
            'shoutbox_can_delete_own_message' => [
                'var_name' => 'shoutbox_can_delete_own_message',
                'info' => 'Allow user deleting their own messages',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    "1" => "1",
                    "2" => "1",
                    "3" => "0",
                    "4" => "1",
                    "5" => "0"
                ],
            ],
            'shoutbox_can_delete_others_message' => [
                'var_name' => 'shoutbox_can_delete_others_message',
                'info' => 'Allow user deleting all messages',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    "1" => "1",
                    "2" => "0",
                    "3" => "0",
                    "4" => "0",
                    "5" => "0"
                ],
            ],
        ];
    }
    
    protected function setComponent()
    {
        $this->component = [
            "block" => [
                "chat" => "",
            ]
        ];
    }
    
    protected function setComponentBlock()
    {
        $this->component_block = [
            "Shoutbox" => [
                "type_id"      => "0",
                "m_connection" => "core.index-member",
                "component"    => "chat",
                "location"     => "3",
                "is_active"    => "1",
                "ordering"     => "3"
            ],
            "Shoutbox Pages" => [
                "type_id"      => "0",
                "m_connection" => "pages.view",
                "component"    => "chat",
                "location"     => "3",
                "is_active"    => "1",
                "ordering"     => "3"
            ],
            "Shoutbox Groups" => [
                "type_id"      => "0",
                "m_connection" => "groups.view",
                "component"    => "chat",
                "location"     => "3",
                "is_active"    => "1",
                "ordering"     => "3"
            ],
        ];
    }
    
    protected function setPhrase()
    {
        $this->phrase = [

        ];
    }
    
    protected function setOthers()
    {
        $this->database = [
            'Shoutbox', 'Shoutbox_Quoted_Message'
        ];
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'https://store.phpfox.com/';
        $this->_apps_dir = 'core-shoutbox';
        $this->admincp_route = \Phpfox::getLib('url')->makeUrl('admincp.app.settings', ['id' => 'phpFox_Shoutbox']);
    }

    protected function preInstall()
    {
        /** @var $db \Phpfox_Database_Driver_Mysql */
        $db = Phpfox::getLib('database');
        if ($db->tableExists(Phpfox::getT('shoutbox'))) {
            $aColumns = $db->getColumns(Phpfox::getT('shoutbox'));
            if (in_array('shout_id', array_column($aColumns, 'Field'))) {
                $db->renameTable(Phpfox::getT('shoutbox'), Phpfox::getT('shoutbox_old'));
            }
        }
    }
}
