# Core Pages :: Changelog

## Version 4.7.14 ##

### Information ###

- **Release Date:** October 08, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Bugs fixed ###

- Some issues with Notification messages
- Change unclear description of some settings
- Wrong position of Cover Photo when editing
- Can not view Page detail after switching "Friends Only Community" setting

### Improvements ###

- Some improvements for editing Page
- Change text of confirmation messages for some actions
- Do not allow to view item link if those items are not integrated into Page
- Validate value of some settings
- Prevent XSS attack
- Improvement for showing Map in Page Info

### Changed files ###

- M Ajax/Ajax.php
- M Block/Category.php
- M Block/PageFeedBlock.php
- M Block/ReassignOwner.php
- M Block/SearchMember.php
- M Controller/AddController.php
- M Controller/Admin/AddCategoryController.php
- M Controller/Admin/ClaimController.php
- M Controller/FrameController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Version/v461.php
- A Service/Api.php
- M Service/Callback.php
- M Service/Pages.php
- M Service/Process.php
- M assets/autoload.js
- A assets/invite.js
- M assets/main.less
- M hooks/photo.component_ajax_process_done.php
- A hooks/photo.service_process_approve__1.php
- A hooks/phpfox_assign_ajax_browsing.php
- A hooks/route_start.php
- M hooks/validator.admincp_user_settings_pages.php
- M phrase.json
- M start.php
- M views/block/cropme.html.php
- M views/block/link.html.php
- M views/block/photo.html.php
- M views/block/reassign-owner.html.php
- M views/block/search-member.html.php
- M views/controller/add.html.php
- M views/controller/admincp/claim.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php
- M views/controller/view.html.php

## Version 4.7.13 ##

### Information ###

- **Release Date:** May 18, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Bugs fixed ###

- Can not redirect to page content of menu after clicking on menu
- Wrong content for email of reassigning owner
- Some minor layout issues

### Improvements ###

- Support review photo for profile and cover before publishing
- New email subject and content settings
- Some improvements for phrase

### Changed files ###

- M  Ajax/Ajax.php
- M  Block/Cropme.php
- M  Block/Photo.php
- M  Controller/PhotoController.php
- M  Controller/ViewController.php
- M  Install.php
- M  Service/Callback.php
- M  Service/Pages.php
- M  Service/Process.php
- M  assets/autoload.js
- M  assets/main.less
- M  hooks/link.component_ajax_addviastatusupdate.php
- M  hooks/photo.component_ajax_process_done.php
- D  hooks/profile.template_block_upload-cover-form.php
- M  phrase.json
- M  views/block/category.html.php
- M  views/block/cropme.html.php
- M  views/block/photo.html.php
- M  views/controller/index.html.php

## Version 4.7.12 ##

### Information ###

- **Release Date:** March 01, 2021
- **Best Compatibility:** phpFox >= 4.8.2

### Bugs fixed ###

- Missing photo of page user in avatar of feed when disable setting "Keep Files In Server"
- Does not auto join Page after the user have been assigned to owner
- Redundant mass action when no having data
- Missing email when mentioning friend in a Page status
- Some issues when editing thumbnail

### Improvements ###

- Allow set default value for Page permissions
- Apply leave page confirmation on manage pages

### Changed files ###

- M Ajax/Ajax.php
- M Block/Cropme.php
- M Controller/PhotoController.php
- M Install.php
- M Service/Callback.php
- M Service/Pages.php
- M Service/Process.php
- M assets/autoload.js
- M hooks/feed.service_feed_get_custom_module.php
- M hooks/feed.service_process_addcomment__1.php
- M hooks/link.component_ajax_addviastatusupdate.php
- M hooks/photo.component_ajax_process_done.php
- M phrase.json
- M views/block/cropme.html.php
- M views/block/reassign-owner.html.php
- M views/block/search-member.html.php
- M views/controller/add.html.php
- M views/controller/widget.html.php

## Version 4.7.11 ##

### Information ###

- **Release Date:** November 09, 2020
- **Best Compatibility:** phpFox >= 4.8.2

### Bugs fixed ###

- Can not view Page detail after switch Friends Only Community setting

### Improvements ###

- Hide all posts of Page in Home Feed of non-member
- Add 'Report' action on status which posted on Page

### Changed files ###

- M Ajax/Ajax.php
- M Controller/AddController.php
- M Controller/PhotoController.php
- M Install.php
- M Service/Callback.php
- M Service/Process.php
- A hooks/feed.service_feed_get_custom_module.php
- A hooks/module_getcomponent_start.php
- M phrase.json
- M views/controller/add.html.php

