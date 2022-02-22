<?php

namespace Apps\Core_Quizzes\Installation\Version;

use Phpfox;

class v470
{
    public function process()
    {
        $tableName = Phpfox::getT('quiz');
        if(db()->tableExists($tableName) && db()->isIndex($tableName, 'view_id_4')) {
            db()->dropIndex($tableName, 'view_id_4');
            db()->addIndex($tableName, '`title`(128),`view_id`,`privacy`', 'title_128_view_privacy');
        }
    }
}