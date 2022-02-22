<?php

namespace Apps\Core_Subscriptions;

use Core\App;


class Install extends App\App
{
    private $_app_phrases = [];

    protected function setId()
    {
        $this->id = 'Core_Subscriptions';
    }

    protected function setAlias()
    {
        $this->alias = 'subscribe';
    }

    protected function setName()
    {
        $this->name = _p('module_subscribe');
    }

    protected function setVersion()
    {
        $this->version = '4.6.7';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.0';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'enable_subscription_packages' => [
                'var_name' => 'enable_subscription_packages',
                'info' => 'Enable Subscription Packages',
                'description' => 'Enable Subscription Packages',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => $iIndex++,

            ],
            'subscribe_is_required_on_sign_up' => [
                'var_name' => 'subscribe_is_required_on_sign_up',
                'info' => 'Subscription on registration is required ?',
                'description' => 'If members should be required to select a subscription package when they register set this to <b>Yes</b>.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => $iIndex++,
            ],
            'subscribe_setting_subject_delete_package_template' => [
                'info' => 'Subscribe - Email Subject - The Package Is No Longer Available',
                'description' => 'Email subject of the "The Package Is No Longer Available" notification.<a role="button" onclick="$Core.editMeta(\'subject_delete_package_template\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="subject_delete_package_template"></span>',
                'type' => '',
                'value' => '{_p var="subject_delete_package_template"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_content_delete_package_template' => [
                'info' => 'Subscribe - Email Content - The Package Is No Longer Available',
                'description' => 'Email content of the "The Package Is No Longer Available" notification.<a role="button" onclick="$Core.editMeta(\'delete_package_template\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="delete_package_template"></span>',
                'type' => '',
                'value' => '{_p var="delete_package_template"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_subject_membership_successfully_updated' => [
                'info' => 'Subscribe - Email Subject - Membership Successfully Updated',
                'description' => 'Email subject of the "Membership Successfully Updated" notification.<a role="button" onclick="$Core.editMeta(\'membership_successfully_updated_site_title\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="membership_successfully_updated_site_title"></span>',
                'type' => '',
                'value' => '{_p var="membership_successfully_updated_site_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_content_membership_successfully_updated' => [
                'info' => 'Subscribe - Email Content - Membership Successfully Updated',
                'description' => 'Email content of the "Membership Successfully Updated" notification.<a role="button" onclick="$Core.editMeta(\'your_membership_on_site_title_has_successfully_been_updated\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_membership_on_site_title_has_successfully_been_updated"></span>',
                'type' => '',
                'value' => '{_p var="your_membership_on_site_title_has_successfully_been_updated"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_subject_membership_pending' => [
                'info' => 'Subscribe - Email Subject - Your Membership Is Pending',
                'description' => 'Email subject of the "Your Membership Is Pending" notification.<a role="button" onclick="$Core.editMeta(\'membership_pending_site_title\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="membership_pending_site_title"></span>',
                'type' => '',
                'value' => '{_p var="membership_pending_site_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_content_membership_pending' => [
                'info' => 'Subscribe - Email Content - Your Membership Is Pending',
                'description' => 'Email content of the "Your Membership Is Pending" notification.<a role="button" onclick="$Core.editMeta(\'your_membership_subscription_on_site_title_is_currently_pending\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_membership_subscription_on_site_title_is_currently_pending"></span>',
                'type' => '',
                'value' => '{_p var="your_membership_subscription_on_site_title_is_currently_pending"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_subject_notify_expiration_template' => [
                'info' => 'Subscribe - Email Subject - Your Subscription Will Be Expired Soon',
                'description' => 'Email subject of the "Your Subscription Will Be Expired Soon" notification.<a role="button" onclick="$Core.editMeta(\'subject_notify_expiration_template\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="subject_notify_expiration_template"></span>',
                'type' => '',
                'value' => '{_p var="subject_notify_expiration_template"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_content_notify_expiration_template' => [
                'info' => 'Subscribe - Email Content - Your Subscription Will Be Expired Soon',
                'description' => 'Email content of the "Your Subscription Will Be Expired Soon" notification.<a role="button" onclick="$Core.editMeta(\'notify_expiration_template\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="notify_expiration_template"></span>',
                'type' => '',
                'value' => '{_p var="notify_expiration_template"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_subject_your_subscription_is_canceled' => [
                'info' => 'Subscribe - Email Subject - Your Subscription Is Canceled Automatically',
                'description' => 'Email subject of the "Your Subscription Is Canceled" notification.<a role="button" onclick="$Core.editMeta(\'your_subscription_is_canceled\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_subscription_is_canceled"></span>',
                'type' => '',
                'value' => '{_p var="your_subscription_is_canceled"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_content_your_subscription_is_canceled' => [
                'info' => 'Subscribe - Email Content - Your Subscription Is Canceled Automatically',
                'description' => 'Email content of the "Your Subscription Is Canceled" notification.<a role="button" onclick="$Core.editMeta(\'subscription_auto_cancel_message\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="subscription_auto_cancel_message"></span>',
                'type' => '',
                'value' => '{_p var="subscription_auto_cancel_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_subject_admin_change_status_to_cancel' => [
                'info' => 'Subscribe - Email Subject - Your Subscription Has Been Cancelled By Admin',
                'description' => 'Email subject of the "Your Subscription Has Been Cancelled By Admin" notification.<a role="button" onclick="$Core.editMeta(\'admin_change_status_to_cancel_subject\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="admin_change_status_to_cancel_subject"></span>',
                'type' => '',
                'value' => '{_p var="admin_change_status_to_cancel_subject"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_content_admin_change_status_to_cancel' => [
                'info' => 'Subscribe - Email Content - Your Subscription Has Been Cancelled By Admin',
                'description' => 'Email content of the "Your Subscription Has Been Cancelled By Admin" notification.<a role="button" onclick="$Core.editMeta(\'admin_change_status_to_cancel_template\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="admin_change_status_to_cancel_template"></span>',
                'type' => '',
                'value' => '{_p var="admin_change_status_to_cancel_template"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_subject_admin_change_status_to_active' => [
                'info' => 'Subscribe - Email Subject - Your Subscription Has Been Activated By Admin',
                'description' => 'Email subject of the "Your Subscription Has Been Activated By Admin" notification.<a role="button" onclick="$Core.editMeta(\'admin_change_status_to_active_subject\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="admin_change_status_to_active_subject"></span>',
                'type' => '',
                'value' => '{_p var="admin_change_status_to_active_subject"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
            'subscribe_setting_content_admin_change_status_to_active' => [
                'info' => 'Subscribe - Email Content - Your Subscription Has Been Activated By Admin',
                'description' => 'Email content of the "Your Subscription Has Been Activated By Admin" notification.<a role="button" onclick="$Core.editMeta(\'admin_change_status_to_active_template\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="admin_change_status_to_active_template"></span>',
                'type' => '',
                'value' => '{_p var="admin_change_status_to_active_template"}',
                'ordering' => $iIndex++,
                'group_id' => 'email',
            ],
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {

    }

    protected function setComponent()
    {
        $this->component = [
            'block' => [
                'message' => '',
            ],
            'controller' => [
                'index' => 'subscribe.index',
            ],
            'ajax' => [

            ],
        ];
    }

    protected function setComponentBlock()
    {

    }

    protected function setPhrase()
    {
        $this->addPhrases($this->_app_phrases);
    }

    protected function setOthers()
    {
        $this->admincp_route = "admincp.subscribe";

        $this->admincp_menu = [
            _p('admin_menu_manage_packages') => 'subscribe',
            _p('subscribe_menu_subscriptions_title') => 'subscribe.list',
            _p('admin_menu_comparison') => 'subscribe.compare',
            _p('subscribe_menu_cancel_reason_title') => 'subscribe.reason',
        ];

        $this->admincp_action_menu = [
            '/admincp/subscribe/add' => _p('subscribe_new_package_title'),
        ];

        $this->menu = [];
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->_apps_dir = "core-subscriptions";
        $this->_writable_dirs = [
            'PF.Base/file/pic/subscribe/',
        ];

        $this->database = [
            'Subscribe_Package', 'Subscribe_Purchase', 'Subscribe_Compare', 'Subscribe_Recent_Payment', 'Subscribe_Reason', 'Subscribe_Cancel_Reason',
        ];

        $this->_admin_cp_menu_ajax = false;
    }
}