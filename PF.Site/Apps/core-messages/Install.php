<?php

namespace Apps\Core_Messages;

use Core\App;
use Phpfox;

class Install extends App\App
{
    private $_app_phrases = [

    ];

    protected function setId()
    {
        $this->id = 'Core_Messages';
    }

    protected function setAlias()
    {
        $this->alias = 'mail';
    }

    protected function setName()
    {
        $this->name = _p('mail_app_title');
    }

    protected function setVersion()
    {
        $this->version = '4.7.8';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.7.0';
    }

    protected function setSettings()
    {
        $this->settings = [
            'chat_group_member_maximum' => [
                'var_name' => 'chat_group_member_maximum',
                'info' => 'Maximum members that user can add to a group chat.',
                'description' => 'Maximum members that user can add to a group chat.',
                'type' => 'integer',
                'value' => 5,
                'ordering' => 1,
            ],
            'custom_list_maximum' => [
                'var_name' => 'custom_list_maximum',
                'info' => 'Maximum custom lists can be created.',
                'description' => 'Maximum custom lists can be created.',
                'type' => 'integer',
                'value' => 5,
                'ordering' => 2,
            ],
            'custom_list_member_maximum' => [
                'var_name' => 'custom_list_member_maximum',
                'info' => 'Maximum members of a custom list.',
                'description' => 'Maximum members of a custom list.',
                'type' => 'integer',
                'value' => 5,
                'ordering' => 3,
            ],
            'show_preview_message' => [
                'var_name' => 'show_last_chat_message',
                'info' => 'Show the last chat message',
                'description' => 'Show the last chat message beneath display names  in the message list box.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => 15,
            ],
            'disallow_select_of_recipients' => [
                'var_name' => 'check_privacy_settings_of_recipients',
                'info' => 'Check privacy settings of recipients',
                'description' => 'When this setting is enabled, the system will check the privacy settings of recipients when sending a message.',
                'type' => 'boolean',
                'value' => '0',
                'ordering' => 16,
            ],
        ];
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'can_compose_message' => [
                'var_name' => 'can_compose_message',
                'info' => 'Can compose message ?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1'
                ]
            ],
            'can_add_attachment_on_mail' => [
                'var_name' => 'can_add_attachment_on_mail',
                'info' => 'Can add attachment on mail ?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1'
                ]
            ],
            'restrict_message_to_friends' => [
                'var_name' => 'restrict_message_to_friends',
                'info' => 'Restrict message to friends',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '0',
                    '2' => '1',
                    '3' => '1',
                    '4' => '0'
                ]
            ],
            'can_read_private_messages' => [
                'var_name' => 'can_read_private_messages',
                'info' => 'Can read private messages ?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0'
                ]
            ],
            'can_delete_others_messages' => [
                'var_name' => 'can_delete_others_messages',
                'info' => 'Can delete messages ?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0'
                ]
            ],
            'enable_captcha_on_mail' => [
                'var_name' => 'enable_captcha_on_mail',
                'info' => 'Enable Captcha on mail',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0'
                ]
            ],
        ];
    }

    protected function setComponent()
    {
        $this->component = [
            'block' => [

            ],
            'controller' => [
                'compose' => 'mail.compose',
                'index' => 'mail.index',
            ],
            'ajax' => [
                'ajax' => ''
            ]
        ];
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
        $this->admincp_menu = [
            _p('conversations_management') => 'mail.conversations',
            _p('export_data_to_chat_plus') => 'mail.export-data-chat-plus',
            _p('Settings') => '#'
        ];
        $this->admincp_action_menu = [];
        $this->menu = [];
        $this->admincp_route = Phpfox::getLib('url')->makeUrl('admincp.app.settings', ['id' => 'Core_Messages']);
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->_apps_dir = "core-messages";
        $this->database = ['Mail_Thread', 'Mail_Thread_Forward', 'Mail_Thread_Text', 'Mail_Thread_User', 'Mail_Thread_Group_Title', 'Mail_Thread_Folder', 'Mail_Thread_Custom_List', 'Mail_Thread_User_Compare'];
        $this->_admin_cp_menu_ajax = false;
    }
}