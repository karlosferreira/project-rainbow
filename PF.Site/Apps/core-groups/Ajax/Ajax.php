<?php

namespace Apps\PHPfox_Groups\Ajax;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Error;
use Phpfox_Image_Helper;
use Phpfox_Plugin;

/**
 * Class Ajax
 *
 * @package Apps\PHPfox_Groups\Ajax
 */
class Ajax extends Phpfox_Ajax
{
    public function request()
    {
        Phpfox::getBlock('groups.category');
    }

    public function delete()
    {
        Phpfox::getUserParam('groups.pf_group_browse', true);

        $groupId = (int)$this->get('id');

        if (empty($groupId) || empty($groupItem = Phpfox::getService('groups')->getForView($groupId)) || empty($groupItem['page_id'])) {
            return $this->alert(_p('unable_to_find_the_page_you_are_trying_to_delete'));
        } elseif (Phpfox::getUserId() != $groupItem['user_id'] && !Phpfox::getUserParam('groups.can_delete_all_groups')) {
            return $this->alert(_p('you_are_unable_to_delete_this_page'));
        }

        if (Phpfox::getService('groups.process')->delete($groupId)) {
            // clear cache if group's event is featured or sponsored
            if (Phpfox::isAppActive('Core_Events')) {
                $iEventFeatured = db()->select('COUNT(*)')
                    ->from(':event')
                    ->where('module_id = "groups" AND item_id = ' . db()->escape($groupId) . ' AND is_featured = 1')
                    ->executeField();

                if ($iEventFeatured) {
                    \Phpfox_Cache::instance()->remove('event_featured');
                }

                $iEventSponsored = db()->select('COUNT(*)')
                    ->from(':event')
                    ->where('module_id = "groups" AND item_id = ' . db()->escape($groupId) . ' AND is_sponsor = 1')
                    ->executeField();

                if ($iEventSponsored) {
                    \Phpfox_Cache::instance()->remove('event_sponsored');
                }

                // delete event belong to group
                db()->delete(':event', "module_id = 'groups' AND item_id = " . db()->escape($groupId));
            }

            if ($iProfileId = (int)$this->get('profile')) {
                $aUser = Phpfox::getService('user')->getUser($iProfileId);
                $redirectUrl = Phpfox::getLib('url')->makeUrl($aUser['user_name'] . '.' . 'groups');
            } else {
                $redirectUrl = Phpfox::getLib('url')->makeUrl('groups');
            }

            Phpfox::addMessage(_p('Group successfully deleted.'));
            $this->call('window.location.href= "' . $redirectUrl . '";');
        }
    }

    public function add()
    {
        Phpfox::isUser(true);

        if (($iId = Phpfox::getService('groups.process')->add($this->get('val')))) {
            $aPage = Phpfox::getService('groups')->getPage($iId);
            $this->call('window.location.href = \'' . \Phpfox_Url::instance()->makeUrl('groups.add',
                    ['id' => $aPage['page_id'], 'new' => '1']) . '\';');
        } else {
            $this->error(false);
            $sError = Phpfox_Error::get();
            $sError = implode('<br />', $sError);
            $this->call('$("#add_group_error_messages").show(); $("#add_group_error_messages").html("' . $sError . '");')
                ->call('$Core.Groups.resetSubmit();');
        }
    }

    public function removeLogo()
    {
        if (($aPage = Phpfox::getService('groups.process')->removeLogo($this->get('page_id'))) !== false) {
            $this->call('window.location.href = \'' . $aPage['link'] . '\';');
        }
    }

    public function deleteWidget()
    {
        $widgetId = (int)$this->get('widget_id');

        if (empty($widgetId) || empty($widget = Phpfox::getService('groups')->getWidgetById($widgetId))) {
            return false;
        }

        if (Phpfox::getService('groups.process')->deleteWidget($this->get('widget_id'))) {
            Phpfox::addMessage(_p(!empty($widget['is_block']) ? 'groups_widget_successfully_deleted' : 'groups_menu_successfully_deleted'));
            $redirectUrl = Phpfox::getLib('url')->makeUrl('groups.add.widget', [
                'id' => $widget['page_id'],
                'sub_tab' => !empty($widget['is_block']) ? 'widget' : 'menu',
            ]);
            $this->call('window.location.href = "' . $redirectUrl . '";');
        }
    }

