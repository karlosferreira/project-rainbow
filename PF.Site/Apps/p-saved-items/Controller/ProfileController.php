<?php

namespace Apps\P_SavedItems\Controller;

use Phpfox;

class ProfileController extends \Phpfox_Component
{
    public function process()
    {
        $this->setParam('bIsProfile', true);
        Phpfox::getComponent('saveditems.collections', array('bNoTemplate' => true), 'controller');
    }
}