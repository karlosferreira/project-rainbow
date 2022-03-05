# Polls :: Change Log

## Version 4.7.4

### Information

- **Release Date:** September 30, 2021
- **Best Compatibility:** phpFox >= 4.7.1

### Improvements

- Change text of confirmation popup when deleting the poll
- The warning message of missing fields should be displayed sequentially

### Bugs Fixed

- The message in popup does not load when clicking on "Vote now" button on Feed
- Warning message is not right when deleting non-existing poll
- Some minor issues about create poll with warning message
- User can still create Poll inside Page by copying and pasting the link although the setting "Can browse and view polls?" is disabled

### Changed Files

- M Ajax/Ajax.php
- M Block/SponsoredBlock.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Install.php
- A Service/Api.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Poll.php
- M Service/Process.php
- A api.md
- A hooks/route_start.php
- M phrase.json
- M start.php
- M views/block/feed-rows.html.php

## Version 4.7.3

### Information

- **Release Date:** May 18, 2021
- **Best Compatibility:** phpFox >= 4.7.1

### Improvements

- Add new setting for Email Notification of Polls app
- Warning message if users want to leave site when they are adding a new poll
- Add new settings to allow Admins review and update all email contents of Polls app
- Improve quality of user's avatar that shows in polls

### Bugs Fixed

- Layout issues
- Some other minor bugs

### Changed Files

- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Service/Callback.php
- M Service/Poll.php
- M Service/Process.php
- M assets/autoload.js
- M phrase.json
- M views/block/answer-voted.html.php
- M views/block/entry.html.php
- M views/block/latest-votes.html.php
- M views/block/vote.html.php
- M views/block/votes.html.php
- M views/controller/add.html.php
- M views/controller/design.html.php

## Version 4.7.2

### Information

- **Release Date:** August 24, 2020
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs Fixed

- Use wrong phrase on Poll results popup
- Time picker does not display when enabling "Set close time"
- Compatible when disabling Feed module
- Adding poll to Forum Thread does not work
- Can view latest vote block even if public votes is disabled

### Improvements

- Disabled button "Submit your vote" after clicked

### Changed Files

- M Ajax/Ajax.php
- M Block/FeedRowsBlock.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Install.php
- M Service/Poll.php
- M Service/Process.php
- M assets/autoload.js
- M phrase.json
- M views/block/answer-voted.html.php
- M views/block/entry.html.php
- M views/block/feed-rows.html.php
- M views/block/vote.html.php

## Version 4.7.1

### Information

- **Release Date:** November 22, 2019
- **Best Compatibility:** phpFox >= 4.7.1

### Bugs Fixed

- When sponor or unsponsor you have to click X after. to go away, but all others goes away after few seconds [#2343](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2343)

### Improvements

- Integrate with Pages/Groups.

### Changed Files

- M Ajax/Ajax.php
- M Block/FeaturedBlock.php
- M Block/SponsoredBlock.php
- M Controller/AddController.php
- M Controller/DesignController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Poll.php
- M Service/Callback.php
- M Service/Poll.php
- M Service/Process.php
- M hooks/template_template_getmenu_3.php
- M phrase.json
- M views/block/entry.html.php
- M views/block/feed-rows.html.php
- M views/block/link.html.php
- M views/block/new.html.php
- M views/block/share.html.php
- M views/block/votes.html.php
- M views/controller/add.html.php
- M views/controller/index.html.php
- M views/controller/profile.html.php


## Version 4.7.0

### Information

- **Release Date:** November 23, 2018
- **Best Compatibility:** phpFox >= 4.7.1

### Improvements

- Update layout for feed item

### Changed Files

- M Ajax/Ajax.php
- M Block/FeedRowsBlock.php
- M Install.php
- M README.md
- M Service/Poll.php
- M assets/autoload.js
- M assets/main.less
- M changelog.md
- M phrase.json
- M views/block/feed-rows.html.php

## Version 4.6.2

### Information

- **Release Date:** October 12, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Add new setting to disallow/allow app to post on Main feed when add new item. (default is allow). 

### Bugs Fixed

- SEO - No phrase input for description and keywords.
- Ban word does not apply for Poll Answer.
- Show error page when disable feed module.
- Owner of poll received Email with wrong language when anyone have any actions (like, comment, ...) in it.
- Not show Unsponsor this Poll in options.

### Changed Files

- M Ajax/Ajax.php
- M Block/AnswerVotedBlock.php
- M Block/FeedRowsBlock.php
- M Block/SponsoredBlock.php
- M Install.php
- M Installation/Database/Poll_Result.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Poll.php
- M Service/Process.php
- D checksum
- M views/block/entry.html.php
- M views/block/feed-rows.html.php
- M views/block/link.html.php
- M views/block/vote.html.php

## Version 4.6.1

### Information

- **Release Date:** February 12, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements
- Improve layout.
 
### Bugs Fixed
- Missing SEO Settings.

### Changed Files
- M views/block/entry.html.php
- M views/block/vote.html.php
- M assets/main.less
- M Install.php
- M Installation/Version/v460.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### New Features

- Support multiple choices when vote polls.
- Support sponsor polls.
- Support feature polls.
- New layout for all app pages and blocks.
- Users can select actions of items on listing page same as on detail page.
- Support drag/drop, preview, progress bar when users upload photos.
- Support AddThis on poll detail page.
- Support 3 styles for pagination.
- Validate all settings, user group settings, and block settings.


### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | poll_view_time_stamp | Poll Time Stamp | Don't use anymore |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | poll_paging_mode | Pagination Style | Select Pagination Style for Polls Listing Page. |

### Removed User Group Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | can_edit_title | Can members of this user group edit the title, image, random setting, privacy setting and comment setting on a poll? | Don't use anymore |
| 2 | can_edit_question | Can members of this user group edit the question and answers of a poll?  | Don't use anymore |
| 3 | can_view_hidden_poll_votes | Can view votes even if the poll is marked to hide votes? (Admin Option) | Don't use anymore |

### New User Group Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | can_feature_poll | Can feature polls? |  |
| 2 | can_sponsor_poll | Can members of this user group mark a poll as Sponsor without paying fee? | |
| 3 | can_purchase_sponsor_poll | Can members of this user group purchase a sponsored ad space for their polls? | |
| 4 | poll_sponsor_price | How much is the sponsor space worth for polls? This works in a CPM basis. | |
| 5 | auto_publish_sponsored_item | After the user has purchased a sponsored space, should the item be published right away? | If set to false, the admin will have to approve each new purchased sponsored event space before it is shown in the site. |

### Deprecated Functions

| ID | Class Name | Function Name | Will Remove In | Reason |
| --- | -------- | ---- | ---- | ---- |
| 1 | Apps\Core_Polls\Service\Poll | getPolls | 4.7.0 | Don't use anymore |

### New Blocks

| ID | Block | Name | Description |
| --- | -------- | ---- | ------------ |
| 1 | poll.featured | Featured | Show featured polls. |
| 2 | poll.sponsored | Sponsored | Show sponsored polls. |
| 3 | poll.latest-votes | Latest Votes | Show latest votes of a poll. |
