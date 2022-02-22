<?php
namespace Apps\phpFox_Shoutbox\Installation\Version;

use Phpfox;

/**
 * Class v430
 * @package Apps\phpFox_Shoutbox\Installation\Version
 */
class v430
{
    public function process()
    {
        $this->moveSetting();
    }

    private function moveSetting()
    {
        $oldSettings = ['shoutbox_day_to_delete_messages'];
        foreach($oldSettings as $oldSetting)
        {
            Phpfox::getService('user.group.setting.process')->deleteOldSetting($oldSetting, 'shoutbox');
        }
    }
}