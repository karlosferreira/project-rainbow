# Videos App  :: Change Log #

## Version 4.7.10 ##

### Information ###

- **Release Date:** September 30, 2021
- **Best Compatibility:**
    - phpFox 4.8.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Improvements ###

- Support configuring metadata if upload video with "Zencoder + S3" method
- Support Web API for Videos (with RESTful API app)
- Support upload video with "Mux" method
- Support compressing Videos on external FFMPEG server with scalable site
- Support Schedule Videos on Feed
- Allow admin configs limitation on number of videos created by user group
- Don't send notification/email of the private video post to tagged users

### Bugs Fixed ###

- Issue when view video in Full screen mode on Activity Feeds [#3013](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/3013)
- Phrases missing
- Can not "view more" in video detail page if its description is long
- Some other minor bugs

### Changed Files ###

- M Ajax/Ajax.php
- M Block/Category.php
- M Controller/Admin/DeleteCategoryController.php
- M Controller/Admin/UtilitiesController.php
- M Controller/CallbackController.php
- M Controller/IndexController.php
- D Controller/MuxCallbackController.php
- M Controller/ShareController.php
- M Controller/UploadController.php
- M Controller/CompileCallbackController.php
- D FFmpegServer/External/Source/config.json
- A FFmpegServer/External/Source/config.sample.php
- M FFmpegServer/External/Source/video.php
- M FFmpegServer/External/Source/composer.json
- M FFmpegServer/External/Source/composer.lock
- M FFmpegServer/README.html
- M FFmpegServer/README.md
- D FFmpegServer/ffmpeg_config.json
- A FFmpegServer/ffmpeg_config.sample.php
- D FFmpegServer/External/Source/vendor/*
- M Install.php
- M Installation/Database/Video.php
- M Job/Encode.php
- A Service/Api.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- A api.md
- M assets/autoload.js
- M assets/main.less
- M composer.json
- M composer.lock
- D hooks/route_start.php
- M hooks/template_getheader_end.php
- M hooks/validator.admincp_user_settings_v.php
- M phrase.json
- M start.php
- M Job/Encode.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/main.less
- M assets/videojs/videojs.css
- M phrase.json
- M vendor/*

## Version 4.7.9 ##

### Information ###

- **Release Date:** April 12, 2021
- **Best Compatibility:**
    - phpFox 4.8.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis
    
### Improvements ###

- Show Video title on Email Notification after uploaded success a video [#2943](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2943)
- Add new setting for Email Notification of Videos app.
- Add new settings to allow Admins review and update all email contents of Videos app
- Warning message if users want to leave site when they are adding a video
- Improved layout on Activity Feeds
- Hide video feature on Activity Feeds if turn of setting "Allow posting on Main Feed"
- Support "https://fb.watch/..." URL
- Support compress video on a external server using FFmpeg

### Bugs Fixed ###

- Phrases missing
- Drag & Drop file on feed doesn't work properly
- Show wrong owner's name when post Video as Page on the activity feeds of page
- Some issues when share a Facebook Video
- Some other minor bugs

### Changed Files ###

- M Ajax/Ajax.php
- M Controller/CallbackController.php
- A Controller/CompileCallbackController.php
- M Controller/ShareController.php
- M Controller/UploadController.php
- A FFmpegServer/*
- M Install.php
- A Installation/Version/v479.php
- M Job/Encode.php
- M README.md
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- M assets/main.less
- M assets/videojs/videojs.css
- M changelog.md
- M hooks/groups.component_controller_view_build.php
- M hooks/pages.component_controller_view_build.php
- M hooks/template_getheader_end.php
- A hooks/user.service_process_edit_status_end.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/add_category_list.html.php
- M views/block/entry.html.php
- M views/block/feed_video.html.php
- M views/controller/edit.html.php
- M views/controller/play.html.php
- M views/controller/share.html.php

## Version 4.7.8 ##

### Information ###

- **Release Date:** July 24, 2020
- **Best Compatibility:**
    - phpFox 4.7.7 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Layout issue with uploaded video on feed
- Can't play video on Android Mobile App 

### Changed files ###

- M Service/Video.php
- M Controller/PlayController.php
- M assets/main.less
- M views/block/feed_video.html.php

## Version 4.7.7 ##

### Information ###

- **Release Date:** July 7, 2020
- **Best Compatibility:**
    - phpFox 4.7.7 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Improvements ###

- Compatible with PHP v7.4
- Improving video player
- Support total view count when play video on feed

### Bugs Fixed ###

- Missing thumbnail when share Facebook video

### Changed files ###

- M Ajax/Ajax.php
- M Install.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/main.less
- M views/block/feed_video.html.php
- M assets/autoload.js
- A assets/videojs/videojs.css
- A assets/videojs/videojs.js
- A assets/videojs/videojs-ie8.min.js
- A assets/videojs/jquery.iframetracker.js

## Version 4.7.6 ##

### Information ###

- **Release Date:** November 21, 2019
- **Best Compatibility:**
    - phpFox 4.7.7 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Missing _120 video thumbnail on CDN server [#2794](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2794).
- No list videos in Page/Group liked when enabling Friend Only Community.
- Can't upload video in Group using FFMPEG method.

### Changed files ###

- M Controller/IndexController.php
- M Install.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/main.less
- M views/block/feed_video.html.php
- M views/block/menu.html.php
- M views/controller/edit.html.php
- M views/controller/play.html.php


## Version 4.7.5 ##

### Information ###

- **Release Date:** July 17, 2019
- **Best Compatibility:**
    - phpFox 4.7.7 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Feed status - Can not share feed on another tab after upload the invalid video.

### Improvements ###

- Show feed status with video in the wall of the person tagged/mentioned.

### Changed files ###

- M Ajax/Ajax.php
- M Controller/CallbackController.php
- M Controller/IndexController.php
- M Controller/ShareController.php
- M Controller/UploadController.php
- M Install.php
- M Job/Encode.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- M assets/main.less
- M hooks/groups.component_controller_view_build.php
- M hooks/pages.component_controller_view_build.php
- M hooks/template_getheader_end.php
- M phrase.json
- M views/block/add_category_list.html.php
- M views/controller/play.html.php

## Version 4.7.4 ##

### Information ###

- **Release Date:** March 04, 2019
- **Best Compatibility:**
    - phpFox 4.7.4 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Upload video from feed - Show public in feed while that video in pending.
- Show wrong phrase [#57721](https://community.phpfox.com/forum/thread/57721/videos-in-status-update-should-be-singular/).
- Show pending videos in User profile >>Videos.

### Improvements ###

- Support editing status feeds with Video.

### Changed files ###
- M Controller/IndexController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M change-log.md
- M hooks/template_getheader_end.php
- M vendor/zencoder/zencoder-php/Services/Zencoder.php
- M views/controller/play.html.php

## Version 4.7.3 ##

### Information ###

- **Release Date:** January 30, 2019
- **Best Compatibility:**
    - phpFox 4.7.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Improvements ###

- Upgrade Amazon S3 lib to support signature version 4

### Changed files ###
- M Ajax/Ajax.php
- M Controller/CallbackController.php
- M Controller/UploadController.php
- M Install.php
- M Job/Encode.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- M composer.json
- M composer.lock
- A hooks/admincp.component_controller_setting_edit_process.php
- M hooks/admincp.service_maintain_delete_files_get_list.php
- D vendor/tpyo/amazon-s3-php-class/S3.php
- D vendor/tpyo/amazon-s3-php-class/composer.json
- M views/controller/play.html.php
- M views/controller/share.html.php

## Version 4.7.2 ##

### Information ###

- **Release Date:** January 24, 2019
- **Best Compatibility:**
    - phpFox 4.7.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Not has notification and mail to page owner when a user post video on page.
- Feeds - Show duplicate the warning message when posting from invalid URL.
- Not show tagged user when uploaded video and check-in.
- Share video by uploading - Save button disappear after select Custom privacy.
- Share video with custom privacy - Friend in list can not view that video.

### Improvements ###

- Support description search.

### Changed files ###

- M Ajax/Ajax.php
- M Controller/CallbackController.php
- M Controller/IndexController.php
- M Controller/ShareController.php
- M Install.php
- M Installation/Version/v452.php
- M Job/Encode.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- M phrase.json
- M views/controller/share.html.php

## Version 4.7.1 ##

### Information ###

- **Release Date:** December 14, 2018
- **Best Compatibility:**
    - phpFox 4.7.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Issue when play mp3 in feed and videos uploaded start to (flicker) in feed.
- Delete videos from pages takes user away from page to main video.
- Video list - Missed duration time when post video from feed.

### Changed files ###

- M Ajax/Ajax.php
- M Service/Process.php
- M assets/autoload.css
- D assets/autoload.less
- M assets/main.less
- M views/block/feed_video.html.php

## Version 4.7.0 ##

### Information ###

- **Release Date:** November 23, 2018
- **Best Compatibility:**
    - phpFox 4.7.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Sponsor Video

### Improvements ###

- Update layout for video detail page

### Changed files ###

- M Ajax/Ajax.php
- M Controller/CallbackController.php
- M Install.php
- M Installation/Database/Video.php
- M Job/Encode.php
- M README.md
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- M assets/main.less
- M change-log.md
- A checksum
- M start.php
- M views/block/entry.html.php
- M views/controller/admincp/add-category.html.php
- M views/controller/edit.html.php
- M views/controller/play.html.php
- M views/controller/share.html.php

## Version 4.6.2 ##

### Information ###

- **Release Date:** August 22, 2018
- **Best Compatibility:**
    - phpFox 4.6.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Show error page when disable feed module
- Created feed still displaying banned word
- Site does not working if disable page app
- The error message is not correct when uploading images
- Owner of video received Email with wrong language when anyone have any actions (like, comment, ...) in it

### Improvements ###

- Show app menu when view video detail 

### Changed files ###

- M Ajax/Ajax.php
- M Block/Featured.php
- M Block/Sponsored.php
- M Controller/CallbackController.php
- M Controller/IndexController.php
- M Controller/PlayController.php
- M Controller/ShareController.php
- M Install.php
- M Job/ConvertOldVideos.php
- M Job/Encode.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- D checksum
- M phrase.json
- M views/block/feed_video.html.php
- M views/block/menu.html.php
- M views/controller/admincp/add-category.html.php
- M views/controller/play.html.php

## Version 4.6.1 ##

### Information ###

- **Release Date:** July 13, 2018
- **Best Compatibility:**
    - phpFox 4.6.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

* ACP - 500 error occurs while deleting category has sub-categories.
* Feed - The share button is disabled and can't be activate again when focus or modify the Video Link.
* Cannot get thumbnail image if url not have protocol (http/https).
* Show duplicated play icon when view upload video.
* Disable share button if the video is uploaded over size.
* Sponsor video block show duplicate items.
* Add comment as feed - Does not create feed after post comment on videos.
* Video detail page - Remove "Back" button on mobile devices.
* Missing "Save" button while uploading video.

### Changed files ###

- M Ajax/Ajax.php
- M Controller/ShareController.php
- M Install.php
- A Installation/Version/v461.php
- M README.md
- M Service/Callback.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- M assets/main.less
- M change-log.md
- A checksum
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M installer.php
- M phrase.json
- M views/block/entry.html.php

## Version 4.6.0 ##

### Information ###

- **Release Date:** January 09, 2018
- **Best Compatibility:**
    - phpFox 4.6.0 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

* Cannot get title and description of YouTube videos.

### Improvements ###

* Hide notice `Upload canceled` after uploading successfully.
* Support play videos when clicking on video thumbnails
* Support drag/drop, preview, progress bar when users upload videos.
* Validate all settings, user group settings, and block settings.
* Improve layout of all pages and blocks.

### Changed files ###

- M Ajax/Ajax.php
- M Block/AddCategoryList.php
- M Block/Category.php
- M Block/Featured.php
- M Block/FeedVideo.php
- M Block/Sponsored.php
- M Block/Suggested.php
- M Controller/Admin/AddCategoryController.php
- M Controller/Admin/CategoryController.php
- M Controller/Admin/ConvertController.php
- M Controller/Admin/DeleteCategoryController.php
- M Controller/Admin/UtilitiesController.php
- M Controller/CallbackController.php
- M Controller/EditController.php
- M Controller/IndexController.php
- M Controller/PlayController.php
- M Controller/ProfileController.php
- M Controller/ShareController.php
- M Controller/UploadController.php
- M Install.php
- M Installation/Database/Video.php
- M Installation/Database/Video_Category.php
- M Installation/Database/Video_Category_Data.php
- M Installation/Database/Video_Embed.php
- M Installation/Database/Video_Text.php
- M Installation/Version/v452.php
- M Installation/Version/v453.php
- M Installation/Version/v454.php
- A Installation/Version/v460.php
- M Job/ConvertOldVideos.php
- M Job/Encode.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Process.php
- M Service/Video.php
- M assets/autoload.js
- A assets/images/default_thumbnail.png
- A assets/main.less
- M change-log.md
- M hooks/admincp.service_maintain_delete_files_get_list.php
- A hooks/bundle__start.php
- M hooks/groups.component_controller_view_build.php
- M hooks/job_queue_init.php
- M hooks/pages.component_controller_view_build.php
- M hooks/template_getheader_end.php
- M hooks/template_template_getmenu_3.php
- A hooks/validator.admincp_user_settings_v.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/entry.html.php
- M views/block/entry_block.html.php
- M views/block/featured.html.php
- M views/block/feed_video.html.php
- M views/block/menu.html.php
- M views/block/sponsored.html.php
- M views/block/suggested.html.php
- M views/controller/admincp/add-category.html.php
- M views/controller/admincp/category.html.php
- M views/controller/admincp/convert.html.php
- M views/controller/admincp/delete-category.html.php
- M views/controller/admincp/utilities.html.php
- M views/controller/edit.html.php
- M views/controller/index.html.php
- M views/controller/play.html.php
- M views/controller/share.html.php

## Version 4.5.4 ##

### Information ###

- **Release Date:** August 29, 2017
- **Best Compatibility:**
    - phpFox 4.5.2 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- Page does not reload after deleting video by using mass action
- Cover and Page photo does not display after uploading video
- Issue after sharing video on feed, show video doesn't exists if this video is pending
- Issue on Member activity feed after sharing video on Page as user
- Page owner can not delete video upload as Page
- Still show un-sponsor video even though this sponsor video has been deleted in AdminCP
- App name does not translate

### Improvements ###

- Hide Video app on Menu when "Can browse and view the Video app?" setting is disabled
- Redirect to Video upload page of current Page after uploading video from local instead of redirecting Video module
- Support collapse/expand sub categories

### Changed files ###

- PF.Site/Apps/core-videos/Service/Video.php
- PF.Site/Apps/core-videos/Service/Process.php
- PF.Site/Apps/core-videos/Service/Category.php
- PF.Site/Apps/core-videos/Service/Callback.php
- PF.Site/Apps/core-videos/Controller/Admin/ConvertController.php
- PF.Site/Apps/core-videos/Controller/CallbackController.php
- PF.Site/Apps/core-videos/Controller/ShareController.php
- PF.Site/Apps/core-videos/Controller/UploadController.php
- PF.Site/Apps/core-videos/Controller/PlayController.php
- PF.Site/Apps/core-videos/Installation/Version/v453.php
- PF.Site/Apps/core-videos/Installation/Version/v454.php
- PF.Site/Apps/core-videos/phrase.json
- PF.Site/Apps/core-videos/Ajax/Ajax.php
- PF.Site/Apps/core-videos/views/controller/admincp/convert.html.php
- PF.Site/Apps/core-videos/views/controller/play.html.php
- PF.Site/Apps/core-videos/Block/Category.php
- PF.Site/Apps/core-videos/start.php
- PF.Site/Apps/core-videos/views/controller/share.html.php
- PF.Site/Apps/core-videos/assets/autoload.js

## Version 4.5.3 ##

### Information ###

- **Release Date:** May 08, 2017
- **Best Compatibility:**
    - phpFox 4.5.2 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

### Bugs Fixed ###

- If you share a video which porting from old Video app to this new one, system display message "The sharing content isn't available now"
- An error occurred in the phrase of new Video app
- Video menu is not active when access all pages of Video app
- System displays wrong error message
- Does not convert all old videos completely
- Could not upload large video via FFMPEG

### Changed files ###

- PF.Site/Apps/core-videos/Service/Video.php
- PF.Site/Apps/core-videos/Service/Process.php
- PF.Site/Apps/core-videos/Service/Category.php
- PF.Site/Apps/core-videos/Controller/Admin/ConvertController.php
- PF.Site/Apps/core-videos/Controller/CallbackController.php
- PF.Site/Apps/core-videos/Controller/ShareController.php
- PF.Site/Apps/core-videos/Controller/UploadController.php
- PF.Site/Apps/core-videos/Controller/PlayController.php
- PF.Site/Apps/core-videos/Installation/Version/v452.php
- PF.Site/Apps/core-videos/phrase.json
- PF.Site/Apps/core-videos/Ajax/Ajax.php
- PF.Site/Apps/core-videos/Job/ConvertOldVideos.php
- PF.Site/Apps/core-videos/Job/Encode.php
- PF.Site/Apps/core-videos/views/controller/admincp/convert.html.php
- PF.Site/Apps/core-videos/views/controller/play.html.php
- PF.Site/Apps/core-videos/Block/Category.php
- PF.Site/Apps/core-videos/start.php

## Version 4.5.2 ##

### Information ###

- **Release Date:** April 17, 2017
- **Best Compatibility:**
    - phpFox 4.5.2 or higher
    - FFMPEG 3.0 or higher
        - FFMPEG Dependencies: version3, libmp3lame, libvpx, libtheora, libfdk-aac, libass, libfaac, libx264, libvorbis

