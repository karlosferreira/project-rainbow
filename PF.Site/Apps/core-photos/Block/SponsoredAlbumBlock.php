<?php

namespace Apps\Core_Photos\Block;

use Phpfox;
use Phpfox_Component;

/**
 * Class SponsoredAlbumBlock
 * @package Apps\Core_Photos\Block
 */
class SponsoredAlbumBlock extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return false;
        }
        if (defined('PHPFOX_IS_PAGES_VIEW') || defined('PHPFOX_IS_USER_PROFILE')) {
            return false;
        }
        $iLimit = $this->getParam('limit', 4);
        if (!(int)$iLimit) {
            return false;
        }
        $iCacheTime = $this->getParam('cache_time', 5);
        $aSponsorAlbum = Phpfox::getService('photo.album')->getRandomSponsoredAlbum($iLimit, $iCacheTime);

        if (empty($aSponsorAlbum)) {
            return false;
        }

        foreach ($aSponsorAlbum as $iKey => $aAlbum) {
            $aSponsorAlbum[$iKey]['link'] = \Phpfox_Url::instance()->makeUrl('ad.sponsor', ['view' => $aAlbum['sponsor_id']]);
            Phpfox::getService('ad.process')->addSponsorViewsCount($aAlbum['sponsor_id'], 'photo.album');
        }

        $this->template()->assign([
                'sHeader'        => _p('photo_album_sponsored_block_title'),
                'aSponsorAlbums' => $aSponsorAlbum,
            ]
        );

        return 'block';
    }
}