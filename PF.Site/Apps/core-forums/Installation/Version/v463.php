<?php

namespace Apps\Core_Forums\Installation\Version;

use Phpfox;

class v463
{
    public function process()
    {
        $tableName = Phpfox::getT('forum_thread');
        if(db()->tableExists($tableName)) {
            if(db()->isIndex($tableName, 'group_id')) {
                db()->dropIndex($tableName, 'group_id');
                db()->addIndex($tableName, '`title_url`(128),`group_id`,`view_id`', 'title_url_128_group_view');
            }
            if(db()->isIndex($tableName, 'group_id_3')) {
                db()->dropIndex($tableName, 'group_id_3');
                db()->addIndex($tableName, '`title_url`(128),`group_id`', 'title_url_128_group');
            }
            if(db()->isIndex($tableName, 'view_id_2')) {
                db()->dropIndex($tableName, 'view_id_2');
                db()->addIndex($tableName, '`title`(128),`view_id`', 'title_128_view');
            }
        }
    }
}