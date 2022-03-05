<?php

namespace Apps\phpFox_Shoutbox\Controller;

define('PHPFOX_AJAX_CALL_PROCESS', true);

use Phpfox;
use Apps\phpFox_Shoutbox\Service\Shoutbox as sb;

class PollingController extends \Admincp_Component_Controller_App_Index
{
    public function process()
    {
        if (!Phpfox::getUserParam('shoutbox.shoutbox_can_view')) {
            exit (json_encode([
                'error' => _p('cannot_display_due_to_privacy')
            ]));
        }

        $type = Phpfox::getLib('request')->get('type');
        $parent_module_id = Phpfox::getLib('request')->get('parent_module_id');
        $parent_item_id = Phpfox::getLib('request')->get('parent_item_id');

        if ($parent_module_id == 'pages') {
            //In pages, check can view shoutbox
            if (!Phpfox::isAppActive('Core_Pages') || !Phpfox::getService('pages')->hasPerm($parent_item_id, 'shoutbox.view_shoutbox')) {
                exit (json_encode([
                    'error' => _p('cannot_display_due_to_privacy')
                ]));
            }
        } elseif ($parent_module_id == 'groups') {
            //In groups, check can view shoutbox
            if (!Phpfox::isAppActive('PHPfox_Groups') || !Phpfox::getService('groups')->hasPerm($parent_item_id, 'shoutbox.view_shoutbox')) {
                exit (json_encode([
                    'error' => _p('cannot_display_due_to_privacy')
                ]));
            }
        }

        if ($type == 'pull') {
            $aJsonData = [];
            $iLastShoutboxId = Phpfox::getLib('request')->get('last', 0);
            if ($iLastShoutboxId == 0) {
                $iLastShoutboxId = Phpfox::getCookie('last_shoutbox_id');
            }
            $aData = sb::get()->check($iLastShoutboxId, $parent_module_id, $parent_item_id);
            if (isset($aData['shoutbox_id'])) {
                $aJsonData = $this->parseMessageData($aData);
            }

            if (function_exists('ob_get_level')) {
                while (ob_get_level()) {
                    ob_get_clean();
                }
            }
            if (count($aJsonData)) {
                echo json_encode($aJsonData);
            } else {
                echo json_encode(['empty' => true]);
            }
        } elseif ($type == 'push') {
            $aVals = [
                'parent_module_id' => $parent_module_id,
                'parent_item_id' => $parent_item_id,
                'text' => Phpfox::getLib('request')->get('text'),
            ];
            $iShoutboxId = sb::process()->add($aVals);
            if (function_exists('ob_get_level')) {
                while (ob_get_level()) {
                    ob_get_clean();
                }
            }
            if (is_int($iShoutboxId)) {
                $aPushShoutBox = sb::get()->getShoutbox($iShoutboxId, true);
                exit (json_encode($this->parseMessageData($aPushShoutBox)));
            } else {
                exit (json_encode([
                    'error' => $iShoutboxId
                ]));
            }
        } elseif ($type == 'more') {
            $iLastShoutboxId = Phpfox::getLib('request')->get('last');
            $aShoutboxes = sb::get()->getLast($iLastShoutboxId, $parent_module_id, $parent_item_id);
            $aJsonData = [];
            foreach ($aShoutboxes as $aShoutbox) {
                $aJson = [];
                if (isset($aShoutbox['shoutbox_id'])) {
                    $aJson = $this->parseMessageData($aShoutbox);
                }
                $aJsonData[] = $aJson;
            }

            if (function_exists('ob_get_level')) {
                while (ob_get_level()) {
                    ob_get_clean();
                }
            }
            if (count($aJsonData)) {
                echo json_encode($aJsonData);
            } else {
                echo json_encode(['empty' => true]);
            }
        }
        exit();
    }

    /**
     * @param $aMessage
     * @return array
     */
    private function parseMessageData($aMessage)
    {
        $parseOutput = Phpfox::getLib('parse.output');
        return [
            'shoutbox_id' => $aMessage['shoutbox_id'],
            'text' => $aMessage['text'],
            'user_avatar' => Phpfox::getLib('phpfox.image.helper')->display([
                'user' => $aMessage,
                'suffix' => '_120_square',
                'width' => 40,
                'height' => 40
            ]),
            'timestamp' => isset($aMessage['timestamp']) ? $aMessage['timestamp'] : 0,
            'parsed_time' => isset($aMessage['timestamp']) ? Phpfox::getLib('date')->convertTime($aMessage['timestamp']) : '',
            'user_profile_link' => Phpfox::getLib('url')->makeUrl($aMessage['user_name']),
            'user_full_name' => $parseOutput->clean($aMessage['full_name']),
            'user_type' => Phpfox::isAdmin() ? 'a' : 'u',
            'type' => Phpfox::getUserId() == $aMessage['user_id'] ? 's' : 'r',
            'can_edit' => $aMessage['canEdit'],
            'edit_title' => _p('shoutbox_edit_message'),
            'like_title' => _p('like'),
            'is_liked' => $aMessage['is_liked'],
            'total_like' => $aMessage['total_like'],
            'quoted_text' => $parseOutput->parse($aMessage['quoted_text']),
            'quoted_full_name' => $parseOutput->clean($aMessage['quoted_full_name']),
            'quoted_user_name' => $aMessage['quoted_user_name'],
            'quote_hover_title' => _p('quote'),
            'dismiss_hover_title' => _p('delete'),
            'hover_title' => _p('edit'),
            'can_delete' => $aMessage['canDeleteOwn'] || $aMessage['canDeleteAll'],
            'is_edited' => $aMessage['is_edited'],
            'edited_title' => _p('shoutbox_edited'),
            'likes_title' => _p('likes'),
            'unlike_title' => _p('unlike'),
            'can_quote' => Phpfox::isUser(),
            'can_show_action' => $aMessage['canShowAction'],
        ];
    }
}