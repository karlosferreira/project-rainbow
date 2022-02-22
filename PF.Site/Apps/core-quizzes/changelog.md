# Quizzes :: Change Log

## Version 4.7.3

### Information

- **Release Date:** July 2, 2021
- **Best Compatibility:** phpFox >= 4.8.6

### Improvements

- Compatible with PHP 8.0
- Add new setting for Email Notification of Quizzes app
- Add new settings to allow Admins review and update all email contents of Quizzes app
- Warning message if users want to leave site when they are adding a quiz

### Bugs Fixed

- Translation issue with email notification when user post quiz in Pages/Groups
- Show timestamp in UNIX when sharing Quiz via AddThis
- Issue with IE 11
- Some minor bugs

### Changed Files

- M Block/SponsoredBlock.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- A Installation/Database/Quiz_Question_Image.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Quiz.php
- M assets/autoload.js
- M phrase.json
- M views/block/entry.html.php
- M views/block/feed-rows.html.php
- M views/block/question.html.php
- M views/block/stat.html.php
- M views/block/takenby.html.php
- A views/block/upload-question-image-form.html.php
- M views/controller/add.html.php

## Version 4.7.2

### Information

- **Release Date:** August 24, 2020
- **Best Compatibility:** phpFox >= 4.7.8

### Bugs Fixed

- Some issues in Edit form
- Compatible when disabling Feed module
- Some issues related to Pages/Groups
- Does not load default question and answer boxes after loading ajax content

### Changed Files

- M Controller/AddController.php
- M Install.php
- M Service/Callback.php
- M Service/Process.php
- M assets/autoload.js
- M views/controller/add.html.ph

## Version 4.7.1

### Information

- **Release Date:** November 21, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Improvements

- Integrate with Pages/Groups.

### Changed Files

- M Ajax/Ajax.php
- M Block/FeaturedBlock.php
- M Block/SponsoredBlock.php
- M Controller/AddController.php
- M Controller/IndexController.php
- M Controller/ViewController.php
- M Install.php
- M Installation/Database/Quiz.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Quiz.php
- M changelog.md
- M hooks/template_template_getmenu_3.php
- M phrase.json
- M views/block/entry.html.php
- M views/block/link.html.php
- M views/block/question.html.php
- M views/controller/add.html.php
- M views/controller/profile.html.php

## Version 4.7.0

### Information

- **Release Date:** December 06, 2018
- **Best Compatibility:** phpFox >= 4.7.1

### Improvements

- Support utf8_mb4.

### Bugs Fixed

- Feed - See more {{item}} question link does not effect when click on.
- Feed - Total plays popup - Paginator does not work.
- Show actial statistics on detail page.

### New Features

- Feed - Update layout follow new design.

### Changed Files

- M Install.php
- A Installation/Version/v470.php
- M assets/autoload.js
- M assets/main.less
- M installer.php
- M views/block/entry.html.php
- M views/block/feed-rows.html.php

## Version 4.6.3

### Information

- **Release Date:** October 11, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements

- Add new setting to disallow/allow app to post on Main feed when add new item. (default is allow)

### Bugs Fixed

- Not minus point when delete Quiz items
- Pagination of "Member Results" section on detail page redirects to strange page

### Changed Files

- M Block/FeedRowsBlock.php
- M Install.php
- M Service/Browse.php
- M Service/Process.php
- M Service/Quiz.php
- M views/block/feed-rows.html.php

## Version 4.6.2

### Information

- **Release Date:** August 29, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements
- Create a quiz require banner: Should cache the questions and answers when user enter missing banner photo

### Bugs Fixed
- SEO - No phrase input for description and keywords
- Show error page when disable feed module
- Only show Sponsored block for owner
- Not show Un-sponsor this quiz option after Sponsored a quiz with 
- Owner of quiz received Email with wrong language when anyone have any actions (like, comment, ...) in it
- Ban word does not apply for Quiz Question & Answer
- Lost image when editing Quiz (use S3)
- Can not create a quiz when enable "Is it a requirement to upload a with the quiz?" setting
- Can not edit questions and answers of own quiz if disable "Can edit their own quizzes?" and enable "Can edit all quizzes?" 
- Not check Answers Minimum when set Answers Per Default to show < Answers Minimum
- Can not delete an own quiz if disable "Can delete their own quizzes?" and enable "Can delete all quizzes?" 

### Changed Files
- M Ajax/Ajax.php
- M Block/SponsoredBlock.php
- M Controller/AddController.php
- M Install.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Process.php
- M Service/Quiz.php
- D checksum
- M phrase.json
- M views/block/entry.html.php
- M views/block/feed-rows.html.php
- M views/block/link.html.php
- M views/block/result.html.php
- M views/controller/add.html.php

## Version 4.6.1

### Information

- **Release Date:** February 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Improvements
- Use minus icon instead of cross icon when adding questions.
- Set default number of required questions to 1 for Registered Users group.

### Bugs Fixed
- Missing SEO settings.

### Changed Files
- M views/block/question.html.php
- M Install.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### New Features

- Notify to quizzes owner when the quiz has new answer.
- Support sponsor quizzes.
- Support feature quizzes.
- New layout for all app pages and blocks.
- Users can select actions of items on listing page same as on detail page.
- Support drag/drop, preview, progress bar when users upload photos.
- Support AddThis on quiz detail page.
- Support 3 styles for pagination.
- Validate all settings, user group settings, and block settings.

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | quizzes_to_show | Quizzes to show | Don't use anymore |
| 2 | quiz_view_time_stamp | Quiz Time Stamp | Don't use anymore ||
| 3 | takers_to_show | Recent Takers To Show | Don't use anymore |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | quiz_paging_mode | Pagination Style | Select Pagination Style for Quizzes Listing Page. |

### Removed User Group Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | can_edit_own_title | This setting tells if members of this user group can edit the title, description and privacy settings in quizzes they posted. | Use setting "Can edit their own quizzes?" |
| 2 | can_edit_others_title | This setting tells if members of this user group can edit the title, description and privacy settings in quizzes posted by other members. | Use setting "Can edit all quizzes?" |

### New User Group Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | quiz_max_upload_size | Max file size for quiz photos upload | Max file size for quiz photos upload in kilobytes (kb). For unlimited add "0" without quotes. |
| 2 | can_feature_quiz | Can feature quizzes? |  |
| 3 | can_sponsor_quiz | Can members of this user group mark a quiz as Sponsor without paying fee? | |
| 4 | can_purchase_sponsor_quiz | Can members of this user group purchase a sponsored ad space for their quizzes? | |
| 5 | quiz_sponsor_price | How much is the sponsor space worth for quizzes? This works in a CPM basis. | |
| 6 | auto_publish_sponsored_item | After the user has purchased a sponsored space, should the item be published right away? | If set to false, the admin will have to approve each new purchased sponsored event space before it is shown in the site. |

### Deprecated Functions

| ID | Class Name | Function Name | Will Remove In | Reason |
| --- | -------- | ---- | ---- | ---- |
| 1 | Apps\Core_Quizzes\Service\Callback | updateCommentText | 4.6.0 | Don't use anymore |
| 2 | Apps\Core_Quizzes\Service\Quiz | getResults | 4.6.0 | Don't use anymore |

### New Blocks

| ID | Block | Name | Description |
| --- | -------- | ---- | ------------ |
| 1 | quiz.featured | Featured | Show featured quizzes. |
| 2 | quiz.sponsored | Sponsored | Show sponsored quizzes. |