## Version 4.7.10 ##

### Information ###

- **Release Date:** September 30, 2020
- **Best Compatibility:** phpFox >= 4.7.6

### Bugs fixed ###

- Fix issues

### Changed files ###

- M Ajax/Ajax.php
- M Controller/AddController.php
- M Controller/ViewController.php
- M Service/Callback.php
- M views/block/menu.html.php

## Version 4.7.9 ##

### Information ###

- **Release Date:** August 24, 2020
- **Best Compatibility:** phpFox >= 4.7.6

### Bugs fixed ###

- Minor layout issue on Menu

### Improvements ###

- Support manage menu item

### Changed files ###

- M Ajax/Ajax.php
- M Controller/AddController.php
- M Controller/WidgetController.php
- M Install.php
- A Installation/Database/PagesMenuTable.php
- M README.md
- M Service/Callback.php
- M Service/Pages.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M views/controller/add.html.php
- M views/controller/widget.html.php

## Version 4.7.8 ##

### Information ###

- **Release Date:** July 06, 2020
- **Best Compatibility:** phpFox >= 4.7.6

### Improvements ###

- Support PHP 7.4

### Changed files ###

- M Ajax/Ajax.php
- M Controller/AddController.php
- M Installation/Database/PagesTable.php
- M Installation/Version/v474.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Pages.php
- M Service/Process.php
- M hooks/feed.service_process_addcomment__1.php
- M views/block/photo.html.php
- M views/controller/add.html.php


## Version 4.7.7 ##

### Information ###

- **Release Date:** November 21, 2019
- **Best Compatibility:** phpFox >= 4.7.6

### Improvements ###

- Add a new Liked Pages page.
- Show Liked Pages when enabling Friends Only Community setting.

### Changed files ###

- M Block/Menu.php
- M Block/Profile.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Pages.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M views/block/menu.html.php
- M views/block/photo.html.php
- M views/block/sponsored.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php


## Version 4.7.6 ##

### Information ###

- **Release Date:** July 30, 2019
- **Best Compatibility:** phpFox >= 4.7.6

### Bugs fixed ###

