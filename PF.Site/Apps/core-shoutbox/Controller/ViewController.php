<?php

namespace Apps\phpFox_Shoutbox\Controller;

use Apps\phpFox_Shoutbox\Service\Shoutbox as sb;
use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

class ViewController extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::getUserParam('shoutbox.shoutbox_can_view')) {
            return Phpfox_Error::display(_p('cannot_display_due_to_privacy'));
        }
        $aShoutbox = sb::get()->getShoutbox($this->request()->getInt('id'), true, true);
        if (!$aShoutbox) {
            return \Phpfox_Error::set(_p('shoutbox_invalid_message'));
        }
        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $aShoutbox['user_id'])) {
            return Phpfox::getLib('module')->setController('error.invalid');
        }
        $parentModuleId = $aShoutbox['parent_module_id'];
        $parentItemId = $aShoutbox['parent_item_id'];
        if ($parentModuleId == 'pages') {
            //In pages, check can view shoutbox
            if (!Phpfox::isAppActive('Core_Pages') || !Phpfox::getService('pages')->hasPerm($parentItemId, 'shoutbox.view_shoutbox')) {
                return Phpfox_Error::display(_p('cannot_display_due_to_privacy'));
            }
        } elseif ($parentModuleId == 'groups') {
            //In groups, check can view shoutbox
            if (!Phpfox::isAppActive('PHPfox_Groups') || !Phpfox::getService('groups')->hasPerm($parentItemId, 'shoutbox.view_shoutbox')) {
                return Phpfox_Error::display(_p('cannot_display_due_to_privacy'));
            }
        }
        $this->template()
            ->setPhrase(Phpfox::getService('shoutbox.get')->getPhrases())
            ->setTitle(_p('full_name_message', ['full_name' => $aShoutbox['full_name']]))
            ->setBreadCrumb(_p('full_name_message', ['full_name' => $aShoutbox['full_name']]), '', true)
            ->setMeta('description', $aShoutbox['text'])
            ->assign('aShoutbox', $aShoutbox);
    }
}