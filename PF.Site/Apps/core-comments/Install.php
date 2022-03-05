<?php

namespace Apps\Core_Comments;

use Core\App;

/**
 * Class Install
 * @copyright [PHPFOX_COPYRIGHT]
 * @author    phpFox LLC
 * @version   4.1.0
 * @package   Apps\Core_Comments
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    protected function setId()
    {
        $this->id = 'Core_Comments';
    }

    protected function setAlias()
    {
        $this->alias = 'comment';
    }

    protected function setName()
    {
        $this->name = _p('comments');
    }

    protected function setVersion()
    {
        $this->version = '4.1.10';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.7';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'comment_hash_check'                     => [
                'var_name'    => 'comment_hash_check',
                'info'        => 'Comment Hash Check',
                'description' => 'If enabled this will check if the last X comments added in the last Y minutes are identical to the comment being added.<br>Notice: X & Y are settings that can be changed. X = Comments To Check. Y = Comment Minutes to Wait Until Next Check.',
                'type'        => 'boolean',
                'value'       => '0',
                'group_id'    => 'spam',
                'ordering'    => $iIndex++
            ],
            'comments_to_check'                      => [
                'var_name'    => 'comments_to_check',
                'info'        => 'Comments To Check',
                'description' => 'If the setting to check if comments are identical you can set here how many comments in the past should be checked.',
                'type'        => 'string',
                'value'       => '10',
                'group_id'    => 'spam',
                'ordering'    => $iIndex++
            ],
            'total_minutes_to_wait_for_comments'     => [
                'var_name'    => 'total_minutes_to_wait_for_comments',
                'info'        => 'Comment Minutes to Wait Until Next Check',
                'description' => 'If the setting to check if comments are identical you can set here how far back we should check in minutes.',
                'type'        => 'integer',
                'value'       => '2',
                'group_id'    => 'spam',
                'ordering'    => $iIndex++
            ],
            'comment_is_threaded'                    => [
                'var_name'    => 'comment_is_threaded',
                'info'        => 'Thread Display',
                'description' => 'If set to Yes comments will be displayed in a thread format allowing users to reply to specific comments instead of the general item they are commenting on.',
                'type'        => 'boolean',
                'value'       => '0',
                'ordering'    => $iIndex++
            ],
            'comment_enable_photo' => [
                'var_name'    => 'comment_enable_photo',
                'info'        => 'Enable Photo on comment',
                'description' => 'If set to Yes users can attach photo to their comment',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex++
            ],
            'comment_enable_sticker' => [
                'var_name'    => 'comment_allow_sticker',
                'info'        => 'Enable Sticker on comment',
                'description' => 'If set to Yes users can select and insert a sticker to their comment',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex++
            ],
            'comment_enable_emoticon' => [
                'var_name'    => 'comment_enable_emoticon',
                'info'        => 'Enable Emojis on comment',
                'description' => 'If set to Yes users can select and insert emojis to their comment',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex++
            ],
            'comments_show_on_activity_feeds'        => [
                'var_name'    => 'comments_show_on_activity_feeds',
                'info'        => 'Number of comment will be shown on activity feeds',
                'description' => 'Define how many comments should be displayed on each activity feed. 0 mean Unlimited <br> View previous comments and View more comments options is available.',
                'type'        => 'integer',
                'value'       => '4',
                'ordering'    => $iIndex++
            ],
            'comments_show_on_item_details'          => [
                'var_name'    => 'comments_show_on_item_details',
                'info'        => 'Number of comment will be shown on item details',
                'description' => 'Define how many comments should be displayed on each item detail. 0 mean Unlimited <br> View previous comments and View more comments options is available.',
                'type'        => 'integer',
                'value'       => '4',
                'ordering'    => $iIndex++
            ],
            'comment_show_replies_on_comment'        => [
                'var_name'    => 'comment_show_replies_within_comment',
                'info'        => 'Show replies on comment',
                'description' => 'If Yes, replies will be shown with comment when your user browse activity feed. No means replies will be hidden as a link',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex++
            ],
            'comment_replies_show_on_activity_feeds' => [
                'var_name'    => 'comment_replies_show_on_activity_feeds',
                'info'        => 'Number of replies will be shown on each comment on activity feeds',
                'description' => 'Define how many replies should be displayed on each comment on activity feed. 0 means Unlimited. <br> View previous replies and View more replies options is available. <br> Note: This is only used if Show replies on comment are enabled. ',
                'type'        => 'integer',
                'value'       => '1',
                'ordering'    => $iIndex++
            ],
            'comment_replies_show_on_item_details'   => [
                'var_name'    => 'comment_replies_show_on_item_details',
                'info'        => 'Number of replies will be shown on each comment on item details',
                'description' => 'Define how many replies should be displayed within each comment on item details. 0 means Unlimited. <br> View previous replies and View more replies options is available. <br> Note: This is only used if Show replies on comment are enabled. ',
                'type'        => 'integer',
                'value'       => '1',
                'ordering'    => $iIndex++
            ],
            'comment_setting_subject_comment_approved_on_site_title' => [
                'var_name' => 'comment_setting_subject_comment_approved_on_site_title',
                'info' => 'Comments - Email Subject - Comment Is Approved',
                'description' => 'Email subject of the "Comment Is Approved" notification. <a role="button" onclick="$Core.editMeta(\'comment_approved_on_site_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="comment_approved_on_site_title"></span>',
                'type' => '',
                'value' => '{_p var="comment_approved_on_site_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'comment_setting_content_comment_approved_on_site_title' => [
                'var_name' => 'comment_setting_content_comment_approved_on_site_title',
                'info' => 'Comments - Email Content - Comment Is Approved',
                'description' => 'Email content of the "Comment Is Approved" notification. <a role="button" onclick="$Core.editMeta(\'one_of_your_comments_on_site_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="one_of_your_comments_on_site_title"></span>',
                'type' => '',
                'value' => '{_p var="one_of_your_comments_on_site_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'can_post_comments'              => [
                'var_name'    => 'can_post_comments',
                'info'        => 'Can post comments?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
            ],
            'edit_own_comment'               => [
                'var_name'    => 'edit_own_comment',
                'info'        => 'Can edit their own comments?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
            ],
            'edit_user_comment'              => [
                'var_name'    => 'edit_user_comment',
                'info'        => 'Can edit all comments?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
            ],
            'delete_own_comment'             => [
                'var_name'    => 'delete_own_comment',
                'info'        => 'Can delete their own comments?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
            ],
            'delete_user_comment'            => [
                'var_name'    => 'delete_user_comment',
                'info'        => 'Can delete all comments?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
            ],
            'comment_post_flood_control'     => [
                'var_name'    => 'comment_post_flood_control',
                'info'        => 'Define how many minutes this user group should wait before they can post a new comment.<br>Note: Set to 0 if there should be no limit.',
                'description' => '',
                'type'        => 'integer',
                'value'       => 0,
            ],
            'can_moderate_comments'          => [
                'var_name'    => 'can_moderate_comments',
                'info'        => 'Can moderate comments?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
            ],
            'approve_all_comments'           => [
                'var_name'    => 'approve_all_comments',
                'info'        => 'Comments must be approved first before they are displayed publicly?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
            ],
            'can_delete_comment_on_own_item' => [
                'var_name'    => 'can_delete_comment_on_own_item',
                'info'        => 'Can delete any comments posted on their own item?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
            ],
            'points_comment'                 => [
                'var_name'    => 'points_comment',
                'info'        => 'Activity points',
                'description' => 'Specify how many points the user will receive when adding a new comment.',
                'type'        => 'integer',
                'value'       => 1,
            ]
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
        $this->phrase = $this->_app_phrases;
    }

    protected function setOthers()
    {
        $this->admincp_route = '/comment/admincp';

        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->admincp_menu = [
            _p('pending_comments') => 'comment.pending-comments',
            _p('spam_comments')    => 'comment.spam-comments',
            _p('manage_stickers')  => '#'
        ];
        $this->_apps_dir = 'core-comments';
        $this->_admin_cp_menu_ajax = false;
        $this->database = [
            'Comment',
            'Comment_Text',
            'Comment_Hash',
            'Comment_Emoticon',
            'Comment_Hide',
            'Comment_Sticker_Set',
            'Comment_User_Sticker_Set',
            'Comment_Stickers',
            'Comment_Extra',
            'Comment_Track',
            'Comment_Previous_Versions'
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/comment/'
        ];
    }
}