- Sharing a pages widget on Facebook shows a blank image [#2713](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2713)

### Changed files ###

- M Controller/ViewController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Pages.php
- M changelog.md
- M phrase.json
- M views/block/page-feed.html.php
- M views/controller/add.html.php

## Version 4.7.5 ##

### Information ###

- **Release Date:** June 07, 2019
- **Best Compatibility:** phpFox >= 4.7.6

### Improvements ###

- Some improvements and bug fixes.

### Bugs fixed ###

- Shift+Break is not retained in widget CKEditor [#2677](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2677)

### Changed files ###

- M Block/PageFeedBlock.php
- M Controller/IndexController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Pages.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/link.component_ajax_addviastatusupdate.php
- M hooks/photo.component_ajax_process_done.php
- M phrase.json
- M views/block/category.html.php
- D views/block/link-listing.html.php
- M views/block/link.html.php
- M views/block/page-feed.html.php
- M views/block/photo.html.php
- M views/controller/admincp/integrate.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php


## Version 4.7.4 ##

### Information ###

- **Release Date:** April 12, 2019
- **Best Compatibility:** phpFox >= 4.7.4

### Improvements ###
- Support to add a comment on page profile/cover feed item.

### Bugs fixed ###
- Edit category/sub-category - Not change on categories block until a clear cache.
- Page Admins: cannot find users that changed their username [#2610](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2610).
- Sharing internal links to pages on status doesn't show any thumbnail image [#2054](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2054)

### Changed files ###

- M Controller/Admin/IndexController.php
- M Controller/PhotoController.php
- M Controller/ViewController.php
- M Install.php
- A Installation/Version/v474.php
- M Job/GenerateMissingThumbnails.php
- M README.md
- M Service/Callback.php
- M Service/Pages.php
- M Service/Process.php
- M Service/Type.php
- M changelog.md
- M hooks/photo.component_ajax_process_done.php
- M hooks/photo.set_cover_photo_for_item.php
- M installer.php
- M phrase.json
- M views/block/photo.html.php

## Version 4.7.3 ##

### Information ###

- **Release Date:** March 04, 2019
- **Best Compatibility:** phpFox >= 4.7.4

### Improvements ###

- Support editing status feeds with Links.
- Support setting for showing created date of pages [#2533](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2533).
- "No pages found." - Change font-size.

### Bugs fixed

- Deleting a pages status doesn't delete the feed.
- Number of likes is not decreased if site admin delete user account who is page's member [#2564](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2564).
- Admin names is not changed if they change their profile names [#2568](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2568).
- Changing page avatar will still apply with page cover photo [#2571](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2571).
- Duplicated notification to owner when post status on Page detail.
- Missed page name in mail title when a user post link on page.
- After Admin approved for Claim page -> The Founder on Page Admin group does not change until cache is cleared.
- Deleting pages status with link does not go away until refreshed.

### Changed files ###

- M Ajax/Ajax.php
- M Block/Menu.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Pages.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/feed.service_process_addcomment__1.php
- M hooks/feed.service_process_deletefeed.php
- M hooks/link.component_ajax_addviastatusupdate.php
- A hooks/user.service_process_update_1.php
- M phrase.json
- M views/block/menu.html.php
- M views/controller/add.html.php
- M views/controller/index.html.php


## Version 4.7.2

### Information

- **Release Date:** January 15, 2019
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs Fixed

- Site admin cannot claim pages.
- Site admin cannot reassign owner to site admin.
- Feeds - Not show the content in feed when user enter HTML tag.
- Cannot create a pages sub category within a newly created empty category ([#2525](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2525)).
- Page detail - Click on Like -> Take long time to action effect.
- Edit page - Set Landing page be Videos - Still show feed when access to page.
- Invite people to like page - Can select people who liked/be invited to send invitation.
- Email shows wrong full name in subject and content when a user posting photo ([#2549](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2549)).

### Improvements

- Clear cache when deleting/creating a category/sub-category.

### Changed Files

- M Ajax/Ajax.php
- M Block/Menu.php
- M Block/Photo.php
- M Block/ReassignOwner.php
- M Controller/AddController.php
- M Controller/Admin/AddCategoryController.php
- M Controller/Admin/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Pages.php
- M Service/Process.php
- M assets/autoload.js
- M hooks/friend.component_block_search_get.php
- M hooks/photo.component_ajax_process_done.php
- M views/block/joinpage.html.php
- M views/block/link.html.php
- M views/block/menu.html.php
- M views/block/reassign-owner.html.php
- M views/controller/add.html.php

## Version 4.7.1

### Information

- **Release Date:** December 14, 2018
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs Fixed

- Page avatar thumbnail not aligned when scrolling down.

### Changed Files

- M views/block/photo.html.php

## Version 4.7.0

### Information

- **Release Date:** December 12, 2018
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs Fixed

- Responsive - Pages >> Members - Page title be hidden by Search box.
- Notification of transferring page links to a group.
- Page members load wrong when click on paging.
- Create new page - Load all sub categories.
- Delete categories - Show wrong for parent categories on "Move to" categories.
- Responsive - Missed app icon on main menu.
- Enable "Friends Only Community setting" - Still show all pages.
- Edit thumbnail does not save.
- ACP - Manage Sub-Categories - Missed Breadcrumb list.
- Search in Pending Pages will redirect to All Pages.
- Manage - Photo - Edit thumbnail issues.
- Manage - Widgets - Can not edit widget.
- Manage - Widgets - Table Header be wrong color.
- Notification of assigning owner of page link to a group.
- Pages Detail - Page's menu - Selected menu not change color.
- Show own pages in Friends' Pages.
- Login as Page's admin - After unlike that page, still can manage page.
- Show wrong feed after add a page's admin.
- Can not retain size or position of pages avatar after upgrade.

### Improvements

- Should improve layout for "sharing page" feed.
- Disable filter on home page.

### New Features

- Feed - Update layout follow new design.

### Changed Files

- M Ajax/Ajax.php
- M Block/AddPage.php
- M Block/DeleteCategory.php
- A Block/PageFeedBlock.php
- M Controller/Admin/IndexController.php
- M Controller/FrameController.php
- M Controller/IndexController.php
- M Install.php
- A Installation/Version/v470.php
- M Job/GenerateMissingThumbnails.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Pages.php
- M Service/Process.php
- M Service/Type.php
- M assets/autoload.js
- M assets/main.less
- M hooks/core.template_block_upload_form_action_1.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/add-page.html.php
- M views/block/cropme.html.php
- M views/block/delete-category.html.php
- A views/block/page-feed.html.php
- M views/block/photo.html.php
- M views/controller/add.html.php
- M views/controller/admincp/add.html.php

## Version 4.6.3

### Information

- **Release Date:** October 12, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Bugs Fixed

- Owner of pages can't manage them if Admin turns off this setting "Can create a page".
- Notification not show when others user comment on your posted link/video/photo in your pages.
- Show duplicate on sponsored block when re-sponsor a denied item.
- Pending pages: Show wrong pages.
- Page admins block: Not load admins account.
- Like page: After click on Like button, the button not change.
- Count wrong All members in page.
- Can not login as page.
- Friends' Pages: Show all pages instead of Friends' pages.
- Sponsor a page with payment: Not show Sponsor this page action.
- Sponsor with payment: show wrong price.

### Improvements

- Improve performance.
- When Create pages and groups, please only allow sub category drop down to show if we have created sub categories.
- Be able to delete posts from apps in feed example from pages groups no option.
- Sitewide block 6 code insert does not display / work for pages > blogs, videos, events, forums, photos.
- Notification not show when others user comment on your posted/share video in your pages.

### Changed Files

- D .gitignore
- M Ajax/Ajax.php
- M Block/AddPage.php
- M Block/Admin.php
- M Block/DeleteCategory.php
- M Block/Sponsored.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/MembersController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/PagesAdminTable.php
- M Installation/Database/PagesPermTable.php
- M Installation/Database/PagesTextTable.php
- M Installation/Database/PagesUrlTable.php
- M Installation/Database/PagesWidgetTextTable.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Pages.php
- M Service/Process.php
- M Service/Type.php
- D app.lock
- M changelog.md
- M hooks/feed.service_process_deletefeed.php
- M views/block/add-page.html.php
- M views/block/delete-category.html.php
- M views/controller/view.html.php



## Version 4.6.2

### Information

- **Release Date:** Aug 21st, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Improvements
- Support fix profile menu on Material Template
- Can add Feature and Sponsor for Pages

### Bugs Fixed
- Profile picture in Created Feed does not rotate as following the current page Profile picture
- Can't view pages member
- Can not add new page if disable feed module
- User Profile - Page info still displaying banned word
- Giving administrator privileges for pages you can add 2 same users with admin privileges
- SEO - when you add description and keywords they dont show when view source
- Re-assign Owner for Pages
- Not show events in Page
- Edit photo and then save thumbnail does nothing in pages
- Activity point be decreased when delete a pending page
- Not have notification emails for page owner when others like and post items on page
- Displaying Group cannot be not found when click on Page profile's photo
- Owner of Page received Email with wrong language when anyone have any actions (like, comment, posted items...) in it
- Content be incorrect in User Group Settings in Pages app

### Changed Files
- M Ajax/Ajax.php
- A Block/Featured.php
- M Block/Pending.php
- M Block/Photo.php
- A Block/ReassignOwner.php
- A Block/Sponsored.php
- M Controller/AddController.php
- M Controller/Admin/IntegrateController.php
- M Controller/IndexController.php
- M Controller/PhotoController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/PagesTable.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Facade.php
- M Service/Pages.php
- M Service/Process.php
- M assets/main.less
- M changelog.md
- M hooks/feed.service_process_addcomment__1.php
- M hooks/link.component_ajax_addviastatusupdate.php
- M hooks/photo.component_ajax_process_done.php
- M phrase.json
- M start.php
- D tests/Service/BrowseTest.php
- D tests/Service/PagesTest.php
- A views/block/featured.html.php
- M views/block/link-listing.html.php
- M views/block/link.html.php
- M views/block/photo.html.php
- A views/block/reassign-owner.html.php
- A views/block/sponsored.html.php
- M views/controller/add.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php

## Version 4.6.1

### Information

- **Release Date:** May 10th, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Improvements
- Disable feature Share when login as pages.
- Support fix profile menu on Material Template.

### Bugs Fixed
- Page meta description is not working when sharing page in Facebook.
- Delete page's feed works not correct.
- Do not show photo after upgrade from V3.
- Create popup - Buttons are not align on IE.
- Feed does not parse \[username] after edit feed.
- Move the "Claim" button to dropdown.
- Show error after uploading photo.
- Add friend buttons disappear when change tabs in member page.
- Do not focus on profile menu when select on the integrate apps.

### Changed Files

## Version 4.6.0

### Information

- **Release Date:** January 9th, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Allow users can re-claim after admin deny their claim.
- Support drag/drop, preview, progress bar when users upload photos.
- Validate all settings, user group settings, and block settings.
- Improve layout of all pages and blocks.

### New Features

- Add `Members` tab in pages profile page.

### New User Group Settings

| ID | Var name  | Description |
| --- | -------- | ----------- |
| 1 | pages.flood_control | Define how many minutes this user group should wait before they can add new group. Note: Setting it to "0" (without quotes) is default and users will not have to wait. |

### Changed files
- M Ajax/Ajax.php
- M Block/AddPage.php
- M Block/Admin.php
- M Block/Category.php
- M Block/DeleteCategory.php
- M Block/Like.php
- M Block/Menu.php
- M Block/Pending.php
- M Block/PeopleAlsoLike.php
- M Block/Photo.php
- M Block/Profile.php
- D Block/ProfilePhoto.php
- A Block/SearchMember.php
- M Controller/AddController.php
- M Controller/Admin/AddCategoryController.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/IntegrateController.php
- M Controller/FrameController.php
- M Controller/IndexController.php
- A Controller/MembersController.php
- M Controller/PhotoController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Version/v453.php
- A Installation/Version/v460.php
- A Job/GenerateMissingThumbnails.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Pages.php
- M Service/Process.php
- M Service/Type.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/autoload.less
- A assets/img/default-category/default_category.png
- A assets/img/default_pagecover.png
- M assets/main.less
- M change-log.md
- A hooks/bundle__start.php
- M hooks/comment.service_comment_massmail__1.php
- A hooks/core.template_block_upload_form_action_1.php
- M hooks/friend.component_block_search_get.php
- M hooks/get_module_blocks.php
- A hooks/job_queue_init.php
- A hooks/mail.component_ajax_compose_process_success.php
- M hooks/photo.component_ajax_process_done.php
- A hooks/photo.service_process_make_profile_picture__end.php
- M hooks/run.php
- A hooks/validator.admincp_settings_pages.php
- A hooks/validator.admincp_user_settings_pages.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/add-page.html.php
- M views/block/admin.html.php
- M views/block/cropme.html.php
- M views/block/delete-category.html.php
- M views/block/joinpage.html.php
- M views/block/like.html.php
- M views/block/link-listing.html.php
- M views/block/link.html.php
- M views/block/login.html.php
- M views/block/menu.html.php
- M views/block/pending.html.php
- M views/block/people-also-like.html.php
- M views/block/photo.html.php
- D views/block/profile-photo.html.php
- M views/block/profile.html.php
- A views/block/search-member.html.php
- M views/controller/add.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/claim.html.php
- M views/controller/admincp/index.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php
- A views/controller/members.html.php
- M views/controller/view.html.php
- M views/controller/widget.html.php

## Version 4.5.3

### Information

- **Release Date:** September 21st, 2017
- **Best Compatibility:** phpFox >= 4.5.3

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | pages.show_page_admins | Show Page Admins | Move to setting of block Admin |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | pages.pages_limit_per_category | Pages Limit Per Category | Define the limit of how many pages per category can be displayed when viewing All Pages page. |
| 2 | pages.pagination_at_search_page | Paging Type |  |
| 3 | pages.display_pages_profile_photo_within_gallery | Display pages profile photo within gallery | Disable this feature if you do not want to display pages profile photos within the photo gallery. |
| 4 | pages.display_pages_profile_photo_within_gallery | Display pages profile photo within gallery | Disable this feature if you do not want to display pages profile photos within the photo gallery. |
| 5 | pages.display_pages_cover_photo_within_gallery | Display pages cover photo within gallery | Disable this feature if you do not want to display pages cover photos within the photo gallery. |
| 6 | pages_meta_description | Pages Meta Description | Meta description added to pages related to the Pages app. |
| 7 | pages_meta_keywords | Pages Meta Keywords | Meta keywords that will be displayed on sections related to the Pages app. |

### Removed User Group Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | pages.can_moderate_pages | Can moderate pages? This will allow a user to edit/delete/approve pages added by other users. | Don't use anymore, split to 2 new user group settings "Can edit all pages?", "Can delete all pages?" |

### New User Group Settings

| ID | Var name | Name |
| --- | -------- | ---- |
| 1 | pages.can_edit_all_pages | Can edit all pages? |
| 2 | pages.can_delete_all_pages | Can delete all pages? |

### Deprecated Functions

| ID | Class Name | Function Name | Will Remove In | Reason |
| --- | -------- | ---- | ---- | ---- |
| 1 | Ajax | signup | 4.6.0 | Don't use anymore |
| 2 | Callback | getNotificationJoined | 4.6.0 | Don't use anymore |
| 3 | Callback | getNotificationRegister | 4.6.0 | Don't use anymore |
