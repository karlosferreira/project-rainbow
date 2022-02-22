<?php

namespace Apps\PHPfox_Videos\Installation\Version;

use Phpfox;
use Phpfox_Url;

class v4710
{
    public function process()
    {
        Phpfox::getService('language.phrase.process')->updatePhrases([
            'setting_v_pf_video_allow_compile_on_storage_system' => '<title>Allow compile Video on external FFMPEG server</title><info>This feature only work with phpFox version >= 4.8.0. Follow our guide to setup your FFmpeg Server to compile video on this <a target="_blank" href="'. trim(str_replace('index.php', '', Phpfox_Url::instance()->makeUrl('')), '/') .'/PF.Site/Apps/core-videos/FFmpegServer/README.html">Link</a><br/>NOTICE: If this setting is enabled, we will prioritize video uploads to the External FFMPEG server and ignore all other "Uploading Methods". If you want to upload videos using: Mux, FFMPEG (local) or Zencoder + S3, you must disable this setting.</info>',
            'setting_v_pf_video_support_upload_video' => '<title>Enable Uploading of Videos</title><info>Enable this option if you would like to give users the ability to upload videos from their computer. <br/><i><b>Notice:</b> This feature requires that Mux, FFMPEG, (ZenCoder/Amazon S3) or External FFMPEG Server be installed. Once you attempt to enable this feature the script will attempt to verify if the server has all the required scripts installed.</i></info>'
        ]);
    }
}
