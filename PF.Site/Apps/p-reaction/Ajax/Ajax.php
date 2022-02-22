<?php

namespace Apps\P_Reaction\Ajax;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Ajax;

class Ajax extends Phpfox_Ajax
{
    public function toggleActiveReaction()
    {
        $iId = $this->get('id');
        $iActive = $this->get('active');
        $bResult = Phpfox::getService('preaction.process')->toggleActiveReaction($iId, $iActive);
        if (!$bResult) {
            $this->call('setTimeout(function(){window.location.reload();},2000);');
        }
    }

    public function showReactedUser()
    {
        $aUsers = Phpfox::getService('preaction')->getSomeReactedUser($this->get('type'), $this->get('item_id'),
            $this->get('react_id'), $this->get('table_prefix'));
        if ($aUsers) {
            $iTotal = $this->get('total_reacted');
            if ($iTotal > count($aUsers)) {
                $iRemain = $iTotal - count($aUsers);
            } else {
                $iRemain = 0;
            }
            $this->template()->assign([
                'aUsers' => $aUsers,
                'iRemainUser' => $iRemain
            ])->getTemplate('preaction.block.user-reacted-preview');
            echo json_encode($this->getContent(false));
            exit;
        }
    }

    public function updateMostReactionOnComment()
    {
        Phpfox::getBlock('preaction.reaction-list-mini', [
            'type_id' => $this->get('type'),
            'item_id' => $this->get('item_id'),
            'table_prefix' => $this->get('table_prefix')
        ]);
        echo json_encode($this->getContent(false));
        exit;
    }

    public function showListReactOnItem()
    {
        Phpfox::getBlock('preaction.list-react-by-item', [
            'type' => $this->get('type'),
            'item_id' => $this->get('item_id'),
            'table_prefix' => $this->get('table_prefix'),
            'react_id' => $this->get('react_id')
        ]);
    }
}