<?php

namespace Apps\Core_Events\Installation\Version;

use Phpfox;

class v470
{
    public function process()
    {
        $tableName = Phpfox::getT('event_category');
        if(db()->tableExists($tableName) && db()->isIndex($tableName, 'is_active')) {
            db()->dropIndex($tableName, 'is_active');
            db()->addIndex($tableName, '`name_url`(128),`is_active`', 'is_active_128');
        }
    }
}