# Marketplace  :: Change Log

## Version 4.7.2

### Information

- **Release Date:** October 6, 2021
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs fixed

- User can still create listing inside Page via URL although has no permission to browse listings
- GIF user avatar is cut in Manage Invites tab of editing listing
- Items of secret groups is showing publicly via default RSS feeds
- Group's admin/owner get email with wrong language phrases when creating listing on Group
- Layout issue on Create listing feed

### Improvements

- Does not allow invite guests that are not members in Secret/Closed Group
- Limitation on number of listings created by user group
- Auto tag when click out of box or click on 'Send Invitation' button
- Change text of the confirm popup when deleting the listing

### Changed Files

- M Block/RowsBlock.php
- M Controller/AddController.php
- M Controller/FrameUploadController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Version/v462.php
- A Service/Api.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category/Category.php
- M Service/Marketplace.php
- M Service/Process.php
- A api.md
- M assets/autoload.js
- A assets/invite.js
- M assets/main.less
- A hooks/friend.component_block_search_get.php
- A hooks/route_start.php
- M hooks/validator.admincp_user_settings_marketplace.php
- M phrase.json
- M start.php
- M views/block/info.html.php
- M views/block/rows.html.php
- M views/controller/add.html.php
- M views/controller/view.html.php

## Version 4.7.1

### Information

- **Release Date:** June 29, 2020
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs fixed

- Layout issue when add new listing without payment section
- Doesn't delete attachment after deleted listing

### Improvements

- Hide payment section when add/edit listing if users don't setup payment information in their account
- Hide payment section when add/edit listing if users is logging as page
- Remove Sponsor and Sponsor In Feed actions for listings when Login As Page
- Support Contact Seller with Instant Messaging app.
- Support Contact Seller with ChatPlus app.

### Changed Files

- M assets/autoload.js
- M assets/main.less
- M Controller/AddController.php
- M Controller/ViewController.php
- M Controller/PurchaseController.php
- M Block/InfoBlock.php
- M Service/Marketplace.php
- M Service/Process.php
- M views/block/info.html.php
- M views/block/menu.html.php
- M views/controller/add.html.php
- M phrase.json
- M Install.php

## Version 4.7.0

### Information

- **Release Date:** November 21, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Improvements

- Integrate with Pages/Groups.

### Changed Files

- M Ajax/Ajax.php
- M Block/FeaturedBlock.php
- M Block/MyBlock.php
- M Block/SponsoredBlock.php
- M Controller/AddController.php
- M Controller/Admin/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Marketplace.php
- M Service/Callback.php
- M Service/Category/Process.php
- M Service/Marketplace.php
- M Service/Process.php
- M assets/main.less
- M phrase.json
- M views/block/feed.html.php
- M views/block/info.html.php
- M views/block/rows.html.php
- M views/controller/add.html.php
- M views/controller/view.html.php


## Version 4.6.4

### Information

- **Release Date:** September 25, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Improvements

- Update layouts.
- Support add/edit listing location with Google Maps autocomplete.
- Support show listings on Google Maps.
- Support search listings on Google Maps.

### Changed Files

- M Block/RelatedBlock.php
- M Block/RowsBlock.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Marketplace.php
- A Installation/Version/v464.php
- A Job/ConvertOldLocation.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Marketplace.php
- M Service/Process.php
- M assets/main.less
- A hooks/job_queue_init.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/feed.html.php
- M views/block/info.html.php
- M views/block/mini.html.php
- M views/block/rows.html.php
- M views/controller/add.html.php
- M views/controller/index.html.php
- M views/controller/view.html.php


## Version 4.6.3

### Information

- **Release Date:** May 03, 2019
- **Best Compatibility:** phpFox >= 4.6.1

### Improvements

- Allow seller to enable/disable Activity Point payment.

### Changed Files

- M Controller/IndexController.php
- M Controller/PurchaseController.php
- M Install.php
- M Installation/Database/Marketplace.php
- A Installation/Version/v463.php
- M README.md
- M Service/Callback.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/mail.component_controller_compose_controller_validation.php
- M hooks/template_template_getmenu_3.php
- M installer.php
- M phrase.json
- M views/block/feed.html.php
- M views/controller/add.html.php
- M views/controller/invoice/index.html.php
- M views/controller/purchase.html.php
- M views/controller/view.html.php


## Version 4.6.2

### Information

- **Release Date:** September 10, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Bugs fixed

- Missing description for Activity Points User group settings.
- Marketplace - Duplicated the "jpeg" in error message.
- Marketplace - Manage invite - Photos are stress.
- Missing plugin to support third party (favourite).
- Show error page when disable feed module.
- Short Description still displaying banned word.
- Owner of listing received Email with wrong language when anyone have any actions (like, comment, ...) in it.

### Improvements

- Check integration with RSS app.
- Should have feature allow users/admin can reopen expired listings.
- Invoices page - Should have some blocks same as other pages.
- Add new setting to disallow/allow app to post on Main feed when add new item. (default is allow)

### Changed Files

- M Ajax/Ajax.php
- M Block/FeedBlock.php
- M Block/RowsBlock.php
- M Block/SponsoredBlock.php
- M Controller/Admin/IndexController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Marketplace_Category_Data.php
- M Installation/Database/Marketplace_Text.php
- A Installation/Version/v462.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category/Category.php
- M Service/Category/Process.php
- M Service/Marketplace.php
- M Service/Process.php
- M assets/main.less
- M changelog.md
- M installer.php
- M phrase.json
- M start.php
- M views/block/info.html.php
- M views/block/list.html.php
- M views/block/menu.html.php
- M views/block/mini.html.php
- M views/controller/add.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/index.html.php
- M views/controller/invoice/index.html.php
- M views/controller/view.html.php

## Version 4.6.1

### Information

- **Release Date:** February 13, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Bugs fixed
- The listing photos aren't shown.
- Can drag and drop categories above the title.

### Improvements
- Add additional message when contact seller.

### Changed Files
- M	Controller/IndexController.php
- M	Install.php
- M	README.md
- M	assets/autoload.js
- M	assets/main.less
- M	change-log.md
- A	hooks/mail.component_controller_compose_controller_validation.php
- M	phrase.json
- M	views/controller/admincp/index.html.php
- M	views/controller/view.html.php
- M Service/Category/Process.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Use cron to send expired notifications.
- Support attachments for listing's description.
- Support emoji for listing's description.
- Users can select actions of listings on listing page same as on detail page.
- Count items on menu My Listings.
- Support drag/drop, preview, progress bar when users upload photos.
- Support AddThis on listing detail page.
- Support 3 styles for pagination.
- Allow admin can change default photos.
- Validate all settings, user group settings, and block settings.
- Update layout for all blocks and pages.

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | marketplace_view_time_stamp | Marketplace View Time Stamp | Don't use anymore |
| 2 | total_listing_more_from | Total "More From" Listings to Display | Don't use anymore |
| 3 | how_many_sponsored_listings | How Many Sponsored Listings To Show | Don't use anymore |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | marketplace_paging_mode | Pagination Style | Select Pagination Style at Search Page. |
| 4 | marketplace_meta_description | Marketplace Meta Description | Meta description added to pages related to the Marketplace app. |
| 5 | marketplace_meta_keywords | Marketplace Meta Keywords | Meta keywords that will be displayed on sections related to the Marketplace app. |

### Deprecated Functions

| ID | Class Name | Function Name | Will Remove In | Reason |
| --- | -------- | ---- | ----- | ---- |
| 1 | Apps\Core_Marketplace\Ajax | categoryOrdering | 4.7.0 | Don't use anymore |


