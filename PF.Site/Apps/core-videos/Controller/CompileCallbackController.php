<?php

namespace Apps\PHPfox_Videos\Controller;

use Phpfox;
use Phpfox_Component;

class CompileCallbackController extends Phpfox_Component
{
    public function process()
    {
        //Handle callback
        $data = $_POST;
        if (empty($data)) {
            echo json_encode([
                'action' => 'skip'
            ]);
            exit;
        }
        if (defined('PHPFOX_DEBUG') && PHPFOX_DEBUG) {
            Phpfox::getLog('ffmpeg-callback.log')->info('Callback from FFMPEG server', $data);
        }
        if (!empty($data['get_storage'])) {
            //Get storage system
            $aItems = $this->loadStorageConfigs();
            $aDefaultStorage = array_filter($aItems, function ($aItem) {
               return !empty($aItem['is_default']);
            });
            $aDefaultStorage = array_values($aDefaultStorage);
            echo json_encode(isset($aDefaultStorage[0]) ? $aDefaultStorage[0] : ['storage_id' => 0, 'service_id' => 'local']);
            exit;
        }
        if (!empty($data['check_only'])) {
            $storage = storage()->get('pf_video_' . $data['title']);
            $encodingValue = $storage->value;
            if (empty($encodingValue)) {
                $action = 'delete';
            } elseif (!empty($encodingValue->uploading)) {
                $action = 'skip';
            } else {
                $time = isset($encodingValue->time_stamp) ? $encodingValue->time_stamp : 0;
                if (!$encodingValue->is_ready) {
                    if ($time && (time() - $time) > 10800) {
                        //If video uploaded at least 3h but not saved > Delete it
                        $action = 'delete';
                        storage()->del('pf_video_' . $data['title']);
                    } else {
                        $action = 'skip';
                    }
                } else {
                    $action = 'convert';
                }
            }
            echo json_encode([
                'action' => $action
            ]);
            exit;
        }
        if (isset($data['status'])) {
            if ($data['status'] == 0) {
                //Success
                $storage = storage()->get('pf_video_' . $data['title']);
                $encodingValue = $storage->value;
                if (!empty($encodingValue)) {
                    $time = isset($encodingValue->time_stamp) ? $encodingValue->time_stamp : 0;
                    if (!$encodingValue->is_ready) {
                        if ($time && (time() - $time) < 10800) {
                            //If video uploaded at least 3h but not saved > Delete it
                            $action = 'delete';
                            storage()->del('pf_video_' . $data['title']);
                        } else {
                            //Video not save
                            $action = 'skip';
                        }
                        echo json_encode([
                            'action' => $action
                        ]);
                        exit;
                    }
                } else {
                    echo json_encode([
                        'action' => 'delete'
                    ]);
                    exit;
                }
                $userId = $encodingValue->user_id;
                $aVals = [
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
                    'server_id'        => 0,
                    'path'             => $data['video_path'],
                    'ext'              => $encodingValue->ext,
                    'image_path'       => $data['image_path'],
                    'image_server_id'  => 0,
                    'duration'         => $data['duration'],
                    'video_size'       => $data['video_size'],
                    'photo_size'       => $data['photo_size'],
                    'feed_values'      => isset($encodingValue->feed_values) ? json_decode($encodingValue->feed_values) : [],
                    'location_name'    => $encodingValue->location_name,
                    'location_latlng'  => $encodingValue->location_latlng,
                    'tagged_friends'   => $encodingValue->tagged_friends,
                    'resolution_x'     => $data['resolution_x'],
                    'resolution_y'     => $data['resolution_y'],
                ];
                $serverId = Phpfox::getLib('storage')->getStorageId();
                if ($serverId) {
                    $aVals['server_id'] = $serverId;
                    $aVals['image_server_id'] = $serverId;
                }
                if (!defined('PHPFOX_FEED_NO_CHECK')) {
                    define('PHPFOX_FEED_NO_CHECK', true);
                }

                // try to reconnect for a long task.
                Phpfox::getLib('database')->reconnect();

                if (empty($encodingValue->is_scheduled)) {
                    $iId = Phpfox::getService('v.process')->addVideo($aVals);

                    if (Phpfox::isModule('notification')) {
                        Phpfox::getService('notification.process')->add('v_ready', $iId, $userId, $userId, true);
                    }

                    $sTitle = (!empty($aVals['title']) ? Phpfox::getLib('parse.output')->clean($aVals['title'], 255) : _p('untitled_video'));
                    Phpfox::getLib('mail')->to($userId)
                        ->subject(['email_your_video_title_is_ready', ['title' => $sTitle]])
                        ->message(['your_video_title_is_ready_click_on_link', ['title' => $sTitle, 'link' => Phpfox::permalink('video.play', $iId, $sTitle)]])
                        ->notification('v.email_notification')
                        ->send();
                } else {
                    Phpfox::getService('core.schedule')->redefineScheduleItem($encodingValue->schedule_id, $aVals);
                }

                storage()->del('pf_video_' . $data['title']);
            } else {
                //Failed
                storage()->del('pf_video_' . $data['title']);
            }
        }
        //Delete video if failed or success
        echo json_encode([
            'action' => 'delete'
        ]);
        exit;
    }

