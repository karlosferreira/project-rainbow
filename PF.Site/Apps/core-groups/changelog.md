# Core Groups :: Changelog

## Version 4.7.13 ##

### Information ###

- **Release Date:** July 08, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Bugs fixed ###

- Some minor bugs

### Improvements ###

- Compatible with PHP 8.0 and phpFox 4.8.6
- Limitation on number of groups created by user group [#585](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/585)

### Changed files ###

- M Ajax/Ajax.php
- M Block/FeedGroupBlock.php
- M Block/GroupAbout.php
- M Block/GroupCategory.php
- M Block/GroupMenu.php
- M Block/ReassignOwner.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/PhotoController.php
- M Install.php
- M Job/SendMemberNotification.php
- M Service/Callback.php
- M Service/Groups.php
- M Service/Process.php
- M assets/autoload.js
- M phrase.json
- M views/block/photo.html.php

## Version 4.7.12 ##

### Information ###

- **Release Date:** April 19, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Bugs fixed ###

- Redundant confirmation popup when editing photo in Manage page
- Missing mass action in Member page after searching
- Show error when approving a user if he has been a group's admin
- Some minor issues about email

### Improvements ###

- Support review Photo before publishing for Group Avatar and Cover
- Support review Email Subject and Content

### Changed files ###

- M Ajax/Ajax.php
- M Block/FeedGroupBlock.php
- M Block/GroupCropme.php
- M Block/GroupPhoto.php
- M Controller/AddController.php
- M Controller/FrameController.php
- M Controller/PhotoController.php
- M Controller/ViewController.php
- M Install.php
- M Job/SendMemberNotification.php
- M Service/Callback.php
- M Service/Facade.php
- M Service/Groups.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M hooks/link.component_ajax_addviastatusupdate.php
- M phrase.json
- M views/block/about.html.php
- M views/block/cropme.html.php
- M views/block/feed-group.html.php
- M views/block/photo.html.php
- M views/controller/index.html.php

## Version 4.7.11 ##

### Information ###

- **Release Date:** March 03, 2021
- **Best Compatibility:** phpFox >= 4.8.2

### Bugs fixed ###

- Some minor issues of phrase
- Missing css for gear icon if site do not install Page app
- Wrong group's url in email content with reassigning owner
- Missing email when mentioning friend in Group status
- Non-members can not view closed groups on Feed
- Anyone can view pending members via url

### Improvements ###

- Allow set default permission value for items
- Apply leave page confirmation on manage Group

### Changed files ###

- M Ajax/Ajax.php
- M Block/GroupCropme.php
- M Install.php
- M Job/SendMemberJoinNotification.php
- M Job/SendMemberNotification.php
- M Service/Callback.php
- M Service/Groups.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- D checksum
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M hooks/core.template_block_upload_form_action_1.php
- M hooks/feed.service_feed_get_custom_module.php
- M hooks/feed.service_process_addcomment__1.php
- M hooks/link.component_ajax_addviastatusupdate.php
- D hooks/photo.component_ajax_process_done.php
- M phrase.json
- M views/block/about.html.php
- M views/block/add-group.html.php
- M views/block/cropme.html.php
- M views/block/menu.html.php
- M views/block/reassign-owner.html.php
- M views/block/search-member.html.php
- M views/controller/add.html.php
- M views/controller/members.html.php
- M views/controller/widget.html.php

## Version 4.7.10 ##

### Information ###

- **Release Date:** November 09, 2020
- **Best Compatibility:** phpFox >= 4.8.2

### Bugs fixed ###

- Can not view Group detail after switch Friends Only Community setting
- Missing phrases.

### Improvements ###

- Hide all posts of Public Group in Home Feed of non-member
- Add 'Report' action on status which posted on Group

### Changed files ###

- M Ajax/Ajax.php
- M Controller/AddController.php
- M Controller/PhotoController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Process.php
- M changelog.md
- A hooks/feed.service_feed_get_custom_module.php
- A hooks/module_getcomponent_start.php
- M phrase.json
- M views/controller/add.html.php

## Version 4.7.9 ##

### Information ###

- **Release Date:** August 24, 2020
- **Best Compatibility:** phpFox >= 4.7.6

### Bugs fixed ###

- Correct email content when creating item in Groups

### Improvements ###

- Support manage item menu
- Improve notification message when someone posts in Groups
- Show full cover photo in Feed 

### Changed files ###

- M Ajax/Ajax.php
- M Controller/ViewController.php
- M Installation/Version/v474.php
- M Service/Callback.php
- M views/block/photo.html.php

## Version 4.7.8 ##

### Information ###

- **Release Date:** July 01, 2020
- **Best Compatibility:** phpFox >= 4.7.6

### Improvements ###

- Support PHP 7.4

### Changed files ###

- M Ajax/Ajax.php
- M Controller/ViewController.php
- M Installation/Version/v474.php
- M Service/Callback.php
- M views/block/photo.html.php


## Version 4.7.7 ##

### Information ###

- **Release Date:** November 21, 2019
- **Best Compatibility:** phpFox >= 4.7.6

### Improvements ###

- Add a new Joined Pages page.
- Show Joined Groups when enabling Friends Only Community setting.
- Group detail - Public Group - Should show Join button in detail page when guest access.

### Changed files ###

- M Block/GroupProfile.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Groups.php
- M Service/Process.php
- M assets/main.less
- A hooks/admincp.service_maintain_delete_files_get_list.php
- D hooks/link.component_service_callback_getactivityfeed__1.php
- M phrase.json
- M views/block/joinpage.html.php
- M views/block/menu.html.php
- M views/block/photo.html.php
- M views/block/related.html.php
- M views/block/sponsored.html.php
- M views/block/widget.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php


## Version 4.7.6 ##

### Information ###

- **Release Date:** July 30, 2019
- **Best Compatibility:** phpFox >= 4.7.6

### Improvements ###

- User could able to cancel pending request to a close group

### Bugs fixed ###

- Sharing a groups widget on Facebook shows a blank image [#2713](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2713)
- Pending Membership, when clicked on does not take us to pending [#2716](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2716)

### Changed files ###

- M Ajax/Ajax.php
- M Controller/MembersController.php
- M Controller/ViewController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Category.php
- M Service/Groups.php
- M Service/Process.php
- M changelog.md
- M phrase.json
- M views/block/joinpage.html.php
- M views/controller/members.html.php
- M views/controller/view.html.php

## Version 4.7.5 ##

### Information ###

- **Release Date:** June 07, 2019
- **Best Compatibility:** phpFox >= 4.7.6

### Improvements ###

- Some improvements and bug fixes.

### Bugs fixed ###

- Shift+Break is not retained in widget CKEditor [#2677](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2677)

### Changed files ###

- M Block/FeedGroupBlock.php
- M Controller/IndexController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Groups.php
- M Service/Process.php
- M assets/main.less
- M changelog.md
- M hooks/link.component_ajax_addviastatusupdate.php
- A hooks/photo.component_ajax_process_done.php
- M phrase.json
- M views/block/category.html.php
- M views/block/feed-group.html.php
- D views/block/link-listing.html.php
- M views/block/link.html.php
- M views/block/menu.html.php
- M views/block/photo.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php


## Version 4.7.4 ##

### Information ###

- **Release Date:** April 12, 2019
- **Best Compatibility:** phpFox >= 4.7.4

### Improvements ###

- Support to add a comment on page profile/cover feed item.

### Bugs fixed ###
- Layout issues
- Sharing internal links to pages on status doesn't show any thumbnail image [#2054](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2054)

### Changed files ###

- M Controller/Admin/AddCategoryController.php
- M Controller/Admin/CategoryController.php
- M Controller/IndexController.php
- M Controller/PhotoController.php
- M Controller/ViewController.php
- M Install.php
- A Installation/Version/v474.php
- M README.md
- M Service/Callback.php
- M Service/Category.php
- M Service/Groups.php
- M Service/Process.php
- M changelog.md
- M hooks/link.component_service_callback_getactivityfeed__1.php
- M hooks/photo.set_cover_photo_for_item.php
- M installer.php
- M phrase.json
- M views/block/photo.html.php
- M views/controller/add.html.php
- M views/controller/index.html.php

## Version 4.7.3 ##

### Information ###

- **Release Date:** March 04, 2019
- **Best Compatibility:** phpFox >= 4.7.4

### Improvements ###

- Support editing status feeds with Links.

### Bugs fixed ###

- Number of members is not updated after site admin delete user account who is group's member [#2564](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2564).
- Changing page avatar will still apply with page cover photo [#2571](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2571).
- No have notifications to owner/admin when post status on Group detail.
- Missed group name in mail title when a user post link on group.
- Bootstrap - Group Detail - Publish date with wrong size.
- Deleting groups status with link does not go away until refreshed [#2580](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2580).

### Changed files ###

- M Ajax/Ajax.php
- M Block/GroupAbout.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Groups.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/feed.service_process_addcomment__1.php
- M hooks/feed.service_process_deletefeed.php
- M hooks/link.component_ajax_addviastatusupdate.php
- M phrase.json
- M views/block/about.html.php

## Version 4.7.2

### Information

- **Release Date:** January 18, 2019
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs fixed

- Group Feeds - Not show the content in feed when user enter HTML tag.
- Groups: Site admin cannot reassign owner to site admin.
- Invite People via Email - Guest can view info and members in Secret group by url.

### Improvements

- No redirection to parent category page if deleting a sub-category.

### Changed files

- M Ajax/Ajax.php
- M Block/GroupEvents.php
- M Block/ReassignOwner.php
- M Controller/Admin/AddCategoryController.php
- M Controller/Admin/CategoryController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Groups.php
- M Service/Process.php
- M phrase.json
- M views/block/reassign-owner.html.php
- M views/controller/add.html.php


## Version 4.7.1

### Information

- **Release Date:** December 14, 2018
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs fixed

- Group avatar thumbnail not aligned when scrolling down.

### Changed files

- M views/block/photo.html.php

## Version 4.7.0

### Information

- **Release Date:** December 12, 2018
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs fixed

- Not have notification email for group owner when others join group.
- Notification not show when others user posted status/video/photo in your group.
- Responsive - Member - Group title be hidden by Search box.
- Group status is not consistency.
- Group member load wrong when click on paging.
- Create new group - Load all sub categories.
- Create new group popup - Type field hide sub categories list.
- Delete categories - Not load parent categories on "Move to" categories.
- Manage - Photo -  Edit thumbnail issues.
- Show wrong feed after add a group's admin.
- Feed - Show group's info with not clear html format.
- Material - Pending Membership button be wrong style.
- Group Detail - Group's menu - Selected menu not change color.
- Can post photo from Group feed when user has no permission.
- Missed Share action on Group Mini Menu block.
- Enable "Friends Only Community setting" - Still show all groups.
- Groups list - Not update Member number after Reassign owner.
- Not has notification for group's owner and admin when a user join group.
- Manage - Invite - Not update status to Invited after sent invitations to friends.
- Show timestamp on sharing feed.
- Sharing group feed missed phrase for category (parent category).
- Not update members number after add group's admin.
- Show own groups in Friends' Groups.
- Login as Group's admin - After unjoin that group, still can manage group.
- Can not view content of "Sharing a group" until user join that group.
- Can send many request to join closed group.
- Responsive - Sharing group feed - Default Page Avatar be not in circle.

### Improvements

- Disable filter in homepage.

### New Features

- Feed - Update layout follow new design.

### Changed files

- M Ajax/Ajax.php
- M Block/AddGroup.php
- A Block/FeedGroupBlock.php
- M Block/GroupDeleteCategory.php
- M Block/GroupMenu.php
- M Block/SearchMember.php
- M Controller/ViewController.php
- M Controller/IndexController.php
- M Install.php
- A Installation/Version/v464.php
- A Installation/Version/v470.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Groups.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M hooks/core.template_block_upload_form_action_1.php
- M hooks/friend.component_block_search_get.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/add-group.html.php
- M views/block/cropme.html.php
- M views/block/delete-category.html.php
- A views/block/feed-group.html.php
- M views/block/menu.html.php
- M views/block/photo.html.php
- M views/controller/add.html.php
- M views/controller/admincp/add-category.html.php

## Version 4.6.3

### Information

- **Release Date:** October 11, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Bugs fixed

- This usergroup can not manage groups which they are admins if admin turn off setting "Allow to create groups"
- Phrase is missing when joined a group
- Can send multiple request to join closed group
- Can't create new widget in group
- Groups - Group Detail - Showing 2 messages when searching a request on pending requests tab 
- Groups - Group Detail - Sort by category works incorrectly
- Groups - The site is redirected to page all groups after searching groups
- Groups -  Reassign owner function works incorrectly
- BE - Groups - Manage categories - 500 error when deleting a category
- Groups - User can see the group even though the groups privacy is secret
- Show error when search

### Improvements

- Improve performance
- When Create pages and groups, please only allow sub category drop down to show if we have created sub categories
- Able to delete feed in detail pages and groups

### Changed files

- D .gitignore
- M Ajax/Ajax.php
- M Block/AddGroup.php
- M Block/GroupCategory.php
- M Block/GroupDeleteCategory.php
- M Block/GroupMenu.php
- M Controller/AddController.php
- M Controller/FrameController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/PagesAdminTable.php
- M Installation/Database/PagesPermTable.php
- M Installation/Database/PagesTextTable.php
- M Installation/Database/PagesUrlTable.php
- M Installation/Database/PagesWidgetTextTable.php
- M Job/ConvertOldGroups.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category.php
- M Service/Groups.php
- M Service/Process.php
- M Service/Type.php
- D app.lock
- M assets/main.less
- M changelog.md
- M hooks/feed.service_process_deletefeed.php
- M phrase.json
- M start.php
- M views/block/about.html.php
- M views/block/add-group.html.php
- M views/block/delete-category.html.php
- M views/block/search-member.html.php
- M views/block/widget.html.php
- M views/controller/index.html.php

## Version 4.6.2

### Information

- **Release Date:** Aug 21, 2018
- **Best Compatibility:** phpFox >= 4.6.1


### Improvements
- Support fix profile menu on Material Template
- Groups Detail - Members - Group owner should be shown in the first as always

### Bugs fixed
- Profile picture in Created Feed does not rotate as following the current group Profile picture
- SEO - No phrase input for description and keywords
- Can't view groups member
- Can not view group detail if disable page module
- Title not unify with other module
- Delete feed from group but main feed still exist
- Group invite email shows incorrect format no link to click on
- Displaying Page not found after clicking on Group Photo on Photo Detail page
- Groups - Edit - Can Edit group by ID of page
- Show error page when disable feed module
- When selecting your group on Android phone, selecting drop down menu to add photo, video etc doesn't work
- User Profile - Group info still displaying banned word
- Edit photo and then save thumbnail does nothing in groups
- Giving administrator privileges for groups you can add 2 same users with admin privileges
- Owner of Groups received Email with wrong language when anyone have any actions (join group, comment, posted items...) in it
- Can not create group if does not install page app
- Approve bar not disappear after approved
- Edit Group: Not load sub-categories when changed Category
- Does not show "Friend's Groups" menu for non-login user
- Manage Categories in ACP: Layout issue when drag categories

### Changed files
- M Ajax/Ajax.php
- A Block/Featured.php
- M Block/GroupPhoto.php
- A Block/ReassignOwner.php
- M Block/SearchMember.php
- A Block/Sponsored.php
- M Controller/AddController.php
- M Controller/Admin/IntegrateController.php
- M Controller/IndexController.php
- M Controller/PhotoController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/PagesTable.php
- M Job/ConvertOldGroups.php
- M Job/SendMemberJoinNotification.php
- M Job/SendMemberNotification.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Facade.php
- M Service/Groups.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/feed.service_process_addcomment__1.php
- A hooks/feed.service_process_deletefeed_end.php
- M hooks/get_module_blocks.php
- M hooks/link.component_ajax_addviastatusupdate.php
- M phrase.json
- M start.php
- A views/block/featured.html.php
- M views/block/link-listing.html.php
- M views/block/link.html.php
- M views/block/photo.html.php
- A views/block/reassign-owner.html.php
- M views/block/related.html.php
- A views/block/sponsored.html.php
- M views/controller/add.html.php
- M views/controller/admincp/category.html.php
- M views/controller/all.html.php
- M views/controller/index.html.php

## Version 4.6.1

### Information

- **Release Date:** April 26, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Bugs fixed
- Show "Unable to find the page you are looking for." when click on profile photo.
- Can't upload profile image in manage group if using S3.
- Can't reorder sub-category.
- Lite package can not upload photo if enable debug mode.
- Missing save button when reposition cover photo.

### Changed files
- A hooks/link.component_service_callback_getactivityfeed__1.php
- M	Controller/IndexController.php
- M	Install.php
- M	Installation/Version/v460.php
- M	Service/Callback.php
- M	Service/Groups.php
- M	Service/Process.php
- M	assets/main.less
- M	phrase.json
- M	start.php
- M	views/block/about.html.php
- M	views/block/photo.html.php
- M	views/controller/admincp/category.html.php

## Version 4.6.0

### Information

- **Release Date:** January 9th, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Support groups admin can re order widgets.
- Support upload thumbnail photo for main categories.
- Add statistic information of Groups (total items, pending items...) into Sit Statistics.
- Users can select actions of groups on listing page same as on detail page.
- Count items on menu My Groups.
- Support drag/drop, preview, progress bar when users upload thumbnail photos for groups.
- Hide all buttons/links if users don't have permission to do.
- Support 3 styles for pagination.
- Allow admin can change default photos.
- Validate all settings, user group settings, and block settings.
- Update new layout for all blocks and pages.

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | groups.pf_group_show_admins | Show Group Admins | Move to setting of block Admin |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | groups.groups_limit_per_category | Groups Limit Per Category | Define the limit of how many groups per category can be displayed when viewing All Groups page. |
| 2 | groups.pagination_at_search_groups | Paging Style |  |
| 3 | groups.display_groups_profile_photo_within_gallery | Display groups profile photo within gallery | Disable this feature if you do not want to display groups profile photos within the photo gallery. |
| 5 | groups.display_groups_cover_photo_within_gallery | Display groups cover photo within gallery | Disable this feature if you do not want to display groups cover photos within the photo gallery. |
| 6 | groups_meta_description | Groups Meta Description | Meta description added to groups related to the Groups app. |
| 7 | groups_meta_keywords | Groups Meta Keywords | Meta keywords that will be displayed on sections related to the Groups app. |

### Removed User Group Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | groups.pf_group_moderate | Can moderate groups? This will allow a user to edit/delete/approve groups added by other users. | Don't use anymore, split to 3 new user group settings "Can edit all groups?", "Can delete all groups?", "Can approve groups?" |

### New User Group Settings

| ID | Var name | Information |
| --- | -------- | ---- |
| 1 | groups.can_edit_all_groups | Can edit all groups? |
| 2 | groups.can_delete_all_groups | Can delete all groups? |
| 3 | groups.can_approve_groups | Can approve groups? |
| 4 | groups.flood_control| Define how many minutes this user group should wait before they can add new group. Note: Setting it to "0" (without quotes) is default and users will not have to wait. |
