<?php

namespace Apps\PHPfox_Videos\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');

class ShareController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('v.pf_video_share', true);

        if (!Phpfox::getService('v.video')->checkLimitation()) {
            return Phpfox_Error::display(_p('v_you_have_reached_your_limit_to_upload_new_video'));
        }

        $bIsAjaxBrowsing = ($this->request()->get('is_ajax_browsing') ? true : false);

        //Support sharing video on feed in case login as page/group
        $iProfilePageId = Phpfox::getUserBy('profile_page_id');
        if ($iProfilePageId && $bIsAjaxBrowsing) {
            if (($sModuleId = Phpfox::getLib('pages.facade')->getPageItemType($iProfilePageId)) == 'groups') {
                Phpfox::getService('groups')->setIsInPage();
            } elseif ($sModuleId == 'pages') {
                Phpfox::getService('pages')->setIsInPage();
            }
        }

        $bUploadSuccess = false;
        if ($sPlugin = Phpfox_Plugin::get('video.component_controller_add_1')) {
            eval($sPlugin);
            if (isset($mReturnFromPlugin)) {
                return $mReturnFromPlugin;
            }
        }

        $sModule = $this->request()->get('module', false);
        $iItemId = $this->request()->getInt('item', false);
        $aCallback = false;
        if ($sModule !== false && $iItemId !== false && Phpfox::hasCallback($sModule, 'getItem')) {
            if ($sPlugin = Phpfox_Plugin::get('video.component_controller_add_2')) {
                eval($sPlugin);
                if (isset($mReturnFromPlugin)) {
                    return $mReturnFromPlugin;
                }
            }
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItemId);
            if ($aCallback === false) {
                return Phpfox_Error::display(_p('cannot_find_the_parent_item'));
            }
            if (Phpfox::hasCallback($sModule, 'checkPermission')) {
                if (!Phpfox::callback($sModule . '.checkPermission', $iItemId, 'pf_video.share_videos')) {
                    return Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
                }
            }
            //Support custom title
            if ($sModule && $iItemId && Phpfox::hasCallback($sModule, 'viewVideo')) {
                $aCallback = Phpfox::callback($sModule . '.viewVideo', $iItemId);
                if ($aCallback === false) {
                    return Phpfox_Error::display(_p('Cannot find the parent item.'));
                }
                $this->template()->setBreadCrumb($aCallback['breadcrumb_title'], $aCallback['breadcrumb_home']);
                $this->template()->setBreadCrumb($aCallback['title'], $aCallback['url_home']);
            } else {
                $this->template()
                    ->setBreadCrumb(isset($aCallback['module_title']) ? $aCallback['module_title'] : _p($sModule),
                        $this->url()->makeUrl($sModule))
                    ->setBreadCrumb($aCallback['title'], Phpfox::permalink($sModule, $iItemId));
            }
            $this->template()
                ->setBreadCrumb(_p('videos'), $this->url()->makeUrl($sModule, [$iItemId, 'video']))
                ->assign([
                    'sModule' => $sModule,
                    'iItemId' => $iItemId
                ]);
        } else {
            if (!empty($sModule) && !empty($iItemId) && $sModule != 'video' && $aCallback === null) {
                return Phpfox_Error::display(_p('cannot_find_the_parent_item'));
            }

            $this->template()
                ->setBreadCrumb(_p('videos'), $this->url()->makeUrl('video'));

        }
        $this->template()->setBreadCrumb(_p('share_a_video'), $this->url()->current(), true);

        $aValidation = [
            'title' => [
                'def' => 'required',
                'title' => _p('provide_a_title_for_this_video')
            ]
        ];

        $iMethodUpload = setting('pf_video_method_upload');
        $bAllowVideoUploading = false;
        if (setting('pf_video_support_upload_video')
            && (setting('pf_video_allow_compile_on_storage_system')
                || ($iMethodUpload == 1 && setting('pf_video_key'))
                || ($iMethodUpload == 0 && setting('pf_video_ffmpeg_path'))
                || ($iMethodUpload == 2 && setting('pf_video_mux_token_id') && setting('pf_video_mux_token_secret'))
            )) {
            $bAllowVideoUploading = true;
        }

        (($sPlugin = Phpfox_Plugin::get('video.component_controller_share_process_validation')) ? eval($sPlugin) : false);
        if (!$bIsAjaxBrowsing) {
            $oValid = Phpfox_Validator::instance()->set([
                    'sFormName' => 'core_js_video_form',
                    'aParams' => $aValidation
                ]
            );

            if (($aVals = $this->request()->get('val'))) {
                if ($sPlugin = Phpfox_Plugin::get('video.component_controller_add_3')) {
                    eval($sPlugin);
                    if (isset($mReturnFromPlugin)) {
                        return $mReturnFromPlugin;
                    }
                }

                if ($oValid->isValid($aVals)) {
                    if ($sPlugin = Phpfox_Plugin::get('video.component_controller_add_4')) {
                        eval($sPlugin);
                        if (isset($mReturnFromPlugin)) {
                            return $mReturnFromPlugin;
                        }
                    }

                    if (preg_match('/dailymotion/', $aVals['url']) && substr($aVals['url'], 0, 8) == 'https://') {
                        $aVals['url'] = str_replace('https', 'http', $aVals['url']);
                    }

                    if (isset($aVals['pf_video_id'])) {
                        if (empty($aVals['pf_video_id'])) {
                            return Phpfox_Error::display(_p('we_could_not_find_a_video_there_please_try_again'));
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
                                'asset_id' => isset($encoding->value->asset_id) ? $encoding->value->asset_id : null
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
                                    'callback_module' => (isset($aVals['callback_module']) ? $aVals['callback_module'] : ''),
                                    'callback_item_id' => (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : 0),
                                    'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                                    'title' => $aVals['title'],
                                    'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                                    'text' => $aVals['text'],
                                    'status_info' => ''
                                ]);
                            } elseif ($iMethodUpload == 0 && setting('pf_video_ffmpeg_path')) {
                                $iJobId = \Phpfox_Queue::instance()->addJob('videos_ffmpeg_encode', []);
                                storage()->set('pf_video_' . $iJobId, [
                                    'encoding_id' => $iJobId,
                                    'id' => $encoding->value->id,
                                    'user_id' => $encoding->value->user_id,
                                    'view_id' => $encoding->value->view_id,
                                    'path' => $encoding->value->path,
                                    'ext' => $encoding->value->ext,
                                    'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                                    'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                                    'callback_module' => (isset($aVals['callback_module']) ? $aVals['callback_module'] : ''),
                                    'callback_item_id' => (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : 0),
                                    'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                                    'title' => $aVals['title'],
                                    'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                                    'text' => $aVals['text'],
                                    'status_info' => ''
                                ]);
                                storage()->del('pf_video_' . $aVals['pf_video_id']);
                            } else {
                                storage()->update('pf_video_' . $aVals['pf_video_id'], [
                                    'privacy' => (isset($aVals['privacy']) ? (int)$aVals['privacy'] : 0),
                                    'privacy_list' => json_encode(isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []),
                                    'callback_module' => (isset($aVals['callback_module']) ? $aVals['callback_module'] : ''),
                                    'callback_item_id' => (isset($aVals['callback_item_id']) ? (int)$aVals['callback_item_id'] : 0),
                                    'parent_user_id' => (isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0),
                                    'title' => $aVals['title'],
                                    'category' => json_encode(isset($aVals['category']) ? $aVals['category'] : []),
                                    'text' => $aVals['text'],
                                    'status_info' => '',
                                    'updated_info' => 1
                                ]);
                            }
                        }

                        $bUploadSuccess = true;
                    } elseif (!empty($aVals['url']) && $parsed = Phpfox::getService('link')->getLink($aVals['url'])) {
                        if (empty($parsed['embed_code'])) {
                            return Phpfox_Error::display(_p('unable_to_load_a_video_to_embed'));
                        }
                        if (isset($parsed['duration'])) {
                            $aVals['duration'] = $parsed['duration'];
                        }
                        if (isset($parsed['width'])) {
                            $aVals['resolution_x'] = $parsed['width'];
                        }
                        if (isset($parsed['height'])) {
                            $aVals['resolution_y'] = $parsed['height'];
                        }
                        if ($iId = Phpfox::getService('v.process')->addVideo($aVals)) {
                            $this->url()->permalink('video.play', $iId, $aVals['title'], true,
                                _p('video_successfully_added'));
                        }
                    } else {
                        $this->template()
                            ->assign([
                                    'bAddFalse' => 1
                                ]
                            );
                    }
                }
            }

            $this->template()
                ->assign([
                    'sCreateJs' => $oValid->createJS(),
                    'sGetJsForm' => $oValid->getJsForm()
                ]);
        }

        define('PHPFOX_APP_DETAIL_PAGE', true);
        $this->template()->setTitle(_p('share_a_video'))
            ->assign([
                    'bIsAjaxBrowsing' => $bIsAjaxBrowsing,
                    'sModule' => $sModule,
                    'iItemId' => $iItemId,
                    'bAllowVideoUploading' => $bAllowVideoUploading,
                    'ivideoFileSize' => user('pf_video_file_size'),
                    'bUploadSuccess' => $bUploadSuccess
                ]
            );

        (($sPlugin = Phpfox_Plugin::get('video.component_controller_share_process')) ? eval($sPlugin) : false);

        return 'controller';
    }
}
