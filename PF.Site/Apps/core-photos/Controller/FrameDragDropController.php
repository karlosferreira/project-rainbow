<?php

namespace Apps\Core_Photos\Controller;

use Core;
use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class FrameDragDropController extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::isUser() || !Phpfox::getUserParam('photo.can_upload_photos') || !Phpfox::getService('photo')->checkUploadPhotoLimitation() || empty($_FILES['file'])) {
            echo json_encode([
                'errors' => ['message' => _p('cannot_find_the_uploaded_file_please_try_again')]
            ]);
            exit;
        }

        $oServicePhotoProcess = Phpfox::getService('photo.process');

        $aVals = $this->request()->getArray('val');
        if (!empty($aVals['category_id'])) {
            $aVals['category_id'] = explode(',', $aVals['category_id'][0]);
        }
        if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share') {
            $aVals['description'] = $aVals['status_info'];
            $aVals['type_id'] = '1';
        }

        // spam checking start
        $iTimestamp = 0;
        !empty($aVals['timestamp']) && $iTimestamp = $aVals['timestamp'];

        if (($iFlood = Phpfox::getUserParam('photo.flood_control_photos')) !== 0) {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp', // The time stamp field
                    'table'      => Phpfox::getT('photo'), // Database table we plan to check
                    'condition'  => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];

            // actually check if flooding

            if (Phpfox::getLib('spam')->check($aFlood)) {
                $sErrorMessage = _p('uploading_photos_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime();
            }
        }
        // spam checking end

        if (!empty($sErrorMessage)) {
            echo json_encode([
                'errors' => [$sErrorMessage]
            ]);
            exit;
        }

        $aImages = [];
        $aParams = Phpfox::getService('photo')->getUploadParams();
        $aParams['user_id'] = Phpfox::getUserId();
        $aParams['type'] = 'photo';

        $aImage = Phpfox::getService('user.file')->load('file', $aParams);

        if (!$aImage) {
            echo json_encode([
                'errors' => [_p('cannot_find_the_uploaded_photo_please_try_again')]
            ]);
            exit;
        }

        if (!empty($aImage['error'])) {
            echo json_encode([
                'errors' => [$aImage['error']]
            ]);
            exit;
        }

        $bIsEditSchedule = (isset($aVals['is_edit_schedule']) && (int)$aVals['is_edit_schedule'] == 1);
        $bIsSchedule = (isset($aVals['confirm_scheduled']) && (int)$aVals['confirm_scheduled'] == 1) || $bIsEditSchedule;
        $bAddNewAlbum = $bNoFeed = false;

        $sFileName = '';
        $iFileSize = 0;
        $aSize = [];
        if($bIsSchedule) {
            $sFileName = uniqid();
            $aParams['file_name'] = $sFileName;
            $aParams['modify_name'] = true;
            $aParams['no_square'] = true;
            $aParams['thumbnail_sizes'] = Phpfox::getService('photo')->getPhotoPicSizes();
            $aFile = Phpfox::getService('user.file')->upload('file', $aParams, true);
            if (empty($aFile) || !empty($aFile['error'])) {
                if (empty($aFile)) {
                    echo json_encode([
                        'errors' => [_p('cannot_find_the_uploaded_file_please_try_again')]
                    ]);
                    exit;
                }

                if (!empty($aFile['error'])) {
                    echo json_encode([
                        'errors' => [$aFile['error']]
                    ]);
                    exit;
                }
            }
            $sFileName = $aFile['name'];
            $iFileSize = $aFile['size'];

            // Get the current image width/height
            $aSize = getimagesize($aParams['upload_dir'] . sprintf($sFileName, ''));

            $aCallback = ((!empty($aVals['callback_module']) && Phpfox::hasCallback($aVals['callback_module'], 'addPhoto')) ? Phpfox::callback($aVals['callback_module'] . '.addPhoto',
                $aVals['callback_item_id']) : null);
            $sAction = (isset($aVals['action']) ? $aVals['action'] : 'view_photo');
            $sExtra = '';
            if (!empty($aVals['start_year']) && !empty($aVals['start_month']) && !empty($aVals['start_day'])) {
                $sExtra .= '&start_year= ' . $aVals['start_year'] . '&start_month= ' . $aVals['start_month'] . '&start_day= ' . $aVals['start_day'] . '';
            }
            if (!empty($aVals['new_album']) && isset($aVals['album_id']) && $aVals['album_id']) {
                $aNewAlbum = explode(',', $aVals['new_album']);
                if (in_array($aVals['album_id'], $aNewAlbum)) {
                    $bNoFeed = true;
                }
            }
            $out = http_build_query((new Core\Request())->all());
            $sAjaxOut = $out . '&' . ((isset($aVals['page_id']) && !empty($aVals['page_id'])) ? 'is_page=1&' : '') . ((isset($aVals['groups_id']) && !empty($aVals['groups_id'])) ? 'is_page=1&' : '') . 'js_disable_ajax_restart=true' . $sExtra . '&twitter_connection=' . ((isset($aVals['connection']) && isset($aVals['connection']['twitter'])) ? $aVals['connection']['twitter'] : '0') . '&facebook_connection=' . (isset($aVals['connection']['facebook']) ? $aVals['connection']['facebook'] : '0') . '&custom_pages_post_as_page=' . $this->request()->get('custom_pages_post_as_page') . '&action=' . $sAction . '' . (isset($iFeedId) ? '&feed_id=' . $iFeedId : '') . '' . ($aCallback !== null ? '&callback_module=' . $aCallback['module'] . '&callback_item_id=' . $aCallback['item_id'] : '') . '&parent_user_id=' . (isset($aVals['parent_user_id']) ? (int)$aVals['parent_user_id'] : 0) . ((isset($aVals['page_id']) && $aVals['page_id'] > 0) ? '&page_id=' . $aVals['page_id'] : '') . ((isset($aVals['groups_id']) && $aVals['groups_id'] > 0) ? '&groups_id=' . $aVals['groups_id'] : '') . '&timestamp=' . $iTimestamp . (($bNoFeed) ? '&no_feed=1' : '&no_feed=0');
        } else {
            if ($iId = $oServicePhotoProcess->add(Phpfox::getUserId(), array_merge($aVals, $aImage))) {
                $aPhoto = Phpfox::getService('photo')->getForProcess($iId);
                $sFileName = $iId;
                $aParams['file_name'] = $sFileName;
                $aParams['modify_name'] = true;
                $aParams['no_square'] = true;
                $aParams['thumbnail_sizes'] = Phpfox::getService('photo')->getPhotoPicSizes();
                $aFile = Phpfox::getService('user.file')->upload('file', $aParams, true);
                if (empty($aFile) || !empty($aFile['error'])) {
                    $oServicePhotoProcess->delete($iId);
                    if (empty($aFile)) {
                        echo json_encode([
                            'errors' => [_p('cannot_find_the_uploaded_file_please_try_again')]
                        ]);
                        exit;
                    }

                    if (!empty($aFile['error'])) {
                        echo json_encode([
                            'errors' => [$aFile['error']]
                        ]);
                        exit;
                    }
                }
                $sFileName = $aFile['name'];

                // Get the current image width/height
                $aSize = getimagesize($aParams['upload_dir'] . sprintf($sFileName, ''));

                // Update the image with the full path to where it is located.
                $aUpdate = [
                    'destination'    => $sFileName,
                    'width'          => $aSize[0],
                    'height'         => $aSize[1],
                    'server_id'      => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                    'allow_rate'     => (empty($aVals['album_id']) ? '1' : '0'),
                    'description'    => (empty($aVals['description']) ? null : $aVals['description']),
                    'allow_download' => 1
                ];

                if (!empty($aVals['category_id'])) {
                    $aUpdate['category_id'] = $aVals['category_id'];
                }
                $oServicePhotoProcess->update(Phpfox::getUserId(), $iId, $aUpdate);
                $iServerId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');

                $sFile = Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '');
                if (!file_exists($sFile)
                    && !Phpfox::getParam('core.keep_files_in_server')
                ) {
                    if ($iServerId > 0) {
                        $sActualFile = Phpfox::getLib('image.helper')->display([
                                'server_id'  => $iServerId,
                                'path'       => 'photo.url_photo',
                                'file'       => $sFileName,
                                'suffix'     => '',
                                'return_url' => true
                            ]
                        );

                        $aExts = preg_split("/[\/\\.]/", $sActualFile);
                        $iCnt = count($aExts) - 1;
                        $sExt = strtolower($aExts[$iCnt]);

                        $aParts = explode('/', $sFileName);
                        $sFile = Phpfox::getParam('photo.dir_photo') . $aParts[0] . '/' . $aParts[1] . '/' . md5($sFileName) . '.' . $sExt;

                        // Create a temp copy of the original file in local server
                        if (filter_var($sActualFile, FILTER_VALIDATE_URL) !== false) {
                            file_put_contents($sFile, fox_get_contents($sActualFile));
                        } else {
                            copy($sActualFile, $sFile);
                        }

                        //Delete file in local server
                        register_shutdown_function(function () use ($sFile) {
                            @unlink($sFile);
                        });
                    }
                }

                $aImages = [
                    'photo_id'    => $iId,
                    'server_id'   => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                    'destination' => urlencode($sFileName),
                    'name'        => $aImage['name'],
                    'ext'         => $aImage['ext'],
                    'size'        => $aFile['size'],
                    'width'       => $aSize[0],
                    'height'      => $aSize[1],
                    'completed'   => 'true'
                ];

                if ($aFile['size'] < $aImage['size']) {
                    Phpfox::getService('photo.process')->updatePhotoInfo($iId, ['file_size' => $aFile['size']]);
                }

                (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_drag_drop_process_photo')) ? eval($sPlugin) : false);
            }
        }

        if (count($aImages)) {
            $aCallback = ((!empty($aVals['callback_module']) && Phpfox::hasCallback($aVals['callback_module'], 'addPhoto')) ? Phpfox::callback($aVals['callback_module'] . '.addPhoto',
                $aVals['callback_item_id']) : null);
            $sAction = (isset($aVals['action']) ? $aVals['action'] : 'view_photo');

            // Have we posted an album for these set of photos?
            if (isset($aVals['album_id']) && !empty($aVals['album_id'])) {
                // Set the album privacy
                Phpfox::getService('photo.album.process')->setPrivacy($aVals['album_id']);

                // Check if we already have an album cover
                if (isset($aPhoto) && $aPhoto['view_id'] == 0 && !Phpfox::getService('photo.album.process')->hasCover($aVals['album_id']) && isset($iId)) {
                    // Set the album cover
                    Phpfox::getService('photo.album.process')->setCover($aVals['album_id'], $iId);
                }

                // Update the album photo count
                if (!Phpfox::getUserParam('photo.photo_must_be_approved')) {
                    Phpfox::getService('photo.album.process')->updateCounter($aVals['album_id'], 'total_photo', false,
                        count($aImages));
                }
                $sAction = 'view_album';
            }

            (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_drag_drop_process_photos_done')) ? eval($sPlugin) : false);

            $sExtra = '';
            if (!empty($aVals['start_year']) && !empty($aVals['start_month']) && !empty($aVals['start_day'])) {
                $sExtra .= '&start_year= ' . $aVals['start_year'] . '&start_month= ' . $aVals['start_month'] . '&start_day= ' . $aVals['start_day'] . '';
            }
            if (!empty($aVals['new_album']) && isset($aVals['album_id']) && $aVals['album_id']) {
                $aNewAlbum = explode(',', $aVals['new_album']);
                if (in_array($aVals['album_id'], $aNewAlbum)) {
                    $bAddNewAlbum = $bNoFeed = true;
                }
            }
            $out = http_build_query((new Core\Request())->all());
            if ($bIsEditSchedule) {
                $out = $out . '&is_edit_schedule=1&schedule_id='.(int)$aVals['schedule_id'];
            }
            $sAjaxOut = $out . '&' . ((isset($aVals['page_id']) && !empty($aVals['page_id'])) ? 'is_page=1&' : '') . ((isset($aVals['groups_id']) && !empty($aVals['groups_id'])) ? 'is_page=1&' : '') . 'js_disable_ajax_restart=true' . $sExtra . '&twitter_connection=' . ((isset($aVals['connection']) && isset($aVals['connection']['twitter'])) ? $aVals['connection']['twitter'] : '0') . '&facebook_connection=' . (isset($aVals['connection']['facebook']) ? $aVals['connection']['facebook'] : '0') . '&custom_pages_post_as_page=' . $this->request()->get('custom_pages_post_as_page') . '&action=' . $sAction . '' . (isset($iFeedId) ? '&feed_id=' . $iFeedId : '') . '' . ($aCallback !== null ? '&callback_module=' . $aCallback['module'] . '&callback_item_id=' . $aCallback['item_id'] : '') . '&parent_user_id=' . (isset($aVals['parent_user_id']) ? (int)$aVals['parent_user_id'] : 0) . ((isset($aVals['page_id']) && $aVals['page_id'] > 0) ? '&page_id=' . $aVals['page_id'] : '') . ((isset($aVals['groups_id']) && $aVals['groups_id'] > 0) ? '&groups_id=' . $aVals['groups_id'] : '') . '&timestamp=' . $iTimestamp . (($bNoFeed) ? '&no_feed=1' : '&no_feed=0') . ($bAddNewAlbum ? '&new_album=1' : '');

            (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_drag_drop_process_photos_done_javascript')) ? eval($sPlugin) : false);
        }

        $encodeData = [
            'ajax'       => $sAjaxOut,
            'album'      => (!empty($aVals['album_id']) ? $aVals['album_id'] : 0),
            'mass_edit'  => Phpfox::getParam('photo.photo_upload_process', 0),
            'id'         => isset($iId) ? $iId : 0,
            'photo_info' => json_encode($aImages),
            'is_edit_schedule' => (int)$bIsEditSchedule,
            'schedule_id' => $bIsEditSchedule ? $aVals['schedule_id'] : 0
        ];

        if($bIsSchedule) {
            $FileData = [
                'file_image_id'     => PHPFOX_TIME . uniqid(),
                'file_server_id'    => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                'file_name'         => $sFileName,
                'file_image'        => $aImage,
                'file_width_height' => $aSize,
                'file_size'         => $iFileSize,
            ];
            $encodeData['file_data'] = $FileData;
        }
        echo json_encode($encodeData);

        exit;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_drag_drop_clean')) ? eval($sPlugin) : false);
    }
}
