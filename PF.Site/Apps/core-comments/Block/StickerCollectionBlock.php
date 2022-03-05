<?php

namespace Apps\Core_Comments\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class StickerCollectionBlock extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser();
        $iUserId = Phpfox::getUserId();
        //Show 8 stickers at first
        $iLimit = 8;
        $aUserStickers = Phpfox::getService('comment.stickers')->getAllStickerSetByUser($iUserId, $iLimit);
        $aAllStickers = Phpfox::getService('comment.stickers')->getAllSticker($iUserId, $iLimit);
        if (!count($aAllStickers)) {
            return false;
        }
        $this->template()->assign([
            'aUserStickers'    => $aUserStickers,
            'iTotalSets'       => count($aAllStickers),
            'iTotalMy'         => count($aUserStickers),
            'aAllStickers'     => $aAllStickers,
            'iStickerFeedId'   => $this->getParam('iFeedId', 0),
            'iStickerParentId' => $this->getParam('iParentId', 0),
            'iStickerEditId'   => $this->getParam('iEditId', 0)
        ]);
        return 'block';
    }
}