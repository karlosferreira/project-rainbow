<?php

namespace Apps\Core_Photos\Controller;

use Core;
use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class FrameController extends Phpfox_Component
{
    public function process()
    {
        // We only allow users the ability to upload images.
        if (!Phpfox::isUser()) {
            exit;
        }

        if (isset($_REQUEST['picup'])) {
            $_FILES['Filedata'] = $_FILES['image'];
            unset($_FILES['image']);
        }
        if (isset($_FILES['Filedata']) && !isset($_FILES['image'])) // photo.enable_mass_uploader == true
        {
            $_FILES['image'] = [];
            $_FILES['image']['error']['image'] = UPLOAD_ERR_OK;
            $_FILES['image']['name']['image'] = $_FILES['Filedata']['name'];
            $_FILES['image']['type']['image'] = $_FILES['Filedata']['type'];
            $_FILES['image']['tmp_name']['image'] = $_FILES['Filedata']['tmp_name'];
            $_FILES['image']['size']['image'] = $_FILES['Filedata']['size'];
        }

        $fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);
        if ($fn) {
            define('PHPFOX_HTML5_PHOTO_UPLOAD', true);

            if (isset($_FILES['ajax_upload'])) {
                $_FILES['image'] = [];
                foreach ($_FILES['ajax_upload'] as $key => $value) {
                    $_FILES['image'][$key][0] = $value;
                }
            } else {
                $sHTML5TempFile = PHPFOX_DIR_CACHE . 'image_' . md5(PHPFOX_DIR_CACHE . $fn . uniqid());

                file_put_contents(
                    $sHTML5TempFile,
                    file_get_contents('php://input')
                );
                $_FILES['image'] = [
                    'name'     => [$fn],
                    'type'     => ['image/jpeg'],
                    'tmp_name' => [$sHTML5TempFile],
                    'error'    => [0],
                    'size'     => [filesize($sHTML5TempFile)]
                ];
            }
        }

        // If no images were uploaded lets get out of here.
        if (!isset($_FILES['image']) && empty($tempCoverPhotoId = $this->request()->get('temp_file_id'))) {
            exit;
        }

        $aVals = $this->request()->get('val');

        // Make sure the user group is actually allowed to upload an image
        if (!Phpfox::getUserParam('photo.can_upload_photos') || (empty($aVals['is_cover_photo']) && !Phpfox::getService('photo')->checkUploadPhotoLimitation())) {
            exit;
        }

        $oFile = \Phpfox_File::instance();
        if (defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
            parse_str($_SERVER['HTTP_X_POST_FORM'], $aVals);
            $aVals = (isset($aVals['val'])) ? $aVals['val'] : [];
        }
        if (!is_array($aVals)) {
            $aVals = [];
        }

        $bIsInline = false;
        if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share') {
            $bIsInline = true;
        }

        $iTimestamp = 0;
        !empty($aVals['timestamp']) && $iTimestamp = $aVals['timestamp'];

        if (($iFlood = Phpfox::getUserParam('photo.flood_control_photos')) !== 0) {
            if (!Phpfox::getLib('session')->get('photo_flood') || Phpfox::getLib('session')->get('photo_flood') != $iTimestamp) {
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
                    Phpfox_Error::set(_p('uploading_photos_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                } else {
                    Phpfox::getLib('session')->set('photo_flood', $iTimestamp);
                }
            }

            $oFile = \Phpfox_File::instance();
            $aVals = $this->request()->get('val');
            if (defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
                parse_str($_SERVER['HTTP_X_POST_FORM'], $aVals);
                $aVals = (isset($aVals['val'])) ? $aVals['val'] : [];
            }
            if (!is_array($aVals)) {
                $aVals = [];
            }

            $bIsInline = false;
            if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share') {
                $bIsInline = true;
            }

            if (!Phpfox_Error::isPassed()) {
                // Output JavaScript
                if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
                    echo '<script type="text/javascript">';
                }

                if (!$bIsInline) {
                    echo '$(\'#js_progress_cache_holder\').hide();';
                    echo '$(\'#js_photo_form_holder\').show();';
                    echo '$(\'#js_upload_error_message\').html(\'<div class="error_message">' . implode('',
                            Phpfox_Error::get()) . '</div>\');';
                    echo '$(\'.js_tmp_upload_bar\').remove();';
                    echo '$Core.loadInit();';
                } else {
                    if (isset($aVals['is_cover_photo'])) {
                        echo '$(\'#js_cover_photo_iframe_loader_error\').html(\'<div class="error_message">' . implode('',
                                Phpfox_Error::get()) . '</div>\').show();';
                    } else {
                        echo 'window.parent.$Core.resetActivityFeedError(\'' . implode('<br/>',
                                Phpfox_Error::get()) . '\');';
                    }
                }
                if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
                    echo '</script>';
                }
                exit;
            }
        }

        $aImages = [];
        $iFileSizes = 0;
        $iCnt = 0;

        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_start')) ? eval($sPlugin) : false);

        if (!empty($aVals['album_id'])) {
            Phpfox::getService('photo.album')->getAlbum(Phpfox::getUserId(), $aVals['album_id'], true);
        }

        if (isset($_REQUEST['status_info']) && !empty($_REQUEST['status_info'])) {
            $aVals['description'] = $_REQUEST['status_info'];
        }

        $bUploadFail = false;
        $aUploadFailMessages = [];
        $sErrorMessage = '';
        $bNoFeed = false;
        $sFileName = null;

        if (Phpfox::isAppActive('Core_Pages') && !empty($aVals['page_id'])) {
            $iUserId = Phpfox::getService('pages')->getUserId($aVals['page_id']);
        } else if (Phpfox::isAppActive('PHPfox_Groups') && !empty($aVals['groups_id'])) {
            $iUserId = Phpfox::getService('groups')->getUserId($aVals['groups_id']);
        } else {
            $iUserId = Phpfox::getUserId();
        }

        if (!empty($tempCoverPhotoId) && !empty($aVals['is_cover_photo']) && !empty($aVals['action']) && $aVals['action'] == 'upload_photo_via_share') {
            $tempCoverPhoto = Phpfox::getService('core.temp-file')->getByFields($tempCoverPhotoId, 'file_id, path, server_id');
            if (empty($tempCoverPhoto['path'])) {
                exit;
            }
            $aImage = $this->request()->get('temp_file_info');
            list($imageInfo, $iId) = $this->_processUploadFile($iUserId, $aVals, $iCnt, $bNoFeed, $iFileSizes, $sFileName, $aImage, null, Phpfox::getParam('photo.dir_photo') . sprintf($tempCoverPhoto['path'], ''));
            if (!empty($imageInfo)) {
                $aImages[] = $imageInfo;
                (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_process_photo')) ? eval($sPlugin) : false);
            }
            @register_shutdown_function(function() use($tempCoverPhoto) {
                Phpfox::getService('core.temp-file')->delete($tempCoverPhoto['file_id'], true);
            });
        } else {
            foreach ($_FILES['image']['error'] as $iKey => $sError) {
                if ($sError == UPLOAD_ERR_OK) {
                    if ($aImage = $oFile->load('image[' . $iKey . ']', [
                        'jpg',
                        'gif',
                        'png'
                    ],
                        (Phpfox::getUserParam('photo.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('photo.photo_max_upload_size') / 1024)))
                    ) {
                        list($imageInfo, $iId) = $this->_processUploadFile($iUserId, $aVals, $iCnt, $bNoFeed,$iFileSizes, $sFileName, $aImage, $iKey);
                        if (!empty($imageInfo)) {
                            $aImages[] = $imageInfo;
                            (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_process_photo')) ? eval($sPlugin) : false);
                        }
                    } else {
                        if ($bIsInline && empty($aVals['is_cover_photo'])) {
                            $bUploadFail = true;
                            $sMessage = _p('photo_name', ['name' => $_FILES['image']['name'][$iKey]]);
                            $sErrorMessage = (Phpfox_Error::isPassed() ? _p('upload_failed') : implode(", ",
                                Phpfox_Error::get()));
                            $sMessage .= ': ' . $sErrorMessage;
                            Phpfox_Error::reset();
                            $aUploadFailMessages[] = $sMessage;
                        }
                    }
                } else {
                    switch ($sError) {
                        case UPLOAD_ERR_INI_SIZE:
                            $sErrorMessage = _p('the_uploaded_file_exceeds_the_upload_max_filesize_max_file_size_directive_in_php_ini',
                                ['upload_max_filesize' => ini_get('upload_max_filesize')]);
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $sErrorMessage = "the_uploaded_file_exceeds_the_MAX_FILE_SIZE_directive_that_was_specified_in_the_HTML_form";
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $sErrorMessage = "the_uploaded_file_was_only_partially_uploaded";
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $sErrorMessage = "no_file_was_uploaded";
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $sErrorMessage = "missing_a_temporary_folder";
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $sErrorMessage = "failed_to_write_file_to_disk";
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $sErrorMessage = "file_upload_stopped_by_extension";
                            break;

                        default:
                            $sErrorMessage = "unknown_upload_error";
                            break;
                    }
                    $sErrorMessage = _p('upload_failed') . '. ' . _p($sErrorMessage);
                    $bUploadFail = true;
                    $sMessage = _p('photo_name', ['name' => $_FILES['image']['name'][$iKey]]);
                    $sMessage .= ': ' . $sErrorMessage;
                    Phpfox_Error::reset();
                    $aUploadFailMessages[] = $sMessage;
                }
            }
        }

        $iFeedId = 0;

        // Make sure we were able to upload some images
        if (count($aImages)) {
            if (defined('PHPFOX_IS_HOSTED_SCRIPT')) {
                unlink(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
            }
            $aCallback = ((!empty($aVals['callback_module']) && Phpfox::hasCallback($aVals['callback_module'], 'addPhoto')) ? Phpfox::callback($aVals['callback_module'] . '.addPhoto',
                $aVals['callback_item_id']) : null);
            $sAction = (isset($aVals['action']) ? $aVals['action'] : 'view_photo');

            // Have we posted an album for these set of photos?
            if (isset($aVals['album_id']) && !empty($aVals['album_id'])) {
                // Set the album privacy
                Phpfox::getService('photo.album.process')->setPrivacy($aVals['album_id']);

                // Check if we already have an album cover
                if (!Phpfox::getService('photo.album.process')->hasCover($aVals['album_id'])) {
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

            // Update the user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'photo', $iFileSizes);

            (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_process_photos_done')) ? eval($sPlugin) : false);

            if (isset($aVals['is_cover_photo']) && $aVals['is_cover_photo']) {
                if (isset($aVals['page_id']) && $aVals['page_id'] > 0) {
                    if (Phpfox::getService('pages.process')->setCoverPhoto($aVals['page_id'], $iId, true)) {
                        $aVals['is_cover_photo'] = 1;
                    } else {
                        echo '<script type="text/javascript">alert("Something went wrong: ' . implode(Phpfox_Error::get()) . '");</script>';
                    }
                }
                if (isset($aVals['groups_id']) && $aVals['groups_id'] > 0) {
                    if (Phpfox::getService('groups.process')->setCoverPhoto($aVals['groups_id'], $iId, true)) {
                        $aVals['is_cover_photo'] = 1;
                    } else {
                        echo '<script type="text/javascript">alert("Something went wrong: ' . implode(Phpfox_Error::get()) . '");</script>';
                    }
                }
            }

            if (isset($_REQUEST['picup'])) {
            } else {
                if (isset($aVals['method']) && $aVals['method'] == 'massuploader') {
                    echo 'window.aImagesUrl.push(' . (json_encode($aImages)) . ');';
                } else {
                    $sExtra = '';
                    if (!empty($aVals['start_year']) && !empty($aVals['start_month']) && !empty($aVals['start_day'])) {
                        $sExtra .= '&start_year= ' . $aVals['start_year'] . '&start_month= ' . $aVals['start_month'] . '&start_day= ' . $aVals['start_day'] . '';
                    }

                    if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD') && empty($tempCoverPhotoId)) {
                        echo '<script type="text/javascript">';
                    }

                    if ($bUploadFail) {
                        foreach ($aUploadFailMessages as $sMessage) {
                            if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
                                echo 'window.parent.';
                            }

                            echo '$Core.cacheActivityFeedError(\'' . $sMessage . '\');';
                        }
                    }

                    if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
                        echo 'window.parent.';
                    }

                    $aParams = [
                        'user_id'                   => $iUserId,
                        'js_disable_ajax_restart'   => 'true',
                        'twitter_connection'        => !empty($aVals['connection']['twitter']) ? $aVals['connection']['twitter'] : '0',
                        'facebook_connection'       => !empty($aVals['connection']['facebook']) ? $aVals['connection']['facebook'] : '0',
                        'custom_pages_post_as_page' => $this->request()->get('custom_pages_post_as_page'),
                        'photos'                    => urlencode(json_encode($aImages)),
                        'action'                    => $sAction,
                        'parent_user_id'            => isset($aVals['parent_user_id']) ? (int)$aVals['parent_user_id'] : 0,
                        'tagged_friends'            => isset($aVals['tagged_friends']) ? (int)$aVals['tagged_friends'] : '',
                        'is_cover_photo'            => isset($aVals['is_cover_photo']) ? '1' : '0',
                        'timestamp'                 => $iTimestamp,
                        'no_feed'                   => $bNoFeed ? '1' : '0',
                    ];

                    if (!empty($aVals['page_id']) || !empty($aVals['groups_id'])) {
                        $aParams['is_page'] = 1;
                    }
                    if (isset($iFeedId)) {
                        $aParams['feed_id'] = $iFeedId;
                    }
                    if ($aCallback !== null) {
                        $aParams['callback_module'] = $aCallback['module'];
                        $aParams['callback_item_id'] = $aCallback['item_id'];
                    }
                    if (isset($aVals['page_id']) && $aVals['page_id'] > 0) {
                        $aParams['page_id'] = $aVals['page_id'];
                    }
                    if (isset($aVals['groups_id']) && $aVals['groups_id'] > 0) {
                        $aParams['groups_id'] = $aVals['groups_id'];
                    }

                    $aParams = array_merge($aParams, (new Core\Request())->all());
                    $out = http_build_query($aParams);

                    echo '$.ajaxCall(\'photo.process\', \'' . $out . $sExtra . '\');';

                    if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD') && empty($tempCoverPhotoId)) {
                        echo '</script>';
                    }
                }
            }

            (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_process_photos_done_javascript')) ? eval($sPlugin) : false);
        } else {
            // Output JavaScript
            if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
                echo '<script type="text/javascript">';
            } else {
                if (isset($sHTML5TempFile) && file_exists($sHTML5TempFile)) {
                    unlink($sHTML5TempFile);
                }
                header('HTTP/1.1 500 Internal Server Error');

                return [
                    'upload_error_message' => (empty($sErrorMessage) ? implode('',
                        Phpfox_Error::get()) : $sErrorMessage)
                ];
            }

            if (!$bIsInline) {
            } else {
                if (isset($aVals['is_cover_photo'])) {
                    echo 'window.parent.$(\'#uploading-cover\').hide();';
                    echo 'window.parent.$(\'#js_activity_feed_form\').show();';
                    echo 'window.parent.$(\'.profiles_banner\').removeClass(\'cover-uploading\');';
                    echo 'window.parent.$(\'#js_cover_photo_iframe_loader_error\').html(\'<div class="error_message">' . implode('',
                            (empty($aUploadFailMessages) ? Phpfox_Error::get() : $aUploadFailMessages)) . '</div>\').show();';
                } else {
                    echo 'window.parent.$Core.resetActivityFeedError(\'' . implode('<br/>',
                            (empty($aUploadFailMessages) ? Phpfox_Error::get() : $aUploadFailMessages)) . '\');';
                }
            }

            if (!defined('PHPFOX_HTML5_PHOTO_UPLOAD')) {
                echo '</script>';
            }
        }

        exit;
    }

    private function _processUploadFile($iUserId, &$aVals, &$iCnt, &$bNoFeed, &$iFileSizes, &$sFileName, $aImage, $iKey = null, $sFilePath = null)
    {
        $oServicePhotoProcess = Phpfox::getService('photo.process');
        $oFile = Phpfox::getLib('file');

        if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share') {
            $aVals['description'] = (isset($aVals['is_cover_photo']) ? null : $aVals['status_info']);
            $aVals['type_id'] = (isset($aVals['is_cover_photo']) ? '0' : '1');
        }

        if (isset($aVals['page_id'])) {
            $aVals['callback_module'] = 'pages';
            $aVals['callback_item_id'] = $aVals['group_id'] = $aVals['page_id'];
        }

        if (isset($aVals['groups_id'])) {
            $aVals['callback_module'] = 'groups';
            $aVals['callback_item_id'] = $aVals['group_id'] = $aVals['groups_id'];
        }

        if (!empty($aVals['new_album']) && isset($aVals['album_id']) && $aVals['album_id']) {
            $aNewAlbum = explode(',', $aVals['new_album']);
            if (in_array($aVals['album_id'], $aNewAlbum)) {
                $bNoFeed = true;
            }
        }

        if ($iId = $oServicePhotoProcess->add($iUserId, array_merge($aVals, $aImage))) {
            $iCnt++;
            // Move the uploaded image and return the full path to that image.
            $sFileName = $oFile->upload(!empty($sFilePath) ? $sFilePath : 'image[' . $iKey . ']',
                Phpfox::getParam('photo.dir_photo'), $iId, true);

            if (!$sFileName) {
                exit('failed: ' . implode('', Phpfox_Error::get()));
            }

            // Get the original image file size.
            $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

            // Get the current image width/height
            $aSize = getimagesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

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

            // Solves bug, when categories are left empty and setting "photo.allow_photo_category_selection" is enabled:
            if (isset($aVals['category_id'])) {
                $aUpdate['category_id'] = $aVals['category_id'];
            } else if (isset($aVals['category_id[]'])) {
                $aUpdate['category_id'] = $aVals['category_id[]'];
            }

            $oServicePhotoProcess->update($iUserId, $iId, $aUpdate);

            // Assign vars for the template.
            return [
                [
                'photo_id'    => $iId,
                'server_id'   => \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                'destination' => $sFileName,
                'name'        => $aImage['name'],
                'ext'         => $aImage['ext'],
                'size'        => $aImage['size'],
                'width'       => $aSize[0],
                'height'      => $aSize[1],
                'completed'   => 'false'
            ], $iId];
        }

        return [false, 0];
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_frame_clean')) ? eval($sPlugin) : false);
    }
}