<?php
namespace Apps\phpFox_Shoutbox\Installation\Version;

use Phpfox;

/**
 * Class v432
 * @package Apps\phpFox_Shoutbox\Installation\Version
 */
class v432
{
    public function process()
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(':cron')
            ->where('module_id = "shoutbox" AND php_code = "Phpfox::getService(\'shoutbox.process\')->cronDeleteOldMessages();"')
            ->execute('getField');
        if (!$iCnt) {
            db()->insert(':cron', [
                'module_id' => 'shoutbox',
                'product_id' => 'phpfox',
                'type_id' => 3,
                'every' => 1,
                'is_active' => 1,
                'php_code' => 'Phpfox::getService(\'shoutbox.process\')->cronDeleteOldMessages();'
            ]);
        }
    }
}