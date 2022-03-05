# Shoutbox :: Change Log

## Version 4.3.4

### Information

- **Release Date:** July 05, 2021
- **Best Compatibility:** phpFox >= 4.8.0

### Fixed Bug

- An error if Pages/Groups apps are disabled
- Layout issues
- Some minor bugs

### Improvements

- Compatible with PHP 8.0
- Improved quality of user's avatar on Shoutbox 

### Changed files

- M Block/Chat.php
- M Block/EditMessage.php
- M Controller/PollingController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- M assets/autoload.js
- M views/block/chat.html.php

## Version 4.3.3

### Information

- **Release Date:** July 06, 2020
- **Best Compatibility:** phpFox >= 4.8.0

### Fixed Bugs

- Compatible with Core 4.8.0

### Changed files

- M Install.php
- M README.md
- M assets/autoload.js
- M assets/main.less
- M change-log.md

## Version 4.3.2

### Information

- **Release Date:** September 09, 2019
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Delete old messages not working [#2764](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2764)
- If in the Shoutbox application we have disabled some group to add new text messages, this group can see the "Quote" icon even though it can not add messages to the shoutbox. [#2734](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2734)

### Changed files

- M Ajax/Ajax.php
- M Block/Chat.php
- M Controller/PollingController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Shoutbox.php
- A Installation/Version/v432.php
- M README.md
- M Service/Get.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M change-log.md
- M hooks/core.component_controller_index_member_start.php
- M hooks/groups.component_controller_view_assign.php
- M hooks/pages.component_controller_view_assign.php
- M installer.php
- M start.php
- M views/block/chat.html.php


## Version 4.3.1

### Information

- **Release Date:** March 26, 2019
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bug

- Duplicated posts.

### Changed files

- M Service/Get.php

## Version 4.3.0

### Information

- **Release Date:** January 15, 2019
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Can insert emoji even if textarea has maximum number of characters.
- Duplicate send message action even if enter or type submit once.
- Show wrong message if pages/groups's enable setting is turned off.

### Improvements

- Using default error popup.
- Update text count after posting message.
- Add new settings to control delete message.

### New Features

- Support edit message.
- Support like message.
- Support emoji.
- Support quote message.
- Add option to delete old messages.
- Add option to control delay time when posting message.


### Changed files

- M Ajax/Ajax.php
- M Block/Chat.php
- A Block/EditMessage.php
- M Controller/PollingController.php
- A Controller/ViewController.php
- M Install.php
- M Installation/Database/Shoutbox.php
- A Installation/Database/Shoutbox_Quoted_Message.php
- A Installation/Version/v430.php
- M Service/Callback.php
- M Service/Get.php
- M Service/Process.php
- D assets/autoload.css
- M assets/autoload.js
- D assets/autoload.less
- M assets/main.less
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M hooks/bundle__start.php
- M hooks/core.component_controller_index_member_start.php
- A installer.php
- M phrase.json
- M start.php
- M views/block/chat.html.php
- A views/block/edit-message.html.php
- A views/controller/view.html.php

### New settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | shoutbox_can_edit_own_message | Can edit own messages | Allow user editing their own messages |
| 2 | shoutbox_can_delete_own_message | Can delete own messages | Allow user deleting their own messages |
| 3 | shoutbox_can_delete_others_message | Can delete all messages | Allow user deleting all messages |


## Version 4.2.1

### Information

- **Release Date:** December 14, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bug

- Deleting shoutbox post immediately after post not working.
- Message is overlapped with Shoutbox.
- Can not delete message after sent, must be refresh page to delete.
- Padding issue on bootstrap template.
- Banned words not applied to text.

### Improvements

- Shoutbox avatar is huge.

### Changed files

- M Block/Chat.php
- M Install.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M views/block/chat.html.php

## Version 4.2.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bug

- Cannot use the app in some servers (do not allow to run php file with permission 777).
- Cannot use the app in sites that upgraded from v3.

### Improvements

- Update new layout for Shoutbox block.
- Check compatible with phpFox core and porting apps 4.6.0.

### Changed files

- PF.Site/Apps/core-shoutbox/Install.php

## Version 4.1.3

### Information

- **Release Date:** September 18, 2017
- **Best Compatibility:** phpFox >= 4.5.2

### Improvements

- Integrate with the new Pages app (4.5.3).

### Changed files

- PF.Site/Apps/core-shoutbox/Install.php
- PF.Site/Apps/core-shoutbox/Block/Chat.php
- PF.Site/Apps/core-shoutbox/Service/Process.php

## Version 4.1.2

### Information

- **Release Date:** April 11, 2017
- **Best Compatibility:** phpFox >= 4.5.2

### Bugs Fixed

- Can not use Shoutbox if enabled bundle JS
- Some issues on layout with right to left languages

### Improvement

- Apply site timestamp format for Shoutbox message time

### Changed files

- PF.Site/Apps/core-shoutbox/Install.php
- PF.Site/Apps/core-shoutbox/assets/autoload.css
- PF.Site/Apps/core-shoutbox/assets/autoload.js
- PF.Site/Apps/core-shoutbox/assets/autoload.less
- PF.Site/Apps/core-shoutbox/hooks/template_getheader_exclude_bundle_js.php
- PF.Site/Apps/core-shoutbox/polling.php
