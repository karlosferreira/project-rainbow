# Messages :: Change log

## Version 4.7.8

### Information

- **Release Date:** July 07, 2021
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Does not show attachment in attachment list after uploaded
- Some other minor bugs

### Improvements

- Compatible with PHP 8.0 and phpFox 4.8.6

### Changed Files

- M Ajax/Ajax.php
- M Controller/Admin/ExportDataController.php
- M Controller/Admin/ManageConversationsController.php
- M Controller/CustomListController.php
- M Controller/SendMessageController.php
- M Install.php
- M Service/Helper.php
- M assets/autoload.js
- M assets/main.less
- M views/block/entry.html.php
- M views/block/group-members.html.php
- M views/block/message-item.html.php
- M views/controller/admincp/conversations.html.php
- M views/controller/panel.html.php

## Version 4.7.7

### Information

- **Release Date:** March 04, 2021
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Fix RTL layout issues
- Some bugs fixed

### Improvements

- Support user leave a chat group
- Add notice for users when they don't have permission to send messages to a person

### Changed Files

- M Controller/ConversationPopupController.php
- M Controller/IndexController.php
- M Controller/SendMessageController.php
- M Controller/ThreadController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/CustomList/Process.php
- M Service/Helper.php
- M Service/Mail.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M phrase.json
- M views/block/entry.html.php
- M views/block/message-item.html.php
- M views/controller/customlist/index.html.php
- M views/controller/panel.html.php

## Version 4.7.6

### Information

- **Release Date:** July 01, 2020
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Fix layout issues
- Support Export Conversations to ChatPlus

### Changed Files

- M Ajax/Ajax.php
- A Controller/Admin/ExportDataController.php
- A Controller/DownloadExportController.php
- M Install.php
- M assets/main.less
- M phrase.json
- M start.php
- A views/controller/admincp/export-data-chat-plus.html.php


## Version 4.7.5

### Information

- **Release Date:** September 09, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Fix layout issues

### Changed Files

- M Ajax/Ajax.php
- M Controller/AddCustomListController.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/ManageConversationsController.php
- M Controller/Admin/ManageMessageController.php
- M Controller/CustomListController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/CustomList/CustomList.php
- M Service/CustomList/Process.php
- M Service/Helper.php
- M Service/Mail.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M phrase.json
- M views/controller/admincp/conversations.html.php
- M views/controller/admincp/messages.html.php
- M views/controller/customlist/add.html.php
- M views/controller/panel.html.php


## Version 4.7.4

### Information

- **Release Date:** May 10, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Broken font when contact seller in Marketplace using SK language.
- Responsive - Missing reply box after scroll up.
- Chat detail - Wrong cursor when hover on action.
- Spam message - Show wrong information of receive user after sent message.

### Changed Files

- M Ajax/Ajax.php
- M Controller/ComposeController.php
- M Controller/ConversationPopupController.php
- M Controller/CustomListController.php
- M Controller/IndexController.php
- M Controller/SendMessageController.php
- M Install.php
- M README.md
- M Service/Helper.php
- M Service/Mail.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M phrase.json
- M views/controller/compose.html.php
- M views/controller/conversation-popup.html.php
- M views/controller/customlist/index.html.php
- M views/controller/index.html.php


## Version 4.7.3

### Information

- **Release Date:** March 04, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- URL in messages does not open in a new window [#2521](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2521).
- Responsive - Layout be broken when try to view message from not mail module.
- Message refresh to last message while viewing long messages [#2587](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2587).
- Issue with a popup window [#2588](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2588).
- Create Custom List - Not show member if add member after search.
- Missed phrases for translation in error messages.
- Show 500 error when search conversation.
- On Internet explorer - 2 columns, 3 columns layout - Layout issue when send a long link message.

### Changed Files

- M Controller/AddCustomListController.php
- M Controller/ComposeController.php
- M Controller/CustomListController.php
- M Service/Mail.php
- M Service/Process.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M views/controller/compose.html.php
- M views/controller/conversation-popup.html.php

## Version 4.7.2

### Information

- **Release Date:** January 02, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Missing phrase in Manage Apps.
- Avatar is scaled on bootstrap template.
- Show duplicated "--TODAY--" when translate 'today' phrase to 'today vn'.
- Message always toward bottom instead of top.
- Top portion of message is always offscreen in view all messages.
- Not show the user's photo in custom list.

### Changed Files

- M Ajax/Ajax.php
- M Controller/ComposeController.php
- M Controller/CustomListController.php
- M Controller/IndexController.php
- M Controller/SendMessageController.php
- M Install.php
- M Service/Helper.php
- M Service/Mail.php
- M Service/Process.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M views/controller/conversation-popup.html.php
- M views/controller/panel.html.php

## Version 4.7.1

### Information

- **Release Date:** December 14, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Wordwrap does not work properly.
- Missing phrases.

### Changed Files

- M Ajax/Ajax.php
- M Controller/AddCustomListController.php
- M Controller/Admin/ManageMessageController.php
- M Controller/CustomListController.php
- M Controller/IndexController.php
- M Install.php
- A Installation/Version/v471.php
- M Service/CustomList/CustomList.php
- M Service/CustomList/Process.php
- M Service/Helper.php
- M Service/Mail.php
- M assets/main.less
- M hooks/validator.admincp_settings_mail.php
- M installer.php
- M phrase.json
- M views/block/message-footer.html.php
- M views/controller/admincp/messages.html.php
- M views/controller/compose.html.php
- M views/controller/customlist/add.html.php
- M views/controller/customlist/index.html.php
- M views/controller/index.html.php

## Version 4.7.0

### Information

- **Release Date:** October 10, 2018
- **Best Compatibility:** phpFox >= 4.7.0

