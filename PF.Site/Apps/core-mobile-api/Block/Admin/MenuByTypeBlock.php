<?php

namespace Apps\Core_MobileApi\Block\Admin;

use Phpfox;
use Phpfox_Component;

class MenuByTypeBlock extends Phpfox_Component
{
    public function process()
    {
        $sType = $this->getParam('type');
        $aMenus = Phpfox::getService('mobile.admincp.menu')->getForAdmin($sType);
        $aMenuHeader = Phpfox::getService('mobile.admincp.menu')->getSectionHeader($sType);
        $this->template()->assign([
            'aMenus'       => $aMenus,
            'aForms'       => $aMenuHeader,
            'sTitle'       => _p($sType . '_menu_items'),
            'sHeaderTitle' => 'section_title'
        ]);
    }
}