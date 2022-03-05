# Blog :: Change Log

## Version 4.6.8

### Information

- **Release Date:** July 2, 2021
- **Best Compatibility:** phpFox >= 4.8.6

### Improvements

- Compatible with PHP 8.0

### Fixed Bugs

- Issue with RSS

### Changed Files

- M Ajax/Ajax.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Version/v453.php
- A Installation/Version/v468.php
- M Service/Blog.php
- M Service/Callback.php
- M Service/Category/Category.php
- M Service/Category/Process.php
- M Service/Process.php
- M installer.php

## Version 4.6.7

### Information

- **Release Date:** April 23, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Improvements

- Add new setting for Email Notification of Blogs app
- Add new settings to allow Admins review and update all email contents of Blogs app
- Warning message if users want to leave site when they are adding a blog
- Hide trending tags of blogs that posted in Pages/Groups when they aren't allowed to show on the blog homepage [#2994](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2994)

### Fixed Bugs

- Some phrases were missing
- Issue with Topic (Tags) in blog
- Translation issue with email notification when user post blog in Pages/Groups

### Changed Files

- M Block/Featured.php
- M Block/Sponsored.php
- A Controller/DownloadController.php
- M Controller/IndexController.php
- M Install.php
- M Service/Api.php
- M Service/Blog.php
- M Service/Callback.php
- M Service/Process.php
- A hooks/tag.service_tag_gettagcloud_before_query.php
- M phrase.json
- M start.php
- M views/block/add-category-list.html.php
- M views/block/feed.html.php
- M views/block/top.html.php
- M views/controller/add.html.php
- M views/controller/view.html.php

## Version 4.6.6

### Information

