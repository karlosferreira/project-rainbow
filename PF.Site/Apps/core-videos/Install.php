<?php

namespace Apps\PHPfox_Videos;

use Core\App;
use Core\App\Install\Setting;
use Phpfox_Url;

class Install extends App\App
{
    private $_app_phrases = [

    ];

    public $store_id = 1819;

    protected function setId()
    {
        $this->id = 'PHPfox_Videos';
    }

    protected function setAlias()
    {
        $this->alias = 'v';
    }

    protected function setName()
    {
        $this->name = _p('Videos');
    }

    protected function setVersion()
    {
        $this->version = '4.7.10';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.8.1';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'pf_video_paging_mode' => [
                'var_name' => 'pf_video_paging_mode',
                'info' => 'Pagination Style',
                'description' => 'Select Pagination Style at Search Page.',
                'type' => 'select',
                'value' => 'loadmore',
                'options' => [
                    'loadmore' => 'Scrolling down to Load More items',
                    'next_prev' => 'Use Next and Prev buttons',
                    'pagination' => 'Use Pagination with page number'
                ],
                'ordering' => $iIndex
            ],
            'pf_video_meta_description' => [
                'var_name' => 'pf_v_meta_description',
                'info' => 'Video Meta Description',
                'description' => 'Meta description added to pages related to the Video app. <a role="button" onclick="$Core.editMeta(\'seo_video_meta_description\', true)">Click here</a> to edit meta description.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_video_meta_description"></span>',
                'type' => '',
                'value' => "{_p var='seo_video_meta_description'}",
                'group_id' => 'seo',
                'ordering' => $iIndex++
            ],
            'pf_video_meta_keywords' => [
                'var_name' => 'pf_v_meta_keywords',
                'info' => 'Video Meta Keywords',
                'description' => 'Meta keywords that will be displayed on sections related to the Video app. <a role="button" onclick="$Core.editMeta(\'seo_video_meta_keywords\', true)">Click here</a> to edit meta keywords.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_video_meta_keywords"></span>',
                'type' => '',
                'value' => "{_p var='seo_video_meta_keywords'}",
                'group_id' => 'seo',
                'ordering' => $iIndex++
            ],
            'pf_video_display_video_created_in_group' => [
                'var_name' => 'pf_display_video_created_in_group',
                'info' => 'Display videos which created in Group to the All Videos page at the Video app',
                'description' => 'Enable to display all public videos to the both Video page in group detail and All Videos page in Video app. Disable to display videos created by an users to the both Video page in group detail and My Videos page of this user in Video app and nobody can see these videos in Video app but owner. <br/><i><b>Notice:</b> This setting will be applied for all types of groups, include secret groups.</i>',
                'type' => Setting\Site::TYPE_RADIO,
                'value' => '0',
                'ordering' => $iIndex++
            ],
            'pf_video_display_video_created_in_page' => [
                'var_name' => 'pf_display_video_created_in_page',
                'info' => 'Display videos which created in Page to the All Videos page at the Video app',
                'description' => ' Enable to display all public videos to the both Video page in page detail and All Videos page in Video app. Disable to display videos created by an users to the both Video page in page detail and My Videos page of this user in Video app and nobody can see these videos in Video app but owner.',
                'type' => Setting\Site::TYPE_RADIO,
                'value' => '0',
                'ordering' => $iIndex++
            ],
            'pf_video_support_upload_video' => [
                'var_name' => 'pf_support_upload_video',
                'info' => 'Enable Uploading of Videos',
                'description' => 'Enable this option if you would like to give users the ability to upload videos from their computer. <br/><i><b>Notice:</b> This feature requires that Mux, FFMPEG, (ZenCoder/Amazon S3) or External FFMPEG Server be installed. Once you attempt to enable this feature the script will attempt to verify if the server has all the required scripts installed.</i>',
                'type' => Setting\Site::TYPE_RADIO,
                'value' => '1',
                'ordering' => $iIndex++
            ],
            'pf_video_method_upload' => [
                'var_name' => 'pf_video_method_upload',
                'info' => 'Uploading Method',
                'description' => 'Select which method to encode your videos.',
                'type' => Setting\Site::TYPE_SELECT,
                'value' => '1',
                'options' => [
                    '0' => 'FFMPEG',
                    '1' => 'Zencoder + S3',
                    '2' => 'Mux'
                ],
                'ordering' => $iIndex++
            ],
            'pf_video_key' => [
                'var_name' => 'pf_video_key',
                'info' => 'Zencoder API Key',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_key' => [
                'var_name' => 'pf_video_s3_key',
                'info' => 'Amazon S3 Access Key',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_secret' => [
                'var_name' => 'pf_video_s3_secret',
                'info' => 'Amazon S3 Secret',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_bucket' => [
                'var_name' => 'pf_video_s3_bucket',
                'info' => 'Amazon S3 Bucket',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_region' => [
                'var_name' => 'pf_video_s3_region',
                'info' => 'Amazon S3 Region',
                'description' => 'This setting is updated from Bucket info. Do not change this value.',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_url' => [
                'var_name' => 'pf_video_s3_url',
                'info' => 'Provide the S3, CloudFront or Custom URL',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_cache_control_meta' => [
                'var_name' => 'pf_video_s3_cache_control_meta',
                'info' => 'Amazon S3 Cache-Control Meta',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_content_disposition_meta' => [
                'var_name' => 'pf_video_s3_content_disposition_meta',
                'info' => 'Amazon S3 Content-Disposition Meta',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_content_encoding_meta' => [
                'var_name' => 'pf_video_s3_content_encoding_meta',
                'info' => 'Amazon S3 Content-Encoding Meta',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_content_language_meta' => [
                'var_name' => 'pf_video_s3_content_language_meta',
                'info' => 'Amazon S3 Content-Language Meta',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_expires_meta' => [
                'var_name' => 'pf_video_s3_expires_meta',
                'info' => 'Amazon S3 Expires Meta',
                'ordering' => $iIndex++
            ],
            'pf_video_s3_website_redirect_location_meta' => [
                'var_name' => 'pf_video_s3_website_redirect_location_meta',
                'info' => 'Amazon S3 x-amz-website-redirect-location Meta',
                'ordering' => $iIndex++
            ],
            'pf_video_ffmpeg_path' => [
                'var_name' => 'pf_video_ffmpeg_path',
                'info' => 'Path to FFMPEG',
                'description' => 'Please enter the path then <a href="' . Phpfox_Url::instance()->makeUrl('admincp.v.utilities') . '">click here</a> to check FFMPEG version and supported video formats.',
                'ordering' => $iIndex++
            ],
            'pf_video_mux_token_id' => [
                'var_name' => 'pf_video_mux_token_id',
                'info' => 'Mux Token ID',
                'type' => 'string',
                'description' => '<b>Mux access token</b> is required for Videos app if your are using Mux as Uploading Method. If you don\'t have one, just follow guide in this link: <a href="https://docs.mux.com/guides/video/stream-video-files#1-get-an-api-access-token" target="_blank">Get a Mux API access token</a>',
                'ordering' => $iIndex++
            ],
            'pf_video_mux_token_secret' => [
                'var_name' => 'pf_lv_mux_token_secret',
                'info' => 'Mux Token Secret',
                'description' => '<b>Mux Token Secret</b> is required for Videos app if your are using Mux as Uploading Method.',
                'type' => 'password',
                'ordering' => $iIndex++
            ],
            'pf_video_mux_webhook_signing_secret' => [
                'var_name' => 'pf_video_mux_webhook_signing_secret',
                'info' => 'Mux Webhook Signing Secret',
                'description' => '<b>Mux Webhook Signing Secret</b> is required for Videos app if your are using Mux as Uploading Method.<br/>You must add a webhook to your Mux project, if you doesn\'t have one, just go to this <a href="https://dashboard.mux.com/settings/webhooks">Link</a> and create a new webhook.<br/>Add URL <a href="'.\Phpfox::getLib('url')->makeUrl('video.mux-callback').'">'.\Phpfox::getLib('url')->makeUrl('video.mux-callback').'</a> to <b>URL to notify</b> field then save it.<br/>After you got a webhook, copy <b>Signing Secret</b> and paste to this setting.',
                'type' => 'password',
                'ordering' => $iIndex++
            ],
            'pf_video_allow_create_feed_when_add_new_item' => [
                'var_name' => 'video_allow_posting_on_main_feed',
                'info' => 'Allow posting on Main Feed',
                'description' => 'Allow posting on Main feed when adding a new video.',
                'type' => 'boolean',
                'value' => '1',
                'ordering' => $iIndex++
            ],
            'pf_video_allow_compile_on_storage_system' => [
                'var_name' => 'video_allow_compile_on_storage_system',
                'info' => 'Allow compile Video on external FFMPEG server',
                'description' => 'This feature only work with phpFox version >= 4.8.0. Follow our guide to setup your FFmpeg Server to compile video on this <a target="_blank" href="'. trim(str_replace('index.php', '', Phpfox_Url::instance()->makeUrl('')), '/') .'/PF.Site/Apps/core-videos/FFmpegServer/README.html">Link</a><br/>NOTICE: If this setting is enabled, we will prioritize video uploads to the External FFMPEG server and ignore all other "Uploading Methods". If you want to upload videos using: Mux, FFMPEG (local) or Zencoder + S3, you must disable this setting.',
                'type' => 'boolean',
                'value' => '0',
                'ordering' => $iIndex++
            ],
            'pf_video_setting_subject_video_is_ready' => [
                'var_name' => 'pf_video_setting_subject_video_is_ready',
                'info' => 'Videos - Email Subject - Your Video Is Ready',
                'description' => 'Email subject of the "Your Video Is Ready" notification. <a role="button" onclick="$Core.editMeta(\'email_your_video_title_is_ready\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_your_video_title_is_ready"></span>',
                'type' => '',
                'value' => '{_p var="email_your_video_title_is_ready"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_video_is_ready' => [
                'var_name' => 'pf_video_setting_content_video_is_ready',
                'info' => 'Videos - Email Content - Your Video Is Ready',
                'description' => 'Email content of the "Your Video Is Ready" notification. <a role="button" onclick="$Core.editMeta(\'your_video_title_is_ready_click_on_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_video_title_is_ready_click_on_link"></span>',
                'type' => '',
                'value' => '{_p var="your_video_title_is_ready_click_on_link"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_tagged_in_video' => [
                'var_name' => 'pf_video_setting_subject_tagged_in_video',
                'info' => 'Videos - Email Subject - Someone Tagged You In A Video',
                'description' => 'Email subject of the "Someone Tagged You In A Video" notification. <a role="button" onclick="$Core.editMeta(\'email_user_name_tagged_you_in_video_tittle\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="email_user_name_tagged_you_in_video_tittle"></span>',
                'type' => '',
                'value' => '{_p var="email_user_name_tagged_you_in_video_tittle"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_tagged_in_video' => [
                'var_name' => 'pf_video_setting_content_tagged_in_video',
                'info' => 'Videos - Email Content - Someone Tagged You In A Video',
                'description' => 'Email content of the "Someone Tagged You In A Video" notification. <a role="button" onclick="$Core.editMeta(\'user_name_tagged_you_in_video_tittle_link\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="user_name_tagged_you_in_video_tittle_link"></span>',
                'type' => '',
                'value' => '{_p var="user_name_tagged_you_in_video_tittle_link"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_post_video_on_wall' => [
                'var_name' => 'pf_video_setting_subject_post_video_on_wall',
                'info' => 'Videos - Email Subject - Someone Post A Video On Your Wall',
                'description' => 'Email subject of the "Someone Post A Video On Your Wall" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_video_on_your_wall\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_video_on_your_wall"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_video_on_your_wall"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_post_video_on_wall' => [
                'var_name' => 'pf_video_setting_content_post_video_on_wall',
                'info' => 'Videos - Email Content - Someone Post A Video On Your Wall',
                'description' => 'Email content of the "Someone Post A Video On Your Wall" notification. <a role="button" onclick="$Core.editMeta(\'full_name_posted_a_video_on_your_wall_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_posted_a_video_on_your_wall_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_posted_a_video_on_your_wall_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_liked_video' => [
                'var_name' => 'pf_video_setting_subject_liked_video',
                'info' => 'Videos - Email Subject - Someone Liked Your Video',
                'description' => 'Email subject of the "Someone Liked Your Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_video_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_video_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_video_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_liked_video' => [
                'var_name' => 'pf_video_setting_content_liked_video',
                'info' => 'Videos - Email Content - Someone Liked Your Video',
                'description' => 'Email content of the "Someone Liked Your Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_video_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_video_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_video_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_liked_video_on_wall' => [
                'var_name' => 'pf_video_setting_subject_liked_video_on_wall',
                'info' => 'Videos - Email Subject - Someone Liked A Video On Your Wall',
                'description' => 'Email subject of the "Someone Liked A Video On Your Wall" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_a_video_title_on_your_wall\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_a_video_title_on_your_wall"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_a_video_title_on_your_wall"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_liked_video_on_wall' => [
                'var_name' => 'pf_video_setting_content_liked_video_on_wall',
                'info' => 'Videos - Email Content - Someone Liked A Video On Your Wall',
                'description' => 'Email content of the "Someone Liked A Video On Your Wall" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_a_video_title_on_your_wall_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_a_video_title_on_your_wall_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_a_video_title_on_your_wall_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_commented_on_their_own_video' => [
                'var_name' => 'pf_video_setting_subject_commented_on_their_own_video',
                'info' => 'Videos - Email Subject - Someone Commented On Their Own Video',
                'description' => 'Email subject of the "Someone Commented On Their Own Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_video\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_video"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_video"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_commented_on_their_own_video' => [
                'var_name' => 'pf_video_setting_content_commented_on_their_own_video',
                'info' => 'Videos - Email Content - Someone Commented On Their Own Video',
                'description' => 'Email content of the "Someone Commented On Their Own Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_video_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_video_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_video_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_commented_on_other_video' => [
                'var_name' => 'pf_video_setting_subject_commented_on_other_video',
                'info' => 'Videos - Email Subject - Someone Commented On Other\'s Video',
                'description' => 'Email subject of the "Someone Commented On Other\'s Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_video_full_name_s_video\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_video_full_name_s_video"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_video_full_name_s_video"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_commented_on_other_video' => [
                'var_name' => 'pf_video_setting_content_commented_on_other_video',
                'info' => 'Videos - Email Content - Someone Commented On Other\'s Video',
                'description' => 'Email content of the "Someone Commented On Other\'s Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_video_full_name_s_video_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_video_full_name_s_video_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_video_full_name_s_video_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_commented_on_your_video' => [
                'var_name' => 'pf_video_setting_subject_commented_on_your_video',
                'info' => 'Videos - Email Subject - Someone Commented On Your Video',
                'description' => 'Email subject of the "Someone Commented On Your Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_video_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_video_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_video_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_commented_on_your_video' => [
                'var_name' => 'pf_video_setting_content_commented_on_your_video',
                'info' => 'Videos - Email Content - Someone Commented On Your Video',
                'description' => 'Email content of the "Someone Commented On Your Video" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_video_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_video_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_video_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_converted_old_videos' => [
                'var_name' => 'pf_video_setting_subject_converted_old_videos',
                'info' => 'Videos - Email Subject - Old Videos Were Converted',
                'description' => 'Email subject of the "Old Videos Were Converted" notification. <a role="button" onclick="$Core.editMeta(\'videos_converted\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="videos_converted"></span>',
                'type' => '',
                'value' => '{_p var="videos_converted"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_converted_old_videos' => [
                'var_name' => 'pf_video_setting_content_converted_old_videos',
                'info' => 'Videos - Email Content - Old Videos Were Converted',
                'description' => 'Email content of the "Old Videos Were Converted" notification. <a role="button" onclick="$Core.editMeta(\'all_old_videos_feed_video_converted_new_videos\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="all_old_videos_feed_video_converted_new_videos"></span>',
                'type' => '',
                'value' => '{_p var="all_old_videos_feed_video_converted_new_videos"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_subject_video_is_approved' => [
                'var_name' => 'pf_video_setting_subject_video_is_approved',
                'info' => 'Videos - Email Subject - Video Is Approved',
                'description' => 'Email subject of the "Video Is Approved" notification. <a role="button" onclick="$Core.editMeta(\'your_video_has_been_approved_on_site_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_video_has_been_approved_on_site_title"></span>',
                'type' => '',
                'value' => '{_p var="your_video_has_been_approved_on_site_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'pf_video_setting_content_video_is_approved' => [
                'var_name' => 'pf_video_setting_content_video_is_approved',
                'info' => 'Videos - Email Content - Video Is Approved',
                'description' => 'Email content of the "Video Is Approved" notification. <a role="button" onclick="$Core.editMeta(\'your_video_has_been_approved_on_site_title_n_nto_view_this_video_follow_the_link_below_n_a_href\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_video_has_been_approved_on_site_title_n_nto_view_this_video_follow_the_link_below_n_a_href"></span>',
                'type' => '',
                'value' => '{_p var="your_video_has_been_approved_on_site_title_n_nto_view_this_video_follow_the_link_below_n_a_href"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'pf_video_total_videos_upload' => [
                'var_name'    => 'pf_video_total_videos_upload',
                'info'        => 'Maximum number of videos',
                'description' => 'Define the total number of videos a user within this user group can upload. Notice: Leave this empty will allow them to upload an unlimited amount of videos. Setting this value to 0 will not allow them the ability to upload videos.',
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
            'pf_video_share' => [
                'var_name' => 'pf_video_share',
                'info' => 'Can share/upload a video?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_file_size' => [
                'var_name' => 'pf_video_file_size',
                'info' => 'Maximum file size of video uploaded (MB)?',
                'type' => Setting\Groups::TYPE_TEXT,
                'value' => '10'
            ],
            'pf_video_max_file_size_photo_upload' => [
                'var_name' => 'pf_video_max_file_size_photo_upload',
                'info' => 'Maximum file size of photo uploaded (KB)?',
                'type' => Setting\Groups::TYPE_TEXT,
                'value' => '8192'
            ],
            'pf_video_view' => [
                'var_name' => 'pf_video_view',
                'info' => 'Can browse and view videos?',
                'type' => 'input:radio',
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '1',
                    '4' => '1',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_comment' => [
                'var_name' => 'pf_video_comment',
                'info' => 'Can add a comment on a video?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_edit_own_video' => [
                'var_name' => 'pf_edit_own_video',
                'info' => 'Can edit own videos?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_edit_all_video' => [
                'var_name' => 'pf_edit_all_video',
                'info' => 'Can edit all videos?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_delete_own_video' => [
                'var_name' => 'pf_delete_own_video',
                'info' => 'Can delete own videos?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_delete_all_video' => [
                'var_name' => 'pf_delete_all_video',
                'info' => 'Can delete all videos?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'can_sponsor_v' => [
                'var_name' => 'pf_video_can_sponsor',
                'info' => 'Can members of this user group mark a video as Sponsor without paying fee?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'can_purchase_sponsor' => [
                'var_name' => 'pf_video_can_purchase_sponsor',
                'info' => 'Can members of this user group purchase a sponsored ad space for their items?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'v_sponsor_price' => [
                'var_name' => 'pf_sponsor_price',
                'info' => 'How much is the sponsor space worth for videos? This works in a CPM basis.',
                'type' => Setting\Groups::TYPE_CURRENCY
            ],
            'auto_publish_sponsored_item' => [
                'var_name' => 'pf_purchase_sponsored_ad_space_have_to_approve',
                'info' => 'After the user has purchased a sponsored space, should the item be published right away? 
If set to No, the admin will have to approve each new purchased sponsored item space before it is shown in the site.',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'points_v' => [
                'var_name' => 'pf_video_activity_point',
                'info' => 'How many activity points should a user receive for sharing a video?',
                'type' => 'input:text',
                'value' => '1'
            ],
            'pf_video_feature' => [
                'var_name' => 'pf_video_feature',
                'info' => 'Can feature videos?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_approve' => [
                'var_name' => 'pf_video_approve',
                'info' => 'Can approve videos?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ],
            'pf_video_approve_before_publicly' => [ // need approve?
                'var_name' => 'pf_approve_before_publicly',
                'info' => 'Videos must be approved first before they are displayed publicly?',
                'type' => Setting\Groups::TYPE_RADIO,
                'value' => [
                    '1' => '0',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'options' => Setting\Groups::$OPTION_YES_NO
            ]
        ];
    }

    protected function setComponent()
    {
        $this->component = [
            'block' => [
                'category' => '',
                'featured' => '',
                'sponsored' => '',
                'suggested' => '',
            ],
            'controller' => [
                'index' => 'v.index',
                'play' => 'v.play'
            ]
        ];
    }

    protected function setComponentBlock()
    {
        $this->component_block = [
            'Categories' => [
                'type_id' => '0',
                'm_connection' => 'v.index',
                'component' => 'category',
                'location' => '1',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Featured Videos' => [
                'type_id' => '0',
                'm_connection' => 'v.index',
                'component' => 'featured',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ],
            'Sponsored Videos' => [
                'type_id' => '0',
                'm_connection' => 'v.index',
                'component' => 'sponsored',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '2',
            ],
            'Suggested Videos' => [
                'type_id' => '0',
                'm_connection' => 'v.play',
                'component' => 'suggested',
                'location' => '3',
                'is_active' => '1',
                'ordering' => '1',
            ]
        ];
    }

    protected function setPhrase()
    {
        $this->addPhrases($this->_app_phrases);
    }

    protected function setOthers()
    {
        $this->notifications = [
            'video_ready' => [
                'message' => 'your_video_is_ready',
                'url' => '/video/play/:id',
                'icon' => 'fa-video-camera'
            ]
        ];
        $this->admincp_route = '/v/admincp';
        $this->admincp_menu = [
            'Categories' => '#',
            'FFMPEG Video Utilities' => 'v.utilities',
            'Convert Old Videos' => 'v.convert',
        ];
        $this->admincp_help = 'https://docs.phpfox.com/display/FOX4MAN/Setting+Up+the+Video+App';
        $this->admincp_action_menu = [
            '/admincp/v/add-category' => 'New Category'
        ];
        $this->map = [];
        $this->menu = [
            'phrase_var_name' => 'menu_videos',
            'url' => 'v',
            'icon' => 'video-camera'
        ];
        $this->database = [
            'Video',
            'Video_Category',
            'Video_Category_Data',
            'Video_Text',
            'Video_Embed',
        ];
        $this->_writable_dirs = [
            'PF.Base/file/pic/video/',
            'PF.Base/file/video/'
        ];
        $this->_admin_cp_menu_ajax = false;
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
        $this->_apps_dir = 'core-videos';
    }
}
