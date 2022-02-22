<?php

namespace Apps\P_Reaction\Service;

use Core\Lib;
use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Image;
use Phpfox_Service;

/**
 * Class Process
 * @package Apps\P_Reaction\Service
 */
class Process extends Phpfox_Service
{

    static $_aIconSize = [64];
    /**
     * Process constructor.
     */
    private $_aLanguages;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('preaction_reactions');
        $this->_aLanguages = Phpfox::getService('language')->getAll();
    }

    public function toggleActiveReaction($iId, $iActive)
    {
        Phpfox::isAdmin(true);

        $iActive = (int)$iActive;
        if ($iActive == 1 && Phpfox::getService('preaction')->countReactions(true) >= 6) {
            return Phpfox_Error::set(_p('you_can_active_maximum_6_reactions_at_a_time'));
        }
        $this->database()->update($this->_sTable, [
            'is_active' => ($iActive == 1 ? 1 : 0)
        ], 'id = ' . (int)$iId);

        $this->cache()->removeGroup('preaction');
        return true;
    }

    public function deleteReaction($iId)
    {
        Phpfox::isAdmin(true);
        $aReaction = Phpfox::getService('preaction')->getReactionById($iId);
        if (!$aReaction || $aReaction['view_id'] == 2) {
            return Phpfox_Error::set(_p('reaction_can_not_found_or_you_can_not_delete_this_reaction'));
        }
        db()->update($this->_sTable, ['is_deleted' => 1], 'id =' . (int)$iId);
        $this->cache()->removeGroup('preaction');
        return true;
    }

    public function add($aVals, $sName = 'title', $bIsEdit = false)
    {
        if (isset($aVals[$sName]) && Lib::phrase()->isPhrase($aVals[$sName])) {
            $finalPhrase = $aVals[$sName];
            //Update phrase
            $this->updatePhrase($aVals);
        } else {
            $finalPhrase = $this->addPhrase($aVals, $sName);
        }
        if (!$finalPhrase) {
            return false;
        }
        $oFile = Phpfox_File::instance();
        $oImage = Phpfox_Image::instance();
        $sPicStorage = Phpfox::getParam('core.dir_pic') . 'preaction/';
        $sIconName = '';
        if ($bIsEdit) {
            $aReaction = Phpfox::getService('preaction')->getForEdit($aVals['id']);
            if (!$aReaction) {
                return Phpfox_Error::set(_p('reaction_you_are_looking_for_does_not_exists'));
            }
        }
        if (isset($_FILES['icon']['name']) && ($_FILES['icon']['name'] != '')) {
            $aIcon = $oFile->load('icon', array('jpg', 'png'));
            if (!Phpfox_Error::isPassed()) {
                return false;
            }
            if ($aIcon !== false) {
                $sIconName = $oFile->upload('icon', $sPicStorage, 'icon');
                foreach (self::$_aIconSize as $size) {
                    $oImage->createThumbnail($sPicStorage . sprintf($sIconName, ''),
                        $sPicStorage . sprintf($sIconName, '_' . $size), $size, $size);
                }
            }
        } elseif (!$bIsEdit) {
            return Phpfox_Error::set(_p('please_select_an_icon_for_react_only_accept_jpg_jpeg_png'));
        }
        $aInsert = [
            'title' => $finalPhrase,
            'is_deleted' => 0,
            'color' => isset($aVals['color']) ? $aVals['color'] : '2681D5',
        ];
        if ($bIsEdit) {
            $iId = $aVals['id'];
            if (!empty($sIconName)) {
                $aInsert['icon_path'] = 'preaction/' . $sIconName;
                $aInsert['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            }
            db()->update($this->_sTable, $aInsert, 'id = ' . (int)$iId);

        } else {
            //De-active if have 6 active reactions
            if (Phpfox::getService('preaction')->countReactions(true) >= 6) {
                $aInsert['is_active'] = 0;
            }
            $aExtra = [
                'view_id' => 0,
                'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                'icon_path' => 'preaction/' . $sIconName,
            ];
            $iId = db()->insert($this->_sTable, array_merge($aInsert, $aExtra));
        }
        $this->cache()->removeGroup('preaction');
        return $iId;
    }

    /**
     * Add a new phrase for category
     *
     * @param array $aVals
     * @param string $sName
     * @param bool $bVerify
     *
     * @return null|string
     * @throws
     */
    protected function addPhrase($aVals, $sName = 'name', $bVerify = true)
    {
        $aFirstLang = end($this->_aLanguages);
        //Add phrases
        $aText = [];
        //Verify name

        foreach ($this->_aLanguages as $aLanguage) {
            if (isset($aVals[$sName . '_' . $aLanguage['language_id']]) && !empty($aVals[$sName . '_' . $aLanguage['language_id']])) {
                if (strlen($aVals[$sName . '_' . $aLanguage['language_id']]) > 12) {
                    return Phpfox_Error::set(_p('language_name_title_cannot_have_greater_than_limit_characters',
                        ['language_name' => $aLanguage['title'], 'limit' => 12]));
                }
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
        $phrase_var_name = 'preaction_react_title_' . md5('preaction_react_title' . $name . PHPFOX_TIME);

        $aValsPhrase = [
            'var_name' => $phrase_var_name,
            'text' => $aText
        ];

        $finalPhrase = Phpfox::getService('language.phrase.process')->add($aValsPhrase);
        return $finalPhrase;
    }
    /**
     * Update phrase when edit a category
     *
     * @param array  $aVals
     * @param string $sName
     */
    protected function updatePhrase($aVals, $sName = 'title')
    {
        foreach ($this->_aLanguages as $aLanguage){
            if (isset($aVals[$sName . '_' . $aLanguage['language_id']])){
                $name = $aVals[$sName . '_' . $aLanguage['language_id']];
                Phpfox::getService('language.phrase.process')->updateVarName($aLanguage['language_id'], $aVals[$sName], $name);
            }
        }
    }
}