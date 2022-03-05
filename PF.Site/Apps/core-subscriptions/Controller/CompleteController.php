<?php
namespace Apps\Core_Subscriptions\Controller;

use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class CompleteController extends Phpfox_Component
{
    public function process()
    {
        $this->url()->send('subscribe');
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_complete_clean')) ? eval($sPlugin) : false);
    }
}