    public function widget()
    {
        $this->setTitle((bool)$this->get('is_menu') ? _p('menus') : _p('widgets'));
        Phpfox::getComponent('groups.widget', [], 'controller');

        (($sPlugin = Phpfox_Plugin::get('groups.component_ajax_widget')) ? eval($sPlugin) : false);

        echo '<script type="text/javascript">$Core.loadInit();</script>';
    }

    public function addFeedComment()
    {
        Phpfox::isUser(true);

        $aVals = (array)$this->get('val');
        $iCustomPageId = isset($_REQUEST['custom_pages_post_as_page']) ? $_REQUEST['custom_pages_post_as_page'] : 0;
        if (($iCustomPageId && $iCustomPageId != $aVals['callback_item_id']) || !Phpfox::getService('groups')->hasPerm($aVals['callback_item_id'],
                'groups.share_updates')) {
            $this->alert(_p('You do not have permission to add comments'));
            $this->call('$Core.activityFeedProcess(false);');

            return;
        }

        $feed = [];
        if (isset($aVals['feed_id'])) {
            $feed = Phpfox::getService('feed')->getFeed($aVals['feed_id'], 'pages_');
        }

        if ((!isset($aVals['feed_id']) || (!empty($feed) && in_array($feed['type_id'], ['link', 'groups_comment']))) && Phpfox::getLib('parse.format')->isEmpty($aVals['user_status'])) {
            $this->alert(_p('add_some_text_to_share'));
            $this->call('$Core.activityFeedProcess(false);');

            return;
        }

        $aPage = Phpfox::getService('groups')->getPage($aVals['callback_item_id']);

        if (!isset($aPage['page_id'])) {
            $this->alert(_p('Unable to find the page you are trying to comment on.'));
            $this->call('$Core.activityFeedProcess(false);');

            return;
        }

        $sLink = Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        $aCallback = [
            'module'                => 'groups',
            'table_prefix'          => 'pages_',
            'link'                  => $sLink,
            'email_user_id'         => $aPage['user_id'],
            'subject'               => [
                'full_name_wrote_a_comment_on_your_group_tile_email_subject',
                ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title']]
            ],
            'message'               => [
                'full_name_wrote_a_comment_on_your_group_tile_email_content_link',
                ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aPage['title']]
            ],
            'notification'          => null,
            'notification_post_tag' => 'groups_post_tag',
            'feed_id'               => 'groups_comment',
            'item_id'               => $aPage['page_id'],
            'add_to_main_feed'      => true,
            'add_tag'               => true
        ];

        $aVals['parent_user_id'] = $aVals['callback_item_id'];

