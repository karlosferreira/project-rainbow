<?php

namespace Apps\P_SavedItems\Controller\Admin;

use Phpfox_Component;

/**
 * Class IndexController
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Controller\Admin
 */
class IndexController extends Phpfox_Component
{
    public function process()
    {
        header('Location: ' . $this->url()->makeUrl('admincp.user.group.add',
                ['group_id' => 2, 'module' => 'saveditems', 'setting' => 1, 'hide_app' => 1]));
        exit;
    }
}