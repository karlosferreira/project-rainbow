<?php

namespace Apps\PHPfox_Videos\Controller;

use GuzzleHttp;
use MuxPhp;
use Phpfox;
use Phpfox_Component;

class MuxCallbackController extends Phpfox_Component
{
    public function process()
    {
        //Handle callback
        $secret = setting('pf_video_mux_webhook_signing_secret');
        $requestBody = @file_get_contents("php://input");
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        if (isset($headers['Mux-Signature']) && $secret) {
            $muxSignature = $headers['Mux-Signature'];
            $dataSignature = explode(',', $muxSignature);
            if (count($dataSignature) < 2) {
                return false;
            }
            $v1 = str_replace('v1=', '', $dataSignature[1]);
            $payload = str_replace('t=', '', $dataSignature[0]) . '.' . $requestBody;
            $hashKey = hash_hmac('sha256', $payload, $secret);
            if ($hashKey != $v1) {
                return false; //invalid webhook
            }
            $requestBody = json_decode($requestBody, true);
            $requestData = isset($requestBody['data']) ? $requestBody['data'] : [];
            if (isset($requestBody['type']) && isset($requestData['id'])) {
                $encoding = storage()->get('pf_video_' . $requestData['id']);
                if (!empty($encoding->value)) {
                    $encodingValue = $encoding->value;
                    if (empty($encoding->value->cancel_upload)) {
                        $videoPath = $imagePath = '';
                        if (!empty($requestData['playback_ids'])) {
                            $videoPath = $requestData['playback_ids'][0]['id'] . '.m3u8';
                            $imagePath = $requestData['playback_ids'][0]['id'] . '/thumbnail.png';
                        }
                        switch ($requestBody['type']) {
                            case 'video.asset.ready':
                                if (isset($requestData['tracks'])) {
                                    $videoTrack = array_filter($requestData['tracks'], function($track) {
                                        return isset($track['type']) && $track['type'] == 'video';
                                    });
                                    $videoTrack = !empty($videoTrack) ? end($videoTrack) : [];
                                }
                                $update = [
                                    'resolution_x' => isset($videoTrack['max_width']) ? $videoTrack['max_width'] : null,
                                    'resolution_y' => isset($videoTrack['max_height']) ? $videoTrack['max_height'] : null,
                                    'duration'     => isset($videoTrack['duration']) ? intval($videoTrack['duration']) : null,
                                    'playback_ids' => $requestData['playback_ids']
                                ];
                                if (!empty($encodingValue->updated_info)) {
                                    $userId = $encodingValue->user_id;
                                    $aVals = array(
                                        'privacy'          => $encodingValue->privacy,
                                        'privacy_list'     => json_decode($encodingValue->privacy_list),
                                        'callback_module'  => $encodingValue->callback_module,
                                        'callback_item_id' => $encodingValue->callback_item_id,
                                        'parent_user_id'   => $encodingValue->parent_user_id,
                                        'title'            => $encodingValue->title,
                                        'category'         => json_decode($encodingValue->category),
                                        'text'             => $encodingValue->text,
                                        'status_info'      => $encodingValue->status_info,
                                        'is_stream'        => 0,
                                        'view_id'          => $encodingValue->view_id,
                                        'user_id'          => $userId,
                                        'server_id'        => -3, //-3 is Mux
                                        'path'             => $videoPath,
                                        'ext'              => $encodingValue->ext,
                                        'default_image'    => $imagePath,
                                        'image_server_id'  => -3, //-3 is Mux
                                        'duration'         => $update['duration'],
                                        'video_size'       => $encodingValue->video_size,
                                        'photo_size'       => null, //Mux doesn't return thumbnail size
                                        'feed_values'      => isset($encodingValue->feed_values) ? json_decode($encodingValue->feed_values) : [],
                                        'location_name'    => $encodingValue->location_name,
                                        'location_latlng'  => $encodingValue->location_latlng,
                                        'tagged_friends'   => $encodingValue->tagged_friends,
                                        'resolution_x'     => $update['resolution_x'],
                                        'resolution_y'     => $update['resolution_y'],
                                        'is_scheduled'     => $encodingValue->is_scheduled,
                                        'asset_id'         => $requestData['id']
                                    );
                                    if (!defined('PHPFOX_FEED_NO_CHECK')) {
                                        define('PHPFOX_FEED_NO_CHECK', true);
                                    }
                                    if (empty($encodingValue->is_scheduled)) {
                                        $iId = Phpfox::getService('v.process')->addVideo($aVals);

                                        if (Phpfox::isModule('notification')) {
                                            Phpfox::getService('notification.process')->add('v_ready', $iId, $userId,
                                                $userId, true);
                                        }

                                        $sTitle = (!empty($aVals['title']) ? Phpfox::getLib('parse.output')->clean($aVals['title'], 255) : _p('untitled_video'));
                                        Phpfox::getLib('mail')->to($userId)
                                            ->subject(['email_your_video_title_is_ready', ['title' => $sTitle]])
                                            ->message(['your_video_title_is_ready_click_on_link', ['title' => $sTitle, 'link' => Phpfox::permalink('video.play', $iId, $sTitle)]])
                                            ->notification('v.email_notification')
                                            ->send();
                                    } else {
                                        $aVals['feed_values'] = (array)$aVals['feed_values'];
                                        Phpfox::getService('core.schedule')->redefineScheduleItem($encodingValue->schedule_id, $aVals);
                                    }
                                    //Unlink temp file
                                    $this->deleteTempVideo($encodingValue);
                                } else {
                                    storage()->update('pf_video_' . $requestData['id'], [
                                        'encoded'         => 1,
                                        'server_id'       => -3,
                                        'image_server_id' => -3,
                                        'duration'        => $update['duration'],
                                        'resolution_x'    => $update['resolution_x'],
                                        'resolution_y'    => $update['resolution_y'],
                                        'playback_ids'    => $update['playback_ids'],
                                        'default_image'   => $imagePath,
                                        'video_path'      => $videoPath,
                                        'asset_id'        => $requestData['id']
                                    ]);
                                    //Unlink temp file
                                    $this->deleteTempVideo($encodingValue, false, false);
                                }
                                break;
                            case 'video.asset.errored':
                            case 'video.asset.deleted':
                                $encoding = storage()->get('pf_video_' . $requestData['id']);
                                if (!empty($encoding->value)) {
                                    $encodingValue = $encoding->value;
                                    $this->deleteTempVideo($encodingValue, true);
                                }
                                break;
                        }
                    } else {
                        $this->deleteTempVideo($encodingValue, true);
                    }
                }
            }
        }
        exit;
    }

    protected function deleteTempVideo($encodingValue, $deleteOnMux = false, $deleteStorage = true)
    {
        $sPathVideo = Phpfox::getParam('core.dir_file') . 'video/' . sprintf($encodingValue->video_temp_path, '');
        Phpfox::getLib('file')->unlink($sPathVideo, $encodingValue->server_id);
        if ($deleteOnMux) {
            try {
                $config = MuxPhp\Configuration::getDefaultConfiguration()
                    ->setUsername(setting('pf_video_mux_token_id'))
                    ->setPassword(setting('pf_video_mux_token_secret'));
                // API Client Initialization
                $assetsApi = new MuxPhp\Api\AssetsApi(new GuzzleHttp\Client(), $config);
                $assetsApi->deleteAsset($encodingValue->encoding_id);
            } catch (MuxPhp\ApiException $e) {
                Phpfox::getLog('mux.log')->error('Error Delete Temp Video On Mux', [$e->getMessage()]);
            }
        }
        if ($deleteStorage) {
            storage()->del('pf_video_' . $encodingValue->encoding_id);
        }
        return true;
    }
}