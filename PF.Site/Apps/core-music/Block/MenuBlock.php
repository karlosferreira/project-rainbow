<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Block;

defined('PHPFOX') or exit('NO DICE!');


class MenuBlock extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {

    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = \Phpfox_Plugin::get('music.component_block_menu_clean')) ? eval($sPlugin) : false);
    }
}