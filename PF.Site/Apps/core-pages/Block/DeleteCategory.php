<?php

namespace Apps\Core_Pages\Block;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class DeleteCategory extends \Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);
        $iCategoryId = $this->request()->getInt('category_id');
        $bIsSub = $this->request()->get('is_sub', false);
        $iNumberOfChildren = Phpfox::getService('pages')->getItemsByCategory($iCategoryId, (boolean)$bIsSub, 0, 0, true, 'delete');
        $iNumberOfSubCategories = $bIsSub ? 0 : Phpfox::getService('pages.type')->countSubCategories($iCategoryId);

        $this->template()->assign(array(
                'iCategoryId' => $iCategoryId,
                'aAllTypes' => Phpfox::getService('pages.type')->get(),
                'iNumberOfChildren' => $iNumberOfChildren,
                'iNumberOfSubCategories' => $iNumberOfSubCategories,
                'bIsSub' => $bIsSub
            )
        );
    }
}
