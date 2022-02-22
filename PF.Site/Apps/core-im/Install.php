<?php

namespace Apps\PHPfox_IM;

use Core\App;

class Install extends App\App
{
    protected function setId()
    {
        $this->id = 'PHPfox_IM';
    }

    public $store_id = 1837;

    /**
     * Set start and end support version of your App.
     */
    protected function setSupportVersion()
    {
        $this->start_support_version = '4.7.0';
    }

    protected function setAlias()
    {
        $this->alias = 'im';
    }

    protected function setName()
    {
        $this->name = _p('instant_messaging');
    }

    protected function setVersion()
    {
        $this->version = '4.8.1';
    }

    protected function setSettings()
    {
        $this->settings = [
            'pf_im_chat_server' => [
                'var_name' => 'pf_im_chat_server',
                'info' => 'Chat Server',
                'value' => 'nodejs',
                'type' => 'select',
                'options' => [
                    'nodejs' => 'Node JS',
                    'firebase' => 'Firebase'
                ],
                'ordering' => 1
            ],
            'pf_firebase_auth_code_snippet' => [
                'var_name' => 'pf_firebase_auth_code_snippet',
                'info' => 'Firebase Config Object',
                'description' => 'The config object got from your firebase project for adding firebase to your web app. How to get this config? <a href="https://firebase.google.com/docs/web/setup" target="_blank">Click here</a>.<br/>Notice: Our default value is an example for config object format, please replace it by your config.',
                'type' => 'large_string',
                'value' => 'var firebaseConfig = {
                    apiKey: "<API_KEY>",
                    authDomain: "<PROJECT_ID>.firebaseapp.com",
                    databaseURL: "https://<PROJECT_ID>.firebaseio.com",
                    projectId: "<PROJECT_ID>",
                    storageBucket: "<PROJECT_ID>.appspot.com",
                    messagingSenderId: "<SENDER_ID",
                    appId: "<APP_ID>"
                  };
                 ',
                'ordering' => 2
            ],
            'pf_im_algolia_app_id' => [
                'var_name' => 'pf_im_algolia_app_id',
                'info' => 'Algolia App Id',
                'description' => 'The Algolia is a plugin to support search full-text messages with Firebase. If you don\'t set up it, user can only search prefix of message with case sensitive. How to get this key? <a href="https://www.algolia.com/doc/guides/sending-and-managing-data/send-and-update-your-data/tutorials/firebase-algolia/?language=javascript#create-an-algolia-application" target="_blank">Click here</a>',
                'ordering' => 3
            ],
            'pf_im_algolia_api_key' => [
                'var_name' => 'pf_im_algolia_api_key',
                'info' => 'Algolia API Key',
                'type' => 'password',
                'description' => 'The Admin API Key of your Algolia App',
                'ordering' => 4
            ],
            'pf_im_node_server' => [
                'var_name' => "pf_im_node_server",
                'info' => "Provide your Node JS server",
                'ordering' => 5
            ],
            'pf_im_node_server_key' => [
                'var_name' => 'pf_im_node_server_key',
                'info' => 'Provide your Node JS server key (Ignore this setting if you are using phpFox IM hosting service)',
                'ordering' => 6
            ],
            'pf_total_conversations' => [
                'var_name' => "pf_total_conversations",
                'info' => "Total Latest Conversations in IM List",
                'description' => 'For unlimited add "0" without quotes.',
                "js_variable" => true,
                'value' => 20,
                'ordering' => 7
            ],
            'pf_time_to_delete_message' => [
                'var_name' => "pf_time_to_delete_message",
                'info' => "How long user can still delete their own message? (days)",
                'description' => 'Define how long a message can be deleted. After this time, message cannot be deleted by owner. Put 0 means owner can delete their messages all time.',
                'value' => 0,
                'ordering' => 8
            ],
            'pf_im_minimise_chat_dock' => [
                'var_name' => "pf_im_minimise_chat_dock",
                'info' => "Minimise chat window in the first time users access to site",
                'description' => '',
                'type' => 'boolean',
                'value' => 1,
                'ordering' => 8
            ],
            'pf_im_allow_non_friends' => [
                'var_name' => "pf_im_allow_non_friends",
                'info' => "Allow users chat with non-friends",
                'description' => '',
                'type' => 'input:radio',
                'value' => 0,
                'ordering' => 9
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
    }

    protected function setOthers()
    {
        $this->admincp_menu = [
            'IM Hosting Package' => '#',
            'Manage Notification Sound' => 'im.manage-sound',
            'Delete Messages' => 'im.delete-messages',
            'Import Data From v3' => 'im.import-data-v3',
            'Export Data To Chat Plus' => 'im.export-data-chat-plus',
        ];
        $this->admincp_route = "/im/admincp";
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->_apps_dir = 'core-im';
        $this->_admin_cp_menu_ajax = false;
    }
}
