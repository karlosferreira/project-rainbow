<?php
namespace Apps\Core_Subscriptions\Block;

use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class MessageBlock extends Phpfox_Component
{
    public function process()
    {

    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_block_message_clean')) ? eval($sPlugin) : false);
    }
}