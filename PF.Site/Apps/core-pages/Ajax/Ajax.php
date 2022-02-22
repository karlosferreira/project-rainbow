<?php

namespace Apps\Core_Pages\Ajax;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Ajax extends Phpfox_Ajax
{
    public function claimPageWithSameAdmin()
    {
        Phpfox::isUser(true);
        if (Phpfox::getService('pages.process')->addPageClaim($this->get('page_id'))) {
            $this->call('$Core.reloadPage();');
        }
    }

    public function removeLogo()
    {
        if (($aPage = Phpfox::getService('pages.process')->removeLogo($this->get('page_id'))) !== false) {
            $this->call('window.location.href = \'' . $aPage['link'] . '\';');
        }
    }

    public function deleteWidget()
    {
        $widgetId = (int)$this->get('widget_id');

        if (empty($widgetId) || empty($widget = Phpfox::getService('pages')->getWidgetById($widgetId))) {
            return false;
        }

        if (Phpfox::getService('pages.process')->deleteWidget($widgetId)) {
            Phpfox::addMessage(_p(!empty($widget['is_block']) ? 'pages_widget_successfully_deleted' : 'pages_menu_successfully_deleted'));
            $redirectUrl = Phpfox::getLib('url')->makeUrl('pages.add.widget', [
                'id' => $widget['page_id'],
                'sub_tab' => !empty($widget['is_block']) ? 'widget' : 'menu',
            ]);
            $this->call('window.location.href = "' . $redirectUrl . '";');
        }
    }

    public function widget()
    {
        $this->setTitle((bool)$this->get('is_menu') ? _p('menus') : _p('widgets'));
        Phpfox::getComponent('pages.widget', [], 'controller');

        (($sPlugin = Phpfox_Plugin::get('pages.component_ajax_widget')) ? eval($sPlugin) : false);

        echo '<script type="text/javascript">$Core.loadInit();</script>';
    }

    public function add()
    {
        Phpfox::isUser(true);

        if (($iId = Phpfox::getService('pages.process')->add($this->get('val')))) {
            $aPage = Phpfox::getService('pages')->getPage($iId);
            $this->call('window.location.href = \'' . Phpfox_Url::instance()->makeUrl('pages.add',
                    ['id' => $aPage['page_id'], 'new' => '1']) . '\';');
        } else {
            $this->error(false);
            $sError = Phpfox_Error::get();
            $sError = implode('<br />', $sError);
            $this->call('$("#add_page_error_messages").show(); $("#add_page_error_messages").html("' . $sError . '");')
                ->call('Core_Pages.resetSubmit();');
        }
    }

    public function addFeedComment()
    {
        Phpfox::isUser(true);
        $aVals = (array)$this->get('val');
        $iCustomPageId = isset($_REQUEST['custom_pages_post_as_page']) ? $_REQUEST['custom_pages_post_as_page'] : 0;
        if (($iCustomPageId && $iCustomPageId != $aVals['callback_item_id']) || !Phpfox::getService('pages')->hasPerm($aVals['callback_item_id'],
                'pages.share_updates')) {
            $this->alert(_p('You do not have permission to add comments'));
            $this->call('$Core.activityFeedProcess(false);');

            return;
        }

        $feed = [];
        if (isset($aVals['feed_id'])) {
            $feed = Phpfox::getService('feed')->getFeed($aVals['feed_id'], 'pages_');
        }

        if ((!isset($aVals['feed_id']) || (!empty($feed) && in_array($feed['type_id'], ['link', 'pages_comment']))) && Phpfox::getLib('parse.format')->isEmpty($aVals['user_status'])) {
            $this->alert(_p('add_some_text_to_share'));
            $this->call('$Core.activityFeedProcess(false);');

            return;
        }

        $aPage = Phpfox::getService('pages')->getPage($aVals['callback_item_id']);

        if (!isset($aPage['page_id'])) {
            $this->alert(_p('unable_to_find_the_page_you_are_trying_to_comment_on'));
            $this->call('$Core.activityFeedProcess(false);');

            return;
        }

        $sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        $aCallback = [
            'module'                => 'pages',
            'table_prefix'          => 'pages_',
            'link'                  => $sLink,
            'email_user_id'         => $aPage['user_id'],
            'subject'               => [
                'full_name_wrote_a_comment_on_your_page_title',
                ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title']]
            ],
            'message'               => [
                'full_name_wrote_a_comment_link',
                ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aPage['title']]
            ],
            'notification'          => ($this->get('custom_pages_post_as_page') ? null : 'pages_comment'),
            'notification_setting'  => ($this->get('custom_pages_post_as_page') ? null : 'pages.email_notification'),
            'notification_post_tag' => 'pages_post_tag',
            'feed_id'               => 'pages_comment',
            'item_id'               => $aPage['page_id'],
            'item_title'            => $aPage['title'],
            'add_tag'               => true
        ];

