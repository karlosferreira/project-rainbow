<?php
namespace Apps\Core_Events;

use Core\App;

/**
 * Class Install
 * @author  phpFox
 * @package Apps\Core_Events
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    public $store_id = 2005;

    protected function setId()
    {
        $this->id = 'Core_Events';
    }

    protected function setAlias()
    {
        $this->alias = 'event';
    }

    protected function setName()
    {
        $this->name = _p('Events');
    }

    protected function setVersion()
    {
        $this->version = '4.8.1';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.1';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'event_paging_mode' => [
                'var_name' => 'event_paging_mode',
                'info' => 'Pagination Style',
                'description' => 'Select Pagination Style at Search Page',
                'type' => 'select',
                'value' => 'loadmore',
                'options' => [
                    'loadmore' => 'Scrolling down to Load More items',
                    'next_prev' => 'Use Next and Pre buttons',
                    'pagination' => 'Use Pagination with page number'
                ],
                'ordering' => $iIndex++
            ],
            'event_default_sort_time' => [
                'var_name' => 'event_default_sort_time',
                'info' => 'Default time to sort events',
                'description' => 'Select default time time to sort events in listing events page (Except Pending page, My page and Profile page) and some blocks',
                'type' => 'select',
                'value' => 'all-time',
                'options' => [
                    'all-time' => 'All Time',
                    'this-month' => 'This Month',
                    'this-week' => 'This week',
                    'today' => 'Today',
                    'upcoming' => 'Upcoming',
                    'ongoing' => 'Ongoing'
                ],
                'ordering' => $iIndex++
            ],
            'event_meta_description' => [
                'var_name' => 'event_meta_description',
                'info' => 'Events Meta Description',
                'description' => 'Meta description added to pages related to the Events app. <a role="button" onclick="$Core.editMeta(\'seo_event_meta_description\', true)">Click here</a> to edit meta description.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_event_meta_description"></span>',
                'type' => '',
                'value' => '{_p var=\'seo_event_meta_description\'}',
                'group_id' => 'seo',
                'ordering' => $iIndex++,
            ],
            'event_meta_keywords' => [
                'var_name' => 'event_meta_keywords',
                'info' => 'Events Meta Keywords',
                'description' => 'Meta keywords that will be displayed on sections related to the Events app. <a role="button" onclick="$Core.editMeta(\'seo_event_meta_keywords\', true)">Click here</a> to edit meta keywords.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_event_meta_keywords"></span>',
                'type' => '',
                'value' => '{_p var=\'seo_event_meta_keywords\'}',
                'group_id' => 'seo',
                'ordering' => $iIndex++,
            ],
            'event_basic_information_time' => [
                'var_name' => 'event_basic_information_time',
                'info' => 'Event Basic Information Time Stamp',
                'description' => 'This is the time stamp that is used when viewing an event.',
                'type' => 'string',
                'value' => 'l, F j, Y g:i a',
                'ordering' => $iIndex++,
            ],
            'event_time_format' => [
                'var_name' => 'event_time_format',
                'info' => 'Time format',
                'type' => 'select',
                'value' => 'g:i a',
                'options' => [
                    'g:i a' => '12-hour format',
                    'G:i' => '24-hour format',
                ],
                'ordering' => $iIndex++
            ],
            'event_display_event_created_in_group' => [
                'var_name' => 'event_display_event_created_in_group',
                'info' => 'Display events which created in Group to the All Events page at the Events app',
                'description' => 'Enable to display all public events to the both Events page in group detail and All Events page in Events app. Disable to display events created by an users to the both Events page in group detail and My Events page of this user in Events app and nobody can see these events in Events app but owner.',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => $iIndex++,
            ],
            'event_display_event_created_in_page' => [
                'var_name' => 'event_display_event_created_in_page',
                'info' => 'Display events which created in Page to the All Events page at the Events app',
                'description' => 'Enable to display all public events to the both Events page in page detail and All Events page in Events app. Disable to display events created by an users to the both Events page in page detail and My Events page of this user in Events app and nobody can see these events in Events app but owner.',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => $iIndex++,
            ],
            'event_allow_create_feed_when_add_new_item' => [
                'var_name' => 'event_allow_posting_on_main_feed',
                'info' => 'Allow posting on Main Feed',
                'description' => 'Allow posting on Main feed when adding a new event.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => $iIndex++
            ],
            'event_paging_mode_map_view' => [
                'var_name' => 'event_paging_mode_map_view',
                'info' => 'Pagination Style for Map view',
                'description' => 'Select Pagination Style at Map view page',
                'type' => 'select',
                'value' => 'next_prev',
                'options' => [
                    'next_prev' => 'Use Next and Pre buttons',
                    'pagination' => 'Use Pagination with page number'
                ],
                'ordering' => $iIndex++
            ],
            'event_allow_create_recurring_event' => [
                'var_name' => 'event_allow_create_recurring_event',
                'info' => 'Allow creating recurring event',
                'description' => 'Allow creating recurring event when adding a new event.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => $iIndex++
            ],
            'event_max_instance_repeat_event' => [
                'var_name' => 'event_max_instance_repeat_event',
                'info' => 'Maximum instances of each repeat events',
                'description' => 'Maximum instances of each repeat events',
                'type' => 'integer',
                'value' => '50',
                'ordering' => $iIndex++
            ],
            'events_setting_subject_invite_friends_to_the_event' => [
                'info' => 'Events - Email Subject - Invite Friends To the Event',
                'description' => 'Email subject of the "Invite Friends To the Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_invited_you_to_the_event_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_invited_you_to_the_event_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_invited_you_to_the_event_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_invite_friends_to_the_event' => [
                'info' => 'Events - Email Content - Invite Friends To the Event',
                'description' => 'Email Content of the "Invite Friends To the Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_invited_you_to_the_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_invited_you_to_the_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_invited_you_to_the_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_invite_friends_email_personal_message' => [
                'info' => 'Events - Email Content - Invite Personal Message',
                'description' => 'Email Content of the "Invite Friends To the Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_the_following_personal_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_the_following_personal_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_the_following_personal_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_event_was_approved' => [
                'info' => 'Events - Email Subject - Event Was Approved',
                'description' => 'Email subject of the "Event Was Approved" notification. <a role="button" onclick="$Core.editMeta(\'your_event_has_been_approved_on_site_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_event_has_been_approved_on_site_title"></span>',
                'type' => '',
                'value' => '{_p var="your_event_has_been_approved_on_site_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_event_was_approved' => [
                'info' => 'Events - Email Content - Event Was Approved',
                'description' => 'Email Content of the "Event Was Approved" notification. <a role="button" onclick="$Core.editMeta(\'your_event_has_been_approved_on_site_title_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_event_has_been_approved_on_site_title_link"></span>',
                'type' => '',
                'value' => '{_p var="your_event_has_been_approved_on_site_title_link"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_liked_a_comment_on_the_event' => [
                'info' => 'Events - Email Subject - Someone Liked A Comment On Event',
                'description' => 'Email subject of the "Someone Liked A Comment On Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_a_comment_you_posted_on_the_event_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_a_comment_you_posted_on_the_event_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_a_comment_you_posted_on_the_event_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_liked_a_comment_on_the_event' => [
                'info' => 'Events - Email Content - Someone Liked A Comment On Event',
                'description' => 'Email Content of the "Someone Liked A Comment On Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_comment_message_event\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_comment_message_event"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_comment_message_event"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_liked_a_event' => [
                'info' => 'Events - Email Subject - Someone Liked A Event',
                'description' => 'Email subject of the "Someone Liked A Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_event_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_event_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_event_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_liked_a_event' => [
                'info' => 'Events - Email Content - Someone Liked A Event',
                'description' => 'Email Content of the "Someone Liked A Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_event_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_event_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_event_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_added_photos_on_the_event_for_owner' => [
                'info' => 'Events - Email Subject (For Event Owner) - Someone Added Photos On The Event',
                'description' => 'Email subject (For Event Owner) of the "Someone Added Photos On The Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_photo_s_on_your_event_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_photo_s_on_your_event_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_photo_s_on_your_event_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_added_photos_on_the_event_for_owner' => [
                'info' => 'Events - Email Content (For Event Owner) - Someone Added Photos On The Event',
                'description' => 'Email Content (For Event Owner) of the "Someone Added Photos On The Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_photo_s_on_your_event_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_photo_s_on_your_event_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_photo_s_on_your_event_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_added_photos_on_gender_the_event' => [
                'info' => 'Events - Email Subject - Event Owner Added Photos On Their Own Event',
                'description' => 'Email subject of the "Event Owner Added Photos On Their Own Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_photo_s_on_gender_own_event_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_photo_s_on_gender_own_event_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_photo_s_on_gender_own_event_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_added_photos_on_gender_the_event' => [
                'info' => 'Events - Email Content - Event Owner Added Photos On Their Own Event',
                'description' => 'Email Content of the "Event Owner Added Photos On Their Own Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_photo_s_on_gender_own_event_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_photo_s_on_gender_own_event_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_photo_s_on_gender_own_event_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_added_photos_on_the_event' => [
                'info' => 'Events - Email Subject - Someone Added Photos On The Event',
                'description' => 'Email subject of the "Someone Added Photos On The Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_photo_s_on_event_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_photo_s_on_event_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_photo_s_on_event_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_added_photos_on_the_event' => [
                'info' => 'Events - Email Content - Someone Added Photos On The Event',
                'description' => 'Email Content of the "Someone Added Photos On The Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_added_photo_s_on_event_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_added_photo_s_on_event_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_added_photo_s_on_event_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_full_name_wrote_a_comment_on_your_event_title' => [
                'info' => 'Events - Email Subject (For Event Owner) - Someone Posted A Status In Event Feed',
                'description' => 'Email subject (For Event Owner) of the "Someone Posted A Status In Event Feed" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_on_your_event_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_wrote_a_comment_on_your_event_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_on_your_event_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_full_name_wrote_a_comment_on_your_event_message' => [
                'info' => 'Events - Email Content (For Event Owner) - Someone Posted A Status In Event Feed',
                'description' => 'Email Content (For Event Owner) of the "Someone Posted A Status In Event Feed" notification. <a role="button" onclick="$Core.editMeta(\'full_name_wrote_a_comment_on_your_event_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_wrote_a_comment_on_your_event_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_wrote_a_comment_on_your_event_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_full_name_wrote_a_comment_on_gender_event_title' => [
                'info' => 'Events - Email Subject - Event Owner Posted A Status In Event Feed',
                'description' => 'Email subject of the "Event Owner Posted A Status In Event Feed" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_own_event_title_email\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_own_event_title_email"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_own_event_title_email"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_full_name_wrote_a_comment_on_gender_event_message' => [
                'info' => 'Events - Email Content - Event Owner Posted A Status In Event Feed',
                'description' => 'Email Content of the "Event Owner Posted A Status In Event Feed" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_own_event_title_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_own_event_title_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_own_event_title_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_full_name_wrote_a_comment_on_event_title' => [
                'info' => 'Events - Email Subject - Someone Posted A Status In Event Feed',
                'description' => 'Email subject of the "Someone Posted A Status In Event Feed" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_event_title_email\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_event_title_email"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_event_title_email"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_full_name_wrote_a_comment_on_event_message' => [
                'info' => 'Events - Email Content - Someone Posted A Status In Event Feed',
                'description' => 'Email Content of the "Someone Posted A Status In Event Feed" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_event_title_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_event_title_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_event_title_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_full_name_changed_content_on_gender_own_event_title_email' => [
                'info' => 'Events - Email Subject - Event Owner Changed Content On Their Owner Event',
                'description' => 'Email subject of the "Event Owner Changed Content On Their Owner Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_changed_content_on_gender_own_event_title_email\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_changed_content_on_gender_own_event_title_email"></span>',
                'type' => '',
                'value' => '{_p var="full_name_changed_content_on_gender_own_event_title_email"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_full_name_changed_content_on_gender_own_event_title_message' => [
                'info' => 'Events - Email Content - Event Owner Changed Content On Their Owner Event',
                'description' => 'Email Content of the "Event Owner Changed Content On Their Owner Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_changed_content_on_gender_own_event_title_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_changed_content_on_gender_own_event_title_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_changed_content_on_gender_own_event_title_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_full_name_changed_content_on_event_title_email' => [
                'info' => 'Events - Email Subject - SomeOne Changed Content On Event',
                'description' => 'Email subject of the "SomeOne Changed Content On Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_changed_content_on_event_title_email\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_changed_content_on_event_title_email"></span>',
                'type' => '',
                'value' => '{_p var="full_name_changed_content_on_event_title_email"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_full_name_changed_content_on_event_title_message' => [
                'info' => 'Events - Email Content - SomeOne Changed Content On Event',
                'description' => 'Email Content of the "SomeOne Changed Content On Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_changed_content_on_event_title_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_changed_content_on_event_title_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_changed_content_on_event_title_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_subject_full_name_changed_content_on_your_event_title_email' => [
                'info' => 'Events - Email Subject - SomeOne Changed Content On Your Event',
                'description' => 'Email subject of the "SomeOne Changed Content On Your Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_changed_content_on_your_event_title_email\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_changed_content_on_your_event_title_email"></span>',
                'type' => '',
                'value' => '{_p var="full_name_changed_content_on_your_event_title_email"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'events_setting_content_full_name_changed_content_on_your_event_title_message' => [
                'info' => 'Events - Email Content - SomeOne Changed Content On Your Event',
                'description' => 'Email Content of the "SomeOne Changed Content On Your Event" notification. <a role="button" onclick="$Core.editMeta(\'full_name_changed_content_on_your_event_title_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_changed_content_on_your_event_title_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_changed_content_on_your_event_title_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {
        $iIndex = 1;
        $this->user_group_settings = [
            'can_access_event' => [
                'var_name' => 'can_access_event',
                'info' => 'Can browse and view the event module?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '1',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'can_create_event' => [
                'var_name' => 'can_create_event',
                'info' => 'Can create an event?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'can_edit_own_event' => [
                'var_name' => 'can_edit_own_event',
                'info' => 'Can edit own event?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => $iIndex++,
            ],
            'can_edit_other_event' => [
                'var_name' => 'can_edit_other_event',
                'info' => 'Can edit all events?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'can_delete_own_event' => [
                'var_name' => 'can_delete_own_event',
                'info' => 'Can delete own event?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => $iIndex++,
            ],
            'can_delete_other_event' => [
                'var_name' => 'can_delete_other_event',
                'info' => 'Can delete all events?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'max_upload_size_event' => [
                'var_name' => 'max_upload_size_event',
                'info' => 'Max file size for event photos in kilobytes (kb). (1024 kb = 1 mb) ',
                'description' => 'For unlimited add "0" without quotes.',
                'type' => 'integer',
                'value' => [
                    '1' => '8192',
                    '2' => '8192',
                    '3' => '8192',
                    '4' => '8192',
                    '5' => '8192'
                ],
                'ordering' => $iIndex++,
            ],
            'can_post_comment_on_event' => [
                'var_name' => 'can_post_comment_on_event',
                'info' => 'Can post comments on events?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => $iIndex++,
            ],
            'can_approve_events' => [
                'var_name' => 'can_approve_events',
                'info' => 'Can approve events?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'can_feature_events' => [
                'var_name' => 'can_feature_events',
                'info' => 'Can feature events?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'event_must_be_approved' => [
                'var_name' => 'event_must_be_approved',
                'info' => 'Events must be approved first before they are displayed publicly?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '1',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'total_mass_emails_per_hour' => [
                'var_name' => 'total_mass_emails_per_hour',
                'info' => 'Define how long this user group must wait until they are allowed to send out another mass email.',
                'description' => '',
                'type' => 'integer',
                'value' => [
                    '1' => '0',
                    '2' => '60',
                    '3' => '60',
                    '4' => '0',
                    '5' => '60'
                ],
                'ordering' => $iIndex++,
            ],
            'can_mass_mail_own_members' => [
                'var_name' => 'can_mass_mail_own_members',
                'info' => 'Can mass email own event guests?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => $iIndex++,
            ],
            'can_sponsor_event' => [
                'var_name' => 'can_sponsor_event',
                'info' => 'Can members of this user group mark a event as Sponsor without paying fee?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'can_purchase_sponsor' => [
                'var_name' => 'can_purchase_sponsor',
                'info' => 'Can members of this user group purchase a sponsored ad space for their items?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'event_sponsor_price' => [
                'var_name' => 'event_sponsor_price',
                'info' => 'How much is the sponsor space worth for events? This works in a CPM basis.',
                'description' => '',
                'type' => 'currency',
                'value' => ['USD' => 0,],
                'ordering' => $iIndex++,
            ],
            'auto_publish_sponsored_item' => [
                'var_name' => 'auto_publish_sponsored_item',
                'info' => 'Auto publish sponsored item?',
                'description' => 'After the user has purchased a sponsored space, should the item be published right away? 
If set to false, the admin will have to approve each new purchased sponsored item space before it is shown in the site.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'flood_control_events' => [
                'var_name' => 'flood_control_events',
                'info' => 'How many minutes should a user wait before they can create another event? ',
                'description' => 'Note: Setting it to "0" (without quotes) is default and users will not have to wait.',
                'type' => 'integer',
                'value' => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'points_event' => [
                'var_name' => 'points_event',
                'info' => 'How many points does the user get when they add a new event?',
                'type' => 'integer',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => $iIndex++,
            ],
            'max_events_created' => [
                'var_name' => 'max_events_created',
                'info' => 'Maximum number of event can create? For unlimited add "0" without quotes',
                'type' => 'integer',
                'value' => [
                    "1" => "0",
                    "2" => "0",
                    "3" => "0",
                    "4" => "10",
                    "5" => "0"
                ],
                'ordering' => $iIndex++,
            ]
        ];
        unset($iIndex);
    }

    protected function setComponent()
    {
        $this->component = [
            'block' => [
                'attending' => '',
                'category' => '',
                'menu' => '',
                'rsvp' => '',
                'profile' => '',
                'info' => '',
                'sponsored' => '',
                'list' => '',
                'invite' => '',
                'featured' => '',
                'suggestion' => ''
            ],
            'controller' => [
                'view' => 'event.view',
                'index' => 'event.index',
                'profile' => 'event.profile'
            ]
        ];
    }

    protected function setComponentBlock()
    {
        $this->component_block = [
            'Map View' => [
                'type_id' => '0',
                'm_connection' => 'event.index',
                'component' => 'gmap-block',
                'module_id' => 'core',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Category' => [
                'type_id' => '0',
                'm_connection' => 'event.index',
                'component' => 'category',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '2',
            ],
            'Birthday' => [
                'type_id' => '0',
                'm_connection' => 'event.index',
                'module_id' => 'friend',
                'component' => 'birthday',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Invite' => [
                'type_id' => '0',
                'm_connection' => 'event.index',
                'component' => 'invite',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '2',
            ],
            'Featured' => [
                'type_id' => '0',
                'm_connection' => 'event.index',
                'component' => 'featured',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '3',
            ],
            'Sponsored' => [
                'type_id' => '0',
                'm_connection' => 'event.index',
                'component' => 'sponsored',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '4',
            ],
            'Event Information' => [
                'type_id' => '0',
                'm_connection' => 'event.view',
                'component' => 'info',
                'location' => '4',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Activity Feed' => [
                'type_id' => '0',
                'm_connection' => 'event.view',
                'module_id' => 'feed',
                'component' => 'display',
                'location' => '4',
                'is_active' => '1',
                'ordering' => '3',
            ],
            'Suggestion Events' => [
                'type_id' => '0',
                'm_connection' => 'event.view',
                'component' => 'suggestion',
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
        $this->admincp_route = '/event/admincp';
        $this->admincp_menu = [
            'Manage Categories' => '#'
        ];
        $this->admincp_action_menu = [
            '/admincp/event/add' => 'Add New Category'
        ];
        $this->map = [];
        $this->menu = [
            'phrase_var_name' => 'menu_event',
            'url' => 'event',
            'icon' => 'calendar'
        ];
        $this->database = [
            'Event',
            'Event_Category',
            'Event_Category_Data',
            'Event_Feed',
            'Event_Feed_Comment',
            'Event_Invite',
            'Event_Text'
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/event/'
        ];
        $this->_apps_dir = 'core-events';
        $this->_admin_cp_menu_ajax = false;
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
    }
}