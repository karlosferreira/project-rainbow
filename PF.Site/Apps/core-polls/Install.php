<?php

namespace Apps\Core_Polls;

use Core\App;
use Phpfox;

/**
 * Class Install
 * @package Apps\Core_Polls
 */
class Install extends App\App
{
    public $store_id = 1904;
    private $_app_phrases = [

    ];

    protected function setId()
    {
        $this->id = 'Core_Polls';
    }

    protected function setAlias()
    {
        $this->alias = 'poll';
    }

    protected function setName()
    {
        $this->name = _p('Polls');
    }

    protected function setVersion()
    {
        $this->version = '4.7.4';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.7.1';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'is_image_required' => [
                'var_name' => 'is_image_required',
                'info' => 'Is Image Required',
                'description' => 'If set to true, users will have to upload an image with every poll they post. 
                By default is set to false, so they don\'t need to upload an image with their polls.',
                'type' => 'boolean',
                'value' => 0,
                'ordering' => $iIndex++
            ],
            'poll_paging_mode' => [
                'var_name' => 'poll_paging_mode',
                'info' => 'Pagination Style',
                'description' => 'Select Pagination Style at Search Page.',
                'type' => 'select',
                'value' => 'loadmore',
                'options' => [
                    'loadmore' => 'Scrolling down to Load More items',
                    'next_prev' => 'Use Next and Pre buttons',
                    'pagination' => 'Use Pagination with page number'
                ],
                'ordering' => $iIndex++
            ],
            'poll_meta_description' => [
                'var_name' => 'poll_meta_description',
                'info' => 'Poll Meta Description',
                'description' => 'Meta description added to pages related to the Polls app. <a role="button" onclick="$Core.editMeta(\'seo_poll_meta_description\', true)">Click here</a> to edit meta description.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_poll_meta_description"></span>',
                'type' => '',
                'value' => '{_p var=\'seo_poll_meta_description\'}',
                'group_id' => 'seo',
                'ordering' => $iIndex++
            ],
            'poll_setting_meta_keywords' => [
                'var_name' => 'poll_setting_meta_keywords',
                'info' => 'Poll Meta Keywords',
                'description' => 'Meta keywords that will be displayed on sections related to the Polls app. <a role="button" onclick="$Core.editMeta(\'seo_poll_meta_keywords\', true)">Click here</a> to edit meta keywords.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_poll_meta_keywords"></span>',
                'type' => '',
                'value' => '{_p var=\'seo_poll_meta_keywords\'}',
                'group_id' => 'seo',
                'ordering' => $iIndex++
            ],
            'poll_allow_create_feed_when_add_new_item' => [
                'var_name' => 'poll_allow_posting_on_main_feed',
                'info' => 'Allow posting on Main Feed',
                'description' => 'Allow posting on Main feed when adding a new poll.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => $iIndex++
            ],
            'display_polls_created_in_page' => [
                'var_name' => 'display_polls_created_in_page',
                'info' => 'Display polls which created in Page to Polls app',
                'description' => 'Enable to display all public polls created in Page to Polls app. Disable to hide them.',
                'type' => 'boolean',
                'value' => '0',
                'ordering' => $iIndex++,
            ],
            'display_polls_created_in_group' => [
                'var_name' => 'display_polls_created_in_group',
                'info' => 'Display polls which created in Group to Polls app',
                'description' => 'Enable to display all public polls created in Group to Polls app. Disable to hide them.',
                'type' => 'boolean',
                'value' => '0',
                'ordering' => $iIndex++,
            ],
            'poll_setting_subject_your_poll_has_been_approved' => [
                'info' => 'Polls - Email Subject - Your Poll Has Been Approved',
                'description' => 'Email subject of the "Your Poll Has Been Approved" notification. <a role="button" onclick="$Core.editMeta(\'email_subject_your_poll_title_has_been_approved\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_subject_your_poll_title_has_been_approved"></span>',
                'type' => '',
                'value' => '{_p var="email_subject_your_poll_title_has_been_approved"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_content_your_poll_has_been_approved' => [
                'info' => 'Polls - Email Content - Your Poll Has Been Approved',
                'description' => 'Email content of the "Your Poll Has Been Approved" notification. <a role="button" onclick="$Core.editMeta(\'your_poll_a_href_link_title_a_has_been_approved_to_view_this_poll_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_poll_a_href_link_title_a_has_been_approved_to_view_this_poll_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="your_poll_a_href_link_title_a_has_been_approved_to_view_this_poll_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_subject_someone_voted_on_your_poll' => [
                'info' => 'Polls - Email Subject - Someone Voted On Your Poll',
                'description' => 'Email subject of the "Someone Voted On Your Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_voted_on_your_poll_question\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_voted_on_your_poll_question"></span>',
                'type' => '',
                'value' => '{_p var="full_name_voted_on_your_poll_question"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_content_someone_voted_on_your_poll' => [
                'info' => 'Polls - Email Content - Someone Voted On Your Poll',
                'description' => 'Email content of the "Someone Voted On Your Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_voted_answer_on_your_poll_question\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_voted_answer_on_your_poll_question"></span>',
                'type' => '',
                'value' => '{_p var="full_name_voted_answer_on_your_poll_question"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_content_someone_voted_some_answers_on_your_poll' => [
                'info' => 'Polls - Email Content - Someone Voted Some Answers On Your Poll',
                'description' => 'Email content of the "Someone Voted Some Answers On Your Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_voted_some_answers_on_your_poll_question\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_voted_some_answers_on_your_poll_question"></span>',
                'type' => '',
                'value' => '{_p var="full_name_voted_some_answers_on_your_poll_question"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_subject_someone_liked_your_poll_question' => [
                'info' => 'Polls - Email Subject - Someone Liked Your Poll Question',
                'description' => 'Email subject of the "Someone Liked Your Poll Question" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_poll_question\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_poll_question"></span>',
                'type' => '',
                'value' => '{_p var="poll.full_name_liked_your_poll_question"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_content_someone_liked_your_poll_question' => [
                'info' => 'Polls - Email Content - Someone Liked Your Poll Question',
                'description' => 'Email content of the "Someone Liked Your Poll Question" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_poll_question_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_poll_question_message"></span>',
                'type' => '',
                'value' => '{_p var="poll.full_name_liked_your_poll_question_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_subject_someone_commented_on_your_poll' => [
                'info' => 'Polls - Email Subject - Someone Commented On Your Poll',
                'description' => 'Email subject of the "Someone Commented On Your Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_one_of_your_polls_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_one_of_your_polls_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_one_of_your_polls_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_content_someone_commented_on_your_poll' => [
                'info' => 'Polls - Email Content - Someone Commented On Your Poll',
                'description' => 'Email content of the "Someone Commented On Your Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_subject_full_name_commented_on_gender_poll' => [
                'info' => 'Polls - Email Subject - Someone Commented On Their Own Poll',
                'description' => 'Email subject of the "Someone Commented On Their Own Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_poll\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_poll"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_poll"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_content_full_name_commented_on_gender_poll' => [
                'info' => 'Polls - Email Content - Someone Commented On Their Own Poll',
                'description' => 'Email content of the "Someone Commented On Their Own Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_subject_full_name_commented_on_other_full_name_s_poll' => [
                'info' => 'Polls - Email Subject - Someone Commented On Other User\'s Poll',
                'description' => 'Email subject of the "Someone Commented On Other User\'s Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_poll\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_poll"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_poll"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'poll_setting_content_full_name_commented_on_other_full_name_s_poll' => [
                'info' => 'Polls - Email Content - Someone Commented On Other User\'s Poll',
                'description' => 'Email content of the "Someone Commented On Other User\'s Poll" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_poll_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ]
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'poll_total_items_can_create' => [
                'var_name'    => 'poll_total_items_can_create',
                'info'        => 'Maximum number of polls',
                'description' => 'Define the total number of polls a user within this user group can create. Notice: Leave this empty will allow them to create an unlimited amount of polls. Setting this value to 0 will not allow them the ability to create polls.',
                'type'        => 'string',
                'value'       => [
                    '1' => '',
                    '2' => '',
                    '3' => '0',
                    '4' => '',
                    '5' => '0'
                ],
            ],
            'poll_can_upload_image' => [
                'var_name' => 'poll_can_upload_image',
                'info' => 'Can upload image?',
                'description' => 'This setting defines if members of this user group can add images along with their polls.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 1,
            ],
            'view_poll_results_before_vote' => [
                'var_name' => 'view_poll_results_before_vote',
                'info' => 'Can members of this user group view poll results before voting on a poll?',
                'description' => 'Note that this setting may be overridden by the "Can view users poll results on their own polls?" and the "Can view users poll results on all polls?" settings. It can also be complemented with the setting "Can view poll results before voting on a poll?"',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => 2,
            ],
            'poll_can_change_own_vote' => [
                'var_name' => 'poll_can_change_own_vote',
                'info' => 'Can change own vote on a poll?',
                'description' => 'If set to false the first vote will be the definitive vote for that user and that poll. If set to true users will be able to change their vote in the future.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => 3,
            ],
            'poll_flood_control' => [
                'var_name' => 'poll_flood_control',
                'info' => 'How often can members of this user group post new polls (in minutes).',
                'description' => '0 => no restriction<br>1 => 1 minute<br>10 => 10 minutes',
                'type' => 'integer',
                'value' => [
                    '1' => '0',
                    '2' => '1',
                    '3' => '9999999',
                    '4' => '0',
                    '5' => '1'
                ],
                'ordering' => 4,
            ],
            'poll_requires_admin_moderation' => [
                'var_name' => 'poll_requires_admin_moderation',
                'info' => 'Polls must be approved first before they are displayed publicly?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '1',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => 5,
            ],
            'poll_can_moderate_polls' => [
                'var_name' => 'poll_can_moderate_polls',
                'info' => 'Can approve polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => 6,
            ],
            'poll_require_captcha_challenge' => [
                'var_name' => 'poll_require_captcha_challenge',
                'info' => 'Do members of this user group need to complete a captcha challenge to submit a poll?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '1',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => 7,
            ],
            'poll_can_edit_own_polls' => [
                'var_name' => 'poll_can_edit_own_polls',
                'info' => 'Can edit their own polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 8,
            ],
            'poll_can_edit_others_polls' => [
                'var_name' => 'poll_can_edit_others_polls',
                'info' => 'Can edit all polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => 9,
            ],
            'poll_can_delete_own_polls' => [
                'var_name' => 'poll_can_delete_own_polls',
                'info' => 'Can delete their own polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 10,
            ],
            'poll_can_delete_others_polls' => [
                'var_name' => 'poll_can_delete_others_polls',
                'info' => 'Can delete all polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => 11,
            ],
            'can_post_comment_on_poll' => [
                'var_name' => 'can_post_comment_on_poll',
                'info' => 'Can post comments on polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 12,
            ],
            'view_poll_results_after_vote' => [
                'var_name' => 'view_poll_results_after_vote',
                'info' => 'When set to yes members of this user group will see the poll results right after voting.',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 13,
            ],
            'maximum_answers_count' => [
                'var_name' => 'maximum_answers_count',
                'info' => 'How many answers can members of this user group add to their polls?',
                'description' => 'By default, minimum value of this setting is 2 even if when you set it smaller than 2.',
                'type' => 'integer',
                'value' => [
                    '1' => '20',
                    '2' => '6',
                    '3' => '0',
                    '4' => '10',
                    '5' => '6'
                ],
                'ordering' => 14,
            ],
            'can_vote_in_own_poll' => [
                'var_name' => 'can_vote_in_own_poll',
                'info' => 'Can vote on their own polls?',
                'description' => 'This is different than changing their votes.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 15,
            ],
            'points_poll' => [
                'var_name' => 'points_poll',
                'info' => 'Activity points',
                'description' => 'Specify how many points the user will receive when adding a new poll.',
                'type' => 'integer',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 16,
            ],
            'can_view_user_poll_results_own_poll' => [
                'var_name' => 'can_view_user_poll_results_own_poll',
                'info' => 'Can view users poll results on their own polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 17,
            ],
            'can_view_user_poll_results_other_poll' => [
                'var_name' => 'can_view_user_poll_results_other_poll',
                'info' => 'Can view users poll results on all polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 18,
            ],
            'highlight_answer_voted_by_viewer' => [
                'var_name' => 'highlight_answer_voted_by_viewer',
                'info' => 'If set to yes the answer chosen by the viewer will be highlighted with a background color.',
                'description' => 'This is useful if you have it set so the members of this usegroup cant view the results after taking the poll as they still will be able to view their own answer.',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 19,
            ],
            'can_access_polls' => [
                'var_name' => 'can_access_polls',
                'info' => 'Can browse and view polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '1',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 20,
            ],
            'can_create_poll' => [
                'var_name' => 'can_create_poll',
                'info' => 'Can create a poll?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering' => 21,
            ],
            'can_feature_poll' => [
                'var_name' => 'can_feature_poll',
                'info' => 'Can feature polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => 22,
            ],
            'can_sponsor_poll' => [
                'var_name' => 'Can mark a poll as sponsor?',
                'info' => 'Can members of this user group mark a poll as Sponsor without paying fee?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering' => 23,
            ],
            'can_purchase_sponsor_poll' => [
                'var_name' => 'can_purchase_sponsor_poll',
                'info' => 'Can members of this user group purchase a sponsored ad space for their polls?',
                'description' => '',
                'type' => 'boolean',
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering' => 24,
            ],
            'poll_sponsor_price' => [
                'var_name' => 'poll_sponsor_price',
                'info' => 'How much is the sponsor space worth for polls? This works in a CPM basis.',
                'description' => '',
                'type' => 'currency',
                'ordering' => 25,
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
                'ordering' => 26
            ],
            'poll_max_upload_size' => [
                'var_name' => 'poll_max_upload_size',
                'info' => 'Max file size for poll photos upload',
                'description' => 'Max file size for poll photos upload in kilobytes (kb). (1024 kb = 1 mb) 
For unlimited add "0" without quotes.',
                'type' => 'integer',
                'value' => [
                    '1' => '8192',
                    '2' => '8192',
                    '3' => '8192',
                    '4' => '8192',
                    '5' => '8192'
                ],
                'ordering' => 27
            ],
        ];
    }

    protected function setComponent()
    {
        $this->component = [
            'block' => [
                'vote' => '',
                'votes' => '',
                'new' => '',
                'featured' => '',
                'sponsored' => '',
                'latest-votes' => ''
            ],
            'controller' => [
                'index' => 'poll.index',
                'view' => 'poll.view',
                'profile' => 'poll.profile',
                'design' => 'poll.design',
            ]
        ];
    }

    protected function setComponentBlock()
    {
        $this->component_block = [
            'Featured' => [
                'type_id' => '0',
                'm_connection' => 'poll.index',
                'component' => 'featured',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Sponsored' => [
                'type_id' => '0',
                'm_connection' => 'poll.index',
                'component' => 'sponsored',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '2',
            ],
            'Latest Votes' => [
                'type_id' => '0',
                'm_connection' => 'poll.view',
                'component' => 'latest-votes',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ],
        ];
    }

    protected function setPhrase()
    {
        $this->phrase = $this->_app_phrases;
    }

    protected function setOthers()
    {
        $this->map = [];
        $this->menu = [
            'phrase_var_name' => 'menu_poll',
            'url' => 'poll',
            'icon' => 'bar-chart'
        ];
        $this->admincp_menu = [
            'Settings' => '#'
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/poll/'
        ];
        $this->admincp_route = Phpfox::getLib('url')->makeUrl('admincp.app.settings', ['id' => 'Core_Polls']);
        $this->database = [
            'Poll',
            'Poll_Answer',
            'Poll_Design',
            'Poll_Result'
        ];
        $this->_apps_dir = 'core-polls';
        $this->_admin_cp_menu_ajax = false;
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
    }
}