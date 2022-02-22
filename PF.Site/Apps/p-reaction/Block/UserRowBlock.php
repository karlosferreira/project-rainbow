<?php

namespace Apps\P_Reaction\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class UserRowBlock extends Phpfox_Component
{
    public function process()
    {
        $iUserId = $this->getParam('user_id');

        if (empty($iUserId)) {
            return false;
        }

        $aUser = Phpfox::getService('user')->getUser($iUserId);
        if (Phpfox::isAppActive('Core_Pages') && empty($aUser['user_name']) && !empty($aUser['profile_page_id'])) {
            $aUser['page'] = Phpfox::getService('preaction')->getPage($aUser['profile_page_id'], Phpfox::getUserId());
        }
        $bIsFriend = Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $iUserId);
        $this->template()->assign(compact('aUser', 'bIsFriend'))
            ->assign([
                'sReactImage' => $this->getParam('react_image')
            ]);

        return 'block';
    }
}