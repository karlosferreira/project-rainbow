<?php

namespace Apps\Core_Subscriptions\Installation\Version;

use Phpfox;

/**
 * Class v463
 * @package Apps\Core_Subscriptions\Installation\Version
 */
class v463
{
    public function process()
    {
        $this->updateCronTime();
    }

    private function updateCronTime()
    {
        db()->update(Phpfox::getT('cron'), ['type_id' => 3], 'module_id = "subscribe"');
    }
}