- **Release Date:** September 12, 2019
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- Adding a smiley in CKEditor in blog puts it on a line by itself. [#2759](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2759)
- Feed cuts off the top of the blog image [#2665](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2665)

### Changed Files

- M Block/BlogNew.php
- M Block/Categories.php
- M Controller/ViewController.php
- M Install.php
- M README.md
- M Service/Blog.php
- M assets/main.less
- M changelog.md
- M hooks/validator.admincp_user_settings_blog.php
- M views/block/new.html.php


## Version 4.6.5

### Information

- **Release Date:** April 27, 2019
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- Fix layout issues.
- Search is terrible. Does not include blog content [#2515](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2515)
- Delete issue.
- Blog double posts [#2567](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2567)
- Lost custom privacy when publishing a draft blog have privacy is custom.
- Adding image in blog via browse cuts off top portion of image [#2629](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2629)

### Changed Files:

- M Block/Featured.php
- M Block/Sponsored.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Api.php
- M Service/Blog.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category/Process.php
- M Service/Permission.php
- M assets/main.less
- M views/block/add-category-list.html.php
- M views/block/new.html.php


## Version 4.6.4

### Information

- **Release Date:** November 23, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- Support RSS.
- Show actial statistics on detail page.

### Improvements

- Remove sponsor item when login as Page.

### Changed Files:

- M Install.php
- M Installation/Version/v453.php
- M README.md
- M Service/Blog.php
- M Service/Category/Category.php
- M assets/autoload.js
- M changelog.md
- M views/controller/view.html.php

## Version 4.6.3

### Information

- **Release Date:** October 18, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Global search - Filter Reshults show "Blog" instead of "Blogs".
- Ban word does not work with created feed.
- Blog description is still displaying ban word.
- Editing: blog.view::blog::related (Suggestion) not working > no component available.
- Manage sponsorships: Click number not change when click on a sponsored feed.
- Can re-sponsor an pending blog.
- Admin can not Sponsor In Feed a blog of other user.

### Improvements

- Add new setting to disallow/allow app to post on Main feed when add new item. (default is allow).

### Changed Files:

- M Ajax/Ajax.php
- M Block/Feed.php
- M Block/Sponsored.php
- M Controller/Admin/CategoryController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Blog_Category_Data.php
- M Installation/Version/v453.php
- M README.md
- M Service/Blog.php
- M Service/Callback.php
- M Service/Category/Category.php
- M Service/Permission.php
- M Service/Process.php
- M changelog.md
- M views/block/entry.html.php
- M views/block/entry_block.html.php
- M views/block/feed.html.php
- M views/block/link.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/category.html.php

## Version 4.6.2

### Information

- **Release Date:** July 13, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Cannot un-sponsor in feed.
- Show like section for non-login user.
- Does not show "My Blogs" and "Friend's Blogs" menu for non-login user.
- Clickable on blog photo in feed.
- RSS Feed - Change phrase for Blogs app.
- ACP - Category - Disable url of inactive category.
- Activity points - Decrease point when delete a draft/pending blog.

### Changed files:
- D  Block/PopularTopic.php
- M  Controller/AddController.php
- M  Controller/IndexController.php
- M  Controller/ViewController.php
- M  Install.php
- M  Installation/Version/v453.php
- M  README.md
- M  Service/Blog.php
- M  Service/Category/Category.php
- M  Service/Process.php
- M  assets/main.less
- M  changelog.md
- M  hooks/admincp.service_maintain_delete_files_get_list.php
- M  start.php
- M  views/block/feed.html.php
- D  views/block/topic.html.php
- M  views/controller/view.html.php

## Version 4.6.1

### Information

- **Release Date:** February 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Cannot un-sponsor blog.
- Missing SEO settings.
- Top bloggers block: cannot get cover photo of the first user.

### Changed files:
- M	Ajax/Ajax.php
- M	Controller/AddController.php
- M	Install.php
- M	Service/Permission.php
- M	views/block/link.html.php
- M	views/block/top.html.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

* Setting cache time of sponsored and featured blocks don't work.
* Register users cannot sponsor blogs in case the price is 0.
* Sub-categories show wrong links in My Blogs page.
* Admincp - Manage Categories: Show `No categories found` after deleting a sub-category.
* The button `Sponsor your items` still be shown while users don't have permission to sponsor.
* Pending Blogs page: Does not show message after approving blogs by mass action.
* Duplicate blogs when click on publish button many times.

### Improvements:

* Improve layout of pages and blocks.
* Support both of topic and hashtag.
* Support drag/drop, preview, progress bar when users upload photo.
* Validate all settings, user group settings, and block settings.

### Changed files:

- Ajax/Ajax.php
- Block/AddCategoryList.php
- Block/BlogNew.php
- Block/Categories.php
- Block/Featured.php
- Block/Feed.php
- Block/PopularTopic.php
- Block/Preview.php
- Block/Related.php
- Block/Sponsored.php
- Block/TopBloggers.php
- Controller/AddController.php
- Controller/Admin/AddCategoryController.php
- Controller/Admin/CategoryController.php
- Controller/Admin/DeleteCategoryController.php
- Controller/DeleteController.php
- Controller/IndexController.php
- Controller/ProfileController.php
- Controller/ViewController.php
- Install.php
- Installation/Database/Blog.php
- Installation/Database/Blog_Category.php
- Installation/Database/Blog_Category_Data.php
- Installation/Database/Blog_Text.php
- Installation/Version/v453.php
- Service/Api.php
- Service/Blog.php
- Service/Browse.php
- Service/Cache/Remove.php
- Service/Callback.php
- Service/Category/Category.php
- Service/Category/Process.php
- Service/Permission.php
- Service/Process.php
- assets/autoload.js
- assets/main.less
- hooks/admincp.service_maintain_delete_files_get_list.php
- hooks/bundle__start.php
- hooks/route_start.php
- hooks/template_template_getmenu_3.php
- hooks/validator.admincp_user_settings_blog.php
- phrase.json
- start.php
- views/block/add-category-list.html.php
- views/block/categories.html.php
- views/block/entry.html.php
- views/block/entry_block.html.php
- views/block/featured.html.php
- views/block/feed.html.php
- views/block/link.html.php
- views/block/new.html.php
- views/block/preview.html.php
- views/block/related.html.php
- views/block/specialmenu.html.php
- views/block/sponsored.html.php
- views/block/top.html.php
- views/controller/add.html.php
- views/controller/admincp/add.html.php
- views/controller/admincp/category.html.php
- views/controller/admincp/delete-category.html.php
- views/controller/delete.html.php
- views/controller/edit.html.php
- views/controller/index.html.php
- views/controller/profile.html.php
- views/controller/view.html.php

### Removed Blocks

| ID | Block | Name | Reason |
| --- | -------- | ----- | ---- |
| 1 | blog.topic | Popular Topic | Don't use anymore. Use the block `tag.cloud` instead |

## Version 4.5.3

### Information

- **Release Date:** September 15, 2017
- **Best Compatibility:** phpFox >= 4.5.3

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | blog_time_stamp | Time Stamps | Don't use anymore. Use the global setting `Global Time Stamp` |
| 2 | top_bloggers_display_limit | Top Bloggers Limit | Move to block setting |
| 3 | top_bloggers_min_post | Blog Count for Top Bloggers | Move to block setting |
| 4 | cache_top_bloggers | Cache Top Bloggers | Move to block setting |
| 5 | cache_top_bloggers_limit | Top Bloggers Cache Time | Move to block setting |
| 6 | display_post_count_in_top_bloggers | Display Post Count for Top Bloggers | Move to block setting |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | blog_paging_mode | Pagination Style | Pagination Style at Search Page (3 styles) |
| 2 | display_blog_created_in_group | Display blogs which created in Group to Blogs app | Enable to display all public blogs created in Group to Blogs app. Disable to hide them |
| 3 | display_blog_created_in_page | Display blogs which created in Page to Blogs app | Enable to display all public blogs created in Page to Blogs app. Disable to hide them |

### New User Group Settings

| ID | Var name | Info |
| --- | -------- | ---- |
| 1 | can_feature_blog | Can feature blogs |
| 2 | can_sponsor_blog | Can members of this user group mark a blog as Sponsor without paying fee |
| 3 | can_purchase_sponsor | Can members of this user group purchase a sponsored ad space |
| 4 | blog_sponsor_price | How much is the sponsor space worth? This works in a CPM basis |
| 5 | auto_publish_sponsored_item | After the user has purchased a sponsored space, should the item be published right away? If set to false, the admin will have to approve each new purchased sponsored event space before it is shown in the site |
| 6 | blog_photo_max_upload_size | Photo max upload size |

### Deprecated Functions

| ID | Class Name | Function Name | Will Remove In | Reason |
| --- | -------- | ---- | ---- | ---- |
| 1 | Apps\Core_Blogs\Service\Category\Category | getCategoriesById | 4.6.0 | Don't use anymore |
| 3 | Apps\Core_Blogs\Service\Category\Category | getSearch | 4.6.0 | Don't use anymore |
| 4 | Apps\Core_Blogs\Service\Category\Category | get | 4.6.0 | Don't use anymore |
| 5 | Apps\Core_Blogs\Service\Category\Process | deleteMultiple | 4.6.0 | Don't use anymore |
| 6 | Apps\Core_Blogs\Service\Category\Process | toggleCategory | 4.6.0 | Don't use anymore |
| 7 | Apps\Core_Blogs\Service\Blog | getInfoForAction | 4.6.0 | Don't use anymore |
| 8 | Apps\Core_Blogs\Service\Blog | filterText | 4.6.0 | Don't use anymore |
| 9 | Apps\Core_Blogs\Service\Blog | filterText | 4.6.0 | Don't use anymore |

### New Blocks

| ID | Block | Name | Description |
| --- | -------- | ----- | ---- |
| 1 | blog.featured | Featured | Display featured blogs list |
| 2 | blog.sponsored | Sponsored | Display sponsored blogs list |
| 3 | blog.topic | PopularTopic | Display most used topics list |
| 4 | blog.related | Related | Display blogs list which have same category with current viewing blog |

