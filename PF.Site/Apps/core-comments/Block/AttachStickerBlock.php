<?php

namespace Apps\Core_Comments\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class AttachStickerBlock extends Phpfox_Component
{
    public function process()
    {
        $iFeedId = $this->getParam('feed_id', 0);
        $iParentId = $this->getParam('parent_id', 0);
        $iEditId = $this->getParam('edit_id', 0);
        $bGlobalBlock = $this->getParam('is_global', true);
        $bUpdateOpened = $this->getParam('bUpdateOpened', false);
        if (!$bGlobalBlock && !$iFeedId && !$iParentId && !$iEditId && !$bUpdateOpened) {
            return false;
        }
        $iUserId = Phpfox::getUserId();
        $this->template()->assign([
            'iFeedId'         => $iFeedId,
            'iParentId'       => $iParentId,
            'iEditId'         => $iEditId,
            'bIsGlobal'       => $bGlobalBlock,
            'bUpdateOpened'   => $bUpdateOpened,
            'aStickerSets'    => Phpfox::getService('comment.stickers')->getAllStickerSetByUser($iUserId),
            'aRecentStickers' => Phpfox::getService('comment.stickers')->getRecentSticker($iUserId)
        ]);
        return 'block';
    }
}