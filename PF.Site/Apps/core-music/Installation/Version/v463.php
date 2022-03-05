<?php

namespace Apps\Core_Music\Installation\Version;

use Phpfox;


class v463
{
    public function process()
    {
        $tableName = Phpfox::getT('music_genre');
        if (db()->tableExists($tableName) && db()->isIndex($tableName, 'name')) {
            db()->dropIndex($tableName, 'name');
            db()->addIndex($tableName, '`name`(128)', 'name_128');
        }

        $tableName = Phpfox::getT('music_song');
        if (db()->tableExists($tableName) && db()->isIndex($tableName, 'view_id_5')) {
            db()->dropIndex($tableName, 'view_id_5');
            db()->addIndex($tableName, '`title`(128),`view_id`,`privacy`', 'title_128_view_privacy');
        }
    }
}