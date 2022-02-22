<?php

defined('PHPFOX') or exit('NO DICE!');

/**
 * If your phpFox site is using an external Storage System (not Local storage), you don't need to input values for following properties:
 * - host
 * - port
 * - username
 * - password
 * - root
 * - base_url
 */

$_aParams = [
    "host"              => "Replace with host of your phpFox server",
    "port"              => "Replace with port of your phpFox server",
    "username"          => "Replace with username to login to your phpFox server",
    "password"          => "Replace with password to login to your phpFox server",
    "root"              => "Replace with path to folder \"PF.Base/file/\" in your phpFox server. Example: /public_html/phpfox/PF.Base/file",
    "base_url"          => "Replace with your phpFox site domain. Example: https://phpfox.com",
    "callback_url"      => "<Your phpFox site domain>/v/compile-callback . Example: https://phpfox.com/v/complie-callback",
    "ffmpeg_path"       => "Path to execute FFmpeg in your FFmpeg server. Example: /usr/bin/ffmpeg",
    "max_execute_files" => 2 //Total videos will be executed (transcode) each time cron job run
];