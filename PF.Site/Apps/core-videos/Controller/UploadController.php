<?php

namespace Apps\PHPfox_Videos\Controller;

use Aws\S3\S3Client;
use Core\Storage\Filesystem;
use Core\Storage\SFTPAdapter;
use GuzzleHttp;
use MuxPhp;
use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_File;

defined('PHPFOX') or exit('NO DICE!');

class UploadController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        user('pf_video_share', '1', null, true);
        if (!setting('pf_video_support_upload_video', 1)) {
            return [
                'error' => _p('the_site_does_not_support_upload_videos_from_your_computer')
            ];
        }

        if (empty($_FILES['ajax_upload']['tmp_name'])) {
            switch ($_FILES['ajax_upload']['error']) {
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

            http_response_code(400);

            return [
                'error' => _p($message)
            ];
        }

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/plain');
        header('Accept-Ranges: bytes');

        $file_size = user('pf_video_file_size', 10);
        $path = PHPFOX_DIR_FILE . 'static' . PHPFOX_DS;
        $sId = md5(uniqid() . Phpfox::getUserId());
        $realName = $sId . '.' . Phpfox_File::instance()->getFileExt($_FILES['ajax_upload']['name']);
        $date = date('y/m/d/');
        $name = $date . $realName;
        $post = [];
        $sVideoUploaded = '';
        $iMethodUpload = setting('pf_video_method_upload');
        $bIsMuxUpload = $iMethodUpload == 2 && setting('pf_video_mux_token_id') && setting('pf_video_mux_token_secret');
        if (!empty($_SERVER['HTTP_X_POST_FORM'])) {
            foreach (explode('&', $_SERVER['HTTP_X_POST_FORM']) as $posts) {
                $part = explode('=', $posts);
                if (empty($part[0])) {
                    continue;
                }
                $post[$part[0]] = (isset($part[1]) ? $part[1] : '');
            }
        }

        if (isset($post['val[callback_module]']) && isset($post['val[callback_item_id]'])) {
            if (Phpfox::isModule($post['val[callback_module]']) &&
                Phpfox::hasCallback($post['val[callback_module]'], 'checkPermission') &&
                !Phpfox::callback($post['val[callback_module]'] . '.checkPermission', $post['val[callback_item_id]'],
                    'pf_video.share_videos')
            ) {
                http_response_code(400);

                return [
                    'error' => _p('you_dont_have_permission_to_share_videos_on_this_page')
                ];
            }
        }

        $ext = '3gp, aac, ac3, ec3, flv, m4f, mov, mj2, mkv, mp4, mxf, ogg, ts, webm, wmv, avi';
        $file = Phpfox_File::instance()->load('ajax_upload', array_map('trim', explode(',', $ext)), $file_size);
        if ($file === false) {
            http_response_code(400);

            return [
                'error' => implode('', Phpfox_Error::get())
            ];
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
            $aResource = fopen($_FILES['ajax_upload']['tmp_name'], 'r');
            if (!$aResource) {
                throw new \InvalidArgumentException("File does not exists!");
            }
            try {
                storage()->set('pf_video_' . $sId, [
                    'uploading' => 1
                ]);
                $result = $oStorage->putStream($realName, $aResource, ['visibility' => 'public']);
            } catch( \Exception $e) {
                storage()->del('pf_video_' . $sId);
                return [
                    'error' => $e->getMessage()
                ];
            }
            if ($result) {
                storage()->update('pf_video_' . $sId, [
                    'uploading' => 0,
                    'is_ready' => 0,
                    'time_stamp' => PHPFOX_TIME,
                    'path' => $realName,
                    'user_id' => Phpfox::getUserId(),
                    'view_id' => Phpfox::getUserParam('pf_video_approve_before_publicly') ? 2 : 0,
                    'id' => $sId,
                    'ext' => Phpfox_File::instance()->getFileExt($_FILES['ajax_upload']['name'])
                ]);
            } else {
                storage()->del('pf_video_' . $sId);
                http_response_code(400);
                return [
                    'error' => _p('unable_to_upload_file_due_to_a_server_error_or_restriction')
                ];
            }
            return [
                'upload' => true,
                'id' => $sId
            ];
        }
        if ($bIsMuxUpload) {
            //Upload to storage system
            $sVideoStorage = PHPFOX_DIR_FILE . 'video';
            if (!is_dir($sVideoStorage)) {
                @mkdir($sVideoStorage, 0777, 1);
                @chmod($sVideoStorage, 0777);
            }
            $sVideoUploaded = Phpfox_File::instance()->upload('ajax_upload', PHPFOX_DIR_FILE . 'video' . PHPFOX_DS, $sId);
            if (!$sVideoUploaded) {
                http_response_code(400);

                return [
                    'error' => !Phpfox_Error::isPassed() ? implode(', ', Phpfox_Error::get()) : _p('unable_to_upload_file_due_to_a_server_error_or_restriction')
                ];
            }
        } elseif (!@move_uploaded_file($_FILES['ajax_upload']['tmp_name'], $path . $realName)) {
            http_response_code(400);

            return [
                'error' => _p('unable_to_upload_file_due_to_a_server_error_or_restriction')
            ];
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
                        'url' => url('/video/callback')
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
                    'ext' => Phpfox_File::instance()->getFileExt($_FILES['ajax_upload']['name']),
                    'default_image' => $date . $sId . '.png/frame_0001.png'
                ]);

                return [
                    'upload' => true,
                    'id' => $encoding_job->id
                ];

            } catch (\Services_Zencoder_Exception $e) {
                http_response_code(400);

                return [
                    'error' => $e->getMessage()
                ];
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
                    'video_size' => $_FILES['ajax_upload']['size'],
                    'server_id' => $iServerId,
                    'playback_ids' => $resultData->getPlaybackIds(),
                    'user_id' => Phpfox::getUserId(),
                    'view_id' => Phpfox::getUserParam('pf_video_approve_before_publicly') ? 2 : 0,
                    'id' => $resultData->getId(),
                    'ext' => Phpfox_File::instance()->getFileExt($_FILES['ajax_upload']['name']),
                ]);
                return [
                    'upload' => true,
                    'id' => $resultData->getId()
                ];
            } catch (MuxPhp\ApiException $e) {
                return [
                    'error' => $e->getMessage()
                ];
            }
        } elseif ($iMethodUpload == 0 && setting('pf_video_ffmpeg_path')) {
            storage()->set('pf_video_' . $sId, [
                'path' => $path . $realName,
                'user_id' => Phpfox::getUserId(),
                'view_id' => Phpfox::getUserParam('pf_video_approve_before_publicly') ? 2 : 0,
                'id' => $sId,
                'ext' => Phpfox_File::instance()->getFileExt($_FILES['ajax_upload']['name'])
            ]);

            return [
                'upload' => true,
                'id' => $sId
            ];
        } else {
            return [
                'error' => _p('the_site_does_not_support_upload_videos_from_your_computer')
            ];
        }
    }
}