        $aVals['parent_user_id'] = $aVals['callback_item_id'];

        if (isset($aVals['user_status']) && ($iId = Phpfox::getService('feed.process')->callback($aCallback)->addComment($aVals))) {
            if (!isset($aVals['feed_id'])) {
                db()->updateCounter('pages', 'total_comment', 'page_id', $aPage['page_id']);
                defined('PHPFOX_PAGES_ADD_COMMENT') || define('PHPFOX_PAGES_ADD_COMMENT', 1);
                Phpfox::getService('feed')->callback($aCallback)->processAjax($iId);
            } else {
                $sStatus = Phpfox::getService('feed.tag')->stripContentHashTag($aVals['user_status'], $feed['item_id'], $feed['type_id']);
                $sStatus = Phpfox::getLib('parse.output')->parse($sStatus);
                $typeLink = $feed['type_id'] == 'link';
                $this->call('Core_Pages.processEditFeedStatus(' . $feed['feed_id'] . ',' . json_encode($sStatus) . ($typeLink ? ',1' : '') . ');');
                $this->call('tb_remove();');
                $this->call('setTimeout(function(){$Core.resetActivityFeedForm();$Core.loadInit();}, 500);');
            }

            if (!Phpfox::hasCallback('pages', 'mailToTagged')) {
                $aMentions = Phpfox::getService('user.process')->getIdFromMentions(Phpfox::getLib('parse.input')->prepare($aVals['user_status']), true, false);
                $aTagged = !empty($aVals['tagged_friends']) ? explode(',', $aVals['tagged_friends']) : [];
                if (!empty($aMentions) || !empty($aTagged)) {
                    Phpfox::getService('pages')->sendMailToTaggedUsers(array_unique(array_merge($aTagged, $aMentions)), $iId, $aPage['page_id']);
                }
            }
        } else {
            $this->call('$Core.activityFeedProcess(false);');
        }
    }

    public function changeUrl()
    {
        Phpfox::isUser(true);

        if (($aPage = Phpfox::getService('pages')->getForEdit($this->get('id')))) {
            $aVals = $this->get('val');

            $sNewTitle = Phpfox::getLib('parse.input')->cleanTitle($aVals['vanity_url']);

            if (Phpfox::getLib('parse.input')->allowTitle($sNewTitle,
                _p('page_name_not_allowed_please_select_another_name'))) {
                if (Phpfox::getService('pages.process')->updateTitle($this->get('id'), $sNewTitle)) {
                    $this->call('if (Core_Pages.canCheckReloadValidate) {$Core.reloadValidation.initEleData["js_form_pages_add"]["val[vanity_url]"] = "' . Phpfox::getLib('parse.output')->clean($sNewTitle) . '";}');
                    $this->alert(_p('successfully_updated_your_pages_url'), _p('url_updated'), 300, 150, true);
                }
            }
            $sUrl = Phpfox::getService('pages')->getUrl($aPage['page_id']);
            $this->call('$(".page_section_menu_link").attr("href", "' . $sUrl . '");');
        }
        $this->call('if (Core_Pages.canCheckReloadValidate) {delete $Core.reloadValidation.changedEleData["js_form_pages_add"]["val[vanity_url]"]; $Core.reloadValidation.preventReload();}');
        $this->call('$Core.processForm(\'#js_pages_vanity_url_button\', true);');
    }

    public function moderation()
    {
        Phpfox::isUser(true);
        $sAction = $this->get('action');

        if (Phpfox::getService('pages.process')->moderation($this->get('item_moderate'), $this->get('action'))) {
            foreach ((array)$this->get('item_moderate') as $iId) {
                $this->remove('#js_pages_user_entry_' . $iId);
            }

            $this->updateCount();
            switch ($sAction) {
                case 'delete':
                    $sMessage = _p('successfully_deleted_user_s_dot');
                    break;
                case 'approve':
                    $sMessage = _p('successfully_approved_user_s_dot');
                    break;
                default:
                    $sMessage = _p('successfully_moderated_user_s');
                    break;
            }
            $this->alert($sMessage, _p('moderation'), 300, 150, true);
        }

        $this->hide('.moderation_process');
    }

    public function logBackUser()
    {
        $this->error(false);
        Phpfox::isUser(true);
        $aUser = Phpfox::getService('pages')->getLastLogin();
        list ($bPass,) = Phpfox::getService('user.auth')->login($aUser['email'], $this->get('password'), true,
            $sType = 'email');
        if ($bPass) {
            Phpfox::getService('pages.process')->clearLogin($aUser['user_id']);

            $this->call('window.location.href = \'' . Phpfox_Url::instance()->makeUrl('') . '\';');
        } else {
            $this->html('#js_error_pages_login_user',
                '<div class="error_message">' . implode('<br />', Phpfox_Error::get()) . '</div>');
        }
    }

    public function login()
    {
        Phpfox::isUser(true);
        $this->setTitle(_p('login_as_a_page'));
        Phpfox::getBlock('pages.login');
    }

    public function loginSearch()
    {
        // Parameters to be sent to the block
        $aParams = [
            'page' => $this->get('page'),
        ];

        // Call the block and send the parameters
        Phpfox::getBlock('pages.login', $aParams);

        // Display the block into the TB box
        $this->call('$(\'.js_box_content\').html(\'' . $this->getContent() . '\');');
    }

    public function processLogin()
    {
        if (Phpfox::getService('pages.process')->login($this->get('page_id'))) {
            $this->call('window.location.href = \'' . Phpfox_Url::instance()->makeUrl('') . '\';');
        }
    }

    public function pageModeration()
    {
        Phpfox::isUser(true);

        switch ($this->get('action')) {
            case 'approve':
                foreach ((array)$this->get('item_moderate') as $iId) {
                    Phpfox::getService('pages.process')->approve($iId);
                }
                Phpfox::addMessage(_p('pages_s_successfully_approved'));
                $this->call('window.location.reload();');

                return;
            case 'delete':
                foreach ((array)$this->get('item_moderate') as $iId) {
                    Phpfox::getService('pages.process')->delete($iId);
                    $this->slideUp('#js_pages_' . $iId);
                }
                $sMessage = _p('pages_s_successfully_deleted');
                break;
            default:
                $sMessage = '';
                break;
        }

        $this->updateCount();
        $this->alert($sMessage, _p('moderation'), 300, 150, true);
        $this->hide('.moderation_process');
        $this->call('setTimeout(function(){window.location.reload();}, 2000);');
    }

    public function approve()
    {
        if (Phpfox::getService('pages.process')->approve($this->get('page_id'))) {
            $this->alert(_p('page_has_been_approved'), _p('page_approved'), 300, 100, true);
            $this->hide('#js_item_bar_approve_image');
            $this->hide('.js_moderation_off');
            $this->show('.js_moderation_on');
            $this->call('window.location.reload();');
        }
    }

    public function updateActivity()
    {
        Phpfox::getService('pages.process')->updateActivity($this->get('id'), $this->get('active'), $this->get('sub'));
    }

    public function categoryOrdering()
    {
        Phpfox::isAdmin(true);
        $aVals = $this->get('val');
        Phpfox::getService('core.process')->updateOrdering([
                'table'  => 'pages_type',
                'key'    => 'type_id',
                'values' => $aVals['ordering']
            ]
        );

        Phpfox::getLib('cache')->removeGroup('pages');
    }

    public function categorySubOrdering()
    {
        Phpfox::isAdmin(true);
        $aVals = $this->get('val');
        Phpfox::getService('core.process')->updateOrdering([
                'table'  => 'pages_category',
                'key'    => 'category_id',
                'values' => $aVals['ordering']
            ]
        );

        Phpfox::getLib('cache')->removeGroup('pages');
    }

    public function approveClaim()
    {
        Phpfox::isAdmin(true);
        if (Phpfox::getService('pages.process')->approveClaim($this->get('claim_id'))) {
            $this->hide('#claim_' . $this->get('claim_id'));
            $this->call('$(\'#global_ajax_message\').hide();');
            Phpfox::getService('pages')->clearAdminCacheWhenCompletePageClaim($this->get('claim_id'));
            Phpfox::addMessage(_p('successfully_the_claimed_page'));
            $this->reload();
        } else {
            $this->alert(_p('An error occurred'));
        }
    }

    public function denyClaim()
    {
        Phpfox::isAdmin(true);
        if (Phpfox::getService('pages.process')->denyClaim($this->get('claim_id'))) {
            $this->hide('#claim_' . $this->get('claim_id'));
        } else {
            $this->alert(_p('An error occurred'));
        }
    }

    public function setCoverPhoto()
    {
        $iPageId = $this->get('page_id');
        $iPhotoId = $this->get('photo_id');

        if (Phpfox::getService('pages.process')->setCoverPhoto($iPageId, $iPhotoId, false, true)) {
            $this->call('window.location.href = "' . Phpfox::permalink('pages', $this->get('page_id'),
                    '') . 'coverupdate_1";');

        }
    }

    public function repositionCoverPhoto()
    {
        Phpfox::isUser(true);

        $photoId = $this->get('photo_id');
        if (!empty($photoId) && Phpfox::getUserParam('photo.photo_must_be_approved')) {
            storage()->set('photo_cover_reposition_' . $photoId, $this->get('position'));
        } elseif (Phpfox::getService('pages.process')->updateCoverPosition($this->get('id'), $this->get('position'))) {
            if (empty($photoId)) {
                Phpfox::addMessage(_p('position_set_correctly'));
            }
            $this->reload();
        }
    }

    public function updateCoverPosition()
    {
        if (Phpfox::getService('pages.process')->updateCoverPosition($this->get('page_id'), $this->get('position'))) {
            $this->call('window.location.href = "' . Phpfox::permalink('pages', $this->get('page_id'), '') . '";');
            Phpfox::addMessage(_p('position_set_correctly'));
        }
    }

    public function removeCoverPhoto()
    {
        if (Phpfox::getService('pages.process')->removeCoverPhoto($this->get('page_id'))) {
            $this->call('window.location.href=window.location.href;');
        }
    }

    public function cropme()
    {
        $canUpload = !!$this->get('allow_upload', false);
        if ($canUpload) {
            $this->setTitle(_p('update_profile_picture'));
        }
        Phpfox::getBlock('pages.cropme', [
            'allow_upload' => $canUpload,
        ]);
        $this->call('<script>Core_Pages.initCropMe();</script>');
    }

    public function processCropme()
    {
        $aVals = $this->get('val');
        $aPage = Phpfox::getService('pages')->getForEdit($aVals['page_id']);
        if (!((Phpfox::getService('pages')->isAdmin($aPage) || Phpfox::getUserParam('pages.can_edit_all_pages')) &&
            Phpfox::getUserParam('pages.can_add_cover_photo_pages'))) {
            return false;
        }

        $sTempPath = null;
        $tempFile = !empty($aVals['temp_file']) ? Phpfox::getService('core.temp-file')->getByFields($aVals['temp_file'], 'path, server_id') : null;
        $aUploadParams = array_merge(Phpfox::getService('pages')->getUploadPhotoParams(), [
            'type' => 'pages_photo',
            'update_space' => false,
        ]);

        if (isset($aVals['crop-data']) && !empty($aVals['crop-data'])) {
            if (!empty($tempFile['path'])) {
                $sProfileTemp = Phpfox::getParam('pages.dir_image') . sprintf($tempFile['path'], '');
            } else {
                if ($aPage['image_server_id']) {
                    $oTempImage = storage()->get('page/thumbnail/' . $aPage['page_id']);
                    if (!empty($oTempImage->value)) {
                        $sProfileTemp = Phpfox::getParam('pages.dir_image') . 'temp' . PHPFOX_DS . $oTempImage->value;
                    }
                } else {
                    $sProfileTemp = Phpfox::getParam('pages.dir_image') . sprintf($aPage['image_path'], '');
                }
            }

            if (empty($sProfileTemp)) {
                $sExtension = 'png';
            } else {
                $sExtension = pathinfo($sProfileTemp, PATHINFO_EXTENSION);
            }

            $sTempPath = PHPFOX_DIR_CACHE . md5('pages_avatar' . $aVals['page_id']) . '.' . $sExtension;

            $oImage = \Phpfox_Image::instance();
            $oFile = \Phpfox_File::instance();

            if ($sExtension == 'gif') {
                $oFile->copy($sProfileTemp, $sTempPath);
                if ($oImage->isSupportNextGenImg()) {
                    if (!empty($aVals['rotation'])) {
                        $oImage->rotate($sTempPath, $aVals['rotation'], null, false);
                    }
                    if (isset($aVals['zoom']) && isset($aVals['crop_coordinate']) && isset($aVals['preview_size'])) {
                        Phpfox::getService('user.file')->cropGifImage($sTempPath, $aVals['zoom'], $aVals['crop_coordinate'], $aVals['preview_size']);
                    }
                }
            } else {
                list(, $data) = explode(';', $aVals['crop-data']);
                list(, $data) = explode(',', $data);
                $data = base64_decode($data);
                file_put_contents($sTempPath, $data);
            }

            if (!file_exists($sTempPath)
                || filesize($sTempPath) <= 0) {
                return false;
            }

            if (empty($aVals['temp_file'])) {
                $aFileInfo = Phpfox::getService('user.file')->upload($sTempPath, $aUploadParams, true);
                $sNewImageName = isset($aFileInfo['name']) ? $aFileInfo['name'] : null;
                $iFileSize = isset($aFileInfo['size']) ? $aFileInfo['size'] : 0;
            } else {
                $sNewImageName = $sTempPath;
                $iFileSize = filesize($sNewImageName);
            }

            if (!$sNewImageName || $iFileSize <= 0) {
                return false;
            }

            // delete temporary image
            register_shutdown_function(function () use ($sTempPath) {
                @unlink($sTempPath);
            });
        } elseif (!empty($aVals['temp_file'])) {
            if (empty($tempFile['path'])) {
                return false;
            }
            $sNewImageName = $tempFile['path'];
        }

        if (!empty($aVals['temp_file'])) {
            if (empty($uploadedInfo = Phpfox::getService('pages.process')->uploadProfilePhoto($aPage, null, $sNewImageName))) {
                return false;
            }

            @register_shutdown_function(function() use($aVals) {
                Phpfox::getService('core.temp-file')->delete($aVals['temp_file'], true);
            });

            $aPage = array_merge($aPage, [
                'image_path' => $uploadedInfo['path'],
                'image_server_id' => $uploadedInfo['serverId'],
            ]);
        } else {
            $oFile = Phpfox::getLib('file');
            $sPageDir = Phpfox::getParam('pages.dir_image');
            $iServerId = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            $aSizes = Phpfox::getService('pages')->getPhotoPicSizes();

            foreach ($aSizes as $iSize) {
                $oFile->unlink($sPageDir . sprintf($aPage['image_path'], '_' . $iSize));
                $oFile->unlink($sPageDir . sprintf($aPage['image_path'], '_' . $iSize . '_square'));
                if ($aPage['image_server_id']) {
                    Phpfox::getLib('cdn')->remove($sPageDir . sprintf($aPage['image_path'], '_' . $iSize));
                    Phpfox::getLib('cdn')->remove($sPageDir . sprintf($aPage['image_path'], '_' . $iSize . '_square'));
                }
            }

            $oFile->unlink($sPageDir . sprintf($aPage['image_path'], ''));
            if ($aPage['image_server_id']) {
                Phpfox::getLib('cdn')->remove($sPageDir . sprintf($aPage['image_path'], ''));
            }

            Phpfox::getService('pages.process')->updateProfilePictureForThumbnail($aPage['page_id'], $sNewImageName, $iServerId);
            // update user photo for page
            Phpfox::getService('pages.process')->updateUserImageAndPhotoProfileForProcessCrop($aPage['page_id'], $sTempPath);
            $aPage = array_merge($aPage, [
                'image_path' => $sNewImageName,
                'image_server_id' => $iServerId,
            ]);

            $oTempImage = storage()->get('page/thumbnail/' . $aPage['page_id']);
            if (is_object($oTempImage) && !empty($oTempImage->value)) {
                $sTempImagePath = $sPageDir . 'temp' . PHPFOX_DS . $oTempImage->value;
                if (file_exists($sTempImagePath)) {
                    @unlink($sTempImagePath);
                }
                storage()->del('page/thumbnail/' . $aPage['page_id']);
            }
        }

        if (!empty($aVals['is_upload'])) {
            $this->call('$Core.reloadPage();');
        } else {
            $sImagePath = Phpfox::getLib('image.helper')->display([
                'server_id'  => $aPage['image_server_id'],
                'path'       => 'pages.url_image',
                'file'       => $aPage['image_path'],
                'suffix'     => '_120_square',
                'max_width'  => '120',
                'max_height' => '120',
                'thickbox'   => true,
                'time_stamp' => true,
                'return_url' => true
            ]);
            $this->call('$("#js_current_image_wrapper span").css("background-image", \'url("' . $sImagePath . '")\');');
            $this->call("tb_remove(); Core_Pages.profilePhoto.reset(); if(Core_Pages.canCheckReloadValidate){\$Core.reloadValidation.reset(true, 'js_form_pages_crop_me');\$Core.reloadValidation.preventReload();}");
        }
    }

    public function logBackIn()
    {
        if (($aUser = Phpfox::getService('pages')->getLastLogin())) {
            if (isset($aUser['fb_user_id']) && $aUser['fb_user_id']) {
                Phpfox::getService('pages.process')->clearLogin($aUser['user_id']);
                Phpfox::getService('user.auth')->logout();
            } else {
                if (Phpfox::getParam('core.auth_user_via_session')) {
                    Phpfox::getLib('database')->delete(Phpfox::getT('session'),
                        'user_id = ' . (int)Phpfox::getUserId());
                }
                list ($bPass,) = Phpfox::getService('user.auth')->login($aUser['email'], $aUser['password'],
                    true, 'email', true);
                if ($bPass) {
                    Phpfox::getService('pages.process')->clearLogin($aUser['user_id']);
                }
            }
        }

        $this->call('window.location.href = \'' . Phpfox::getLib('url')->makeUrl('') . '\';');
    }

    public function deleteCategory()
    {
        $this->setTitle(_p('delete_category'));
        Phpfox::getBlock('pages.delete-category');
    }

    public function deleteCategoryImage()
    {
        Phpfox::getService('pages.type')->deleteImage($this->get('type_id'));
        $this->call('$(".category-image").remove();');
        $this->softNotice(_p('delete_category_image_successfully'));
    }

    public function addPage()
    {
        Phpfox::getBlock('pages.add-page', ['type_id' => $this->get('type_id')]);
    }

    /**
     * Get lat long of user base on IP
     */
    public function getMyCity()
    {
        $sInfo = \Phpfox_Request::instance()->send('http://freegeoip.net/json/' . \Phpfox_Request::instance()->getIp(), [], 'GET');
        $oInfo = null;
        if ($sInfo) {
            $oInfo = json_decode($sInfo);
        }
        // during testing latlng wont work
        if (empty($oInfo->latitude)) {
            $oInfo->latitude = '-43.132123';
            $oInfo->longitude = '9.140625';
        } else {
            $this->call('setCookie("core_places_location", "' . $oInfo->latitude . ',' . $oInfo->longitude . '");');
        }
        $this->call('$Core.PagesLocation.gMyLatLng = new google.maps.LatLng("' . $oInfo->latitude . '","' . $oInfo->longitude . '");');
        $this->call('$($Core.PagesLocation).trigger("gotVisitorLocation");');
    }

    public function orderWidget()
    {
        $aOrdering = $this->get('ordering');

        if (empty($aOrdering)) {
            return;
        }

        foreach ($aOrdering as $iWidgetId => $iOrder) {
            Phpfox::getService('pages')->updateItemOrder(Phpfox::getT('pages_widget'), 'widget_id', $iWidgetId,
                $iOrder);
        }

        Phpfox::getLib('cache')->remove('pages_' . $this->get('page_id') . '_widgets');
    }

    public function orderMenu()
    {
        $aPageMenus = $this->get('page_menu');

        if (empty($aPageMenus)) {
            return;
        }

        Phpfox::getService('pages.process')->orderMenu($aPageMenus, $this->get('page_id'));
    }

    public function toggleActivePageMenu()
    {
        $iMenuId = $this->get('menu_id');
        $sMenuName = $this->get('menu_name');
        $iPageId = $this->get('page_id');
        $iActive = $this->get('is_active');
        if (!isset($iActive) || empty($iPageId) || empty($sMenuName)) {
            return;
        }

        $iId = Phpfox::getService('pages.process')->updateActiveMenu($iMenuId, $sMenuName, $iActive, $iPageId);

        if (empty($iMenuId)) {
            echo $iId;
        }
    }

    public function removeMember()
    {
        $iPageId = $this->get('page_id');
        $iUserId = $this->get('user_id');

        if (!$iPageId || !$iUserId) {
            return;
        }

        Phpfox::getService('like.process')->delete('pages', $iPageId, $iUserId);
        $this->fadeOut("#pages-member-$iUserId")
            ->call('Core_Pages.updateCounter("#all-members-count");');
    }

    public function removeAdmin()
    {
        $iPageId = $this->get('page_id');
        $iUserId = $this->get('user_id');

        if (!$iPageId || !$iUserId) {
            return;
        }

        Phpfox::getService('pages.process')->removeAdmin($iPageId, $iUserId);
        $this->fadeOut("#pages-member-$iUserId")
            ->call('Core_Pages.updateCounter("#admin-members-count");');
    }

    public function getMembers()
    {
        $sContainer = $this->get('container');
        Phpfox::getBlock('pages.search-member', [
            'tab'       => $this->get('tab'),
            'container' => $sContainer,
            'page_id'   => $this->get('page_id'),
            'search'    => $this->get('search')
        ]);
        $this->html("$sContainer", $this->getContent(false));
        if ($this->get('search')) {
            $this->call('Core_Pages.searchingDone(true);');
        } else {
            $this->call('Core_Pages.hideSearchResults();');
        }
        $this->call('$Core.loadInit();');
    }

    public function memberModeration()
    {
        $sAction = $this->get('action');
        $aUserId = $this->get('item_moderate');
        $iPageId = $this->get('page_id');

        switch ($sAction) {
            case 'delete':
                foreach ($aUserId as $iUserId) {
                    Phpfox::getService('like.process')->delete('pages', $iPageId, $iUserId);
                }
                break;
        }

        $this->call('window.location.reload();');
    }

    public function feature()
    {
        if (Phpfox::getService('pages.process')->feature($this->get('page_id'), $this->get('type'))) {
            if ($this->get('type')) {
                $this->alert(_p('page_successfully_featured'), _p('feature'), 300, 150, true);
            } else {
                $this->alert(_p('page_successfully_un_featured'), _p('un_feature'), 300, 150, true);
            }
        }
    }

    public function sponsor()
    {
        Phpfox::isUser(true);
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return $this->alert('your_request_is_invalid');
        }
        $iPageId = $this->get('page_id');
        $iType = $this->get('type');
        if (Phpfox::getService('pages.process')->sponsor($iPageId, $iType)) {
            $aPage = Phpfox::getService('pages')->getForView($iPageId);
            if ($iType == '1') {
                $sModule = _p('pages');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'pages',
                    'item_id' => $iPageId,
                    'name'    => _p('default_campaign_custom_name', ['module' => $sModule, 'name' => $aPage['title']])
                ]);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('pages', $iPageId);
            }
            $this->alert($iType == '1' ? _p('page_successfully_sponsored') : _p('page_successfully_un_sponsored'), null, 300, 150, true);
        }
        return true;
    }

    public function showReassignOwner()
    {
        $this->setTitle(_p('reassign_owner'));

        Phpfox::getBlock('pages.reassign-owner', ['page_id' => $this->get('page_id')]);
    }

    public function reassignOwner()
    {
        $iPageId = $this->get('page_id');
        $sUserId = $this->get('user_id');
        if (!$iPageId) {
            return $this->alert(_p('failed_missing_page_id'));
        }
        if (!$sUserId) {
            return $this->alert(_p('please_select_a_friend_first'));
        }

        $this->call('$("#js_page_reassign_submit").addClass("disabled").attr("disabled",true);$("#js_page_reassign_loading").show();');

        if (Phpfox::getService('pages.process')->reassignOwner($iPageId, (int)trim($sUserId, ','))) {
            $this->alert(_p('reassign_owner_successfully'));
            $this->reload();
            return true;
        } else {
            $this->call('$("#js_page_reassign_submit").removeClass("disabled").removeAttr("disabled");$("#js_page_reassign_loading").hide();');
        }
        return false;
    }
}
