# Mobile Api :: Change Log

## Version 4.6.8

### Information

- **Release Date:** October 25, 2021
- **Best Compatibility:** phpFox >= 4.8.4

### Improvements

- Compatible with new phpFox Mobile App
- Some bugs fixed

### Changed Files

- M Api/Form/Blog/BlogForm.php
- M Api/Form/Event/EventCategoryForm.php
- M Api/Form/Event/EventForm.php
- M Api/Form/Forum/ForumAnnouncementForm.php
- M Api/Form/Forum/ForumForm.php
- M Api/Form/Forum/ForumThreadForm.php
- M Api/Form/Group/GroupForm.php
- M Api/Form/Group/GroupInfoForm.php
- M Api/Form/Group/GroupPermissionForm.php
- M Api/Form/Group/GroupProfileForm.php
- M Api/Form/Marketplace/MarketplaceForm.php
- M Api/Form/Music/MusicAlbumForm.php
- M Api/Form/Music/MusicPlaylistForm.php
- M Api/Form/Music/MusicSongForm.php
- M Api/Form/Page/PageForm.php
- M Api/Form/Page/PagePermissionForm.php
- M Api/Form/Page/PageProfileForm.php
- M Api/Form/Photo/PhotoAlbumForm.php
- M Api/Form/Photo/PhotoForm.php
- M Api/Form/Poll/PollForm.php
- M Api/Form/Quiz/QuizForm.php
- M Api/Form/Type/GeneralType.php
- M Api/Form/Type/IntegerType.php
- M Api/Form/Type/LocationType.php
- M Api/Form/Type/PhoneNumberType.php
- M Api/Form/Validator/DateTimeFormatValidator.php
- M Api/Form/Validator/StringLengthValidator.php
- M Api/Form/Validator/TypeValidator.php
- M Api/Form/Video/VideoForm.php
- M Api/Resource/EventResource.php
- M Api/Resource/FriendSearchResource.php
- M Api/Resource/GroupAdminResource.php
- M Api/Resource/GroupMemberResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/MusicPlaylistResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/NotificationResource.php
- M Api/Resource/PageAdminResource.php
- M Api/Resource/PageMemberResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/UserResource.php
- M Api/Security/AccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Event/EventAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/Page/PageAccessControl.php
- M Install.php
- M Service/AbstractApi.php
- M Service/Admincp/AdConfigService.php
- M Service/Auth/AuthenticationApi.php
- M Service/Auth/GrantType/AppleAuth.php
- M Service/Auth/GrantType/FacebookAuth.php
- A Service/Auth/GrantType/GoogleAuth.php
- M Service/Auth/GrantType/UserPasswordAuth.php
- M Service/Auth/Storage.php
- M Service/BlogApi.php
- M Service/CoreApi.php
- M Service/EventApi.php
- M Service/FileApi.php
- M Service/ForumThreadApi.php
- M Service/GroupAdminApi.php
- M Service/GroupApi.php
- M Service/GroupInfoApi.php
- M Service/GroupMemberApi.php
- M Service/GroupPermissionApi.php
- M Service/GroupPhotoApi.php
- M Service/GroupProfileApi.php
- M Service/Helper/FeedAttachmentHelper.php
- M Service/Helper/PhotoBrowseHelper.php
- M Service/MarketplaceApi.php
- M Service/MarketplacePhotoApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicPlaylistApi.php
- M Service/MusicSongApi.php
- M Service/PageAdminApi.php
- M Service/PageApi.php
- M Service/PageInfoApi.php
- M Service/PageMemberApi.php
- M Service/PagePermissionApi.php
- M Service/PagePhotoApi.php
- M Service/PageProfileApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PollApi.php
- M Service/QuizApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Version1_4/Service/AccountApi.php
- M Version1_4/Service/UserApi.php
- M Version1_6/Api/Form/Event/EventForm.php
- M Version1_6/Api/Form/Marketplace/MarketplaceForm.php
- M Version1_6/Service/EventApi.php
- M Version1_6/Service/MarketplaceApi.php
- M Version1_7_2/Service/GroupMemberApi.php
- M Version1_7_2/Service/PageMemberApi.php
- M Version1_7_3/Api/Form/Quiz/QuizForm.php
- M Version1_7_3/Service/AccountApi.php
- M Version1_7_3/Service/QuizApi.php
- M Version1_7_3/Service/UserApi.php
- M Version1_7_4/Api/Form/Event/EventForm.php
- M Version1_7_4/Service/EventApi.php
- M hooks/user.service_auth_handlestatus.php
- A hooks/user.service_process_updatepassword.php
- M mobilePhrase.json
- M phrase.json

## Version 4.6.7

### Information

- **Release Date:** August 31, 2021
- **Best Compatibility:** phpFox >= 4.8.3

### Improvements

- Some bugs fixed

### Changed Files

- M Adapter/MobileApp/ScreenSetting.php
- M Api/Form/Blog/BlogForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Music/MusicPlaylistAddSongForm.php
- M Api/Form/Type/GeneralType.php
- M Api/Form/Type/PollAnswerType.php
- M Api/Form/Type/QuizQuestionType.php
- M Api/Form/Type/TextType.php
- M Api/Form/Type/TextareaType.php
- M Api/Form/User/DeleteAccountForm.php
- M Api/Form/Validator/RequiredValidator.php
- M Api/Resource/AdResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/Object/HyperLink.php
- M Api/Resource/VideoResource.php
- M Api/Security/AccessControl.php
- M Api/Security/Poll/PollAccessControl.php
- M Install.php
- M README.md
- M Service/Admincp/AdConfigService.php
- M Service/Admincp/MenuService.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/FeedApi.php
- M Service/FriendApi.php
- M Service/GroupApi.php
- M Service/Helper/SearchBrowseHelper.php
- M Service/PageApi.php
- M Service/SearchApi.php
- M Service/UserApi.php
- M Version1_6/Service/CommentStickerApi.php
- M Version1_7/Api/Form/Ad/SponsorItemForm.php
- M Version1_7_3/Service/AccountApi.php
- M Version1_7_4/Api/Form/Event/EventForm.php
- M changelog.md
- M hooks/run_start.php
- M start.php

## Version 4.6.6

### Information

- **Release Date:** August 04, 2021
- **Best Compatibility:** phpFox >= 4.8.3

### Improvements

- Compatible with phpFox v4.8.6 and PHP 8.0
- Compatible with new Mobile App
- Improved Form APIs
- Improved Feed APIs
- Improved Facebook and Apple login
- Support chat with non-friend on Mobile App with Instant Messaging >= 4.8.1
- Support APIs for deep link on Mobile App and allow Admin configure Mobile App banner on web
- Support Admin configure mobile menus for a specific user group 
- Support APIs for Recurring Events and Online Events (only work with Events App >= 4.8.0)
- Support APIs that allow user select Renew Method when purchase a recurring membership and renew membership
- Support new item privacy: "Community"
- Fixed some bugs

### Changed Files

- M Api/Form/Event/EventForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Quiz/QuizForm.php
- M Api/Form/Subscribe/CancelForm.php
- A Api/Form/Subscribe/RenewMethodForm.php
- M Api/Form/Type/DateType.php
- M Api/Form/Type/LocationType.php
- M Api/Form/Type/PrivacyType.php
- M Api/Form/Type/UrlType.php
- M Api/Form/User/EditProfileForm.php
- M Api/Form/User/UserRegisterForm.php
- M Api/Form/User/UserSearchForm.php
- M Api/Form/Validator/Filter/TextFilter.php
- M Api/Resource/ActivityPointResource.php
- M Api/Resource/AdResource.php
- M Api/Resource/BlogResource.php
- M Api/Resource/CommentResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/ForumAnnouncementResource.php
- M Api/Resource/ForumResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/LikeResource.php
- M Api/Resource/MarketplaceResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/PhotoResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/SubscriptionResource.php
- M Api/Resource/UserResource.php
- M Api/Resource/VideoResource.php
- M Api/Security/AccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Event/EventAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/Music/MusicAlbumAccessControl.php
- M Api/Security/Music/MusicPlaylistAccessControl.php
- M Api/Security/Music/MusicSongAccessControl.php
- M Api/Security/Photo/PhotoAccessControl.php
- M Api/Security/Photo/PhotoAlbumAccessControl.php
- M Api/Security/Quiz/QuizAccessControl.php
- M Controller/Admin/AddController.php
- M Controller/Admin/ManageInformationController.php
- M Install.php
- M Installation/Database/MenuItem.php
- A Installation/Version/v466.php
- M Service/AccountApi.php
- M Service/ActivityPointApi.php
- M Service/AdApi.php
- M Service/Admincp/MenuService.php
- M Service/Admincp/SettingService.php
- M Service/AnnouncementApi.php
- M Service/ApiVersionResolver.php
- M Service/Auth/AuthenticationApi.php
- M Service/Auth/GrantType/AppleAuth.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/Device/DeviceService.php
- M Service/EventApi.php
- M Service/FeedApi.php
- M Service/ForumPostApi.php
- M Service/FriendApi.php
- M Service/GroupApi.php
- M Service/Helper/BrowseHelper.php
- A Service/Helper/EventBrowseHelper.php
- M Service/Helper/FeedAttachmentHelper.php
- M Service/MenuApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/PageApi.php
- M Service/PhotoApi.php
- M Service/QuizResultApi.php
- M Service/SubscriptionApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Version1_4/Api/Form/User/UserRegisterForm.php
- M Version1_6/Api/Form/Event/EventForm.php
- M Version1_7_1/Service/MenuApi.php
- M Version1_7_3/Api/Form/Quiz/QuizForm.php
- M Version1_7_3/Service/UserApi.php
- A Version1_7_4/Api/Form/Event/EventForm.php
- A Version1_7_4/Service/EventApi.php
- A Version1_7_4/Service/PhotoApi.php
- A assets/admin.css
- M assets/autoload.js
- M assets/jscript/admin.js
- A assets/jscript/smartbanner/smartbanner.min.css
- A assets/jscript/smartbanner/smartbanner.min.js
- A hooks/template_getheader_end.php
- M installer.php
- M mobilePhrase.json
- M phrase.json
- M start.php
- M views/block/admincp/add-ad-config-extra.html.php
- M views/block/admincp/menu-by-type.html.php
- M views/controller/admincp/add.html.php
- M views/controller/admincp/manage-information.html.php
- M views/controller/admincp/menu-item.html.php

