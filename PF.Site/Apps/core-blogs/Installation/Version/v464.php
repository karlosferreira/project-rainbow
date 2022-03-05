<?php

namespace Apps\Core_Blogs\Installation\Version;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v464
 * @package Apps\Core_Blogs\Installation\Version
 */
class v464
{
    public function process(){
        // Update index length to 128
        $tableName = Phpfox::getT('blog');
        if(db()->tableExists($tableName) && db()->isIndex($tableName, 'title')) {
            db()->dropIndex($tableName, 'title');
            db()->addIndex($tableName, '`title`(128),`is_approved`,`privacy`,`post_status`', 'title_128_approved_privacy_status');
        }
    }
}
