<?php
namespace Apps\Core_Subscriptions\Service\Compare;

use Phpfox;
use Phpfox_Error;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Process extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_compare');
    }

    public function AddFeature($aVals)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();

        $aLanguages = Phpfox::getService('language')->getAll();
        foreach ($aLanguages as $aLanguage) {
            if (empty($aVals['feature_title'][$aLanguage['language_id']])) {
                $aVals['feature_title'][$aLanguage['language_id']] = $aVals['feature_title'][$sDefaultLanguageCode];
            }
        }
        $sTitleVarName = 'subscription_compare_title_' . md5($sDefaultLanguageCode . time());
        \Core\Lib::phrase()->addPhrase($sTitleVarName, $aVals['feature_title']);

        $aFeatures = [];
        $iCount = 1;
        foreach($aVals['features'] as $iPackageId => $aFeature)
        {
            $sTextVarName = '';
            if((int)$aFeature['option'] == 3)
            {
                if(!empty($aFeature['text'][$sDefaultLanguageCode]))
                {
                    foreach ($aLanguages as $aLanguage) {
                        if (empty($aFeature['text'][$aLanguage['language_id']])) {
                            $aFeature['text'][$aLanguage['language_id']] = $aFeature['text'][$sDefaultLanguageCode];
                        }
                    }
                    $sTextVarName = 'subscription_compare_text_'.$iCount.'_'. md5($sDefaultLanguageCode . time());
                    \Core\Lib::phrase()->addPhrase($sTextVarName, $aFeature['text']);
                    $iCount++;
                }
            }
            $aTemp = [
                'option' => $aFeature['option'],
                'text' => !empty($sTextVarName) ? $sTextVarName : ''
            ];
            $aFeatures[$iPackageId] = $aTemp;
        }

        $iLastOrderId = $this->database()->select('ordering')->from(Phpfox::getT('subscribe_compare'))->order('ordering DESC')->execute('getSlaveField');
        $iLastOrderId = ((int)$iLastOrderId + 1);
        $aInsert = [
            'feature_title' => $sTitleVarName,
            'feature_value' => json_encode($aFeatures),
            'ordering' => $iLastOrderId
        ];

        db()->insert(Phpfox::getT('subscribe_compare'), $aInsert);
        return true;
    }

    public function deleteCompare($iCompareId)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        Phpfox::getUserParam('admincp.has_admin_access', true);
        db()->delete(Phpfox::getT('subscribe_compare'),'compare_id = '.$iCompareId);
    }

    public function updateOrderCompare($aVals)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        if (!isset($aVals['ordering'])) {
            return Phpfox_Error::set(_p('not_a_valid_request'));
        }

        foreach ($aVals['ordering'] as $iId => $iOrder) {
            $this->database()->update(Phpfox::getT('subscribe_compare'), array('ordering' => (int)$iOrder), 'compare_id = ' . (int)$iId);
        }
        return null;
    }

    public function updateFeature($aVals)
    {
        $aEdit = Phpfox::getService('subscribe.compare')->getFeature($aVals['compare_id']);
        $aCompareInfo = json_decode($aEdit['feature_value'], true);
        $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();
        $aLanguages = Phpfox::getService('language')->getAll();

        foreach ($aLanguages as $aLanguage) {
            if (empty($aVals['feature_title'][$aLanguage['language_id']])) {
                $aVals['feature_title'][$aLanguage['language_id']] = $aVals['feature_title'][$sDefaultLanguageCode];
            }
        }

        foreach ($aLanguages as $aLanguage) {
            $iPhraseId = db()->select('phrase_id')
                ->from(Phpfox::getT('language_phrase'))
                ->where('language_id="' . $aLanguage['language_id'] . '" AND var_name="' . $aEdit['feature_title'] . '"' )
                ->executeField();

            Phpfox::getService('language.phrase.process')->update($iPhraseId, $aVals['feature_title'][$aLanguage['language_id']]);
        }


        $aFeatures = [];
        $iCount = 1;
        foreach($aVals['features'] as $iPackageId => $aFeature)
        {
            $sTextVarName = '';
            if(!empty($aCompareInfo[$iPackageId]))
            {
                if((int)$aFeature['option'] == 3)
                {
                    if(empty($aCompareInfo[$iPackageId]['text']))
                    {
                        if(!empty($aFeature['text'][$sDefaultLanguageCode]))
                        {
                            foreach ($aLanguages as $aLanguage) {
                                if (empty($aFeature['text'][$aLanguage['language_id']])) {
                                    $aFeature['text'][$aLanguage['language_id']] = $aFeature['text'][$sDefaultLanguageCode];
                                }
                            }
                            $sTextVarName = 'subscription_compare_text_'.$iCount.'_'. md5($sDefaultLanguageCode . time());
                            \Core\Lib::phrase()->addPhrase($sTextVarName, $aFeature['text']);
                            $iCount++;
                        }
                    }
                    else
                    {
                        $sTextVarName = $aCompareInfo[$iPackageId]['text'];
                        foreach ($aLanguages as $aLanguage) {
                            if (empty($aFeature['text'][$aLanguage['language_id']])) {
                                $aFeature['text'][$aLanguage['language_id']] = $aFeature['text'][$sDefaultLanguageCode];
                            }
                        }

                        foreach ($aLanguages as $aLanguage) {
                            $iPhraseId = db()->select('phrase_id')
                                ->from(Phpfox::getT('language_phrase'))
                                ->where('language_id="' . $aLanguage['language_id'] . '" AND var_name="' . $sTextVarName . '"' )
                                ->executeField();

                            Phpfox::getService('language.phrase.process')->update($iPhraseId, $aFeature['text'][$aLanguage['language_id']]);
                        }
                    }

                }
            }
            else
            {
                if((int)$aFeature['option'] == 3)
                {
                    if(!empty($aFeature['text'][$sDefaultLanguageCode]))
                    {
                        foreach ($aLanguages as $aLanguage) {
                            if (empty($aFeature['text'][$aLanguage['language_id']])) {
                                $aFeature['text'][$aLanguage['language_id']] = $aFeature['text'][$sDefaultLanguageCode];
                            }
                        }
                        $sTextVarName = 'subscription_compare_text_'.$iCount.'_'. md5($sDefaultLanguageCode . time());
                        \Core\Lib::phrase()->addPhrase($sTextVarName, $aFeature['text']);
                        $iCount++;
                    }
                }
            }

            $aTemp = [
                'option' => $aFeature['option'],
                'text' => !empty($sTextVarName) ? $sTextVarName : ''
            ];
            $aFeatures[$iPackageId] = $aTemp;
        }

        db()->update(Phpfox::getT('subscribe_compare'),['feature_value' => json_encode($aFeatures)],'compare_id = '.$aEdit['compare_id']);
        \Core\Lib::phrase()->clearCache();
        return true;
    }

    /* This function updates the table related to the Comparison of different packages
    */
    public function updateCompare($aVals)
    {
        Phpfox::isAdmin(true);
        $oParse = Phpfox::getLib('parse.input');
        // 1. Delete every record we have
        $this->database()->truncateTable(Phpfox::getT('subscribe_compare'));
        $iEmpty = 0;
        // 2. Go through each of the features
        foreach ($aVals as $aRow) {
            $aValue = array();
            // 2.1 Go through each of the packages
            foreach ($aRow['package'] as $iPackageId => $aValues) {
                if ($aValues['radio'] > 0) {
                    $aValue[] = array(
                        'package_id' => $iPackageId,
                        'value' => ($aValues['radio'] == 1 ? 'img_accept.png' : 'img_cross.png')
                    );
                } else {
                    if (!empty($aValues['text'])) {
                        $aValue[] = array('package_id' => $iPackageId, 'value' => $oParse->clean($aValues['text']));
                    }
                }
            }

            // 3. Insert this row
            if (!empty($aValue)) {
                // 3.1 if the title is empty then add our magic title to hide it
                if (empty($aRow['title'])) {
                    $aRow['title'] = 'no-feature-title-' . $iEmpty;
                    $iEmpty++;
                }
                // 3.2 insert!
                $this->database()->insert(Phpfox::getT('subscribe_compare'), array(
                    'feature_title' => $oParse->clean($aRow['title']),
                    'feature_value' => json_encode($aValue)
                ));
            }
        }

        return true;
    }
}