## Version 4.6.5

### Information

- **Release Date:** March 04, 2021
- **Best Compatibility:** phpFox >= 4.8.3

### Improvements

- Compatible with Mobile App 1.7.4
- Support Sign Up with Phone Number on Mobile App
- Improved Form APIs
- Applied manage Pages/Groups menus from Web to Mobile App
- Integrate Quizzes to Pages/Groups on Mobile App
- Improved Facebook and Apple login
- Support shows Table/Iframe HTML on Mobile App
- Add some new privacy, notification settings
- Support manage "Pushing Notifications On App", "SMS Notifications"
- Support Contact Us form on Mobile App
- Fixed some bugs

### Changed Files

- A .gitignore
- M Adapter/MobileApp/MobileApp.php
- M Adapter/MobileApp/ScreenSetting.php
- M Adapter/Utility/UrlUtility.php
- M Api/Exception/ErrorException.php
- M Api/Form/Blog/BlogForm.php
- M Api/Form/Event/EventCategoryForm.php
- M Api/Form/Event/EventForm.php
- M Api/Form/Event/EventInviteForm.php
- M Api/Form/Event/EventSearchForm.php
- M Api/Form/Feed/FeedPostForm.php
- M Api/Form/Feed/ShareFeedForm.php
- M Api/Form/Forum/ForumAnnouncementForm.php
- M Api/Form/Forum/ForumForm.php
- M Api/Form/Forum/ForumPostForm.php
- M Api/Form/Forum/ForumPostSearchForm.php
- M Api/Form/Forum/ForumThreadForm.php
- M Api/Form/Forum/ForumThreadSearchForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Group/GroupCategoryForm.php
- M Api/Form/Group/GroupForm.php
- M Api/Form/Group/GroupInviteForm.php
- M Api/Form/Group/GroupPermissionForm.php
- M Api/Form/Group/GroupPhotoForm.php
- M Api/Form/Group/GroupProfileForm.php
- M Api/Form/Group/GroupSearchForm.php
- M Api/Form/Group/GroupTypeForm.php
- M Api/Form/Marketplace/MarketplaceForm.php
- M Api/Form/Marketplace/MarketplaceInviteForm.php
- M Api/Form/Marketplace/MarketplacePhotoForm.php
- M Api/Form/Music/MusicAlbumForm.php
- M Api/Form/Music/MusicPlaylistForm.php
- M Api/Form/Music/MusicSongForm.php
- M Api/Form/Page/PageCategoryForm.php
- M Api/Form/Page/PageForm.php
- M Api/Form/Page/PagePermissionForm.php
- M Api/Form/Page/PagePhotoForm.php
- M Api/Form/Page/PageSearchForm.php
- M Api/Form/Page/PageTypeForm.php
- M Api/Form/Photo/PhotoAlbumForm.php
- M Api/Form/Photo/PhotoForm.php
- M Api/Form/Poll/PollForm.php
- M Api/Form/Quiz/QuizForm.php
- M Api/Form/SearchForm.php
- M Api/Form/Subscribe/CancelForm.php
- M Api/Form/Subscribe/ChangePackageForm.php
- M Api/Form/Type/EmailType.php
- M Api/Form/Type/PhoneNumberType.php
- M Api/Form/Type/PrivacyType.php
- M Api/Form/User/AccountSettingForm.php
- M Api/Form/User/DeleteAccountForm.php
- M Api/Form/User/EditProfileForm.php
- M Api/Form/User/UpdateLanguageForm.php
- M Api/Form/User/UserRegisterForm.php
- M Api/Form/User/UserSearchForm.php
- M Api/Form/Validator/Filter/NumberFilter.php
- M Api/Form/Validator/Filter/TextFilter.php
- M Api/Form/Video/VideoForm.php
- M Api/Resource/AccountResource.php
- M Api/Resource/AnnouncementResource.php
- M Api/Resource/CommentResource.php
- M Api/Resource/FeedEmbed/UserCover.php
- M Api/Resource/FeedEmbed/UserPhoto.php
- M Api/Resource/FeedResource.php
- M Api/Resource/ForumAnnouncementResource.php
- M Api/Resource/ForumPostResource.php
- M Api/Resource/FriendListItemResource.php
- M Api/Resource/FriendRequestResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/GroupResource.php
- A Api/Resource/GroupWidgetResource.php
- M Api/Resource/LikeResource.php
- M Api/Resource/LinkResource.php
- M Api/Resource/NotificationResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/PageTypeResource.php
- A Api/Resource/PageWidgetResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/PhotoResource.php
- M Api/Resource/PollResource.php
- M Api/Resource/QuizResource.php
- M Api/Resource/QuizResultResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/UserResource.php
- M Api/Resource/VideoResource.php
- M Api/Security/AccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Forum/ForumPostAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/Page/PageAccessControl.php
- M Api/Security/User/UserAccessControl.php
- M Controller/DocsController.php
- M Install.php
- A Installation/Database/PushNotificationSetting.php
- A Installation/Version/v464.php
- M README.md
- M Service/AbstractApi.php
- M Service/AccountApi.php
- M Service/Admincp/MenuService.php
- M Service/ApiVersionResolver.php
- M Service/AttachmentApi.php
- M Service/Auth/AuthenticationApi.php
- M Service/Auth/GrantType/AppleAuth.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/Auth/GrantType/UserPasswordAuth.php
- M Service/Auth/Storage.php
- M Service/BlogApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/Device/DeviceService.php
- M Service/EventApi.php
- M Service/FeedApi.php
- M Service/ForumApi.php
- M Service/ForumModeratorApi.php
- M Service/FriendApi.php
- M Service/FriendRequestApi.php
- M Service/GroupAdminApi.php
- M Service/GroupApi.php
- M Service/GroupPhotoApi.php
- A Service/GroupWidgetApi.php
- M Service/Helper/BrowseHelper.php
- M Service/Helper/MobileAppHelper.php
- M Service/Helper/ParametersResolver.php
- M Service/Helper/RequestHelper.php
- M Service/Helper/SearchHelper.php
- M Service/LinkApi.php
- M Service/MarketplaceApi.php
- M Service/MarketplaceInviteApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/PageAdminApi.php
- M Service/PageApi.php
- M Service/PagePhotoApi.php
- A Service/PageWidgetApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PollApi.php
- M Service/QuizApi.php
- M Service/SubscriptionApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Version1_4/Api/Form/GeneralForm.php
- M Version1_4/Api/Form/User/AccountSettingForm.php
- M Version1_4/Api/Form/User/UserRegisterForm.php
- M Version1_4/Api/Resource/ForumAnnouncementResource.php
- M Version1_4/Api/Resource/ForumThreadResource.php
- M Version1_4/Service/UserApi.php
- M Version1_6/Api/Form/Event/EventForm.php
- M Version1_6/Api/Form/Marketplace/MarketplaceForm.php
- M Version1_6/Api/Security/Marketplace/MarketplaceAccessControl.php
- M Version1_6/Service/MarketplaceApi.php
- M Version1_7/Api/Form/Ad/SponsorItemForm.php
- M Version1_7/Service/AdApi.php
- M Version1_7/Service/PollApi.php
- M Version1_7_1/Service/MenuApi.php
- A Version1_7_3/Api/Form/Quiz/QuizForm.php
- A Version1_7_3/Api/Form/User/AccountSettingForm.php
- A Version1_7_3/Api/Form/User/ContactUsForm.php
- A Version1_7_3/Api/Form/User/UserRegisterForm.php
- A Version1_7_3/Api/Resource/PushNotificationSettingsResource.php
- A Version1_7_3/Api/Resource/SmsNotificationSettingsResource.php
- A Version1_7_3/Api/Security/Quiz/QuizAccessControl.php
- A Version1_7_3/Service/AccountApi.php
- A Version1_7_3/Service/GroupMemberApi.php
- A Version1_7_3/Service/QuizApi.php
- A Version1_7_3/Service/UserApi.php
- M changelog.md
- M hooks/user.service_auth_handlestatus.php
- M installer.php
- M mobilePhrase.json
- M phrase.json
- M start.php

## Version 4.6.4

### Information

- **Release Date:** November 25, 2020
- **Best Compatibility:** phpFox >= 4.8.1

### Improvements

- Support ChatPlus
- Bugs fixed

### Changed Files

- M Adapter/MobileApp/TabSetting.php
- M Adapter/PushNotification/Firebase.php
- M Api/AbstractResourceApi.php
- M Api/Exception/ErrorException.php
- M Api/Form/Blog/BlogForm.php
- M Api/Form/Forum/ForumAnnouncementForm.php
- M Api/Form/Forum/ForumPostForm.php
- M Api/Form/Forum/ForumThreadForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Group/GroupCategoryForm.php
- M Api/Form/Marketplace/MarketplaceForm.php
- M Api/Form/Marketplace/MarketplaceInviteForm.php
- M Api/Form/Marketplace/MarketplacePhotoForm.php
- M Api/Form/Music/MusicPlaylistAddSongForm.php
- M Api/Form/Page/PageCategoryForm.php
- M Api/Form/Page/PageInviteForm.php
- M Api/Form/Page/PageProfileForm.php
- M Api/Form/SearchForm.php
- M Api/Mapping/ResourceMetadata.php
- M Api/Resource/FeedHiddenResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/ForumThreadResource.php
- M Api/Resource/FriendRequestResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/UserResource.php
- M Api/ResourceRoute.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Service/AbstractApi.php
- M Service/AccountApi.php
- M Service/AdApi.php
- M Service/AnnouncementApi.php
- M Service/Auth/AuthenticationApi.php
- M Service/Auth/Storage.php
- M Service/CoreApi.php
- M Service/EventApi.php
- M Service/FeedApi.php
- M Service/FileApi.php
- M Service/ForumAnnouncementApi.php
- M Service/ForumApi.php
- M Service/ForumModeratorApi.php
- M Service/ForumPostApi.php
- M Service/ForumThreadApi.php
- M Service/FriendApi.php
- M Service/FriendRequestApi.php
- M Service/GroupApi.php
- M Service/Helper/BrowseHelper.php
- M Service/Helper/FeedAttachmentHelper.php
- M Service/Helper/Pagination.php
- M Service/Helper/ParametersBag.php
- M Service/Helper/ParametersResolver.php
- M Service/Helper/SearchHelper.php
- M Service/LikeApi.php
- M Service/MarketplaceInviteApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicSongApi.php
- M Service/NotificationApi.php
- M Service/PageApi.php
- M Service/QuizApi.php
- M Service/QuizResultApi.php
- M Service/ReportApi.php
- M Service/SubscriptionApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Version1_4/Service/ForumPostApi.php
- M Version1_6/Service/NotificationApi.php
- M Version1_7/Api/Form/Ad/SponsorItemForm.php
- M Version1_7/Service/AdApi.php
- M Version1_7_2/Service/PageMemberApi.php
- M phrase.json

