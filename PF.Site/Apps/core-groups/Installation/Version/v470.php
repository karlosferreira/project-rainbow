<?php

namespace Apps\PHPfox_Groups\Installation\Version;

use Phpfox;

class v470
{
    public function process()
    {
        $tableName = Phpfox::getT('pages');
        if(db()->tableExists($tableName) && db()->isIndex($tableName, 'view_id')) {
            db()->dropIndex($tableName, 'view_id');
            db()->addIndex($tableName, '`title`(128),`view_id`,`privacy`', 'title_128_view_privacy');
        }

        $tableName = Phpfox::getT('pages_url');
        if(db()->tableExists($tableName) && db()->isIndex($tableName, 'vanity_url')) {
            db()->dropIndex($tableName, 'vanity_url');
            db()->addIndex($tableName, '`vanity_url`(128)', 'vanity_url_128');
        }
    }
}
