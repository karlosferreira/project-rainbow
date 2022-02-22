<?php
namespace Apps\Core_Marketplace\Installation\Version;

use Phpfox_Queue;

/**
 * Class v464
 * @package Apps\Core_Marketplace\Installation\Version
 */
class v464
{
    public function process()
    {
        db()->delete(':block', 'component = \'related\' AND module_id = \'marketplace\' AND m_connection = \'marketplace.view\'');
        Phpfox_Queue::instance()->addJob('marketplace_convert_old_location', [], null, 3600);
    }
}