## Version 4.6.3

### Information

- **Release Date:** October 12, 2020
- **Best Compatibility:** phpFox >= 4.8.1

### Improvements

- Compatible with Mobile App 1.7.2
- Standardize data and time format on Mobile app
- Support the HEIC photos
- Hide account settings from apps which don't support on Mobile App
- Support remove members in Pages/Groups on Mobile app
- Support counter on app icon with notification from IM Firebase Chat
- Bugs fixed

### Changed Files

- M Adapter/Localization/LocalizationInterface.php
- M Adapter/Localization/NoTranslate.php
- M Adapter/MobileApp/TabSetting.php
- M Api/Exception/ErrorException.php
- M Api/Exception/NotFoundErrorException.php
- M Api/Exception/PaymentRequiredErrorException.php
- M Api/Exception/PermissionErrorException.php
- M Api/Exception/UnauthorizedErrorException.php
- M Api/Exception/UndefinedResourceName.php
- M Api/Exception/UnknownErrorException.php
- M Api/Exception/ValidationErrorException.php
- M Api/Form/ActivityPoint/ActivityPointForm.php
- M Api/Form/Event/EventForm.php
- M Api/Form/Feed/FeedPostForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Group/GroupProfileForm.php
- M Api/Form/Marketplace/MarketplaceForm.php
- M Api/Form/Page/PageForm.php
- M Api/Form/Photo/PhotoForm.php
- M Api/Form/Type/ActivityPointPackageType.php
- M Api/Form/User/EditProfileForm.php
- M Api/Form/Video/VideoForm.php
- M Api/Resource/BlogResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/GroupMemberResource.php
- M Api/Resource/PageMemberResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/UserResource.php
- M Api/Security/AccessControl.php
- M Api/Security/ActivityPoint/ActivityPointAccessControl.php
- M Api/Security/Announcement/AnnouncementAccessControl.php
- M Api/Security/Attachment/AttachmentAccessControl.php
- M Api/Security/Blog/BlogAccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Event/EventAccessControl.php
- M Api/Security/Forum/ForumAccessControl.php
- M Api/Security/Forum/ForumPostAccessControl.php
- M Api/Security/Forum/ForumThankAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/Marketplace/MarketplaceAccessControl.php
- M Api/Security/Music/MusicAlbumAccessControl.php
- M Api/Security/Music/MusicPlaylistAccessControl.php
- M Api/Security/Music/MusicSongAccessControl.php
- M Api/Security/Page/PageAccessControl.php
- M Api/Security/Photo/PhotoAccessControl.php
- M Api/Security/Photo/PhotoAlbumAccessControl.php
- M Api/Security/Poll/PollAccessControl.php
- M Api/Security/Quiz/QuizAccessControl.php
- M Api/Security/User/UserAccessControl.php
- M Api/Security/UserInterface.php
- M Api/Security/Video/VideoAccessControl.php
- M Install.php
- M Installation/Database/MenuItem.php
- M Installation/Version/v410.php
- M Installation/Version/v440.php
- M README.md
- M Service/AccountApi.php
- M Service/ActivityPointApi.php
- M Service/ApiVersionResolver.php
- M Service/AttachmentApi.php
- M Service/BlogApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/EventApi.php
- M Service/EventInviteApi.php
- M Service/FeedApi.php
- M Service/ForumAnnouncementApi.php
- M Service/ForumApi.php
- M Service/ForumPostApi.php
- M Service/ForumThreadApi.php
- M Service/GroupApi.php
- M Service/GroupMemberApi.php
- M Service/Helper/SearchBrowseHelper.php
- M Service/Helper/SearchHelper.php
- M Service/MarketplaceCategoryApi.php
- M Service/MarketplaceInviteApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/PageApi.php
- M Service/PageMemberApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PollApi.php
- M Service/PollResultApi.php
- M Service/SearchApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Version1_4/Api/Form/GeneralForm.php
- M Version1_4/Api/Resource/ForumThreadResource.php
- M Version1_4/Service/AccountApi.php
- M Version1_4/Service/ForumPostApi.php
- M Version1_4/Service/ForumThreadApi.php
- M Version1_6/Api/Form/Event/EventForm.php
- M Version1_6/Api/Form/Marketplace/MarketplaceForm.php
- M Version1_6/Service/CommentApi.php
- M Version1_6/Service/MarketplaceApi.php
- M Version1_6/Service/NotificationApi.php
- M Version1_7/Service/AccountApi.php
- M Version1_7/Service/PollApi.php
- A Version1_7_2/Service/GroupMemberApi.php
- A Version1_7_2/Service/PageMemberApi.php
- M mobilePhrase.json
- M phrase.json

## Version 4.6.2

### Information

- **Release Date:** August 31, 2020
- **Best Compatibility:** phpFox >= 4.8.1

### Improvements

