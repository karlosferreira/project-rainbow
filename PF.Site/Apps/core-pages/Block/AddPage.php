<?php

namespace Apps\Core_Pages\Block;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class AddPage extends \Phpfox_Component
{
    public function process()
    {
        // get main category
        $iTypeId = $this->request()->get('type_id');
        $aMainCategory = Phpfox::getService('pages.type')->getById($iTypeId);

        if (!$aMainCategory) {
            return false;
        }
        $aCategories = Phpfox::getService('pages.type')->get();

        $bNoSubCategories = true;
        $aSubCategories = [];
        foreach($aCategories as $aCategory) {
            if(((int)$aCategory['type_id'] == (int)$iTypeId)) {
                if(!empty($aCategory['categories'])) {
                    $aSubCategories = $aCategory['categories'];
                    $bNoSubCategories = false;
                }
                break;
            }
        }
        $this->template()->assign([
            'aMainCategory' => $aMainCategory,
            'iTypeId' => $iTypeId,
            'aSubCategories' => $aSubCategories,
            'bNoSubCategories' => $bNoSubCategories
        ]);

        return 'block';
    }
}
