<?php

namespace Apps\Core_Comments\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class EmoticonBlock extends Phpfox_Component
{
    public function process()
    {
        $iFeedId = $this->getParam('feed_id', 0);
        $iParentId = $this->getParam('parent_id', 0);
        $iEditId = $this->getParam('edit_id', 0);
        $bGlobalBlock = $this->getParam('is_global', true);
        if (!$bGlobalBlock && !$iFeedId && !$iParentId && !$iEditId) {
            return false;
        }
        $this->template()->assign([
            'iFeedId'          => $iFeedId,
            'iParentId'        => $iParentId,
            'iEditId'          => $iEditId,
            'bIsReply'         => $iParentId > 0 ? "true" : "false",
            'bIsEdit'          => $iEditId > 0 ? "true" : "false",
            'bIsGlobal'       => $bGlobalBlock,
            'aEmoticons'       => Phpfox::getService('comment.emoticon')->getAll(),
            'aRecentEmoticons' => Phpfox::getService('comment.emoticon')->getRecentEmoticon()
        ]);
        return 'block';
    }
}