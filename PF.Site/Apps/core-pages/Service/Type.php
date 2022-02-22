<?php

namespace Apps\Core_Pages\Service;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class Type extends \Phpfox_Pages_Type
{
    /**
     * @return Facade
     */
    public function getFacade()
    {
        return Phpfox::getService('pages.facade');
    }

    /**
     * Get page types
     * @param int $iCacheTime , default 5
     * @param bool $bNotUseCache
     * @return array
     */
    public function get($iCacheTime = 5, $bNotUseCache = false)
    {
        $sCacheId = $this->cache()->set('pages_types');
        if ($bNotUseCache || !($aRows = $this->cache()->get($sCacheId, $iCacheTime))) {
            $aRows = $this->database()->select('*')
                ->from($this->_sTable)
                ->where('is_active = 1 AND item_type = 0')
                ->order('ordering ASC')
                ->execute('getSlaveRows');

            foreach ($aRows as $iKey => $aRow) {
                $aRows[$iKey]['pages_count'] = db()->select('count(*)')
                    ->from(':pages')
                    ->where(['type_id' => $aRow['type_id'], 'view_id' => 0])
                    ->executeField();
                $aRows[$iKey]['category_id'] = $aRow['type_id'];
                $aRows[$iKey]['url'] = Phpfox::permalink($this->getFacade()->getItemType() . '.category',
                    $aRow['type_id'], $aRow['name']);
                $aRows[$iKey]['categories'] = $aRows[$iKey]['sub'] = $this->getFacade()->getCategory()->getByTypeId($aRow['type_id']);
                if (isset($aRow['image_path']) && strpos($aRow['image_path'], 'default-category') === false) {
                    $aRows[$iKey]['image_path'] = sprintf($aRow['image_path'], '_200');
                }
            }

            $this->cache()->save($sCacheId, $aRows);
            $this->cache()->group('pages', $sCacheId);
        }

        return $aRows;
    }

    /**
     * Get number of sub categories belong to a type
     * @param $iTypeId
     * @return int
     */
    public function countSubCategories($iTypeId)
    {
        return db()->select('count(*)')->from(':pages_category')->where(['type_id' => $iTypeId])->executeField();
    }

    /**
     * Get type name
     *
     * @param $iTypeId
     * @return string
     */
    public function getTypeName($iTypeId)
    {
        return db()->select('name')->from(':pages_type')->where(['type_id' => $iTypeId])->executeField();
    }
}
