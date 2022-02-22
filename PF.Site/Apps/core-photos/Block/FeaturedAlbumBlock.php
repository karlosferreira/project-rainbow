<?php

namespace Apps\Core_Photos\Block;

use Phpfox;
use Phpfox_Component;

/**
 * Class FeaturedAlbumBlock
 * @package Apps\Core_Photos\Block
 */
class FeaturedAlbumBlock extends Phpfox_Component
{
    public function process()
    {
        if (defined('PHPFOX_IS_PAGES_VIEW') || defined('PHPFOX_IS_USER_PROFILE')) {
            return false;
        }
        $iLimit = $this->getParam('limit', 4);
        if (!(int)$iLimit) {
            return false;
        }
        $iCacheTime = $this->getParam('cache_time', 5);
        $aAlbums = (array)Phpfox::getService('photo.album')->getFeaturedAlbums($iLimit, $iCacheTime);
        foreach ($aAlbums as $iKey => $aAlbum) {
            $aAlbums[$iKey]['link'] = $this->url()->makeUrl('photo.album.' . $aAlbum['album_id'] . '.' . $aAlbum['name']);
        }

        if (!count($aAlbums)) {
            return false;
        }
        $this->template()->assign([
                'sHeader'         => _p('photo_album_featured_block_title'),
                'aFeaturedAlbums' => $aAlbums,
            ]
        );


        return 'block';
    }
}