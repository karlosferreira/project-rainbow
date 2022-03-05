<?php
if (Phpfox::getParam('v.pf_video_allow_create_feed_when_add_new_item') && Phpfox::getUserParam('v.pf_video_view')) {
    $video_phrases = [
        'video' => _p('Video'),
        'say' => _p('say_something_about_this_video'),
        'uploading' => _p('Uploading...'),
        'no_friends_found' => _p('no_friends_found'),
        'share' => \_p('share')
    ];

    $sData .= '<script>var v_phrases = ' . json_encode($video_phrases) . ';</script>';
    if (Phpfox::isAppActive('PHPfox_Videos')) {
        $val = user('pf_video_share', 1);
        $val = ($val) ? 1 : 0;
        $bCanCheckIn = Phpfox::getService('v.video')->canCheckInInFeed();

        $sData .= '<script>window.can_post_video = ' . (Phpfox::getService('v.video')->checkLimitation() ? 1 : 0) . ';</script>';
        $sData .= '<script>window.can_post_video_on_profile = ' . $val . ';</script>';
        $sData .= '<script>window.can_checkin_in_video = ' . $bCanCheckIn . ';</script>';
    }
    $appId = Phpfox::getParam('link.facebook_app_id');
    $sData .= '<script>v_facebook_app_id = "' . $appId . '";</script>';
    $sData .= '<div id="fb-root"></div><script crossorigin="anonymous" async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0"></script>';
}
