<?php

namespace Apps\PHPfox_Groups\Installation\Version;

class v464
{
    public function process()
    {
        // remove duplicated menu
        db()->delete(':menu', [
            'm_connection' => 'main',
            'module_id'    => 'groups',
            'product_id'   => 'phpfox',
            'var_name'     => 'menu_groups',
            'url_value'    => '/groups'
        ]);
    }
}
