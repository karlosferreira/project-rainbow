<?php

namespace Apps\Core_Events\Installation\Version;

use Phpfox_Queue;

class v472
{
    public function process()
    {
        Phpfox_Queue::instance()->addJob('event_convert_old_location', [], null, 3600);
    }
}