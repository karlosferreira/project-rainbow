<?php

namespace Apps\PHPfox_Videos\Installation\Version;

use Phpfox;

class v470
{
    public function process()
    {
        $tableName = Phpfox::getT('video');
        if(db()->tableExists($tableName)) {
            if(db()->isIndex($tableName, 'in_process_4')) {
                db()->dropIndex($tableName, 'in_process_4');
                db()->addIndex($tableName, '`title`(128),`in_process`,`view_id`,`item_id`,`privacy`', 'title_128_process_view_item_privacy');
            }
            if(db()->isIndex($tableName, 'in_process_6')) {
                db()->dropIndex($tableName, 'in_process_6');
                db()->addIndex($tableName, '`title`(128),`in_process`,`view_id`,`privacy`', 'title_128_process_view_privacy');
            }
        }
    }
}
