# Forum :: Change Log

## Version 4.6.5

### Information

- **Release Date:** July 05, 2021
- **Best Compatibility:** phpFox >= 4.6.1

### Improvements

- Compatible with PHP 8.0 and phpFox 4.8.6
- Warning message if users want to leave site when they are adding new thread
- Add new setting for Email Notification of Forum app
- Add new settings to allow Admins review and update all email contents of Forum app

### Fixed Bugs

- Issue when disabled module Attachment
- Issue with pending posts
- Issue with Forum's permissions
- Pending Announcement shows on threads listing
- Group's admin/owner get emails with wrong language phrases when a new thread is posted on group
- Some other minor bugs

### Changed Files

- M Ajax/Ajax.php
- M Block/MergeBlock.php
- M Block/RecentThreadBlock.php
- M Controller/ForumController.php
- M Controller/IndexController.php
- M Controller/PostController.php
- M Controller/ThreadController.php
- M Install.php
- M Installation/Version/v460.php
- M Service/Callback.php
- M Service/Forum.php
- M Service/Moderate/Moderate.php
- M Service/Post/Post.php
- M Service/Post/Process.php
- M Service/Process.php
- M Service/Subscribe/Subscribe.php
- M Service/Thread/Process.php
- M Service/Thread/Thread.php
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M views/block/menu.html.php
- M views/block/post.html.php
- M views/block/recent-post.html.php
- M views/block/thanks.html.php
- M views/block/thread-detail.html.php
- M views/block/thread-entry.html.php
- M views/block/thread-rows.html.php
- M views/controller/post.html.php
- M views/controller/thread.html.php

## Version 4.6.4

### Information

- **Release Date:** August 20, 2020
- **Best Compatibility:** phpFox >= 4.6.1

### Improvements

- Hide RSS Feed icon in Forum when disable "RSS Feed within Forums"

### Fixed bugs

- Some minor css issues
- Some issues about editing Forum in AdminCP
- Can not add attachments when editing Thread
- Can not sponsor a Thread with fee in Pages/Groups
- Compatible with disabling feed module
- Can see preview of Threads when users have no permissions
- Can not see checkbox when using mass actions on Threads
- Missing first character in reply in feed content

### Changed files

- M Ajax/Ajax.php
- M Block/RecentThreadBlock.php
- M Block/ReplyBlock.php
- M Block/SponsoredBlock.php
- M Controller/ForumController.php
- M Install.php
- M Service/Callback.php
- M Service/Forum.php
- M Service/Moderate/Process.php
- M Service/Post/Process.php
- M Service/Process.php
- M Service/Thread/Process.php
- M Service/Thread/Thread.php
- M assets/main.less
- M change-log.md
- A hooks/ajax_getdata.php
- M hooks/core.template_block_comment_border_new.php
- M hooks/template_template_getmenu_3.php
- M phrase.json
- M views/block/admincp/moderator.html.php
- M views/block/admincp/permission.html.php
- M views/block/feed-rows.html.php
- M views/block/post.html.php
- M views/block/reply.html.php
- M views/block/search.html.php
- M views/block/thread-detail.html.php
- M views/block/thread-rows.html.php
- M views/controller/forum.html.php
- M views/controller/post.html.php
- M views/controller/search.html.php

## Version 4.6.3

### Information

- **Release Date:** December 21, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Improvements

- Support utf8_mb4.
- Show actual statistics on detail page.
- Add limit when get announcements.

### Fix bugs

- Show error when add reply.
- Cross site scripting.

### Changed files

- M Controller/ForumController.php
- M Install.php
- A Installation/Version/v463.php
- M Service/Post/Post.php
- M Service/Thread/Thread.php
- M assets/main.less
- M installer.php
- M views/block/forum.html.php
- M views/block/thread-entry.html.php
- M views/block/thread-rows.html.php

## Version 4.6.2

### Information

- **Release Date:** October 28, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Improve performance.
- Add new setting to disallow/allow app to post on Main feed when add new item. (default is allow).

### Fix bugs

