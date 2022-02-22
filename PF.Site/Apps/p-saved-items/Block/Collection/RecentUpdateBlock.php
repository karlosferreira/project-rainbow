<?php

namespace Apps\P_SavedItems\Block\Collection;

use Phpfox;
use Phpfox_Component;

/**
 * Class RecentUpdateBlock
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Block\Collection
 */
class RecentUpdateBlock extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::isUser()) {
            return false;
        }

        $limit = 3;
        $cacheTime = 5;

        $collections = Phpfox::getService('saveditems.collection')->getRecentUpdate($limit, $cacheTime);
        if (empty($collections)) {
            return false;
        }

        $aFooter = [];
        if (Phpfox::getUserParam('saveditems.can_create_collection')) {
            $aFooter = [
                '<i class="ico ico-plus mr-1"></i>' . _p('saveditems_create_new_collection_uppercase') => 'javascript:tb_show(\'' . _p('saveditems_new_collection') . '\', $.ajaxBox(\'saveditems.showCreateCollectionPopup\')); void(0);'
            ];
        }

        $this->template()->assign([
            'collections' => $collections,
            'sHeader' => _p('saveditems_recently_updated'),
            'aFooter' => $aFooter
        ]);

        return 'block';
    }
}