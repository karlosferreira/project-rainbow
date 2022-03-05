# Activity Points :: Change log

## Version 4.7.8

### Information

- **Release Date:** July 08, 2021
- **Best Compatibility:** phpFox >= 4.7.2

### Improvements

- Compatible with PHP 8.0 and phpFox 4.8.6
- Display activity points on User Profile if the viewer is profile's owner
- Display the conversion rate of Activity points to the Frontend [#853](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/853)

### Bugs Fixed

- Some minor bugs

### Changed files

- M Block/AdjustPointBlock.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/PackageController.php
- M Controller/Admin/PointController.php
- M Controller/Admin/TransactionController.php
- M Controller/IndexController.php
- M Install.php
- M Service/ActivityPoint.php
- M Service/Callback.php
- M Block/AdjustPointBlock.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/PackageController.php
- M Controller/Admin/PointController.php
- M Controller/Admin/TransactionController.php
- M Controller/IndexController.php
- M Install.php
- M Service/ActivityPoint.php
- M Service/Callback.php
- M Service/Package/Package.php
- M Service/Package/Process.php
- M Service/Process.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/main.less
- M hooks/admincp.service_module_process_delete_end.php
- M hooks/on_after_install_app.php
- A hooks/profile.service_profile_get_profile_menu.php
- M hooks/user.include_service_group_setting_importfromapp_start.php
- M phrase.json
- M views/block/adjust-point.html.php
- M views/controller/index.html.php

## Version 4.7.7

### Information

- **Release Date:** July 21, 2020
- **Best Compatibility:** phpFox >= 4.7.2

### Improvements

- Fully support PHP 7.4
- Add Mass action button to expand/collapse all activity point settings of modules [#2751](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2751)

### Bugs Fixed

- Some issues in ACP - Transaction history [#2859](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2859)
- Show old point packages listing after adding/deleting point packages
- Correct hook name [#2840](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2840)

### Changed files

- M Controller/Admin/TransactionController.php
- M Install.php
- M Job/UpdatePoints.php
- M README.md
- M Service/ActivityPoint.php
- M Service/Callback.php
- M Service/Package/Package.php
- M Service/Package/Process.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- D hooks/on_after_install_app.php
- M views/controller/admincp/index.html.php
- M views/controller/information.html.php

## Version 4.7.6

### Information

- **Release Date:** July 29, 2019
- **Best Compatibility:** phpFox >= 4.7.2

### Improvements

- Disable App in ActivityPoints, also hide the App in Frontend [#2720](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2720)

### Bugs Fixed

- Currency position of price display [#2681](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2681)

### Changed files

- M Controller/PackageController.php
- M Service/ActivityPoint.php
- M Service/Package/Package.php
- M Service/Process.php
- M assets/autoload.js
- M changelog.md
- M views/controller/admincp/package.html.php
- M views/controller/admincp/transaction.html.php
- M views/controller/index.html.php
- M views/controller/package.html.php

## Version 4.7.5

### Information

- **Release Date:** April 12, 2019
- **Best Compatibility:** phpFox >= 4.7.2

### Improvements

- Separate Phrases for Activity Points in Frontend and Backend [#795](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/795)

### Bugs Fixed
- Language phrase issues
- Activity points history. Gifting points today is not displayed in sent history unless the "to" date is set into the future [#2517](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2517)
- Price invalid if less than $1.00 [#2512](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2512)

### Changed files

- M Ajax/Ajax.php
- M Block/AdjustPointBlock.php
- D Block/InformationBlock.php
- M Block/PurchasePackageBlock.php
- M Controller/Admin/AddPackageController.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/PointController.php
- M Controller/Admin/TransactionController.php
- M Controller/CompleteController.php
- M Controller/IndexController.php
- M Controller/InformationController.php
- M Controller/PackageController.php
- M Install.php
- M Installation/Version/v470.php
- M Installation/Version/v474.php
- A Installation/Version/v475.php
- M Job/UpdatePoints.php
- M README.md
- M Service/ActivityPoint.php
- M Service/Process.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/adjust-point.html.php
- D views/block/information.html.php
- M views/controller/admincp/index.html.php
- M views/controller/admincp/point.html.php
- M views/controller/admincp/transaction.html.php
- M views/controller/index.html.php
- M views/controller/information.html.php
- M views/controller/package.html.php


## Version 4.7.4

### Information

- **Release Date:** December 26, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Bugs Fixed

- Can not create table statistics.

### Changed files

- M Controller/Admin/TransactionController.php
- M Controller/IndexController.php
- M Install.php
- M Installation/Database/Activity_Point_Statistics.php
- M Installation/Database/Activity_Point_Transaction.php
- M Installation/Version/v470.php
- A Installation/Version/v474.php
- M Job/UpdatePoints.php
- M README.md
- M Service/ActivityPoint.php
- M changelog.md
- M installer.php
- M phrase.json
- M views/controller/admincp/point.html.php

## Version 4.7.3

### Information

- **Release Date:** December 14, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Bugs Fixed

- Sent Transaction history shows user name instead of full name.
- ACP - Points Package - Package not affect after edited.

### Changed files

- M Installation/Database/Activity_Point_Statistics.php
- M Installation/Version/v460.php
- M Service/Package/Process.php
- M hooks/core.template_block_notification_dropdown_menu.php


## Version 4.7.2

### Information

- **Release Date:** October 30, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Bugs Fixed ###

- Can not purchase Point package
- Can not edit point setting in Firefox

### Changed files ###

- M Controller/PackageController.php
- M assets/autoload.js

## Version 4.7.1

### Information

- **Release Date:** October 29, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Bugs Fixed ###

- Can not create Point Package in AdminCP
- Breadcrumbs are missing in some pages

### Changed files ###

- M Controller/Admin/PackageController.php
- M Controller/Admin/PointController.php
- M Controller/Admin/TransactionController.php
- M Install.php
- M README.md
- M Service/Package/Process.php
- M changelog.md

## Version 4.7.0

### Information

- **Release Date:** October 10, 2018
- **Best Compatibility:** phpFox >= 4.7.0

