<?php

namespace Apps\phpFox_Shoutbox\Ajax;

use Phpfox_Ajax;
use Apps\phpFox_Shoutbox\Service\Shoutbox as sb;
use Phpfox;

class Ajax extends Phpfox_Ajax
{
    public function processLike()
    {
        Phpfox::isUser(true);
        $shoutboxId = $this->get('shoutbox_id');
        $type = $this->get('type');
        $totalLikes = (int)sb::process()->processLike($shoutboxId, $type);
        $this->call('$("#shoutbox_message_' . $shoutboxId . '").find(".js_shoutbox_like").removeClass("' . ($type == 'like' ? 'unlike' : 'liked') . '").addClass("' . ($type == 'like' ? 'liked' : 'unlike') . '").data("type","' . ($type == 'like' ? 'unlike' : 'like') . '").attr("title","' . ($type == 'like' ? _p('unlike') : _p('like')) . '");');
        $this->call('$("#shoutbox_message_' . $shoutboxId . '").find(".js_shoutbox_text_total_like").html("' . ($totalLikes > 0 ? ('<a href=\"javascript:void(0);\" onclick=\"appShoutbox.showLikedMembers(' . (int)$shoutboxId . ');\">' . $totalLikes) . ' ' . ((int)$totalLikes == 1 ? _p('like') : _p('likes')) . '</a>' : '') . '");');
    }

    public function updateMessage()
    {
        Phpfox::isUser(true);
        $iShoutbox = $this->get('shoutbox_id');
        $sText = trim(urldecode($this->get('text')));
        $keepQuoted = $this->get('keep_quoted');
        if (empty($iShoutbox)) {
            return false;
        }
        if (!($aShoutbox = sb::get()->getShoutbox($iShoutbox))) {
            return false;
        }
        if (!$aShoutbox['canEdit']) {
            $this->call('js_box_remove($("#js_edit_shoutbox_message_content").get(0));');
            return \Phpfox_Error::set(_p('shoutbox_you_are_not_allowed_to_edit_message'));
        }
        if (empty($sText) && empty($keepQuoted)) {
            return \Phpfox_Error::set(_p('type_something_to_chat'));
        }
        sb::process()->update($iShoutbox, $sText, $keepQuoted);
        $aShoutbox = sb::get()->getShoutbox($iShoutbox, true);
        $this->call('js_box_remove($("#js_edit_shoutbox_message_content").get(0));');
        $this->call('$("#shoutbox_message_' . $iShoutbox . '").find(".item_view_content").html(' . json_encode($aShoutbox['text'])  . ');');
        $this->call('$("#shoutbox_message_' . $iShoutbox . '").find(".js_edited_text").html("' . _p('shoutbox_edited') . '").removeClass("hide");');
        $this->call('appShoutbox.changeText(' . $iShoutbox . ');');
    }

    public function openEditPopup()
    {
        Phpfox::isUser(true);
        Phpfox::getBlock('shoutbox.edit-message', [
            'shoutbox_id' => $this->get('shoutbox_id')
        ]);
    }

    public function add()
    {
        Phpfox::isUser(true);
        $iTimeId = $this->get('time_id');
        $aVals = [
            'parent_module_id' => $this->get('parent_module_id'),
            'parent_item_id' => $this->get('parent_item_id'),
            'text' => $this->get('text'),
        ];
        $iShoutboxId = sb::process()->add($aVals);
        $this->call("$('[data-value=\"new_shoutbox_" . $iTimeId . "\"]').attr('data-value', '" . $iShoutboxId . "');");
        $this->call("$('#new_shoutbox_" . $iTimeId . "').attr('id', 'shoutbox_message_" . $iShoutboxId . "');");
        $this->call("window.loadTime();");
    }

    public function delete()
    {
        Phpfox::isUser(true);
        $aShoutbox = sb::get()->getShoutbox($this->get('id'));
        return sb::process()->delete($aShoutbox);
    }

    public function test()
    {
        sb::get()->getShoutboxes();
    }
}