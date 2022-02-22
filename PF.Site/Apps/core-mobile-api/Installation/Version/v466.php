<?php

namespace Apps\Core_MobileApi\Installation\Version;

use Phpfox;

class v466
{
    public function process()
    {
        $tableName = Phpfox::getT('mobile_api_menu_item');
        if (db()->tableExists($tableName)) {
            $friendMenu = db()->select('COUNT(*)')->from($tableName)->where(['module_id' => 'core', 'path' => 'friend'])->executeField();
            if ($friendMenu) {
                db()->update($tableName, [
                    'module_id' => 'friend'
                ], 'module_id = \'core\' AND path = \'friend\'');
            }
        }
    }
}