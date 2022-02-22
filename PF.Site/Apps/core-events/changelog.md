# Events  :: Changelog

## Version 4.8.0

### Information

- **Release Date:** July 05, 2021
- **Best Compatibility:** phpFox >= 4.8.3

### Bugs Fixed

- Events of secret groups is showing publicly via default RSS feeds
- Layout issues
- Some other minor bugs

### Improvements

- Compatible with PHP 8.0 and phpFox 4.8.6
- Support Online Event
- Add new setting for Email Notification of Events app
- Add new settings to allow Admins review and update all email contents of Events app
- Warning message if users want to leave site when they are adding an event (required phpFox version >= 4.8.3)
- Notification to be sent to all attendees when a post/status is made on the event [#1007](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/1007)
- Notification to be sent to all attendees when a change to the event is made [#1006](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/1006)
- Support "Invitee Only" privacy [#1004](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/1004)
- Support Limitation on number of events created by user group [#345](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/345)
- Support Recurring Event [#991](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/991)

### Changed Files

- M Ajax/Ajax.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Event.php
- M Installation/Version/v460.php
- A Job/AddNotificationForPostStatusInEvent.php
- A Job/AddNotificationWhenChangeEventContent.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category/Category.php
- M Service/Event.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- A checksum
- A hooks/feed.service_feed_processfeed.php
- M hooks/job_queue_init.php
- A hooks/privacy.component_block_form_process.php
- M phrase.json
- A views/block/applyforrepeatevent.html.php
- M views/block/feed-rows.html.php
- M views/block/info.html.php
- M views/block/item.html.php
- M views/block/mini-entry.html.php
- M views/controller/add.html.php

## Version 4.7.3

### Information

- **Release Date:** November 21, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs Fixed

- Event shows profile country in home feed and user profile feed [#2820](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2820).

### Improvements

- Rss feed - Improve to get events created from Page/Group

### Changed Files

- M Block/FeaturedBlock.php
- M Block/SponsoredBlock.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Install.php
- M Service/Callback.php
- M Service/Category/Process.php
- M Service/Event.php
- M Service/Process.php
- M assets/autoload.js
- M views/block/feed-rows.html.php
- M views/block/info.html.php
- M views/controller/add.html.php


## Version 4.7.2

### Information

- **Release Date:** September 17, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs Fixed

- Layout issues

### Improvements

- Support add/edit event location with Google Maps autocomplete.
- Support show events on Google Maps.
- Support search events on Google Maps.

### Changed Files

- M Ajax/Ajax.php
- M Block/CategoryBlock.php
- M Controller/IndexController.php
- M Install.php
- M Installation/Database/Event.php
- A Installation/Version/v472.php
- A Job/ConvertOldLocation.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category/Category.php
- M Service/Event.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- A hooks/job_queue_init.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/info.html.php
- M views/block/menu.html.php
- M views/block/rsvp-action.html.php
- M views/controller/add.html.php


## Version 4.7.1

### Information

- **Release Date:** March 01, 2019
- **Best Compatibility:** phpFox >= 4.7.4

### Bugs Fixed

- Feeds - Not change the status on feed after clicking on event option.
- Feeds - Not show the content in feed when user enter HTML tag.
- Guest List pop up - Pagination issue.

### Improvements

- Support edit status with Link.
- Event Detail - Attachments - Space between attachment too large.

### Changed Files

- M Ajax/Ajax.php
- M Block/AttendingBlock.php
- M Install.php
- M Service/Callback.php
- M Service/Category/Category.php
- M assets/autoload.js
- M assets/main.less
- M hooks/template_template_getmenu_3.php
- M views/block/info.html.php
- M views/controller/add.html.php

## Version 4.7.0

### Information

- **Release Date:** December 06, 2018
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs Fixed

- Event detail - Button be overflow.
- Event - Event Detail - Start time and end time are not correct.
- Event - Event Detail - Redundant the attending option.
- Event - Event Detail - Event privacy is not correct.
- Event - Missing breadcrumb in the event detail page.
- Device - Event - Event Detail - Share button is not working.
- Event - User Profile - English error.
- Event - Event Detail - Layout spacing is not good.
- Create new event - Show wrong in feed.
- Edit event - Not load selected category.
- Show all content of event in feed.
- Mass Email Guests - Time be not converted yet.
- Invite people to come - Invite users - Invited users not receive mail .
- After click Cancel Attending an event, it will appear in Invited Events.
- Profile - Not hide event on mini menu when disable "Can browse and view the event module?".
- Most Liked, Most Discussed sort working wrong.
- From Feed, click on Maybe Attending button -> not change status.
- Bootstrap - Guest list pop up - Has 2 Add Friend Button.
- Responsive - Invited Events/Invites block - Confirm drop-down be cut.
- Invited Event - Misses padding between icon and text on buttons.
- Missed letter "s" in case has 0 guests.
- Bootstrap - Invited Events list - Layout issue after confirm an event as "Maybe Attending".

### Improvements

- Support un-attending/maybe attending when click on button.
- Event - Should not allow create a new event when the start time is smaller than the current time.
- Login as page - Event - Not redirect to the sponsor items page when Sponsor In Feed event.
- Feed - Should bring guest number to down-line for below case.

### New Features

- Event detail - Make layout follow new design.
- Invited block and detail page - Update layout follow new structure.
- Feed - Update layout follow new design.

### Changed Files

- M Ajax/Ajax.php
- M Block/AttendingBlock.php
- M Block/InfoBlock.php
- M Block/InviteBlock.php
- M Block/RsvpBlock.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- A Installation/Version/v470.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Event.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- M installer.php
- M phrase.json
- M start.php
- M views/block/attending.html.php
- M views/block/feed-rows.html.php
- M views/block/info.html.php
- M views/block/item.html.php
- M views/block/list.html.php
- M views/block/rsvp-action.html.php
- M views/block/rsvp.html.php
- M views/controller/add.html.php
- M views/controller/index.html.php

## Version 4.6.3

### Information

- **Release Date:** October 10, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Bugs Fixed

- Pending event - Allow sponsor a pending item.
- Wrong privacy when sponsor in feed.
- Sponsored Event block does not show.
- Admin can not Sponsor In Feed a event of other user.

### Improvements

- Add new setting to disallow/allow app to post on Main feed when add new item. (default is allow).
- Sponsor event - Should send notification to user when admin approve.

### Changed Files

- M Ajax/Ajax.php
- M Install.php
- M Installation/Database/Event_Category_Data.php
- M Installation/Database/Event_Text.php
- D Installation/Version/v463.php
- M README.md
- M Service/Browse.php
- M Service/Callback.php
- M Service/Category/Process.php
- M Service/Event.php
- M Service/Process.php
- M changelog.md
- M installer.php

## Version 4.6.2

### Information

- **Release Date:** September 05, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Bugs Fixed
- Show error page when disable feed/page module
- Location is still displaying ban word
- Clicks in events don't show up in Manage Sponsorships
- Show total Invited Events for non-login user
- Owner of Event received Email with wrong language when admin approved or another user comment on it
- Invited people received Email with wrong language when invite people to come to event by inviting user
- Events - Delete Event Category - Event still displaying on parent category after deleting sub-category
- Duplicated content in a mail when send Mass Email Guests for many guests use different languages
- Search Events: Not work with Location filter in My Events, Groups and Pages modules
- Manage Categories in ACP: Layout issue when drag categories

### Changed Files
- M Ajax/Ajax.php
- M Block/SponsoredBlock.php
- M Controller/Admin/DeleteController.php
- M Controller/Admin/IndexController.php
- M Controller/IndexController.php
- M Install.php
- M README.md
- M Service/Callback.php
- M Service/Category/Category.php
- M Service/Category/Process.php
- M Service/Event.php
- M Service/Process.php
- M assets/autoload.js
- M changelog.md
- M start.php
- M views/block/feed-rows.html.php
- M views/block/item.html.php
- M views/block/menu.html.php
- M views/block/mini-entry.html.php
- M views/block/sponsored.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/delete.html.php
- M views/controller/admincp/index.html.php

## Version 4.6.1

### Information

- **Release Date:** May 11, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Bugs Fixed

- Site shows error code after deleting page/group that have event.
- Can't invite guest.
- Missing description for Activity Points User group settings.
- Not reload the page when editing status which has single quote in status.
- Show tag friend feature when edit feed in event detail.
- Some minor layout issues.

### Changed Files
- M	Ajax/Ajax.php
- M	Block/InfoBlock.php
- M	Controller/IndexController.php
- M	Install.php
- M	README.md
- M	Service/Callback.php
- M	Service/Event.php
- M	assets/main.less
- A	hooks/feed.component_block_edit_user_status_end.php
- M	phrase.json
- M	views/block/info.html.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Users can select actions of items on listing page same as on detail page.
- Support drag/drop, preview, progress bar when users upload banners.
- Support AddThis on event detail page.
- Support 3 styles for pagination.
- Validate all settings, user group settings, and block settings.
- Admins can control to show/hide events that belonged to pages/groups in events listing page.
- Allow admin can change default banners.

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | cache_events_per_user | Profile Event Count | Don't use anymore |
| 2 | cache_upcoming_events_info | Cache Upcoming Events (Hours) | Don't use anymore |
| 3 | can_view_pirvate_events | Can view private events? | Don't use anymore |
| 4 | event_basic_information_time_short | Event Basic Information Time Stamp (Short) | Don't use anymore |
| 5 | event_view_time_stamp_profile | Event Profile Time Stamp | Don't use anymore |
| 6 | event_browse_time_stamp | Event Browsing Time Stamp | Don't use anymore |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | event_paging_mode | Pagination Style | Select Pagination Style at Search Page. |
| 2 | event_default_sort_time | Default time to sort events | Select default time time to sort events in listing events page (Except Pending page and Profile page) and some blocks |
| 3 | event_display_event_created_in_group | Display events which created in Group to the All Events page at the Events app | Enable to display all public events to the both Events page in group detail and All Events page in Events app. Disable to display events created by an users to the both Events page in group detail and My Events page of this user in Events app and nobody can see these events in Events app but owner. |
| 3 | event_display_event_created_in_page | Display events which created in Page to the All Events page at the Events app | Enable to display all public events to the both Events page in page detail and All Events page in Events app. Disable to display events created by an users to the both Events page in page detail and My Events page of this user in Events app and nobody can see these events in Events app but owner. |
| 4 | event_meta_description | Events Meta Description | Meta description added to pages related to the Events app. |
| 5 | event_meta_keywords | Events Meta Keywords | Meta keywords that will be displayed on sections related to the Events app. |

### New Blocks

| ID | Block | Name | Description |
| --- | -------- | ---- | ------------ |
| 1 | event.suggestion | Suggestion | Suggest events have same categories with viewing event. |



