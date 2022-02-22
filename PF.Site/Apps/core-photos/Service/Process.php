<?php

namespace Apps\Core_Photos\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('photo');
    }

    /**
     * Update photo info
     *
     * @param $iPhotoId
     * @param $aUpdate
     *
     * @return bool
     */
    public function updatePhotoInfo($iPhotoId, $aUpdate)
    {
        return db()->update(Phpfox::getT('photo_info'), $aUpdate, 'photo_id = ' . (int)$iPhotoId);
    }

    /**
     * @param $iId
     *
     * @return bool
     */
    public function makeProfilePicture($iId)
    {
        $aPhoto = db()->select('p.destination, p.server_id, p.view_id')
            ->from(Phpfox::getT('photo'), 'p')
            ->where('p.photo_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aPhoto) || !isset($aPhoto['destination'])) {
            return false;
        }

        $sTempName = Phpfox::getLib('image.helper')->display([
            'server_id'  => $aPhoto['server_id'],
            'path'       => 'photo.url_photo',
            'file'       => $aPhoto['destination'],
            'suffix'     => '',
            'return_url' => true
        ]);

        define('PHPFOX_USER_PHOTO_IS_COPY', true);

        $aRet = Phpfox::getService('user.process')->uploadImage(Phpfox::getUserId(), true, $sTempName, false, $iId, $aPhoto['view_id'] == 0);

        if ($sPlugin = Phpfox_Plugin::get('photo.service_process_make_profile_picture__end')) {
            eval($sPlugin);
        }

        return (isset($aRet['user_image']) && !empty($aRet['user_image']));
    }

    /**
     * @param $iId
     *
     * @return bool
     */
    public function makeCoverPicture($iId)
    {
        $aPhoto = db()->select('p.destination, p.server_id, p.view_id, pi.file_name, pi.file_size, pi.mime_type, pi.extension')
            ->from(Phpfox::getT('photo'), 'p')
            ->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->where('p.photo_id = ' . (int)$iId)
            ->executeRow();

        if (empty($aPhoto) || !isset($aPhoto['destination'])) {
            return false;
        }
        $aPhoto['destination'] = str_replace(['{', '}'], '', $aPhoto['destination']);
        $sTempName = Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '');
        if (!file_exists($sTempName)) {
            $sTempName = Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_1024');
        }
        if (!file_exists($sTempName) && $aPhoto['server_id'] > 0) {
            $sActualFile = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aPhoto['server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aPhoto['destination'],
                    'suffix'     => '_1024',
                    'return_url' => true
                ]
            );
            file_put_contents($sTempName, fox_get_contents($sActualFile));
            register_shutdown_function(function () use ($sTempName) {
                @unlink($sTempName);
            });
        }
        $oFile = \Phpfox_File::instance();
        $aImage = [
            'description' => null,
            'type_id'     => 0,
            "name"        => $aPhoto['file_name'],
            'type'        => $aPhoto['mime_type'],
            'size'        => $aPhoto['file_size'],
            'ext'         => $aPhoto['extension'],
            'force_publish' => $aPhoto['view_id'] == 0,
        ];

        if ($iPhotoId = $this->add(Phpfox::getUserId(), $aImage)) {
            // Move the uploaded image and return the full path to that image.
            $sFileName = $oFile->upload($sTempName, Phpfox::getParam('photo.dir_photo'), $iPhotoId);
            // Get the original image file size.
            $iFileSizes = filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
            //Create thumbnail for new cover photo
            $oImage = Phpfox::getLib('image');
            $sFile = Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '');
            list(, $height, ,) = getimagesize($sFile);
            foreach (Phpfox::getService('photo')->getPhotoPicSizes() as $iSize) {
                $oImage->createThumbnail($sFile, Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize), $iSize, $height, true, false);
                $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName,
                        '_' . $iSize));
            }
            // Update the user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'photo', $iFileSizes);

            // Get the current image width/height
            $aSize = getimagesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

            // Update the image with the full path to where it is located.
            $aUpdate = [
                'destination' => $sFileName,
                'width'       => $aSize[0],
                'height'      => $aSize[1],
                'server_id'   => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                'allow_rate'  => 1,
                'description' => null
            ];
            $this->update(Phpfox::getUserId(), $iPhotoId, $aUpdate);

            return Phpfox::getService('user.process')->updateCoverPhoto($iPhotoId, null, $aPhoto['view_id'] == 0);
        }
        return false;
    }

    /**
     * Adding a new photo.
     *
     * @param int     $iUserId        User ID of the user that the photo belongs to.
     * @param array   $aVals          Array of the post data being passed to insert.
     * @param boolean $bIsUpdate      True if we plan to update the entry or false to insert a new entry in the database.
     * @param boolean $bAllowTitleUrl Set to true to allow the editing of the SEO url. remove in v4.6
     *
     * @return int ID of the newly added photo or the ID of the current photo we are editing.
     */
    public function add($iUserId, $aVals, $bIsUpdate = false, $bAllowTitleUrl = false)
    {
        $oParseInput = Phpfox::getLib('parse.input');

        // Create the fields to insert.
        $aFields = [];

        if ($sPlugin = Phpfox_Plugin::get('photo.service_process_add__start')) {
            eval($sPlugin);
        }

        if (isset($aVals['type_id']) && $aVals['type_id'] == 1 && empty($aVals['parent_user_id'])) {
            $iTimelineAlbumId = db()->select('album_id')
                ->from(Phpfox::getT('photo_album'))
                ->where('timeline_id=' . (int)$iUserId)
                ->execute('getSlaveField');
            if (empty($iTimelineAlbumId)) {
                $iTimelineAlbumId = db()->insert(Phpfox::getT('photo_album'), [
                    'privacy'         => Phpfox::getParam('core.friends_only_community') ? (Phpfox::isModule('friend') ? '1' : '3') : '0',
                    'privacy_comment' => '0',
                    'user_id'         => (int)$iUserId,
                    'name'            => "{_p var='timeline_photos'}",
                    'time_stamp'      => isset($aVals['schedule_timestamp']) ? $aVals['schedule_timestamp'] : PHPFOX_TIME,
                    'timeline_id'     => $iUserId,
                    'total_photo'     => 0
                ]);
                db()->insert(Phpfox::getT('photo_album_info'), ['album_id' => $iTimelineAlbumId]);
            }
            db()->update(Phpfox::getT('photo'), ['is_cover' => 0], 'album_id=' . (int)$iTimelineAlbumId);
            db()->updateCounter('photo_album', 'total_photo', 'album_id', $iTimelineAlbumId);
            $aVals['album_id'] = $iTimelineAlbumId;
            $aFields['is_cover'] = 'int';
            $aVals['is_cover'] = 1;
        }

        // Make sure we are updating the album ID
        (!empty($aVals['album_id']) ? $aFields['album_id'] = 'int' : null);

        // Is this an update?
        if ($bIsUpdate) {
            // Make sure we only update the fields that the user is allowed to
            (Phpfox::getUserParam('photo.can_add_mature_images') ? $aFields['mature'] = 'int' : null);
            $aFields['allow_comment'] = 'int';
            $aFields['allow_rate'] = null;
            (!empty($aVals['destination']) ? $aFields[] = 'destination' : null);

            // Check if we really need to update the title
            if (!empty($aVals['title'])) {
                $aFields[] = 'title';

                if (!stristr(PHP_OS, "win")) {
                    $aVals['original_title'] = $aVals['title'];
                }

                // Clean the title for any sneaky attacks
                $aVals['title'] = $oParseInput->clean($aVals['title'], 255);
            }

            $iAlbumId = (int)(empty($aVals['move_to']) ? (isset($aVals['album_id']) ? $aVals['album_id'] : 0) : $aVals['move_to']);

            if (!empty($aVals['set_album_cover'])) {
                $aFields['is_cover'] = 'int';
                $aVals['is_cover'] = '1';

                db()->update(Phpfox::getT('photo'), ['is_cover' => '0'], 'album_id = ' . (int)$iAlbumId);
            }

            $iOldAlbumId = 0;
            $bIsCoverPhoto = false;
            if (!empty($aVals['move_to'])) {
                $aPhoto = Phpfox::getService('photo')->getPhotoItem($aVals['photo_id']);
                if ($aPhoto) {
                    $iOldAlbumId = $aPhoto['album_id'];
                    if ($aPhoto['is_cover'] == 1) {
                        $bIsCoverPhoto = true;
                    }
                }
                $aFields['album_id'] = 'int';
                $aVals['album_id'] = (int)$aVals['move_to'];

                $aAlbum = Phpfox::getService('photo.album')->getForEdit($aVals['move_to']);
                if ($aAlbum) {
                    $aFields['module_id'] = '';
                    $aVals['module_id'] = $aAlbum['module_id'];

                    $aFields['group_id'] = 'int';
                    $aVals['group_id'] = (int)$aAlbum['group_id'];
                }

                if (!isset($aVals['is_cover'])) {
                    $aFields['is_cover'] = 'int';
                    $aVals['is_cover'] = '0';
                }
            }

            if (isset($aVals['privacy'])) {
                $aFields['privacy'] = 'int';
                $aFields['privacy_comment'] = 'int';
            }

            if (!isset($aVals['allow_download'])) {
                $aVals['allow_download'] = 0;
            }
            $aFields['allow_download'] = 'int';
            // Update the data into the database.
            db()->process($aFields, $aVals)->update($this->_sTable, 'photo_id = ' . (int)$aVals['photo_id']);

            // Check if we need to update the description of the photo
            $aFieldsInfo = [
                'description'
            ];

            // Clean the data before we add it into the database
            $aVals['description'] = (empty($aVals['description']) ? null : $this->preParse()->prepare($aVals['description'], false));

            (!empty($aVals['width']) ? $aFieldsInfo[] = 'width' : 0);
            (!empty($aVals['height']) ? $aFieldsInfo[] = 'height' : 0);

            // Check if we have anything to add into the photo_info table
            if (isset($aFieldsInfo)) {
                db()->process($aFieldsInfo, $aVals)->update(Phpfox::getT('photo_info'),
                    'photo_id = ' . (int)$aVals['photo_id']);
            }

            if (!empty($aVals['location'])) {
                $aLocation = [
                    'location_name'   => !empty($aVals['location']['name']) ? Phpfox::getLib('parse.input')->clean($aVals['location']['name']) : null,
                    'location_latlng' => null
                ];
                if ((!empty($aVals['location']['latlng']))) {
                    $aMatch = explode(',', $aVals['location']['latlng']);
                    $aMatch['latitude'] = floatval($aMatch[0]);
                    $aMatch['longitude'] = floatval($aMatch[1]);
                    $aLocation['location_latlng'] = json_encode([
                        'latitude'  => $aMatch['latitude'],
                        'longitude' => $aMatch['longitude']
                    ]);
                }
                db()->update(Phpfox::getT('photo_info'), $aLocation, 'photo_id =' . (int)$aVals['photo_id']);
            }
            // Add tags for the photo
            if (Phpfox::isModule('tag')) {
                if (Phpfox::getParam('tag.enable_hashtag_support')) {
                    Phpfox::getService('tag.process')->update('photo', $aVals['photo_id'], $iUserId,
                        (!empty($aVals['description']) ? $aVals['description'] : null), true);
                }
                if (Phpfox::getParam('tag.enable_tag_support')) {
                    Phpfox::getService('tag.process')->update('photo', $aVals['photo_id'], $iUserId,
                        (!empty($aVals['tag_list']) ? $aVals['tag_list'] : null));
                }
            }

            // Make sure if we plan to add categories for this image that there is something to add
            db()->delete(Phpfox::getT('photo_category_data'), 'photo_id = ' . (int)$aVals['photo_id']);
            if (isset($aVals['category_id']) && count($aVals['category_id'])) {
                if (!is_array($aVals['category_id'])) {
                    $aVals['category_id'] = [$aVals['category_id']];
                }
                // Loop through all the categories

                foreach ($aVals['category_id'] as $iCategory) {
                    // Add each of the categories
                    if ((int)$iCategory) {
                        Phpfox::getService('photo.category.process')->updateForItem($aVals['photo_id'], $iCategory);
                    }
                }
            }

            $iId = $aVals['photo_id'];

            if (Phpfox::isModule('privacy') && isset($aVals['privacy'])) {
                if ($aVals['privacy'] == '4') {
                    Phpfox::getService('privacy.process')->update('photo', $iId,
                        (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
                } else {
                    Phpfox::getService('privacy.process')->delete('photo', $iId);
                }
            }

            if (!empty($iAlbumId)) {
                $aAlbum = Phpfox::getService('photo.album')->getAlbum($iUserId, $iAlbumId, true);
                if (empty($aAlbum['timeline_id'])) {
                    if (!empty($aAlbum['privacy'])) {
                        $aVals['privacy'] = $aAlbum['privacy'];
                    }
                    if (!empty($aAlbum['privacy_comment'])) {
                        $aVals['privacy_comment'] = $aAlbum['privacy_comment'];
                    }
                    $this->database()->update(Phpfox::getT('photo'),
                        [
                            'privacy'         => (!empty($aAlbum['privacy']) ? $aAlbum['privacy'] : 0),
                            'privacy_comment' => (!empty($aAlbum['privacy_comment']) ? $aAlbum['privacy_comment'] : 0),
                            'type_id'         => 0 //Not a timeline photo anymore
                        ],
                        'photo_id = ' . (int)$iId);
                    if (isset($aAlbum['privacy']) && $aAlbum['privacy'] == 4) {
                        $aPrivacy = Phpfox::getService('privacy')->get('photo_album', $aAlbum['album_id']);
                        Phpfox::getService('privacy.process')->delete('photo', $iId);
                        $aList = [];
                        foreach ($aPrivacy as $privacy) {
                            $aList[] = $privacy['friend_list_id'];
                        }
                        Phpfox::getService('privacy.process')->add('photo', $iId, $aList);
                    }
                }
            }

            if (!isset($aVals['privacy'])) {
                $aVals['privacy'] = 0;
            }

            if (!isset($aVals['privacy_comment'])) {
                $aVals['privacy_comment'] = 0;
            }

            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('photo', $iId, $aVals['privacy'],
                $aVals['privacy_comment']) : null);

            if (!empty($aVals['move_to'])) {
                // check before move feed
                $iFeedId = db()->select('feed_id')
                    ->from(':feed')
                    ->where('type_id = \'photo\' AND item_id = ' . (int)$aVals['photo_id'])
                    ->execute('getSlaveField');

                if ($iFeedId) {
                    $iPhotoId = db()->select('photo_id')
                        ->from(':photo_feed')
                        ->where('feed_id = ' . (int)$iFeedId . ' AND feed_time = 0')
                        ->limit(1)
                        ->execute('getSlaveField');
                    if ($iPhotoId) {
                        db()->update(Phpfox::getT('feed'), ['item_id' => $iPhotoId], ['feed_id' => (int)$iFeedId]);
                        // delete the photo feed
                        db()->delete(Phpfox::getT('photo_feed'), 'photo_id = ' . $iPhotoId);
                    } else {
                        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('photo',
                            $aVals['photo_id']) : null);
                    }
                }

                Phpfox::getService('photo.album.process')->updateCounter($aVals['move_to'], 'total_photo');
                if ($iOldAlbumId) {
                    Phpfox::getService('photo.album.process')->updateCounter($iOldAlbumId, 'total_photo', true);
                    if ($bIsCoverPhoto) {
                        $iNewCoverPhotoId = db()->select('photo_id')
                            ->from(Phpfox::getT('photo'))
                            ->where('album_id = ' . (int)$iOldAlbumId)
                            ->order('photo_id DESC')
                            ->execute('getSlaveField');
                        if ($iNewCoverPhotoId) {
                            db()->update(Phpfox::getT('photo'), ['is_cover' => 1],
                                ['photo_id' => (int)$iNewCoverPhotoId]);
                        }
                    }
                }
            }
        } else {
            $callbackModule = !empty($aVals['callback_module']) ? $aVals['callback_module'] : null;

            if (!isset($aVals['privacy'])) {
                if (!empty($callbackModule)) {
                    if (in_array($callbackModule, ['pages', 'groups']) || !Phpfox::hasCallback($callbackModule, 'getDefaultItemPrivacy')) {
                        $aVals['privacy'] = 0;
                    } else {
                        $aVals['privacy'] = Phpfox::callback($callbackModule . '.getDefaultItemPrivacy', [
                            'parent_id' => $aVals['callback_item_id'],
                            'item_type' => 'photo',
                        ]);
                    }
                } else {
                    $aVals['privacy'] = Phpfox::getParam('core.friends_only_community') ? (Phpfox::isModule('friend') ? '1' : '3') : '0';
                }
            }

            if ($callbackModule) {
                $aVals['module_id'] = $callbackModule;
            }

            // Define all the fields we need to enter into the database
            $aFields['user_id'] = 'int';
            $aFields['parent_user_id'] = 'int';
            $aFields['type_id'] = 'int';
            $aFields['allow_download'] = 'int';
            $aFields['time_stamp'] = 'int';
            $aFields['server_id'] = 'int';
            $aFields['view_id'] = 'int';
            $aFields['group_id'] = 'int';
            $aFields[] = 'module_id';
            $aFields[] = 'title';

            if (isset($aVals['privacy'])) {
                $aFields['privacy'] = 'int';
                $aFields['privacy_comment'] = 'int';
            }

            // Define all the fields we need to enter into the photo_info table
            $aFieldsInfo = [
                'photo_id'  => 'int',
                'file_name',
                'mime_type',
                'extension',
                'file_size' => 'int',
                'description',
                'location_name',
                'location_latlng',
                'tagged_friends'
            ];

            $supportedFormats = ['jpg', 'jpeg', 'gif', 'png'];
            $imageObject = \Phpfox_Image::instance();
            if ($imageObject->isSupportNextGenImg()) {
                $supportedFormats = array_merge($supportedFormats, $imageObject->getNextGenImgFormats());
            }
            // Clean and prepare the title and SEO title
            $aVals['title'] = $oParseInput->clean(rtrim(preg_replace("/^(.*?)\.(" . implode('|', $supportedFormats) . ")$/i", "$1",
                rawurldecode($aVals['name']))), 255);

            // Add the user_id
            $aVals['user_id'] = $iUserId;

            // Add the original server ID for LB.
            $aVals['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');

            // Add the time stamp.
            $aVals['time_stamp'] = isset($aVals['schedule_timestamp']) ? $aVals['schedule_timestamp'] : PHPFOX_TIME;

            $aVals['view_id'] = isset($aVals['view_id']) ? $aVals['view_id'] : (empty($aVals['force_publish']) && Phpfox::getUserParam('photo.photo_must_be_approved') ? '1' : '0');
            if (!isset($aVals['allow_download'])) {
                $aVals['allow_download'] = 1;
            }
            // Insert the data into the database.
            $iId = db()->process($aFields, $aVals)->insert($this->_sTable);

            // Prepare the data to enter into the photo_info table
            $aInfo = [
                'photo_id'        => $iId,
                'file_name'       => Phpfox::getLib('parse.input')->clean($aVals['name'], 100),
                'extension'       => strtolower($aVals['ext']),
                'file_size'       => $aVals['size'],
                'mime_type'       => $aVals['type'],
                'description'     => (empty($aVals['description']) ? null : $this->preParse()->prepare($aVals['description'], false)),
                'location_name'   => (!empty($aVals['location']['name'])) ? Phpfox::getLib('parse.input')->clean($aVals['location']['name']) : null,
                'location_latlng' => null,
                'tagged_friends'  => (empty($aVals['tagged_friends']) ? null : $aVals['tagged_friends'])
            ];
            if ((!empty($aVals['location']['latlng']))) {
                $aMatch = explode(',', $aVals['location']['latlng']);
                $aMatch['latitude'] = floatval($aMatch[0]);
                $aMatch['longitude'] = floatval($aMatch[1]);
                $aInfo['location_latlng'] = json_encode([
                    'latitude'  => $aMatch['latitude'],
                    'longitude' => $aMatch['longitude']
                ]);
            }
            // Insert the data into the photo_info table
            db()->process($aFieldsInfo, $aInfo)->insert(Phpfox::getT('photo_info'));

            if (!Phpfox::getUserParam('photo.photo_must_be_approved')) {
                if (empty($aVals['is_cover_photo'])) {
                    // Update user activity
                    Phpfox::getService('user.activity')->update($iUserId, 'photo');
                }
            }

            // Make sure if we plan to add categories for this image that there is something to add
            if (isset($aVals['category_id']) && count($aVals['category_id'])) {
                // Loop thru all the categories
                foreach ($aVals['category_id'] as $iCategory) {
                    // Add each of the categories
                    if ((int)$iCategory) {
                        Phpfox::getService('photo.category.process')->updateForItem($iId, $iCategory);
                    }
                }
            }

            if (isset($aVals['privacy']) && $aVals['privacy'] == '4') {
                Phpfox::getService('privacy.process')->add('photo', $iId,
                    (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
            }

            if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') && !empty($aVals['description'])) {
                Phpfox::getService('tag.process')->add('photo', $iId, $iUserId, $aVals['description'], true);
            }
        }

        // Plugin call
        if ($sPlugin = Phpfox_Plugin::get('photo.service_process_add__end')) {
            eval($sPlugin);
        }

        // Return the photo ID#
        return $iId;
    }

    /**
     * Updating a new photo. We piggy back on the add() method so we don't have to do the same code twice.
     *
     * @param int     $iUserId        User ID of the user that the photo belongs to.
     * @param int     $iId
     * @param array   $aVals          Array of the post data being passed to insert.
     * @param boolean $bAllowTitleUrl Set to true to allow the editing of the SEO url.
     *
     * @return int ID of the newly added photo or the ID of the current photo we are editing.
     */
    public function update($iUserId, $iId, $aVals, $bAllowTitleUrl = false)
    {
        $aVals['photo_id'] = $iId;
        if (Phpfox::getParam('feed.cache_each_feed_entry')) {
            $this->cache()->remove(['feeds', 'photo_' . $iId]);
        }
        return $this->add($iUserId, $aVals, true, $bAllowTitleUrl);
    }

    /**
     * Used to delete a photo.
     *
     * @param int    $iId ID of the photo we want to delete.
     * @param bool   $bPass
     * @param string $sView
     * @param int    $iUserId
     *
     * @return boolean We return true since if nothing fails we were able to delete the image.
     */
    public function delete($iId, $bPass = false, $sView = '', $iUserId = 0)
    {
        // Get the image ID and full path to the image.
        $aPhoto = db()->select('p.user_id, p.module_id, p.group_id, p.is_sponsor, p.is_featured, p.album_id, p.photo_id, p.destination, p.server_id, p.is_cover, p.type_id, p.parent_user_id, p.view_id, pi.location_latlng, pi.location_name, pi.tagged_friends, pi.description')
            ->from($this->_sTable, 'p')
            ->leftJoin(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
            ->where('p.photo_id = ' . (int)$iId)
            ->execute('getSlaveRow');
        if (!isset($aPhoto['user_id'])) {
            return false;
        }
        // check current page to redirect when delete success
        $sParentReturn = true;
        if ($aPhoto['module_id'] == 'pages' && Phpfox::getService('pages')->isAdmin($aPhoto['group_id'])) {
            $sParentReturn = Phpfox::getService('pages')->getUrl($aPhoto['group_id']) . 'photo/';
            $bPass = true; // is owner of page
        } else if ($aPhoto['module_id'] == 'groups' && Phpfox::getService('groups')->isAdmin($aPhoto['group_id'])) {
            $sParentReturn = Phpfox::getService('groups')->getUrl($aPhoto['group_id']) . 'photo/';
            $bPass = true; // is owner of group
        } else if ($aPhoto['type_id'] == 1 && Phpfox::getUserId() == $aPhoto['parent_user_id'] && $aPhoto['parent_user_id'] != 0 && $aPhoto['group_id'] == 0) {
            $sParentReturn = Phpfox::getService('user')->getLink($aPhoto['parent_user_id']);
            $bPass = true; // is owner of wall
        }
        if (!empty($sView)) {
            if ($sView != 'view' && $sView != 'profile') {
                $sParentReturn = Phpfox::getLib('url')->makeUrl('photo', ['view' => $sView]);
            } else if ($sView == 'profile' && $iUserId) {
                $sParentReturn = Phpfox::getService('user')->getLink($iUserId) . 'photo/';
            }
        }

        if ($bPass === false && !Phpfox::getService('user.auth')->hasAccess('photo', 'photo_id', $iId,
                'photo.can_delete_own_photo', 'photo.can_delete_other_photos', $aPhoto['user_id'], false)
        ) {
            return false;
        }

        if (!empty($aPhoto['destination'])) {
            $this->deleteFiles($aPhoto['destination'], $aPhoto['user_id'], $aPhoto['server_id']);
        }

        // Delete this entry from the database
        db()->delete($this->_sTable, 'photo_id = ' . $aPhoto['photo_id']);
        db()->delete(Phpfox::getT('photo_info'), 'photo_id = ' . $aPhoto['photo_id']);
        // delete the photo tags
        db()->delete(Phpfox::getT('photo_tag'), 'photo_id = ' . $aPhoto['photo_id']);
        // delete the category_data
        db()->delete(Phpfox::getT('photo_category_data'), 'photo_id = ' . $aPhoto['photo_id']);

        (($sPlugin = Phpfox_Plugin::get('photo.service_process_delete__1')) ? eval($sPlugin) : false);

        if (Phpfox::isModule('feed')) {
            $feedDisplayCallback = null;
            $rootFeedTable = 'feed';
            $targetFeedTables = ['feed'];
            if (!empty($aPhoto['module_id']) && !empty($aPhoto['group_id']) && Phpfox::hasCallback($aPhoto['module_id'], 'getFeedDisplay')) {
                $feedDisplayCallback = Phpfox::callback($aPhoto['module_id'] . '.getFeedDisplay', $aPhoto['group_id']);
                if (!empty($feedDisplayCallback['table_prefix'])) {
                    $rootFeedTable = $feedDisplayCallback['table_prefix'] . 'feed';
                    $targetFeedTables = array_merge([$rootFeedTable], $targetFeedTables);
                }
            }

            if (!empty($targetFeedTables)) {
                $rootFeedId = $maxPhotoId = null;
                foreach ($targetFeedTables as $feedTable) {
                    $feedId = db()->select('feed_id')
                        ->from(Phpfox::getT($feedTable))
                        ->where([
                            'type_id' => 'photo',
                            'item_id' => $aPhoto['photo_id']
                        ])->executeField(false);

                    if (empty($feedId)) {
                        continue;
                    }

                    if (empty($rootFeedId)) {
                        $rootFeedId = $feedId;
                    }

                    if (empty($maxPhotoId)) {
                        $maxPhotoId = db()->select('MAX(pf.photo_id)')
                            ->from(':photo_feed', 'pf')
                            ->join(':photo', 'p', 'p.photo_id = pf.photo_id')
                            ->where([
                                'pf.feed_id' => $rootFeedId,
                                'pf.feed_table' => $rootFeedTable,
                            ])->executeField(false);
                    }

                    if (!empty($maxPhotoId)) {
                        $feedTableName = Phpfox::getT($feedTable);
                        if (db()->update($feedTableName, ['item_id' => $maxPhotoId], ['feed_id' => $feedId])) {
                            if ($rootFeedTable == $feedTable) {
                                db()->delete(':photo_feed', [
                                    'feed_id' => $rootFeedId,
                                    'feed_table' => $rootFeedTable,
                                    'photo_id' => $maxPhotoId,
                                ]);
                                if (db()->update(':photo_info', [
                                    'location_latlng' => $aPhoto['location_latlng'],
                                    'location_name' => $aPhoto['location_name'],
                                    'tagged_friends' => $aPhoto['tagged_friends'],
                                    'description' => $aPhoto['description'],
                                ], ['photo_id' => $maxPhotoId])) {
                                    db()->update(':feed_tag_data', [
                                        'item_id' => $maxPhotoId,
                                    ], ['item_id' => $aPhoto['photo_id'], 'type_id' => 'photo']);
                                }
                            }
                            db()->update($feedTableName, [
                                'item_id' => $maxPhotoId,
                            ], [
                                'type_id' => 'photo',
                                'item_id' => $iId,
                                'parent_feed_id' => $feedId,
                            ]);
                        }
                    } else {
                        $isMainFeed = $feedTable == 'feed';
                        Phpfox::getService('feed.process')->deleteFeed($feedId, $isMainFeed ? null : $aPhoto['module_id'], $isMainFeed ? 0 : $aPhoto['group_id']);
                    }
                }
            }

            Phpfox::getService('feed.process')->delete('user_photo', $iId);
            Phpfox::getService('feed.process')->delete('user_cover', $iId);
            Phpfox::getService('feed.process')->delete('comment_photo', $iId);
        }

        db()->delete(':photo_feed', ['photo_id' => $iId]);

        //close all sponsorships
        (Phpfox::isAppActive('Core_BetterAds') ? Phpfox::getService('ad.process')->closeSponsorItem('photo', (int)$iId) : null);

        (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem($aPhoto['user_id'], $aPhoto['photo_id'],
            'photo') : null);

        (Phpfox::isModule('tag') ? Phpfox::getService('tag.process')->deleteForItem($aPhoto['user_id'], $iId,
            'photo') : null);

        (Phpfox::isModule('like') ? Phpfox::getService('like.process')->delete('photo', $iId, 0, true) : null);
        (Phpfox::isModule('notification') ? Phpfox::getService('notification.process')->deleteAllOfItem([
            'photo_like',
            'photo_approved',
            'photo_feed_profile',
            'photo_tag',
            'photo_feed_tag'
        ], (int)$iId) : null);

        if ((int)$aPhoto['view_id'] == 0) {
            Phpfox::getService('user.activity')->update($aPhoto['user_id'], 'photo', '-');
        } else {
            $this->_removePendingProfileAndCoverCaches($aPhoto);
        }

        if ($aPhoto['album_id'] > 0) {
            Phpfox::getService('photo.album.process')->updateCounter($aPhoto['album_id'], 'total_photo', true);
        }

        //if deleting photo is cover, set other photo to cover
        if (isset($aPhoto['is_cover']) && $aPhoto['is_cover'] && isset($aPhoto['album_id']) && $aPhoto['album_id']) {
            //Select random photo from this album
            $iNewCoverPhotoId = db()->select('photo_id')
                ->from(':photo')
                ->where('album_id = ' . (int)$aPhoto['album_id'] . ' AND view_id = 0')
                ->order('photo_id DESC')
                ->execute('getSlaveField');
            if ($iNewCoverPhotoId) {
                db()->update(':photo', ['is_cover' => 1], 'photo_id=' . (int)$iNewCoverPhotoId);
            }
        }

        if (empty($aPhoto['group_id'])) {
            //delete user profile photo
            $iAvatarId = ((Phpfox::isUser()) ? storage()->get('user/avatar/' . $aPhoto['user_id']) : null);
            if ($iAvatarId) {
                $iAvatarId = $iAvatarId->value;
            }
            if ($iAvatarId && $iAvatarId == $iId) {
                Phpfox::getService('user.process')->removeProfilePic($aPhoto['user_id']);
                storage()->del('user/avatar/' . $aPhoto['user_id']);
            }

            //delete user cover photo
            $iCoverId = ((Phpfox::isUser()) ? storage()->get('user/cover/' . $aPhoto['user_id']) : null);
            if ($iCoverId) {
                $iCoverId = $iCoverId->value;
            }
            if ($iCoverId && $iCoverId == $iId) {
                Phpfox::getService('user.process')->removeLogo($aPhoto['user_id']);
                storage()->del('user/cover/' . $aPhoto['user_id']);
            }
        }

        if ($aPhoto['module_id'] && $aPhoto['group_id'] && Phpfox::hasCallback($aPhoto['module_id'], 'onDeletePhoto')) {
            Phpfox::callback($aPhoto['module_id'] . '.onDeletePhoto', $aPhoto);
        }

        $repositionCacheObject = storage()->get('photo_cover_reposition_' . $aPhoto['photo_id']);
        if (is_object($repositionCacheObject)) {
            storage()->del('photo_cover_reposition_' . $aPhoto['photo_id']);
        }

        if ($aPhoto['is_sponsor'] == 1) {
            $this->cache()->remove('photo_sponsored');
        }
        if ($aPhoto['is_featured'] == 1) {
            $this->cache()->remove('photo_featured');
        }

        return $sParentReturn;
    }

    /**
     * Update the photo counters.
     *
     * @param int     $iId      ID# of the photo
     * @param string  $sCounter Field we plan to update
     * @param boolean $bMinus   True increases to the count and false decreases the count
     */
    public function updateCounter($iId, $sCounter, $bMinus = false)
    {
        db()->update($this->_sTable, [
            $sCounter => ['= ' . $sCounter . ' ' . ($bMinus ? '-' : '+'), 1]
        ], 'photo_id = ' . (int)$iId
        );
    }

    public function approve($iId, $iTimeStamp = 0)
    {
        $aPhoto = db()->select('p.*, pi.description, pi.tagged_friends, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'p')
            ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.photo_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aPhoto['photo_id'])) {
            return false;
        }
        if ($aPhoto['view_id'] == '0') {
            return true;
        }

        $aCallback = ((!empty($aPhoto['module_id']) && Phpfox::hasCallback($aPhoto['module_id'], 'addPhoto')) ? Phpfox::callback($aPhoto['module_id'] . '.addPhoto',
            $aPhoto['photo_id']) : null);

        db()->update($this->_sTable, ['view_id' => 0, 'time_stamp' => PHPFOX_TIME],
            'photo_id = ' . $aPhoto['photo_id']);

        if ($aPhoto['album_id'] > 0) {
            Phpfox::getService('photo.album.process')->updateCounter($aPhoto['album_id'], 'total_photo');
            // Check if we already have an album cover
            if (!Phpfox::getService('photo.album.process')->hasCover($aPhoto['album_id'])) {
                // Set the album cover
                Phpfox::getService('photo.album.process')->setCover($aPhoto['album_id'], $iId);
            }
        }

        $iFeedId = 0;
        $bIgnoreAddPhotoFeed = false;
        $bCanAddFeed = true;
        $bCanUpdateUserActivity = true;
        $bHasAlreadyProcessTaggedFriends = false;

        if (!empty($ignoredFeedCache = storage()->get('photo_no_feed_' . $aPhoto['photo_id'])) && !empty($ignoredFeedCache->value)) {
            $bCanUpdateUserActivity = $bCanAddFeed = false;
            storage()->del('photo_no_feed_' . $aPhoto['photo_id']);
        }

        if ($bCanAddFeed) {
            if ($iTimeStamp && !empty($_SESSION['approve_photo_feed_' . $aPhoto['user_id'] . '_' . $aPhoto['album_id'] . '_' . $iTimeStamp])) {
                $iFeedId = $_SESSION['approve_photo_feed_' . $aPhoto['user_id'] . '_' . $aPhoto['album_id'] . '_' . $iTimeStamp];
                $bHasAlreadyProcessTaggedFriends = true;
            } elseif (empty($iTimeStamp)) {
                $iFeedId = db()->select('feed_id')
                    ->from(':photo_feed')
                    ->where([
                        'photo_id' => $aPhoto['photo_id'],
                        'AND feed_id > 0 AND feed_time > 0'
                    ])->executeField(false);
                $bHasAlreadyProcessTaggedFriends = $bIgnoreAddPhotoFeed = !!$iFeedId;
            }

            if (!$bIgnoreAddPhotoFeed) {
                if (!empty($iFeedId)) {
                    db()->insert(Phpfox::getT('photo_feed'), [
                            'feed_id'    => $iFeedId,
                            'photo_id'   => $aPhoto['photo_id'],
                            'feed_table' => (empty($aCallback['table_prefix']) ? 'feed' : $aCallback['table_prefix'] . 'feed')
                        ]
                    );
                } else {
                    $sFeedType = $this->_getFeedType($aPhoto);
                    $bIsSpecialFeedType = in_array($sFeedType, $this->_getFeedTypeForIgnoredPhotoFeed());

                    if ($bIsSpecialFeedType) {
                        $aTypeArray = explode('_', $sFeedType);
                        $sTypeModuleId = !empty($aTypeArray) ? array_shift($aTypeArray) : null;
                        if (Phpfox::isModule($sTypeModuleId)) {
                            $sSubType = implode('_', $aTypeArray);
                            if (in_array($sSubType, ['cover', 'cover_photo'])) {
                                $callbackName = 'approveCoverPhoto';
                            } elseif (in_array($sSubType, ['photo'])) {
                                $callbackName = 'approveProfilePhoto';
                            }
                            if (!empty($callbackName) && Phpfox::hasCallback($sTypeModuleId, $callbackName)) {
                                $bCanUpdateUserActivity = false;
                                Phpfox::callback($sTypeModuleId . '.' . $callbackName, $aPhoto['photo_id']);
                            }
                        }
                    } elseif (Phpfox::isModule('feed') && Phpfox::getParam('photo.photo_allow_create_feed_when_add_new_item', 1)) {
                        $postedUserId = $aPhoto['user_id'];
                        $feedProcessService = Phpfox::getService('feed.process');
                        $parentUserId = !empty($aPhoto['group_id']) ? $aPhoto['group_id'] : (!empty($aPhoto['parent_user_id']) ? $aPhoto['parent_user_id'] : 0);
                        $iFeedId = $feedProcessService->callback($aCallback)->add($sFeedType,
                            $aPhoto['photo_id'], $aPhoto['privacy'], $aPhoto['privacy_comment'],
                            $parentUserId, $postedUserId);
                        if ($aCallback && !empty($iLoopFeedId = $feedProcessService->getLoopFeedId())) {
                            storage()->set('photo_parent_feed_' . $iLoopFeedId, $iFeedId);
                            $feedProcessService->resetLoopFeedId();
                        }
                    }

                    if (!$bIsSpecialFeedType && $iTimeStamp) {
                        $_SESSION['approve_photo_feed_' . $aPhoto['user_id'] . '_' . $aPhoto['album_id'] . '_' . $iTimeStamp] = $iFeedId;
                    }
                }
            } elseif (!empty($iFeedId)) {
                db()->update(':photo_feed', ['feed_time' => 0], ['photo_id' => $aPhoto['photo_id'], 'feed_id' => $iFeedId]);
            }

            if (empty($iTimeStamp) && $iFeedId && !$bIgnoreAddPhotoFeed) {
                db()->select('photo_id, feed_time')
                    ->from(':photo_feed')
                    ->where([
                        'feed_id' => 0,
                        'photo_id' => $aPhoto['photo_id'],
                        'AND feed_time <> 0'
                    ])->union()->unionFrom('main_photo');
                $feedPhotos = db()->select('main_photo.photo_id AS main_photo_id, sub_photo.photo_id AS temp_photo_id, sub_photo.feed_time')
                    ->leftJoin(':photo_feed', 'sub_photo', 'sub_photo.feed_time = main_photo.feed_time AND sub_photo.feed_id = 0 AND sub_photo.feed_time > 0 AND sub_photo.photo_id <> ' . $aPhoto['photo_id'])
                    ->executeRows(false);
                if (!empty($feedPhotos)) {
                    db()->delete(':photo_feed', ['feed_id' => 0, 'photo_id' => $aPhoto['photo_id']]);
                    if (!empty($subPhotoIds = array_column($feedPhotos, 'temp_photo_id'))) {
                        db()->update(':photo_feed', [
                            'feed_id' => $iFeedId,
                        ], ['photo_id' => ['in' => implode(',', $subPhotoIds)]]);
                    }
                }
            }
        }

        if ($bCanUpdateUserActivity && empty($aPhoto['is_cover_photo']) && empty($aPhoto['is_profile_photo'])) {
            Phpfox::getService('user.activity')->update($aPhoto['user_id'], 'photo');
        }

        if (Phpfox::isModule('notification')) {
            Phpfox::getService('notification.process')->add('photo_approved', $aPhoto['photo_id'], $aPhoto['user_id']);
        }

        // notification to tagged and mentioned friends
        if (!$bHasAlreadyProcessTaggedFriends) {
            $this->notifyTaggedInFeed($aPhoto['description'], $aPhoto['photo_id'], $aPhoto['user_id'], $iFeedId, $aPhoto['tagged_friends'], $aPhoto['privacy'], $aPhoto['parent_user_id'], $aPhoto['module_id']);

            if ($aPhoto['module_id'] && Phpfox::isModule('notification') && Phpfox::isModule($aPhoto['module_id']) && Phpfox::hasCallback($aPhoto['module_id'], 'addItemNotification')) {
                Phpfox::callback($aPhoto['module_id'] . '.addItemNotification', [
                    'page_id'      => $aPhoto['group_id'],
                    'item_perm'    => 'photo.view_browse_photos',
                    'item_type'    => 'photo',
                    'item_id'      => $aPhoto['photo_id'],
                    'owner_id'     => $aPhoto['user_id'],
                    'items_phrase' => 'photos__l'
                ]);
            }
        }

        $bCanUseTitle = Phpfox::getParam('photo.photo_show_title', 1);
        $sLink = Phpfox::permalink('photo', $aPhoto['photo_id'], $bCanUseTitle ? $aPhoto['title'] : null);

        (($sPlugin = Phpfox_Plugin::get('photo.service_process_approve__1')) ? eval($sPlugin) : false);

        Phpfox::getLib('mail')->to($aPhoto['user_id'])
            ->subject([$bCanUseTitle ? 'photo.your_photo_title_has_been_approved' : 'photo_your_photo_has_been_approved', ['title' => $aPhoto['title']]])
            ->message([$bCanUseTitle ? 'your_photo_has_been_approved_message' : 'photo_your_photo_without_title_has_been_approved_message', ['sLink' => $sLink, 'title' => $aPhoto['title']]])
            ->notification('photo.email_notification')
            ->send();

        return true;
    }

    public function addPhotoFeedForPending($photoIds, $feedTable = 'feed', $itemSectionNumber = 20)
    {
        if (empty($photoIds)) {
            return false;
        }

        $photoIds = array_unique($photoIds);
        $timeStamp = PHPFOX_TIME;
        $table = Phpfox::getT('photo_feed');

        foreach (array_chunk($photoIds, $itemSectionNumber) as $photoItemIds) {
            if (empty($photoItemIds)) {
                continue;
            }
            $insert = [];
            foreach($photoItemIds as $photoItemId) {
                $insert[] = [
                    'feed_id' => 0,
                    'photo_id' => $photoItemId,
                    'feed_table' => $feedTable,
                    'feed_time' => $timeStamp,
                ];
            }
            db()->multiInsert($table, ['feed_id', 'photo_id', 'feed_table', 'feed_time'], $insert);
        }
    }

    private function _getFeedTypeForIgnoredPhotoFeed()
    {
        $types = ['user_photo', 'user_cover', 'pages_photo', 'groups_photo', 'groups_cover_photo', 'pages_cover_photo'];

        ($sPlugin = Phpfox_Plugin::get('photo.service_process_getfeedtypeforignoredphotofeed')) ? eval($sPlugin) : null;

        return $types;
    }

    private function _getFeedType($aPhoto)
    {
        $feedType = 'photo';
        $isProfilePhoto = null;

        if (!empty($pendingCache = storage()->get('user_profile_photo_pending_' . $aPhoto['photo_id'])) && !empty($pendingCache->value->album_id)) {
            $isProfilePhoto = true;
        } elseif (!empty($pendingCache = storage()->get('user_cover_photo_pending_' . $aPhoto['photo_id'])) && !empty($pendingCache->value->album_id)) {
            $isProfilePhoto = false;
        } elseif (in_array($aPhoto['module_id'], ['pages', 'groups']) && !empty($pendingCache = storage()->get($aPhoto['module_id'] . '_cover_photo_pending_' . $aPhoto['group_id'])) && !empty($pendingCache->value->album_id)) {
            $isProfilePhoto = false;
        }

        if (isset($isProfilePhoto)) {
            if (empty($aPhoto['module_id']) && empty($aPhoto['group_id'])) {
                $feedType = $isProfilePhoto ? 'user_photo' : 'user_cover';
            } elseif (in_array($aPhoto['module_id'], ['pages', 'groups']) && !empty($aPhoto['group_id'])) {
                if ($aPhoto['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages')) {
                    $feedType = $isProfilePhoto ? 'pages_photo' : 'pages_cover_photo';
                } elseif ($aPhoto['module_id'] == 'groups' && Phpfox::isAppActive('PHPfox_Groups')) {
                    $feedType = $isProfilePhoto ? 'groups_photo' : 'groups_cover_photo';
                }
            }
        } else {
            if (!empty($aPhoto['is_profile_photo'])) {
                if (empty($aPhoto['module_id']) && empty($aPhoto['group_id'])) {
                    $feedType = 'user_photo';
                } elseif (!empty($aPhoto['group_id'])) {
                    switch ($aPhoto['module_id']) {
                        case 'pages':
                            if (Phpfox::isAppActive('Core_Pages')) {
                                $feedType = 'pages_photo';
                            }
                            break;
                        case 'groups':
                            if (Phpfox::isAppActive('PHPfox_Groups')) {
                                $feedType = 'groups_photo';
                            }
                            break;
                    }
                }
            } elseif (!empty($aPhoto['is_cover_photo']) && empty($aPhoto['module_id']) && empty($aPhoto['group_id'])) {
                $feedType = 'user_cover';
            } elseif (!empty($aPhoto['module_id']) && !empty($aPhoto['group_id'])
                && in_array($aPhoto['module_id'], ['groups', 'pages'])
                && ($aPhoto['module_id'] == 'pages' && Phpfox::isAppActive('Core_Pages')) || ($aPhoto['module_id'] == 'groups' && Phpfox::isAppActive('PHPfox_Groups'))) {
                $isCover = !empty($aPhoto['is_cover_photo']);
                if (!$isCover) {
                    $isCover = db()->select('page_id')
                        ->from(':pages')
                        ->where([
                            'page_id' => $aPhoto['group_id'],
                            'cover_photo_id' => $aPhoto['photo_id']
                        ])->executeField(false);
                }
                if ($isCover) {
                    $feedType = $aPhoto['module_id'] == 'pages' ? 'pages_cover_photo' : 'groups_cover_photo';
                }
            }
        }

        ($sPlugin = Phpfox_Plugin::get('photo.service_process_getfeedtype')) ? eval($sPlugin) : null;

        return $feedType;
    }

    public function feature($iId, $sType)
    {
        return db()->update($this->_sTable, ['is_featured' => ($sType == '1' ? 1 : 0)], 'view_id = 0 AND photo_id = ' . (int)$iId);
    }

    public function sponsor($iId, $sType)
    {
        if (!Phpfox::getUserParam('photo.can_sponsor_photo') && !Phpfox::getUserParam('photo.can_purchase_sponsor') && !defined('PHPFOX_API_CALLBACK')) {
            return Phpfox_Error::set(_p('hack_attempt'));
        }

        $iType = (int)$sType;
        if ($iType != 0 && $iType != 1) {
            return false;
        }
        db()->update($this->_sTable, ['is_sponsor' => $iType], 'photo_id = ' . (int)$iId);
        if ($sPlugin = Phpfox_Plugin::get('photo.service_process_sponsor__end')) {
            eval($sPlugin);
        }
        return true;
    }

    public function rotate($iId, $sCmd)
    {
        $aPhoto = db()->select('p.user_id, p.title, p.photo_id, p.destination, p.server_id, pi.extension')
            ->from($this->_sTable, 'p')
            ->leftJoin(':photo_info', 'pi', 'pi.photo_id = p.photo_id')
            ->where(['p.photo_id' => (int)$iId])
            ->execute('getSlaveRow');

        if (!isset($aPhoto['photo_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_photo_you_plan_to_edit'));
        }

        if (($aPhoto['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('photo.can_edit_own_photo')) || Phpfox::getUserParam('photo.can_edit_other_photo')) {
            if (!Phpfox::getService('photo')->canRotate($aPhoto['extension'])) {
                return $aPhoto;
            }

            $aSizes = Phpfox::getService('photo')->getPhotoPicSizes();
            $aSizes[] = '';
            $aParts = explode('/', $aPhoto['destination']);
            $sParts = '';
            if (is_array($aParts)) {
                foreach ($aParts as $sPart) {
                    if (!empty($sPart)) {
                        if (!preg_match('/jpg|gif|png|jpeg/i', $sPart)) {
                            $sParts .= $sPart . '/';
                        }
                    }
                }
            }

            foreach ($aSizes as $iSize) {
                $sFile = Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'],
                        (empty($iSize) ? '' : '_') . $iSize);
                if (file_exists($sFile) || $aPhoto['server_id'] > 0) {
                    $sActualFile = Phpfox::getLib('image.helper')->display([
                            'server_id'  => $aPhoto['server_id'],
                            'path'       => 'photo.url_photo',
                            'file'       => $aPhoto['destination'],
                            'suffix'     => (empty($iSize) ? '' : '_') . $iSize,
                            'return_url' => true
                        ]
                    );

                    $aExts = preg_split("/[\/\\.]/", $sActualFile);
                    $iCnt = count($aExts) - 1;
                    $sExt = strtolower($aExts[$iCnt]);

                    $sFile = Phpfox::getParam('photo.dir_photo') . $sParts . md5($aPhoto['destination']) . (empty($iSize) ? '' : '_') . $iSize . '.' . $sExt;

                    // fix issue allow_url_fopen = Off
                    file_put_contents($sFile, fox_get_contents($sActualFile));
                    Phpfox::getLib('image')->rotate($sFile, $sCmd, null, $aPhoto['server_id']);
                } else {
                    $sExt = '';
                }


                db()->update(Phpfox::getT('photo'),
                    ['destination' => $sParts . md5($aPhoto['destination']) . '%s.' . $sExt],
                    'photo_id = ' . (int)$aPhoto['photo_id']);
            }

            return $aPhoto;
        }

        return false;
    }

    public function massProcess($aAlbum, $aVals)
    {
        foreach ($aVals as $iPhotoId => $aVal) {
            if (isset($aVals['set_album_cover']) && is_numeric($iPhotoId)) {
                if ($aVals['set_album_cover'] == $iPhotoId && empty($aVal['move_to'])) {
                    db()->update(Phpfox::getT('photo'), ['is_cover' => '1'],
                        "album_id = $aAlbum[album_id] AND photo_id = $iPhotoId");
                } else {
                    db()->update(Phpfox::getT('photo'), ['is_cover' => '0'],
                        "album_id = $aAlbum[album_id] AND photo_id = $iPhotoId");
                }
            }
            if (!is_numeric($iPhotoId)) {
                continue;
            }

            if (isset($aVal['delete_photo'])) {
                if (!$this->delete($iPhotoId)) {
                    return false;
                }

                continue;
            }

            $this->update($aAlbum['user_id'], $iPhotoId, $aVal);
        }

        // if no photo in album is set cover, select first
        $aPhotos = db()->select('*')->from($this->_sTable)->where(['album_id' => $aAlbum['album_id']])->executeRows();
        if (count($aPhotos)) {
            $bNoCover = true;
            foreach ($aPhotos as $aPhoto) {
                if ($aPhoto['is_cover']) {
                    $bNoCover = false;
                    break;
                }
            }
            if ($bNoCover) {
                $iPhotoId = db()->select('photo_id')->from($this->_sTable)->where(['album_id' => $aAlbum['album_id']])->executeField();
                db()->update($this->_sTable, ['is_cover' => '1'], "photo_id=$iPhotoId");
            }
        }
        return true;
    }

    /**
     * @param        $sContent
     * @param        $iItemId
     * @param        $iOwnerId
     * @param int    $iFeedId
     * @param string $taggedFriends
     * @param int    $iPrivacy
     * @param int    $iParentUserId
     * @param string $moduleId
     * @return bool
     */
    public function notifyTaggedInFeed($sContent, $iItemId, $iOwnerId, $iFeedId = 0, $taggedFriends = '', $iPrivacy = 0, $iParentUserId = 0, $moduleId = '')
    {
        if (!Phpfox::isModule('feed') || !$iFeedId) {
            return false;
        }
        // notification to tagged and mentioned friends
        $aTagged = [];
        if (!empty($taggedFriends)) {
            $aTagged = explode(',', $taggedFriends);
        }
        Phpfox::getService('feed.tag')->updateFeedTaggedUsers([
            'feed_type'      => 'photo',
            'content'        => $sContent,
            'owner_id'       => $iOwnerId,
            'privacy'        => $iPrivacy,
            'tagged_friend'  => $aTagged,
            'item_id'        => $iItemId,
            'feed_id'        => $iFeedId,
            'parent_user_id' => $iParentUserId,
            'module_id'      => $moduleId
        ]);
        return true;
    }


    /**
     * @param string $sName
     * @param null   $iUserId
     * @param int    $iServerId
     *
     * @return bool
     */
    public function deleteFiles($sName = '', $iUserId = null, $iServerId = 0)
    {
        if (empty($sName)) {
            return false;
        }

        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }

        $aParams = Phpfox::getService('photo')->getUploadParams();
        $aParams['type'] = 'photo';
        $aParams['path'] = $sName;
        $aParams['user_id'] = $iUserId;
        $aParams['update_space'] = ($iUserId ? true : false);
        $aParams['server_id'] = $iServerId;
        $aParams['thumbnail_sizes'] = Phpfox::getService('photo')->getPhotoPicSizes();

        return Phpfox::getService('user.file')->remove($aParams);
    }

    /**
     * Remove temporary photos after expired time
     *
     * @param int $iExpiredTime
     */
    public function removeTemporaryPhotos($iExpiredTime = 86400)
    {
        $aAllPhotos = db()->select('photo_id, time_stamp')->from(':photo')->where(['is_temp' => 1])->executeRows();

        foreach ($aAllPhotos as $aPhoto) {
            if (time() - $aPhoto['time_stamp'] < $iExpiredTime) {
                continue;
            }
            // delete temporary photo older than one day
            Phpfox::getService('photo.process')->delete($aPhoto['photo_id']);
        }
    }

    private function _removePendingProfileAndCoverCaches($aPhoto)
    {
        if (empty($aPhoto['photo_id']) || $aPhoto['view_id'] != 1) {
            return false;
        }

        storage()->del('user_profile_photo_pending_' . $aPhoto['photo_id']);
        storage()->del('photo_pending_feed_' . $aPhoto['photo_id']);

        if (empty($aPhoto['module_id']) && empty($aPhoto['group_id'])) {
            storage()->del('user_cover_photo_pending_' . $aPhoto['photo_id']);
        } elseif (!empty($aPhoto['group_id']) && in_array($aPhoto['module_id'], ['pages', 'groups'])) {
            storage()->del($aPhoto['module_id'] . '_profile_photo_pending_' . $aPhoto['group_id']);
            storage()->del($aPhoto['module_id'] . '_cover_photo_pending_' . $aPhoto['group_id']);
        }

        ($sPlugin = Phpfox_Plugin::get('photo.service_process__removependingprofileandcovercaches'));
    }

    public function deleteScheduleImage($aTempImage, $iScheduleId, $bUpdateSchedule = false)
    {
        $aSchedule = Phpfox::getService('core.schedule')->getScheduleItem($iScheduleId);
        if (empty($aSchedule)) {
            Phpfox_Error::set(_p('this_scheduled_item_not_exist'));
        }

        $data = unserialize($aSchedule['data']);
        $aParams = Phpfox::getService('photo')->getUploadParams();
        $aParams['type'] = 'photo';
        $aParams['thumbnail_sizes'] = Phpfox::getService('photo')->getPhotoPicSizes();;
        $aParams['path'] = $aTempImage['file_name'];
        $aParams['user_id'] = $aSchedule['user_id'];
        $aParams['update_space'] = ($aSchedule['user_id'] ? true : false);
        $aParams['server_id'] = $aTempImage['file_server_id'];

        if (Phpfox::getService('user.file')->remove($aParams)) {
            if ($bUpdateSchedule) {
                $aFileData = $data['aFileData'];
                array_splice($aFileData, array_search($aTempImage['file_image_id'], array_column($aFileData, 'file_image_id')), 1);
                $data['aFileData'] = $aFileData;
                db()->update(':schedule', ['data' => serialize($data)], ['schedule_id' => $iScheduleId]);
            }
        } else {
            return false;
        }
        return true;
    }

    public function updateSchedulePhotoCount($iUserId, $iTotalPhotos, $isMinus = false)
    {
        if (!$iUserId) {
            return false;
        }
        $sCacheId = 'photo/schedule/' . $iUserId;
        $aCurrentSchedule = storage()->get($sCacheId);
        $iCurrentScheduleCount = 0;
        if (!empty($aCurrentSchedule->value)) {
            $iCurrentScheduleCount = (int)$aCurrentSchedule->value;
        }
        storage()->del($sCacheId);
        if ($isMinus) {
            storage()->set($sCacheId, $iCurrentScheduleCount > $iTotalPhotos ? ($iCurrentScheduleCount - (int)$iTotalPhotos) : 0);
        } else {
            storage()->set($sCacheId, $iCurrentScheduleCount + (int)$iTotalPhotos);
        }
        return true;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('photo.service_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}