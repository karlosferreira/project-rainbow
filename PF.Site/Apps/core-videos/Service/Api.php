<?php

namespace Apps\PHPfox_Videos\Service;

use Aws\S3\S3Client;
use Core\Api\ApiServiceBase;
use Core\Storage\Filesystem;
use Core\Storage\SFTPAdapter;
use GuzzleHttp;
use MuxPhp;
use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Queue;
use Phpfox_Validator;

class Api extends ApiServiceBase
{
    public function __construct()
    {
        $this->setPublicFields([
            'video_id',
            'in_process',
            'is_stream',
            'is_featured',
            'is_spotlight',
            'is_sponsor',
            'view_id',
            'module_id',
            'item_id',
            'privacy',
            'title',
            'user_id',
            'parent_user_id',
            'destination',
            'embed_code',
            'image_path',
            'file_ext',
            'duration',
            'resolution_x',
            'resolution_y',
            'total_comment',
            'total_like',
            'total_view',
            'time_stamp',
            'status_info',
            'page_user_id',
            'location_latlng',
            'location_name',
            'tagged_friends',
        ]);
    }

    public function upload()
    {
        $this->isUser();

        if (!user('pf_video_share', '1', null)) {
            return $this->error(_p('you_dont_have_permission_to_share_videos_on_this'));
        } elseif (!setting('pf_video_support_upload_video', 1)) {
            return $this->error(_p('the_site_does_not_support_upload_videos_from_your_computer'));
        }

        if (empty($_FILES['file']['tmp_name'])) {
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = "The uploaded file exceeds the upload_max_filesize (" . ini_get('upload_max_filesize') . ") directive in php.ini";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "the_uploaded_file_exceeds_the_MAX_FILE_SIZE_directive_that_was_specified_in_the_HTML_form";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "the_uploaded_file_was_only_partially_uploaded";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = "no_file_was_uploaded";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "missing_a_temporary_folder";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "failed_to_write_file_to_disk";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = "file_upload_stopped_by_extension";
                    break;

                default:
                    $message = "unknown_upload_error";
                    break;
            }

            return $this->error(_p($message));
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/plain');
        header('Accept-Ranges: bytes');

        $vals = $this->request()->get('val');
        $file_size = user('pf_video_file_size', 10);
        $path = PHPFOX_DIR_FILE . 'static' . PHPFOX_DS;
        $sId = md5(uniqid() . Phpfox::getUserId());
        $realName = $sId . '.' . Phpfox_File::instance()->getFileExt($_FILES['file']['name']);
        $date = date('y/m/d/');
        $name = $date . $realName;
        $iMethodUpload = setting('pf_video_method_upload');
        $bIsMuxUpload = $iMethodUpload == 2 && setting('pf_video_mux_token_id') && setting('pf_video_mux_token_secret');

        if (!empty($vals['module_id']) && !empty($vals['item_id'])) {
            if (Phpfox::isModule($vals['module_id']) &&
                Phpfox::hasCallback($vals['module_id'], 'checkPermission') &&
                !Phpfox::callback($vals['module_id'] . '.checkPermission', $vals['item_id'], 'pf_video.share_videos')) {
                return $this->error(_p('you_dont_have_permission_to_share_videos_on_this_page'));
            }
        }

        $ext = '3gp, aac, ac3, ec3, flv, m4f, mov, mj2, mkv, mp4, mxf, ogg, ts, webm, wmv, avi';
        $file = Phpfox_File::instance()->load('file', array_map('trim', explode(',', $ext)), $file_size);
        if ($file === false) {
            return $this->error(implode('', Phpfox_Error::get()));
        }

        if (Phpfox::getParam('v.pf_video_allow_compile_on_storage_system') && version_compare(Phpfox::getCurrentVersion(), '4.8.0', '>=')) {
            if (file_exists(dirname(dirname(__FILE__)) . '/FFmpegServer/ffmpeg_config.php')) {
                require_once dirname(dirname(__FILE__)) . '/FFmpegServer/ffmpeg_config.php';
            }
            $aConfig = isset($_aParams) ? $_aParams : [];
            $aConfig['permPublic'] = 0777;
            $aConfig['directoryPerm'] = 0777;
            $aConfig['passive'] = true;
            $aConfig['ssl'] = false;
            $aConfig['ignorePassiveAddress'] = false;
            $oStorage = new Filesystem(new SFTPAdapter($aConfig));
            $aResource = fopen($_FILES['file']['tmp_name'], 'r');
            if (!$aResource) {
                return $this->error('File does not exists!');
            }

            try {
                storage()->set('pf_video_' . $sId, [
                    'uploading' => 1
                ]);
                $result = $oStorage->putStream($realName, $aResource, ['visibility' => 'public']);
            } catch( \Exception $e) {
                storage()->del('pf_video_' . $sId);
                return $this->error($e->getMessage());
            }

            if ($result) {
                storage()->set('pf_video_' . $sId, [
                    'uploading' => 0,
                    'is_ready' => 0,
                    'time_stamp' => PHPFOX_TIME,
                    'path' => $realName,
                    'user_id' => Phpfox::getUserId(),
                    'view_id' => Phpfox::getUserParam('pf_video_approve_before_publicly') ? 2 : 0,
                    'id' => $sId,
                    'ext' => Phpfox_File::instance()->getFileExt($_FILES['file']['name'])
                ]);
            } else {
                storage()->del('pf_video_' . $sId);
                return $this->error(_p('unable_to_upload_file_due_to_a_server_error_or_restriction'));
            }

            return $this->success([
                'pf_video_id' => $sId
            ]);
        }
        if ($bIsMuxUpload) {
            //Upload to storage system
            $sVideoStorage = PHPFOX_DIR_FILE . 'video';
            if (!is_dir($sVideoStorage)) {
                @mkdir($sVideoStorage, 0777, 1);
                @chmod($sVideoStorage, 0777);
            }
            $sVideoUploaded = Phpfox_File::instance()->upload('file', PHPFOX_DIR_FILE . 'video' . PHPFOX_DS, $sId);
            if (!$sVideoUploaded) {
                return $this->error(!Phpfox_Error::isPassed() ? implode(', ', Phpfox_Error::get()) : _p('unable_to_upload_file_due_to_a_server_error_or_restriction'));
            }
        } elseif (!@move_uploaded_file($_FILES['file']['tmp_name'], $path . $realName)) {
            return $this->error(_p('unable_to_upload_file_due_to_a_server_error_or_restriction'));
        }

        if ($iMethodUpload == 1 && setting('pf_video_key') && setting('pf_video_s3_key')) {
            $bucket = setting('pf_video_s3_bucket');
            $region = setting('pf_video_s3_region', 'us-east-2');
            $_oS3Client = new S3Client([
                'region' => $region,
                'version' => 'latest',
                'credentials' => [
                    'key' => setting('pf_video_s3_key'),
                    'secret' => setting('pf_video_s3_secret'),
                ],
            ]);

            $uploadParams = [
                'Bucket' => $bucket,
                'Key' => $name,
                'SourceFile' => $path . $realName,
                'ACL' => 'public-read',
            ];

            if (!empty($metaTags = Phpfox::getService('v.video')->getVideoMetaTags())) {
                $uploadParams = array_merge($uploadParams, $metaTags);
            }

            $result = $_oS3Client->putObject($uploadParams);

            if(defined('PHPFOX_DEBUG') && PHPFOX_DEBUG) {
                Phpfox::getLog('v_s3.log')->info('s3_put_result ' . $result);
            }
            try {
                $zencoder = new \Services_Zencoder(setting('pf_video_key'));
                $params = [
                    "input" => 's3://' . $bucket . '/' . $name,
                    'notifications' => [
                        'url' => url('/video/callback'),
                    ],
                    "outputs" => [
                        [
                            "label" => "mp4 high",
                            'h264_profile' => 'high',
                            'url' => 's3://' . $bucket . '/' . $date . $sId . '.mp4',
                            'public' => true,
                            'thumbnails' => [
                                'label' => 'thumb',
                                'size' => '852x480',
                                'base_url' => 's3://' . $bucket . '/' . $date . $sId . '.png',
                                'number' => 3
                            ]
                        ]
                    ]
                ];

                $encoding_job = $zencoder->jobs->create($params);

                storage()->set('pf_video_' . $encoding_job->id, [
                    'encoding_id' => $encoding_job->id,
                    'video_path' => $date . $sId . '.mp4',
                    'user_id' => Phpfox::getUserId(),
                    'view_id' => Phpfox::getUserParam('pf_video_approve_before_publicly') ? 2 : 0,
                    'id' => $sId,
                    'ext' => Phpfox_File::instance()->getFileExt($_FILES['file']['name']),
                    'default_image' => $date . $sId . '.png/frame_0001.png'
                ]);

                return $this->success([
                    'pf_video_id' => $encoding_job->id
                ]);

            } catch (\Services_Zencoder_Exception $e) {
                return $this->error($e->getMessage());
            }
        } elseif ($bIsMuxUpload) {
            $sVideoPath = $sVideoUploaded;
            $sVideoUploaded = Phpfox::getParam('core.url_file') . 'video/' . sprintf($sVideoUploaded, '');
            $iServerId = (int)Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            if ($iServerId > 0 && $sVideoUploaded) {
                $sVideoUploaded = Phpfox::getLib('cdn')->getUrl($sVideoUploaded, $iServerId);
            }
            $config = MuxPhp\Configuration::getDefaultConfiguration()
                ->setUsername(setting('pf_video_mux_token_id'))
                ->setPassword(setting('pf_video_mux_token_secret'));
            // API Client Initialization
            $assetsApi = new MuxPhp\Api\AssetsApi(new GuzzleHttp\Client(), $config);
            $input = new MuxPhp\Models\InputSettings(['url' => $sVideoUploaded]);
            $createAssetRequest = new MuxPhp\Models\CreateAssetRequest([
                "input" => $input,
                "playback_policy" => [MuxPhp\Models\PlaybackPolicy::PUBLIC_PLAYBACK_POLICY]
            ]);

            // Ingest
            try {
                /** @var MuxPhp\Models\AssetResponse $result */
                $result = $assetsApi->createAsset($createAssetRequest);
                /** @var MuxPhp\Models\Asset $resultData */
                $resultData = $result->getData();
                storage()->set('pf_video_' . $resultData->getId(), [
                    'encoding_id' => $resultData->getId(),
                    'asset_id' => $resultData->getId(),
                    'video_temp_path' => $sVideoPath,
                    'video_size' => $_FILES['file']['size'],
                    'server_id' => $iServerId,
                    'playback_ids' => $resultData->getPlaybackIds(),
                    'user_id' => Phpfox::getUserId(),
                    'view_id' => Phpfox::getUserParam('pf_video_approve_before_publicly') ? 2 : 0,
                    'id' => $resultData->getId(),
                    'ext' => Phpfox_File::instance()->getFileExt($_FILES['file']['name']),
                ]);

                return $this->success([
                    'pf_video_id' => $resultData->getId()
                ]);
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
        } elseif ($iMethodUpload == 0 && setting('pf_video_ffmpeg_path')) {
            storage()->set('pf_video_' . $sId, [
                'path' => $path . $realName,
                'user_id' => Phpfox::getUserId(),
                'view_id' => Phpfox::getUserParam('pf_video_approve_before_publicly') ? 2 : 0,
                'id' => $sId,
                'ext' => Phpfox_File::instance()->getFileExt($_FILES['file']['name'])
            ]);

            return $this->success([
                'pf_video_id' => $sId
            ]);
        } else {
            return $this->error(_p('the_site_does_not_support_upload_videos_from_your_computer'));
        }
    }

    /**
     * @description: update a video
     * @param $params
     *
     * @return array|bool
     */
    public function put($params)
    {
        $this->isUser();

        if (empty(($aVideo = Phpfox::getService('v.video')->getForEdit($params['id']))) ||
            !(($aVideo['user_id'] == Phpfox::getUserId() && user('pf_video_edit_own_video')) || user('pf_video_edit_all_video'))) {
            return $this->error(_p('unable_to_edit_this_video'));
        }

        $aVals = $this->request()->get('val');

        $aValidation = [
            'title' => [
                'def' => 'required',
                'title' => _p('provide_a_title_for_this_video'),
            ],
        ];

        $oValid = Phpfox_Validator::instance()->set([
                'sFormName' => 'core_js_video_form',
                'aParams' => $aValidation,
            ]
        );

        $aCallback = null;
        $sModule = $aVideo['module_id'];
        $iItemId = $aVideo['item_id'];

        if (!empty($sModule) && $sModule != 'video' && Phpfox::hasCallback($sModule, 'getItem')) {
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItemId);
            if ($aCallback === false) {
                return $this->error(_p('cannot_find_the_parent_item'));
            }
        } else {
            if (!empty($sModule) && ($sModule != 'user') && !empty($iItemId) && $sModule != 'video' && $aCallback === null) {
                return $this->error(_p('cannot_find_the_parent_item'));
            }
        }

        if ($oValid->isValid($aVals) && Phpfox::getService('v.process')->update($aVideo['video_id'], $aVals)) {
            return $this->get(['id' => $params['id']], [_p('video_successfully_updated')]);
        }

        return $this->error();
    }

