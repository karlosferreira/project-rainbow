<?php

namespace Apps\Core_Activity_Points;

use Core\App;

/**
 * Class Install
 * @author  SonVLH <sonvlh@younetco.com>
 * @version 4.6.0
 * @package Apps\Core_Activity_Points
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    protected function setId()
    {
        $this->id = 'Core_Activity_Points';
    }

    protected function setAlias()
    {
        $this->alias = 'activitypoint';
    }

    protected function setName()
    {
        $this->name = _p('module_activitypoint');
    }

    protected function setVersion()
    {
        $this->version = '4.7.8';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.7.2';
    }

    protected function setSettings()
    {
        $this->settings = [
            'enable_activity_points' => [
                'var_name' => 'enable_activity_points',
                'info' => 'Show Activity Points',
                'description' => 'Enable this option to show the activity points.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => 1,
            ],
            'activity_points_conversion_rate' => [
                'var_name' => 'activity_points_conversion_rate',
                'info' => 'Activity Points Conversion Rate',
                'description' => 'Define how much an activity point is worth for each available currency.',
                'type' => 'string',
                'ordering' => 2,
            ]
        ];
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'can_purchase_with_activity_points' => [
                'var_name' => 'can_purchase_with_activity_points',
                'info' => 'Allow to use Activity Points for exchanging items',
                'description' => 'Enable this option if you would like to allow users to be able to purchase items by their activity points.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
            ],
            'can_purchase_points' => [
                'var_name' => 'can_purchase_points',
                'info' => 'Allow member to purchase Activity Points',
                'description' => 'Enable this option if you would like users to buy point packages.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
            ],
            'can_gift_activity_points' => [
                'var_name' => 'can_gift_activity_points',
                'info' => 'Can members of this user group gift activity points?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
            ],
            'can_admin_adjust_activity_points' => [
                'var_name' => 'can_admin_adjust_activity_points',
                'info' => 'Allow users of this group using the function "Send/Minus Activity Points" in AdminCP > Member Points',
                'description' => 'If enabled, this user group will be allowed to send or minus activity points for members, or multi members in the community.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
            ],
            'maximum_activity_points_admin_can_adjust' => [
                'var_name' => 'maximum_activity_points_admin_can_adjust',
                'info' => 'Maximum Activity Points that this user group will send to members in AdminCP > Member Points',
                'description' => '',
                'type' => 'integer',
                'value' => [
                    '1' => '1000',
                    '2' => '0',
                    '3' => '0',
                    '4' => '50',
                    '5' => '0'
                ],
            ],
            'period_time_admin_adjust_activity_points' => [
                'var_name' => 'period_time_admin_adjust_activity_points',
                'info' => 'The period for sending Activity Points to members in AdminCP > Member Points',
                'description' => '',
                'type' => 'select',
                'value' => 'per_day',
                'options' => [
                    'per_day' => 'Per day',
                    'per_week' => 'Per week',
                    'per_month' => 'Per month',
                    'per_year' => 'Per year'
                ],
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
        $this->addPhrases($this->_app_phrases);
    }

    protected function setOthers()
    {
        $this->admincp_menu = [
            _p('activitypoint_admincp_points_setting') => '#',
            _p('activitypoint_points_package') => 'activitypoint.package',
            _p('activitypoint_admincp_transaction_history') => 'activitypoint.transaction',
            _p('activitypoint_member_points') => 'activitypoint.point'
        ];
        $this->admincp_action_menu = [
            '/admincp/activitypoint/add-package' => _p('activitypoint_add_new_package')
        ];
        $this->menu = [];
        $this->admincp_route = "/activitypoint/admincp";
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'https://store.phpfox.com/';
        $this->_apps_dir = "core-activity-points";
        $this->database = ['Activity_Point_Package', 'Activity_Point_Package_Purchase', 'Activity_Point_Setting', 'Activity_Point_Statistics', 'Activity_Point_Transaction', 'Activity_Point_Period_Adjust_Point'];
        $this->_admin_cp_menu_ajax = false;
        $this->_writable_dirs = [
            'PF.Base/file/pic/activitypoint/'
        ];
    }
}