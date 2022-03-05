# Saved Items :: Change Log

## Version 4.1.1

### Information

- **Release Date:** September 23, 2021
- **Best Compatibility:** phpFox >= 4.7.2

### Bugs fixed ###
- Web - Does not update collection thumb after save playlist
- Web - Statistic menu - Count wrong number of save items on each app
- API - Collections list - Missing the last item is saved on mobile app
- API - Does not show thumb of all save items
- API - Search result page - Event/Marketplace item - Missing default photo when item no have photo
- API - Auto add unopened icon again after view save item the second time
- API - Layout issues

### Improvements ###

- Web - Compatible with php 8.0
- Web - Add privacy for Collection
- Web - Add contributor
- Web - Prevent XSS attack
- API - Collection detail - Do not support the status read/unread a item
- API - Support can clickable on the save item

### Changed files ###

- M       Ajax/Ajax.php
- A       Api/Form/SavedItemsAddToCollectionForm.php
- A       Api/Form/SavedItemsCollectionForm.php
- A       Api/Form/SavedItemsSearchForm.php
- A       Api/Resource/SavedItemsCollectionResource.php
- A       Api/Resource/SavedItemsResource.php
- A       Api/Security/SavedItemsAccessControll.php
- A       Api/Security/SavedItemsCollectionAccessControl.php
- M       Block/AddCollectionBlock.php
- M       Block/CategoryBlock.php
- A       Block/Collection/AddCollectionPopup.php
- A       Block/Collection/AddFriendPopup.php
- A       Block/Collection/FriendListPopup.php
- M       Block/Collection/RecentUpdateBlock.php
- A       Block/ListingUser.php
- A       Block/OpenConfirmationPopup.php
- M       Controller/Admin/IndexController.php
- A       Controller/AllCollectionsController.php
- M       Controller/CollectionsController.php
- M       Controller/IndexController.php
- A       Controller/ProfileController.php
- M       Install.php
- M       Installation/Database/Collection.php
- M       Installation/Database/CollectionData.php
- A       Installation/Database/CollectionFriend.php
- M       Installation/Database/Saved_Items.php
- A       Installation/Version/v411.php
- M       README.md
- A       Service/Api/SavedItemsApi.php
- A       Service/Api/SavedItemsCollectionApi.php
- M       Service/Callback.php
- M       Service/Collection/Browse.php
- M       Service/Collection/Collection.php
- M       Service/Collection/Process.php
- A       Service/Friend/Friend.php
- A       Service/Friend/Process.php
- M       Service/Process.php
- M       Service/SavedItems.php
- M       assets/autoload.js
- M       assets/images/collection-empty-image.png
- M       assets/images/default-collection-photo.png
- M       assets/main.less
- M       changelog.md
- A       hooks/core.template_block_notification_dropdown_menu.php
- M       hooks/feed.template_block_entry_2.php
- A       hooks/mobile.api_resource_base_generate_array_end.php
- A       hooks/mobile.core_api_get_app_settings.php
- A       hooks/mobile.service_core_api_site_settings_no_cache.php
- A       hooks/mobile.service_coreapi_mobilePhrases.php
- A       hooks/mobile_api_routing_registration.php
- M       hooks/template_getheader.php
- M       hooks/template_gettemplatefile.php
- M       icon.png
- A       installer.php
- M       phrase.json
- M       start.php
- M       views/block/category.html.php
- A       views/block/collection/add-collection-popup.html.php
- A       views/block/collection/add-friend-popup.html.php
- M       views/block/collection/add-to-collection.html.php
- M       views/block/collection/form.html.php
- A       views/block/collection/friend-list-popup.html.php
- M       views/block/collection/item-entry.html.php
- M       views/block/collection/link.html.php
- M       views/block/collection/list.html.php
- M       views/block/collection/quick-checkbox.html.php
- M       views/block/collection/quick-form.html.php
- M       views/block/collection/recent-update.html.php
- M       views/block/item-entry.html.php
- A       views/block/listing-user.html.php
- A       views/block/open-confirmation-popup.html.php
- M       views/block/save-action.html.php
- M       views/block/saved-alert.html.php
- A       views/controller/all-collections.html.php
- M       views/controller/collections.html.php
- M       views/controller/index.html.php
- A       views/mobile-templates/create-collection.jsx
- A       views/mobile-templates/saved-item-collection-block.jsx
- A       views/mobile-templates/saved-item-collection.jsx
- A       views/mobile-templates/saved-item.jsx


## Version 4.1.0

### Information

- **Release Date:** June 17, 2019
- **Best Compatibility:** phpFox >= 4.7.2
