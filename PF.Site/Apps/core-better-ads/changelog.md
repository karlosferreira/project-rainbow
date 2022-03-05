# BetterAds :: Change log

## Version 4.2.9

### Information

- **Release Date:** July 06, 2021
- **Best Compatibility:** phpFox >= 4.7.6

### Fixed Bugs

- Editing Ad: it doesn't load photo when view preview of ad
- Issue with validation process when create a new ad
- Some other minor bugs

### Improvements

- Compatible with PHP 8.0 and phpFox 4.8.6
- Add new settings to allow Admins review and update all email contents of Ads app

### Changed files

- M Ajax/Ajax.php
- M Block/Display.php
- M Block/Inner.php
- M Controller/AddController.php
- M Controller/Admin/AddController.php
- M Controller/ManageController.php
- M Controller/ManageSponsorController.php
- M Controller/SponsorController.php
- M Install.php
- M Service/Ad.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- M Service/Report.php
- M assets/autoload.js
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M hooks/get_module_blocks.php
- M hooks/template_getheader_end.php
- M installer.php
- M phrase.json
- M views/block/targetting.html.php
- M views/controller/invoice.html.php
- M views/controller/manage-sponsor.html.php
- M views/controller/report.html.php

## Version 4.2.8

### Information

- **Release Date:** March 19, 2021
- **Best Compatibility:** phpFox >= 4.7.6

### Fixed Bugs

- Missing some phrases in Sponsor Settings
- Loading forever after activating/deactivating items
- Wrong link after clicking on HTML ad
- Wrong text in Location/State/Province dropdown
- Auto sponsor items on app block 

### Improvements