- Compatible with Mobile App 1.7.1
- Improving workflow when changing email/username on App. [#2909](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2909)
- Added number of friends on Friends block on user profile screen.
- Support Checkin when post/edit status in other users wall on Mobile App.	
- Support feature user on Mobile App.
- Support remove tag on feed of Mobile App.
- Support custom Bottom Menus on Mobile App.
- Some other improvements on API.
- Bugs fixed

### Changed Files

- M Install.php
- M phrase.json
- A mobilePhrase.json
- M Install.php
- M Service/GroupApi.php
- M Service/AttachmentApi.php
- M Service/AccountApi.php
- M Service/ApiVersionResolver.php
- M Service/LikeApi.php
- M Service/BlogApi.php
- M Service/MarketplaceApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/ActivityPointApi.php
- M Service/CoreApi.php
- M Service/UserApi.php
- M Service/NotificationApi.php
- M Service/ForumThreadApi.php
- M Service/ForumPostApi.php
- M Service/EventApi.php
- M Service/MenuApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicSongApi.php
- M Service/MusicPlaylistApi.php
- M Service/MusicGenreApi.php
- M Service/PageApi.php
- M Service/PhotoApi.php
- M Service/PhotoAlbumApi.php
- M Service/PollApi.php
- M Service/PollResultApi.php
- M Service/QuizApi.php
- M Service/VideoApi.php
- M Service/UserApi.php
- M Service/FileApi.php
- M Service/FeedApi.php
- M Service/FriendApi.php
- M Service/FriendRequestApi.php
- M Service/AbstractApi.php
- M Service/Helper/BrowseHelper.php
- M Service/Helper/ParametersResolver.php
- M Api/AbstractResourceApi.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/CommentResource.php
- M Api/Resource/BlogResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/UserResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/MarketplaceResource.php
- M Api/Resource/MusicAlbumResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/PollResource.php
- M Api/Resource/QuizResource.php
- M Api/Resource/VideoResource.php
- M Api/Resource/GroupSectionResource.php
- M Api/Resource/PageSectionResource.php
- M Api/Resource/PollResultResource.php
- A Api/Resource/UserStatisticResource.php
- M Api/Resource/ForumThreadResource.php
- M Api/Security/User/UserAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Form/Type/PrivacyType.php
- M Version1_4/Service/UserApi.php
- M Version1_6/Service/MarketplaceApi.php
- M Version1_6/Service/CommentApi.php
- M Version1_6/Service/CommentStickerApi.php
- M Version1_7/Service/PollApi.php
- M Version1_7/Service/AdApi.php
- A Version1_7/Service/AccountApi.php
- A Version1_7_1/Service/MenuApi.php
- A Adapter/MobileApp/TabSetting.php
- M Adapter/Localization/PhpfoxLocalization.php

## Version 4.6.1

### Information

- **Release Date:** July 01, 2020
- **Best Compatibility:** phpFox >= 4.7.7

### Improvements

- Bugs fixed

### Changed Files

- M Install.php
- M Service/CommentApi.php

## Version 4.6.0

### Information

- **Release Date:** May 27, 2020
- **Best Compatibility:** phpFox >= 4.7.7

### Improvements

- Compatible with Mobile App 1.7.0
- Bug fixes

### Changed Files

- M Adapter/MobileApp/Screen.php
- M Adapter/Parse/ParseInterface.php
- M Adapter/Parse/PhpfoxParse.php
- M Adapter/PushNotification/Firebase.php
- A Api/Form/ActivityPoint/ActivityPointForm.php
- M Api/Form/Type/AbstractOptionType.php
- A Api/Form/Type/ActivityPointPackageType.php
- M Api/Form/User/UserRegisterForm.php
- M Api/Resource/ActivityPointResource.php
- M Api/Resource/AdResource.php
- M Api/Resource/BlogResource.php
- M Api/Resource/CommentResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/ForumThreadResource.php
- A Api/Resource/FriendMentionResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/LinkResource.php
- M Api/Resource/MarketplaceResource.php
- M Api/Resource/MusicAlbumResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/PhotoResource.php
- M Api/Resource/PollResource.php
- M Api/Resource/QuizResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/UserResource.php
- M Api/Resource/VideoResource.php
- A Api/Security/ActivityPoint/ActivityPointAccessControl.php
- M Api/Security/Blog/BlogAccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Event/EventAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/Marketplace/MarketplaceAccessControl.php
- M Api/Security/Music/MusicAlbumAccessControl.php
- M Api/Security/Music/MusicSongAccessControl.php
- M Api/Security/Page/PageAccessControl.php
- M Api/Security/Photo/PhotoAccessControl.php
- M Api/Security/Photo/PhotoAlbumAccessControl.php
- M Api/Security/Poll/PollAccessControl.php
- M Api/Security/Quiz/QuizAccessControl.php
- M Api/Security/Video/VideoAccessControl.php
- M Install.php
- M README.md
- M Service/AbstractApi.php
- A Service/ActivityPointApi.php
- M Service/AdApi.php
- M Service/Admincp/AdConfigService.php
- M Service/ApiVersionResolver.php
- M Service/Auth/GrantType/AppleAuth.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/BlogApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/Device/DeviceService.php
- M Service/EventApi.php
- M Service/FeedApi.php
- M Service/ForumSubscribeApi.php
- M Service/ForumThankApi.php
- M Service/ForumThreadApi.php
- M Service/FriendApi.php
- M Service/GroupApi.php
- M Service/Helper/PsrRequestHelper.php
- M Service/Helper/SearchHelper.php
- M Service/LinkApi.php
- M Service/MarketplaceApi.php
- M Service/MarketplaceCategoryApi.php
- M Service/MarketplaceInviteApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicPlaylistApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/NotificationApi.php
- M Service/PageApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PollApi.php
- M Service/QuizApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Version1_4/Api/Form/User/UserRegisterForm.php
- M Version1_4/Service/UserApi.php
- M Version1_6/Api/Form/Marketplace/MarketplaceForm.php
- A Version1_6/Api/Resource/MarketplaceResource.php
- A Version1_6/Api/Security/Marketplace/MarketplaceAccessControl.php
- M Version1_6/Service/CommentApi.php
- M Version1_6/Service/MarketplaceApi.php
- A Version1_6/Service/NotificationApi.php
- A Version1_7/Api/Form/Ad/SponsorItemForm.php
- A Version1_7/Service/AdApi.php
- M changelog.md
- M phrase.json
- M vendor/


## Version 4.5.2

### Information

- **Release Date:** February 11, 2020
- **Best Compatibility:** phpFox >= 4.7.7

### Improvements

- Bug fixes
- Integrate Marketplace with Pages/Groups

### Changed Files

- M Install.php
- M Api/Resource/GroupResource.php
- M Api/Resource/PageResource.php
- M Service/MarketplaceApi.php
- M Service/GroupApi.php
- M Service/PageApi.php
- M Version1_6/Service/MarketplaceApi.php
- M Version1_6/Api/Form/Marketplace/MarketplaceForm.php
- A Version1_6/Api/Resource/MarketplaceResource.php
- A Version1_6/Api/Security/Marketplace/MarketplaceAccessControl.php

## Version 4.5.1

### Information

- **Release Date:** February 04, 2020
- **Best Compatibility:** phpFox >= 4.7.7

### Improvements

- Bug fixes

### Changed Files

- M Install.php
- M Service/Auth/GrantType/AppleAuth.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/CoreApi.php
- M Service/FeedApi.php
- M Service/Helper/PsrRequestHelper.php
- M phrase.json


## Version 4.5.0

### Information

- **Release Date:** January 08, 2020
- **Best Compatibility:** phpFox >= 4.7.7

### Improvements

- Compatible with Mobile App 1.6.x
- Bug fixes

### Changed Files

- M Adapter/Localization/LocalizationInterface.php
- M Adapter/Localization/NoTranslate.php
- M Adapter/Localization/PhpfoxLocalization.php
- M Adapter/MobileApp/BaseView.php
- M Adapter/MobileApp/MobileApp.php
- M Adapter/MobileApp/MobileAppSettingInterface.php
- M Adapter/MobileApp/Screen.php
- M Adapter/MobileApp/ScreenSetting.php
- M Adapter/MobileApp/SettingParametersBag.php
- M Adapter/Parse/ParseInterface.php
- M Adapter/Privacy/UserPrivacyInterface.php
- M Adapter/PushNotification/Firebase.php
- M Adapter/PushNotification/PushNotificationInterface.php
- M Adapter/Setting/PhpfoxSetting.php
- M Adapter/Setting/SettingInterface.php
- M Adapter/Utility/ArrayUtility.php
- M Adapter/Utility/UrlUtility.php
- M Api/AbstractResourceApi.php
- M Api/ActivityFeedInterface.php
- M Api/ApiRequestInterface.php
- M Api/Exception/ErrorException.php
- M Api/Form/Blog/BlogForm.php
- M Api/Form/Event/EventCategoryForm.php
- M Api/Form/Event/EventForm.php
- M Api/Form/Event/EventInviteForm.php
- M Api/Form/Feed/FeedPostForm.php
- M Api/Form/Feed/ShareFeedForm.php
- M Api/Form/Forum/ForumAnnouncementForm.php
- M Api/Form/Forum/ForumForm.php
- M Api/Form/Forum/ForumPostForm.php
- M Api/Form/Forum/ForumPostSearchForm.php
- M Api/Form/Forum/ForumThreadForm.php
- M Api/Form/Forum/ForumThreadSearchForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Group/GroupCategoryForm.php
- M Api/Form/Group/GroupForm.php
- M Api/Form/Group/GroupInfoForm.php
- M Api/Form/Group/GroupInviteForm.php
- M Api/Form/Group/GroupPermissionForm.php
- M Api/Form/Group/GroupPhotoForm.php
- M Api/Form/Group/GroupProfileForm.php
- M Api/Form/Group/GroupTypeForm.php
- M Api/Form/Marketplace/MarketplaceForm.php
- M Api/Form/Marketplace/MarketplaceInviteForm.php
- M Api/Form/Marketplace/MarketplacePhotoForm.php
- M Api/Form/Music/MusicAlbumForm.php
- M Api/Form/Music/MusicPlaylistAddSongForm.php
- M Api/Form/Music/MusicPlaylistForm.php
- M Api/Form/Music/MusicSongForm.php
- M Api/Form/Page/PageCategoryForm.php
- M Api/Form/Page/PageClaimForm.php
- M Api/Form/Page/PageForm.php
- M Api/Form/Page/PageInfoForm.php
- M Api/Form/Page/PageInviteForm.php
- M Api/Form/Page/PagePermissionForm.php
- M Api/Form/Page/PagePhotoForm.php
- M Api/Form/Page/PageProfileForm.php
- M Api/Form/Page/PageTypeForm.php
- M Api/Form/Photo/PhotoAlbumForm.php
- M Api/Form/Photo/PhotoForm.php
- M Api/Form/Poll/PollForm.php
- M Api/Form/Quiz/QuizForm.php
- M Api/Form/Report/ReportForm.php
- M Api/Form/SearchForm.php
- M Api/Form/Subscribe/CancelForm.php
- M Api/Form/Subscribe/ChangePackageForm.php
- M Api/Form/TransformerInterface.php
- M Api/Form/Type/AbstractOptionType.php
- M Api/Form/Type/BirthdayType.php
- M Api/Form/Type/CountryStateType.php
- M Api/Form/Type/FileType.php
- M Api/Form/Type/GeneralType.php
- M Api/Form/Type/HierarchyType.php
- M Api/Form/Type/LocationType.php
- M Api/Form/Type/MultiFileType.php
- M Api/Form/Type/PrivacyType.php
- M Api/Form/Type/RangeType.php
- M Api/Form/Type/TextType.php
- M Api/Form/User/AccountSettingForm.php
- M Api/Form/User/ChangePasswordForm.php
- M Api/Form/User/DeleteAccountForm.php
- M Api/Form/User/EditProfileForm.php
- M Api/Form/User/ForgetPasswordRequest.php
- M Api/Form/User/UpdateLanguageForm.php
- M Api/Form/User/UserRegisterForm.php
- M Api/Form/User/UserSearchForm.php
- M Api/Form/Validator/AllowedValuesValidator.php
- M Api/Form/Validator/DateTimeFormatValidator.php
- M Api/Form/Validator/Filter/NumberFilter.php
- M Api/Form/Validator/Filter/TextFilter.php
- M Api/Form/Validator/NumberRangeValidator.php
- M Api/Form/Validator/TypeValidator.php
- M Api/Form/Validator/ValidateInterface.php
- M Api/Form/Video/VideoForm.php
- M Api/Mapping/ResourceMetadata.php
- M Api/ReducerInterface.php
- M Api/Resource/AccountResource.php
- M Api/Resource/ActivityPointResource.php
- M Api/Resource/AdResource.php
- M Api/Resource/AnnouncementResource.php
- M Api/Resource/AttachmentResource.php
- M Api/Resource/BlockedUserResource.php
- M Api/Resource/BlogCategoryResource.php
- M Api/Resource/BlogResource.php
- M Api/Resource/CommentResource.php
- M Api/Resource/EmailNotificationSettingsResource.php
- M Api/Resource/EventCategoryResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedEmbed/UserPhoto.php
- M Api/Resource/FeedHiddenResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/ForumAnnouncementResource.php
- M Api/Resource/ForumPostResource.php
- M Api/Resource/ForumResource.php
- M Api/Resource/ForumThankResource.php
- M Api/Resource/ForumThreadResource.php
- M Api/Resource/FriendFeedResource.php
- M Api/Resource/FriendListItemResource.php
- M Api/Resource/FriendListResource.php
- M Api/Resource/FriendRequestResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/FriendSearchResource.php
- M Api/Resource/GroupAdminResource.php
- M Api/Resource/GroupCategoryResource.php
- M Api/Resource/GroupMemberResource.php
- M Api/Resource/GroupPhotoResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/GroupSectionResource.php
- M Api/Resource/GroupTypeResource.php
- M Api/Resource/ItemPrivacySettingsResource.php
- M Api/Resource/LikeResource.php
- M Api/Resource/MarketplaceCategoryResource.php
- M Api/Resource/MarketplaceInviteResource.php
- M Api/Resource/MarketplaceInvoiceResource.php
- M Api/Resource/MarketplacePhotoResource.php
- M Api/Resource/MarketplaceResource.php
- M Api/Resource/MusicAlbumResource.php
- M Api/Resource/MusicGenreResource.php
- M Api/Resource/MusicPlaylistResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/NotificationResource.php
- A Api/Resource/Object/Coordinate.php
- M Api/Resource/Object/FeedParam.php
- M Api/Resource/Object/HyperLink.php
- M Api/Resource/PageAdminResource.php
- M Api/Resource/PageCategoryResource.php
- M Api/Resource/PageMemberResource.php
- M Api/Resource/PagePhotoResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/PageSectionResource.php
- M Api/Resource/PageTypeResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/PhotoCategoryResource.php
- M Api/Resource/PhotoResource.php
- M Api/Resource/PollAnswerResource.php
- M Api/Resource/PollResource.php
- M Api/Resource/PollResultResource.php
- M Api/Resource/ProfilePrivacySettingsResource.php
- M Api/Resource/QuizResource.php
- M Api/Resource/QuizResultResource.php
- M Api/Resource/QuizUserResultResource.php
- M Api/Resource/ReportResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/SearchResource.php
- M Api/Resource/SubscriptionResource.php
- M Api/Resource/UserInfoResource.php
- M Api/Resource/UserPhotoResource.php
- M Api/Resource/UserResource.php
- M Api/Resource/VideoResource.php
- M Api/ResourceRoute.php
- M Api/Security/AccessControl.php
- M Api/Security/Announcement/AnnouncementAccessControl.php
- M Api/Security/Attachment/AttachmentAccessControl.php
- M Api/Security/Blog/BlogAccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Event/EventAccessControl.php
- M Api/Security/Forum/ForumAnnouncementAccessControl.php
- M Api/Security/Forum/ForumPostAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/GroupsAppContext.php
- M Api/Security/Marketplace/MarketplaceAccessControl.php
- M Api/Security/Music/MusicAlbumAccessControl.php
- M Api/Security/Music/MusicPlaylistAccessControl.php
- M Api/Security/Music/MusicSongAccessControl.php
- M Api/Security/Page/PageAccessControl.php
- M Api/Security/PagesAppContext.php
- M Api/Security/PermissionInterface.php
- M Api/Security/Photo/PhotoAccessControl.php
- M Api/Security/Photo/PhotoAlbumAccessControl.php
- M Api/Security/Poll/PollAccessControl.php
- M Api/Security/Quiz/QuizAccessControl.php
- M Api/Security/User/UserAccessControl.php
- M Api/Security/UserInterface.php
- M Api/Security/Video/VideoAccessControl.php
- M Block/Admin/MenuByTypeBlock.php
- M Controller/Admin/AddAdmobConfigController.php
- M Controller/Admin/AddController.php
- M Controller/Admin/ManageAdmobConfigController.php
- M Controller/Admin/ManageInformationController.php
- M Install.php
- M Installation/Database/AdsConfigs.php
- M Installation/Database/AdsConfigsScreen.php
- M Installation/Database/DeviceToken.php
- M Installation/Database/MenuItem.php
- M Installation/Version/v410.php
- M Installation/Version/v421.php
- M Installation/Version/v440.php
- M README.md
- M Service/AbstractApi.php
- M Service/AccountApi.php
- M Service/AdApi.php
- M Service/Admincp/AdConfigService.php
- M Service/Admincp/MenuService.php
- M Service/Admincp/SettingService.php
- M Service/AnnouncementApi.php
- M Service/ApiVersionResolver.php
- M Service/AttachmentApi.php
- M Service/Auth/AuthenticationApi.php
- A Service/Auth/GrantType/AppleAuth.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/Auth/GrantType/UserPasswordAuth.php
- M Service/Auth/Storage.php
- M Service/BlogApi.php
- M Service/BlogCategoryApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/Device/DeviceService.php
- M Service/EventApi.php
- M Service/EventCategoryApi.php
- M Service/EventInviteApi.php
- M Service/FeedApi.php
- M Service/FileApi.php
- M Service/ForumAnnouncementApi.php
- M Service/ForumApi.php
- M Service/ForumModeratorApi.php
- M Service/ForumPostApi.php
- M Service/ForumSubscribeApi.php
- M Service/ForumThankApi.php
- M Service/ForumThreadApi.php
- M Service/FriendApi.php
- M Service/FriendRequestApi.php
- M Service/FriendTagApi.php
- M Service/GroupAdminApi.php
- M Service/GroupApi.php
- M Service/GroupCategoryApi.php
- M Service/GroupInfoApi.php
- M Service/GroupInviteApi.php
- M Service/GroupMemberApi.php
- M Service/GroupPermissionApi.php
- M Service/GroupPhotoApi.php
- M Service/GroupProfileApi.php
- M Service/GroupTypeApi.php
- M Service/Helper/BrowseHelper.php
- M Service/Helper/FeedAttachmentHelper.php
- M Service/Helper/Pagination.php
- M Service/Helper/ParametersBag.php
- M Service/Helper/ParametersResolver.php
- M Service/Helper/RequestHelper.php
- M Service/LikeApi.php
- M Service/MarketplaceApi.php
- M Service/MarketplaceCategoryApi.php
- M Service/MarketplaceInviteApi.php
- M Service/MarketplaceInvoiceApi.php
- M Service/MarketplacePhotoApi.php
- M Service/MenuApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicGenreApi.php
- M Service/MusicPlaylistApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/NotificationApi.php
- M Service/PageAdminApi.php
- M Service/PageApi.php
- M Service/PageCategoryApi.php
- M Service/PageInfoApi.php
- M Service/PageInviteApi.php
- M Service/PageMemberApi.php
- M Service/PagePermissionApi.php
- M Service/PagePhotoApi.php
- M Service/PageProfileApi.php
- M Service/PageTypeApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PhotoCategoryApi.php
- M Service/PollApi.php
- M Service/PollResultApi.php
- M Service/QuizApi.php
- M Service/QuizResultApi.php
- M Service/ReportApi.php
- M Service/ReportReasonApi.php
- M Service/SearchApi.php
- M Service/SubscriptionApi.php
- M Service/TagApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Service/VideoCategoryApi.php
- M Version1_4/Api/Form/GeneralForm.php
- M Version1_4/Api/Form/User/AccountSettingForm.php
- M Version1_4/Api/Form/User/UserRegisterForm.php
- M Version1_4/Api/Resource/ForumAnnouncementResource.php
- M Version1_4/Service/AccountApi.php
- M Version1_4/Service/ForumAnnouncementApi.php
- M Version1_4/Service/ForumPostApi.php
- M Version1_4/Service/UserApi.php
- A Version1_6/Api/Form/Event/EventForm.php
- A Version1_6/Api/Form/Marketplace/MarketplaceForm.php
- A Version1_6/Api/Resource/CommentStickerResource.php
- A Version1_6/Api/Resource/CommentStickerSetResource.php
- A Version1_6/Service/CommentApi.php
- A Version1_6/Service/CommentStickerApi.php
- A Version1_6/Service/EventApi.php
- A Version1_6/Service/MarketplaceApi.php
- A assets/images/app-images/no-comment.png
- A assets/images/app-images/no-friend-request.png
- A assets/images/app-images/no-friend.png
- A assets/images/app-images/no-member.png
- A assets/images/app-images/no-notification.png
- M assets/jscript/admin.js
- M changelog.md
- M hooks/friend.service_request_process_add_end.php
- M hooks/mail.service_process_add.php
- M hooks/notification.service_process_add_end.php
- M hooks/user.component_controller_setting_settitle.php
- M hooks/validator.admincp_settings_mobile.php
- M phrase.json
- M start.php


## Version 4.4.1

### Information

- **Release Date:** August 28, 2019
- **Best Compatibility:** phpFox >= 4.7.7

### Improvements

- Store user activity
- Bug fixes

### Changed Files

- M Service/ApiVersionResolver.php

## Version 4.4.0

### Information

- **Release Date:** August 01, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Compatible with Mobile App 1.5.x
- Bug fixes

### Changed Files

- M Adapter/Localization/NoTranslate.php
- M Adapter/Localization/PhpfoxLocalization.php
- M Adapter/MobileApp/BaseView.php
- M Adapter/MobileApp/MobileApp.php
- M Adapter/MobileApp/Screen.php
- M Adapter/MobileApp/ScreenSetting.php
- M Adapter/MobileApp/SettingParametersBag.php
- M Adapter/Parse/PhpfoxParse.php
- M Adapter/Privacy/UserPrivacy.php
- M Adapter/PushNotification/Firebase.php
- M Adapter/Utility/ArrayUtility.php
- M Adapter/Utility/UrlUtility.php
- M Ajax/Ajax.php
- M Api/AbstractResourceApi.php
- M Api/Exception/NotFoundErrorException.php
- M Api/Exception/PaymentRequiredErrorException.php
- M Api/Exception/UnauthorizedErrorException.php
- M Api/Form/Blog/BlogForm.php
- M Api/Form/Blog/BlogSearchForm.php
- M Api/Form/Event/EventCategoryForm.php
- M Api/Form/Event/EventForm.php
- M Api/Form/Event/EventInviteForm.php
- M Api/Form/Event/EventSearchForm.php
- M Api/Form/Feed/FeedPostForm.php
- M Api/Form/Feed/ShareFeedForm.php
- M Api/Form/Form.php
- M Api/Form/Forum/ForumAnnouncementForm.php
- M Api/Form/Forum/ForumForm.php
- M Api/Form/Forum/ForumPostForm.php
- M Api/Form/Forum/ForumPostSearchForm.php
- M Api/Form/Forum/ForumThreadForm.php
- M Api/Form/Forum/ForumThreadSearchForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Group/GroupCategoryForm.php
- M Api/Form/Group/GroupForm.php
- M Api/Form/Group/GroupInfoForm.php
- M Api/Form/Group/GroupInviteForm.php
- M Api/Form/Group/GroupPermissionForm.php
- M Api/Form/Group/GroupPhotoForm.php
- M Api/Form/Group/GroupProfileForm.php
- M Api/Form/Group/GroupTypeForm.php
- M Api/Form/Marketplace/MarketplaceForm.php
- M Api/Form/Marketplace/MarketplaceInviteForm.php
- M Api/Form/Marketplace/MarketplacePhotoForm.php
- M Api/Form/Music/MusicAlbumForm.php
- M Api/Form/Music/MusicPlaylistAddSongForm.php
- M Api/Form/Music/MusicPlaylistForm.php
- M Api/Form/Music/MusicSongForm.php
- M Api/Form/Page/PageCategoryForm.php
- M Api/Form/Page/PageClaimForm.php
- M Api/Form/Page/PageForm.php
- M Api/Form/Page/PageInfoForm.php
- M Api/Form/Page/PageInviteForm.php
- M Api/Form/Page/PagePermissionForm.php
- M Api/Form/Page/PagePhotoForm.php
- M Api/Form/Page/PageProfileForm.php
- M Api/Form/Page/PageTypeForm.php
- M Api/Form/Photo/PhotoAlbumForm.php
- M Api/Form/Photo/PhotoForm.php
- M Api/Form/Poll/PollForm.php
- M Api/Form/Quiz/QuizForm.php
- M Api/Form/Report/ReportForm.php
- M Api/Form/SearchForm.php
- M Api/Form/Subscribe/CancelForm.php
- M Api/Form/Subscribe/ChangePackageForm.php
- M Api/Form/Type/AbstractOptionType.php
- M Api/Form/Type/BirthdayType.php
- M Api/Form/Type/ButtonType.php
- M Api/Form/Type/CheckboxType.php
- M Api/Form/Type/ChoiceType.php
- M Api/Form/Type/CountryStateType.php
- M Api/Form/Type/CustomGendersType.php
- M Api/Form/Type/DateTimeType.php
- M Api/Form/Type/DateType.php
- M Api/Form/Type/EmailType.php
- M Api/Form/Type/FileType.php
- M Api/Form/Type/GeneralType.php
- M Api/Form/Type/HierarchyType.php
- M Api/Form/Type/IntegerType.php
- M Api/Form/Type/MembershipPackageType.php
- M Api/Form/Type/MultiCheckbox.php
- M Api/Form/Type/MultiChoiceType.php
- M Api/Form/Type/MultiFileType.php
- M Api/Form/Type/PasswordType.php
- M Api/Form/Type/PhoneNumberType.php
- M Api/Form/Type/PriceType.php
- M Api/Form/Type/PrivacyType.php
- M Api/Form/Type/QuizQuestionType.php
- M Api/Form/Type/TextType.php
- M Api/Form/Type/TextareaType.php
- M Api/Form/Type/TimeType.php
- M Api/Form/Type/UrlType.php
- M Api/Form/Type/VideoUploadType.php
- M Api/Form/User/AccountSettingForm.php
- M Api/Form/User/ChangePasswordForm.php
- M Api/Form/User/DeleteAccountForm.php
- M Api/Form/User/EditProfileForm.php
- M Api/Form/User/ForgetPasswordRequest.php
- A Api/Form/User/UpdateLanguageForm.php
- M Api/Form/User/UserRegisterForm.php
- M Api/Form/User/UserSearchForm.php
- M Api/Form/Validator/AllowedValuesValidator.php
- M Api/Form/Validator/Filter/TextFilter.php
- M Api/Form/Validator/TypeValidator.php
- M Api/Form/Video/VideoForm.php
- M Api/ReducerInterface.php
- M Api/Resource/AccountResource.php
- M Api/Resource/ActivityPointResource.php
- A Api/Resource/AnnouncementResource.php
- M Api/Resource/AttachmentResource.php
- M Api/Resource/BlockedUserResource.php
- M Api/Resource/BlogResource.php
- M Api/Resource/CommentResource.php
- M Api/Resource/EmailNotificationSettingsResource.php
- M Api/Resource/EventCategoryResource.php
- M Api/Resource/EventInviteResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedEmbed/UserPhoto.php
- A Api/Resource/FeedHiddenResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/ForumAnnouncementResource.php
- M Api/Resource/ForumPostResource.php
- M Api/Resource/ForumResource.php
- M Api/Resource/ForumSubscribeResource.php
- M Api/Resource/ForumThankResource.php
- M Api/Resource/ForumThreadResource.php
- M Api/Resource/FriendFeedResource.php
- M Api/Resource/FriendListItemResource.php
- M Api/Resource/FriendListResource.php
- M Api/Resource/FriendRequestResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/FriendSearchResource.php
- M Api/Resource/GroupAdminResource.php
- M Api/Resource/GroupCategoryResource.php
- M Api/Resource/GroupInfoResource.php
- M Api/Resource/GroupMemberResource.php
- M Api/Resource/GroupPhotoResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/GroupSectionResource.php
- M Api/Resource/GroupTypeResource.php
- M Api/Resource/ItemPrivacySettingsResource.php
- M Api/Resource/LikeResource.php
- M Api/Resource/LinkResource.php
- M Api/Resource/MarketplaceCategoryResource.php
- M Api/Resource/MarketplaceInviteResource.php
- A Api/Resource/MarketplaceInvoiceResource.php
- M Api/Resource/MarketplacePhotoResource.php
- M Api/Resource/MarketplaceResource.php
- M Api/Resource/MusicAlbumResource.php
- M Api/Resource/MusicGenreResource.php
- M Api/Resource/MusicPlaylistResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/NotificationResource.php
- M Api/Resource/Object/Image.php
- M Api/Resource/PageAdminResource.php
- M Api/Resource/PageCategoryResource.php
- M Api/Resource/PageMemberResource.php
- M Api/Resource/PagePermissionResource.php
- M Api/Resource/PagePhotoResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/PageSectionResource.php
- M Api/Resource/PageTypeResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/PhotoCategoryResource.php
- M Api/Resource/PhotoResource.php
- M Api/Resource/PollAnswerResource.php
- M Api/Resource/PollResource.php
- M Api/Resource/PollResultResource.php
- M Api/Resource/ProfilePrivacySettingsResource.php
- M Api/Resource/QuizResource.php
- M Api/Resource/QuizResultResource.php
- M Api/Resource/QuizUserResultResource.php
- M Api/Resource/ReportResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/SearchResource.php
- M Api/Resource/SubscriptionResource.php
- M Api/Resource/UserInfoResource.php
- M Api/Resource/UserPhotoResource.php
- M Api/Resource/UserResource.php
- M Api/Resource/VideoCategoryResource.php
- M Api/Resource/VideoResource.php
- M Api/ResourceInterface.php
- M Api/Security/AccessControl.php
- A Api/Security/Announcement/AnnouncementAccessControl.php
- M Api/Security/AppContextInterface.php
- M Api/Security/Attachment/AttachmentAccessControl.php
- M Api/Security/Blog/BlogAccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Forum/ForumAccessControl.php
- M Api/Security/Forum/ForumAnnouncementAccessControl.php
- M Api/Security/Forum/ForumPostAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/GroupsAppContext.php
- M Api/Security/Music/MusicSongAccessControl.php
- M Api/Security/Page/PageAccessControl.php
- M Api/Security/PagesAppContext.php
- M Api/Security/Photo/PhotoAccessControl.php
- M Api/Security/Poll/PollAccessControl.php
- M Api/Security/User/UserAccessControl.php
- M Api/Security/Video/VideoAccessControl.php
- M Controller/Admin/AddController.php
- M Controller/Admin/ManageAdmobConfigController.php
- M Controller/Admin/ManageInformationController.php
- M Controller/Admin/MenuItemController.php
- M Controller/DocsController.php
- M Install.php
- M Installation/Database/AdsConfigs.php
- M Installation/Database/AdsConfigsScreen.php
- M Installation/Database/DeviceToken.php
- M Installation/Database/MenuItem.php
- M Installation/Version/v410.php
- M Installation/Version/v421.php
- A Installation/Version/v440.php
- M README.md
- M Service/AbstractApi.php
- M Service/AccountApi.php
- M Service/AdApi.php
- M Service/Admincp/AdConfigService.php
- M Service/Admincp/MenuService.php
- M Service/Admincp/SettingService.php
- A Service/AnnouncementApi.php
- M Service/ApiVersionResolver.php
- M Service/AttachmentApi.php
- M Service/Auth/AuthenticationApi.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/Auth/GrantType/UserPasswordAuth.php
- M Service/Auth/RestApiTransport.php
- M Service/Auth/Storage.php
- M Service/BlogApi.php
- M Service/BlogCategoryApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/Device/DeviceService.php
- M Service/EventApi.php
- M Service/EventCategoryApi.php
- M Service/EventInviteApi.php
- M Service/FeedApi.php
- M Service/FileApi.php
- M Service/ForumAnnouncementApi.php
- M Service/ForumApi.php
- M Service/ForumModeratorApi.php
- M Service/ForumPostApi.php
- M Service/ForumSubscribeApi.php
- M Service/ForumThankApi.php
- M Service/ForumThreadApi.php
- M Service/FriendApi.php
- M Service/FriendRequestApi.php
- M Service/FriendTagApi.php
- M Service/GroupAdminApi.php
- M Service/GroupApi.php
- M Service/GroupCategoryApi.php
- M Service/GroupInfoApi.php
- M Service/GroupInviteApi.php
- M Service/GroupMemberApi.php
- M Service/GroupPermissionApi.php
- M Service/GroupPhotoApi.php
- M Service/GroupProfileApi.php
- M Service/GroupTypeApi.php
- M Service/Helper/BrowseHelper.php
- M Service/Helper/FeedAttachmentHelper.php
- M Service/Helper/Pagination.php
- M Service/Helper/ParametersResolver.php
- M Service/Helper/PhotoBrowseHelper.php
- M Service/Helper/PsrRequestHelper.php
- M Service/Helper/RequestHelper.php
- M Service/Helper/SearchBrowseHelper.php
- M Service/Helper/SearchHelper.php
- M Service/IntlApi.php
- M Service/LikeApi.php
- M Service/LinkApi.php
- M Service/MarketplaceApi.php
- M Service/MarketplaceCategoryApi.php
- M Service/MarketplaceInviteApi.php
- A Service/MarketplaceInvoiceApi.php
- M Service/MarketplacePhotoApi.php
- M Service/MenuApi.php
- M Service/MessageApi.php
- M Service/MessageConversationApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicGenreApi.php
- M Service/MusicPlaylistApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/NotificationApi.php
- M Service/PageAdminApi.php
- M Service/PageApi.php
- M Service/PageCategoryApi.php
- M Service/PageInfoApi.php
- M Service/PageInviteApi.php
- M Service/PageMemberApi.php
- M Service/PagePermissionApi.php
- M Service/PagePhotoApi.php
- M Service/PageProfileApi.php
- M Service/PageTypeApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PhotoCategoryApi.php
- M Service/PollAnswerApi.php
- M Service/PollApi.php
- M Service/PollResultApi.php
- M Service/QuizApi.php
- M Service/QuizResultApi.php
- M Service/ReportApi.php
- M Service/ReportReasonApi.php
- M Service/SearchApi.php
- M Service/SubscriptionApi.php
- M Service/TagApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Service/VideoCategoryApi.php
- M Version1_4/Api/Form/GeneralForm.php
- M Version1_4/Api/Form/User/AccountSettingForm.php
- M Version1_4/Api/Form/User/UserRegisterForm.php
- M Version1_4/Api/Resource/ForumAnnouncementResource.php
- M Version1_4/Api/Resource/ForumThreadResource.php
- M Version1_4/Service/AccountApi.php
- M Version1_4/Service/ForumAnnouncementApi.php
- M Version1_4/Service/ForumPostApi.php
- M Version1_4/Service/ForumThreadApi.php
- M Version1_4/Service/UserApi.php
- A assets/images/app-images/no-chat.png
- A assets/images/app-images/no-conversation.png
- A assets/images/app-images/no-item.png
- A assets/images/app-images/no-result.png
- A assets/images/app-images/no-wifi.png
- M changelog.md
- M composer.json
- M hooks/friend.service_request_process_add_end.php
- M hooks/mail.service_process_add.php
- M hooks/notification.service_process_add_end.php
- M hooks/user.component_controller_setting_settitle.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/admincp/add-ad-config-extra.html.php
- M views/controller/admincp/menu-item.html.php

## Version 4.3.3

### Information

- **Release Date:** June 20, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Fix Chat issues
- Fix phrase encode

### Changed Files

- Api/Resource/SearchResource.php
- M Service/CoreApi.php
- M changelog.md
- M Install.php
- M README.md

## Version 4.3.2

### Information

- **Release Date:** June 14, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Bug fixes
- Support upload endpoint for field FileType for 3rd party API

### Changed Files

- M Adapter/MobileApp/MobileApp.php
- M Api/Resource/UserResource.php
- M Service/UserApi.php
- M Service/ApiVersionResolver.php
- M Api/Form/Type/FileType.php
- M Api/Form/Type/VideoUploadType.php
- M changelog.md
- M Install.php
- M README.md

## Version 4.3.1

### Information

- **Release Date:** May 13, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Bug fixes

### Changed Files

- M Version1_4/Service/UserApi.php

## Version 4.3.0

### Information

- **Release Date:** May 13, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Support user subscription
- Compatible with Mobile App 1.4.x
- Bug fixes

### Changed Files

- M Adapter/Localization/LocalizationInterface.php
- M Adapter/Localization/PhpfoxLocalization.php
- M Adapter/MobileApp/MobileApp.php
- M Adapter/MobileApp/Screen.php
- M Adapter/MobileApp/ScreenSetting.php
- M Adapter/Parse/PhpfoxParse.php
- M Adapter/PushNotification/Firebase.php
- M Adapter/PushNotification/PushNotificationInterface.php
- M Api/Exception/ErrorException.php
- A Api/Exception/PaymentRequiredErrorException.php
- A Api/Exception/UnauthorizedErrorException.php
- M Api/Form/Blog/BlogForm.php
- M Api/Form/Event/EventForm.php
- M Api/Form/Event/EventInviteForm.php
- M Api/Form/Event/EventSearchForm.php
- M Api/Form/Feed/FeedPostForm.php
- M Api/Form/Forum/ForumAnnouncementForm.php
- M Api/Form/Forum/ForumForm.php
- M Api/Form/Forum/ForumPostForm.php
- M Api/Form/Forum/ForumThreadForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Group/GroupCategoryForm.php
- M Api/Form/Group/GroupForm.php
- M Api/Form/Group/GroupInfoForm.php
- M Api/Form/Group/GroupInviteForm.php
- M Api/Form/Group/GroupPermissionForm.php
- M Api/Form/Group/GroupPhotoForm.php
- M Api/Form/Group/GroupProfileForm.php
- M Api/Form/Group/GroupTypeForm.php
- M Api/Form/Marketplace/MarketplaceForm.php
- M Api/Form/Marketplace/MarketplaceInviteForm.php
- M Api/Form/Marketplace/MarketplacePhotoForm.php
- M Api/Form/Marketplace/MarketplaceSearchForm.php
- M Api/Form/Music/MusicAlbumForm.php
- M Api/Form/Music/MusicPlaylistForm.php
- M Api/Form/Music/MusicSongForm.php
- M Api/Form/Page/PageCategoryForm.php
- M Api/Form/Page/PageClaimForm.php
- M Api/Form/Page/PageForm.php
- M Api/Form/Page/PageInfoForm.php
- M Api/Form/Page/PageInviteForm.php
- M Api/Form/Page/PagePermissionForm.php
- M Api/Form/Page/PagePhotoForm.php
- M Api/Form/Page/PageProfileForm.php
- M Api/Form/Page/PageTypeForm.php
- M Api/Form/Photo/PhotoAlbumForm.php
- M Api/Form/Photo/PhotoForm.php
- M Api/Form/Poll/PollForm.php
- M Api/Form/Quiz/QuizForm.php
- M Api/Form/SearchForm.php
- A Api/Form/Subscribe/CancelForm.php
- A Api/Form/Subscribe/ChangePackageForm.php
- M Api/Form/Type/CheckboxType.php
- A Api/Form/Type/ClickableType.php
- M Api/Form/Type/CountryStateType.php
- M Api/Form/Type/FileType.php
- M Api/Form/Type/MembershipPackageType.php
- M Api/Form/Type/RadioType.php
- A Api/Form/Type/RangeType.php
- M Api/Form/Type/RelationshipPickerType.php
- M Api/Form/Type/TextareaType.php
- M Api/Form/User/AccountSettingForm.php
- M Api/Form/User/ChangePasswordForm.php
- A Api/Form/User/DeleteAccountForm.php
- M Api/Form/User/EditProfileForm.php
- M Api/Form/User/ForgetPasswordRequest.php
- M Api/Form/User/UserRegisterForm.php
- M Api/Form/User/UserSearchForm.php
- M Api/Form/Validator/Filter/TextFilter.php
- M Api/Form/Validator/StringLengthValidator.php
- M Api/Form/Video/VideoForm.php
- M Api/Resource/BlogCategoryResource.php
- M Api/Resource/BlogResource.php
- M Api/Resource/CommentResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedResource.php
- M Api/Resource/FileResource.php
- M Api/Resource/ForumPostResource.php
- M Api/Resource/ForumResource.php
- M Api/Resource/ForumThreadResource.php
- M Api/Resource/FriendListItemResource.php
- M Api/Resource/FriendListResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/FriendSearchResource.php
- M Api/Resource/GroupCategoryResource.php
- M Api/Resource/GroupMemberResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/GroupTypeResource.php
- M Api/Resource/LikeResource.php
- M Api/Resource/MarketplaceResource.php
- M Api/Resource/MusicAlbumResource.php
- M Api/Resource/MusicGenreResource.php
- M Api/Resource/MusicPlaylistResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/Object/Statistic.php
- M Api/Resource/PageCategoryResource.php
- M Api/Resource/PageMemberResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/PageTypeResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/PhotoCategoryResource.php
- M Api/Resource/PhotoResource.php
- M Api/Resource/QuizResultResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/SearchResource.php
- A Api/Resource/SubscriptionResource.php
- M Api/Resource/TagResource.php
- M Api/Resource/UserPhotoResource.php
- M Api/Resource/UserResource.php
- M Api/Resource/VideoCategoryResource.php
- M Api/Security/AccessControl.php
- M Api/Security/Attachment/AttachmentAccessControl.php
- M Api/Security/Blog/BlogAccessControl.php
- M Api/Security/Comment/CommentAccessControl.php
- M Api/Security/Event/EventAccessControl.php
- M Api/Security/Forum/ForumAnnouncementAccessControl.php
- M Api/Security/Forum/ForumThankAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Security/Group/GroupAccessControl.php
- M Api/Security/GroupsAppContext.php
- M Api/Security/Marketplace/MarketplaceAccessControl.php
- M Api/Security/Music/MusicAlbumAccessControl.php
- M Api/Security/Music/MusicPlaylistAccessControl.php
- M Api/Security/Music/MusicSongAccessControl.php
- M Api/Security/Page/PageAccessControl.php
- M Api/Security/PagesAppContext.php
- M Api/Security/Photo/PhotoAccessControl.php
- M Api/Security/Poll/PollAccessControl.php
- M Api/Security/Quiz/QuizAccessControl.php
- M Api/Security/User/UserAccessControl.php
- M Api/Security/Video/VideoAccessControl.php
- M Install.php
- D Installation/Database/NotificationQueue.php
- A Installation/Version/v421.php
- A Job/PushNotification.php
- M README.md
- M Service/AbstractApi.php
- M Service/AccountApi.php
- M Service/Admincp/MenuService.php
- M Service/Admincp/SettingService.php
- A Service/ApiVersionResolver.php
- M Service/AttachmentApi.php
- M Service/Auth/AuthenticationApi.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/Auth/GrantType/UserPasswordAuth.php
- M Service/Auth/Storage.php
- M Service/BlogApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/Device/DeviceService.php
- M Service/EventApi.php
- M Service/EventInviteApi.php
- M Service/FeedApi.php
- M Service/ForumAnnouncementApi.php
- M Service/ForumApi.php
- M Service/ForumModeratorApi.php
- M Service/ForumPostApi.php
- M Service/ForumThreadApi.php
- M Service/FriendApi.php
- M Service/FriendRequestApi.php
- M Service/FriendTagApi.php
- M Service/GroupAdminApi.php
- M Service/GroupApi.php
- M Service/GroupInfoApi.php
- M Service/GroupInviteApi.php
- M Service/GroupMemberApi.php
- M Service/GroupPermissionApi.php
- M Service/GroupPhotoApi.php
- M Service/GroupProfileApi.php
- M Service/Helper/FeedAttachmentHelper.php
- M Service/Helper/MobileAppHelper.php
- M Service/Helper/ParametersResolver.php
- M Service/LikeApi.php
- M Service/MarketplaceApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/NotificationApi.php
- M Service/PageAdminApi.php
- M Service/PageApi.php
- M Service/PageInfoApi.php
- M Service/PageMemberApi.php
- M Service/PageProfileApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PollApi.php
- M Service/PollResultApi.php
- M Service/QuizApi.php
- A Service/SubscriptionApi.php
- M Service/TagApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- M Service/VideoCategoryApi.php
- A Version1_4/Api/Form/GeneralForm.php
- A Version1_4/Api/Form/User/AccountSettingForm.php
- A Version1_4/Api/Form/User/UserRegisterForm.php
- A Version1_4/Api/Resource/ForumAnnouncementResource.php
- A Version1_4/Api/Resource/ForumThreadResource.php
- A Version1_4/Service/AccountApi.php
- A Version1_4/Service/ForumAnnouncementApi.php
- A Version1_4/Service/ForumPostApi.php
- A Version1_4/Service/ForumThreadApi.php
- A Version1_4/Service/UserApi.php
- M changelog.md
- A hooks/job_queue_init.php
- M installer.php
- M phrase.json
- M start.php


## Version 4.2.0

### Information

- **Release Date:** March 06, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Improvements

- Support AdMobs
- Compatible with Mobile App 1.3.x
- Support 3rd party API

### Changed Files

- M Adapter/MobileApp/MobileApp.php
- M Adapter/MobileApp/MobileAppSettingInterface.php
- A Adapter/MobileApp/ScreenSetting.php
- M Adapter/Parse/PhpfoxParse.php
- M Adapter/PushNotification/Firebase.php
- M Ajax/Ajax.php
- M Api/AbstractResourceApi.php
- M Api/Exception/ErrorException.php
- M Api/Form/Blog/BlogForm.php
- M Api/Form/Event/EventInviteForm.php
- M Api/Form/Forum/ForumThreadSearchForm.php
- M Api/Form/GeneralForm.php
- M Api/Form/Group/GroupInviteForm.php
- M Api/Form/Marketplace/MarketplaceInviteForm.php
- M Api/Form/Page/PageInviteForm.php
- M Api/Form/Poll/PollForm.php
- M Api/Form/Quiz/QuizForm.php
- M Api/Form/Type/AbstractOptionType.php
- M Api/Form/Type/CheckboxType.php
- M Api/Form/Type/FileType.php
- A Api/Form/Type/MembershipPackageType.php
- M Api/Form/Type/MultiFileType.php
- M Api/Form/Type/TextareaType.php
- M Api/Form/User/AccountSettingForm.php
- M Api/Form/User/EditProfileForm.php
- M Api/Form/User/ForgetPasswordRequest.php
- M Api/Form/User/UserRegisterForm.php
- M Api/Form/User/UserSearchForm.php
- M Api/Form/Validator/Filter/TextFilter.php
- M Api/Form/Video/VideoForm.php
- M Api/Resource/AccountResource.php
- A Api/Resource/ActivityPointResource.php
- A Api/Resource/AdResource.php
- M Api/Resource/BlockedUserResource.php
- M Api/Resource/BlogCategoryResource.php
- M Api/Resource/BlogResource.php
- M Api/Resource/EventCategoryResource.php
- M Api/Resource/EventResource.php
- M Api/Resource/FeedEmbed/UserPhoto.php
- M Api/Resource/FeedResource.php
- M Api/Resource/ForumAnnouncementResource.php
- M Api/Resource/ForumPostResource.php
- M Api/Resource/ForumResource.php
- M Api/Resource/ForumThreadResource.php
- A Api/Resource/FriendFeedResource.php
- M Api/Resource/FriendRequestResource.php
- M Api/Resource/FriendResource.php
- M Api/Resource/GroupAdminResource.php
- M Api/Resource/GroupCategoryResource.php
- M Api/Resource/GroupMemberResource.php
- M Api/Resource/GroupResource.php
- M Api/Resource/LikeResource.php
- M Api/Resource/MarketplaceCategoryResource.php
- M Api/Resource/MarketplaceResource.php
- M Api/Resource/MusicAlbumResource.php
- M Api/Resource/MusicGenreResource.php
- M Api/Resource/MusicPlaylistResource.php
- M Api/Resource/MusicSongResource.php
- M Api/Resource/NotificationResource.php
- M Api/Resource/PageAdminResource.php
- M Api/Resource/PageCategoryResource.php
- M Api/Resource/PageMemberResource.php
- M Api/Resource/PageResource.php
- M Api/Resource/PhotoAlbumResource.php
- M Api/Resource/PhotoCategoryResource.php
- M Api/Resource/PhotoResource.php
- M Api/Resource/PollAnswerResource.php
- M Api/Resource/PollResource.php
- M Api/Resource/QuizResource.php
- M Api/Resource/ResourceBase.php
- M Api/Resource/SearchResource.php
- D Api/Resource/UserActivityPointResource.php
- A Api/Resource/UserPhotoResource.php
- M Api/Resource/UserResource.php
- M Api/Resource/VideoCategoryResource.php
- M Api/Resource/VideoResource.php
- M Api/Security/AccessControl.php
- M Api/Security/AppContextFactory.php
- M Api/Security/Forum/ForumAnnouncementAccessControl.php
- M Api/Security/Forum/ForumPostAccessControl.php
- M Api/Security/Forum/ForumThreadAccessControl.php
- M Api/Security/Poll/PollAccessControl.php
- M Api/Security/User/UserAccessControl.php
- A Controller/Admin/AddAdmobConfigController.php
- A Controller/Admin/ManageAdmobConfigController.php
- M Install.php
- A Installation/Database/AdsConfigs.php
- A Installation/Database/AdsConfigsScreen.php
- M README.md
- M Service/AbstractApi.php
- M Service/AccountApi.php
- A Service/AdApi.php
- A Service/Admincp/AdConfigService.php
- M Service/Auth/AuthenticationApi.php
- M Service/Auth/GrantType/FacebookAuth.php
- M Service/Auth/RestApiTransport.php
- M Service/Auth/Storage.php
- M Service/BlogApi.php
- M Service/BlogCategoryApi.php
- M Service/CommentApi.php
- M Service/CoreApi.php
- M Service/Device/DeviceService.php
- M Service/EventApi.php
- M Service/FeedApi.php
- M Service/FileApi.php
- M Service/ForumApi.php
- M Service/ForumPostApi.php
- M Service/ForumThreadApi.php
- M Service/FriendApi.php
- M Service/FriendRequestApi.php
- M Service/GroupApi.php
- M Service/GroupMemberApi.php
- M Service/GroupPermissionApi.php
- M Service/Helper/BrowseHelper.php
- M Service/Helper/FeedAttachmentHelper.php
- M Service/Helper/MobileAppHelper.php
- M Service/Helper/ParametersResolver.php
- M Service/Helper/SearchHelper.php
- M Service/LikeApi.php
- M Service/MarketplaceApi.php
- M Service/MusicAlbumApi.php
- M Service/MusicPlaylistApi.php
- M Service/MusicSongApi.php
- M Service/NameResource.php
- M Service/NotificationApi.php
- M Service/PageApi.php
- M Service/PageMemberApi.php
- M Service/PagePermissionApi.php
- M Service/PhotoAlbumApi.php
- M Service/PhotoApi.php
- M Service/PollApi.php
- M Service/PollResultApi.php
- M Service/QuizApi.php
- M Service/ReportApi.php
- M Service/SearchApi.php
- M Service/UserApi.php
- M Service/VideoApi.php
- A assets/images/default-images/blocked-user/no_image.png
- A assets/images/default-images/blocked-user/no_image_cover.png
- A assets/images/default-images/blog/no_image.png
- A assets/images/default-images/event/no_image.png
- A assets/images/default-images/groups/no_image.png
- A assets/images/default-images/groups/no_image_cover.png
- A assets/images/default-images/marketplace/no_image.png
- A assets/images/default-images/music-album/no_image.png
- A assets/images/default-images/music-playlist/no_image.png
- A assets/images/default-images/music-song/no_image.png
- A assets/images/default-images/pages/no_image.png
- A assets/images/default-images/pages/no_image_cover.png
- A assets/images/default-images/photo-album/no_image.png
- A assets/images/default-images/poll/no_image.png
- A assets/images/default-images/quiz/no_image.png
- A assets/images/default-images/user/no_image.png
- A assets/images/default-images/user/no_image_cover.png
- A assets/images/default-images/video/no_image.png
- A assets/images/sample_ads_layout.jpg
- M assets/jscript/admin.js
- M changelog.md
- M hooks/feed.service_feed_get_start.php
- M phrase.json
- M start.php
- M vendor/symfony/http-foundation/Request.php
- A views/block/admincp/add-ad-config-extra.html.php
- A views/controller/admincp/add-ad-config.html.php
- A views/controller/admincp/manage-ads-config.html.php


## Version 4.1.7

### Information

- **Release Date:** January 30, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Do not push notifications on Mobile.
- Support translating phrases.

## Version 4.1.6

### Information

- **Release Date:** January 24, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Can not sign up when enable Anti-Spam Security Questions.

## Version 4.1.5

### Information

- **Release Date:** January 22, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Fixing broken utf8 encoding

## Version 4.1.4

### Information

- **Release Date:** January 10, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Fix chat on im-node hosting service 

## Version 4.1.3

### Information

- **Release Date:** January 05, 2019
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Phrase fixes and improve some features.

## Version 4.1.2

### Information

- **Release Date:** December 28, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Bug fixes and improve some features.

## Version 4.1.1

### Information

- **Release Date:** December 18, 2018
- **Best Compatibility:** phpFox >= 4.7.0

### Fixed Bugs

- Show blank page when disable any app of Core.

## Version 4.1.0

### Information

- **Release Date:** December 14, 2018
- **Best Compatibility:** phpFox >= 4.7.0
