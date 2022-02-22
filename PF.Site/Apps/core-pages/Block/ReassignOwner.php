<?php

namespace Apps\Core_Pages\Block;

use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class ReassignOwner extends \Phpfox_Component
{
    public function process()
    {
        $iPageId = $this->getParam('page_id');
        if (!$iPageId) {
            return false;
        }
        $aPage = Phpfox::getService('pages')->getPage($iPageId);

        $this->template()->assign([
            'iPageId' => $iPageId,
            'aOwner' => Phpfox::getService('user')->getUser($aPage['user_id']),
            'bIncludeCurrentUser' => Phpfox::isAdmin() && (int)$aPage['user_id'] != Phpfox::getUserId()
        ]);
        return 'block';
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('pages.component_block_reassign_owner_clean')) ? eval($sPlugin) : false);
    }
}
