# Subscriptions :: Change log

## Version 4.6.7

### Information

- **Release Date:** July 05, 2021
- **Best Compatibility:** phpFox >= 4.8.6

### Fixed Bugs

- Some minor bugs

### Improvements

- Compatible with PHP 8.0 and phpFox 4.8.6
- Add new settings to allow Admins review and update all email contents of Subscriptions app
- Allow Admin choose payment method when a subscription is renewed for Package Recurring [#2990](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2990)
- Support cancel Subscription on Paypal when users cancel their subscription in phpFox site (Required phpFox version >= 4.8.6 and Paypal Client ID and Client Secret) [#2991](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2991)
- Add option to cancel the chosen subscription during Registration process

### Changed Files

- M Ajax/Ajax.php
- M Block/CancelSubscriptionBlock.php
- M Block/ListBlock.php
- M Block/RenewPaymentBlock.php
- M Block/UpgradeBlock.php
- M Controller/Admin/AddController.php
- M Controller/Admin/AddReasonController.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/ListController.php
- M Controller/CompareController.php
- M Controller/RegisterController.php
- M Controller/RenewMethodController.php
- M Install.php
- M Installation/Database/Subscribe_Package.php
- M Installation/Database/Subscribe_Purchase.php
- M Installation/Version/v462.php
- A Job/ProcessActiveSubscriptionAfterDeletePackage.php
- M Service/Callback.php
- M Service/Compare/Process.php
- M Service/Helper.php
- M Service/Process.php
- M Service/Purchase/Process.php
- M Service/Purchase/Purchase.php
- M Service/Reason/Reason.php
- M Service/Subscribe.php
- M assets/autoload.js
- M hooks/admincp.component_controller_setting_edit_process.php
- A hooks/job_queue_init.php
- M hooks/user.service_process_updateadvanced_end.php
- M installer.php
- M phrase.json
- M views/block/cancel-subscription.html.php
- M views/block/renew-payment.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/list.html.php
- M views/controller/admincp/reason.html.php
- M views/controller/register.html.php
- M views/controller/renew-method.html.php

## Version 4.6.6

### Information

- **Release Date:** February 05, 2021
- **Best Compatibility:** phpFox >= 4.7.2

### Fixed Bugs

- User does not receive mail when subscribe a free package
- Can not buy a recurring subscription when enable "Verify email at Sign up"
- Price does not apply currency format in Purchase detail
- Some minor issues related to phrases

### Improvements

- Support hook for third party to cancel subscriptions in Payment Gateway when user manually cancels in phpFox site
- Add Email notifications for all actions

### Changed Files

- M Block/UpgradeBlock.php
- M Controller/Admin/ViewController.php
- M Controller/RegisterController.php
- M Controller/RenewMethodController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Purchase/Process.php
- M Service/Purchase/Purchase.php
- M Service/Reason/Reason.php
- M Service/Subscribe.php
- M assets/autoload.js
- M assets/main.less
- M hooks/core.template-notification-custom.php
- M hooks/init.php
- M phrase.json
- M views/block/compare.html.php
- M views/block/entry.html.php
- M views/block/list.html.php
- M views/controller/admincp/index.html.php
- M views/controller/admincp/view.html.php
- M views/controller/renew-method.html.php

## Version 4.6.5

### Information

- **Release Date:** May 30, 2019
- **Best Compatibility:** phpFox >= 4.7.2

### Fixed Bugs

- Fix phrases issues.
- No invoice when login.

### Changed Files

- M Service/Subscribe.php
- M views/controller/admincp/index.html.php
- M views/controller/index.html.php
- M Controller/RegisterController.php
- M Controller/RenewMethodController.php
- M hooks/init.php
- M Service/Purchase/Purchase.php


## Version 4.6.4

### Information

- **Release Date:** May 06, 2019
- **Best Compatibility:** phpFox >= 4.7.2

### Improvements

- Support buy packages on Mobile app
 
### Changed Files

- M Controller/Admin/ListController.php
- M Controller/CompareController.php
- M Controller/IndexController.php
- M Controller/ListController.php
- M Install.php
- M README.md
- M Service/Subscribe.php
- M changelog.md
- M hooks/init.php

## Version 4.6.3

### Information

- **Release Date:** January 30, 2019
- **Best Compatibility:** phpFox >= 4.7.2

### Fixed Bugs

- ACP - Subscriptions - Manage Packages - Search time is not correct.
- ACP - Subscriptions - Manage Packages - Text count is not working when editing a package.
- ACP - Subscriptions - Manage Packages - Redirect page is wrong when clicking on pagination.
- Membership - My Subscriptions - Package is still showing on my subscriptions page after it is deleted by admin.
- ACP - Subscriptions - Manage Packages - Subscription ID is broken down line.
- ACP - Subscriptions - New Packages - Background color issue.
- ACP - Subscriptions - Purchase orders - Breadcrumb is hidden after searching invalid data.

### Changed Files

- M Controller/Admin/AddCompareController.php
- M Controller/Admin/ListController.php
- M Install.php
- M Installation/Version/v462.php
- A Installation/Version/v463.php
- M README.md
- M Service/Compare/Process.php
- M Service/Purchase/Purchase.php
- M Service/Subscribe.php
- M assets/autoload.css
- M assets/jscript/admincp/admincp.js
- M changelog.md
- A hooks/admincp.component_controller_setting_edit_process.php
- M installer.php
- M views/controller/admincp/add-compare.html.php
- M views/controller/admincp/add-reason.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/index.html.php

## Version 4.6.2

### Information

- **Release Date:** January 08, 2019
- **Best Compatibility:** phpFox >= 4.7.2

### Fixed Bugs

- Missing phrase in Manage Apps.
- Membership Packages - Page Title show "Page not found".
- Price invalid if less than $1.00.
- Missed some phrases.
- Bootstrap theme - My subscription - Missed icon on Pre/Next button of pagination.
- Allow Admin groups only to force account without payment in case system is enabling "subscription package requirement" by changing account's user group equal to package's user group on success.

### Improvements

- Support select package from compare page.	

### Changed Files

- M Block/CancelReasonBlock.php
- M Block/CancelSubscriptionBlock.php
- M Block/UpgradeBlock.php
- M Controller/Admin/AddCompareController.php
- M Controller/Admin/AddReasonController.php
- M Controller/Admin/DeleteReasonController.php
- M Controller/Admin/IndexController.php
- M Controller/Admin/ListController.php
- M Controller/Admin/ReasonController.php
- M Controller/CompareController.php
- M Controller/IndexController.php
- M Controller/ListController.php
- M Controller/RegisterController.php
- M Install.php
- M Installation/Version/v460.php
- A Installation/Version/v462.php
- M README.md
- M Service/Callback.php
- M Service/Purchase/Process.php
- M Service/Reason/Process.php
- M Service/Subscribe.php
- M assets/autoload.css
- M assets/autoload.js
- M changelog.md
- M hooks/init.php
- M hooks/user.service_auth___construct_end.php
- A hooks/user.service_process_updateadvanced_end.php
- M installer.php
- M phrase.json
- M views/block/advance-entry.html.php
- M views/block/cancel-reason.html.php
- M views/block/cancel-subscription.html.php
- M views/block/compare-admin.html.php
- M views/block/compare.html.php
- M views/block/entry.html.php
- M views/controller/admincp/add-compare.html.php
- M views/controller/admincp/add-reason.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/delete-reason.html.php
- M views/controller/admincp/index.html.php
- M views/controller/admincp/list.html.php
- M views/controller/admincp/reason.html.php
- M views/controller/admincp/view.html.php
- M views/controller/compare.html.php
- M views/controller/index.html.php
- M views/controller/list.html.php
- M views/controller/renew-method.html.php


## Version 4.6.1

### Information

- **Release Date:** October 11, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- User received Email with wrong language when buy subscription
- Migrate data for module version
- Responsive - Cancel subscription  - Layout of Buttons 
- Turn on Bundle JS - Action of user on IE is block
- Subscription in ACP >> Search by time: Not show datepicker when click on calendar icon
- Have to fill full language when Create new reason
- Staff can not buy subscriptions
- Membership subscription: Phrases be not translated
- Cancel reason in back end: Missing Navigation and page title
- Membership package: Not show the currently used package
- Show wrong mail title when subscription be active/cancel
- Plan Comparison: Missing No icon in Comparison table
- My subscription: Not show Renew button for active package
- Missing link in message from Admin who change status of subscription
- Edit Cancel reason: Can not edit with multi languages

### Improvements

- Purchase with membership package on sign up process is failed if Email verification is turned on.

### Changed Files

- M Block/RenewPaymentBlock.php
- M Block/UpgradeBlock.php
- M Controller/Admin/AddController.php
- M Controller/Admin/AddReasonController.php
- M Controller/Admin/CompareController.php
- M Controller/Admin/ListController.php
- M Controller/Admin/ReasonController.php
- M Controller/CompareController.php
- M Controller/IndexController.php
- M Controller/ListController.php
- M Install.php
- A Installation/Version/v461.php
- M README.md
- M Service/Compare/Process.php
- M Service/Process.php
- M Service/Purchase/Process.php
- M Service/Purchase/Purchase.php
- M Service/Reason/Process.php
- M Service/Subscribe.php
- M assets/autoload.css
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M hooks/init.php
- M hooks/user.service_auth___construct_end.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/cancel-subscription.html.php
- M views/block/compare-admin.html.php
- M views/block/compare.html.php
- M views/block/renew-payment.html.php
- M views/block/upgrade.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/index.html.php
- M views/controller/admincp/list.html.php
- M views/controller/admincp/reason.html.php
- M views/controller/index.html.php

## Version 4.6.0

### Information

- **Release Date:** July 26, 2018
- **Best Compatibility:** phpFox >= 4.6.1b5
