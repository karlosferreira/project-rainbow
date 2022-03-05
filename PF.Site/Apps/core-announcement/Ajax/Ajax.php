<?php

namespace Apps\Core_Announcement\Ajax;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Plugin;

class Ajax extends Phpfox_Ajax
{
    public function toggleActiveAnnouncement()
    {
        $iAnnouncementId = $this->get('aid');
        $iActive = $this->get('active');
        Phpfox::getService('announcement.process')->toggleActiveAnnouncement($iAnnouncementId, $iActive);
    }

    /**
     * Hides the announcement block from the Dashboard
     */
    public function hideAnnouncement()
    {
        (($sPlugin = Phpfox_Plugin::get('announcement.component_ajax_hide__start')) ? eval($sPlugin) : false);
        if (Phpfox::getUserParam('announcement.can_close_announcement') == true) {
            if (($iId = $this->get('id')) && Phpfox::getService('announcement.process')->hide($iId)) {
                return true;
            }
        }
        (($sPlugin = Phpfox_Plugin::get('announcement.component_ajax_hide__end')) ? eval($sPlugin) : false);
        return $this->alert(_p('im_afraid_you_are_not_allowed_to_close_this_announcement'));
    }
}