        if (isset($aVals['user_status']) && ($iId = Phpfox::getService('feed.process')->callback($aCallback)->addComment($aVals))) {
            if (!isset($aVals['feed_id'])) {
                \Phpfox_Database::instance()->updateCounter('pages', 'total_comment', 'page_id', $aPage['page_id']);

                defined('PHPFOX_PAGES_ADD_COMMENT') || define('PHPFOX_PAGES_ADD_COMMENT', 1);
                Phpfox::getService('feed')->callback($aCallback)->processAjax($iId);
            } else {
                $sStatus = Phpfox::getService('feed.tag')->stripContentHashTag($aVals['user_status'], $feed['item_id'], $feed['type_id']);
                $sStatus = Phpfox::getLib('parse.output')->parse($sStatus);
                $this->call('$Core.Groups.processEditFeedStatus(' . $feed['feed_id'] . ',' . json_encode($sStatus) . ($feed['type_id'] == 'link' ? ',1' : '') . ');');
                $this->call('tb_remove();');
                $this->call('setTimeout(function(){$Core.resetActivityFeedForm();$Core.loadInit();}, 500);');
            }

            if (!Phpfox::hasCallback('groups', 'mailToTagged')) {
                $aMentions = Phpfox::getService('user.process')->getIdFromMentions(Phpfox::getLib('parse.input')->prepare($aVals['user_status']), true, false);
                $aTagged = !empty($aVals['tagged_friends']) ? explode(',', $aVals['tagged_friends']) : [];
                if (!empty($aMentions) || !empty($aTagged)) {
                    Phpfox::getService('groups')->sendMailToTaggedUsers(array_unique(array_merge($aTagged, $aMentions)), $iId, $aPage['page_id']);
                }
            }
        } else {
            $this->call('$Core.activityFeedProcess(false);');
        }
    }

    public function changeUrl()
    {
        Phpfox::isUser(true);

        if (($aPage = Phpfox::getService('groups')->getForEdit($this->get('id')))) {
            $aVals = $this->get('val');

            $sNewTitle = Phpfox::getLib('parse.input')->cleanTitle($aVals['vanity_url']);

            if (Phpfox::getLib('parse.input')->allowTitle($sNewTitle,
                _p('Group name not allowed. Please select another name.'))) {
                if (Phpfox::getService('groups.process')->updateTitle($this->get('id'), $sNewTitle)) {
                    $this->call('$Core.reloadValidation.initEleData["js_form_groups_add"]["val[vanity_url]"] = "' . Phpfox::getLib('parse.output')->clean($sNewTitle) . '";');
                    $this->alert(_p('Successfully updated your group URL.'), _p('URL Updated!'), 300, 150, true);
                }
            }
            $sUrl = Phpfox::getService('groups')->getUrl($aPage['page_id']);
            $this->call('$(".page_section_menu_link").attr("href", "' . $sUrl . '");');
        }

        $this->call('delete $Core.reloadValidation.changedEleData["js_form_groups_add"]["val[vanity_url]"]; $Core.reloadValidation.preventReload();');
        $this->call('$Core.processForm(\'#js_groups_vanity_url_button\', true);');
    }

    public function signup()
    {
        Phpfox::isUser(true);
        if (Phpfox::getService('groups.process')->register($this->get('page_id'))) {
            $this->alert(_p('Successfully registered for this group. Your membership is pending an admins approval. As soon as your membership has been approved you will be notified.'));
            if (empty($this->get('request_inline'))) {
                $this->call('setTimeout(function(){window.location.reload();}, 4000);');
            }
        }
    }

    public function deleteRequest()
    {
        Phpfox::isUser(true);
        if (Phpfox::getService('groups.process')->deleteRegister($this->get('page_id'))) {
            $this->alert(_p('successfully_deleted_request_register_for_this_group'));
            if (empty($this->get('request_inline'))) {
                $this->call('setTimeout(function(){window.location.reload();}, 2000);');
            }
        }
    }

    public function moderation()
    {
        Phpfox::isUser(true);
        $sAction = $this->get('action');

        if (Phpfox::getService('groups.process')->moderation($this->get('item_moderate'), $this->get('action'))) {
            foreach ((array)$this->get('item_moderate') as $iId) {
                $this->remove('#js_pages_user_entry_' . $iId);
            }

            $this->updateCount();
            switch ($sAction) {
                case 'delete':
                    $sMessage = _p('Successfully deleted user(s).');
                    break;
                case 'approve':
                    $sMessage = _p('Successfully approved user(s).');
                    break;
                default:
                    $sMessage = _p('Successfully moderated user(s).');
                    break;
            }
            $this->alert($sMessage, _p('Moderation'), 300, 150, true);
        }

        $this->hide('.moderation_process');
        $this->call('setTimeout(function() {location.reload();}, 3000);');
    }

    public function logBackUser()
    {
        $this->error(false);
        Phpfox::isUser(true);
        $aUser = Phpfox::getService('groups')->getLastLogin();
        list ($bPass,) = Phpfox::getService('user.auth')->login($aUser['email'], $this->get('password'), true,
            $sType = 'email');
        if ($bPass) {
            Phpfox::getService('groups.process')->clearLogin($aUser['user_id']);

            $this->call('window.location.href = \'' . \Phpfox_Url::instance()->makeUrl('') . '\';');
        } else {
            $this->html('#js_error_pages_login_user',
                '<div class="error_message">' . implode('<br />', \Phpfox_Error::get()) . '</div>');
        }
    }

    public function pageModeration()
    {
        Phpfox::isUser(true);

        switch ($this->get('action')) {
            case 'approve':
                foreach ((array)$this->get('item_moderate') as $iId) {
                    Phpfox::getService('groups.process')->approve($iId);
                }
                Phpfox::addMessage(_p('Group(s) successfully approved.'));
                $this->call('window.location.reload();');
                break;
            case 'delete':
                foreach ((array)$this->get('item_moderate') as $iId) {
                    Phpfox::getService('groups.process')->delete($iId);
                }
                Phpfox::addMessage(_p('Group(s) successfully deleted.'));
                $this->call('window.location.reload();');
                break;
            default:
                $sMessage = '';
                $this->updateCount();
                $this->alert($sMessage, _p('Moderation'), 300, 150, true);
                $this->hide('.moderation_process');
                break;
        }
    }

    public function approve()
    {
        if (Phpfox::getService('groups.process')->approve($this->get('page_id'))) {
            $this->alert(_p('Group has been approved.'), _p('Group Approved'), 300, 100, true);
            $this->hide('#js_item_bar_approve_image');
            $this->hide('.js_moderation_off');
            $this->show('.js_moderation_on');
            $this->call('$(".item-pending").remove();');
        }
    }

    public function updateActivity()
    {
        Phpfox::getService('groups.process')->updateActivity($this->get('id'), $this->get('active'), $this->get('sub'));
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

        Phpfox::getLib('cache')->removeGroup('groups');
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

        Phpfox::getLib('cache')->removeGroup('groups');
    }


    public function setCoverPhoto()
    {
        $iPageId = $this->get('page_id');
        $iPhotoId = $this->get('photo_id');

        if (Phpfox::getService('groups.process')->setCoverPhoto($iPageId, $iPhotoId, false, true)) {
            $this->call('window.location.href = "' . Phpfox::permalink('groups', $this->get('page_id'),
                    '') . 'coverupdate_1";');

        }
    }

    public function repositionCoverPhoto()
    {
        $photoId = $this->get('photo_id');
        if (!empty($photoId) && Phpfox::getUserParam('photo.photo_must_be_approved')) {
            storage()->set('photo_cover_reposition_' . $photoId, $this->get('position'));
        } else {
            Phpfox::getService('groups.process')->updateCoverPosition($this->get('id'), $this->get('position'));
            if (empty($photoId)) {
                Phpfox::addMessage(_p('position_set_correctly'));
            }
        }
        $this->reload();
    }

    public function updateCoverPosition()
    {
        if (Phpfox::getService('groups.process')->updateCoverPosition($this->get('page_id'), $this->get('position'))) {
            $this->call('window.location.href = "' . Phpfox::permalink('groups', $this->get('page_id'), '') . '";');
            Phpfox::addMessage(_p('Position set correctly.'));
        }
    }

    public function removeCoverPhoto()
    {
        if (Phpfox::getService('groups.process')->removeCoverPhoto($this->get('page_id'))) {
            $this->call('window.location.href=window.location.href;');
        }
    }

    public function cropme()
    {
        $canUpload = !!$this->get('allow_upload', false);
        if ($canUpload) {
            $this->setTitle(_p('update_profile_picture'));
        }
        Phpfox::getBlock('groups.cropme', [
            'allow_upload' => $canUpload,
        ]);
        $this->call('<script>$Core.Groups.initCropMe();</script>');
    }

    public function processCropme()
    {
        $aVals = $this->get('val');
        $aPage = Phpfox::getService('groups')->getForEdit($aVals['page_id']);
        if (!Phpfox::getService('groups')->isAdmin($aPage) && !Phpfox::getUserParam('groups.can_edit_all_groups')) {
            return false;
        }

        $oFile = Phpfox::getLib('file');
        $sPageDir = Phpfox::getParam('pages.dir_image');
        $sTempPath = null;
        $tempFile = !empty($aVals['temp_file']) ? Phpfox::getService('core.temp-file')->getByFields($aVals['temp_file'], 'path, server_id') : null;
        $aUploadParams = array_merge(Phpfox::getService('groups')->getUploadPhotoParams(), [
            'type' => 'groups_photo',
            'update_space' => false,
        ]);

        if (isset($aVals['crop-data']) && !empty($aVals['crop-data'])) {
            if (!empty($tempFile['path'])) {
                $sProfileTemp = Phpfox::getParam('pages.dir_image') . sprintf($tempFile['path'], '');
            } else {
                if ($aPage['image_server_id']) {
                    $oTempImage = storage()->get('group/thumbnail/' . $aPage['page_id']);
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

            if (!file_exists($sTempPath) || filesize($sTempPath) <= 0) {
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
            if (empty($uploadedInfo = Phpfox::getService('groups.process')->uploadProfilePhoto($aPage, null, $sNewImageName))) {
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
            $iServerId = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            $aSizes = Phpfox::getService('groups')->getPhotoPicSizes();

            foreach ($aSizes as $iSize) {
                $oFile->unlink($sPageDir . sprintf($aPage['image_path'], '_' . $iSize));
                $oFile->unlink($sPageDir . sprintf($aPage['image_path'], '_' . $iSize . '_square'));
                if ($aPage['image_server_id']) {
                    Phpfox::getLib('cdn')->remove($sPageDir . sprintf($aPage['image_path'], '_' . $iSize));
                    Phpfox::getLib('cdn')->remove($sPageDir . sprintf($aPage['image_path'], '_' . $iSize . '_square'));
                }
            }

            Phpfox::getService('groups.process')->updateProfilePictureForThumbnail($aPage['page_id'], $sNewImageName, $iServerId);
            // update user photo for group
            Phpfox::getService('groups.process')->updateUserImageAndPhotoProfileForProcessCrop($aPage['page_id'], $sTempPath);

            $aPage = array_merge($aPage, [
                'image_path' => $sNewImageName,
                'image_server_id' => $iServerId,
            ]);

            /**
             * ++ Note: we do not replace original image for future editing thumbnail
             */

            //save temporary modified thumbnail image to local
            if ($aPage['image_server_id'] != 0) {
                $oTempImage = storage()->get('group/thumbnail/' . $aPage['page_id']);
                $sTempImagePath = $sPageDir . 'temp' . PHPFOX_DS . $oTempImage->value;
                if(file_exists($sTempImagePath)) {
                    @unlink($sTempImagePath);
                    Phpfox::getService('groups.process')->saveTempFileToLocalServer($aPage['page_id'], sprintf($sNewImageName, ''), $iServerId);
                }
            }
        }

        if (!empty($aVals['is_upload'])) {
            $this->call('$Core.reloadPage();');
        } else {
            //End crop image
            $sImagePath = Phpfox_Image_Helper::instance()->display([
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
            $this->call("tb_remove(); \$Core.Groups.profilePhoto.reset(); \$Core.reloadValidation.reset(true, 'js_form_groups_crop_me', true); \$Core.reloadValidation.preventReload();");
        }
    }

    public function deleteCategory()
    {
        $this->setTitle(_p('delete_category'));
        Phpfox::getBlock('groups.delete-category');
    }

    public function deleteCategoryImage()
    {
        Phpfox::getService('groups.type')->deleteImage($this->get('type_id'));
        $this->call('$(".category-image").remove();');
        $this->softNotice(_p('delete_category_image_successfully'));
    }

    public function addGroup()
    {
        Phpfox::getBlock('groups.add-group', ['type_id' => $this->get('type_id')]);
    }

    public function orderWidget()
    {
        $aOrdering = $this->get('ordering');

        if (empty($aOrdering)) {
            return;
        }

        foreach ($aOrdering as $iWidgetId => $iOrder) {
            Phpfox::getService('groups')->updateItemOrder(Phpfox::getT('pages_widget'), 'widget_id', $iWidgetId,
                $iOrder);
        }

        Phpfox::getLib('cache')->remove('groups_' . $this->get('page_id') . '_widgets');
    }

    public function orderMenu()
    {
        $aPageMenus = $this->get('page_menu');

        if (empty($aPageMenus)) {
            return;
        }

        Phpfox::getService('groups.process')->orderMenu($aPageMenus, $this->get('page_id'));
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

        $iId = Phpfox::getService('groups.process')->updateActiveMenu($iMenuId, $sMenuName, $iActive, $iPageId);

        if (empty($iMenuId)) {
            echo $iId;
        }
    }

    public function removeMember()
    {
        $iGroupId = $this->get('group_id');
        $iUserId = $this->get('user_id');

        if (!$iGroupId || !$iUserId) {
            return;
        }

        Phpfox::getService('like.process')->delete('groups', $iGroupId, $iUserId);
        $this->fadeOut("#groups-member-$iUserId")
            ->call('$Core.Groups.updateCounter("#all-members-count");');
    }

    public function approvePendingRequest()
    {
        $iSignUpId = $this->get('sign_up');
        $iUserId = $this->get('user_id');

        if (!$iSignUpId) {
            return;
        }

        Phpfox::getService('groups.process')->moderation([$iSignUpId], 'approve');
        $this->fadeOut("#groups-member-$iUserId");
        $this->call('$Core.Groups.showSuccessMessage(\'' . _p('groups_the_membership_to_join_this_group_was_approved_successfully') . '\');');
    }

    public function removePendingRequest()
    {
        $iSignUpId = $this->get('sign_up');
        $iUserId = $this->get('user_id');

        if (!$iSignUpId) {
            return;
        }

        Phpfox::getService('groups.process')->moderation([$iSignUpId], '');
        $this->fadeOut("#groups-member-$iUserId")
            ->call('$Core.Groups.updateCounter("#pending-members-count");');
        $this->call('$Core.Groups.showSuccessMessage(\'' . _p('groups_the_membership_to_join_this_group_was_denied_successfully') . '\');');
    }

    public function removeAdmin()
    {
        $iGroupId = $this->get('group_id');
        $iUserId = $this->get('user_id');

        if (!$iGroupId || !$iUserId) {
            return;
        }

        Phpfox::getService('groups.process')->removeAdmin($iGroupId, $iUserId);
        $this->fadeOut("#groups-member-$iUserId")
            ->call('$Core.Groups.updateCounter("#admin-members-count");');
    }

    public function getMembers()
    {
        $sContainer = $this->get('container');
        Phpfox::getBlock('groups.search-member', [
            'tab'       => $this->get('tab'),
            'container' => $sContainer,
            'group_id'  => $this->get('group_id'),
            'search'    => $this->get('search')
        ]);
        $this->html("$sContainer", $this->getContent(false));
        $searchText = $this->get('search');
        if (isset($searchText) && $searchText != '') {
            $this->call('$Core.Groups.searchingDone(true);');
        } else {
            $this->call('$Core.Groups.hideSearchResults();');
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
                    Phpfox::getService('like.process')->delete('groups', $iPageId, $iUserId);
                }
                break;
        }

        $this->call('window.location.reload();');
    }

    public function feature()
    {
        if (Phpfox::getService('groups.process')->feature($this->get('page_id'), $this->get('type'))) {
            if ($this->get('type')) {
                $this->alert(_p('group_successfully_featured'), _p('feature'), 300, 150, true);
            } else {
                $this->alert(_p('group_successfully_un_featured'), _p('un_feature'), 300, 150, true);
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
        if (Phpfox::getService('groups.process')->sponsor($iPageId, $iType)) {
            $aPage = Phpfox::getService('groups')->getForView($iPageId);
            if ($iType == '1') {
                $sModule = _p('groups');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'groups',
                    'item_id' => $iPageId,
                    'name'    => _p('default_campaign_custom_name', ['module' => $sModule, 'name' => $aPage['title']])
                ]);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('groups', $iPageId);
            }
            $this->alert($iType == '1' ? _p('group_successfully_sponsored') : _p('group_successfully_un_sponsored'), null, 300, 150, true);
        }
        return true;
    }

    public function showReassignOwner()
    {
        $this->setTitle(_p('reassign_owner'));

        Phpfox::getBlock('groups.reassign-owner', ['page_id' => $this->get('page_id')]);
    }

    public function reassignOwner()
    {
        $iPageId = $this->get('page_id');
        $sUserId = $this->get('user_id');
        if (!$iPageId) {
            return $this->alert(_p('failed_missing_group_id'));
        }
        if (!$sUserId) {
            return $this->alert(_p('please_select_a_friend_first'));
        }
        $this->call('$("#js_group_reassign_submit").addClass("disabled").attr("disabled",true);$("#js_page_reassign_loading").show();');
        if (Phpfox::getService('groups.process')->reassignOwner($iPageId, (int)trim($sUserId, ','))) {
            $this->alert(_p('reassign_owner_successfully'));
            $this->call('setTimeout(function(){$Core.reloadPage();},2000);');
            return true;
        } else {
            $this->call('$("#js_group_reassign_submit").removeClass("disabled").removeAttr("disabled");$("#js_page_reassign_loading").hide();');
        }
        return false;
    }

}
