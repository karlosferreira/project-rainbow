<?php

namespace Apps\PHPfox_Groups;

use Core\App;
use Core\App\Install\Setting;

/**
 * Class Install
 * @author  phpFox
 * @package Apps\PHPfox_Groups
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    public $store_id = 2007;

    protected function setId()
    {
        $this->id = 'PHPfox_Groups';
    }

    /**
     * Set start and end support version of your App.
     */
    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.7';
    }

    protected function setAlias()
    {
        $this->alias = 'groups';
    }

    public function setName()
    {
        $this->name = _p('Groups');
    }

    public function setVersion()
    {
        $this->version = '4.7.14';
    }

    public function setSettings()
    {
        $this->settings = [
            'groups_default_item_privacy' => [
                'info' => 'Default Item Privacy',
                'description' => 'Set default item privacy when a group is created.',
                'type' => 'select',
                'options' => [
                    '1' => 'Members Only',
                    '2' => 'Admins Only'
                ],
                'value' => 1,
                'ordering' => 1,
            ],
            'groups_limit_per_category' => [
                'info' => 'Groups Limit Per Category',
                'description' => 'Define the limit of how many groups per category can be displayed when viewing All Groups page.',
                'type' => 'integer',
                'value' => 6,
                'ordering' => 1
            ],
            'pagination_at_search_groups' => [
                'info' => 'Paging Type',
                'description' => '',
                'type' => 'select',
                'options' => [
                    'loadmore' => 'Scrolling down to load more items',
                    'next_prev' => 'Use Next and Pre buttons',
                    'pagination' => 'Use pagination with page number'
                ],
                'value' => 'loadmore',
                'ordering' => 0
            ],
            'display_groups_profile_photo_within_gallery' => [
                'info' => 'Display groups profile photo within gallery',
                'description' => 'Disable this feature if you do not want to display groups profile photos within the photo gallery.',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => 3
            ],
            'display_groups_cover_photo_within_gallery' => [
                'info' => 'Display groups cover photo within gallery',
                'description' => 'Disable this feature if you do not want to display groups cover photos within the photo gallery.',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => 4
            ],
            'groups_setting_meta_description' => [
                'info' => 'Groups Meta Description',
                'description' => 'Meta description added to groups related to the Groups app. <a role="button" onclick="$Core.editMeta(\'seo_groups_meta_description\', true)">Click here</a> to edit meta description.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_groups_meta_description"></span>',
                'type' => '',
                'value' => '{_p var="seo_groups_meta_description"}',
                'ordering' => 5,
                'group_id' => 'seo'
            ],
            'groups_setting_meta_keywords' => [
                'info' => 'Groups Meta Keywords',
                'description' => 'Meta keywords that will be displayed on sections related to the Groups app. <a role="button" onclick="$Core.editMeta(\'seo_groups_meta_keywords\', true)">Click here</a> to edit meta keywords.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_groups_meta_keywords"></span>',
                'type' => '',
                'value' => '{_p var="seo_groups_meta_keywords"}',
                'ordering' => 6,
                'group_id' => 'seo'
            ],
            'groups_setting_subject_email_posted_a_link_on_group_for_admins' => [
                'info' => 'Groups - Email Subject (For Group Admins) - Someone Posted A Link On Group',
                'description' => 'Email subject (For Group Admins) of the "Someone Posted A Link On Group" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_posted_a_link_on_group_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_posted_a_link_on_group_subject"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_posted_a_link_on_group_subject"}',
                'ordering' => 7,
                'group_id' => 'email'
            ],
            'groups_setting_content_email_posted_a_link_on_group_for_admins' => [
                'info' => 'Groups - Email Content (For Group Admins) - Someone Posted A Link On Group',
                'description' => 'Email content (For Group Admins) of the "Someone Posted A Link On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_link_on_group_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_link_on_group_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_link_on_group_link"}',
                'ordering' => 8,
                'group_id' => 'email'
            ],
            'groups_setting_subject_email_posted_a_link_on_group_for_owner_group' => [
                'info' => 'Groups - Email Subject (For Group Owner) - Someone Posted A Link On Group',
                'description' => 'Email subject (For Group Owner) of the "Someone Posted A Link On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_link_on_your_group_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_link_on_your_group_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_link_on_your_group_title"}',
                'ordering' => 9,
                'group_id' => 'email'
            ],
            'groups_setting_content_email_posted_a_link_on_group_for_owner_group' => [
                'info' => 'Groups - Email Content (For Group Owner) - Someone Posted A Link On Group',
                'description' => 'Email content (For Group Owner) of the "Someone Posted A Link On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_link_on_your_group_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_link_on_your_group_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_link_on_your_group_link"}',
                'ordering' => 10,
                'group_id' => 'email'
            ],
            'groups_setting_subject_wrote_a_comment_on_group' => [
                'info' => 'Groups - Email Subject (For Group Admins) - Someone Wrote A Comment On Group',
                'description' => 'Email subject (For Group Admins) of the "Someone Wrote A Comment On Group" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_wrote_a_comment_on_group_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_wrote_a_comment_on_group_subject"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_wrote_a_comment_on_group_subject"}',
                'ordering' => 11,
                'group_id' => 'email'
            ],
            'groups_setting_content_wrote_a_comment_on_group' => [
                'info' => 'Groups - Email Content (For Group Admins) - Someone Wrote A Comment On Group',
                'description' => 'Email content (For Group Admins) of the "Someone Wrote A Comment On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_on_group_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_wrote_a_comment_on_group_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_on_group_link"}',
                'ordering' => 12,
                'group_id' => 'email'
            ],
            'groups_setting_subject_wrote_a_comment_on_group_for_owner' => [
                'info' => 'Groups - Email Subject (For Group Owner) - Someone Wrote A Comment On Group',
                'description' => 'Email subject (For Group Owner) of the "Someone Wrote A Comment On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_on_your_group_tile_email_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="' . $this->getVariableOfPhrase('full_name_wrote_a_comment_on_your_group_tile_email_subject') . '"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_on_your_group_tile_email_subject"}',
                'ordering' => 13,
                'group_id' => 'email'
            ],
            'groups_setting_content_wrote_a_comment_on_group_for_owner' => [
                'info' => 'Groups - Email Content (For Group Owner) - Someone Wrote A Comment On Group',
                'description' => 'Email content (For Group Owner) of the "Someone Wrote A Comment On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_on_your_group_tile_email_content_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_wrote_a_comment_on_your_group_tile_email_content_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_on_your_group_tile_email_content_link"}',
                'ordering' => 14,
                'group_id' => 'email'
            ],
            'groups_setting_subject_convert_old_group' => [
                'info' => 'Groups - Email Subject - Convert Old Group',
                'description' => 'Email subject of the "Convert Old Group" notification. <a role="button" onclick="$Core.editMeta(\'' . $this->getVariableOfPhrase('Groups converted') . '\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="' . $this->getVariableOfPhrase('Groups converted') . '"></span>',
                'type' => '',
                'value' => '{_p var="' . $this->getVariableOfPhrase('Groups converted') . '"}',
                'ordering' => 15,
                'group_id' => 'email'
            ],
            'groups_setting_content_convert_old_group' => [
                'info' => 'Groups - Email Content - Convert Old Group',
                'description' => 'Email content of the "Convert Old Group" notification. <a role="button" onclick="$Core.editMeta(\'' . $this->getVariableOfPhrase('All old groups (page type) converted new groups') . '\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="' . $this->getVariableOfPhrase('All old groups (page type) converted new groups') . '"></span>',
                'type' => '',
                'value' => '{_p var="' . $this->getVariableOfPhrase('All old groups (page type) converted new groups') . '"}',
                'ordering' => 16,
                'group_id' => 'email'
            ],
            'groups_setting_subject_tagged_in_a_post_in_group' => [
                'info' => 'Groups - Email Subject - Someone Tagged You In A Post In Group',
                'description' => 'Email subject of the "Someone Tagged You In A Post In Group" notification. <a role="button" onclick="$Core.editMeta(\'groups_full_name_tagged_you_in_a_post_in_group_title_no_html\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="groups_full_name_tagged_you_in_a_post_in_group_title_no_html"></span>',
                'type' => '',
                'value' => '{_p var="groups_full_name_tagged_you_in_a_post_in_group_title_no_html"}',
                'ordering' => 17,
                'group_id' => 'email'
            ],
            'groups_setting_content_tagged_in_a_post_in_group' => [
                'info' => 'Groups - Email Content - Someone Tagged You In A Post In Group',
                'description' => 'Email content of the "Someone Tagged You In A Post In Group" notification. <a role="button" onclick="$Core.editMeta(\'groups_user_name_tagged_you_in_a_post_in_group_title_check_it_out\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="groups_user_name_tagged_you_in_a_post_in_group_title_check_it_out"></span>',
                'type' => '',
                'value' => '{_p var="groups_user_name_tagged_you_in_a_post_in_group_title_check_it_out"}',
                'ordering' => 18,
                'group_id' => 'email'
            ],
            'groups_setting_subject_post_some_items_on_group' => [
                'info' => 'Groups - Email Subject - Someone Post Some Items On Group',
                'description' => 'Email subject of the "Someone Post Some Items On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_post_some_items_on_your_group_title_replacement\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_items_on_your_group_title_replacement"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_items_on_your_group_title_replacement"}',
                'ordering' => 19,
                'group_id' => 'email'
            ],
            'groups_setting_content_post_some_items_on_group' => [
                'info' => 'Groups - Email Content - Someone Post Some Items On Group',
                'description' => 'Email content of the "Someone Post Some Items On Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_post_some_items_on_your_group_title_link_replacement\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_items_on_your_group_title_link_replacement"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_items_on_your_group_title_link_replacement"}',
                'ordering' => 20,
                'group_id' => 'email'
            ],
            'groups_setting_subject_user_join_group_for_group_owner' => [
                'info' => 'Groups - Email Subject (For Group Owner) - Someone Joined Group',
                'description' => 'Email subject (For Group Owner) of the "Someone Joined Group" notification. <a role="button" onclick="$Core.editMeta(\'' . $this->getVariableOfPhrase('{{ full_name }} joined your group "{{ title }}"') . '\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="' . $this->getVariableOfPhrase('{{ full_name }} joined your group "{{ title }}"') . '"></span>',
                'type' => '',
                'value' => '{_p var="' . $this->getVariableOfPhrase('{{ full_name }} joined your group "{{ title }}"') . '"}',
                'ordering' => 21,
                'group_id' => 'email'
            ],
            'groups_setting_content_user_join_group_for_group_owner' => [
                'info' => 'Groups - Email Content (For Group Owner) - Someone Joined Group',
                'description' => 'Email content (For Group Owner) of the "Someone Joined Group" notification. <a role="button" onclick="$Core.editMeta(\'' . $this->getVariableOfPhrase('{{ full_name }} joined your group "<a href="{{ link }}">{{ title }}</a>" To view this group follow the link below: <a href="{{ link }}">{{ link }}</a>') . '\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="' . $this->getVariableOfPhrase('{{ full_name }} joined your group "<a href="{{ link }}">{{ title }}</a>" To view this group follow the link below: <a href="{{ link }}">{{ link }}</a>') . '"></span>',
                'type' => '',
                'value' => '{_p var="' . $this->getVariableOfPhrase('{{ full_name }} joined your group "<a href="{{ link }}">{{ title }}</a>" To view this group follow the link below: <a href="{{ link }}">{{ link }}</a>') . '"}',
                'ordering' => 22,
                'group_id' => 'email'
            ],
            'groups_setting_subject_membership_accepted' => [
                'info' => 'Groups - Email Subject - Membership Accepted',
                'description' => 'Email subject of the "Membership accepted" notification. <a role="button" onclick="$Core.editMeta(\'' . $this->getVariableOfPhrase('Membership accepted to "{{ title }}"') . '\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="' . $this->getVariableOfPhrase('Membership accepted to "{{ title }}"') . '"></span>',
                'type' => '',
                'value' => '{_p var="' . $this->getVariableOfPhrase('Membership accepted to "{{ title }}"') . '"}',
                'ordering' => 23,
                'group_id' => 'email'
            ],
            'groups_setting_content_membership_accepted' => [
                'info' => 'Groups - Email Content - Membership Accepted',
                'description' => 'Email content of the "Membership Accepted" notification. <a role="button" onclick="$Core.editMeta(\'' . $this->getVariableOfPhrase('Your membership to the group "<a href="{{ link }}">{{ title }}</a>" has been accepted. To view this group follow the link below: <a href="{{ link }}">{{ link }}</a>') . '\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="' . $this->getVariableOfPhrase('Your membership to the group "<a href="{{ link }}">{{ title }}</a>" has been accepted. To view this group follow the link below: <a href="{{ link }}">{{ link }}</a>') . '"></span>',
                'type' => '',
                'value' => '{_p var="' . $this->getVariableOfPhrase('Your membership to the group "<a href="{{ link }}">{{ title }}</a>" has been accepted. To view this group follow the link below: <a href="{{ link }}">{{ link }}</a>') . '"}',
                'ordering' => 24,
                'group_id' => 'email'
            ],
            'groups_setting_subject_liked_a_comment_on_the_group' => [
                'info' => 'Groups - Email Subject - Someone Liked A Comment On Group',
                'description' => 'Email subject of the "Someone Liked A Comment On Group" notification. <a role="button" onclick="$Core.editMeta(\'groups_full_name_liked_a_comment_you_made_on_the_group_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="groups_full_name_liked_a_comment_you_made_on_the_group_title"></span>',
                'type' => '',
                'value' => '{_p var="groups_full_name_liked_a_comment_you_made_on_the_group_title"}',
                'ordering' => 25,
                'group_id' => 'email'
            ],
            'groups_setting_content_liked_a_comment_on_the_group' => [
                'info' => 'Groups - Email Content - Someone Liked A Comment On Group',
                'description' => 'Email content of the "Someone Liked A Comment On Group" notification. <a role="button" onclick="$Core.editMeta(\'groups_full_name_liked_a_comment_you_made_on_the_group_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="groups_full_name_liked_a_comment_you_made_on_the_group_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="groups_full_name_liked_a_comment_you_made_on_the_group_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => 26,
                'group_id' => 'email'
            ],
            'groups_setting_subject_invite_friends_to_the_group' => [
                'info' => 'Groups - Email Subject - Invite Friends To The Group',
                'description' => 'Email subject of the "Invite Friends To The Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_sent_you_a_group_invitation\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_sent_you_a_group_invitation"></span>',
                'type' => '',
                'value' => '{_p var="full_name_sent_you_a_group_invitation"}',
                'ordering' => 27,
                'group_id' => 'email'
            ],
            'groups_setting_content_invite_friends_to_the_group_1' => [
                'info' => 'Groups - Email Content - Invite Friends To The Group Part 1',
                'description' => 'Email content of the "Invite Friends To The Group" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_invited_you_to_the_group_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_invited_you_to_the_group_title"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_invited_you_to_the_group_title"}',
                'ordering' => 28,
                'group_id' => 'email'
            ],
            'groups_setting_content_invite_friends_to_the_group_2' => [
                'info' => 'Groups - Email Content - Invite Friends To The Group Part 2',
                'description' => 'Email content of the "Invite friends to the group" notification. <a role="button" onclick="$Core.editMeta(\'to_view_this_group_click_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="to_view_this_group_click_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="to_view_this_group_click_the_link_below_a_href_link_link_a"}',
                'ordering' => 29,
                'group_id' => 'email'
            ],
            'groups_setting_content_invite_friends_email_personal_message' => [
                'info' => 'Groups - Email Content - Invite Personal Message',
                'description' => 'Email content of the "Invite Friends To The Group" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_the_following_personal_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_the_following_personal_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_the_following_personal_message"}',
                'ordering' => 30,
                'group_id' => 'email'
            ],
            'groups_setting_subject_invite_via_email_in_the_group' => [
                'info' => 'Groups - Email Subject - Invite Via Email In The Group',
                'description' => 'Subject of the "Invite Via Email In The Group" email. <a role="button" onclick="$Core.editMeta(\'email_full_name_invited_you_to_the_group_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_invited_you_to_the_group_title"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_invited_you_to_the_group_title"}',
                'ordering' => 31,
                'group_id' => 'email'
            ],
            'groups_setting_content_invite_via_email_in_the_group' => [
                'info' => 'Groups - Email Content - Invite Via Email In The Group',
                'description' => 'Content of the "Invite Via Email In The Group" email. <a role="button" onclick="$Core.editMeta(\'full_name_invited_you_to_the_group_title_link_check_out\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_invited_you_to_the_group_title_link_check_out"></span>',
                'type' => '',
                'value' => '{_p var="full_name_invited_you_to_the_group_title_link_check_out"}',
                'ordering' => 32,
                'group_id' => 'email'
            ],
            'groups_setting_subject_invite_friends_become_admin_group' => [
                'info' => 'Groups - Email Subject - Invite Friends Become Group Admin',
                'description' => 'Email subject of the "Invite Friends Become Group Admin" notification. <a role="button" onclick="$Core.editMeta(\'email_you_have_been_invited_to_become_an_admin_of_group_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_you_have_been_invited_to_become_an_admin_of_group_subject"></span>',
                'type' => '',
                'value' => '{_p var="email_you_have_been_invited_to_become_an_admin_of_group_subject"}',
                'ordering' => 33,
                'group_id' => 'email'
            ],
            'groups_setting_content_invite_friends_become_admin_group' => [
                'info' => 'Groups - Email Content - Invite Friends Become Group Admin',
                'description' => 'Email content of the "Invite Friends Become Group Admin" notification. <a role="button" onclick="$Core.editMeta(\'email_you_have_been_invited_to_become_an_admin_of_group_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_you_have_been_invited_to_become_an_admin_of_group_message"></span>',
                'type' => '',
                'value' => '{_p var="email_you_have_been_invited_to_become_an_admin_of_group_message"}',
                'ordering' => 34,
                'group_id' => 'email'
            ],
            'groups_setting_subject_new_request_join_to_group_for_group_admins' => [
                'info' => 'Groups - Email Subject (For Group Admins) - New Request Join To Group',
                'description' => 'Email subject (For Group Admins) of the "New Request Join To Group" notification. <a role="button" onclick="$Core.editMeta(\'email_new_request_to_join_group_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_new_request_to_join_group_subject"></span>',
                'type' => '',
                'value' => '{_p var="email_new_request_to_join_group_subject"}',
                'ordering' => 35,
                'group_id' => 'email'
            ],
            'groups_setting_content_new_request_join_to_group_for_group_admins' => [
                'info' => 'Groups - Email Content (For Group Admins) - New Request Join To Group',
                'description' => 'Email content (For Group Admins) of the "New Request Join To Group" notification. <a role="button" onclick="$Core.editMeta(\'email_new_request_to_join_group_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_new_request_to_join_group_message"></span>',
                'type' => '',
                'value' => '{_p var="email_new_request_to_join_group_message"}',
                'ordering' => 36,
                'group_id' => 'email'
            ],
            'groups_setting_subject_new_request_join_to_group_for_group_owner' => [
                'info' => 'Groups - Email Subject (For Group Owner) - New Request Join To Group',
                'description' => 'Email subject (For Group Owner) of the "New Request Join To Group" notification. <a role="button" onclick="$Core.editMeta(\'email_new_request_to_join_your_group_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_new_request_to_join_your_group_subject"></span>',
                'type' => '',
                'value' => '{_p var="email_new_request_to_join_your_group_subject"}',
                'ordering' => 37,
                'group_id' => 'email'
            ],
            'groups_setting_content_new_request_join_to_group_for_group_owner' => [
                'info' => 'Groups - Email Content (For Group Owner) - New Request Join To Group',
                'description' => 'Email content (For Group Owner) of the "New Request Join To Group" notification. <a role="button" onclick="$Core.editMeta(\'email_new_request_to_join_your_group_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_new_request_to_join_your_group_message"></span>',
                'type' => '',
                'value' => '{_p var="email_new_request_to_join_your_group_message"}',
                'ordering' => 38,
                'group_id' => 'email'
            ],
            'groups_setting_subject_become_owner_group' => [
                'info' => 'Groups - Email Subject - Become Group Owner',
                'description' => 'Email subject of the "Become Group Owner" notification. <a role="button" onclick="$Core.editMeta(\'email_you_become_owner_of_group_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_you_become_owner_of_group_title"></span>',
                'type' => '',
                'value' => '{_p var="email_you_become_owner_of_group_title"}',
                'ordering' => 39,
                'group_id' => 'email'
            ],
            'groups_setting_content_become_owner_group' => [
                'info' => 'Groups - Email Content - Become Group Owner',
                'description' => 'Email content of the "Become Group Owner" notification. <a role="button" onclick="$Core.editMeta(\'groups_email_full_name_assigned_you_as_owner_of_group\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="groups_email_full_name_assigned_you_as_owner_of_group"></span>',
                'type' => '',
                'value' => '{_p var="groups_email_full_name_assigned_you_as_owner_of_group"}',
                'ordering' => 40,
                'group_id' => 'email'
            ],
            'groups_setting_subject_group_was_transfer_to_another_user' => [
                'info' => 'Groups - Email Subject - Group Was Transferred To Another User',
                'description' => 'Email subject of the "Group Was Transferred To Another User" notification. <a role="button" onclick="$Core.editMeta(\'email_your_group_title_transfer_to_another\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_your_group_title_transfer_to_another"></span>',
                'type' => '',
                'value' => '{_p var="email_your_group_title_transfer_to_another"}',
                'ordering' => 41,
                'group_id' => 'email'
            ],
            'groups_setting_content_group_was_transfer_to_another_user' => [
                'info' => 'Groups - Email Content - Group Was Transferred To Another User',
                'description' => 'Email content of the "Group Was Transferred To Another User" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_just_transfer_your_group_title_to_another_user_name\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_just_transfer_your_group_title_to_another_user_name"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_just_transfer_your_group_title_to_another_user_name"}',
                'ordering' => 42,
                'group_id' => 'email'
            ],
            'groups_setting_subject_group_approved_subject' => [
                'info' => 'Groups - Email Subject - Your Group Has Been Approved',
                'description' => 'Email subject of the "Your Group Has Been Approved" notification. <a role="button" onclick="$Core.editMeta(\'groups_group_approved_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="groups_group_approved_subject"></span>',
                'type' => '',
                'value' => '{_p var="groups_group_approved_subject"}',
                'ordering' => 43,
                'group_id' => 'email'
            ],
            'groups_setting_content_group_approved_content' => [
                'info' => 'Groups - Email Content - Your Group Has Been Approved',
                'description' => 'Email content of the "Your Group Has Been Approved" notification. <a role="button" onclick="$Core.editMeta(\'groups_group_approved_content\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="groups_group_approved_content"></span>',
                'type' => '',
                'value' => '{_p var="groups_group_approved_content"}',
                'ordering' => 44,
                'group_id' => 'email'
            ],
        ];
    }

    private function getVariableOfPhrase($sString)
    {
        return 'app_' . md5($sString);
    }

    public function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'pf_group_browse' => [
                'var_name' => 'pf_group_browse',
                'info' => 'Can browse groups?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    "1" => "1",
                    "2" => "1",
                    "3" => "1",
                    "4" => "1",
                    "5" => "0"
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_group_add_cover_photo' => [
                'var_name' => 'pf_group_add_cover_photo',
                'info' => 'Can add a cover photo on groups?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    "1" => "1",
                    "2" => "1",
                    "3" => "1",
                    "4" => "1",
                    "5" => "0"
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'can_edit_all_groups' => [
                'info' => 'Can edit all groups?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => 3
            ],
            'can_delete_all_groups' => [
                'info' => 'Can delete all groups?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => 4
            ],
            'can_approve_groups' => [
                'info' => 'Can approve groups?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => 5
            ],
            'pf_group_add' => [
                'var_name' => 'pf_group_add',
                'info' => 'Can add groups?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    "1" => "1",
                    "2" => "1",
                    "3" => "0",
                    "4" => "1",
                    "5" => "0"
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_group_max_upload_size' => [
                'var_name' => 'pf_group_max_upload_size',
                'info' => 'Max file size for upload files in kilobytes (kb). For unlimited add "0" without quotes.',
                'type' => Setting\Groups::TYPE_TEXT,
                'value' => 8192,
            ],
            'pf_group_approve_groups' => [
                'var_name' => 'pf_group_approve_groups',
                'info' => 'Groups must be approved first before they are displayed publicly?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => 0,
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'points_groups' => [
                'var_name' => 'points_groups',
                'info' => 'Activity points received when creating a new group.',
                'type' => Setting\Groups::TYPE_TEXT,
                'value' => 1
            ],
            'flood_control' => [
                'info' => 'Define how many minutes this user group should wait before they can add new group. Note: Setting it to "0" (without quotes) is default and users will not have to wait.',
                'type' => 'integer',
                'value' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0
                ]
            ],
            'can_feature_group' => [
                'var_name' => 'can_feature_group',
                'info' => 'Can feature a group?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ]
            ],
            'can_sponsor_groups' => [
                'var_name' => 'can_sponsor_groups',
                'info' => 'Can members of this user group mark a group as Sponsor without paying fee?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ]
            ],
            'can_purchase_sponsor_groups' => [
                'var_name' => 'can_purchase_sponsor_groups',
                'info' => 'Can members of this user group purchase a sponsored ad space?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ]
            ],
            'groups_sponsor_price' => [
                'var_name' => 'groups_sponsor_price',
                'info' => 'How much is the sponsor space worth for groups? This works in a CPM basis.',
                'description' => '',
                'type' => 'currency'
            ],
            'auto_publish_sponsored_item' => [
                'var_name' => 'auto_publish_sponsored_item',
                'info' => 'Auto publish sponsored item?',
                'description' => 'After the user has purchased a sponsored space, should the item be published right away? 
If set to No, the admin will have to approve each new purchased sponsored item space before it is shown in the site.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
            ],
            'max_groups_created' => [
                'var_name' => 'max_groups_created',
                'info' => 'The maximum number of groups that members of this user group can create. For unlimited add "0" without quotes',
                'type' => 'integer',
                'value' => 0,
                'ordering' => 20,
            ]
        ];
    }

    public function setComponent()
    {
        $this->component = [
            "block" => [
                "about" => "",
                "admin" => "",
                "category" => "",
                "events" => "",
                "members" => "",
                "menu" => "",
                "photo" => "",
                "profile" => "",
                "widget" => "",
                "cropme" => "",
                "related" => "",
                "featured" => "",
                "sponsored" => ""
            ],
            "controller" => [
                "index" => "groups.index",
                "add" => "groups.add",
                "all" => "groups.all",
                "view" => "groups.view",
                "profile" => "groups.profile"
            ]
        ];
    }

    public function setComponentBlock()
    {
        $this->component_block = [
            "Groups Likes/Members" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "members",
                "location" => "3",
                "is_active" => "1",
                "ordering" => "3"
            ],
            "Groups Info" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "about",
                "location" => "1",
                "is_active" => "1",
                "ordering" => "3"
            ],
            "Groups Mini Menu" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "menu",
                "location" => "1",
                "is_active" => "0",
                "ordering" => "4"
            ],
            "Groups Widget" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "widget",
                "location" => "1",
                "is_active" => "1",
                "ordering" => "5"
            ],
            "Groups" => [
                "type_id" => "0",
                "m_connection" => "profile.index",
                "component" => "profile",
                "location" => "1",
                "is_active" => "1",
                "ordering" => "4"
            ],
            "Groups Admin" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "admin",
                "location" => "3",
                "is_active" => "1",
                "ordering" => "6"
            ],
            "Categories" => [
                "type_id" => "0",
                "m_connection" => "groups.index",
                "component" => "category",
                "location" => "1",
                "is_active" => "1",
                "ordering" => "10"
            ],
            "Feed display" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "display",
                "location" => "2",
                "is_active" => "1",
                "ordering" => "10",
                "module_id" => "feed"
            ],
            "Group Events" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "events",
                "location" => "3",
                "is_active" => "1",
                "ordering" => "7"
            ],
            "Related Groups" => [
                "type_id" => "0",
                "m_connection" => "groups.view",
                "component" => "related",
                "location" => "1",
                "is_active" => "1",
                "ordering" => "8"
            ],
            'Featured Groups' => [
                'type_id' => '0',
                'm_connection' => 'groups.index',
                'component' => 'featured',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Sponsored Groups' => [
                'type_id' => '0',
                'm_connection' => 'groups.index',
                'component' => 'sponsored',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '2',
            ],
        ];
    }

    protected function setPhrase()
    {
        $this->phrase = $this->_app_phrases;
    }

    protected function setOthers()
    {
        $this->admincp_route = "/groups/admincp";
        $this->admincp_menu = [
            _p("Add New Category") => "groups.add-category",
            _p("Manage Categories") => "#",
            _p('Manage Integrated Items') => 'groups.integrate',
            _p("Convert old groups") => "groups.convert"
        ];
        $this->menu = [
            "phrase_var_name" => "menu_groups",
            "url" => "groups",
            "icon" => "users"
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/pages/'
        ];
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->_admin_cp_menu_ajax = false;
        $this->_apps_dir = 'core-groups';
        // database tables
        $this->database = [
            'PagesAdminTable',
            'PagesCategoryTable',
            'PagesClaimTable',
            'PagesFeedCommentTable',
            'PagesFeedTable',
            'PagesInviteTable',
            'PagesLoginTable',
            'PagesPermTable',
            'PagesSignupTable',
            'PagesTable',
            'PagesTextTable',
            'PagesTypeTable',
            'PagesUrlTable',
            'PagesWidgetTable',
            'PagesWidgetTextTable',
            'PagesMenuTable'
        ];
        $this->allow_remove_database = false; // do not allow user to remove database
    }
}
