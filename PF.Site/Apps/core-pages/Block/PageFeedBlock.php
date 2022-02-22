<?php
namespace Apps\Core_Pages\Block;

use Phpfox;
use Phpfox_Component;

class PageFeedBlock extends Phpfox_Component
{
    public function process()
    {
        if($iFeedId = $this->getParam('this_feed_id'))
        {
            $aPage = $this->getParam('custom_param_feed_page_' . $iFeedId);
            $aCoverPhoto = ($aPage['cover_photo_id'] ? Phpfox::getService('photo')->getCoverPhoto($aPage['cover_photo_id']) : false);
            if(empty($aPage))
            {
                return false;
            }
            $this->template()->assign([
                'aPage' => $aPage,
                'aCoverPhoto' => $aCoverPhoto
            ]);
        }
    }
}