- Add "Handles non-pages" selector to control non-pages [#2854](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2854)
- Support email notifications for all actions in app

### Changed files

- M Ajax/Ajax.php
- M Block/Display.php
- M Controller/AddController.php
- M Controller/Admin/AddController.php
- M Controller/Admin/AddPlacementController.php
- M Controller/Admin/SponsorSettingController.php
- M Install.php
- M Service/Ad.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M views/controller/add.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/addplacement.html.php

## Version 4.2.7

### Information

- **Release Date:** September 04, 2019
- **Best Compatibility:** phpFox >= 4.7.6

### Fixed Bugs

- When click preview ad its all dark you don't really see the ad [#2779](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2779)

### Changed files

- M Controller/ImageController.php
- M Controller/InvoiceController.php
- M Install.php
- M README.md
- M Service/Ad.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M views/block/display.html.php
- M views/block/targetting.html.php
- M views/controller/admincp/migrate-sponsorships.html.php
- M views/controller/admincp/sponsor.html.php
- M views/controller/invoice.html.php
- M views/controller/manage.html.php


## Version 4.2.6

### Information

- **Release Date:** March 01, 2019
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- Create ad from back end -  Not show Clicks total field.
- No increase click when create 1 ad with type is HTML.
- The Ads do not complete after reaches views or click counter.
- Manage sponsor setting - Layout is not consist with another page.
- Pending ads do not show in alerts from ACP.
- Setting "Unique viewers counter" does not effect.
- Can not preview ad when creating ad from back-end.

### Changed files

- M Ajax/Ajax.php
- M Install.php
- M Service/Ad.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/autoload.less
- M hooks/get_module_blocks.php
- M phrase.json
- M views/block/display.html.php
- M views/block/targetting.html.php
- M views/controller/admincp/add.html.php


## Version 4.2.5

### Information

- **Release Date:** January 04, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- In Back end: Edit an ad -> Create a new ad for that edited ad.
- The numbered/bulleted list is not working on sponsor items page.
- Missing phrase in Manage Apps.
- User has custom gender can not see ads.
- Not write the first letter of some word in capital.
- Missed app menu when scroll down in some pages.
- Missed some phrases in Edit Ad and Ad detail.
- Manage Ads in Back end - Show ads with "n/a" status when filter by Active.
- Ad Detail - A long Destination URL hide Gender field.
- Show wrong url in "Sponsor Ad Approved " mail.
- ACP - Sponsor Settings - Missed one sponsor setting for some apps.
- ACP - Sponsor setting - Feed - Not load currencies in sponsor prices setting.
- Layout issue when a placement has long name.

### Improvements

- Support add setting to control ads blocks.

### Changed files

- M Controller/Admin/AddController.php
- M Controller/IndexController.php
- M Install.php
- M Installation/Version/v422.php
- M README.md
- M Service/Ad.php
- M Service/Get.php
- M Service/Process.php
- M Service/Report.php
- M Service/Sponsor.php
- M assets/main.less
- M changelog.md
- M hooks/get_module_blocks.php
- M hooks/template_getheader_end.php
- M installer.php
- M phrase.json
- M views/block/daily-reports.html.php
- M views/block/targetting.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/index.html.php
- M views/controller/invoice.html.php
- M views/controller/manage.html.php
- M views/controller/report.html.php
- M views/controller/sponsor.html.php

## Version 4.2.4

### Information

- **Release Date:** October 12, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- Click stats are not calculated and displayed correctly in Manage Ad page.
- Groups - Sponsor block does not show.
- Ad detail - Photo be cut.
- Show message pending approval when create an ad without approval.
- Create an ad - On IE - Missing padding between buttons.
- Admin can not Sponsor In Feed an others item.
- Manage sponsorships: View and click number not change when click on a sponsored feed.
- Not show languages in Ad detail.
- Ad detail: Gender - Show comma before if choose "any".
- Manage sponsorship -  Be overflow when item has long name.
- Ad detail: layout issue on IE.
- Showing ad wrong when create ad with gender is Male or Female.

### Improvements

- Add more condition for different languages available for better ads and sponsor ads.
- Create ad - Should have option any for Gender.
- Sponsor without payment: should update status when disable "Auto publish sponsored <item>".

### New Features

- Create sponsor in block for Groups

### Changed files

- M Ajax/Ajax.php
- M Controller/AddController.php
- M Controller/Admin/AddController.php
- M Controller/Admin/AddPlacementController.php
- M Controller/Admin/SponsorController.php
- M Controller/SponsorController.php
- M Install.php
- M Installation/Database/BetterAds.php
- M Installation/Database/Sponsor.php
- A Installation/Version/v424.php
- M README.md
- M Service/Ad.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- M Service/Report.php
- M assets/main.less
- M changelog.md
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M hooks/feed.service_feed_can_sponsored.php
- D hooks/get_t.php
- M installer.php
- M phrase.json
- M views/block/display.html.php
- M views/block/targetting.html.php
- M views/controller/add.html.php
- M views/controller/admincp/add.html.php
- M views/controller/manage.html.php
- M views/controller/report.html.php


## Version 4.2.3

### Information

- **Release Date:** Aug 21, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs
- Video Channel - Can't play video when Better Ads app is enabled
- Forum - Not display successful notification message after sponsored a thread without payment
- Forum - Not display Sponsored Threads block in Forum homepage after sponsored a thread
- Forum - Can not view sponsored thread when click on a thread in Sponsored Threads block
- Sponsor in feed - Working wrong
- Sponsor in feed - Can not un-sponsor in feed
- Owner of Ad received Email with wrong language when admin approved the ad
- Manage Sponsorships - dates search not working right for events maybe all apps
- Manage Sponsorships - will not delete after you un-sponsor song - still show running
- AdminCP > Manage Sponsorships - show status "upcoming" for sponsorship have start date
- Show Total cost field in Sponsor In Feed a song without payment
- Sponsor items - Total price is wrong
- Search working wrong in Manage Sponsorships
- Need approve when sponsor a song/song album while setting not need approve
- Manage Sponsorships in front-end is overflow when item name is long
- Not save Location after edited an Ad
- Ad show wrong if have add location target
- Sponsor item without payment need approve: auto publish sponsored item when not approved yet
- Sponsor item with payment need approve: Not publish sponsored item after approved
- Sponsor in feed - Phrase is wrong

### Changed files
- M Ajax/Ajax.php
- M Block/Display.php
- M Controller/Admin/AddController.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/SponsorController.php
- M Controller/ManageController.php
- M Controller/ManageSponsorController.php
- M Controller/SponsorController.php
- M Install.php
- M README.md
- M Service/Ad.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- M Service/Sponsor.php
- M assets/main.less
- M changelog.md
- M views/block/targetting.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/index.html.php
- M views/controller/admincp/invoice.html.php
- M views/controller/admincp/migrate-ads.html.php
- M views/controller/admincp/sponsor-setting.html.php
- M views/controller/admincp/sponsor.html.php
- M views/controller/invoice.html.php
- M views/controller/manage-sponsor.html.php
- M views/controller/manage.html.php
- M views/controller/sponsor.html.php

## Version 4.2.2

### Information

- **Release Date:** July 27, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs
- Pay per click is working wrong.

### Changed files
- M Block/Display.php
- M Controller/AddController.php
- M Controller/Admin/SponsorController.php
- M Install.php
- M README.md
- M Service/Ad.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Migrate.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- D checksum
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M hooks/get_module_blocks.php
- M phrase.json
- M start.php
- M views/controller/add.html.php
- M views/controller/admincp/add.html.php
- M views/controller/invoice.html.php

## Version 4.2.1

### Information

- **Release Date:** Jun 15, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs
- Ad image disappear when cron clear temporary files.
- "clicks' counter does not increase.
- Better Ads city filter doesn't work.
- Better Ads not work when disable old Ad.
- Clear cache every-time we sponsor a photo or video in Feed.
- Missing some setting when upgrade Better Ads.
- Not show thumbnails photo when click on Review Ads in ACP
- Show Image tooltip field when edit Ads type HTML in ACP.

### Changed files
- M	assets/autoload.css
- M assets/autoload.js
- M	Service/Ad.php
- M	Service/Process.php
- M	Service/Get.php
- M	Ajax/Ajax.php
- M	views/block/display.html.php
- M	views/controller/admincp/add.html.php
- M	Service/Process.php
- M	Controller/Admin/AddController.php

## Version 4.2.0

### Information

- **Release Date:** April 27, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### New features
- Migrate data from Basic Ad.

### Improvements
- Allow placement to set "Disallowed Controller" and user group.
- Admin can edit ad in AdminCP.
- Improve preview ad when adding.
- Improve layout.

### Fixed Bugs
- Check and fix all issues from old version.

### Changed files
- M	Ajax/Ajax.php
- A	Block/DailyReports.php
- A	Block/DeletePlacement.php
- M	Block/Display.php
- D	Block/Display_1.php
- D	Block/Display_10.php
- D	Block/Display_11.php
- D	Block/Display_12.php
- D	Block/Display_2.php
- D	Block/Display_3.php
- D	Block/Display_4.php
- D	Block/Display_5.php
- D	Block/Display_6.php
- D	Block/Display_7.php
- D	Block/Display_8.php
- D	Block/Display_9.php
- M	Block/Inner.php
- A	Block/MigrateAd.php
- M	Block/Sample.php
- M	Block/Sponsored.php
- D	Block/Sponsored_Blog.php
- D	Block/Sponsored_Event.php
- D	Block/Sponsored_Marketplace.php
- D	Block/Sponsored_Photo.php
- D	Block/Sponsored_Poll.php
- D	Block/Sponsored_Quiz.php
- D	Block/Sponsored_Video.php
- M	Controller/AddController.php
- M	Controller/Admin/AddController.php
- M	Controller/Admin/AddPlacementController.php
- M	Controller/Admin/IndexController.php
- M	Controller/Admin/InvoiceController.php
- A	Controller/Admin/MigrateAdsController.php
- A	Controller/Admin/MigrateSponsorshipsController.php
- M	Controller/Admin/PlacementController.php
- M	Controller/Admin/SponsorController.php
- A	Controller/Admin/SponsorSettingController.php
- D	Controller/IframeController.php
- M	Controller/ImageController.php
- M	Controller/IndexController.php
- M	Controller/InvoiceController.php
- M	Controller/ManageController.php
- M	Controller/ManageSponsorController.php
- M	Controller/PreviewController.php
- M	Controller/ReportController.php
- M	Controller/SampleController.php
- M	Controller/SponsorController.php
- M	Install.php
- M	Installation/Database/BetterAds.php
- M	Installation/Database/Country.php
- M	Installation/Database/Hide.php
- M	Installation/Database/Invoice.php
- M	Installation/Database/Log.php
- M	Installation/Database/Plan.php
- M	Installation/Database/Sponsor.php
- M	Installation/Database/View.php
- A	Installation/Version/v420.php
- A	Service/Ad.php
- D	Service/Ads.php
- A	Service/Browse.php
- M	Service/Callback.php
- M	Service/Get.php
- A	Service/Migrate.php
- M	Service/Process.php
- M	Service/Report.php
- M	Service/Sponsor.php
- M	assets/autoload.css
- M	assets/autoload.js
- M	assets/autoload.less
- A	assets/main.less
- D	change-log.md
- A	changelog.md
- A	checksum
- M	hooks/admincp.service_maintain_delete_files_get_list.php
- D	hooks/blog.template_block_entry_links_main.php
- A	hooks/bundle__start.php
- D	hooks/event.template_block_entry_links_main.php
- M	hooks/feed.service_feed_can_sponsored.php
- M	hooks/get_module_blocks.php
- A	hooks/get_t.php
- D	hooks/marketplace.template_block_entry_links_main.php
- D	hooks/music.template_block_entry_links_main.php
- D	hooks/photo.template_block_menu.php
- D	hooks/poll.template_block_entry_links_main.php
- D	hooks/quiz.template_block_entry_links_main.php
- A	hooks/template_getheader_end.php
- A	hooks/validator.admincp_settings_ad.php
- A	installer.php
- M	phrase.json
- M	start.php
- A	views/block/daily-reports.html.php
- A	views/block/delete-placement.html.php
- A	views/block/detail-info-body.html.php
- M	views/block/display.html.php
- D views/block/display_1.html.php
- D views/block/display_10.html.php
- D views/block/display_11.html.php
- D views/block/display_12.html.php
- D views/block/display_2.html.php
- D views/block/display_3.html.php
- D views/block/display_4.html.php
- D views/block/display_5.html.php
- D views/block/display_6.html.php
- D views/block/display_7.html.php
- D views/block/display_8.html.php
- D views/block/display_9.html.php
- M views/block/inner.html.php
- A views/block/migrate-ad.html.php
- M views/block/sample.html.php
- M views/block/sponsored.html.php
- D views/block/sponsored_blog.html.php
- D views/block/sponsored_event.html.php
- D views/block/sponsored_marketplace.html.php
- D views/block/sponsored_photo.html.php
- D views/block/sponsored_poll.html.php
- D views/block/sponsored_quiz.html.php
- D views/block/sponsored_video.html.php
- M views/block/targetting.html.php
- M views/controller/add.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/addplacement.html.php
- M views/controller/admincp/index.html.php
- M views/controller/admincp/invoice.html.php
- A views/controller/admincp/migrate-ads.html.php
- A views/controller/admincp/migrate-sponsorships.html.php
- D views/controller/admincp/placement.html.php
- A views/controller/admincp/placements.html.php
- A views/controller/admincp/sponsor-setting.html.php
- M views/controller/admincp/sponsor.html.php
- D views/controller/iframe.html.php
- M views/controller/image.html.php
- M views/controller/index.html.php
- M views/controller/invoice.html.php
- M views/controller/manage-sponsor.html.php
- M views/controller/manage.html.php
- M views/controller/preview.html.php
- M views/controller/report.html.php
- M views/controller/sample.html.php
- M views/controller/sponsor.html.php

## Version 4.1.3

### Information

- **Release Date:** September 29, 2017
- **Best Compatibility:** phpFox >= 4.5.2

### Fixed Bugs

- Block "Sponsored Events" isn't shown.
- ACP - Manage Placements: doesn't count ads that added from ACP.
- ACP - Add Ads: cannot create HTML Ads.
- ACP - Add Ads: Date Picker doesn't work.
- ACP - Edit Ads: cannot select Age group.
- ACP - Manage Campaigns: Counter works wrong with HTML Ads
- Sponsor items show wrong phrase.
- Cannot create new ad.

### Changed files
- - M Block/Sponsored_Event.php
- - M Block/Sponsored_Marketplace.php
- - M Controller/AddController.php
- - M Controller/Admin/AddController.php
- - M Install.php
- - A README.md
- - M Service/Get.php
- - M Service/Sponsor.php
- - M assets/autoload.css
- - M assets/autoload.js
- - M assets/autoload.less
- - M change-log.md
- - M phrase.json
- - M views/controller/admincp/add.html.php
- - M views/controller/admincp/index.html.php

## Version 4.1.2

### Information

- **Release Date:** April 20, 2017
- **Best Compatibility:** phpFox >= 4.5.2

### Fixed Bugs

 - Support RTL
 - Fix some layout issues
 - Add missing phrases
 - Fix set target by location + state + city
 
### New feature

 - Allow users hide ads and sponsor items

### Changed files

 - **M**	Ajax/Ajax.php
 - **M**	Block/Display_1.php
 - **M**	Block/Display_10.php
 - **M**	Block/Display_11.php
 - **M**	Block/Display_12.php
 - **M**	Block/Display_2.php
 - **M**	Block/Display_3.php
 - **M**	Block/Display_4.php
 - **M**	Block/Display_5.php
 - **M**	Block/Display_6.php
 - **M**	Block/Display_7.php
 - **M**	Block/Display_8.php
 - **M**	Block/Display_9.php
 - **M**	Block/Sponsored_Blog.php
 - **M**	Block/Sponsored_Event.php
 - **M**	Block/Sponsored_Marketplace.php
 - **M**	Block/Sponsored_Photo.php
 - **M**	Block/Sponsored_Poll.php
 - **M**	Block/Sponsored_Quiz.php
 - **M**	Controller/Admin/AddController.php
 - **M**	Controller/SponsorController.php
 - **M**	Install.php
 - **A**	Installation/Database/Hide.php
 - **M**	Service/Get.php
 - **M**	Service/Process.php
 - **M**	Service/Sponsor.php
 - **M**	assets/autoload.css
 - **M**	assets/autoload.js
 - **M**	assets/autoload.less
 - **A**	hooks/admincp.service_maintain_delete_files_get_list.php
 - **M**	phrase.json
 - **M**	start.php
 - **M**	views/block/display.html.php
 - **M**	views/block/display_1.html.php
 - **M**	views/block/display_10.html.php
 - **M**	views/block/display_11.html.php
 - **M**	views/block/display_12.html.php
 - **M**	views/block/display_2.html.php
 - **M**	views/block/display_3.html.php
 - **M**	views/block/display_4.html.php
 - **M**	views/block/display_5.html.php
 - **M**	views/block/display_6.html.php
 - **M**	views/block/display_7.html.php
 - **M**	views/block/display_8.html.php
 - **M**	views/block/display_9.html.php
 - **M**	views/block/sponsored_blog.html.php
 - **M**	views/block/sponsored_event.html.php
 - **M**	views/block/sponsored_marketplace.html.php
 - **M**	views/block/sponsored_photo.html.php
 - **M**	views/block/sponsored_poll.html.php
 - **M**	views/block/sponsored_quiz.html.php
 - **M**	views/block/targetting.html.php
 - **M**	views/controller/add.html.php
 - **M**	views/controller/admincp/addplacement.html.php
