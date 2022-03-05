# Instant Messaging :: Changelog

## Version 4.8.1

### Information

- **Release Date:** November 27, 2020
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Improve security of authentication when using Firebase chat.
- Add new setting to control chat window minimize or not in the first time user access to site.
- Don't open chat dock when click on conversation at All Messages page.
- Change "Manage Messages" page in AdminCP [#2954](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2961)

### Bugs Fixed

- Some layout issues.
- Can't load more old messages in some cases.
- Some issues when export data to ChatPlus.
- Can still chat with a non friend after reload the browser [#2961](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2961)
- Other small bugs

### Changed Files
- D Controller/AdminManageMessagesController.php
- A Controller/AdminDeleteMessagesController.php    
- M Install.php
- M assets/autoload.js
- M hooks/template_getheader_end.php
- M phrase.json
- M server/index.js
- M start.php
- M views/controller/admincp/export-data-chat-plus.html.php
- A views/controller/admincp/delete-messages.html.php       
- D views/controller/admincp/manage-messages.html.php

## Version 4.8.0

### Information

- **Release Date:** June 23, 2020
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Update new layout
- Export Conversations to ChatPlus 

### Changed Files

- M server/index.js
- M assets/autoload.js
- M assets/main.less
- M phrase.json
- M start.php
- M changelog.md
- M Install.php
- M Controller/AdminManageMessagesController.php
- A Controller/AdminExportDataController.php
- A views/controller/admincp/export-data-chat-plus.html.php

## Version 4.7.2

### Information

- **Release Date:** April 22, 2020
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Improve IM Firebase chat performance.
- Improve push notification to Mobile app.
- Support load more old conversations

### Bug Fixed

- Missing IM host package after auto-renew
- Don't show unread message when search friend

### Changed Files

- M server/fcm.js
- M server/index.js
- M hooks/init.php
- M assets/autoload.js
- D assets/autoload.less
- A assets/main.less
- M assets/autoload.css
- M phrase.json
- M start.php
- M changelog.md
- M Install.php

## Version 4.7.1

### Information

- **Release Date:** January 20, 2020
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Check permission when downloading attachments private.
- No add new feed item when sharing a link.

### Changed Files

- M Ajax/Ajax.php
- M Controller/AdminManageMessagesController.php
- A Controller/DownloadController.php
- M Install.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/autoload.less
- M changelog.md
- M hooks/init.php
- M hooks/notification.component_ajax_update_1.php
- M hooks/template_getheader_end.php
- M phrase.json
- M server/fcm.js
- M server/hooks/hosting.js
- M server/package.json
- M start.php
- M views/admincp.html

## Version 4.7.0

### Information

- **Release Date:** August 1, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Support remove all messages
- Support Google Firebase

### Changed Files

- M README.md
- M Install.php
- M start.php
- M phrase.json
- M assets/autoload.css
- M assets/autoload.js
- M assets/autoload.less
- A assets/images/algolia-remove.png
- A assets/images/firebase-remove-step.png
- M hooks/notification.component_ajax_update_1.php
- M hooks/template_getheader_end.php
- M hooks/init.php
- M server/index.js
- M server/hooks/hosting.js
- M views/popup.html
- M views/admincp.html
- A views/controller/admincp/manage-messages.html.php
- A Controller/AdminManageMessagesController.php
- M Ajax/Ajax.php

## Version 4.6.2

### Information

- **Release Date:** May 07, 2019
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Fix issue can't connect to IM server.

### Improvements

- Support push notification to the Mobile app when having a new message.

### Changed Files

- M hooks/init.php
- M server/config.js.new
- M server/index.js
- M server/package.json
- A server/redis_client.js
- A server/fcm.js

## Version 4.6.1

### Information

- **Release Date:** February 07, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Cannot use hosting service.

### Changed Files
- M start.php
- A hooks/init.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Cannot connect when client switch between http and https
- Setting "Node JS server" is hidden when upgrading IM app from 4.5.2 on phpFox 4.5.3.

### Improvements

- Check compatible with phpFox core 4.6.0.

### Changed Files

- M Controller/AdminManageSoundController.php
- M Install.php
- M README.md
- M assets/autoload.css
- M assets/autoload.js
- M assets/autoload.less
- D assets/dropzone.js
- M assets/im-libraries.min.js
- M change-log.md
- A hooks/bundle__start.php
- M hooks/set_editor_end.php
- M hooks/template_getheader_end.php
- A hooks/validator.admincp_settings_im.php
- M phrase.json
- M server/hooks/hosting.js
- D server/hooks/sample.js
- M server/index.js
- M start.php
- M views/controller/admincp/import-data-v3.html.php
- M views/controller/admincp/manage-sound.html.php

## Version 4.5.4

### Information

- **Release Date:** August 29, 2017
- **Best Compatibility:** phpFox >= 4.5.2

### Fixed Bugs

- Some layout issues.
- Issue when hosting expired.
- Show duplicate link when search message.
- Can reply banned user.

### Changed Files

- M assets/autoload.js
- M assets/autoload.less
- M assets/autoload.css
- M views/admincp.html
- M views/controller/admincp/manage-sound.html.php
- M Install.php
- M start.php

## Version 4.5.3

### Information

- **Release Date: June 26, 2017**
- **Best Compatibility:** phpFox >= 4.5.2

### Fixed Bugs

- Could not buy the IM hosting service at AdminCP.
- Cannot reply another conversation after a friend's account was deleted.

### Changed Files

- A Ajax/Ajax.php
- M assets/autoload.js
- M assets/autoload.less
- M assets/autoload.css
- M hooks/mail.service_callback_getglobalnotifications.php
- M hooks/notification.component_ajax_update_1.php
- M hooks/template_getheader_end.php
- M server/hooks/hosting.js
- M server/config.js.new
- M server/index.js
- M views/admincp.html
- M Install.php
- M installer.php
- M phrase.json
- M start.php

## Version 4.5.2

### Information

- **Release Date: June 6, 2017** 
- **Best Compatibility:** phpFox >= 4.5.2