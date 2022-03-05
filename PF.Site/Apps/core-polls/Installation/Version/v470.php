<?php

namespace Apps\Core_Polls\Installation\Version;

use Phpfox;

class v470
{
    public function process()
    {
        $tableName = Phpfox::getT('poll');
        if(db()->tableExists($tableName) && db()->isIndex($tableName, 'item_id_3')) {
            db()->dropIndex($tableName, 'item_id_3');
            db()->addIndex($tableName, '`question`(128),`item_id`,`view_id`,`privacy`', 'question_128_item_view_privacy');
        }
    }
}