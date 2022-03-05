<?php

namespace Apps\P_SavedItems\Block;

use Phpfox;
use Phpfox_Component;

/**
 * Class CategoryBlock
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Block
 */
class CategoryBlock extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::isUser()) {
            return false;
        }

        $types = Phpfox::getService('saveditems')->getStatisticByType();
        $allTotalItem = 0;
        foreach ($types as $key => $type) {
            $types[$key]['url'] = Phpfox::getLib('url')->makeUrl('saved', ['type' => $type['type_id']]);
            $allTotalItem += $type['total_item'];
        }

        if ($allTotalItem == 0) {
            return false;
        }

        $this->template()->assign([
            'aSaveItemTypes' => $types,
            'sHeader' => _p('saveditems_item_types'),
            'currentType' => $this->request()->get('type'),
            'allTotalItem' => $allTotalItem
        ]);

        return 'block';
    }
}