<?php

namespace Apps\P_StatusBg\Service;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Error;
use Phpfox_Service;

class Process extends Phpfox_Service
{
    private $_sBTable;
    private $_sSBTable;
    private $_aLanguages;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('pstatusbg_collections');
        $this->_sBTable = Phpfox::getT('pstatusbg_backgrounds');
        $this->_sSBTable = Phpfox::getT('pstatusbg_status_background');
        $this->_aLanguages = Phpfox::getService('language')->getAll();
    }

    /**
     * @param $aVals
     * @param string $sName
     * @param bool $bIsEdit
     * @return bool|int
     * @throws \Exception
     */
    public function add($aVals, $sName = 'title', $bIsEdit = false)
    {
        if (isset($aVals[$sName]) && \Core\Lib::phrase()->isPhrase($aVals[$sName])) {
            $finalPhrase = $aVals[$sName];
            //Update phrase
            $this->updatePhrase($aVals);
        } else {
            $finalPhrase = $this->addPhrase($aVals, $sName);
        }
        if (!$finalPhrase || (!$aVals['time_stamp'] && !$bIsEdit)) {
            return false;
        }
        if ($bIsEdit) {
            $aCollection = Phpfox::getService('pstatusbg')->getForEdit($aVals['id']);
            if (!$aCollection) {
                return Phpfox_Error::set(_p('collection_you_are_looking_for_does_not_exists'));
            }
        }

        if ($aVals['is_default']) {
            db()->update($this->_sTable, ['is_default' => 0, 'is_active' => 0], 'is_default = 1 AND is_active = 1');
        } elseif ($aVals['is_active']) {
            db()->update($this->_sTable, ['is_active' => 0], 'is_default = 0 AND is_active = 1');
        }
        $aInsert = [
            'title' => $finalPhrase,
            'is_active' => $aVals['is_active'],
            'is_default' => $aVals['is_default'],

        ];

        if ($bIsEdit) {
            $iId = $aVals['id'];
            db()->update($this->_sTable, $aInsert, 'collection_id = ' . (int)$iId);
        } else {
            //Add all image of this collection
            $iTotalBg = db()->select('COUNT(*)')
                ->from($this->_sBTable)
                ->where('time_stamp = ' . (int)$aVals['time_stamp'])
                ->execute('getField');
            $aInsert['total_background'] = $iTotalBg;
            $aInsert['view_id'] = 0;
            $aInsert['time_stamp'] = PHPFOX_TIME;
            $iId = db()->insert($this->_sTable, $aInsert);
            if ($iId && $iTotalBg) {
                db()->update($this->_sBTable, ['collection_id' => $iId], 'time_stamp = ' . (int)$aVals['time_stamp']);
                $iFirstBgId = db()->select('background_id')->from($this->_sBTable)->where('collection_id =' . $iId)->order('ordering ASC, background_id ASC')->execute('getField');
                db()->update($this->_sTable, ['main_image_id' => $iFirstBgId], 'collection_id = ' . $iId);
            }
        }
        $this->cache()->removeGroup('pstatusbg');
        return $iId;
    }

    /**
     * Update phrase when edit a category
     *
     * @param array $aVals
     * @param string $sName
     */
    protected function updatePhrase($aVals, $sName = 'title')
    {
        foreach ($this->_aLanguages as $aLanguage) {
            if (isset($aVals[$sName . '_' . $aLanguage['language_id']])) {
                $name = $aVals[$sName . '_' . $aLanguage['language_id']];
                Phpfox::getService('language.phrase.process')->updateVarName($aLanguage['language_id'], $aVals[$sName],
                    $name);
            }
        }
    }

    /**
     * Add a new phrase for collection
     *
     * @param $aVals
     * @param string $sName
     * @param bool $bVerify
     * @return bool|mixed|null|string
     * @throws \Exception
     */
    protected function addPhrase($aVals, $sName = 'title', $bVerify = true)
    {
        $aFirstLang = end($this->_aLanguages);
        //Add phrases
        $aText = [];
        //Verify name

        foreach ($this->_aLanguages as $aLanguage) {
            if (isset($aVals[$sName . '_' . $aLanguage['language_id']]) && !empty($aVals[$sName . '_' . $aLanguage['language_id']])) {
                $aText[$aLanguage['language_id']] = $aVals[$sName . '_' . $aLanguage['language_id']];
            } elseif ($bVerify) {
                return Phpfox_Error::set((_p('Provide a "{{ language_name }}" ' . $sName . '.',
                    ['language_name' => $aLanguage['title']])));
            } else {
                $bReturnNull = true;
            }
        }
        if (isset($bReturnNull) && $bReturnNull) {
            //If we don't verify value, phrase can't be empty. Return null for this case.
            return null;
        }
        $name = $aVals[$sName . '_' . $aFirstLang['language_id']];
        $phrase_var_name = 'pstatusbg_collection_title_' . md5('pstatusbg_collection_title' . $name . PHPFOX_TIME);

        $aValsPhrase = [
            'var_name' => $phrase_var_name,
            'text' => $aText
        ];

        $finalPhrase = Phpfox::getService('language.phrase.process')->add($aValsPhrase);
        return $finalPhrase;
    }

    public function deleteCollection($iId)
    {
        Phpfox::isAdmin(true);

        $aCollection = Phpfox::getService('pstatusbg')->getForEdit($iId);
        if (!$aCollection || $aCollection['view_id'] == 1 || $aCollection['is_default'] == 1) {
            return Phpfox_Error::set(_p('collection_can_not_found_or_you_can_not_delete_it'));
        }
        db()->update($this->_sTable, ['is_deleted' => 1], 'collection_id =' . (int)$iId);
        db()->update($this->_sBTable, ['is_deleted' => 1], 'collection_id =' . (int)$iId);

        $this->cache()->removeGroup('pstatusbg');
        return true;
    }

    public function deleteBackground($iBackgroundId, $aCollection = null, $bCheckMain = true)
    {
        Phpfox::isAdmin(true);
        $aBackground = db()->select('*')
            ->from($this->_sBTable)
            ->where('background_id =' . (int)$iBackgroundId)
            ->execute('getRow');
        if (!$aBackground || $aBackground['view_id']) {
            return false;
        }

        if ($aCollection == null) {
            $aCollection = db()->select('c.*')
                ->from($this->_sTable, 'c')
                ->join($this->_sBTable, 'b', 'c.collection_id = b.collection_id')
                ->where('b.background_id =' . (int)$iBackgroundId)
                ->execute('getRow');
        }
        if (!$aCollection) {
            return false;
        }
        //Update main image for collection
        if ($aCollection['main_image_id'] == $iBackgroundId && $bCheckMain) {
            $iOtherBackground = db()->select('background_id')
                ->from($this->_sBTable)
                ->where('is_deleted = 0 AND collection_id = ' . $aCollection['collection_id'] . ' AND background_id <>' . (int)$iBackgroundId)
                ->order('ordering ASC, background_id ASC')
                ->execute('getField');
            db()->update($this->_sTable, ['main_image_id' => $iOtherBackground ? $iOtherBackground : 0],
                'collection_id =' . $aCollection['collection_id']);
            $this->cache()->removeGroup('pstatusbg');
        }
        //Mark sticker is deleted
        db()->update($this->_sBTable, ['is_deleted' => 1], 'background_id = ' . $aBackground['background_id']);

        db()->updateCounter('pstatusbg_collections', 'total_background', 'collection_id',
            $aCollection['collection_id'], true);
        return true;
    }

    public function toggleActiveCollection($iId, $iActive)
    {
        Phpfox::isAdmin(true);
        $aCollection = Phpfox::getService('pstatusbg')->getForEdit($iId);
        if (!$aCollection) {
            return Phpfox_Error::set(_p('collection_you_are_looking_for_does_not_exists'));
        }
        $iActive = (int)$iActive;
        if ($iActive == 1 && Phpfox::getService('pstatusbg')->countTotalActiveCollection() >= 2) {
            return Phpfox_Error::set(_p('you_cannot_activate_this_collection_because_the_maximum_number_of_active_collections_is_2'));
        }
        if ($iActive == 0 && $aCollection['is_default'] == 1) {
            return Phpfox_Error::set(_p('you_cannot_deactive_default_collection'));
        }
        $this->database()->update($this->_sTable, [
            'is_active' => ($iActive == 1 ? 1 : 0)
        ], 'collection_id = ' . (int)$iId);

        $this->cache()->removeGroup('pstatusbg');
        return true;
    }

    public function setDefault($iId)
    {
        Phpfox::isAdmin(true);
        $aCollection = Phpfox::getService('pstatusbg')->getForEdit($iId);
        if (!$aCollection) {
            return Phpfox_Error::set(_p('collection_you_are_looking_for_does_not_exists'));
        }
        if ($aCollection['is_default']) {
            return true;
        }
        db()->update($this->_sTable, ['is_default' => 0, 'is_active' => 0], 'is_default = 1');

        //Set default
        db()->update($this->_sTable, ['is_default' => 1, 'is_active' => 1], 'collection_id =' . (int)$iId);
        $this->cache()->removeGroup('pstatusbg');
        return true;
    }

    /**
     * @param $aParams
     * @param $iCollectionId
     * @return bool
     */
    public function updateImagesOrdering($aParams, $iCollectionId)
    {
        $iCnt = 0;
        foreach ($aParams['values'] as $mKey => $mOrdering) {
            if ($iCnt == 0) {
                db()->update($this->_sTable, ['main_image_id' => $mKey], 'collection_id =' . $iCollectionId);
            }
            $iCnt++;
            db()->update($this->_sBTable, array('ordering' => $iCnt),
                'background_id =' . $mKey . ' AND collection_id =' . $iCollectionId);
        }
        $this->cache()->removeGroup('pstatusbg');
        return true;
    }

    public function editUserStatusCheck($iItemId, $sType, $iUserId, $iActive)
    {
        return db()->update($this->_sSBTable, ['is_active' => $iActive], 'item_id = ' . (int)$iItemId . ' AND type_id = \'' . $sType . '\' AND user_id = ' . (int)$iUserId);
    }

    public function addBackgroundForStatus($sType, $iItemId, $iBackgroundId, $iUserId = null, $sModule = null)
    {
        if (!$iBackgroundId || !$iItemId) {
            return false;
        }
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        $aInsert = [
            'type_id' => $sType,
            'item_id' => $iItemId,
            'background_id' => $iBackgroundId,
            'user_id' => $iUserId,
            'time_stamp' => PHPFOX_TIME,
            'module_id' => $sModule
        ];
        $iId = db()->insert($this->_sSBTable, $aInsert);

        return $iId;
    }
}