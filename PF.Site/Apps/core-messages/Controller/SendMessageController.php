<?php

namespace Apps\Core_Messages\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Ajax;
use Phpfox_Template;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

class SendMessageController extends Phpfox_Component
{
    public function process()
    {
        $aVals = $this->request()->get('val');
        $iHolderId = $this->request()->get('attachment_holder_id');
        $bIsAjaxPopup = (int)$this->request()->get('is_ajax_popup');
        $sView = $this->request()->get('view');
        $bPass = true;

        if (!empty($aVals['attachment'])) {
            $realAttachmentIds = Phpfox::getService('mail.helper')->hasAttachmentsOnSend($aVals['attachment']);
            $aVals['attachment'] = !empty($realAttachmentIds) ? implode(',', $realAttachmentIds) : null;
        }

        if ((!empty($aVals['message']) && empty(strip_tags($aVals['message'], '<img>'))) || (empty($aVals['message']) && empty($aVals['attachment']))) {
            echo "window.parent.sCustomMessageString = '" . _p('provide_message') . "';tb_show('Error', $.ajaxBox('core.message', 'height=150&width=300'));";
            exit();
        }
        if (($iNewId = Phpfox::getService('mail.process')->add($aVals)) && $bPass) {
            $aVals['message'] = strip_tags($aVals['message'], '<img>');
            list($aCon, $aMessages) = Phpfox::getService('mail')->getThreadedMail($iNewId);

            $aMessages = array_reverse($aMessages);

            if (!empty($aMessages[1])) {
                if (!Phpfox::getLib('date')->isToday($aMessages[1]['time_stamp'])) {
                    $aMessages[0]['message_time_list'][0] = Phpfox::getTime('m_d_Y');
                    $aMessages[0]['message_time_list'][1] = _p('today');
                }
            }
            if (empty($aMessages[1])) {
                $aMessages[0]['message_time_list'][0] = Phpfox::getTime('m_d_Y');
                $aMessages[0]['message_time_list'][1] = _p('today');
            }
            $aMessages[0]['timestamp_parsed'] = Phpfox::getTime(Phpfox::getParam('core.conver_time_to_string'),
                $aMessages[0]['time_stamp']);

            Phpfox_Template::instance()->assign([
                    'aMail' => $aMessages[0],
                    'aCon' => $aCon,
                    'bIsLastMessage' => true
                ]
            )->getTemplate('mail.block.entry');
            $sContent = base64_encode(Phpfox_Ajax::instance()->getContent(false));
            $sMessageContent = '';
            list(, $aMail,) = Phpfox::getService('mail')->getSearch(['view' => $sView], 0, 1);
            if(count($aMail)) {
                Phpfox_Template::instance()->assign([
                        'aMail' => $aMail[0],
                        'bIsSentbox' => false,
                        'bIsTrash' => false,
                        'mailkey' => 0
                    ]
                )->getTemplate('mail.block.message-item');
                $sMessageContent = base64_encode(Phpfox_Ajax::instance()->getContent(false));
            }

            echo 'var sConversationListContentTemp = "' . $sContent . '";';
            echo '$("#mail_threaded_new_message").append($Core.b64DecodeUnicode(sConversationListContentTemp));';
            echo "$('.js_mail_messages_content').mCustomScrollbar(\"scrollTo\", \"bottom\", {scrollInertia:0});";
            echo "$('.mail_thread_form_holder').addClass('not_fixed');";

            if (!$bIsAjaxPopup) {
                if($sMessageContent) {
                    echo '$("#js_message_' . $iNewId . '").remove();';
                    echo 'var sMessageListContent = $("#js_core_messages_conversation_list .mCSB_container").html();';
                    echo '$("#js_core_messages_conversation_list .mCSB_container").html("");';
                    echo 'var sMessageContentTemp = "' . $sMessageContent . '";';
                    echo '$("#js_core_messages_conversation_list .mCSB_container").html($Core.b64DecodeUnicode(sMessageContentTemp));';
                    echo '$("#js_core_messages_conversation_list .mCSB_container").append(sMessageListContent);';
                    echo '$("#js_conversation_load_more").mCustomScrollbar("scrollTo", "top", {scrollInertia:0});';
                    echo '$(".mail_holder").removeClass("is_selected_thread"); $("#js_message_' . $iNewId . '").addClass("is_selected_thread");';
                }
            } else {
                echo "coreMessages.onEnterSubmit();";
                echo "coreMessagesCustomAttachment.initAttachmentHolder();";
            }
            if (!empty($aVals['attachment'])) {
                echo '$("#' . $iHolderId . '").closest("#js_compose_new_message").find(".js_attachment_list .attachment-row").remove();';
                echo '$("#' . $iHolderId . '").closest("#js_compose_new_message").find(".attachment-form-action a.attachment-delete-all:first").addClass("hide");';
                echo '$("#' . $iHolderId . '").find(".attachment-counter:first").html("(0)");';
                echo '$("#' . $iHolderId . '").closest("#js_compose_new_message").find(".no-attachment:first").removeClass("hide");';
                echo '$("#' . $iHolderId . '").closest("#js_compose_new_message").find(".js_attachment").val("");';
            }
            echo '$(".attachment-row").removeClass("rebuilt");';
            echo 'setTimeout(function(){$Core.loadInit();},100);';
        } else {
            $aThread = Phpfox::getService('mail')->getThread($aVals['thread_id']);
            if($aThread['is_group']) {
                echo "window.parent.sCustomMessageString = '"._p('unable_to_send_message_to_this_group_because_member_disabled_option')."';";
            } else {
                echo "window.parent.sCustomMessageString = '"._p('unable_to_send_message_to_this_user')."';";
            }
            echo "tb_show('Error', $.ajaxBox('core.message', 'height=150&width=300'));";
            exit();
        }
        exit();
    }
}
