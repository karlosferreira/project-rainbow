# Support Compile Video on Storage Systems

## Requirements

- PHP server support:

  - PHP version >= 7.2.5
  - FFMPEG 
  - SFTP connection
  - Cron job
  - shell_exec function
  - cURL support
  - GD Library
  
- Your phpFox server allow external CURL call from FFMPEG server

## Workflow

1. Video uploaded from browser
2. Transfer that video to FFMPEG server which mention in Requirements section
3. Compress video on that server
4. Transfer video to Storage System which set in your site.

## How to setup?
1. Copy **/PF.Site/Apps/core-videos/FFmpegServer/ffmpeg_config.sample.php** to **/PF.Site/Apps/core-videos/FFmpegServer/ffmpeg_config.php**
2. Update your FFMPEG server info to file **/PF.Site/Apps/core-videos/FFmpegServer/ffmpeg_config.php** in your phpFox server. Follow guide on that file.
3. Copy **/PF.Site/Apps/core-videos/FFmpegServer/External/Source/config.sample.php** to **/PF.Site/Apps/core-videos/FFmpegServer/External/Source/config.php**
4. Update your phpFox site info to file **/PF.Site/Apps/core-videos/FFmpegServer/External/Source/config.php**. Follow guide on that file.
5. Copy all source code in **/PF.Site/Apps/core-videos/FFmpegServer/External** into your FFMPEG server
6. Setup Cronjob in your FFMPEG server to execute file **/Source/video.php**, it should be set to run every 5 minutes. **Example: \*/5 \* \* \* \* php /public_html/Source/video.php**
7. Enable setting "Allow compile Video on external FFMPEG server" of Videos App in your phpFox site.
8. Done.

#### NOTE: If you upgraded from Videos v4.7.9 you must update 2 configurations files:

1.1. Copy your configurations from **/PF.Site/Apps/core-videos/FFmpegServer/ffmpeg_config.json** to **/PF.Site/Apps/core-videos/FFmpegServer/ffmpeg_config.php**, then remove **ffmpeg_config.json** file.

2.1. Copy your configurations from **/PF.Site/Apps/core-videos/FFmpegServer/External/Source/config.json** to **/PF.Site/Apps/core-videos/FFmpegServer/External/Source/config.php**, then remove **config.json** file.

When you're done, you can go to step (3)