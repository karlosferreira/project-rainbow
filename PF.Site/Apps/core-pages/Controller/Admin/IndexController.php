<?php

namespace Apps\Core_Pages\Controller\Admin;

use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class IndexController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        // delete category
        if ($this->request()->get('delete')) {
            $sAction = $this->request()->get('child_action');
            $iCategoryId = $this->request()->get('category_id');
            $bIsSub = (boolean)$this->request()->get('is_sub');

            // process children
            if ($sAction === 'move') {
                $newCategory = explode('_', $this->request()->get('new_category_id'));
                // move pages
                Phpfox::getService('pages')->moveItemsToAnotherCategory($iCategoryId, $newCategory[0], $bIsSub,
                    count($newCategory) > 1, Phpfox::getService('pages.facade')->getItemTypeId());
                // move sub-categories and pages
                if (!$bIsSub) {
                    Phpfox::getService('pages.category')->moveSubCategoriesToAnotherType($iCategoryId, $newCategory[0]);
                }
            }
            $pageType = 0;
            if ($bIsSub) {
                $pageType = Phpfox::getService('pages.category')->getTypeIdByCategoryId($iCategoryId);
            }
            // delete category
            Phpfox::getService('pages.process')->deleteCategory($iCategoryId, $bIsSub, $sAction === 'del');
            $countSub = Phpfox::getService('pages.type')->countSubCategories($pageType);
            $this->url()->send('admincp.pages', $pageType && $countSub ? ['sub' => $pageType] : null, _p('successfully_deleted_the_category'));
        }

        $iId = $this->request()->getInt('sub');
        $bSubCategory = (boolean)$iId;
        if ($bSubCategory) {
            $parentCategory = Phpfox::getService('pages.type')->getById($iId);
            if ($parentCategory) {
                $this->template()->assign('sParentCategory', _p($parentCategory['name']));
            }
        }

        if ($bSubCategory) {
            $this->template()->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
                ->setBreadCrumb(_p('pages'), $this->url()->makeUrl('admincp.app', ['id' => 'Core_Pages']));
        }

        $this->template()->setTitle(($bSubCategory ? _p('manage_sub_categories') : _p('manage_categories')))
            ->setBreadCrumb(($bSubCategory ? _p('manage_sub_categories') : _p('manage_categories')))
            ->assign(array(
                    'bSubCategory' => $bSubCategory,
                    'aCategories' => ($bSubCategory ? Phpfox::getService('pages.category')->getForAdmin($iId) : Phpfox::getService('pages.type')->getForAdmin())
                )
            );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('pages.component_controller_admincp_index_clean')) ? eval($sPlugin) : false);
    }
}
