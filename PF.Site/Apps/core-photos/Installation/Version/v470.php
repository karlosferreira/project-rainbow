<?php

namespace Apps\Core_Photos\Installation\Version;

use Phpfox;

/**
 * Class v465
 * @package Apps\Core_Photos\Installation\Version
 */
class v470
{
    public function process()
    {
        $tableName = Phpfox::getT('photo');
        if (db()->tableExists($tableName) && db()->isIndex($tableName, 'view_id_4')) {
            db()->dropIndex($tableName, 'view_id_4');
            db()->addIndex($tableName, '`title`(128),`view_id`,`privacy`', 'title_128_view_privacy');
        }

        $tableName = Phpfox::getT('photo_category');
        if (db()->tableExists($tableName) && db()->isIndex($tableName, 'name_url')) {
            db()->dropIndex($tableName, 'name_url');
        }
    }
}
