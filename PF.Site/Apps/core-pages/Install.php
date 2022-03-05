<?php

namespace Apps\Core_Pages;

use Core\App;

class Install extends App\App
{
    private $_app_phrases = [

    ];

    public $store_id = 1896;

    protected function setId()
    {
        $this->id = 'Core_Pages';
    }

    protected function setAlias()
    {
        $this->alias = 'pages';
    }

    protected function setName()
    {
        $this->name = _p('pages_app');
    }

    protected function setVersion()
    {
        $this->version = '4.7.14';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.4';
    }

    protected function setSettings()
    {
        $this->settings = [
            'pages_default_item_privacy' => [
                'info' => 'Default Item Privacy',
                'description' => 'Set default item privacy when a page is created.',
                'type' => 'select',
                'options' => ['Anyone', 'Members Only', 'Admins Only'],
                'value' => 0,
                'ordering' => 1,
            ],
            'admin_in_charge_of_page_claims' => [
                'info' => 'Admin in Charge of Page Claims',
                'description' => 'Choose which admin should receive a mail when someone claims a page. Claiming a page is a user group setting, not every member is allowed to claim a page. To enable a user group to claim pages please go to <b>Manage User Groups</b>.',
                'type' => 'select',
                'options' => ['None', 'Admin'],
                'value' => 0,
                'ordering' => 2
            ],
            'pages_limit_per_category' => [
                'info' => 'Pages Limit Per Category',
                'description' => 'Define the limit of how many pages per category can be displayed when viewing All Pages page.',
                'type' => 'integer',
                'value' => 6,
                'ordering' => 1
            ],
            'pagination_at_search_page' => [
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
            'display_pages_profile_photo_within_gallery' => [
                'info' => 'Display pages profile photo within gallery',
                'description' => 'Disable this feature if you do not want to display pages profile photos within the photo gallery.',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => 3
            ],
            'display_pages_cover_photo_within_gallery' => [
                'info' => 'Display pages cover photo within gallery',
                'description' => 'Disable this feature if you do not want to display pages cover photos within the photo gallery.',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => 4
            ],
            'pages_setting_meta_description' => [
                'info' => 'Pages Meta Description',
                'description' => 'Meta description added to pages related to the Pages app. <a role="button" onclick="$Core.editMeta(\'seo_pages_meta_description\', true)">Click here</a> to edit meta description.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_pages_meta_description"></span>',
                'type' => '',
                'value' => '{_p var="seo_pages_meta_description"}',
                'ordering' => 5,
                'group_id' => 'seo'
            ],
            'pages_setting_meta_keywords' => [
                'info' => 'Pages Meta Keywords',
                'description' => 'Meta keywords that will be displayed on sections related to the Pages app. <a role="button" onclick="$Core.editMeta(\'seo_pages_meta_keywords\', true)">Click here</a> to edit meta keywords.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_pages_meta_keywords"></span>',
                'type' => '',
                'value' => '{_p var="seo_pages_meta_keywords"}',
                'ordering' => 6,
                'group_id' => 'seo'
            ],
            'pages_setting_subject_tagged_in_a_post_in_page' => [
                'info' => 'Pages - Email Subject - Someone Tagged You In A Post In Page',
                'description' => 'Email subject of the "Someone Tagged You In A Post In Page" notification. <a role="button" onclick="$Core.editMeta(\'pages_full_name_tagged_you_in_a_post_in_page_title_no_html\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="pages_full_name_tagged_you_in_a_post_in_page_title_no_html"></span>',
                'type' => '',
                'value' => '{_p var="pages_full_name_tagged_you_in_a_post_in_page_title_no_html"}',
                'ordering' => 7,
                'group_id' => 'email'
            ],
            'pages_setting_content_tagged_in_a_post_in_page' => [
                'info' => 'Pages - Email Content - Someone Tagged You In A Post In Page',
                'description' => 'Email content of the "Someone Tagged You In A Post In Page" notification. <a role="button" onclick="$Core.editMeta(\'pages_user_name_tagged_you_in_a_post_in_page_title_check_it_out\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="pages_user_name_tagged_you_in_a_post_in_page_title_check_it_out"></span>',
                'type' => '',
                'value' => '{_p var="pages_user_name_tagged_you_in_a_post_in_page_title_check_it_out"}',
                'ordering' => 8,
                'group_id' => 'email'
            ],
            'pages_setting_subject_membership_accepted' => [
                'info' => 'Pages - Email Subject - Membership Accepted',
                'description' => 'Email subject of the "Membership Accepted" notification. <a role="button" onclick="$Core.editMeta(\'membership_accepted_to_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="membership_accepted_to_title"></span>',
                'type' => '',
                'value' => '{_p var="membership_accepted_to_title"}',
                'ordering' => 9,
                'group_id' => 'email'
            ],
            'pages_setting_content_membership_accepted' => [
                'info' => 'Pages - Email Content - Membership Accepted',
                'description' => 'Email content of the "Membership Accepted" notification. <a role="button" onclick="$Core.editMeta(\'your_membership_to_the_page_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_membership_to_the_page_link"></span>',
                'type' => '',
                'value' => '{_p var="your_membership_to_the_page_link"}',
                'ordering' => 10,
                'group_id' => 'email'
            ],
            'pages_setting_subject_user_liked_page_for_owner_page' => [
                'info' => 'Pages - Email Subject (For Page Owner)- User Like Page',
                'description' => 'Email subject (For Page Owner) of the "User Like Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_page_title"}',
                'ordering' => 11,
                'group_id' => 'email'
            ],
            'pages_setting_content_user_liked_page_for_owner_page' => [
                'info' => 'Pages - Email Content (For Page Owner) - User Like Page',
                'description' => 'Email content (For Page Owner) of the "User Like Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_page\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_page"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_page"}',
                'ordering' => 12,
                'group_id' => 'email'
            ],
            'pages_setting_subject_email_posted_a_video_on_page_for_admins' => [
                'info' => 'Pages - Email Subject (For Page Admins) - Someone Posted A Video On Page',
                'description' => 'Email subject (For Page Admins) of the "Someone Posted A Video On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_video_on_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_video_on_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_video_on_page_title"}',
                'ordering' => 13,
                'group_id' => 'email'
            ],
            'pages_setting_content_email_posted_a_video_on_page_for_admins' => [
                'info' => 'Pages - Email Content (For Page Admins) - Someone Posted A Video On Page',
                'description' => 'Email content (For Page Admins) of the "Someone Posted A Video On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_video_on_page_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_video_on_page_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_video_on_page_link"}',
                'ordering' => 14,
                'group_id' => 'email'
            ],
            'pages_setting_subject_email_posted_a_video_on_page_for_owner_page' => [
                'info' => 'Pages - Email Subject (For Page Owner)- Someone Posted A Video On Page',
                'description' => 'Email subject (For Page Owner) of the "Someone Posted A Video On Page" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_posted_a_video_on_your_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_posted_a_video_on_your_page_title"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_posted_a_video_on_your_page_title"}',
                'ordering' => 13,
                'group_id' => 'email'
            ],
            'pages_setting_content_email_posted_a_video_on_page_for_owner_page' => [
                'info' => 'Pages - Email Content (For Page Owner) - Someone Posted A Video On Page',
                'description' => 'Email content (For Page Owner) of the "Someone Posted A Video On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_video_on_your_page_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_video_on_your_page_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_video_on_your_page_link"}',
                'ordering' => 14,
                'group_id' => 'email'
            ],
            'pages_setting_subject_liked_a_comment_on_the_page' => [
                'info' => 'Pages - Email Subject - Someone Liked A Comment On Page',
                'description' => 'Email subject of the "Someone Liked A Comment On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_a_comment_you_made_on_the_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_a_comment_you_made_on_the_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_a_comment_you_made_on_the_page_title"}',
                'ordering' => 15,
                'group_id' => 'email'
            ],
            'pages_setting_content_liked_a_comment_on_the_page' => [
                'info' => 'Pages - Email Content - Someone Liked A Comment On Page',
                'description' => 'Email content of the "Someone Liked A Comment On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_a_comment_you_made_on_the_page_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_a_comment_you_made_on_the_page_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_a_comment_you_made_on_the_page_title_to_view_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => 16,
                'group_id' => 'email'
            ],
            'pages_setting_subject_invite_friends_to_the_page' => [
                'info' => 'Pages - Email Subject - Invite Friends To The Page',
                'description' => 'Email subject of the "Invite Friends To The Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_sent_you_a_page_invitation\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_sent_you_a_page_invitation"></span>',
                'type' => '',
                'value' => '{_p var="full_name_sent_you_a_page_invitation"}',
                'ordering' => 17,
                'group_id' => 'email'
            ],
            'pages_setting_content_invite_friends_to_the_page_1' => [
                'info' => 'Pages - Email Content - Invite Friends To The Page Part 1',
                'description' => 'Email content of the "Invite Friends To The Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_invited_you_to_the_page_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_invited_you_to_the_page_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_invited_you_to_the_page_message"}',
                'ordering' => 18,
                'group_id' => 'email'
            ],
            'pages_setting_content_invite_friends_to_the_page_2' => [
                'info' => 'Pages - Email Content - Invite Friends To The Page Part 2',
                'description' => 'Email content of the "Invite Friends To The Page" notification. <a role="button" onclick="$Core.editMeta(\'to_view_this_page_click_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="to_view_this_page_click_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="to_view_this_page_click_the_link_below_a_href_link_link_a"}',
                'ordering' => 19,
                'group_id' => 'email'
            ],
            'pages_setting_content_invite_friends_email_personal_message' => [
                'info' => 'Pages - Email Content - Invite Personal Message',
                'description' => 'Email content of the "Invite Friends To The Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_the_following_personal_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_the_following_personal_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_the_following_personal_message"}',
                'ordering' => 20,
                'group_id' => 'email'
            ],
            'pages_setting_subject_invite_via_email_in_the_page' => [
                'info' => 'Pages - Email Subject - Invite Via Email In The Page',
                'description' => 'Email subject of the "Invite Via Email In The Page" email. <a role="button" onclick="$Core.editMeta(\'full_name_invited_you_to_the_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_invited_you_to_the_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_invited_you_to_the_page_title"}',
                'ordering' => 21,
                'group_id' => 'email'
            ],
            'pages_setting_content_invite_via_email_in_the_page' => [
                'info' => 'Pages - Email Content - Invite Via Email In The Page',
                'description' => 'Email content of the "Invite Via Email In The Page" email. <a role="button" onclick="$Core.editMeta(\'full_name_invited_you_to_the_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_invited_you_to_the_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_invited_you_to_the_title"}',
                'ordering' => 22,
                'group_id' => 'email'
            ],
            'pages_setting_subject_invite_friends_become_admin_page' => [
                'info' => 'Pages - Email Subject - Invite Friends Become Page Admin',
                'description' => 'Email subject of the "Invite Friends Become Page Admin" notification. <a role="button" onclick="$Core.editMeta(\'email_you_have_been_invited_to_become_an_admin_of_page_subject\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_you_have_been_invited_to_become_an_admin_of_page_subject"></span>',
                'type' => '',
                'value' => '{_p var="email_you_have_been_invited_to_become_an_admin_of_page_subject"}',
                'ordering' => 23,
                'group_id' => 'email'
            ],
            'pages_setting_content_invite_friends_become_admin_page' => [
                'info' => 'Pages - Email Content - Invite Friends Become Page Admin',
                'description' => 'Email content of the "Invite Friends Become Page Admin" notification. <a role="button" onclick="$Core.editMeta(\'email_you_have_been_invited_to_become_an_admin_of_page_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_you_have_been_invited_to_become_an_admin_of_page_message"></span>',
                'type' => '',
                'value' => '{_p var="email_you_have_been_invited_to_become_an_admin_of_page_message"}',
                'ordering' => 24,
                'group_id' => 'email'
            ],
            'pages_setting_subject_become_owner_page' => [
                'info' => 'Pages - Email Subject - Become Page Owner',
                'description' => 'Email subject of the "Become Page Owner" notification. <a role="button" onclick="$Core.editMeta(\'email_you_become_owner_of_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_you_become_owner_of_page_title"></span>',
                'type' => '',
                'value' => '{_p var="email_you_become_owner_of_page_title"}',
                'ordering' => 25,
                'group_id' => 'email'
            ],
            'pages_setting_content_become_owner_page' => [
                'info' => 'Pages - Email Content - Become Page Owner',
                'description' => 'Email content of the "Become Page Owner" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_assigned_you_as_owner_of_page\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_assigned_you_as_owner_of_page"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_assigned_you_as_owner_of_page"}',
                'ordering' => 26,
                'group_id' => 'email'
            ],
            'pages_setting_subject_page_was_transfer_to_another_user' => [
                'info' => 'Pages - Email Subject - Page Was Transferred To Another User',
                'description' => 'Email subject of the "Page Was Transferred To Another User" notification. <a role="button" onclick="$Core.editMeta(\'email_your_page_title_transfer_to_another\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_your_page_title_transfer_to_another"></span>',
                'type' => '',
                'value' => '{_p var="email_your_page_title_transfer_to_another"}',
                'ordering' => 27,
                'group_id' => 'email'
            ],
            'pages_setting_page_page_was_transfer_to_another_user' => [
                'info' => 'Pages - Email Content - Page Was Transferred To Another User',
                'description' => 'Email content of the "Page Was Transferred To Another User" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_just_transfer_your_page_title_to_another_user_name\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_just_transfer_your_page_title_to_another_user_name"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_just_transfer_your_page_title_to_another_user_name"}',
                'ordering' => 28,
                'group_id' => 'email'
            ],
            'pages_setting_subject_wrote_a_comment_on_page' => [
                'info' => 'Pages - Email Subject (For Page Admins) - Someone Wrote A Comment On Page',
                'description' => 'Email subject (For Page Admins) of the "Someone Wrote A Comment On Page" notification. <a role="button" onclick="$Core.editMeta(\'email_full_name_wrote_a_comment_on_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_full_name_wrote_a_comment_on_page_title"></span>',
                'type' => '',
                'value' => '{_p var="email_full_name_wrote_a_comment_on_page_title"}',
                'ordering' => 29,
                'group_id' => 'email'
            ],
            'pages_setting_content_wrote_a_comment_on_page' => [
                'info' => 'Pages - Email Content (For Page Admins) - Someone Wrote A Comment On Page',
                'description' => 'Email content (For Page Admins) of the "Someone Wrote A Comment On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_on_page_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_wrote_a_comment_on_page_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_on_page_link"}',
                'ordering' => 30,
                'group_id' => 'email'
            ],
            'pages_setting_subject_wrote_a_comment_on_page_for_owner' => [
                'info' => 'Pages - Email Subject (For Page Owner) - Someone Wrote A Comment On Page',
                'description' => 'Email subject (For Page Owner) of the "Someone Wrote A Comment On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_on_your_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_wrote_a_comment_on_your_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_on_your_page_title"}',
                'ordering' => 31,
                'group_id' => 'email'
            ],
            'pages_setting_content_wrote_a_comment_on_page_for_owner' => [
                'info' => 'Pages - Email Content (For Page Owner) - Someone Wrote A Comment On Page',
                'description' => 'Email content (For Page Owner) of the "Someone Wrote A Comment On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_wrote_a_comment_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_link"}',
                'ordering' => 32,
                'group_id' => 'email'
            ],
            'pages_setting_subject_email_post_some_images_on_page_for_admins' => [
                'info' => 'Pages - Email Subject (For Page Admins) - Someone Posted Some Images On Page',
                'description' => 'Email subject (For Page Admins) of the "Someone Posted Some Images On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_post_some_images_on_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_images_on_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_images_on_page_title"}',
                'ordering' => 33,
                'group_id' => 'email'
            ],
            'pages_setting_content_email_post_some_images_on_page_for_admins' => [
                'info' => 'Pages - Email Content (For Page Admins) - Someone Posted Some Images On Page',
                'description' => 'Email content (For Page Admins) of the "Someone Posted Some Images On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_post_some_images_on_page_title_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_images_on_page_title_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_images_on_page_title_link"}',
                'ordering' => 34,
                'group_id' => 'email'
            ],
            'pages_setting_subject_email_post_some_images_on_page_for_owner_page' => [
                'info' => 'Pages - Email Subject (For Page Owner) - Someone Posted Some Images On Page',
                'description' => 'Email subject (For Page Owner) of the "Someone Posted Some Images On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_post_some_images_on_your_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_images_on_your_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_images_on_your_page_title"}',
                'ordering' => 35,
                'group_id' => 'email'
            ],
            'pages_setting_content_email_post_some_images_on_page_for_owner_page' => [
                'info' => 'Pages - Email Content (For Page Owner) - Someone Posted Some Images On Page',
                'description' => 'Email content (For Page Owner) of the "Someone Posted Some Images On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_post_some_images_on_your_page_title_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_images_on_your_page_title_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_images_on_your_page_title_link"}',
                'ordering' => 36,
                'group_id' => 'email'
            ],
            'pages_setting_subject_email_posted_a_link_on_page_for_admins' => [
                'info' => 'Pages - Email Subject (For Page Admins) - Someone Posted A Link On Page',
                'description' => 'Email subject (For Page Admins) of the "Someone Posted A Link On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_link_on_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_link_on_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_link_on_page_title"}',
                'ordering' => 37,
                'group_id' => 'email'
            ],
            'pages_setting_content_email_posted_a_link_on_page_for_admins' => [
                'info' => 'Pages - Email Content (For Page Admins) - Someone Posted A Link On Page',
                'description' => 'Email content (For Page Admins) of the "Someone Posted A Link On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_link_on_page_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_link_on_page_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_link_on_page_link"}',
                'ordering' => 38,
                'group_id' => 'email'
            ],
            'pages_setting_subject_email_posted_a_link_on_page_for_owner_page' => [
                'info' => 'Pages - Email Subject (For Page Owner) - Someone Posted A Link On Page',
                'description' => 'Email subject (For Page Owner) of the "Someone Posted A Link On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_link_on_your_page_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_link_on_your_page_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_link_on_your_page_title"}',
                'ordering' => 39,
                'group_id' => 'email'
            ],
            'pages_setting_content_email_posted_a_link_on_page_for_owner_page' => [
                'info' => 'Pages - Email Content (For Page Owner) - Someone Posted A Link On Page',
                'description' => 'Email content (For Page Owner) of the "Someone Posted A Link On Page" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_link_on_your_page_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_link_on_your_page_link"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_link_on_your_page_link"}',
                'ordering' => 40,
                'group_id' => 'email'
            ],
        ];
    }

    protected function setUserGroupSettings()
    {
        $iOrdering = 0;
        $this->user_group_settings = [
            'can_view_browse_pages' => [
                'info' => 'Can browse and view pages?',
                'type' => 'boolean',
                'value' => 1,
                'ordering' => ++$iOrdering
            ],
            'can_add_new_pages' => [
                'info' => 'Can create new pages?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'can_edit_all_pages' => [
                'info' => 'Can edit all pages?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'can_delete_all_pages' => [
                'info' => 'Can delete all pages?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'can_approve_pages' => [
                'info' => 'Can approve pages?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'approve_pages' => [
                'info' => 'Pages must be approved first before they are displayed publicly?',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => ++$iOrdering
            ],
            'can_claim_page' => [
                'info' => 'Can members of this user group contact the site to claim a page?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'can_add_cover_photo_pages' => [
                'info' => 'Can add a cover photo on pages?',
                'type' => 'boolean',
                'value' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'max_upload_size_pages' => [
                'info' => 'Max file size for photos upload in kilobytes (kb). (1024 kb = 1 mb). For unlimited add "0" without quotes.',
                'type' => 'integer',
                'value' => [
                    1 => 8192,
                    2 => 8192,
                    3 => 0,
                    4 => 8192,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'points_pages' => [
                'info' => 'Activity points received when creating a new page.',
                'type' => 'integer',
                'value' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 1,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'pages_flood_control' => [
                'info' => 'Define how many minutes this user group should wait before they can add new page. Note: Set to 0 if there should be no limit.',
                'type' => 'integer',
                'value' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 0
                ],
                'ordering' => ++$iOrdering
            ],
            'can_feature_page' => [
                'var_name' => 'can_feature_page',
                'info' => 'Can feature a page?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => ++$iOrdering
            ],
            'can_sponsor_pages' => [
                'var_name' => 'can_sponsor_pages',
                'info' => 'Can members of this user group mark a page as Sponsor without paying fee?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => ++$iOrdering
            ],
            'can_purchase_sponsor_pages' => [
                'var_name' => 'can_purchase_sponsor_pages',
                'info' => 'Can members of this user group purchase a sponsored ad space?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => ++$iOrdering
            ],
            'pages_sponsor_price' => [
                'var_name' => 'pages_sponsor_price',
                'info' => 'How much is the sponsor space worth for pages? This works in a CPM basis.',
                'description' => '',
                'type' => 'currency',
                'ordering' => ++$iOrdering
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
            'max_pages_created' => [
                'var_name' => 'max_pages_created',
                'info' => 'The maximum number of pages that members of this user group can create. For unlimited add "0" without quotes',
                'type' => 'integer',
                'value' => 0,
                'ordering' => ++$iOrdering,
            ],
        ];
    }

    protected function setComponent()
    {
        $this->component = [
            'block' => [
                'photo' => '',
                'admin' => '',
                'widget' => '',
                'profile' => '',
                'category' => '',
                'menu' => '',
                'like' => '',
                'people-also-like' => '',
                'featured' => '',
                'sponsored' => ''
            ],
            'controller' => [
                'index' => 'pages.index',
                'view' => 'pages.view',
                'profile' => 'pages.profile',
            ]
        ];
    }

    protected function setComponentBlock()
    {
        $this->component_block = [
            'People Also Like' => [
                'type_id' => '0',
                'm_connection' => 'pages.view',
                'component' => 'people-also-like',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Widgets' => [
                'type_id' => '0',
                'm_connection' => 'pages.view',
                'component' => 'widget',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '2',
            ],
            'Pages Likes/Members' => [
                'type_id' => '0',
                'm_connection' => 'pages.view',
                'component' => 'like',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '3',
            ],
            'Pages Mini Menu' => [
                'type_id' => '0',
                'm_connection' => 'pages.view',
                'component' => 'menu',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '4',
            ],
            'Pages' => [
                'type_id' => '0',
                'm_connection' => 'profile.index',
                'component' => 'profile',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '4',
            ],
            'Page Admins' => [
                'type_id' => '0',
                'm_connection' => 'pages.view',
                'component' => 'admin',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '5',
            ],
            'Categories' => [
                'type_id' => '0',
                'm_connection' => 'pages.index',
                'component' => 'category',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '10',
            ],
            'Featured Pages' => [
                'type_id' => '0',
                'm_connection' => 'pages.index',
                'component' => 'featured',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Sponsored Pages' => [
                'type_id' => '0',
                'm_connection' => 'pages.index',
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
        $this->menu = [
            'phrase_var_name' => 'menu_pages',
            'url' => 'pages',
            "icon" => "flag"
        ];
        $this->_apps_dir = 'core-pages';
        $this->_admin_cp_menu_ajax = false;

        // brand
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';

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

        $this->admincp_route = '/pages/admincp';
        $this->admincp_menu = [
            _p('manage_integrated_items') => 'pages.integrate',
            _p('add_new_category') => 'pages.add',
            _p('manage_categories') => '#',
            _p('manage_claims') => 'pages.claim'
        ];
        $this->_admin_cp_menu_ajax = false;

        $this->_writable_dirs = [
            'PF.Base/file/pic/pages/'
        ];

        $this->allow_remove_database = false; // do not allow user to remove database
    }
}
