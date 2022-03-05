<?php

namespace Apps\Core_Music;

use Core\App;


/**
 * Class Install
 * @package Apps\Core_Music
 */
class Install extends App\App
{
    private $_app_phrases = [

    ];

    public $store_id = 1838;

    protected function setId()
    {
        $this->id = 'Core_Music';
    }

    protected function setAlias()
    {
        $this->alias = 'music';
    }

    protected function setName()
    {
        $this->name = _p('Music');
    }

    protected function setVersion()
    {
        $this->version = '4.6.8';
    }

    protected function setSupportVersion()
    {
        $this->start_support_version = '4.7.8';
    }

    protected function setSettings()
    {
        $iIndex = 1;
        $this->settings = [
            'music_paging_mode'                         => [
                'var_name'    => 'music_paging_mode',
                'info'        => 'Pagination Style',
                'description' => 'Select Pagination Style at Search Page.',
                'type'        => 'select',
                'value'       => 'loadmore',
                'options'     => [
                    'loadmore'   => 'Scrolling down to Load More items',
                    'next_prev'  => 'Use Next and Pre buttons',
                    'pagination' => 'Use Pagination with page number'
                ],
                'ordering'    => $iIndex
            ],
            'music_display_music_created_in_group'      => [
                'var_name'    => 'music_display_music_created_in_group',
                'info'        => 'Display music which created in Group to the Music app',
                'description' => 'Enable to display all public music created in Group to the Music app. Disable to hide them.',
                'type'        => 'boolean',
                'value'       => 0,
                'ordering'    => $iIndex++
            ],
            'music_display_music_created_in_page'       => [
                'var_name'    => 'music_display_music_created_in_page',
                'info'        => 'Display music which created in Page to the Music app',
                'description' => 'Enable to display all public music created in Page to the Music app. Disable to hide them.',
                'type'        => 'boolean',
                'value'       => 0,
                'ordering'    => $iIndex++
            ],
            'music_meta_description'                    => [
                'var_name'    => 'music_meta_description',
                'info'        => 'Music Meta Description',
                'description' => 'Meta description added to pages related to the Music app. <a role="button" onclick="$Core.editMeta(\'seo_music_meta_description\', true)">Click here</a> to edit meta description.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_music_meta_description"></span>',
                'type'        => '',
                'value'       => '{_p var=\'seo_music_meta_description\'}',
                'group_id'    => 'seo',
                'ordering'    => $iIndex++
            ],
            'music_meta_keywords'                       => [
                'var_name'    => 'music_meta_keywords',
                'info'        => 'Music Meta Keywords',
                'description' => 'Meta keywords that will be displayed on sections related to the Music app. <a role="button" onclick="$Core.editMeta(\'seo_music_meta_keywords\', true)">Click here</a> to edit meta keywords.<span style="float:right;">(SEO) <input style="width:150px;" readonly value="seo_music_meta_keywords"></span>',
                'type'        => '',
                'value'       => '{_p var=\'seo_music_meta_keywords\'}',
                'group_id'    => 'seo',
                'ordering'    => $iIndex++
            ],
            'music_allow_create_feed_when_add_new_item' => [
                'var_name'    => 'music_allow_posting_on_main_feed',
                'info'        => 'Allow posting on Main Feed',
                'description' => 'Allow posting on Main feed when adding a new song/album/playlist.',
                'type'        => 'boolean',
                'value'       => '1',
                'ordering'    => $iIndex++
            ],
            'music_setting_subject_approved_song' => [
                'var_name' => 'music_setting_subject_approved_song',
                'info' => 'Music Song - Email Subject - Song Is Approved',
                'description' => 'Email subject of the "Song Is Approved" notification. <a role="button" onclick="$Core.editMeta(\'your_song_title_has_been_approved_on_site_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_song_title_has_been_approved_on_site_title"></span>',
                'type' => '',
                'value' => '{_p var="your_song_title_has_been_approved_on_site_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_approved_song' => [
                'var_name' => 'music_setting_content_approved_song',
                'info' => 'Music Song - Email Content - Song Is Approved',
                'description' => 'Email content of the "Song Is Approved" notification. <a role="button" onclick="$Core.editMeta(\'your_song_title_has_been_approved_on_site_title_to_view_this_song\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="your_song_title_has_been_approved_on_site_title_to_view_this_song"></span>',
                'type' => '',
                'value' => '{_p var="your_song_title_has_been_approved_on_site_title_to_view_this_song"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_liked_song' => [
                'var_name' => 'music_setting_subject_liked_song',
                'info' => 'Music Song - Email Subject - Someone Liked Your Song',
                'description' => 'Email subject of the "Someone Liked Your Song" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_song_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_song_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_song_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_liked_song' => [
                'var_name' => 'music_setting_content_liked_song',
                'info' => 'Music Song - Email Content - Someone Liked Your Song',
                'description' => 'Email content of the "Someone Liked Your Song" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_song_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_song_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_song_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_your_song' => [
                'var_name' => 'music_setting_subject_commented_on_your_song',
                'info' => 'Music Song - Email Subject - Someone Commented On Your Song',
                'description' => 'Email subject of the "Someone Commented On Your Song" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_song_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_song_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_song_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_your_song' => [
                'var_name' => 'music_setting_content_commented_on_your_song',
                'info' => 'Music Song - Email Content - Someone Commented On Your Song',
                'description' => 'Email content of the "Someone Commented On Your Song" notification. <a role="button" onclick="$Core.editMeta(\'name_commented_on_your_song\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="name_commented_on_your_song"></span>',
                'type' => '',
                'value' => '{_p var="name_commented_on_your_song"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_their_own_song' => [
                'var_name' => 'music_setting_subject_commented_on_their_own_song',
                'info' => 'Music Song - Email Subject - Someone Commented On Their Own Song',
                'description' => 'Email subject of the "Someone Commented On Their Own Song" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_song\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_song"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_song"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_their_own_song' => [
                'var_name' => 'music_setting_content_commented_on_their_own_song',
                'info' => 'Music Song - Email Content - Someone Commented On Their Own Song',
                'description' => 'Email content of the "Someone Commented On Their Own Song" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_song_a_href_link_title_a_to_see_the_comment_thread_folow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_song_a_href_link_title_a_to_see_the_comment_thread_folow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_song_a_href_link_title_a_to_see_the_comment_thread_folow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_other_song' => [
                'var_name' => 'music_setting_subject_commented_on_other_song',
                'info' => 'Music Song - Email Subject - Someone Commented On Other\'s Song',
                'description' => 'Email subject of the "Someone Commented On Other\'s Song" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_song\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_song"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_song"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_other_song' => [
                'var_name' => 'music_setting_content_commented_on_other_song',
                'info' => 'Music Song - Email Content - Someone Commented On Other\'s Song',
                'description' => 'Email content of the "Someone Commented On Other\'s Song" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_names_song\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_names_song"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_names_song"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_liked_album' => [
                'var_name' => 'music_setting_subject_liked_album',
                'info' => 'Music Album - Email Subject - Someone Liked Your Album',
                'description' => 'Email subject of the "Someone Liked Your Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_album_name\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_album_name"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_album_name"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_liked_album' => [
                'var_name' => 'music_setting_content_liked_album',
                'info' => 'Music Album - Email Content - Someone Liked Your Album',
                'description' => 'Email content of the "Someone Liked Your Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_album_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_album_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_album_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_your_album' => [
                'var_name' => 'music_setting_subject_commented_on_your_album',
                'info' => 'Music Album - Email Subject - Someone Commented On Your Album',
                'description' => 'Email subject of the "Someone Commented On Your Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_album_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_album_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_album_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_your_album' => [
                'var_name' => 'music_setting_content_commented_on_your_album',
                'info' => 'Music Album - Email Content - Someone Commented On Your Album',
                'description' => 'Email content of the "Someone Commented On Your Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_album_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_album_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_album_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_their_own_album' => [
                'var_name' => 'music_setting_subject_commented_on_their_own_album',
                'info' => 'Music Album - Email Subject - Someone Commented Their Own Album',
                'description' => 'Email subject of the "Someone Commented Their Own Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_album\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_album"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_album"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_their_own_album' => [
                'var_name' => 'music_setting_content_commented_on_their_own_album',
                'info' => 'Music Album - Email Content - Someone Commented Their Own Album',
                'description' => 'Email content of the "Someone Commented Their Own Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_other_album' => [
                'var_name' => 'music_setting_subject_commented_on_other_album',
                'info' => 'Music Album - Email Subject - Someone Commented Other\'s Album',
                'description' => 'Email subject of the "Someone Commented Other\'s Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_album\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_album"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_album"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_other_album' => [
                'var_name' => 'music_setting_content_commented_on_other_album',
                'info' => 'Music Album - Email Content - Someone Commented Other\'s Album',
                'description' => 'Email content of the "Someone Commented Other\'s Album" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_album_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_liked_playlist' => [
                'var_name' => 'music_setting_subject_liked_playlist',
                'info' => 'Music Playlist - Email Subject - Someone Liked Your Playlist',
                'description' => 'Email subject of the "Someone Liked Your Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_music_playlist_name\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_music_playlist_name"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_music_playlist_name"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_liked_playlist' => [
                'var_name' => 'music_setting_content_liked_playlist',
                'info' => 'Music Playlist - Email Content - Someone Liked Your Playlist',
                'description' => 'Email content of the "Someone Liked Your Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_liked_your_music_playlist_message\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_liked_your_music_playlist_message"></span>',
                'type' => '',
                'value' => '{_p var="full_name_liked_your_music_playlist_message"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_your_playlist' => [
                'var_name' => 'music_setting_subject_commented_on_your_playlist',
                'info' => 'Music Playlist - Email Subject - Someone Commented On Your Playlist',
                'description' => 'Email subject of the "Someone Commented On Your Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_music_playlist_title\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_music_playlist_title"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_music_playlist_title"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_your_playlist' => [
                'var_name' => 'music_setting_content_commented_on_your_playlist',
                'info' => 'Music Playlist - Email Content - Someone Commented On Your Playlist',
                'description' => 'Email content of the "Someone Commented On Your Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_your_music_playlist_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_your_music_playlist_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_your_music_playlist_a_href_link_title_a_to_see_the_commented_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_their_own_playlist' => [
                'var_name' => 'music_setting_subject_commented_on_their_own_playlist',
                'info' => 'Music Playlist - Email Subject - Someone Commented On Their Own Playlist',
                'description' => 'Email subject of the "Someone Commented On Their Own Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_music_playlist\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_music_playlist"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_music_playlist"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_their_own_playlist' => [
                'var_name' => 'music_setting_content_commented_on_their_own_playlist',
                'info' => 'Music Playlist - Email Content - Someone Commented On Their Own Playlist',
                'description' => 'Email content of the "Someone Commented On Their Own Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_gender_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_gender_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_gender_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_subject_commented_on_other_playlist' => [
                'var_name' => 'music_setting_subject_commented_on_other_playlist',
                'info' => 'Music Playlist - Email Subject - Someone Commented On Other\'s Playlist',
                'description' => 'Email subject of the "Someone Commented On Other\'s Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_music_playlist\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_music_playlist"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_music_playlist"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ],
            'music_setting_content_commented_on_other_playlist' => [
                'var_name' => 'music_setting_content_commented_on_other_playlist',
                'info' => 'Music Playlist - Email Content - Someone Commented On Other\'s Playlist',
                'description' => 'Email content of the "Someone Commented On Other\'s Playlist" notification. <a role="button" onclick="$Core.editMeta(\'full_name_commented_on_other_full_name_s_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a\', true)">Click here</a> to edit.<span style="float:right;">(Email) <input style="width:150px;" readonly value="full_name_commented_on_other_full_name_s_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"></span>',
                'type' => '',
                'value' => '{_p var="full_name_commented_on_other_full_name_s_music_playlist_a_href_link_user_name_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a"}',
                'group_id' => 'email',
                'ordering' => $iIndex++,
            ]
        ];
        unset($iIndex);
    }

    protected function setUserGroupSettings()
    {
        $this->user_group_settings = [
            'can_upload_music_public'           => [
                'var_name'    => 'can_upload_music_public',
                'info'        => 'Can upload music?',
                'description' => 'Notice: This will allow this user group the right to upload songs to the public music section.',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 1,
            ],
            'can_add_comment_on_music_album'    => [
                'var_name'    => 'can_add_comment_on_music_album',
                'info'        => 'Can add comments on music albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 2,
            ],
            'can_add_comment_on_music_song'     => [
                'var_name'    => 'can_add_comment_on_music_song',
                'info'        => 'Can add a comment on a song?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 3,
            ],
            'can_add_music_album'               => [
                'var_name'    => 'can_add_music_album',
                'info'        => 'Can add new music album?',
                'description' => 'Notice: This will allow this user group the right to add music albums to the public music section.',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 4,
            ],
            'can_edit_other_music_albums'       => [
                'var_name'    => 'can_edit_other_music_albums',
                'info'        => 'Can edit all music albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 5,
            ],
            'can_edit_own_albums'               => [
                'var_name'    => 'can_edit_own_music_albums',
                'info'        => 'Can edit own music albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 6,
            ],
            'can_edit_other_song'               => [
                'var_name'    => 'can_edit_other_song',
                'info'        => 'Can edit all songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 7,
            ],
            'can_edit_own_song'                 => [
                'var_name'    => 'can_edit_own_song',
                'info'        => 'Can edit own songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 8,
            ],
            'can_delete_own_track'              => [
                'var_name'    => 'can_delete_own_track',
                'info'        => 'Can delete own songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 9,
            ],
            'can_delete_other_tracks'           => [
                'var_name'    => 'can_delete_other_tracks',
                'info'        => 'Can delete all songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 10,
            ],
            'can_delete_own_music_album'        => [
                'var_name'    => 'can_delete_own_music_album',
                'info'        => 'Can delete own music albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 11,
            ],
            'can_delete_other_music_albums'     => [
                'var_name'    => 'can_delete_other_music_albums',
                'info'        => 'Can delete all music albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 12,
            ],
            'music_max_file_size'               => [
                'var_name'    => 'music_max_file_size',
                'info'        => 'Maximum file size of songs uploaded',
                'description' => 'Max file size for songs upload in megabyte (MB). (1MB = 1000kb) 
For unlimited add "0" without quotes.',
                'type'        => 'integer',
                'value'       => [
                    '1' => '10',
                    '2' => '10',
                    '3' => '10',
                    '4' => '10',
                    '5' => '10'
                ],
                'ordering'    => 13,
            ],
            'can_feature_songs'                 => [
                'var_name'    => 'can_feature_songs',
                'info'        => 'Can feature songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 14,
            ],
            'can_approve_songs'                 => [
                'var_name'    => 'can_approve_songs',
                'info'        => 'Can approve songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 15,
            ],
            'music_song_approval'               => [
                'var_name'    => 'music_song_approval',
                'info'        => 'Songs must be approved first before they are displayed publicly?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '0',
                    '2' => '0',
                    '3' => '1',
                    '4' => '0',
                    '5' => '1'
                ],
                'ordering'    => 16,
            ],
            'can_feature_music_albums'          => [
                'var_name'    => 'can_feature_music_albums',
                'info'        => 'Can feature music albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 17,
            ],
            'can_access_music'                  => [
                'var_name'    => 'can_access_music',
                'info'        => 'Can browse and view the music app?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '1',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 18,
            ],
            'can_sponsor_song'                  => [
                'var_name'    => 'can_sponsor_song',
                'info'        => 'Can members of this user group mark a song as Sponsor without paying fee?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 19,
            ],
            'can_purchase_sponsor_song'         => [
                'var_name'    => 'can_purchase_sponsor_song',
                'info'        => 'Can members of this user group purchase a sponsored ad space for their songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 20,
            ],
            'music_song_sponsor_price'          => [
                'var_name'    => 'music_song_sponsor_price',
                'info'        => 'How much is the sponsor space worth for music songs? This works in a CPM basis.',
                'description' => '',
                'type'        => 'currency',
                'value'       => ['USD' => 0,],
                'ordering'    => 21,
            ],
            'auto_publish_sponsored_song'       => [
                'var_name'    => 'auto_publish_sponsored_song',
                'info'        => 'Auto publish sponsored song?',
                'description' => 'After the user has purchased a sponsored space, should the song be published right away? 
If set to No, the admin will have to approve each new purchased sponsored song space before it is shown in the site.',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 22,
            ],
            'can_sponsor_album'                 => [
                'var_name'    => 'can_sponsor_music_album',
                'info'        => 'Can members of this user group mark a music album as Sponsor without paying the fee?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 23,
            ],
            'can_purchase_sponsor_album'        => [
                'var_name'    => 'can_purchase_sponsor_music_album',
                'info'        => 'Can members of this user group purchase a sponsored ad space for their music albums?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 24,
            ],
            'music_album_sponsor_price'         => [
                'var_name'    => 'music_album_sponsor_price',
                'info'        => 'How much is the sponsor space worth for music albums? This works in a CPM basis.',
                'description' => '',
                'type'        => 'currency',
                'value'       => ['USD' => 0,],
                'ordering'    => 25,
            ],
            'auto_publish_sponsored_album'      => [
                'var_name'    => 'auto_publish_sponsored_music_album',
                'info'        => 'Auto publish sponsored music album?',
                'description' => 'After the user has purchased a sponsored space, should the music album be published right away? 
If set to No, the admin will have to approve each new purchased sponsored music album space before it is shown in the site.',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '0',
                    '5' => '0'
                ],
                'ordering'    => 26,
            ],
            'points_music_song'                 => [
                'var_name'    => 'points_music_song',
                'info'        => 'Activity points',
                'description' => 'How many activity points should a user receive for uploading a song?',
                'type'        => 'integer',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 27,
            ],
            'max_songs_per_upload'              => [
                'var_name'    => 'max_songs_per_upload',
                'info'        => 'Maximum number of songs per upload',
                'description' => 'Define the maximum number of songs a user can upload each time they use the upload form. 
Notice: This setting does not control how many songs a user can upload in total, just how many they can upload each time they use the upload form to upload new songs.',
                'type'        => 'integer',
                'value'       => [
                    '1' => '10',
                    '2' => '10',
                    '3' => '0',
                    '4' => '10',
                    '5' => '10'
                ],
                'ordering'    => 28
            ],
            'can_download_songs'                => [
                'var_name'    => 'can_download_songs',
                'info'        => 'Can download songs?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 29,
            ],
            'can_add_comment_on_music_playlist' => [
                'var_name'    => 'can_add_comment_on_music_playlist',
                'info'        => 'Can add comments on music playlists?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 30,
            ],
            'can_add_music_playlist'            => [
                'var_name'    => 'can_add_music_playlist',
                'info'        => 'Can add new playlist?',
                'description' => 'Notice: This will allow this user group the right to add playlists to the public music section.',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 31,
            ],
            'can_edit_other_music_playlists'    => [
                'var_name'    => 'can_edit_other_music_playlists',
                'info'        => 'Can edit all playlists?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 32,
            ],
            'can_edit_own_playlists'            => [
                'var_name'    => 'can_edit_own_playlists',
                'info'        => 'Can edit own playlists?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 33,
            ],
            'can_delete_own_music_playlist'     => [
                'var_name'    => 'can_delete_own_music_playlist',
                'info'        => 'Can delete own playlists?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '1',
                    '3' => '0',
                    '4' => '1',
                    '5' => '1'
                ],
                'ordering'    => 34,
            ],
            'can_delete_other_music_playlists'  => [
                'var_name'    => 'can_delete_other_music_playlists',
                'info'        => 'Can delete all playlists?',
                'description' => '',
                'type'        => 'boolean',
                'value'       => [
                    '1' => '1',
                    '2' => '0',
                    '3' => '0',
                    '4' => '1',
                    '5' => '0'
                ],
                'ordering'    => 35,
            ],
            'max_music_song_created' => [
                'var_name' => 'max_music_song_created',
                'info' => 'The maximum number of music songs that members of this user group can create. For unlimited add "0" without quotes',
                'type' => 'integer',
                'value' => 0,
                'ordering' => 36,
            ],
            'max_music_album_created' => [
                'var_name' => 'max_music_album_created',
                'info' => 'The maximum number of music albums that members of this user group can create. For unlimited add "0" without quotes',
                'type' => 'integer',
                'value' => 0,
                'ordering' => 36,
            ],
            'max_music_playlist_created' => [
                'var_name' => 'max_music_playlist_created',
                'info' => 'The maximum number of music playlists that members of this user group can create. For unlimited add "0" without quotes',
                'type' => 'integer',
                'value' => 0,
                'ordering' => 36,
            ],
        ];
    }

    protected function setComponent()
    {
        $this->component = [
            'block'      => [
                'song'            => '',
                'list'            => '',
                'genre'           => '',
                'sponsored-song'  => '',
                'sponsored-album' => '',
                'featured'        => '',
                'featured-album'  => '',
                'new-album'       => '',
                'photo'           => '',
                'track'           => '',
                'suggestion'      => '',
                'related-album'   => '',
                'other-playlist'  => ''
            ],
            'controller' => [
                'index'           => 'music.index',
                'view'            => 'music.view',
                'view-album'      => 'music.view-album',
                'browse'          => 'music.browse',
                'browse.song'     => 'music.browse.song',
                'browse.album'    => 'music.browse.album',
                'album'           => 'music.album',
                'profile'         => 'music.profile',
                'browse.playlist' => 'music.browse.playlist',
                'view-playlist'   => 'music.view-playlist'
            ]
        ];
    }

    protected function setComponentBlock()
    {
        $this->component_block = [
            'Sponsored Songs'          => [
                'type_id'      => '0',
                'm_connection' => 'music.index',
                'component'    => 'sponsored-song',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Genres Index'             => [
                'type_id'      => '0',
                'm_connection' => 'music.index',
                'component'    => 'list',
                'location'     => '1',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Genres Browse Song'       => [
                'type_id'      => '0',
                'm_connection' => 'music.browse.song',
                'component'    => 'list',
                'location'     => '1',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Genres View'              => [
                'type_id'      => '0',
                'm_connection' => 'music.view',
                'component'    => 'list',
                'location'     => '1',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'New Albums'               => [
                'type_id'      => '0',
                'm_connection' => 'music.index',
                'component'    => 'new-album',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Featured Songs'           => [
                'type_id'      => '0',
                'm_connection' => 'music.index',
                'component'    => 'featured',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '3',
            ],
            'Suggestion'               => [
                'type_id'      => '0',
                'm_connection' => 'music.view',
                'component'    => 'suggestion',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Manage Tracks for Albums' => [
                'type_id'      => '0',
                'm_connection' => 'music.album',
                'component'    => 'track',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Sponsored Albums'         => [
                'type_id'      => '0',
                'm_connection' => 'music.browse.album',
                'component'    => 'sponsored-album',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Featured Albums'          => [
                'type_id'      => '0',
                'm_connection' => 'music.browse.album',
                'component'    => 'featured-album',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Album Tracklist'          => [
                'type_id'      => '0',
                'm_connection' => 'music.view-album',
                'component'    => 'track',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '1',
            ],
            'Related Albums'           => [
                'type_id'      => '0',
                'm_connection' => 'music.view-album',
                'component'    => 'related-album',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Latest Songs'             => [
                'type_id'      => '0',
                'm_connection' => 'profile.index',
                'component'    => 'song',
                'location'     => '3',
                'is_active'    => '1',
                'ordering'     => '2',
            ],
            'Other Playlist'           => [
                'type_id'      => '0',
                'm_connection' => 'music.view-playlist',
                'component'    => 'other-playlist',
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
        $this->admincp_route = '/music/admincp';
        $this->admincp_menu = [
            'Manage Genres' => '#',
        ];
        $this->admincp_action_menu = [
            '/admincp/music/add' => 'Add Genre'
        ];
        $this->map = [];
        $this->menu = [
            'phrase_var_name' => 'menu_music',
            'url'             => 'music',
            'icon'            => 'music'
        ];
        $this->database = [
            'Music_Album',
            'Music_Album_Text',
            'Music_Genre',
            'Music_Profile',
            'Music_Song',
            'Music_Genre_Data',
            'Music_Feed',
            'Music_Playlist',
            'Music_Playlist_Data'
        ];
        $this->_writable_dirs = [
            'PF.Base/file/music/',
            'PF.Base/file/pic/music/'
        ];
        $this->_apps_dir = 'core-music';
        $this->_admin_cp_menu_ajax = false;
        $this->_publisher = 'phpFox';
        $this->_publisher_url = 'http://store.phpfox.com/';
    }
}