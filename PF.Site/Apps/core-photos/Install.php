<?php

namespace Apps\Core_Photos;

use Core\App;

/**
 * Class Install
 * @author  phpFox
 * @package Apps\core_photos
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    public $store_id = 1887;

    protected function setId()
    {
        $this->id = 'Core_Photos';
    }

    protected function setAlias()
    {
        $this->alias = 'photo';
    }

    protected function setName()
    {
        $this->name = _p('Photos');
    }

    protected function setVersion()
    {
        $this->version = '4.7.16';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.7';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'photo_paging_mode'                         => [
                'var_name'    => 'photo_paging_mode',
                'info'        => 'Pagination Style',
                'description' => 'Select Pagination Style at Search Page.',
                'type'        => 'select',
                'value'       => 'loadmore',
                'options'     => [
                    'loadmore'   => 'Scrolling down to Load More items',
                    'next_prev'  => 'Use Next and Prev buttons',
                    'pagination' => 'Use Pagination with page number'
                ],
                'ordering'    => $iIndex++,
            ],
            'allow_photo_category_selection'            => [
                'var_name'    => 'allow_photo_category_selection',
                'info'        => 'Allow Selection of Categories',
                'description' => 'Enable this feature to give users the option to select categories directly while uploading photos.',
                'type'        => 'boolean',
                'value'       => 1,
                'ordering'    => $iIndex++,
            ],
            'photo_upload_process'                      => [
                'var_name'    => 'photo_upload_process',
                'info'        => 'Edit Photos After Upload',
                'description' => 'Enable this option if you want users to edit the batch of photos they had just recently updated.',
                'type'        => 'boolean',
                'value'       => 1,
                'ordering'    => $iIndex++,
            ],
            'ajax_refresh_on_featured_photos'           => [
                'var_name'    => 'ajax_refresh_on_featured_photos',
                'info'        => 'AJAX Refresh Featured Photos',
                'description' => 'With this option enabled photos within the "Featured Photo" block will refresh.',
                'type'        => 'boolean',
                'value'       => 0,
                'ordering'    => $iIndex++,
            ],
            'display_profile_photo_within_gallery'      => [
                'var_name'    => 'display_profile_photo_within_gallery',
                'info'        => 'Display User Profile Photos within Gallery',
                'description' => 'Disable this feature if you do not want to display user profile photos within the photo gallery.',
                'type'        => 'boolean',
                'value'       => 0,
                'ordering'    => $iIndex++,
            ],
            'display_cover_photo_within_gallery'        => [
                'var_name'    => 'display_cover_photo_within_gallery',
                'info'        => 'Display User Cover Photos within Gallery',
                'description' => 'Disable this feature if you do not want to display user cover photos within the photo gallery.',
                'type'        => 'boolean',
                'value'       => 0,
                'ordering'    => $iIndex++,
            ],
            'display_photo_album_created_in_group'      => [
                'var_name'    => 'display_photo_album_created_in_group',
                'info'        => 'Display photos/albums which created in Group to the Photo app',
                'description' => 'Enable to display all public photos/albums created in Group to Photos app. Disable to hide them.',
                'type'        => 'boolean',
                'value'       => '0',
                'ordering'    => $iIndex++,
            ],
            'display_photo_album_created_in_page'       => [
                'var_name'    => 'display_photo_album_created_in_page',
                'info'        => 'Display photos/albums which created in Page to the Photo app',
                'description' => 'Enable to display all public photos/albums created in Page to Photos app. Disable to hide them.',
                'type'        => 'boolean',
                'value'       => '0',
                'ordering'    => $iIndex++,
            ],
            'display_timeline_photo_within_gallery'     => [
                'var_name'    => 'display_timeline_photo_within_gallery',
                'info'        => 'Display User Timeline Photos within Gallery',
                'description' => 'Disable this feature if you do not want to display user timeline photos within the photo gallery.',
                'type'        => 'boolean',
                'value'       => 0,
                'ordering'    => $iIndex++,
            ],
            'photo_allow_create_feed_when_add_new_item' => [
                'var_name'    => 'photo_allow_posting_on_main_feed',
                'info'        => 'Allow posting on Main Feed',
                'description' => 'Allow posting on Main feed when adding a new photo/album.',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex
            ],
            'photo_meta_description'                    => [
                'var_name'    => 'photo_meta_description',
                'info'        => 'Photo Meta Description',
                'description' => 'Meta description added to pages related to the Photo app. <a role="button" onclick="$Core.editMeta(\'seo_photo_meta_description\', true)">Click here</a> to edit meta description.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_photo_meta_description"></span>',
                'type'        => '',
                'value'       => '{_p var=\'seo_photo_meta_description\'}',
                'group_id'    => 'seo',
                'ordering'    => $iIndex++,
            ],
            'photo_meta_keywords'                       => [
                'var_name'    => 'photo_meta_keywords',
                'info'        => 'Photo Meta Keywords',
                'description' => 'Meta keywords that will be displayed on sections related to the Photo app. <a role="button" onclick="$Core.editMeta(\'seo_photo_meta_keywords\', true)">Click here</a> to edit meta keywords.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_photo_meta_keywords"></span>',
                'type'        => '',
                'value'       => '{_p var=\'seo_photo_meta_keywords\'}',
                'group_id'    => 'seo',
                'ordering'    => $iIndex++,
            ],
            'photo_mode_views'                          => [
                'var_name'    => 'photo_mode_views',
                'info'        => 'Photo Mode Views',
                'description' => 'Select mode views to view photos.',
                'type'        => 'multi_checkbox',
                'value'       => ['grid', 'casual'],
                'options'     => [
                    'grid'   => 'Grid',
                    'casual' => 'Casual'
                ],
                'ordering'    => $iIndex++,
            ],
            'photo_default_mode_view'                   => [
                'var_name'    => 'photo_default_mode_view',
                'info'        => 'Default Photo Mode View',
                'description' => 'Select a default mode to view photos when there are more than 1 view mode.',
                'type'        => 'select',
                'value'       => 'grid',
                'options'     => [
                    'grid'   => 'Grid',
                    'casual' => 'Casual'
                ],
                'ordering'    => $iIndex++,
            ],
            'photo_allow_posting_user_photo_feed' => [
                'var_name'    => 'photo_allow_posting_user_photo_feed',
                'info'        => 'Allow posting User\'s Profile/Cover Photo on Main Feed',
                'description' => 'Allow posting on Main feed when users updated their profile/cover photo. ',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex++,
            ],
            'photo_show_title' => [
                'var_name'    => 'photo_show_title',
                'info'        => 'Show Photo Title',
                'description' => 'Allow showing photo title.',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex++,
            ],

            'photo_setting_subject_user_name_tagged_you_in_a_photo_post' => [
                'info' => 'Photos - Email Subject - Someone Tagged You In A Photo Post',
                'description' => 'Email subject of the "Someone Tagged You In A Photo Post" notification.<a role="button" onclick="$Core.editMeta(\'user_name_tagged_you_in_a_photo_post\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="user_name_tagged_you_in_a_photo_post"></span>',
                'type' => '',
                'value' => '{_p var="user_name_tagged_you_in_a_photo_post"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_user_name_tagged_you_in_a_photo_post_check_it_out' => [
                'info' => 'Photos - Email Content - Someone Tagged You In A Photo Post',
                'description' => 'Email content of the "Someone Tagged You In A Photo Post" notification.<a role="button" onclick="$Core.editMeta(\'user_name_tagged_you_in_a_photo_post_check_it_out\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="user_name_tagged_you_in_a_photo_post_check_it_out"></span>',
                'type' => '',
                'value' => '{_p var="user_name_tagged_you_in_a_photo_post_check_it_out"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],

            'photo_setting_subject_full_name_commented_on_gender_photo' => [
                'info' => 'Photos - Email Subject - Someone Commented On Their Own Photo',
                'description' => 'Email subject of the "Someone Commented On Their Own Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_photo\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_photo"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_photo"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_gender_photo' => [
                'info' => 'Photos - Email Content - Someone Commented On Their Own Photo',
                'description' => 'Email content of the "Someone Commented On Their Own Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_gender_photo_without_photo_title' => [
                'info' => 'Photos - Email Content - Someone Commented On Their Own Photo Without Photo Title',
                'description' => 'Email content of the "Someone Commented On Their Own Photo Without Photo Title" notification.<a role="button" onclick="$Core.editMeta(\'photo_full_name_commented_on_gender_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="photo_full_name_commented_on_gender_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="photo_full_name_commented_on_gender_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_commented_on_other_full_name_s_photo' => [
                'info' => 'Photos - Email Subject - Someone Commented On Other User\'s Photo',
                'description' => 'Email subject of the "Someone Commented On Other User\'s Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_photo\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_photo"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_photo"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_other_full_name_s_photo' => [
                'info' => 'Photos - Email Content -  Someone Commented On Other User\'s Photo',
                'description' => 'Email content of the "Someone Commented On Other User\'s Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_other_full_name_s_photo_without_photo_title' => [
                'info' => 'Photos - Email Content -  Someone Commented On Other User\'s Photo Without Photo Title',
                'description' => 'Email content of the "Someone Commented On Other User\'s Photo Without Photo Title" notification.<a role="button" onclick="$Core.editMeta(\'photo_full_name_commented_on_other_full_name_s_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="photo_full_name_commented_on_other_full_name_s_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="photo_full_name_commented_on_other_full_name_s_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_commented_on_your_photo_title' => [
                'info' => 'Photos - Email Subject - Someone Commented On Your Photo',
                'description' => 'Email subject of the "Someone Commented On Your Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_photo_title\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_photo_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_photo_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_commented_on_your_photo_without_photo_title' => [
                'info' => 'Photos - Email Subject - Someone Commented On Your Photo Without Photo Title',
                'description' => 'Email subject of the "Someone Commented On Your Photo Without Photo Title" notification.<a role="button" onclick="$Core.editMeta(\'photo_full_name_commented_on_your_photo\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="photo_full_name_commented_on_your_photo"></span>',
                'type' => '',
                'value' => '{_p var="photo_full_name_commented_on_your_photo"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_your_photo_title' => [
                'info' => 'Photos - Email Content -  Someone Commented On Your Photo',
                'description' => 'Email content of the "Someone Commented On Your Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_photo_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_your_photo_without_photo_title' => [
                'info' => 'Photos - Email Content -  Someone Commented On Your Photo Without Photo Title',
                'description' => 'Email content of the "Someone Commented On Your Photo" notification.<a role="button" onclick="$Core.editMeta(\'photo_full_name_commented_on_your_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="photo_full_name_commented_on_your_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="photo_full_name_commented_on_your_photo_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_liked_your_photo_title' => [
                'info' => 'Photos - Email Subject - Someone Liked Your Photo',
                'description' => 'Email subject of the "Someone Liked Your Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_liked_your_photo_title\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_photo_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_photo_title"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_liked_your_photo_without_photo_title' => [
                'info' => 'Photos - Email Subject - Someone Liked Your Photo Without Photo Title',
                'description' => 'Email subject of the "Someone Liked Your Photo Without Photo Title" notification.<a role="button" onclick="$Core.editMeta(\'photo_full_name_liked_your_photo\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="photo_full_name_liked_your_photo"></span>',
                'type' => '',
                'value' => '{_p var="photo_full_name_liked_your_photo"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_liked_your_photo_message' => [
                'info' => 'Photos - Email Content -  Someone Liked Your Photo',
                'description' => 'Email content of the "Someone Liked Your Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_liked_your_photo_message\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_photo_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_photo_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_liked_your_photo_message_without_photo_title' => [
                'info' => 'Photos - Email Content -  Someone Liked Your Photo Without Photo Title',
                'description' => 'Email content of the "Someone Liked Your Photo" notification.<a role="button" onclick="$Core.editMeta(\'photo_full_name_liked_your_photo_message\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="photo_full_name_liked_your_photo_message"></span>',
                'type' => '',
                'value' => '{_p var="photo_full_name_liked_your_photo_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_liked_your_photo_album_name' => [
                'info' => 'Photos - Email Subject - Someone Liked Your Photo Album',
                'description' => 'Email subject of the "Someone Liked Your Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_liked_your_photo_album_name\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_photo_album_name"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_photo_album_name"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_liked_your_photo_album_message' => [
                'info' => 'Photos - Email Content -  Someone Liked Your Photo Album',
                'description' => 'Email content of the "Someone Liked Your Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_liked_your_photo_album_message\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_photo_album_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_photo_album_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_post_some_images_on_your_wall' => [
                'info' => 'Photos - Email Subject - Someone Posted Some Images On Your Wall',
                'description' => 'Email subject of the "Someone Posted Some Images On Your Wall" notification.<a role="button" onclick="$Core.editMeta(\'full_name_post_some_images_on_your_wall\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_images_on_your_wall"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_images_on_your_wall"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_post_some_images_on_your_wall_message' => [
                'info' => 'Photos - Email Content - Someone Posted Some Images On Your Wall',
                'description' => 'Email content pf the "Someone Posted Some Images On Your Wall" notification.<a role="button" onclick="$Core.editMeta(\'full_name_post_some_images_on_your_wall_message\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_post_some_images_on_your_wall_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_post_some_images_on_your_wall_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_your_photo_title_has_been_approved' => [
                'info' => 'Photos - Email Subject - Your Photo Has Been Approved',
                'description' => 'Email subject of the "Your Photo Has Been Approved" notification.<a role="button" onclick="$Core.editMeta(\'your_photo_title_has_been_approved\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_photo_title_has_been_approved"></span>',
                'type' => '',
                'value' => '{_p var="your_photo_title_has_been_approved"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_your_photo_has_been_approved_message' => [
                'info' => 'Photos - Email Content - Your Photo Has Been Approved',
                'description' => 'Email content of the "Your Photo Has Been Approved" notification.<a role="button" onclick="$Core.editMeta(\'your_photo_has_been_approved_message\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_photo_has_been_approved_message"></span>',
                'type' => '',
                'value' => '{_p var="your_photo_has_been_approved_message"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],

            'photo_setting_subject_full_name_tagged_you_in_a_photo' => [
                'info' => 'Photos - Email Subject - Someone Tagged You In A Photo',
                'description' => 'Email subject of the "Someone Tagged You In A Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_tagged_you_in_a_photo\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_tagged_you_in_a_photo"></span>',
                'type' => '',
                'value' => '{_p var="full_name_tagged_you_in_a_photo"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_tagged_you_on_gender_photo' => [
                'info' => 'Photos - Email Content - Someone Tagged You In A Photo',
                'description' => 'Email content of the "Someone Tagged You In A Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_tagged_you_on_gender_photo\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_tagged_you_on_gender_photo"></span>',
                'type' => '',
                'value' => '{_p var="full_name_tagged_you_on_gender_photo"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_tagged_you_on_user_photo' => [
                'info' => 'Photos - Email Content - Someone Tagged You on Other User\'s Photo',
                'description' => 'Email content of the "Someone Tagged You on Other User\'s Photo" notification.<a role="button" onclick="$Core.editMeta(\'full_name_tagged_you_on_user_photo\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_tagged_you_on_user_photo"></span>',
                'type' => '',
                'value' => '{_p var="full_name_tagged_you_on_user_photo"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_commented_on_your_photo_album_name' => [
                'info' => 'Photos - Email Subject - Someone Commented On Your Photo Album',
                'description' => 'Email subject of the "Someone Commented On Your Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_photo_album_name\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_photo_album_name"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_photo_album_name"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_your_photo_album_name' => [
                'info' => 'Photos - Email Content - Someone Commented On Your Photo Album',
                'description' => 'Email content of the "Someone Commented On Your Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_commented_on_gender_photo_album' => [
                'info' => 'Photos - Email Subject - Someone Commented On Their Own Photo Album',
                'description' => 'Email subject of the "Someone Commented On Their Own Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_photo_album\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_photo_album"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_photo_album"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_gender_photo_album' => [
                'info' => 'Photos - Email Content - Someone Commented On Their Own Photo Album',
                'description' => 'Email content of the "Someone Commented On Their Own Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_subject_full_name_commented_on_other_full_name_s_photo_album' => [
                'info' => 'Photos - Email Subject - Someone Commented On Other User\'s Photo Album',
                'description' => 'Email subject of the "Someone Commented On Other User\'s Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_photo_album\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_photo_album"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_photo_album"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
            'photo_setting_content_full_name_commented_on_other_full_name_s_photo_album' => [
                'info' => 'Photos - Email Content - Someone Commented On Other User\'s Photo Album',
                'description' => 'Email content of the "Someone Commented On Other User\'s Photo Album" notification.<a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)"> Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_photo_album_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'ordering' => $iIndex++,
                'group_id' => 'email'
            ],
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'photo_total_photos_upload' => [
                'var_name'    => 'photo_total_photos_upload',
                'info'        => 'Maximum number of photos',
                'description' => 'Define the total number of photos a user within this user group can upload. Notice: Leave this empty will allow them to upload an unlimited amount of photos. Setting this value to 0 will not allow them the ability to upload photos.',
                'type'        => 'string',
                'value'       => [
                    '1' => '',
                    '2' => '',
                    '3' => '0',
                    '4' => '',
                    '5' => '0'
                ],
                'ordering'    => 1
            ],
            'can_upload_photos'                   => [
                'var_name'    => 'can_upload_photos',
                'info'        => 'Can upload photos?',
                'description' => '',
                'type'        => 'input:radio',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 1
            ],
            'max_images_per_upload'               => [
                'var_name'    => 'max_images_per_upload',
                'info'        => 'Maximum number of images per upload',
                'description' => 'Define the maximum number of images a user can upload each time they use the upload form. Leave to 0 for no images 
Notice: This setting does not control how many images a user can upload in total, just how many they can upload each time they use the upload form to upload new images.',
                'type'        => 'integer',
                'value'       => [
                    '1' => '10',
                    '2' => '10',
                    '3' => '0',
                    '4' => '10',
                    '5' => '10'
                ],
                'ordering'    => 2
            ],
            'points_photo'                        => [
                'var_name'    => 'points_photo',
                'info'        => 'Activity points',
                'description' => 'How many activity points should a user receive for uploading a new image.',
                'type'        => 'integer',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 3
            ],
            'can_create_photo_album'              => [
                'var_name'    => 'can_create_photo_album',
                'info'        => 'Can create a new photo album?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 4
            ],
            'can_use_privacy_settings'            => [
                'var_name'    => 'can_use_privacy_settings',
                'info'        => 'Can use privacy settings when creating an album?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 5
            ],
            'max_number_of_albums'                => [
                'var_name'    => 'max_number_of_photo_albums',
                'info'        => 'Maximum number of photo albums',
                'description' => 'Define the total number of photo albums a user within this user group can create. 
Notice: Leave this empty will allow them to create an unlimited amount of photo albums. Setting this value to 0 will not allow them the ability to create photo albums.',
                'type'        => 'string',
                'value'       => [
                    '1' => '',
                    '2' => '20',
                    '3' => '0',
                    '4' => '30',
                    '5' => '20'
                ],
                'ordering'    => 6
            ],
            'can_view_photos'                     => [
                'var_name'    => 'can_view_photos',
                'info'        => 'Can browse and view the photo module?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '1',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 7
            ],
            'can_search_for_photos'               => [
                'var_name'    => 'can_search_for_photos',
                'info'        => 'Can search for photos?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 8
            ],
            'can_download_user_photos'            => [
                'var_name'    => 'can_download_user_photos',
                'info'        => 'Can download other users photos?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 9
            ],
            'can_post_on_photos'                  => [
                'var_name'    => 'can_post_on_photos',
                'info'        => 'Can post comments on photos?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 10
            ],
            'can_post_on_albums'                  => [
                'var_name'    => 'can_post_on_photo_albums',
                'info'        => 'Can post comments on photo albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 11
            ],
            'can_add_mature_images'               => [
                'var_name'    => 'can_add_mature_images',
                'info'        => 'Can add mature images with warnings?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 12
            ],
            'photo_mature_age_limit'              => [
                'var_name'    => 'photo_mature_age_limit',
                'info'        => 'Photo mature age limit?',
                'description' => 'Note: The age you define will 
- not allow users with younger the ability to view mature photos (strict)
- display warning to users with younger while viewing mature photos (warning)',
                'type'        => 'integer',
                'value'       => [
                    '1' => '18',
                    '2' => '18',
                    '3' => '18',
                    '4' => '18',
                    '5' => '18'
                ],
                'ordering'    => 13
            ],
            'total_photos_displays'               => [
                'var_name'    => 'total_photos_displays',
                'info'        => 'Define how many images a user can view at once when browsing the public photo section?',
                'description' => '',
                'type'        => 'array',
                'value'       => [
                    '1' => [20, 40, 60],
                    '2' => [20, 40, 60],
                    '3' => [20, 40, 60],
                    '4' => [20, 40, 60],
                    '5' => [20, 40, 60]
                ],
                'ordering'    => 14
            ],
            'can_edit_own_photo'                  => [
                'var_name'    => 'can_edit_own_photo',
                'info'        => 'Can edit own photo?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 15
            ],
            'can_edit_other_photo'                => [
                'var_name'    => 'can_edit_other_photo',
                'info'        => 'Can edit all photos?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 16
            ],
            'can_delete_own_photo'                => [
                'var_name'    => 'can_delete_own_photo',
                'info'        => 'Can delete own photos?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 17
            ],
            'can_delete_other_photos'             => [
                'var_name'    => 'can_delete_other_photos',
                'info'        => 'Can delete all photos?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 18
            ],
            'can_edit_own_photo_album'            => [
                'var_name'    => 'can_edit_own_photo_album',
                'info'        => 'Can edit own photo albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 19
            ],
            'can_edit_other_photo_albums'         => [
                'var_name'    => 'can_edit_other_photo_albums',
                'info'        => 'Can edit all photo albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 20
            ],
            'can_delete_own_photo_album'          => [
                'var_name'    => 'can_delete_own_photo_album',
                'info'        => 'Can delete own photo albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 21
            ],
            'can_delete_other_photo_albums'       => [
                'var_name'    => 'can_delete_other_photo_albums',
                'info'        => 'Can delete all photo albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 22
            ],
            'photo_must_be_approved'              => [
                'var_name'    => 'photo_must_be_approved',
                'info'        => 'Photos must be approved first before they are displayed publicly?',
                'description' => 'Set this to True if photos uploaded must be approved before they are visible to the public.',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '1',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 23
            ],
            'can_approve_photos'                  => [
                'var_name'    => 'can_approve_photos',
                'info'        => 'Can approve photos that require moderation?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 24
            ],
            'can_feature_photo'                   => [
                'var_name'    => 'can_feature_photo',
                'info'        => 'Can feature a photo?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 25
            ],
            'can_tag_own_photo'                   => [
                'var_name'    => 'can_tag_own_photo',
                'info'        => 'Can tag own photo?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 26
            ],
            'can_tag_other_photos'                => [
                'var_name'    => 'can_tag_other_photos',
                'info'        => 'Can tag photos added by all users?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 27
            ],
            'how_many_tags_on_own_photo'          => [
                'var_name'    => 'how_many_tags_on_own_photo',
                'info'        => 'How many times can a user tag their own photo?. Set 0 won\'t allow users to tag on their own photos',
                'description' => '',
                'type'        => 'integer',
                'value'       => [
                    '1' => '40',
                    '2' => '40',
                    '3' => '0',
                    '4' => '40',
                    '5' => '40'
                ],
                'ordering'    => 28
            ],
            'how_many_tags_on_other_photo'        => [
                'var_name'    => 'how_many_tags_on_other_photo',
                'info'        => 'How many times can this user tag photos added by other users?. Set 0 won\'t allow users to tag on photos added by other users',
                'description' => '',
                'type'        => 'integer',
                'value'       => [
                    '1' => '4',
                    '2' => '4',
                    '3' => '0',
                    '4' => '4',
                    '5' => '4'
                ],
                'ordering'    => 29
            ],
            'photo_max_upload_size'               => [
                'var_name'    => 'photo_max_upload_size',
                'info'        => 'Max file size for photos upload',
                'description' => 'Max file size for photos upload in kilobytes (kb). (1024 kb = 1 mb) 
For unlimited add "0" without quotes.',
                'type'        => 'integer',
                'value'       => [
                    '1' => '8192',
                    '2' => '8192',
                    '3' => '8192',
                    '4' => '8192',
                    '5' => '8192'
                ],
                'ordering'    => 30
            ],
            'can_sponsor_photo'                   => [
                'var_name'    => 'can_sponsor_photo',
                'info'        => 'Can members of this user group mark a photo as Sponsor without paying fee?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 31
            ],
            'can_purchase_sponsor'                => [
                'var_name'    => 'can_purchase_sponsor_photos',
                'info'        => 'Can members of this user group purchase a sponsored ad space for photos?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 32
            ],
            'photo_sponsor_price'                 => [
                'var_name'    => 'photo_sponsor_price',
                'info'        => 'How much is the sponsor space worth for photos? This works in a CPM basis.',
                'description' => '',
                'type'        => 'currency'
            ],
            'auto_publish_sponsored_item'         => [
                'var_name'    => 'auto_publish_sponsored_item',
                'info'        => 'Auto publish sponsored item?',
                'description' => 'After the user has purchased a sponsored space, should the item be published right away? 
If set to No, the admin will have to approve each new purchased sponsored item space before it is shown in the site.',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 33
            ],
            'can_view_photo_albums'               => [
                'var_name'    => 'can_view_photo_albums',
                'info'        => 'Can view photo albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '1',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 34
            ],
            'flood_control_photos'                => [
                'var_name'    => 'flood_control_photos',
                'info'        => 'Flood control photos',
                'description' => 'How many minutes should a user wait before they can upload another batch of photos? 
Note: Setting it to "0" (without quotes) is default and users will not have to wait.',
                'type'        => 'integer',
                'value'       => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 35
            ],
            'refresh_featured_photo'              => [
                'var_name'    => 'refresh_featured_photo',
                'info'        => 'How many minutes or seconds the script should wait until it refreshes the feature photo?',
                'description' => 'Define how many minutes or seconds the script should wait until it refreshes the feature photo. <br/>
Notice: To add X minutes here are some examples: <br/>
1 min <br/>
2 min <br/>
30 min <br/>
If you would like to define it in seconds here are some examples: <br/>
20 sec <br/>
30 sec <br/>
90 sec',
                'type'        => 'input:text',
                'value'       => [
                    '1' => '1 min',
                    '2' => '1 min',
                    '3' => '1 min',
                    '4' => '1 min',
                    '5' => '1 min'
                ],
                'ordering'    => 36
            ],
            'maximum_image_width_keeps_in_server' => [
                'var_name'    => 'maximum_image_width_keeps_in_server',
                'info'        => 'Maximum image width keeps in server (in pixel)',
                'description' => 'If image width user upload higher than this value will crop to this value.',
                'type'        => 'integer',
                'value'       => [
                    '1' => '1500',
                    '2' => '1200',
                    '3' => '1200',
                    '4' => '1500',
                    '5' => '1200'
                ],
                'ordering'    => 37
            ],
            'can_sponsor_album'                   => [
                'var_name'    => 'can_sponsor_photo_album',
                'info'        => 'Can members of this user group mark a photo album as Sponsor without paying fee?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 38
            ],
            'can_purchase_sponsor_album'          => [
                'var_name'    => 'can_purchase_sponsor_photo_albums',
                'info'        => 'Can members of this user group purchase a sponsored ad space for photo albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 39
            ],
            'photo_album_sponsor_price'           => [
                'var_name'    => 'photo_album_sponsor_price',
                'info'        => 'How much is the sponsor space worth for photo albums? This works in a CPM basis.',
                'description' => '',
                'type'        => 'currency'
            ],
            'can_feature_photo_album'             => [
                'var_name'    => 'can_feature_photo_album',
                'info'        => 'Can feature a photo album?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 40
            ],
            'auto_publish_sponsored_album'        => [
                'var_name'    => 'auto_publish_sponsored_photo_album',
                'info'        => 'Can automatically publish a sponsored photo album?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 41
            ]
        ];
    }

    protected function setComponent()
    {
        $this->component = [
            'block'      => [
                'category'        => '',
                'featured'        => '',
                'detail'          => '',
                'stream'          => '',
                'profile'         => '',
                'sponsored'       => '',
                'album-tag'       => '',
                'my-photo'        => '',
                'featured-album'  => '',
                'sponsored-album' => ''
            ],
            'controller' => [
                'index'   => 'photo.index',
                'view'    => 'photo.view',
                'profile' => 'photo.profile',
                'album'   => 'photo.album',
                'add'     => 'photo.add',
                'albums'  => 'photo.albums'
            ]
        ];
    }

    protected function setComponentBlock()
    {
        $this->component_block = [
            'Categories'             => [
                'type_id'      => '0',
                'm_connection' => 'photo.index',
                'component'    => 'category',
                'location'     => '1',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Featured Photos'        => [
                'type_id'      => '0',
                'm_connection' => 'photo.index',
                'component'    => 'featured',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Sponsored Photos'       => [
                'type_id'      => '0',
                'm_connection' => 'photo.index',
                'component'    => 'sponsored',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Viewing Photo'          => [
                'type_id'      => '0',
                'm_connection' => 'photo.view',
                'component'    => 'stream',
                'location'     => '7',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'In This Album'          => [
                'type_id'      => '0',
                'm_connection' => 'photo.album',
                'component'    => 'album-tag',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Photos'                 => [
                'type_id'      => '0',
                'm_connection' => 'profile.index',
                'component'    => 'my-photo',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Featured Photos Album'  => [
                'type_id'      => '0',
                'm_connection' => 'photo.albums',
                'component'    => 'featured-album',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Sponsored Photos Album' => [
                'type_id'      => '0',
                'm_connection' => 'photo.albums',
                'component'    => 'sponsored-album',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
        ];
    }

    protected function setPhrase()
    {
        $this->phrase = $this->_app_phrases;
    }

    protected function setOthers()
    {
        $this->admincp_route = '/photo/admincp';
        $this->admincp_menu = [
            _p('Categories') => '#',
        ];
        $this->admincp_action_menu = [
            '/admincp/photo/add' => _p('New Category')
        ];
        $this->map = [];
        $this->menu = [
            'phrase_var_name' => 'menu_photos',
            'url'             => 'photo',
            'icon'            => 'photo'
        ];
        $this->database = [
            'Photo',
            'Photo_Category',
            'Photo_Category_Data',
            'Photo_Info',
            'Photo_Feed',
            'Photo_Tag',
            'Photo_Album',
            'Photo_Album_Info'
        ];
        $this->_apps_dir = 'core-photos';
        $this->_admin_cp_menu_ajax = false;
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->_writable_dirs = [
            'PF.Base/file/pic/photo/'
        ];
    }
}