- Forum tags (trending) don't get removed after deleting thread.
- Response ajax link wrong when un-sponsor.
- Forum sponsored not working right because in edit mode sponsor thread not working with ads also it doubles sponsored in advertise.
- Show repeat a thread whenever add post to that thread.
- Show sponsor action in drop-down menu when user has no permission.
- Post reply to thread: Show wrong notice message.
- Show duplicate on sponsored block when re-sponsor a denied item.
- Show sub-menu of forum when click on discussion tab.

### Changed files

- M Ajax/Ajax.php
- M Block/RecentPostBlock.php
- M Block/RecentThreadBlock.php
- M Controller/ForumController.php
- M Controller/IndexController.php
- M Controller/RecentController.php
- M Controller/ThreadController.php
- M Install.php
- M Installation/Database/Forum_Moderator_Access.php 
- M Installation/Database/Forum_Post_Text.php
- M README.md
- M Service/Callback.php
- M Service/Forum.php
- M Service/Moderate/Process.php
- M Service/Post/Post.php
- M Service/Post/Process.php
- M Service/Process.php
- M Service/Thread/Process.php
- M Service/Thread/Thread.php
- M assets/autoload.js
- M change-log.md
- M phrase.json
- M views/block/menu.html.php
- M views/block/search.html.php
- M views/controller/forum.html.php

## Version 4.6.1

### Information

- **Release Date:** Aug 21, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fix bugs

- SEO - No phrase input for description and keywords
- User can not see their post when replies the thread from "recent posts" block
- Broken layout when disable feed module
- Display an option not permission in menu
- Should show app menu on all pages
- Created feed is still displaying banned word
- Not display inserted photo when post reply
- Wrong result for searching in My Threads page
- In forum app in admincp there is a typo "detele" instead of "delete"
- Still display banned words in Recent Discussions, Recent Posts blocks
- Inserted photo be crossed

### Changed files
- M Ajax/Ajax.php
- M Block/ReplyBlock.php
- M Block/SponsoredBlock.php
- M Controller/Admin/AddController.php
- M Controller/Admin/DeleteController.php
- M Controller/ForumController.php
- M Controller/PostController.php
- M Controller/ThreadController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Forum.php
- M Service/Process.php
- M Service/Thread/Process.php
- M Service/Thread/Thread.php
- M assets/autoload.js
- D assets/autoload.less
- M assets/main.less
- M change-log.md
- M phrase.json
- M views/block/entry.html.php
- M views/block/forum.html.php
- M views/block/post.html.php
- M views/block/recent-post.html.php
- M views/block/reply.html.php
- M views/block/search.html.php
- M views/block/sponsored.html.php
- M views/block/thread-detail.html.php
- M views/block/thread-entry.html.php
- M views/controller/admincp/index.html.php
- M views/controller/thread.html.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Allow admin can view and approve/deny pending posts in thread detail page.
- Support both of topic and hashtag.
- Hide `Reply` button and thread tools in closed threads detail pages.
- Users can select actions of thread/post on listing page same as on thread detail page.
- Support AddThis in thread detail page.
- Support 3 styles for paginations.
- Validate all settings, user group settings, and block settings.

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | total_recent_posts_display | Total Recent Posts Display | Don't use anymore |
| 2 | total_recent_discussions_display | Total Recent Discussions Display | Don't use anymore |
| 3 | forum_user_time_stamp | Forum User Time Stamp | Don't use anymore |
| 4 | can_add_tags_on_threads | Can add tags to threads? | Don't use anymore |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | forum_paging_mode | Pagination Style | Select Pagination Style at Search Page. |
| 2 | forum_meta_description | Forum Meta Description | Meta description added to pages related to the Forum app. |
| 3 | forum_meta_keywords | Forum Meta Keywords | Meta keywords that will be displayed on sections related to the Forum app. |
| 4 | default_search_type | Default option to search in main forum page | |

### Removed Blocks

| ID | Block | Name |  Reason |
| --- | -------- | ---- | ---- |
| 1 | forum.recent | Recent Threads | Don't use anymore |

### New Blocks

| ID | Block | Name | Description |
| --- | -------- | ---- | ------------ |
| 1 | forum.recent-post | Recent Posts | Show recent posts of forum |
| 2 | forum.recent-thread | Recent Discussions | Show recent threads of forum |
| 2 | forum.sponsored | Sponsored Threads | Show sponsored threads of forum |


