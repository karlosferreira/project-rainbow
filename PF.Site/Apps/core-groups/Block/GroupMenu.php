<?php

namespace Apps\PHPfox_Groups\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

class GroupMenu extends Phpfox_Component
{
    public function process()
    {
        $aPage = $this->getParam('aPage');
        $aPageUser = Phpfox::getService('user')->getUser($aPage['page_user_id']);
        $aPageUser['item_type'] = $aPage['item_type'];
        $bIsPending = false;

        if((int)$aPage['reg_method'] == 1)
        {
            $aPendingUsers = array_unique(array_column(Phpfox::getService('groups')->getPendingUsers($aPage['page_id']), 'user_id'));
            $bIsPending = in_array(Phpfox::getUserId(), $aPendingUsers);
        }

        $this->template()->assign([
            'aPageUser' => $aPageUser,
            'bIsPending' => $bIsPending,
        ]);
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('groups.component_block_menu_clean')) ? eval($sPlugin) : false);
    }
}
