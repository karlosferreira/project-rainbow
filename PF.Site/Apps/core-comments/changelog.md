# Comments :: Change log

## Version 4.1.10

### Information

- **Release Date:** November 25, 2021
- **Best Compatibility:** phpFox >= 4.8.7

### Bugs Fixed

- Enable "Add Comments as Feeds" - Missing content on feed if have link and emoji in the comment
- Backend - Error code is shown when admin clicks on breadcrumb link
- Users can write a comment with the content is identical
- The warning message is displayed incorrectly when users post a comment
- "Spam Comment" function is not working correctly
- Turn on "Disable All External URL's" - Redundant tag a after comment with an external link
- Show HTML tag when admin drags and drops the option "Approve/Deny" to comment field
- Mobile browser - Issue when reply a comment or mention friend in comment

### Improvements

- Some improvements for "Confirm" popup
- Add actions (Approve & Deny) when admin tries to view detail of spam comment
- Send button should be active after input emojis
- Don't allow users to edit/delete pending comments
- Change text when post owner tries to delete a comment on their post
- Change text when admin site approves/denies the comment

### Changed Files

- M Controller/Admin/SpamCommentsController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Comment.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M phrase.json
- M start.php
- M views/block/admin/entry.html.php
- M views/block/comment.html.php
- M views/block/edit-comment.html.php
- M views/block/entry.html.php
- M views/block/mini.html.php
- M views/controller/admincp/manage-stickers.html.php
- M views/controller/admincp/pending-comments.html.php
- M views/controller/admincp/spam-comments.html.php

## Version 4.1.9

### Information

- **Release Date:** September 15, 2021
- **Best Compatibility:** phpFox >= 4.8.7

### Bugs Fixed

- The position to show Emojis is not correct after updating a comment
- Minor issue in Firefox after posting a comment
- Some minor issues when editing comment
- Some link preview does not align left
- Receive notifications when a blocked user comments
- Auto add link preview on reply compose box

### Improvements

- Prevent the HTML tags in content from either being executed as JS script or rendered as HTML content
- Support insert emoticon into specific caret position when adding comment

### Changed Files

- M Ajax/Ajax.php
- M Install.php
- M Service/Comment.php
- M Service/History.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M start.php
- M views/block/mini.html.php

## Version 4.1.8

### Information

- **Release Date:** June 25, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Bugs Fixed

- Doesn't mention user automatically if others reply their comment
- Show HTML code when click on "View ... more replies" [#3009](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/3009)
- Some minor bugs
- Some layout issues

### Improvements

- Compatible with PHP 8.0

### Changed Files

- M Ajax/Ajax.php
- M Block/CommentBlock.php
- M Install.php
- M Installation/Data/v412.php
- M README.md
- M Service/Api.php
- M Service/Callback.php
- M Service/Comment.php
- M Service/Process.php
- M assets/autoload.js
- M assets/images/stickers/set_1/23.gif
- M assets/images/stickers/set_2/31.gif
- M assets/main.less
- A hooks/feed.service_callback__getnotificationmini_like.php
- M start.php
- M views/block/admin/list-stickers.html.php
- M views/block/comment.html.php
- M views/block/mini.html.php

## Version 4.1.7

### Information

- **Release Date:** April 12, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Bugs Fixed

- Some minor bugs

### Improvements

- Compatible with phpFox v4.8.4
- Improve performance
- Warning message if users want to leave site when they are writing a comment 
- Improvement on the layout of Mention user when add/edit a comment
- Add new settings to allow Admins review and update all email contents of Comments app
- Add new settings to allow Admins control attachment items on comment

### Changed Files

- M Ajax/Ajax.php
- M Block/AttachStickerBlock.php
- M Block/EmoticonBlock.php
- M Install.php
- M Installation/Database/Comment.php
- M README.md
- M Service/Comment.php
- M Service/Emoticon.php
- M Service/Process.php
- M Service/Stickers/Process.php
- M Service/Stickers/Stickers.php
- M assets/autoload.js
- M assets/jscript/admin.js
- M assets/main.less
- M changelog.md
- M start.php
- M views/block/attach-sticker.html.php
- M views/block/comment.html.php
- M views/block/edit-comment.html.php
- M views/block/edit-history.html.php
- M views/block/emoticon.html.php
- M views/block/mini.html.php
- A views/block/recent-stickers.html.php

## Version 4.1.6

### Information

- **Release Date:** January 20, 2021
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs Fixed

- Number of comment shows incorrectly on activity feeds [#2963](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2963)

### Changed Files

- M Install.php
- M Service/Comment.php
- M changelog.md
- D hooks/ajax_process.php
- M hooks/run_set_controller.php

## Version 4.1.5

### Information

- **Release Date:** November 09, 2020
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs Fixed

- Issue when disable module Feed
- Show blank space underneath a post after delete user comment on Mobile App 
- Can't cancel edit comment by press ESC button
- Removing highlight hashtag in a comment
- Issue with comment of blocked users
- Issue with One-way friendship setting
- Some layout issues

### Changed Files

- M Controller/CommentsController.php
- M Install.php
- M README.md
- M Service/Comment.php
- M Service/Process.php
- M assets/autoload.js
- M changelog.md
- M start.php
- M views/block/comment.html.php
- M views/block/edit-comment.html.php

## Version 4.1.4

### Information

- **Release Date:** July 01, 2020
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs Fixed

- Click on notification about pending comment - Show 500 error

### Changed Files

- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Comment.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/feed.service_feed_processfeed.php
- M start.php

## Version 4.1.3

### Information

- **Release Date:** February 11, 2020
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs Fixed

- View 4 More Comments won't load on some Apps [#2860](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2860)

### Changed Files

- M Controller/CommentsController.php
- M Install.php
- M README.md
- M changelog.md


## Version 4.1.2

### Information

- **Release Date:** January 08, 2020
- **Best Compatibility:** phpFox >= 4.7.8

### Improvements

- Compatible with Mobile App

### Changed Files

- M Install.php
- M Installation/Data/v410.php
- A Installation/Data/v412.php
- M Installation/Database/Comment_Emoticon.php
- M Service/Comment.php
- M Service/Process.php
- M installer.php
- M phrase.json
- M views/block/admin/entry.html.php
- M views/block/edit-history.html.php
- M views/block/emoticon.html.php
- M views/block/entry.html.php
- M views/block/mini-extra.html.php


## Version 4.1.1

### Information

- **Release Date:** November 25, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Improvements

- Compatible with Reaction App

### Changed Files

- M Block/CommentBlock.php
- M hooks/template_gettemplatefile.php
- M views/block/comment.html.php
- D views/block/like-display.html.php
- D views/block/like-link.html.php
- D views/block/link.html.php

## Version 4.1.0

### Information

- **Release Date:** November 07, 2019
- **Best Compatibility:** phpFox >= 4.7.8
