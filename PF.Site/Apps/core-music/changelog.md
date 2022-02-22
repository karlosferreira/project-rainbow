# Music :: Change Log

## Version 4.6.7

### Information

- **Release Date:** July 15, 2021
- **Best Compatibility:** phpFox >= 4.7.8

### Improvements

- Compatible with PHP 8.0 and phpFox 4.8.6
- Add a new option to allow users to create new albums when adding songs [#862](https://github.com/PHPfox-Official/phpfox-v4-feature-requests/issues/862)
- Warning message if users want to leave site when they are adding a music song/album/playlist
- Limitation on number of song/album/playlist created by user group
- Add new settings to allow Admins review and update all email contents of Music app

### Fixed Bugs

- Upload multiple songs, in some cases, their feed only shows one song
- Some other minor bugs

### Changed Files

- M Ajax/Ajax.php
- M Block/AddToPlaylistBlock.php
- M Block/ListBlock.php
- M Block/OtherPlaylistBlock.php
- M Block/PlaylistFeedBlock.php
- M Block/TrackBlock.php
- M Controller/AlbumController.php
- M Controller/Browse/AlbumController.php
- M Controller/Browse/PlaylistController.php
- M Controller/FrameController.php
- M Controller/IndexController.php
- M Controller/PlaylistController.php
- M Controller/UploadController.php
- M Controller/ViewAlbumController.php
- M Controller/ViewPlaylistController.php
- M Install.php
- A Installation/Version/v467.php
- M README.md
- M Service/Album/Album.php
- M Service/Album/Process.php
- M Service/Callback.php
- M Service/Genre/Genre.php
- M Service/Music.php
- M Service/Playlist/Browse.php
- M Service/Playlist/Playlist.php
- M Service/Playlist/Process.php
- M Service/Process.php
- M assets/autoload.js
- M assets/main.less
- R changelog.md    change-log.md
- M installer.php
- M phrase.json
- M views/block/add-to-playlist.html.php
- M views/block/album-rows.html.php
- M views/block/mini-entry.html.php
- M views/block/playlist-rows.html.php
- M views/block/upload.html.php
- M views/controller/album.html.php
- M views/controller/playlist.html.php
- M views/controller/upload.html.php
- M views/controller/view-album.html.php
- M views/controller/view-playlist.html.php
- M views/controller/view.html.php

## Version 4.6.6

### Information

- **Release Date:** November 26, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Fixed Bugs

- Music App setting >registered users> can delete all songs ON for default please change [#2825](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2825)

### Changed Files

- M Ajax/Ajax.php
- M Block/AddToPlaylistBlock.php
- M Block/AlbumRowsBlock.php
- M Block/FeaturedAlbumBlock.php
- M Block/FeaturedBlock.php
- M Block/ListBlock.php
- M Block/NewAlbumBlock.php
- M Block/OtherPlaylistBlock.php
- M Block/PlaylistFeedBlock.php
- M Block/RelatedAlbumBlock.php
- M Block/RowsBlock.php
- M Block/SongBlock.php
- M Block/SponsoredAlbumBlock.php
- M Block/SponsoredSongBlock.php
- M Block/SuggestionBlock.php
- M Block/TrackBlock.php
- M Block/UploadBlock.php
- M Block/UserPlaylistBlock.php
- M Controller/Admin/AddController.php
- M Controller/Admin/DeleteController.php
- M Controller/AlbumController.php
- M Controller/Browse/AlbumController.php
- M Controller/Browse/PlaylistController.php
- M Controller/FrameController.php
- M Controller/IndexController.php
- M Controller/PlaylistController.php
- M Controller/ProfileController.php
- M Controller/UploadController.php
- M Controller/ViewAlbumController.php
- M Controller/ViewController.php
- M Controller/ViewPlaylistController.php
- M Install.php
- M Installation/Database/Music_Album.php
- M Installation/Database/Music_Album_Text.php
- M Installation/Database/Music_Feed.php
- M Installation/Database/Music_Genre.php
- M Installation/Database/Music_Genre_Data.php
- M Installation/Database/Music_Playlist.php
- M Installation/Database/Music_Playlist_Data.php
- M Installation/Database/Music_Profile.php
- M Installation/Database/Music_Song.php
- M Installation/Version/v453.php
- M Installation/Version/v463.php
- M Service/Album/Album.php
- M Service/Album/Browse.php
- M Service/Album/Process.php
- M Service/Browse.php
- M Service/Callback.php
- M Service/Genre/Genre.php
- M Service/Genre/Process.php
- M Service/Music.php
- M Service/Playlist/Playlist.php
- M Service/Playlist/Process.php
- M Service/Process.php
- M Service/Song/Browse.php
- M Service/Song/Process.php
- M assets/autoload.js
- M assets/main.less
- M hooks/template_template_getmenu_3.php
- M hooks/validator.admincp_user_settings_music.php
- M installer.php
- M phrase.json
- M start.php
- M views/block/add-to-playlist.html.php
- M views/block/album-rows.html.php
- M views/block/playlist-feed.html.php
- M views/block/playlist-rows.html.php
- M views/controller/album.html.php
- M views/controller/upload.html.php
- M views/controller/view-album.html.php
- M views/controller/view-playlist.html.php
- M views/controller/view.html.php


## Version 4.6.5

### Information

- **Release Date:** September 05, 2019
- **Best Compatibility:** phpFox >= 4.7.8

### Fixed Bugs

- Phrase in ad app / music sponsor setting needs correction / also settings are not relevant to selected apps [#2752](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2752)

### Changed Files

- M Block/FeaturedAlbumBlock.php
- M Block/ListBlock.php
- M Block/SponsoredAlbumBlock.php
- M Controller/IndexController.php
- M Install.php
- M Service/Album/Album.php
- M Service/Callback.php
- M Service/Music.php
- M Service/Process.php
- M assets/autoload.js
- M phrase.json
- M views/block/album-rows.html.php
- M views/block/playlist-feed.html.php


## Version 4.6.4

### Information

- **Release Date:** April 27, 2019
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- User cannot edit own playlists.
- Share song page - Upload button change wrong color when hover.
- Does not show photo for music playlist [#2496](https://github.com/PHPfox-Official/phpfox-v4-issues/issues/2496)

### Changed Files

- M Block/UploadBlock.php
- M Controller/AlbumController.php
- M Controller/Browse/AlbumController.php
- M Controller/IndexController.php
- M Controller/UploadController.php
- M Controller/ViewPlaylistController.php
- M Install.php
- M README.md
- M Service/Album/Album.php
- M Service/Callback.php
- M Service/Music.php
- M Service/Playlist/Playlist.php
- M Service/Playlist/Process.php
- M Service/Process.php
- M assets/main.less
- M change-log.md
- M hooks/template_template_getmenu_3.php
- M phrase.json
- M views/block/playlist-feed.html.php
- M views/block/playlist-rows.html.php
- M views/block/upload.html.php
- M views/controller/upload.html.php


## Version 4.6.3

### Information

- **Release Date:** December 18, 2018
- **Best Compatibility:** phpFox >= 4.6.1

### Fixed Bugs

- Playlist/Album detail - Missing title and top-padding.
- Responsive - Add song to playlist from Feed popup be cut.
- Group Detail - Breadcrumb list missed "Music" when go to album detail.

### Improvements

- Add block genre list on location 1 when filter by genre.
- Remove sponsor in feed and item if login as page.

### Changed Files

- M Controller/IndexController.php
- M Controller/ViewAlbumController.php
- M Controller/ViewPlaylistController.php
- M Install.php
- A Installation/Version/v463.php
- M Service/Album/Album.php
- M Service/Callback.php
- M Service/Music.php
- M Service/Playlist/Playlist.php
- M Service/Playlist/Process.php
- M Service/Process.php
- M assets/main.less
- M hooks/admincp.service_maintain_delete_files_get_list.php
- M installer.php
- M start.php
- M views/block/upload.html.php
- M views/controller/upload.html.php
- M views/controller/view-album.html.php
- M views/controller/view.html.php

## Version 4.6.2

### Information

- **Release Date:** October 18, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Can re-sponsor an pending song/album.
- Show duplicate on sponsored block when re-sponsor a denied item.
- Manage sponsorships: Click number not change when click on a sponsored feed.
- Block Manager in ACP: Missing phrase.
- New music app add to playlist on mobile device hidden.
- Admin can not Sponsor In Feed a song of other user.

### Changed Files

- M Ajax/Ajax.php
- M Block/RowsBlock.php
- M Install.php
- M README.md
- M Service/Album/Album.php
- M Service/Album/Process.php
- M Service/Callback.php
- M Service/Genre/Genre.php
- M Service/Genre/Process.php
- M Service/Music.php
- M Service/Process.php
- M assets/main.less
- M change-log.md
- M phrase.json
- M start.php
- M views/block/mini-feed-entry.html.php
- M Ajax/Ajax.php
- M Block/RowsBlock.php
- M Install.php
- M README.md
- M Service/Album/Album.php
- M Service/Album/Process.php
- M Service/Callback.php
- M Service/Genre/Genre.php
- M Service/Genre/Process.php
- M Service/Music.php
- M Service/Process.php
- M assets/main.less
- M change-log.md
- M phrase.json
- M start.php
- M views/block/mini-feed-entry.html.php

## Version 4.6.1

### Information

- **Release Date:** October 01, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Show error page when disable feed module
- Not show exactly profile picture after adding songs in All songs, My songs, Friend's songs
- Clicked number in manage sponsorships not change after click sponsor item in sponsored block.
- Site does not working if disable page app
- Response ajax link wrong when un-sponsor	
- Show Add a playlist button when disable "Can add new playlist?" setting
- Layout issue in Album and Playlist Detail in IE, Firefox
- Not show success message popup when a user sponsor an other user's song
- Upload new song: Publish song while not click on finish yet
- Playlist: Not have drop-down action like album
- Playlists: Show wrong title for "All playlists" page
- Search: Switch to All playlists tab while searching in My playlists
- Guests can not see All playlists
- Not show success message popup when a user sponsor/un-sponsor an other user's album

### Improvements

- Add new setting to disallow/allow app to post on Main feed when add new item. (default is allow)
- All playlists/My playlists - Should show information like album

### New Features

- Ability to save tracks to own playlist
- Add All Playlist and allow comment on playlist detail

### Changed Files

- M  Ajax/Ajax.php
- M Block/RowsBlock.php
- M Service/Album/Album.php
- M Service/Album/Process.php
- M Service/Callback.php
- M Service/Genre/Genre.php
- M Service/Genre/Process.php
- M Service/Music.php
- M Service/Process.php
- M assets/main.less
- M phrase.json
- M start.php
- M views/block/mini-feed-entry.html.php

## Version 4.6.0

### Information

- **Release Date:** January 09, 2018
- **Best Compatibility:** phpFox >= 4.6.0

### Fixed Bugs

- Upload new songs: show unknown error when upload an over size file than the limit of server.
- Song privacy: user can play a privacy song in album.
- Some layout issues with right-to-left language.

### Improvements

- Support search albums in global search.
- Support CKEditor for description fields of songs/albums.
- Support drag/drop, preview, progress bar when users upload songs, photos.
- Allow admin can change default photos of songs and albums.
- Validate all settings, user group settings, and block settings.
- Improve layout for all pages and blocks.

### Changed Files

- Ajax/Ajax.php
- Block/AlbumRowsBlock.php
- Block/FeaturedAlbumBlock.php
- Block/FeaturedBlock.php
- Block/GenreBlock.php
- Block/ListBlock.php
- Block/NewAlbumBlock.php
- Block/RelatedAlbumBlock.php
- Block/SongBlock.php
- Block/SponsoredAlbumBlock.php
- Block/SponsoredSongBlock.php
- Block/SuggestionBlock.php
- Block/UploadBlock.php
- Controller/Admin/IndexController.php
- Controller/AlbumController.php
- Controller/Browse/AlbumController.php
- Controller/DownloadController.php
- Controller/FrameController.php
- Controller/IndexController.php
- Controller/UploadController.php
- Controller/ViewController.php
- Install.php
- Installation/Database/Music_Album.php
- Installation/Database/Music_Song.php
- Service/Album/Album.php
- Service/Album/Process.php
- Service/Callback.php
- Service/Genre/Genre.php
- Service/Genre/Process.php
- Service/Music.php
- Service/Process.php
- assets/autoload.css
- assets/autoload.js
- assets/image/music_v02.png
- assets/image/nophoto_song.png
- assets/image/song_detail_bg.png
- assets/jscript/mediaelementplayer/mediaelement-and-player.js
- assets/main.less
- hooks/bundle__start.php
- hooks/user.template_block_setting_form.php
- hooks/validator.admincp_user_settings_music.php
- phrase.json
- start.php
- views/block/album-rows.html.php
- views/block/featured-album.html.php
- views/block/featured.html.php
- views/block/genre.html.php
- views/block/list.html.php
- views/block/menu-album.html.php
- views/block/menu.html.php
- views/block/mini-album.html.php
- views/block/mini-entry.html.php
- views/block/mini-feed-entry.html.php
- views/block/mini.html.php
- views/block/new-album.html.php
- views/block/related-album.html.php
- views/block/rows.html.php
- views/block/song-genres.html.php
- views/block/song.html.php
- views/block/sponsored-album.html.php
- views/block/sponsored-song.html.php
- views/block/suggestion.html.php
- views/block/track-entry.html.php
- views/block/track.html.php
- views/block/upload-photo.html.php
- views/block/upload.html.php
- views/controller/admincp/add.html.php
- views/controller/album.html.php
- views/controller/browse/album.html.php
- views/controller/index.html
- views/controller/index.html.php
- views/controller/upload.html.php
- views/controller/view-album.html.php
- views/controller/view.html.php

### Removed Blocks

| ID | Block | Reason |
| --- | -------- | ---- |
| 1 | music.genre | Don't use anymore |

### New User Group Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | music.can_download_songs | Can download songs? | Admin can control which user groups have permission to down load songs |


## Version 4.5.3p1

### Information

- **Release Date:** September 29, 2017
- **Best Compatibility:** phpFox >= 4.5.3

### Fixed Bugs

- Process bar is not working when upload songs
- Doesn't show item actions in listing page if the viewer doesn't have mass permission

### Changed Files

- assets/autoload.js
- assets/main.less

## Version 4.5.3

### Information

- **Release Date:** September 19, 2017
- **Best Compatibility:** phpFox >= 4.5.3

### Removed Settings

| ID | Var name | Name | Reason |
| --- | -------- | ---- | --- |
| 1 | music_enable_mass_uploader | Enable mass uploader | Don't use anymore |
| 2 | sponsored_songs_to_show | How Many Sponsor Songs To Show | Don't use anymore |

### New Settings

| ID | Var name | Name | Description |
| --- | -------- | ---- | ---- |
| 1 | music_paging_mode | Pagination Style | Select Pagination Style at Search Page. |
| 2 | music_display_music_created_in_group | Display music which created in Group to the Music app | Enable to display all public music created in Group to the Music app. Disable to hide them. |
| 3 | music_display_music_created_in_page | Display music which created in Page to the Music app | Enable to display all public music created in Page to the Music app. Disable to hide them. |
| 4 | music_meta_description | Music Meta Description | Meta description added to pages related to the Music app. |
| 5 | music_meta_keywords | Music Meta Keywords | Meta keywords that will be displayed on sections related to the Music app. |
| 6 | max_songs_per_upload | Maximum number of songs per upload | Define the maximum number of songs a user can upload each time they use the upload form. Notice: This setting does not control how many songs a user can upload in total, just how many they can upload each time they use the upload form to upload new songs. |

### Deprecated Functions

| ID | Class Name | Function Name | Will Remove In | Reason |
| --- | -------- | ---- | ---- | ---- |
| 1 | Apps\Core_Music\Service\Callback | getFavoriteSong | 4.6.0 | Don't use anymore |
| 2 | Apps\Core_Music\Service\Callback | getFavoriteAlbum | 4.6.0 | Don't use anymore |
| 3 | Apps\Core_Music\Service\Album\Album | getLatestAlbums | 4.6.0 | Don't use anymore |
| 4 | Apps\Core_Music\Service\Genre\Genre | getUserGenre | 4.6.0 | Don't use anymore |

### Deprecated Blocks

| ID | Block | Will Remove In | Reason |
| --- | -------- | ---- | ---- |
| 1 | music.genre | 4.6.0 | Don't use anymore |

### New Blocks

| ID | Block | Name | Description |
| --- | -------- | ---- | ------------ |
| 1 | music.suggestion | Suggestion | Suggest songs have same genres with viewing song. |
| 2 | music.related-album | Related Albums | Show other albums have same owner with viewing album. |