    private function loadStorageConfigsFromEnv()
    {
        $servers = Phpfox::getParam('core.storage_handling');
        if (empty($servers)) {
            return false;
        }
        $default_id = Phpfox::getParam('core.storage_default', '0');

        $configs = [
            '0' => [
                'storage_id'    => '0',
                'service_class' => 'Core\Storage\LocalAdapter',
                'service_id'    => 'local',
                'is_default'    => $default_id == '0',
                'config'        => json_encode([
                    'storage_id' => '0'
                ])
            ]
        ];
        $availableStorageServices = $this->loadAvailableStorageServices();
        foreach ($servers as $server_id => $config) {
            if (!$config || !$config['driver'] || !$availableStorageServices[$config['driver']]) {
                continue;
            }

            $configs[$server_id] = [
                'storage_id'    => $server_id,
                'service_class' => $availableStorageServices[$config['driver']],
                'service_id'    => $config['driver'],
                'is_default'    => $default_id == $server_id,
                'config'        => json_encode($config)
            ];
        }
        return $configs;
    }

    private function loadAvailableStorageServices()
    {
        $results = [
            'local' => 'Core\Storage\LocalAdapter'
        ];
        foreach (db()->select('s.service_class, s.service_id')
                     ->from(':core_storage_service', 's')
                     ->execute('getSlaveRows') as $row) {
            $results[$row['service_id']] = $row['service_class'];
        }

        return $results;
    }
    private function loadStorageConfigs()
    {
        $configs = $this->loadStorageConfigsFromEnv();
        if (!$configs) {
            $cache = Phpfox::getLib('cache');
            $sCacheId = $cache->set('pf_video_storage_configs');

            $configs = $cache->getLocalFirst($sCacheId);
            if (!$configs) {
                $configs = $this->loadStorageConfigsFromDatabase();
            }
            $cache->saveBoth($sCacheId, $configs);
        }
        return $configs;
    }

    private function loadStorageConfigsFromDatabase()
    {
        $configs = [];
        $configs['0'] = [
            'storage_id'    => '0',
            'service_class' => 'Core\Storage\LocalAdapter',
            'service_id'    => 'local',
            'is_default'    => false,
            'config'        => json_encode([
                'storage_id' => '0'
            ])
        ];
        $rows = db()->select('d.*, s.edit_link, s.service_class, s.service_phrase_name')
            ->from(':core_storage', 'd')
            ->join(':core_storage_service', 's', 's.service_id=d.service_id')
            ->where('d.is_active=1')
            ->execute('getSlaveRows');

        foreach ($rows as $row) {
            $storage_id = $row['storage_id'];
            $params['storage_id'] = $storage_id;
            $configs[$storage_id] = [
                'storage_id'    => $storage_id,
                'service_class' => $row['service_class'],
                'service_id'    => $row['service_id'],
                'is_default'    => !!$row['is_default'],
                'config'        => $row['config'],
            ];
        }
        return $configs;
    }
}