    /**
     * @description: delete a video
     * @param $params
     *
     * @return array|bool
     */
    public function delete($params)
    {
        $this->isUser();

        if (!user('pf_video_view', '1')) {
            return $this->error(_p('Cannot {{ action }} this {{ item }}.',
                ['action' => _p('delete__l'), 'item' => _p('Video')]), true);
        }

        if (Phpfox::getService('v.process')->delete($params['id'])) {
            return $this->success([], [_p('{{ item }} successfully deleted.', ['item' => _p('Video')])]);
        }

        return $this->error();
    }

    /**
     * @description: add new video
     * @return array|bool
     */
    public function post()
    {
        $this->isUser();

        if (!Phpfox::getUserParam('v.pf_video_share')) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('Video')]));
        } elseif (!Phpfox::getService('v.video')->checkLimitation()) {
            return $this->error(_p('v_you_have_reached_your_limit_to_upload_new_video'));
        }

        $aVals = $this->request()->get('val');
        $bIsFeed = !empty($aVals['is_feed']);

        //Support sharing video on feed in case login as page/group
        $iProfilePageId = Phpfox::getUserBy('profile_page_id');
        if ($iProfilePageId && $bIsFeed) {
            if (($sModuleId = Phpfox::getLib('pages.facade')->getPageItemType($iProfilePageId)) == 'groups') {
                Phpfox::getService('groups')->setIsInPage();
            } elseif ($sModuleId == 'pages') {
                Phpfox::getService('pages')->setIsInPage();
            }
        }

        $sModule = !empty($aVals['module_id']) ? $aVals['module_id'] : false;
        $iItemId = !empty($aVals['item_id']) ? $aVals['item_id'] : false;
        $aCallback = false;
        if ($sModule && $iItemId) {
            $aVals = array_merge($aVals, [
                'callback_module' => $sModule,
                'callback_item_id' => $iItemId,
            ]);
        }

        if ($sModule !== false && $iItemId !== false && Phpfox::hasCallback($sModule, 'getItem')) {
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItemId);
            if ($aCallback === false) {
                return $this->error(_p('cannot_find_the_parent_item'));
            }
            if (Phpfox::hasCallback($sModule, 'checkPermission')) {
                if (!Phpfox::callback($sModule . '.checkPermission', $iItemId, 'pf_video.share_videos')) {
                    return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
                }
            }
        } else {
            if (!empty($sModule) && !empty($iItemId) && $sModule != 'video' && $aCallback === false) {
                return $this->error(_p('cannot_find_the_parent_item'));
            }
        }

        $parsedVideoUrl = null;
        if (!empty($aVals['url']) && !empty($parsedVideoUrl = Phpfox::getService('link')->getLink($aVals['url']))) {
            if (empty($parsedVideoUrl['embed_code'])) {
                return $this->error(_p('unable_to_load_a_video_to_embed'));
            }
            if (isset($parsedVideoUrl['duration'])) {
                $aVals['duration'] = $parsedVideoUrl['duration'];
            }
            if (isset($parsedVideoUrl['width'])) {
                $aVals['resolution_x'] = $parsedVideoUrl['width'];
            }
            if (isset($parsedVideoUrl['height'])) {
                $aVals['resolution_y'] = $parsedVideoUrl['height'];
            }
            if ((!isset($aVals['title']) || $aVals['title'] == '') && isset($parsedVideoUrl['title'])) {
                $aVals['title'] = $parsedVideoUrl['title'];
            }
            if ((!isset($aVals['text']) || $aVals['text'] == '') && isset($parsedVideoUrl['description'])) {
                $aVals['text'] = $parsedVideoUrl['description'];
            }
            if (isset($parsedVideoUrl['embed_code'])) {
                $aVals['embed_code'] = $parsedVideoUrl['embed_code'];
            }
            if (isset($parsedVideoUrl['default_image'])) {
                $aVals['default_image'] = $parsedVideoUrl['default_image'];
            }
        }

        if (!$bIsFeed) {
            $iMethodUpload = setting('pf_video_method_upload');
            $oValid = Phpfox_Validator::instance()->set([
                'sFormName' => 'core_js_video_form',
                'aParams' => [
                    'title' => [
                        'def' => 'required',
                        'title' => _p('provide_a_title_for_this_video'),
                    ],
                ],
            ]);

            if ($oValid->isValid($aVals)) {
                if (preg_match('/dailymotion/', $aVals['url']) && substr($aVals['url'], 0, 8) == 'https://') {
                    $aVals['url'] = str_replace('https', 'http', $aVals['url']);
                }

                if (isset($aVals['pf_video_id'])) {
                    if (empty($aVals['pf_video_id'])) {
                        return $this->error(_p('we_could_not_find_a_video_there_please_try_again'));
                    }

                    $encoding = storage()->get('pf_video_' . $aVals['pf_video_id']);
                    if (!empty($encoding->value->encoded)) {
                        $aVals = array_merge($aVals, [
                            'is_stream' => 0,
                            'user_id' => $encoding->value->user_id,
                            'server_id' => $encoding->value->server_id,
                            'path' => $encoding->value->video_path,
                            'ext' => $encoding->value->ext,
                            'default_image' => isset($encoding->value->default_image) ? $encoding->value->default_image : '',
                            'image_path' => isset($encoding->value->image_path) ? $encoding->value->image_path : '',
                            'image_server_id' => $encoding->value->image_server_id,
                            'duration' => $encoding->value->duration,
                            'video_size' => $encoding->value->video_size,
                            'photo_size' => $encoding->value->photo_size,
                            'resolution_x' => $encoding->value->resolution_x,
                            'resolution_y' => $encoding->value->resolution_y,
                        ]);

                        $iId = Phpfox::getService('v.process')->addVideo($aVals);

                        if (Phpfox::isModule('notification')) {
                            Phpfox::getService('notification.process')->add('v_ready', $iId,
                                $encoding->value->user_id, $encoding->value->user_id, true);
                        }

                        $sTitle = (!empty($aVals['title']) ? Phpfox::getLib('parse.output')->clean($aVals['title'], 255) : _p('untitled_video'));
                        Phpfox::getLib('mail')->to($encoding->value->user_id)
                            ->subject(['email_your_video_title_is_ready', ['title' => $sTitle]])
                            ->message(['your_video_title_is_ready_click_on_link', ['title' => $sTitle, 'link' => Phpfox::permalink('video.play', $iId, $sTitle)]])
                            ->notification('v.email_notification')
                            ->send();

                        $file = PHPFOX_DIR_FILE . 'static/' . $encoding->value->id . '.' . $encoding->value->ext;
                        if (file_exists($file)) {
                            @unlink($file);
                        }

                        storage()->del('pf_video_' . $aVals['pf_video_id']);
                    } else {
                        if (Phpfox::getParam('v.pf_video_allow_compile_on_storage_system') && version_compare(Phpfox::getCurrentVersion(), '4.8.0', '>=')) {
                            storage()->update('pf_video_' . $aVals['pf_video_id'], [
                                'encoding_id' => '',
                                'is_ready' => 1,
                                'id' => $encoding->value->id,
                                'user_id' => $encoding->value->user_id,
                                'view_id' => $encoding->value->view_id,
                                'path' => $encoding->value->path,
                                'ext' => $encoding->value->ext,
                                'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                                'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                                'module_id' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                                'item_id' => (isset($aVals['item_id']) ? (int)$aVals['item_id'] : 0),
                                'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                                'title' => $aVals['title'],
                                'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                                'text' => $aVals['text'],
                                'status_info' => '',
                            ]);
                        } elseif ($iMethodUpload == 0 && setting('pf_video_ffmpeg_path')) {
                            $iJobId = Phpfox_Queue::instance()->addJob('videos_ffmpeg_encode', []);
                            storage()->set('pf_video_' . $iJobId, [
                                'encoding_id' => $iJobId,
                                'id' => $encoding->value->id,
                                'user_id' => $encoding->value->user_id,
                                'view_id' => $encoding->value->view_id,
                                'path' => $encoding->value->path,
                                'ext' => $encoding->value->ext,
                                'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                                'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                                'module_id' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                                'item_id' => (isset($aVals['item_id']) ? (int)$aVals['item_id'] : 0),
                                'callback_module' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                                'callback_item_id' => (isset($aVals['item_id']) ? $aVals['item_id'] : ''),
                                'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                                'title' => $aVals['title'],
                                'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                                'text' => $aVals['text'],
                                'status_info' => '',
                            ]);
                            storage()->del('pf_video_' . $aVals['pf_video_id']);
                        } else {
                            storage()->update('pf_video_' . $aVals['pf_video_id'], [
                                'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                                'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                                'module_id' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                                'item_id' => (isset($aVals['item_id']) ? (int)$aVals['item_id'] : 0),
                                'callback_module' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                                'callback_item_id' => (isset($aVals['item_id']) ? $aVals['item_id'] : ''),
                                'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                                'title' => $aVals['title'],
                                'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                                'text' => $aVals['text'],
                                'status_info' => '',
                                'updated_info' => 1,
                            ]);
                        }
                    }

                    return $this->success([], [_p('your_video_has_successfully_been_saved_and_will_be_published_when_we_are_done_processing_it')]);
                } elseif (!empty($parsedVideoUrl) && !empty($iId = Phpfox::getService('v.process')->addVideo($aVals))) {
                    return $this->get(['id' => $iId], [_p('{{ item }} successfully added.', ['item' => _p('Video')])]);
                }
            }
        } else {
            $status = text()->clean($aVals['status_info']);
            if (isset($aVals['pf_video_id'])) {
                if (empty($aVals['pf_video_id'])) {
                    return $this->error(_p('we_could_not_find_a_video_there_please_try_again'));
                }

                $encoding = storage()->get('pf_video_' . $aVals['pf_video_id']);

                // set owner of video
                $iUserId = 0;
                if (!empty($aVals['item_id']) && !empty($aVals['module_id']) && Phpfox::isModule($aVals['module_id'])) {
                    if (in_array($aVals['module_id'], ['pages', 'groups']) && $aVals['custom_pages_post_as_page']) {
                        $iUserId = Phpfox::getService($aVals['module_id'])->getUserId($aVals['item_id']);
                    } elseif (Phpfox::hasCallback($aVals['module_id'], 'getUserId')) {
                        $iUserId = Phpfox::callback($aVals['module_id'] . '.getUserId', $aVals['item_id']);
                    }
                }

                if (!empty($aVals['custom_pages_post_as_page'])) {
                    $iUserId = Phpfox::getPageUserId();
                } else {
                    $iUserId ?: $iUserId = $encoding->value->user_id;
                }

                if (!empty($encoding->value->encoded)) {
                    $aVals = array_merge($aVals, [
                        'text' => '',
                        'status_info' => $status,
                        'is_stream' => 0,
                        'user_id' => $iUserId,
                        'server_id' => $encoding->value->server_id,
                        'path' => $encoding->value->video_path,
                        'ext' => $encoding->value->ext,
                        'default_image' => isset($encoding->value->default_image) ? $encoding->value->default_image : '',
                        'image_path' => isset($encoding->value->image_path) ? $encoding->value->image_path : '',
                        'image_server_id' => $encoding->value->image_server_id,
                        'duration' => $encoding->value->duration,
                        'video_size' => $encoding->value->video_size,
                        'photo_size' => $encoding->value->photo_size,
                        'resolution_x' => $encoding->value->resolution_x,
                        'resolution_y' => $encoding->value->resolution_y,
                        'feed_values' => $aVals,
                    ]);

                    $this->_extractLocation($aVals, $aVals);

                    $aVals['is_scheduled'] = !empty($aVals['confirm_scheduled']);

                    $iId = Phpfox::getService('v.process')->addVideo($aVals);

                    if (Phpfox::isModule('notification')) {
                        Phpfox::getService('notification.process')->add('v_ready', $iId, $encoding->value->user_id,
                            $encoding->value->user_id, true);
                    }

                    $sTitle = (!empty($aVals['title']) ? Phpfox::getLib('parse.output')->clean($aVals['title'], 255) : _p('untitled_video'));
                    Phpfox::getLib('mail')->to($encoding->value->user_id)
                        ->subject(['email_your_video_title_is_ready', ['title' => $sTitle]])
                        ->message(['your_video_title_is_ready_click_on_link', ['title' => $sTitle, 'link' => Phpfox::permalink('video.play', $iId, $sTitle)]])
                        ->notification('v.email_notification')
                        ->send();

                    $file = PHPFOX_DIR_FILE . 'static/' . $encoding->value->id . '.' . $encoding->value->ext;
                    if (file_exists($file)) {
                        unlink($file);
                    }

                    storage()->del('pf_video_' . $aVals['pf_video_id']);
                } else {
                    if (Phpfox::getParam('v.pf_video_allow_compile_on_storage_system') && version_compare(Phpfox::getCurrentVersion(), '4.8.0', '>=')) {
                        $aStorageData = [
                            'is_ready' => 1,
                            'encoding_id' => '',
                            'id' => $encoding->value->id,
                            'user_id' => $iUserId,
                            'view_id' => $encoding->value->view_id,
                            'path' => $encoding->value->path,
                            'ext' => $encoding->value->ext,
                            'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                            'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                            'module_id' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                            'item_id' => (isset($aVals['item_id']) ? (int)$aVals['item_id'] : 0),
                            'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                            'title' => $aVals['title'],
                            'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                            'text' => isset($aVals['text']) ? $aVals['text'] : '',
                            'status_info' => $status,
                            'feed_values' => json_encode($aVals),
                            'tagged_friends' => isset($aVals['tagged_friends']) ? $aVals['tagged_friends'] : null,
                        ];
                        $this->_extractLocation($aVals, $aStorageData);
                        storage()->update('pf_video_' . $aVals['pf_video_id'], $aStorageData);
                    } elseif (setting('pf_video_method_upload') == 0 && setting('pf_video_ffmpeg_path')) {
                        $iJobId = Phpfox_Queue::instance()->addJob('videos_ffmpeg_encode', []);
                        $aStorageData = [
                            'encoding_id' => $iJobId,
                            'id' => $encoding->value->id,
                            'user_id' => $iUserId,
                            'view_id' => $encoding->value->view_id,
                            'path' => $encoding->value->path,
                            'ext' => $encoding->value->ext,
                            'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                            'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                            'module_id' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                            'item_id' => (isset($aVals['item_id']) ? (int)$aVals['item_id'] : 0),
                            'callback_module' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                            'callback_item_id' => (isset($aVals['item_id']) ? $aVals['item_id'] : ''),
                            'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                            'title' => $aVals['title'],
                            'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                            'text' => isset($aVals['text']) ? $aVals['text'] : '',
                            'status_info' => $status,
                            'feed_values' => json_encode($aVals),
                            'tagged_friends' => isset($aVals['tagged_friends']) ? $aVals['tagged_friends'] : null,
                        ];
                        $this->_extractLocation($aVals, $aStorageData);

                        storage()->set('pf_video_' . $iJobId, $aStorageData);
                        storage()->del('pf_video_' . $aVals['pf_video_id']);
                    } else {
                        $aStorageData = [
                            'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                            'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                            'module_id' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                            'item_id' => (isset($aVals['item_id']) ? (int)$aVals['item_id'] : 0),
                            'callback_module' => (isset($aVals['module_id']) ? $aVals['module_id'] : ''),
                            'callback_item_id' => (isset($aVals['item_id']) ? $aVals['item_id'] : ''),
                            'parent_user_id' => isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0,
                            'title' => $aVals['title'],
                            'category' => json_encode([]),
                            'text' => '',
                            'status_info' => $status,
                            'updated_info' => 1,
                            'user_id' => $iUserId,
                            'feed_values' => json_encode($aVals),
                            'tagged_friends' => isset($aVals['tagged_friends']) ? $aVals['tagged_friends'] : null,
                        ];
                        $this->_extractLocation($aVals, $aStorageData);
                        storage()->update('pf_video_' . $aVals['pf_video_id'], $aStorageData);
                    }
                }

                return $this->success([], [_p('your_video_has_successfully_been_saved_and_will_be_published_when_we_are_done_processing_it')]);
            } elseif (!empty($aVals['url'])) {
                $sUrl = trim($aVals['url']);
                if (substr($sUrl, 0, 7) != 'http://' && substr($sUrl, 0, 8) != 'https://') {
                    return $this->error(_p('please_provide_a_valid_url'));
                }

                if (!empty($parsedVideoUrl)) {
                    $embed_code = str_replace('http://player.vimeo.com/', 'https://player.vimeo.com/',
                        $parsedVideoUrl['embed_code']);
                    $aVals['title'] = $parsedVideoUrl['title'];
                    $aVals['text'] = str_replace("<br />", "\r\n", $parsedVideoUrl['description']);
                    $aVals['status_info'] = $status;
                    $aVals['embed_code'] = $embed_code;
                    $this->_extractLocation($aVals, $aVals);
                    $aVals['is_scheduled'] = !empty($aVals['confirm_scheduled']);
                    $iVideoid = Phpfox::getService('v.process')->addVideo($aVals);

                    return $this->get(['id' => $iVideoid], [_p('{{ item }} successfully added.', ['item' => _p('Video')])]);
                } else {
                    return $this->error(_p('we_could_not_find_a_video_there_please_check_the_url_and_try_again'));
                }
            }
        }

        return $this->error();
    }

    /**
     * @description: get info of a video
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function get($params, $messages = [])
    {
        if (!user('pf_video_view', '1')
            || empty($aVideo = Phpfox::getService('v.video')->getVideo($params['id'], true))
            || ($this->isUser() && Phpfox::getService('user.block')->isBlocked(null, $aVideo['user_id']))
            || (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('v', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'],
                    $aVideo['is_friend'], true))) {
            return $this->error(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                ['action' => _p('view__l'), 'item' => _p('Video')]));
        }

        if (!empty($aVideo['module_id']) && !empty($aVideo['item_id']) && $aVideo['module_id'] != 'video') {
            if (!Phpfox::isModule($aVideo['module_id'])) {
                return $this->error(_p('cannot_find_the_parent_item'));
            } elseif (($aVideo['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aVideo['item_id'],
                        'pf_video.view_browse_videos'))
                || (Phpfox::hasCallback($aVideo['module_id'], 'checkPermission') && !Phpfox::callback($aVideo['module_id'] . '.checkPermission', $aVideo['item_id'], 'pf_video.view_browse_videos'))) {
                return $this->error(_p('unable_to_view_this_section_due_to_privacy_settings'));
            }
        }

        return $this->success($this->getItem($aVideo, 'public'), $messages);
    }

    private function _extractLocation($aVals, &$aReturn)
    {
        $aReturn['location_name'] = (!empty($aVals['location']['name'])) ? Phpfox::getLib('parse.input')->clean($aVals['location']['name']) : null;
        if ((!empty($aVals['location']['latlng']))) {
            $aMatch = explode(',', $aVals['location']['latlng']);
            $aMatch['latitude'] = floatval($aMatch[0]);
            $aMatch['longitude'] = floatval($aMatch[1]);
            $aReturn['location_latlng'] = json_encode([
                'latitude' => $aMatch['latitude'],
                'longitude' => $aMatch['longitude'],
            ]);
        } else {
            $aReturn['location_latlng'] = null;
        }
    }

    /**
     * @description: get videos
     * @return array|bool
     */
    public function gets()
    {
        $userId = $this->request()->get('user_id');
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $view = $this->request()->get('view');
        $categoryId = $this->request()->get('category_id');
        $user = !empty($userId) ? Phpfox::getService('user')->get($userId) : [];

        if (!user('pf_video_view', '1')
            || (in_array($view, ['my', 'pending']) && !$this->isUser())
            || ($view == 'pending' && !user('pf_video_approve'))
            || (!empty($userId) && empty($user['user_id']))) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('Videos')]));
        }

        if (!empty($user['user_id']) && (!empty($legacy = $this->request()->get('legacy')))) {
            Phpfox::getService('core')->getLegacyItem([
                    'field' => ['video_id', 'title'],
                    'table' => 'video',
                    'redirect' => 'video',
                    'title' => $legacy,
                ]
            );
        }

        $this->initSearchParams();

        $this->search()->set([
                'type' => 'video',
                'field' => 'video.video_id',
                'search_tool' => [
                    'table_alias' => 'video',
                    'search' => [
                        'name' => 'search',
                        'field' => ['video.title', 'video_text.text_parsed'],
                    ],
                    'sort' => [
                        'latest' => ['video.time_stamp', _p('latest')],
                        'most-viewed' => ['video.total_view', _p('most_viewed')],
                        'most-liked' => ['video.total_like', _p('most_liked')],
                        'most-talked' => ['video.total_comment', _p('most_discussed')],
                    ],
                    'show' => [$this->getSearchParam('limit')],
                ],
            ]
        );

        $aBrowseParams = [
            'module_id' => 'v',
            'alias' => 'video',
            'field' => 'video_id',
            'table' => Phpfox::getT('video'),
            'hide_view' => ['pending', 'my'],
        ];

        switch ($view) {
            case 'my':
                $sCondition = ' AND video.user_id = ' . Phpfox::getUserId();
                $aModules = ['user'];
                if (!Phpfox::isAppActive('PHPfox_Groups')) {
                    $aModules[] = 'groups';
                }
                if (!Phpfox::isAppActive('Core_Pages')) {
                    $aModules[] = 'pages';
                }
                $sCondition .= ' AND video.module_id NOT IN ("' . implode('","', $aModules) . '")';
                $this->search()->setCondition($sCondition);
                break;
            case 'pending':
                $sCondition = ' AND video.view_id = 2';
                $aModules = [];
                if (!Phpfox::isAppActive('PHPfox_Groups')) {
                    $aModules[] = 'groups';
                }
                if (!Phpfox::isAppActive('Core_Pages')) {
                    $aModules[] = 'pages';
                }
                $sCondition .= ' AND video.module_id NOT IN ("' . implode('","', $aModules) . '")';
                $this->search()->setCondition($sCondition);
                break;
            default:
                if (!empty($user['user_id'])) {
                    $this->search()->setCondition(' AND video.in_process = 0 AND video.view_id = 0 AND video.item_id = 0 AND video.privacy IN(' . (setting('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ') AND video.user_id = ' . (int)$user['user_id']);
                } else {
                    $sCondition = ' AND video.in_process = 0 AND video.view_id = 0';
                    if (!empty($moduleId)) {
                        $sCondition .= ' AND video.module_id = \'' . Phpfox::getLib('database')->escape($moduleId) . '\' AND video.item_id = ' . (int)$itemId;
                        if (!user('privacy.can_view_all_items')) {
                            $sCondition .= ' AND video.privacy IN(%PRIVACY%)';
                        }
                    } else {
                        if (setting('pf_video_display_video_created_in_group') || setting('pf_video_display_video_created_in_page')) {
                            $aModules = ['video'];
                            if (setting('pf_video_display_video_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                                $aModules[] = 'groups';
                            }
                            if (setting('pf_video_display_video_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                                $aModules[] = 'pages';
                            }
                            $sCondition .= ' AND video.module_id IN ("' . implode('","', $aModules) . '")';
                        } else {
                            $sCondition .= ' AND video.item_id = 0';
                        }
                        if (!user('privacy.can_view_all_items')) {
                            $sCondition .= ' AND video.privacy IN(%PRIVACY%)';
                        }
                    }
                    $this->search()->setCondition($sCondition);
                }
                break;
        }

        if (!empty($categoryId)) {
            $aCategory = Phpfox::getService('v.category')->getCategory($categoryId);
            if ($aCategory['category_id']) {
                if (!Phpfox::isAdmin()) {
                    // check this category de-active
                    $aCategory = Phpfox::getService('v.category')->getCategory($categoryId);
                    if (!$aCategory['is_active']) {
                        return $this->error(_p('the_category_you_are_looking_for_does_not_exist_or_has_been_removed'));
                    }

                    // check parent categories de-active
                    $aParentCategories = Phpfox::getService('v.category')->getParentCategories($categoryId);
                    foreach ($aParentCategories as $aParentCategory) {
                        if (!$aParentCategory['is_active']) {
                            return $this->error(_p('the_category_you_are_looking_for_does_not_exist_or_has_been_removed'));
                        }
                    }
                }

                $sChildIds = Phpfox::getService('v.category')->getChildIds($categoryId);
                $categoryIdIds = $categoryId;

                if ($sChildIds) {
                    $categoryIdIds .= ',' . $sChildIds;
                }

                $this->search()->setCondition('AND vcd.category_id IN (' . $categoryIdIds . ')');

                Phpfox::getService('v.browse')->setIsCategorySearch();
            }
        }

        if (!empty($moduleId) && in_array($moduleId, ['pages', 'groups'])) {
            $sService = $moduleId == 'pages' ? 'pages' : 'groups';
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $itemId, 'pf_video.view_browse_videos')) {
                return $this->error(_p('Cannot display this section due to privacy.'));
            }
        }

        Phpfox::getService('v.browse')->setIsApi();

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)->execute();

        $items = $this->search()->browse()->getRows();
        $parsedItems = [];

        foreach ($items as $item) {
            $sImagePath = $item['image_path'];
            $item = array_merge(Phpfox::getService('v.video')->compileVideo($item, 360, 1024, false), ['image_path' => $sImagePath]);
            $parsedItems[] = $this->getItem($item);
        }

        return $this->success($parsedItems);
    }
}