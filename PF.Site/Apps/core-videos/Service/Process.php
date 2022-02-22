<?php

namespace Apps\PHPfox_Videos\Service;

use Aws\S3\S3Client;
use GuzzleHttp;
use MuxPhp;
use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Process extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('video');
    }

    /**
     * START CATEGORIES PROCESS
     */

    /**
     * @param $aVals
     *
     * @return bool|int
     * @throws \Exception
     */
    public function addCategory($aVals)
    {
        //Add phrase for category
        $aLanguages = Phpfox::getService('language')->getAll();
        $name = $aVals['name_' . $aLanguages[0]['language_id']];
        $phrase_var_name = 'video_category_' . md5('Video Category' . $name . PHPFOX_TIME);

        //Add phrases
        $aText = [];
        foreach ($aLanguages as $aLanguage) {
            if (isset($aVals['name_' . $aLanguage['language_id']]) && !empty($aVals['name_' . $aLanguage['language_id']])) {
                $aText[$aLanguage['language_id']] = $aVals['name_' . $aLanguage['language_id']];
            } else {
                return Phpfox_Error::set((_p('provide_a_language_name_name',
                    ['language_name' => $aLanguage['title']])));
            }
        }
        $aValsPhrase = [
            'product_id' => 'phpfox',
            'module' => 'video',
            'var_name' => $phrase_var_name,
            'text' => $aText
        ];
        $finalPhrase = Phpfox::getService('language.phrase.process')->add($aValsPhrase);

        $iId = db()->insert(Phpfox::getT('video_category'), [
            'name' => $finalPhrase,
            'time_stamp' => PHPFOX_TIME,
            'ordering' => '0',
            'parent_id' => (!empty($aVals['parent_id']) ? (int)$aVals['parent_id'] : 0),
            'is_active' => '1'
        ]);
        Phpfox::getLib('cache')->removeGroup('video');
        return $iId;
    }

    /**
     * @param       $iCategoryId
     * @param array $aVals
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteCategory($iCategoryId, $aVals = [])
    {
        $aCategory = db()->select('*')
            ->from(Phpfox::getT('video_category'))
            ->where('category_id=' . intval($iCategoryId))
            ->execute('getSlaveRow');

        // Delete phrase of category
        if (isset($aCategory['name']) && Phpfox::isPhrase($aCategory['name'])) {
            Phpfox::getService('language.phrase.process')->delete($aCategory['name'], true);
        }

        if ($aVals && isset($aVals['delete_type'])) {
            switch ($aVals['delete_type']) {
                case 1:
                    $aSubs = db()->select('vc.category_id')
                        ->from(':video_category', 'vc')
                        ->where('vc.parent_id = ' . intval($iCategoryId))
                        ->execute('getSlaveRows');
                    $sCategoryIds = $iCategoryId;
                    foreach ($aSubs as $key => $aSub) {
                        $sCategoryIds .= ',' . $aSub['category_id'];
                    }
                    $aItems = db()->select('vcd.video_id')
                        ->from(':video_category_data', 'vcd')
                        ->where("vcd.category_id IN (" . $sCategoryIds . ')')
                        ->execute('getSlaveRows');
                    foreach ($aItems as $aItem) {
                        $iVideoId = $aItem['video_id'];
                        $this->delete($iVideoId);
                    }
                    db()->delete(':video_category', 'parent_id = ' . intval($iCategoryId));
                    break;
                case 2:
                    if (!empty($aVals['new_category_id'])) {
                        $aItems = db()->select('d.video_id')
                            ->from(Phpfox::getT('video_category_data'), 'd')
                            ->where("d.category_id = " . intval($iCategoryId))
                            ->execute('getSlaveRows');
                        foreach ($aItems as $aItem) {
                            $iVideoId = $aItem['video_id'];
                            db()->delete(Phpfox::getT('video_category_data'),
                                'category_id = ' . intval($aVals['new_category_id']) . ' AND video_id = ' . intval($iVideoId));
                        }
                        db()->update(Phpfox::getT('video_category_data'),
                            ['category_id' => intval($aVals['new_category_id'])],
                            'category_id = ' . intval($iCategoryId));
                        db()->update(':video_category', ['parent_id' => $aVals['new_category_id']],
                            'parent_id = ' . intval($iCategoryId));
                    }
                    break;
                default:
                    break;
            }
        }

        db()->delete(Phpfox::getT('video_category'), 'category_id = ' . intval($iCategoryId));
        Phpfox::getLib('cache')->removeGroup('video');

        return true;
    }

    /**
     * @param $iId
     * @param $aVals
     *
     * @return bool
     * @throws \Exception
     */
    public function updateCategory($iId, $aVals)
    {
        $aLanguages = Phpfox::getService('language')->getAll();
        if (Phpfox::isPhrase($aVals['name'])) {
            foreach ($aLanguages as $aLanguage) {
                if (isset($aVals['name_' . $aLanguage['language_id']])) {
                    $name = $aVals['name_' . $aLanguage['language_id']];
                    Phpfox::getService('language.phrase.process')->updateVarName($aLanguage['language_id'],
                        $aVals['name'], $name);
                }
            }
        } else {
            //Add new phrase if before is not phrase
            $name = $aVals['name_' . $aLanguages[0]['language_id']];
            $phrase_var_name = 'video_category_' . md5('Video Category' . $name . PHPFOX_TIME);
            $aText = [];
            foreach ($aLanguages as $aLanguage) {
                if (isset($aVals['name_' . $aLanguage['language_id']]) && !empty($aVals['name_' . $aLanguage['language_id']])) {
                    $aText[$aLanguage['language_id']] = $aVals['name_' . $aLanguage['language_id']];
                } else {
                    Phpfox_Error::set((_p('provide_a_language_name_name',
                        ['language_name' => $aLanguage['title']])));
                }
            }
            $aValsPhrase = [
                'product_id' => 'phpfox',
                'module' => 'video',
                'var_name' => $phrase_var_name,
                'text' => $aText
            ];
            $aVals['name'] = Phpfox::getService('language.phrase.process')->add($aValsPhrase);
        }

        db()->update(Phpfox::getT('video_category'), [
            'parent_id' => (!empty($aVals['parent_id']) ? (int)$aVals['parent_id'] : 0),
            'name' => $aVals['name'],
        ], 'category_id = ' . (int)$iId
        );
        Phpfox::getLib('cache')->removeGroup('video');

        return true;
    }

    /**
     * @param $iId
     * @param $iType
     */
    public function updateCategoryActivity($iId, $iType)
    {
        Phpfox::isAdmin(true);
        db()->update((Phpfox::getT('video_category')), ['is_active' => (int)($iType == '1' ? 1 : 0)],
            'category_id = ' . (int)$iId);
        // clear cache
        Phpfox::getLib('cache')->removeGroup('video');
    }

    private function _getDefaultItemPrivacy($callbackModule = null, $callbackItemId = null)
    {
        if (!empty($callbackModule) && !empty($callbackItemId)) {
            if (in_array($callbackModule, ['pages', 'groups']) || !Phpfox::hasCallback($callbackModule, 'getDefaultItemPrivacy')) {
                $videoPrivacy = 0;
            } else {
                $videoPrivacy = Phpfox::callback($callbackModule . '.getDefaultItemPrivacy', [
                    'parent_id' => $callbackItemId,
                    'item_type' => 'v',
                ]);
            }
        } else {
            $videoPrivacy = Phpfox::getParam('core.friends_only_community') ? (Phpfox::isModule('friend') ? '1' : '3') : '0';
        }

        return $videoPrivacy;
    }

    /**
     * VIDEO PROCESS
     */
    /**
     * @param $aVals
     * @return int
     * @throws \Exception
     */
    public function addVideo($aVals)
    {
        if (!defined('PHPFOX_FORCE_IFRAME')) {
            define('PHPFOX_FORCE_IFRAME', true);
        }
        $aCategories = [];
        if (!empty($aVals['category'])) {
            foreach ($aVals['category'] as $iCategory) {
                if (empty($iCategory)) {
                    continue;
                }
                if (!is_numeric($iCategory)) {
                    continue;
                }
                $aCategories[] = $iCategory;
            }
        }

        if (!empty($aVals['url']) && !Phpfox::getService('link')->getLink($aVals['url'])) {
            return Phpfox_Error::set(_p('unable_to_embed_this_video_due_to_privacy_settings'));
        }

        if ($sPlugin = Phpfox_Plugin::get('video.service_process_addvideo__start')) {
            eval($sPlugin);
        }

        if (isset($aVals['privacy'])) {
            $videoPrivacy = $aVals['privacy'];
        } else {
            $videoPrivacy = $this->_getDefaultItemPrivacy(!empty($aVals['callback_module']) ? $aVals['callback_module'] : null,
                !empty($aVals['callback_item_id']) ? $aVals['callback_item_id'] : null);
        }

        Phpfox::getService('ban')->checkAutomaticBan((isset($aVals['title']) ? $aVals['title'] : '' . isset($aVals['text'])) ? $aVals['text'] : '');

        $sModule = 'video';
        $iPageUserId = 0;
        $iItem = 0;
        $aCallback = null;
        $iUserId = (!empty($aVals['user_id']) ? $aVals['user_id'] : Phpfox::getUserId());

        if (!empty($aVals['callback_module'])) {
            if(Phpfox::hasCallback($aVals['callback_module'], 'uploadVideo')) {
                $aCallback = Phpfox::callback($aVals['callback_module'] . '.uploadVideo', $aVals);
                $sModule = $aCallback['module'];
                $iItem = $aCallback['item_id'];
            }
            elseif (!empty($aVals['callback_item_id'])) {
                $sModule = $aVals['callback_module'];
                $iItem = $aVals['callback_item_id'];
            }
            if (in_array($sModule, ['pages', 'groups'])) {
                $iPageUserId = Phpfox::getService('v.video')->getPageUserId($iItem);
            }
            //post in pages/groups doesn't have parent_user_id
            if (isset($aVals['parent_user_id'])) {
                unset($aVals['parent_user_id']);
            }
        } elseif (!empty($aVals['parent_user_id'])) {
            $sModule = 'user';
            $iItem = $aVals['parent_user_id'];
        }

        $iViewId = ((isset($aVals['view_id']) ? $aVals['view_id'] : Phpfox::getUserParam('pf_video_approve_before_publicly')) ? 2 : 0);
        $aSql = [
            'is_stream' => (isset($aVals['is_stream']) ? $aVals['is_stream'] : 1),
            'view_id' => $iViewId,
            'module_id' => $sModule,
            'item_id' => (int)$iItem,
            'page_user_id' => (int)$iPageUserId,
            'privacy' => $videoPrivacy,
            'privacy_comment' => 0,
            'user_id' => $iUserId,
            'parent_user_id' => isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0,
            'time_stamp' => isset($aVals['schedule_timestamp']) ? $aVals['schedule_timestamp'] : PHPFOX_TIME,
            'location_name' => (!empty($aVals['location_name'])) ? $aVals['location_name'] : null,
            'location_latlng' => (!empty($aVals['location_latlng'])) ? $aVals['location_latlng'] : null,
            'tagged_friends' => (!empty($aVals['tagged_friends'])) ? $aVals['tagged_friends'] : null,
            'resolution_x' => (!empty($aVals['resolution_x'])) ? $aVals['resolution_x'] : null,
            'resolution_y' => (!empty($aVals['resolution_y'])) ? $aVals['resolution_y'] : null,
            'asset_id' => (!empty($aVals['asset_id'])) ? $aVals['asset_id'] : null,
            'video_size' => isset($aVals['video_size']) ? $aVals['video_size'] : 0
        ];

        $aSql['title'] = (!empty($aVals['title']) ? $this->preParse()->clean($aVals['title'],
            255) : _p('untitled_video'));
        $aSql['duration'] = (isset($aVals['duration']) ? $aVals['duration'] : 0);
        $aSql['status_info'] = (isset($aVals['status_info']) ? $aVals['status_info'] : '');

        $iId = db()->insert($this->_sTable, $aSql);

        if (!$iId) {
            return false;
        }

        $aUpdate = [];
        if (!empty($aVals['default_image'])) {
            if (isset($aVals['image_server_id']) && in_array($aVals['image_server_id'], [-1, -3])) {
                $aUpdate['image_path'] = $aVals['default_image'];
                $aUpdate['image_server_id'] = $aVals['image_server_id'];
            } else {
                // is dailymotion
                if (strpos($aVals['default_image'], 'dailymotion.com/thumbnail')) {
                    $aUpdate['image_path'] = $aVals['default_image'];
                    $aUpdate['image_server_id'] = -2;
                } else {
                    list($sImagePath, $iPhotoSize) = $this->downloadImage($aVals['default_image']);
                    $aVals['photo_size'] = $iPhotoSize;
                    $aUpdate['image_path'] = $sImagePath;
                    $aUpdate['image_server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
                }
            }
        } else {
            if (!empty($aVals['image_path'])) {
                $aUpdate['image_path'] = $aVals['image_path'];
                $aUpdate['image_server_id'] = $aVals['image_server_id'];
            }
        }

        if (!empty($aVals['path'])) {
            $aUpdate['destination'] = $aVals['path'];
            $aUpdate['server_id'] = isset($aVals['server_id']) ? $aVals['server_id'] : Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            $aUpdate['file_ext'] = isset($aVals['ext']) ? $aVals['ext'] : '';
        }

        if (count($aUpdate)) {
            db()->update($this->_sTable, $aUpdate, 'video_id = ' . $iId);
        }

        if (!empty($aVals['embed_code'])) {
            db()->insert(Phpfox::getT('video_embed'), [
                    'video_id' => $iId,
                    'video_url' => $aVals['url'],
                    'embed_code' => $aVals['embed_code']
                ]
            );
        }

        $sDescription = isset($aVals['text']) ? $aVals['text'] : '';
        db()->insert(Phpfox::getT('video_text'), [
                'video_id' => $iId,
                'text' => $this->preParse()->clean($sDescription),
                'text_parsed' => $this->preParse()->prepare($sDescription)
            ]
        );

        // hash tag in description
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            if (!empty($sDescription)) {
                Phpfox::getService('tag.process')->add('v', $iId, $iUserId, $sDescription, true);
            }
            if (!empty($aVals['status_info'])) {
                Phpfox::getService('tag.process')->add('v', $iId, $iUserId, $aVals['status_info'], true);
            }
        }

        if (count($aCategories)) {
            foreach ($aCategories as $iCategoryId) {
                db()->insert(Phpfox::getT('video_category_data'), ['video_id' => $iId, 'category_id' => $iCategoryId]);
            }
        }

        $aCallback = null;
        if ($sModule != 'video' && Phpfox::hasCallback($sModule, 'getFeedDetails')) {
            $aCallback = Phpfox::callback($sModule . '.getFeedDetails', $iId);
        }

        $iFeedId = 0;
        if (Phpfox::isModule('feed') && !defined('PHPFOX_SKIP_FEED_ENTRY') && ($iViewId == 0) && Phpfox::getParam('v.pf_video_allow_create_feed_when_add_new_item', 1)) {
            $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('v', $iId, $videoPrivacy, 0,
                ($aCallback === null ? (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0) : $aVals['callback_item_id']), $iUserId, false, 0, null, false, $aSql['time_stamp']);
        }

        // notification to tagged and mentioned friends
        $this->notifyTaggedInFeed($aVals['status_info'], $iId, $iUserId, $iFeedId, $aVals['tagged_friends'], $videoPrivacy, (isset($aVals['parent_user_id']) ? (int)$aVals['parent_user_id'] : 0), $sModule);

        if (Phpfox::isModule('notification') && $sModule != 'video' && $iItem && Phpfox::isModule($sModule) && Phpfox::hasCallback($sModule, 'addItemNotification') && ($iViewId == 0)) {
            Phpfox::callback($sModule . '.addItemNotification', [
                'page_id' => $iItem,
                'item_perm' => 'pf_video.view_browse_videos',
                'item_type' => 'v',
                'item_id' => $iId,
                'owner_id' => $iUserId,
                'items_phrase' => 'videos__l',
            ]);
        }

        if ($iItem && ($iViewId == 0)) {
            if ($sModule == 'user') {
                if (Phpfox::isModule('notification')) {
                    Phpfox::getService('notification.process')->add('v_newItem_wall', $iId, $iItem, $iUserId);
                }
                // send mail
                list(, $link) = Phpfox::getService('v.video')->getFeedLink($iId);
                $aOwnerUser = Phpfox::getService('user')->getUser($iUserId);
                $sOwner = (isset($aOwnerUser['full_name']) && $aOwnerUser['full_name']) ? $aOwnerUser['full_name'] : $aOwnerUser['user_name'];
                Phpfox::getLib('mail')->to($iItem)
                    ->subject([
                        'full_name_posted_a_video_on_your_wall', ['full_name' => $sOwner]
                    ])
                    ->message([
                        'full_name_posted_a_video_on_your_wall_message', ['full_name' => $sOwner, 'link' => $link]
                    ])
                    ->notification('comment.add_new_comment')
                    ->send();
            } elseif ($sModule == 'pages') {
                $iPageOwnerId = db()->select('user_id')->from(Phpfox::getT('pages'))->where('page_id = ' . (int)$iItem)->execute('getSlaveField');
                if ($iPageOwnerId) {
                    Phpfox::getService('notification.process')->add('v_newItem_pages', $iId, $iPageOwnerId, $iUserId);
                }
            }
        }

        // Update user space usage
        if (isset($aVals['photo_size']) && $aVals['photo_size'] > 0) {
            Phpfox::getService('user.space')->update($iUserId, 'photo', $aVals['photo_size']);
        }
        if (isset($aVals['video_size']) && $aVals['video_size'] > 0) {
            Phpfox::getService('user.space')->update($iUserId, 'video', $aVals['video_size']);
        }

        // Update user activity
        ($iViewId == 0) && Phpfox::getService('user.activity')->update($iUserId, 'v');

        if (Phpfox::isModule('privacy') && $videoPrivacy == '4') {
            Phpfox::getService('privacy.process')->add('v', $iId,
                (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
        }

        if (($iViewId == 0) && !empty($sModule) && Phpfox::hasCallback($sModule, 'onVideoPublished')) {
            Phpfox::callback($sModule . '.onVideoPublished', [
                'video_id' => $iId,
                'item_id' => $aSql['item_id'],
                'user_id' => $aSql['user_id']
            ]);
        }

        // Plugin call
        if ($sPlugin = Phpfox_Plugin::get('video.service_process_addvideo__end')) {
            eval($sPlugin);
        }

        return $iId;
    }

    /**
     * @param $iId
     * @param $aVals
     * @return bool
     * @throws \Exception
     */
    public function update($iId, $aVals)
    {
        $aCategories = [];
        if (isset($aVals['category']) && count($aVals['category'])) {
            foreach ($aVals['category'] as $iCategory) {
                if (empty($iCategory)) {
                    continue;
                }
                if (!is_numeric($iCategory)) {
                    continue;
                }
                $aCategories[] = $iCategory;
            }
        }

        $aVideo = db()->select('v.video_id, v.privacy, v.view_id, v.user_id, v.is_featured, v.is_sponsor, v.status_info')
            ->from($this->_sTable, 'v')
            ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
            ->where('v.video_id = ' . (int)$iId)
            ->execute('getRow');

        if (!isset($aVideo['video_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_video_you_plan_to_edit'));
        }

        Phpfox::getService('ban')->checkAutomaticBan((isset($aVals['title']) ? $aVals['title'] : '' . isset($aVals['text'])) ? $aVals['text'] : '');

        if (($aVideo['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('pf_video_edit_own_video')) || Phpfox::getUserParam('pf_video_edit_all_video')) {
            if (Phpfox::getLib('parse.format')->isEmpty($aVals['title'])) {
                return Phpfox_Error::set(_p('provide_a_title_for_this_video'));
            }

            $aSql = [
                'title' => $this->preParse()->clean($aVals['title'], 255)
            ];

            if (isset($aVals['privacy'])) {
                $aSql['privacy'] = (int)$aVals['privacy'];
            } else {
                $aVals['privacy'] = $aVideo['privacy'];
            }

            if (isset($aVals['parent_user_id'])) {
                $aSql['parent_user_id'] = $aVals['parent_user_id'];
            }

            /*
             *  Remove video's thumbnail when
             *  1. upload new thumbnail, remove old.
             *  2. just remove thumbnail.
            */
            if (!empty($aVals['temp_file']) || !empty($aVals['remove_photo'])) {
                $this->deleteThumbnail($aVideo);
                $aSql['image_path'] = null;
                $aSql['image_server_id'] = 0;
            }

            // if user edit video's thumbnail image
            if (!empty($aVals['temp_file'])) {
                // get image from temp file
                $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
                if (!empty($aFile)) {
                    if (!Phpfox::getService('user.space')->isAllowedToUpload($aVideo['user_id'], $aFile['size'])) {
                        Phpfox::getService('core.temp-file')->delete($aVals['temp_file'], true);

                        return false;
                    }
                    $aSql['image_path'] = $aFile['path'];
                    $aSql['image_server_id'] = $aFile['server_id'];
                    Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
                }
            }

            // update video
            db()->update($this->_sTable, $aSql, 'video_id = ' . $iId);

            db()->update(Phpfox::getT('video_text'), [
                'text' => (empty($aVals['text']) ? null : $this->preParse()->clean($aVals['text'])),
                'text_parsed' => (empty($aVals['text']) ? null : $this->preParse()->prepare($aVals['text']))
            ], 'video_id = ' . $iId
            );

            // hash tag in description
            $this->updateHashtag($iId, $aVideo, $aVals['text']);

            db()->delete(Phpfox::getT('video_category_data'), 'video_id = ' . (int)$iId);
            if (count($aCategories)) {
                foreach ($aCategories as $iCategoryId) {
                    db()->insert(Phpfox::getT('video_category_data'),
                        ['video_id' => $iId, 'category_id' => $iCategoryId]);
                }
            }

            if (Phpfox::isModule('feed')) {
                $feedProcess = Phpfox::getService('feed.process');
                $feedProcess->update('v', $iId, $aVals['privacy']);
                $feedProcess->clearCache('v', $iId);
            }

            if (Phpfox::isModule('privacy')) {
                if ($aVals['privacy'] == '4') {
                    Phpfox::getService('privacy.process')->update('v', $iId,
                        (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
                } else {
                    Phpfox::getService('privacy.process')->delete('v', $iId);
                }
            }

            if ($aVideo['is_sponsor'] == 1) {
                $this->cache()->remove('video_sponsored');
            }
            if ($aVideo['is_featured'] == 1) {
                $this->cache()->remove('video_featured');
            }

            if ($sPlugin = Phpfox_Plugin::get('video.service_process_update_1')) {
                eval($sPlugin);
            }

            return true;
        }

        return Phpfox_Error::set(_p('invalid_permissions'));
    }

    /**
     * @param $sContent
     * @param $iItemId
     * @param $iOwnerId
     * @param int $iFeedId
     * @param string $taggedFriends
     * @param int $iPrivacy
     * @param int $iParentUserId
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
        return Phpfox::getService('feed.tag')->updateFeedTaggedUsers([
            'feed_type' => 'v',
            'content' => $sContent,
            'owner_id' => $iOwnerId,
            'privacy' => $iPrivacy,
            'tagged_friend' => $aTagged,
            'item_id' => $iItemId,
            'feed_id' => $iFeedId,
            'parent_user_id' => $iParentUserId,
            'module_id' => $moduleId
        ]);
    }

    /**
     * Delete video's thumbnail
     * @param $aVideo
     * @return bool
     */
    public function deleteThumbnail($aVideo)
    {
        if (empty($aVideo['image_path'])) {
            return false;
        }

        if ($aVideo['image_server_id'] == -1) {
            // delete thumbnail image from Amazon S3
            $this->deleteThumbnailFromS3($aVideo);
        } elseif ($aVideo['image_server_id'] != -3) {
            $this->deleteThumbnailFromServer($aVideo);
        }

        return true;
    }

    /**
     * Delete video's thumbnails from server/cdn
     * @param $aVideo
     * @return bool
     */
    public function deleteThumbnailFromServer($aVideo)
    {
        if (!$aVideo['image_path']) {
            return true;
        }

        $aParams = Phpfox::getService('v.video')->getUploadPhotoParams();
        $aParams['type'] = 'v_edit_video';
        $aParams['path'] = $aVideo['image_path'];
        $aParams['user_id'] = $aVideo['user_id'];
        $aParams['update_space'] = true;
        $aParams['server_id'] = $aVideo['image_server_id'];

        return Phpfox::getService('user.file')->remove($aParams);
    }

    /**
     * Delete thumbnails from AmazonS3
     * @param $aVideo
     * @return bool
     */
    public function deleteThumbnailFromS3($aVideo)
    {
        if (!setting('pf_video_s3_url', false)) {
            return false;
        }

        $aHeaders = get_headers(setting('pf_video_s3_url') . $aVideo['image_path'], true);
        if (preg_match('/200 OK/i', $aHeaders[0])) {
            $iFileSize = (int)$aHeaders["Content-Length"];
            $iFileSize > 0 && Phpfox::getService('user.space')->update($aVideo['user_id'], 'photo', $iFileSize, '-');
        }
        $sPath = str_replace('.png/frame_0001.png', '', $aVideo['image_path']);
        $oClient = new S3Client([
            'region' => setting('pf_video_s3_region', 'us-east-2'),
            'version' => 'latest',
            'credentials' => [
                'key' => setting('pf_video_s3_key'),
                'secret' => setting('pf_video_s3_secret'),
            ],
        ]);
        foreach ([
                     '.png/frame_0000.png',
                     '.png/frame_0001.png',
                     '.png/frame_0002.png'
                 ] as $ext) {
            $oClient->deleteObject([
                'Bucket' => setting('pf_video_s3_bucket'),
                'Key' => $sPath . $ext
            ]);
        }

        return true;
    }

    /**
     * @param $iId
     * @param $iType
     * @return bool|mixed
     * @throws \Exception
     */
    public function sponsor($iId, $iType)
    {
        if (!Phpfox::getUserParam('v.can_sponsor_v') && !Phpfox::getUserParam('v.can_purchase_sponsor') && !defined('PHPFOX_API_CALLBACK')) {
            return Phpfox_Error::set(_p('hack_attempt'));
        }

        $iType = (int)$iType;
        if ($iType != 1 && $iType != 0) {
            return false;
        }
        db()->query("UPDATE " . Phpfox::getT('video') . " SET is_sponsor = " . $iType . " WHERE video_id = " . (int)$iId);
        $this->cache()->remove('video_sponsored');
        if ($sPlugin = Phpfox_Plugin::get('video.service_process_sponsor__end')) {
            return eval($sPlugin);
        }

        return true;
    }

    /**
     * @param null $iId
     * @param string $sView
     * @param int $iUserId
     * @param bool $bShowError
     * @param bool $bForce
     * @return bool|string
     * @throws \Exception
     */
    public function delete($iId = null, $sView = '', $iUserId = 0, $bShowError = true, $bForce = false)
    {
        $aVideo = db()->select('v.video_id, v.module_id, v.item_id, v.user_id, v.destination, v.image_path, v.server_id, v.image_server_id, v.is_sponsor, v.is_featured, v.view_id, v.asset_id, v.video_size')
            ->from($this->_sTable, 'v')
            ->where(($iId === null ? 'v.view_id = 1 AND v.user_id = ' . Phpfox::getUserId() : 'v.video_id = ' . (int)$iId))
            ->execute('getSlaveRow');

        if (!isset($aVideo['video_id'])) {
            if ($bShowError && !$bForce) {
                return Phpfox_Error::set(_p('unable_to_find_the_video_you_plan_to_delete'));
            } else {
                return false;
            }
        }

        // check current page to redirect when delete success
        $sParentReturn = true;
        if ($aVideo['module_id'] == 'pages' && (Phpfox::getService('pages')->isAdmin($aVideo['item_id']) || Phpfox::getUserParam('v.pf_video_delete_all_video'))) {
            $sParentReturn = Phpfox::getService('pages')->getUrl($aVideo['item_id']) . 'video/';
            $bForce = true; // is owner of page
        } elseif ($aVideo['module_id'] == 'groups' && (Phpfox::getService('groups')->isAdmin($aVideo['item_id']) || Phpfox::getUserParam('v.pf_video_delete_all_video'))) {
            $sParentReturn = Phpfox::getService('groups')->getUrl($aVideo['item_id']) . 'video/';
            $bForce = true; // is owner of group
        } elseif ($aVideo['module_id'] == 'user' && Phpfox::getUserId() == $aVideo['item_id']) {
            $sParentReturn = Phpfox::getService('user')->getLink($aVideo['item_id']);
            $bForce = true; // is owner of wall
        }
        if (!empty($sView)) {
            if ($sView != 'play' && $sView != 'profile') {
                $sParentReturn = Phpfox_Url::instance()->makeUrl('video', ['view' => $sView]);
            } elseif ($sView == 'profile' && $iUserId) {
                $sParentReturn = Phpfox::getService('user')->getLink($iUserId) . 'video/';
            }

        }

        // check permission delete video
        if (Phpfox::isUser(true) && (
                ($aVideo['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('pf_video_delete_own_video'))
                || Phpfox::getUserParam('pf_video_delete_all_video')
                || $bForce
            )
        ) {

            (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem(null,
                $aVideo['video_id'], 'v') : null);
            if (Phpfox::isModule('feed')) {
                $feedProcess = Phpfox::getService('feed.process');
                $feedProcess->delete('v', $aVideo['video_id']);
                $feedProcess->delete('v_comment', $aVideo['video_id']);
            }
            (Phpfox::isModule('like') ? Phpfox::getService('like.process')->delete('v', (int)$aVideo['video_id'], 0, true) : null);
            (Phpfox::isModule('notification') ? Phpfox::getService('notification.process')->deleteAllOfItem(['v_like', 'v_approved', 'v_ready', 'v_newItem_wall', 'v_newItem_pages'], (int)$aVideo['video_id']) : null);

            db()->delete(Phpfox::getT('video'), 'video_id = ' . $aVideo['video_id']);
            db()->delete(Phpfox::getT('video_category_data'), 'video_id = ' . $aVideo['video_id']);
            db()->delete(Phpfox::getT('video_text'), 'video_id = ' . $aVideo['video_id']);
            db()->delete(Phpfox::getT('video_embed'), 'video_id = ' . $aVideo['video_id']);
            db()->delete(Phpfox::getT('track'), 'item_id = ' . (int)$aVideo['video_id'] . ' AND type_id="v"');

            if ((int)$aVideo['view_id'] == 0) {
                // Update user activity
                Phpfox::getService('user.activity')->update($aVideo['user_id'], 'v', '-');
            }
            // remove images
            if (!empty($aVideo['image_path'])) {
                $iFileSize = 0;
                if ($aVideo['image_server_id'] == -1) {
                    $this->deleteThumbnailFromS3($aVideo);
                } elseif ($aVideo['image_server_id'] != -3) {
                    $aSizes = ['_500', '_1024']; // Sizes now defined
                    if (strpos($aVideo['image_path'], 'video/') !== 0) { // support V3 video
                        $aVideo['image_path'] = 'video/' . $aVideo['image_path'];
                        $aSizes = ['_120'];
                    }
                    // Foreach size
                    foreach ($aSizes as $sSize) {
                        // Get the possible image
                        $sImage = Phpfox::getParam('core.dir_pic') . sprintf($aVideo['image_path'], $sSize);
                        // if the image exists
                        if ($aVideo['image_server_id'] == 0 && file_exists($sImage)) {
                            $iFileSize += filesize($sImage);
                        } else {
                            if ($aVideo['image_server_id'] > 0) {
                                // Get the file size stored when the photo was uploaded
                                $sTempUrl = Phpfox::getLib('cdn')->getUrl(str_replace(Phpfox::getParam('core.dir_pic'),
                                    Phpfox::getParam('core.url_pic'), $sImage), $aVideo['image_server_id']);
                                $aHeaders = get_headers($sTempUrl, true);
                                if (preg_match('/200 OK/i', $aHeaders[0])) {
                                    $iFileSize += (int)$aHeaders["Content-Length"];
                                }
                            }
                        }
                        // Do not check if filesize is greater than 0, or CDN file will not be deleted
                        Phpfox::getLib('file')->unlink($sImage);
                    }
                }

                if ($iFileSize > 0) {
                    Phpfox::getService('user.space')->update($aVideo['user_id'], 'photo', $iFileSize, '-');
                }
            }
            // remove videos
            if (!empty($aVideo['destination'])) {
                $iFileSize = 0;
                if ($aVideo['server_id'] == -1) {
                    $aHeaders = get_headers(setting('pf_video_s3_url') . $aVideo['destination'], true);
                    if (preg_match('/200 OK/i', $aHeaders[0])) {
                        $iFileSize += (int)$aHeaders["Content-Length"];
                    }
                    $sPath = str_replace('.mp4', '', $aVideo['destination']);
                    $oClient = new S3Client([
                        'region' => setting('pf_video_s3_region', 'us-east-2'),
                        'version' => 'latest',
                        'credentials' => [
                            'key' => setting('pf_video_s3_key'),
                            'secret' => setting('pf_video_s3_secret'),
                        ],
                    ]);
                    foreach (['.webm', '-low.mp4', '.ogg', '.mp4'] as $ext) {
                        $oClient->deleteObject([
                            'Bucket' => setting('pf_video_s3_bucket'),
                            'Key' => $sPath . $ext
                        ]);
                    }
                } elseif ($aVideo['server_id'] == -3) {
                    if (!empty($aVideo['video_size'])) {
                        $iFileSize += (int)$aVideo['video_size'];
                    }
                    if (!empty($aVideo['asset_id'])) {
                        try {
                            $config = MuxPhp\Configuration::getDefaultConfiguration()
                                ->setUsername(setting('pf_video_mux_token_id'))
                                ->setPassword(setting('pf_video_mux_token_secret'));
                            // API Client Initialization
                            $assetsApi = new MuxPhp\Api\AssetsApi(new GuzzleHttp\Client(), $config);
                            $assetsApi->deleteAsset($aVideo['asset_id']);
                        } catch (MuxPhp\ApiException $e) {
                            Phpfox::getLog('mux.log')->error('Error Delete Video On Mux', [$e->getMessage()]);
                        }
                    }
                } else {
                    $sPathVideo = Phpfox::getParam('core.dir_file') . 'video/' . sprintf($aVideo['destination'], '');
                    if ($aVideo['server_id'] == 0 && file_exists($sPathVideo)) {
                        $iFileSize += filesize($sPathVideo);
                    } else {
                        if ($aVideo['server_id'] > 0) {
                            $sTempUrl = Phpfox::getLib('cdn')->getUrl(Phpfox::getParam('core.url_file') . 'video/' . sprintf($aVideo['destination'], ''), $aVideo['server_id']);
                            $aHeaders = get_headers($sTempUrl, true);
                            if (preg_match('/200 OK/i', $aHeaders[0])) {
                                $iFileSize += (int)$aHeaders["Content-Length"];
                            }
                        }
                    }
                    Phpfox::getLib('file')->unlink($sPathVideo, $aVideo['server_id']);
                }

                if ($iFileSize > 0) {
                    Phpfox::getService('user.space')->update($aVideo['user_id'], 'video', $iFileSize, '-');
                }
            }

            if ($sPlugin = Phpfox_Plugin::get('video.service_process_delete_1')) {
                eval($sPlugin);
            }

            if ($aVideo['is_sponsor'] == 1) {
                // close sponsorship
                Phpfox::getService('ad.process')->closeSponsorItem('v', $aVideo['video_id']);
                // clear cache
                $this->cache()->remove('video_sponsored');
            }
            if ($aVideo['is_featured'] == 1) {
                $this->cache()->remove('video_featured');
            }

            if (Phpfox::isModule('tag')) {
                $iCnt = db()->select('COUNT(*)')
                    ->from(Phpfox::getT('tag'))
                    ->where('category_id = "v" AND item_id = ' . (int)$iId)
                    ->execute('getSlaveField');
                if ($iCnt) {
                    db()->delete(Phpfox::getT('tag'),
                        'category_id = "v" AND item_id = ' . (int)$iId);
                    $this->cache()->remove('tag_cloud_global');
                }
            }
            return $sParentReturn;
        }

        return Phpfox_Error::set(_p('invalid_permissions'));
    }

    /**
     * @param $iId
     * @param $iType
     * @return bool
     */
    public function feature($iId, $iType)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('pf_video_feature', false);
        db()->update($this->_sTable, ['is_featured' => ($iType ? '1' : '0')],
            'video_id = ' . (int)$iId);
        if ($iType) {
            $aVideo = Phpfox::getService('v.video')->getInfoForNotification($iId);
            if (empty($aVideo['video_id'])) {
                return false;
            }
            $iSenderUserId = $aVideo['user_id'];
            if ((int)Phpfox::getUserId() > 0) {
                $iSenderUserId = Phpfox::getUserId();
            }
            Phpfox::getService("notification.process")->add("v_featured", $iId, $aVideo['user_id'], $iSenderUserId);
        }
        $this->cache()->remove('video_featured');

        return true;
    }

    /**
     * @param $iId
     * @param bool $bShowError
     * @return bool
     * @throws \Exception
     */
    public function approve($iId, $bShowError = true)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('pf_video_approve', false);

        $aVideo = db()->select('v.*')
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.video_id = ' . (int)$iId)
            ->execute('getRow');

        if (!isset($aVideo['video_id'])) {
            if ($bShowError) {
                return Phpfox_Error::set(_p('unable_to_find_the_video_you_want_to_approve'));
            } else {
                return false;
            }
        }

        if ($aVideo['view_id'] == '0') {
            return false;
        }

        db()->update($this->_sTable, ['view_id' => '0', 'time_stamp' => PHPFOX_TIME], 'video_id = ' . $iId);

        $ownerId = $aVideo['user_id'];
        if (Phpfox::isModule('notification')) {
            Phpfox::getService('notification.process')->add('v_approved', $iId, $ownerId);
        }

        (($sPlugin = Phpfox_Plugin::get('video.service_process_approve__1')) ? eval($sPlugin) : false);
        // Send the user an email
        $sLink = Phpfox::getLib('url')->permalink('video.play', $iId, $aVideo['title']);
        Phpfox::getLib('mail')->to($ownerId)
            ->subject([
                'your_video_has_been_approved_on_site_title',
                ['site_title' => Phpfox::getParam('core.site_title')]
            ])
            ->message([
                'your_video_has_been_approved_on_site_title_n_nto_view_this_video_follow_the_link_below_n_a_href',
                ['site_title' => Phpfox::getParam('core.site_title'), 'sLink' => $sLink]
            ])
            ->notification('v.email_notification')
            ->send();

        $aCallback = null;
        if ($aVideo['module_id'] != 'video' && Phpfox::hasCallback($aVideo['module_id'], 'getFeedDetails')) {
            $aCallback = Phpfox::callback($aVideo['module_id'] . '.getFeedDetails', $iId);
        }

        $iFeedId = 0;
        if (Phpfox::isModule('feed') && !defined('PHPFOX_SKIP_FEED_ENTRY') && Phpfox::getParam('v.pf_video_allow_create_feed_when_add_new_item', 1)) {
            $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('v', $iId, $aVideo['privacy'],
                $aVideo['privacy_comment'], ($aCallback === null ? (isset($aVideo['parent_user_id']) ? $aVideo['parent_user_id'] : 0) : $aVideo['item_id']), $ownerId);
        }

        // notification to tagged and mentioned friends
        $this->notifyTaggedInFeed($aVideo['status_info'], $iId, $ownerId, $iFeedId, $aVideo['tagged_friends'], $aVideo['privacy'], $aVideo['parent_user_id'], $aVideo['module_id']);

        if (Phpfox::isModule('notification') && $aVideo['module_id'] != 'video' && $aVideo['item_id'] && Phpfox::isModule($aVideo['module_id']) && Phpfox::hasCallback($aVideo['module_id'],
                'addItemNotification')) {
            Phpfox::callback($aVideo['module_id'] . '.addItemNotification', [
                'page_id' => $aVideo['item_id'],
                'item_perm' => 'pf_video.view_browse_videos',
                'item_type' => 'v',
                'item_id' => $iId,
                'owner_id' => $ownerId,
                'items_phrase' => 'videos__l',
            ]);
        }

        if ($aVideo['module_id'] == 'user' && $aVideo['item_id']) {
            if (Phpfox::isModule('notification')) {
                Phpfox::getService('notification.process')->add('v_newItem_wall', $iId, $aVideo['item_id'], $ownerId);
            }
            // send mail
            list(, $link) = Phpfox::getService('v.video')->getFeedLink($iId);
            $aOwnerUser = Phpfox::getService('user')->getUser($ownerId);
            $sOwner = (isset($aOwnerUser['full_name']) && $aOwnerUser['full_name']) ? $aOwnerUser['full_name'] : $aOwnerUser['user_name'];
            Phpfox::getLib('mail')->to($aVideo['item_id'])
                ->subject([
                    'full_name_posted_a_video_on_your_wall', ['full_name' => $sOwner]
                ])
                ->message([
                    'full_name_posted_a_video_on_your_wall_message', ['full_name' => $sOwner, 'link' => $link]
                ])
                ->notification('comment.add_new_comment')
                ->send();
        }
        if (Phpfox::isModule('notification') && $aVideo['module_id'] == 'pages' && $aVideo['item_id'] && Phpfox::isAppActive('Core_Pages')) {
            $aPage = Phpfox::getService('pages')->getPage($aVideo['item_id']);
            if ($aPage['user_id'] != $ownerId) {
                Phpfox::getService('notification.process')->add('v_newItem_pages', $iId, $aPage['user_id'], $ownerId, true);
            }
        }

        if ($sPlugin = Phpfox_Plugin::get('video.service_process_approve_1')) {
            eval($sPlugin);
        }

        if (!empty($aVideo['module_id']) && Phpfox::hasCallback($aVideo['module_id'], 'onVideoPublished')) {
            Phpfox::callback($aVideo['module_id'] . '.onVideoPublished', $aVideo);
        }

        // Update user activity
        Phpfox::getService('user.activity')->update($ownerId, 'v');

        return true;
    }

    /**
     * @param $sImgUrl
     * @return array
     */
    public function downloadImage($sImgUrl)
    {
        if (!$sImgUrl) {
            return ['', 0];
        }
        if (PHPFOX_IS_HTTPS && Phpfox::getParam('core.use_secure_image_display')) {
            $aUrl = parse_url($sImgUrl);
            $sExternalUrl = preg_replace('/external=([^?&]*)/', '$1', $aUrl['query']);
            if (!empty($sExternalUrl)) {
                $sImgUrl = base64_decode($sExternalUrl);
            }
        }
        $sImgUrl = str_replace('dailymotion.com/thumbnail/160x120', 'dailymotion.com/thumbnail/640x360', $sImgUrl);

        $pos = stripos($sImgUrl, ".bmp");
        if ($pos > 0) {
            return $sImgUrl;
        }
        //Check Folder Storage
        $sNewsPicStorage = Phpfox::getParam('core.dir_pic') . 'video';
        if (!is_dir($sNewsPicStorage)) {
            @mkdir($sNewsPicStorage, 0777, 1);
            @chmod($sNewsPicStorage, 0777);
        }

        // Generate Image object and store image to the temp file
        $iToken = rand();
        $sTempImage = 'video_temp_thumbnail_' . $iToken . '_' . PHPFOX_TIME . '.jpg';
        if (strpos($sImgUrl, '//graph.facebook.com') != false) {
            $oImage = fox_get_contents($sImgUrl);
            if (strpos($oImage, 'error') !== false) {
                return ['', 0];
            }
        } else {
            if (substr($sImgUrl, 0, 17) == '//img.youtube.com') {
                $sImgUrl = 'https:' . $sImgUrl;
            }
            $sImgUrl = html_entity_decode($sImgUrl);
            $oImage = Phpfox::getLib('request')->send($sImgUrl, [], 'GET');

            if (empty($oImage) && (substr($sImgUrl, 0, 8) == 'https://')) {
                $sImgUrl = 'http://' . substr($sImgUrl, 8);
                $oImage = Phpfox::getLib('request')->send($sImgUrl, [], 'GET');
            }
        }
        Phpfox::getLib('file')->writeToCache($sTempImage, $oImage);

        // Save image
        $ThumbNail = Phpfox::getLib('file')->getBuiltDir($sNewsPicStorage . PHPFOX_DS) . md5('image_' . $iToken . '_' . PHPFOX_TIME) . '%s.jpg';
        Phpfox::getLib('image')->createThumbnail(PHPFOX_DIR_CACHE . $sTempImage, sprintf($ThumbNail, '_' . 1024), 1024, 1024);
        Phpfox::getLib('image')->createThumbnail(PHPFOX_DIR_CACHE . $sTempImage, sprintf($ThumbNail, '_' . 500), 500, 500);
        @unlink(PHPFOX_DIR_CACHE . $sTempImage);

        $iPhotoSize = 0;
        if (file_exists(sprintf($ThumbNail, '_' . 500))) {
            $iPhotoSize += filesize(sprintf($ThumbNail, '_' . 500));
        }
        if (file_exists(sprintf($ThumbNail, '_' . 1024))) {
            $iPhotoSize += filesize(sprintf($ThumbNail, '_' . 1024));
        }

        $sFileName = str_replace(Phpfox::getParam('core.dir_pic'), "", $ThumbNail);
        $sFileName = str_replace("\\", "/", $sFileName);

        // Return logo file
        return [$sFileName, $iPhotoSize];
    }

    public function updateHashtag($iVideoId, $aVideo = null, $sDescription = null, $sStatusInfo = null)
    {
        if (!Phpfox::isModule('tag') || !Phpfox::getParam('tag.enable_hashtag_support')) {
            return false;
        }
        if ($aVideo === null) {
            $aVideo = db()->select('v.video_id, v.status_info, vt.text')
                ->from($this->_sTable, 'v')
                ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
                ->where('v.video_id = ' . (int)$iVideoId)
                ->execute('getRow');
        }

        if (!isset($aVideo['video_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_video_you_plan_to_edit'));
        }
        $sUpdate = '';
        if ($sDescription !== null && $sStatusInfo !== null) {
            //All new
            $sUpdate = $sStatusInfo . ' ' . $sDescription;
        } elseif ($sDescription !== null) {
            //New description - old status
            $sUpdate = ($aVideo['status_info'] !== null ? $aVideo['status_info'] : '') . ' ' . $sDescription;
        } elseif ($sStatusInfo !== null) {
            //New status - old description
            $sUpdate = ($aVideo['text'] !== null ? $aVideo['text'] : '') . ' ' . $sStatusInfo;
        }
        Phpfox::getService('tag.process')->update('v', $iVideoId, Phpfox::getUserId(), $sUpdate, true);
        return true;
    }
}
