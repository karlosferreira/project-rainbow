<?php
/**
 * [PHPFOX_HEADER]
 */
namespace Apps\Core_Marketplace\Service\Category;

use Core_Service_Systems_Category_Process;
use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');


class Process extends Core_Service_Systems_Category_Process
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('marketplace_category');
        $this->_sTableData = Phpfox::getT('marketplace_category_data');
        $this->_sModule = 'marketplace';
        $this->_sItemId = 'listing_id';
        parent::__construct();
    }

    /**
     * @param       $iCategoryId
     * @param array $aVals
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteCategory($iCategoryId, $aVals = array())
    {
        $aCategory = db()->select('*')
            ->from($this->_sTable)
            ->where('category_id=' . intval($iCategoryId))
            ->execute('getSlaveRow');

        // Delete phrase of category
        if (isset($aCategory['name']) && Phpfox::isPhrase($aCategory['name'])) {
            Phpfox::getService('language.phrase.process')->delete($aCategory['name'], true);
        }

        if ($aVals && isset($aVals['delete_type'])) {
            switch ($aVals['delete_type']) {
                case 1:
                    $aSubs = db()->select('ec.category_id')
                        ->from($this->_sTable, 'ec')
                        ->where('ec.parent_id = ' . intval($iCategoryId))
                        ->execute('getSlaveRows');
                    $sCategoryIds = $iCategoryId;
                    foreach ($aSubs as $key => $aSub) {
                        $sCategoryIds .= ',' . $aSub['category_id'];
                    }
                    $aItems = db()->select('d.listing_id')
                        ->from($this->_sTableData, 'd')
                        ->where("d.category_id IN (" . $sCategoryIds . ')')
                        ->execute('getSlaveRows');
                    foreach ($aItems as $aItem) {
                        $iListingId = $aItem['listing_id'];
                        Phpfox::getService('marketplace.process')->delete($iListingId);
                    }
                    db()->delete($this->_sTable, 'parent_id = ' . intval($iCategoryId));
                    break;
                case 2:
                    if (!empty($aVals['new_category_id'])) {
                        $bIsParentCategory = empty($aCategory['parent_id']);
                        // if current category is parent and it has sub categories => move to new parent category
                        if ($bIsParentCategory) {
                            $aSubCategories = db()->select('category_id')->from($this->_sTable)->where(['parent_id' => $iCategoryId])->executeRows();
                            if (!empty($aSubCategories)) {
                                // update parent of sub categories
                                foreach (array_column($aSubCategories, 'category_id') as $iSubCategoryId) {
                                    db()->update($this->_sTable, [
                                        'parent_id' => $aVals['new_category_id']
                                    ], [
                                        'category_id' => $iSubCategoryId
                                    ]);

                                    // update all listings belong to these sub categories
                                    $aListingsOfSubCategories = db()->select('listing_id')->from($this->_sTableData)->where(['category_id' => $iSubCategoryId])->executeRows();

                                    foreach (array_column($aListingsOfSubCategories, 'listing_id') as $iListingId) {
                                        db()->update($this->_sTableData, [
                                            'category_id' => $aVals['new_category_id']
                                        ], 'listing_id = ' . $iListingId . ' AND category_id != ' . $iSubCategoryId);
                                    }
                                }
                            }
                        }

                        $aItems = db()->select('listing_id')
                            ->from($this->_sTableData)
                            ->where("category_id = " . intval($iCategoryId))
                            ->execute('getSlaveRows');

                        foreach (array_column($aItems, 'listing_id') as $iListingId) {
                            db()->delete($this->_sTableData, 'listing_id = ' . intval($iListingId));

                            db()->insert($this->_sTableData, [
                                'listing_id' => $iListingId,
                                'category_id' => $aVals['new_category_id']
                            ]);
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        db()->delete($this->_sTable, 'category_id = ' . intval($iCategoryId));
        $this->cache()->removeGroup('marketplace_category');

        return true;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_category_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}