<?php

namespace Apps\Core_Messages\Ajax;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Template;
use Phpfox_Url;

class Ajax extends Phpfox_Ajax
{

    public function showGroupMembers()
    {
        Phpfox::isUser(true);

        $iThreadId = $this->get('id');
        if (empty($iThreadId)) {
            return Phpfox_Error::display(_p('mail_invalid_conversation'));
        }

        Phpfox::getBlock('mail.group-members', [
            'thread_id' => $iThreadId
        ]);
    }

    public function markConversationUnarchive()
    {
        Phpfox::isUser(true);

        $iId = $this->get('id');
        $iCnt = Phpfox::getService('mail')->checkStatusConversationForUser($iId, Phpfox::getUserId());
        if ($iCnt) {
            Phpfox::getService('mail.process')->archiveThread($iId, 0);
            $this->call('$Core.reloadPage();');
            return true;
        }
        return Phpfox_Error::set(_p('mail_can_do_this_action'));
    }

    public function markConversationUnspam()
    {
        Phpfox::isUser(true);

        $iId = $this->get('id');
        $iCnt = Phpfox::getService('mail')->checkStatusConversationForUser($iId, Phpfox::getUserId(), false);
        if ($iCnt) {
            Phpfox::getService('mail.process')->applyConversationAction('un-spam', $iId);
            $this->call('$Core.reloadPage();');
            return true;
        }
        return Phpfox_Error::set(_p('mail_can_do_this_action'));
    }

