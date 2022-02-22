<?php

namespace Apps\Core_MobileApi\Controller\Admin;

use Phpfox;
use Phpfox_Component;

class MenuItemController extends Phpfox_Component
{
    public function process()
    {
        $iTotalMenu = Phpfox::getService('mobile.admincp.menu')->getTotalMenu();
        $this->template()->assign([
            'iTotalMenu' => $iTotalMenu
        ])
            ->setBreadCrumb(_p('manage_menus'), $this->url()->makeUrl('admincp.apps', ['id' => 'Core_MobileApi']))
            ->setTitle(_p('manage_menus'));
    }
}