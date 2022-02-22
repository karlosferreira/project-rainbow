<?php
namespace Apps\Core_Marketplace\Installation\Version;

use Phpfox;

/**
 * Class v463
 * @package Apps\Core_Marketplace\Installation\Version
 */
class v463
{
    public function process()
    {
        $tableName = Phpfox::getT('marketplace_category');
        if(db()->tableExists($tableName) && db()->isIndex($tableName, 'is_active')) {
            db()->dropIndex($tableName, 'is_active');
            db()->addIndex($tableName, '`is_active`', 'is_active_only');
        }
    }
}