    public function deleteAttachmentText()
    {
        Phpfox::isUser(true);

        Phpfox::isUser(true);
        $iAttachmentId = $this->get('attachment_id');
        $sAttachmentName = $this->get('attachment_name');
        $iMessageId = $this->get('message_id');
        if (!Phpfox::isModule('attachment')) {
            return false;
        }
        $aMessage = Phpfox::getService('mail')->getMessage($iMessageId);
        $aAttachment = Phpfox::getService('attachment')->getForDownload($iAttachmentId);
        if (!empty($aMessage)) {
            if ($aAttachment['is_image']) {
                $sImagePath = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aAttachment['server_id'],
                    'title' => $aAttachment['description'],
                    'path' => 'core.url_attachment',
                    'file' => $aAttachment['destination'],
                    'suffix' => '_view',
                    'max_width' => 'attachment.attachment_max_medium',
                    'max_height' => 'attachment.attachment_max_medium',
                    'return_url' => true
                ]);
                $sAttachmentText = '<img class="parsed_image" src="' . $sImagePath . '" alt="' . $sAttachmentName . '">';
            } else {
                $sAttachmentText = '[attachment="' . $iAttachmentId . '"]' . $sAttachmentName . '[/attachment]';

            }
            $sNewText = str_replace($sAttachmentText, '', $aMessage['text']);
            if (!empty($sNewText) || (int)$aMessage['total_attachment'] > 1) {
                Phpfox::getService('mail.process')->updateMessage(['text' => $sNewText], $iMessageId);
                if ($aAttachment['is_image']) {
                    $this->call('$("img[src=\'' . $sImagePath . '\']").remove();');
                } else {
                    $sUrl = Phpfox_Url::instance()->makeUrl('attachment', ['download', 'id' => $iAttachmentId]);
                    $this->call('$("a[href=\'' . $sUrl . '\']").remove();');
                }

            } else {
                Phpfox::getService('mail.process')->processAction($iMessageId, 'delete', $aMessage['thread_id']);
                if ($iTargetMessageId = $this->get('next_message_id')) {
                    $this->call('if($("#js_mail_thread_message_' . $iTargetMessageId . '").hasClass("is_last_message")){$("#js_mail_thread_message_' . $iTargetMessageId . '").removeClass("is_last_message");}else{$("#js_mail_thread_message_' . $iTargetMessageId . '").removeClass("is_middle_message").addClass("is_first_message");}');
                } elseif ($iTargetMessageId = $this->get('previous_message_id')) {
                    $this->call('if($("#js_mail_thread_message_' . $iTargetMessageId . '").hasClass("is_middle_message")){$("#js_mail_thread_message_' . $iTargetMessageId . '").removeClass("is_middle_message").addClass("is_last_message");}else{$("#js_mail_thread_message_' . $iTargetMessageId . '").removeClass("is_first_message");}');
                } else {
                    $sMessageDate = date('m/d/Y', $aMessage['time_stamp']);
                    $this->call('if($("[data-date=\'' . $sMessageDate . '\']").length == 1){if($("#js_mail_thread_message_' . $iMessageId . '").prev(".message-time-list").length) {$("#js_mail_thread_message_' . $iMessageId . '").prev(".message-time-list").remove();}else{$("#js_mail_thread_message_' . $iMessageId . '").closest(\'#mail_threaded_new_message\').prev(".message-time-list").remove();}}');
                    $this->call('setTimeout(function(){if($("#page_mail_index").length && !parseInt($("#js_check_load_more_conversation_content").val())){$Core.reloadPage();}},100);');
                }
                $this->call('$("#js_mail_thread_message_' . $iMessageId . '").remove();');
            }
            $this->call('$.ajaxCall("attachment.delete", "id=' . $iAttachmentId . '");');
            $this->call('$(".attachment-row").removeClass("rebuilt");coreMessages.processDeleteAttachment();');
        }

    }

    /**
     * load more conversations for mail.index page
     */
    public function loadMore()
    {
        Phpfox::isUser(true);

        $iPage = $this->get('page');
        $aSearch = $this->get('search');
        $iPageSize = 8;
        list(, $aConversations) = Phpfox::getService('mail')->getSearch($aSearch, $iPage, $iPageSize);
        if (!empty($aConversations)) {
            foreach ($aConversations as $aConversation) {
                Phpfox_Template::instance()->assign([
                        'aMail' => $aConversation,
                        'mailkey' => 1
                    ]
                )->getTemplate('mail.block.message-item');
            }
            $sContent = $this->getContent(false);
            $this->call('coreMessages.bContinueLoadMore = true;');
            $this->call("$('#js_load_more_page').val(" . ((int)$iPage + 1) . ");");
            $this->call("$('#js_check_load_more').val(1);");
            $this->append('#js_conversation_load_more .mCSB_container', $sContent);
            $this->call('$Core.loadInit();');
            $this->call('coreMessages.loadMailThread();');
        } else {
            $this->call("$('#js_check_load_more').val(0);");
        }
        $this->call("$('.js_core_messages_load_more_icon').remove();");
        $this->call('coreMessagesCustomConversationMassActions.checkSelect();');
    }

    /**
     * load more customlist for mail.customlist.index page
     */
    public function loadMoreCustomList()
    {
        Phpfox::isUser(true);

        $iPage = $this->get('page');
        $aSearch = $this->get('search');
        $iPageSize = 8;
        list(, $aCustomList) = Phpfox::getService('mail.customlist')->getSearch($aSearch, $iPage, $iPageSize);
        if (!empty($aCustomList)) {
            foreach ($aCustomList as $aCustom) {
                Phpfox_Template::instance()->assign([
                        'aCustom' => $aCustom,
                        'key' => 1
                    ]
                )->getTemplate('mail.block.customlist.custom-item');
            }
            $sContent = $this->getContent(false);
            $this->call('coreMessages.bContinueLoadMore = true;');
            $this->call("$('#js_load_more_page').val(" . ((int)$iPage + 1) . ");");
            $this->call("$('#js_check_load_more').val(1);");
            $this->append('#js_core_messages_custom_list_load_more .mCSB_container', $sContent);
            $this->call('coreMessages.initCustomListItemAction();');
        } else {
            $this->call("$('#js_check_load_more').val(0);");
        }
        $this->call("$('.js_core_messages_load_more_icon').remove();");
        $this->call('coreMessagesCustomListAction.checkSelect();');
    }

    /**
     * delete messages of conversation
     */
    public function actionMessagesMultiple()
    {
        Phpfox::getUserParam('admincp.has_admin_access', true);
        $sAction = $this->get('action');
        $sId = $this->get('data');
        $iThreadId = $this->get('thread_id');
        if (Phpfox::getService('mail.process')->processActionMultiple($sAction, $sId, $iThreadId)) {
            $this->call('$Core.reloadPage();');
        }
    }

    /**
     * delete a message of conversation in admincp
     */
    public function actionMessageAdmincp()
    {
        Phpfox::getUserParam('admincp.has_admin_access', true);
        $iId = $this->get('id');
        $sAction = $this->get('action');
        $iThreadId = $this->get('thread_id');
        if (Phpfox::getService('mail.process')->processAction($iId, $sAction, $iThreadId)) {
            $this->call('$Core.reloadPage();');
        }
    }

    /**
     * build friends and customlist for advanced compose message
     */
    public function buildList()
    {
        Phpfox::isUser(true);
        list($aFriends, $aList) = Phpfox::getService('mail')->buildFriendsAndCustomList();
        $this->call('coreMessages.search.aUsersBuild = ' . json_encode($aFriends) . ';');
        $this->call('coreMessages.search.aCustomListBuild = ' . json_encode($aList) . ';');
    }

    /**
     * change customlist title
     */
    public function changeCustomListTitle()
    {
        Phpfox::isUser(true);
        $aVals = $this->get('val');
        $sNewTitle = Phpfox::getService('mail.customlist.process')->changeCustomListTitle($aVals);
        if (Phpfox_Error::isPassed()) {
            $this->call('tb_remove();');
            $this->html('.js_customlist_name_' . $aVals['folder_id'], $sNewTitle);
            $this->call('$(".js_customlist_name_' . $aVals['folder_id'] . '").removeClass("hide_it").html("' . $sNewTitle . '");');
            $this->call('$(".js_custom_list_title_change").hide();');
        } else {
            $this->call('$(".js_custom_list_title_change").val("' . $aVals['old_title'] . '");');
        }
    }

    /**
     * delete a customlist
     */
    public function deleteCustom()
    {
        Phpfox::isUser(true);
        $iFolder = $this->get('folder_id');
        if (Phpfox::getService('mail.customlist.process')->delete($iFolder)) {
            $this->call('$Core.reloadPage();');
        } else {
            Phpfox_Error::set(_p('mail_cannot_delete_custom'));
        }
    }

    /**
     * load content of customlist
     */
    public function loadEditCustomList()
    {
        Phpfox::isUser(true);
        $iFolderId = $this->get('folder_id');
        $aCustom = Phpfox::getService('mail.customlist')->getCustomList($iFolderId);
        Phpfox::getComponent('mail.customlist.add', [
            'id' => $iFolderId
        ], 'controller');
        $sContent = $this->getContent(false);
        $sTitleContentDefault = '<div class="js_custom_list_title custon-list-input" data-id="' . $aCustom['folder_id'] . '"><span id="back-to-list-js" class="back-to-list hidden"><i class="ico ico-arrow-left-circle-o" aria-hidden="true"></i></span><span class="js_customlist_name_' . $aCustom['folder_id'] . ' fw-bold">' . $aCustom['name'] . '</span><input type="text" class="js_custom_list_title_change" maxlength="128" value="' . $aCustom['name'] . '" style="display:none;"></div>';
        $sTitleContentDefault .= '<div class="dropdown remove-list-group"><span class="btn fz-16" data-toggle="dropdown"><i class="ico ico-gear-o" aria-hidden="true"></i></span><ul class="dropdown-menu dropdown-menu-right"><li><a href="' . \Phpfox_Url::instance()->makeUrl('mail', ['customlist_id' => $aCustom['folder_id']]) . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>' . _p('mail_send_messages') . '</a></li><li class="item_delete"><a href="' . \Phpfox_Url::instance()->makeUrl('mail.customlist', ['delete' => $aCustom['folder_id']]) . '" class="sJsConfirm" data-message="' . _p('are_you_sure') . '"><i class="fa fa-trash-o" aria-hidden="true"></i>' . _p('Delete') . '</a></li></ul></div>';

        $this->html('.js_core_messages_custom_list #js_content', $sContent);
        $this->html('.js_core_messages_custom_list #js_title_content', $sTitleContentDefault);
        if (!empty($aCustom['users'])) {
            $users = [];
            foreach ($aCustom['users'] as $user) {
                $users[] = [
                    'user_id' => $user['user_id'],
                    'full_name' => $user['full_name'],
                    'user_image' => base64_encode(Phpfox::getLib('image.helper')->display([
                        'user' => $user,
                        'suffix' => '_120_square',
                        'max_width' => 32,
                        'max_height' => 32,
                        'no_link' => true,
                        'style' => "vertical-align:middle;",
                    ]))
                ];
            }

            $this->call('aCustomlistMembers = ' . json_encode($users) . ';');
            $this->call('coreMessages.aSelectedCustomlistUsers = {};');
            $this->call('coreMessages.initMembersCustomList();');
            $this->call('coreMessages.changeCustomListTitle();');
            $this->call('coreMessages.BackToList();');
            $this->call('coreMessages.customListAction();');
            $this->call('$Core.loadInit();');
        }
    }

    /**
     * actions for multiple customlist
     */
    public function customlist()
    {
        Phpfox::isUser(true);
        $sAction = $this->get('action');
        $sIds = $this->get('list_id');
        if ($sAction == 'delete') {
            Phpfox::getService('mail.customlist.process')->delete($sIds);
        }

        $this->call('$Core.reloadPage();');

    }

    /**
     * load add customlist content for mail.customlist.index page
     */
    public function loadAddCustomList()
    {
        Phpfox::isUser(true);
        Phpfox::getComponent('mail.customlist.add', [], 'controller');
        $sContent = $this->getContent(false);
        $sTitleContentDefault = '<div class="fw-bold create-custom"><span id="back-to-list-js" class="back-to-list hidden"><i class="ico ico-arrow-left-circle-o"></i></span>' . _p('mail_create_custom_list') . '</div>';
        $this->html('.js_core_messages_custom_list #js_content', $sContent);
        $this->html('.js_core_messages_custom_list #js_title_content', $sTitleContentDefault);
        $this->call('$Core.loadInit();');
        $this->call('coreMessages.customListAction();');
        $this->call('$Core.searchFriendsInput.setLiveUsers([]);');
        $this->call('$(".js_core_messages_custom_list article.item").removeClass("is_selected_thread");');
    }

    /**
     * change group conversation title
     */
    public function changeGroupTitle()
    {
        Phpfox::isUser(true);
        $aVals = $this->get('val');
        list(, $sNewTitle) = Phpfox::getService('mail.process')->changeGroupTitle($aVals);
        if (Phpfox_Error::isPassed()) {
            $this->call('tb_remove();');
            $this->html('#js_mail_title_' . $aVals['thread_id'], $sNewTitle);
        }
    }

    /**
     * execute a mass action for conversation
     */
    public function applyConversationAction()
    {
        Phpfox::isUser(true);
        $sAction = $this->get('action');
        $iThreadId = $this->get('thread_id');
        Phpfox::getService('mail.process')->applyConversationAction($sAction, $iThreadId);

        if ($sAction == 'spam') {
            $this->call('$("#js_message_' . $iThreadId . '").prev("._moderator").slideUp("slow");');
            $this->call('$("#js_message_' . $iThreadId . '").slideUp("slow", function(){$(this).remove();});');
            $this->call('$("#js_core_messages_action_content").html("").css("background-color","#ffffff");');

        } else if ($sAction == 'delete') {
            $this->call('$("#js_message_' . $iThreadId . '").prev("._moderator").slideUp();');
            $this->slideUp('#js_message_' . $iThreadId);
        }
    }


    /**
     * send a new message for single or group conversation
     */
    public function composeMessageWithAjax()
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('mail.can_compose_message', true);

        $aVals = $this->get('val');
        Phpfox::getComponent('mail.compose', [
            'val' => $aVals
        ], 'controller');
        if (Phpfox_Error::isPassed()) {
            $this->call('window.location.href = "' . Phpfox_Url::instance()->makeUrl('mail') . '";');
        }
    }

    /**
     * load compose message content
     */
    public function loadComposeController()
    {
        Phpfox::isUser(true);
        if (!Phpfox::getUserParam('mail.can_compose_message')) {
            return false;
        }
        Phpfox::getComponent('mail.compose', [], 'controller');
        $sContent = $this->getContent(false);

        Phpfox_Template::instance()->assign([
            'sForm' => 'js_ajax_compose_message'
        ])->getTemplate('mail.block.message-footer');
        $sFooterContent = $this->getContent(false);

        $this->html('#js_core_messages_content_title', $sContent);
        $this->html('#js_core_messages_action_content', '');
        $this->html('#js_core_messages_content_footer', $sFooterContent);
        $this->call('$("#js_core_messages_conversation_list li.core-messages__list-item").removeClass("is_selected_thread");');
        $this->call('$Core.loadInit();');
        $this->call('if($("#js_core_messages_search").length){$("#js_core_messages_search").focus()}');
    }

    /**
     * load conversation content
     */
    public function loadThreadController()
    {
        Phpfox::isUser(true);
        $iThreadId = $this->get('thread_id');
        $sView = $this->get('view');
        Phpfox::getService('mail.process')->threadIsRead($iThreadId);
        Phpfox::getComponent('mail.thread', [
            'id' => $iThreadId
        ], 'controller');
        $sContent = $this->getContent(false);
        list($aThread,) = Phpfox::getService('mail')->getThreadedMail($iThreadId);
        $sContentTitle = Phpfox::getService('mail.helper')->createConversationTitle($aThread, $sView);
        $this->html('#js_core_messages_action_content', $sContent);
        $this->html('#js_core_messages_content_title', $sContentTitle);
        $this->call('$("#js_compose_new_message").data("form", "js_ajax_mail_thread");');
        $this->call('$([document.documentElement, document.body]).animate({scrollTop: ($("#js_compose_message_textarea").offset().top - $(window).height() + 50 )}, 0);');
        $this->call('$("#js_compose_message_textarea").trigger("focus");');
        $this->call('coreMessages.bContinueLoadMoreConversationContent = true;');
        $this->call('coreMessages.initConversationContentCustomScroll();');
        $this->call('coreMessages.renewSelectedConversation(' . $iThreadId . ');');
        $this->call('$Core.loadInit();');
        $this->call('coreMessages.renewUnreadMessagesPanelCount();');

    }

    /**
     * mass action for multiple conversation
     */
    public function archive()
    {
        Phpfox::isUser(true);
        switch ($this->get('action')) {
            case 'delete':
                {
                    foreach ((array)$this->get('conversation_action') as $iId) {
                        Phpfox::getService('mail.process')->applyConversationAction('delete', $iId);
                    }
                    break;
                }
            case 'spam':
                {
                    foreach ((array)$this->get('conversation_action') as $iId) {
                        Phpfox::getService('mail.process')->applyConversationAction('spam', $iId);
                    }
                    break;
                }
            case 'un-spam':
                {
                    foreach ((array)$this->get('conversation_action') as $iId) {
                        Phpfox::getService('mail.process')->applyConversationAction('un-spam', $iId);
                    }
                    break;
                }
            case 'archive':
                {
                    foreach ((array)$this->get('conversation_action') as $iId) {
                        Phpfox::getService('mail.process')->archiveThread($iId, 1);
                    }
                    break;
                }
            case 'un-archive':
                {
                    foreach ((array)$this->get('conversation_action') as $iId) {
                        Phpfox::getService('mail.process')->archiveThread($iId, 0);
                    }
                    break;
                }
            case 'mark_as_read':
                {
                    foreach ((array)$this->get('conversation_action') as $iId) {
                        Phpfox::getService('mail.process')->threadIsRead($iId);
                    }
                    break;
                }
        }
        $this->call('$Core.reloadPage();');
    }

    /**
     * load more messages for a conversation
     */
    public function viewMoreThreadMail()
    {
        Phpfox::isUser(true);
        $sObjectId = $this->get('object_id');
        $iUserId = $this->get('user_id');
        $iLastMessageId = $this->get('message_id');
        $sDate = $this->get('date');
        $iType = (int)$this->get('type');
        list($aCon, $aMessages) = Phpfox::getService('mail')->getThreadedMail($this->get('thread_id'), $this->get('page'), false, $this->get('offset'));
        if ($aCon === false || ($aCon !== false && !count($aMessages))) {
            $this->call("$('.js_core_messages_load_more_icon').remove();");
            $this->call('$("#' . $sObjectId . '").find("#js_check_load_more_conversation_content").val(0);');
            return null;
        }

        if (!in_array(Phpfox::getUserId(), $aCon['user_id']) && !Phpfox::isAdmin()) {
            $this->call("$('.js_core_messages_load_more_icon').remove();");
            $this->call('$("#' . $sObjectId . '").find("#js_check_load_more_conversation_content").val(0);');
            return false;
        }
        list($aMessages, $aTimes) = Phpfox::getService('mail')->listDateForMessages($aMessages, true);
        foreach ($aTimes as $sTimeKey => $iTimeValue) {
            $this->call('$("#' . $sObjectId . '").find("#js_message_time_' . $sTimeKey . '").remove();');
        }
        $iCnt = 0;
        foreach ($aMessages as $iKey => $aMail) {
            $iCnt++;
            Phpfox_Template::instance()->assign([
                    'aMail' => $aMail,
                    'aCon' => $aCon,
                    'bIsLastMessage' => ($iCnt == count($aMessages) ? true : false)
                ]
            )->getTemplate('mail.block.entry');
        }
        $this->prepend('#' . $sObjectId . ' .mCSB_container:first', $this->getContent(false));
        $this->call('$Core.loadInit();');
        $bMore = ($iCnt >= 10);
        $this->call("$('.js_core_messages_load_more_icon').remove();");
        if ($bMore) {
            $this->call('coreMessages.bContinueLoadMoreConversationContent = true;');
            $this->call('$("#' . $sObjectId . '").mCustomScrollbar("scrollTo","-=200",{scrollInertia:0});');
        } else {
            $this->call('$("#' . $sObjectId . '").find("#js_check_load_more_conversation_content").val(0);');
        }
        $sJS = '';
        if (((int)$aMessages[$iCnt - 1]['user_id'] == (int)$iUserId) && ($sDate == $aMessages[$iCnt - 1]['message_with_same_date'])) {

            $sJS .= ($iType == 1) ? '$("#js_mail_thread_message_' . $iLastMessageId . '").removeClass("is_first_message").addClass("is_middle_message");' : '$("#js_mail_thread_message_' . $iLastMessageId . '").addClass("is_last_message");';
            $sJS .= '$("#js_mail_thread_message_' . $iLastMessageId . '").find(".mail_user_image").remove();';
            if ((int)$aMessages[$iCnt - 1]['message_with_order'] == 1) {
                $sJS .= '$("#js_mail_thread_message_' . $aMessages[$iCnt - 1]['message_id'] . '").addClass("is_first_message");';
            } else {
                $sJS .= '$("#js_mail_thread_message_' . $aMessages[$iCnt - 1]['message_id'] . '").removeClass("is_last_message").addClass("is_middle_message");';
            }
        }
        $this->call($sJS);
        return null;
    }

    /**
     * mark conversation archived
     */
    public function delete()
    {
        Phpfox::isUser(true);
        Phpfox::getService('mail.process')->archiveThread($this->get('id'));
        $this->call('$("#js_message_' . $this->get('id') . '").prev("._moderator").slideUp();');
        $this->slideUp('#js_message_' . $this->get('id'));
        $iNumberMessage = Phpfox::getService('mail')->getUnseenTotal();

        if (!$iNumberMessage) {
            $this->call('$(\'#js_total_new_messages\').html(\'\').hide();');
        } else {
            $this->call('$.ajaxCall("notification.update", "", "GET");');
        }
    }

    /**
     * show compose new message popup
     */
    public function compose()
    {
        Phpfox::isUser(true);
        if (Phpfox::getUserParam('mail.can_compose_message') == false) {
            echo '<script type="text/javascript">window.location = "' . Phpfox_Url::instance()->makeUrl('subscribe') . '"; </script>';
            return;
        }
        $this->setTitle(_p('new_message'));

        Phpfox::getComponent('mail.compose', null, 'controller');

        (($sPlugin = Phpfox_Plugin::get('mail.component_ajax_compose')) ? eval($sPlugin) : false);

        echo '<script type="text/javascript">$Core.loadInit();</script>';
    }

    /**
     * send new message with compose message popup
     */
    public function composeProcess()
    {
        Phpfox::isUser(true);

        $sType = $this->get('type');

        $aVal = $this->get('val');
        unset($aVal['js_compose_message_textarea']);
        $message = (empty($aVal['message'])) ? '' : $aVal['message'];
        if (empty($aVal['to']) && empty($aVal['customlist'])) {
            $this->call('$(\'#\' + tb_get_active()).find(\'.js_box_content:first textarea.on_enter_submit\').val(\'' . $message . '\');');
            $this->call('$(\'#\' + tb_get_active()).find(\'.js_box_content:first #js_ajax_compose_error_message\').html(\'<div class="error_message">' . str_replace("'", "\\'", _p('mail_please_select_at_least_one_friend_or_one_custom_list')) . '</div>\');');
            return false;
        }

        if (empty($aVal['message']) && empty($aVal['attachment'])) {
            $this->call('$(\'#\' + tb_get_active()).find(\'.js_box_content:first #js_ajax_compose_error_message\').html(\'<div class="error_message">' . str_replace("'", "\\'", _p('can_not_send_empty_message')) . '</div>\');');
            return false;
        }

        $this->errorSet('#js_ajax_compose_error_message');
        $aParams = ($sType == 'claim-page' && isset($aVal['page_id'])) ? ['page_id' => $aVal['page_id']] : [];
        $componentCompose = Phpfox::getComponent('mail.compose', $aParams, 'controller');
        $bReturn = $componentCompose->getReturn();
        if (!$bReturn) return false;

        if (Phpfox_Error::isPassed()) {
            $this->call('$(\'#\' + tb_get_active()).find(\'.js_box_content:first\').html(\'<div class="message">' . str_replace("'", "\\'", _p('your_message_was_successfully_sent')) . '</div>\'); setTimeout(\'tb_remove();\', 2000);');

            (($sPlugin = Phpfox_Plugin::get('mail.component_ajax_compose_process_success')) ? eval($sPlugin) : false);
        }

        return null;
    }

    /**
     * mark conversation read or unread
     */
    public function toggleRead()
    {
        Phpfox::getService('mail.process')->toggleThreadIsRead($this->get('id'));
    }

    /**
     * mark all conversation read
     */
    public function markAllRead()
    {
        Phpfox::isUser(true);
        Phpfox::getService('mail.process')->markAllRead();
        $this->call('$(\'#message-panel-body\').find(\'.is_new\').removeClass(\'is_new\');');
        $this->call('$(\'#js_total_unread_messages\').html(\'\').hide();');
        $this->call('$(\'#js_total_new_messages\').html(\'\').hide();');
        $this->slideAlert('#message-panel-body', _p('marked_all_as_read_successfully'));
        if ($this->get('reload')) {
            $this->reload();
        }
    }
}