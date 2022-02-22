<?php
namespace Apps\Core_Subscriptions\Service\Reason;

use Phpfox;
use Phpfox_Error;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Process extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_reason');
    }

    public function deteleReason($aVals)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        if((int)$aVals['option'] == 1)
        {
            $iReasonId = db()->select('reason_id')
                ->from(Phpfox::getT('subscribe_reason'))
                ->where('is_default = 1')
                ->execute('getSlaveField');
            db()->update(Phpfox::getT('subscribe_cancel_reason'),['reason_id' => (int)$iReasonId],'reason_id = '.$aVals['reason_id']);
        }
        else
        {
            db()->update(Phpfox::getT('subscribe_cancel_reason'),['reason_id' => (int)$aVals['extra_option']],'reason_id = '.$aVals['reason_id']);

        }
        db()->delete(Phpfox::getT('subscribe_reason'),'reason_id = '.$aVals['reason_id']);
        return true;
    }

    public function updateReason($iReasonId, $aVals)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();
        if (!isset($aVals['title'][$sDefaultLanguageCode]) || empty($aVals['title'][$sDefaultLanguageCode])) {
            return Phpfox_Error::set(_p('Provide reason content'));
        }

        $aLanguages = Phpfox::getService('language')->getAll();
        $aReason = Phpfox::getService('subscribe.reason')->getReasonById($iReasonId);
        foreach ($aLanguages as $aLanguage) {
            if (empty($aVals['title'][$aLanguage['language_code']])) {
                $aVals['title'][$aLanguage['language_code']] = $aVals['title'][$sDefaultLanguageCode];
            }
        }

        $bCount = 0;
        foreach($aLanguages as $aLanguage)
        {
            $iPhraseId = Phpfox::getLib('database')->select('phrase_id')
                ->from(':language_phrase')
                ->where('language_id="' . $aLanguage['language_id'] . '" AND var_name="' . $aReason['title'] . '"' )
                ->executeField();
            if($iPhraseId)
            {
                Phpfox::getService('language.phrase.process')->update($iPhraseId, $aVals['title'][$aLanguage['language_id']]);
            }
            else
            {
                $bCount++;
            }
        }

        if($bCount > 0)
        {
            \Core\Lib::phrase()->addPhrase($aReason['title'], $aVals['title']);
        }
        \Core\Lib::phrase()->clearCache();
        return true;

    }

    public function addReason($aVals)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();
        if (!isset($aVals['title'][$sDefaultLanguageCode]) || empty($aVals['title'][$sDefaultLanguageCode])) {
            return Phpfox_Error::set(_p('Provide reason content'));
        }

        $aLanguages = Phpfox::getService('language')->getAll();
        foreach ($aLanguages as $aLanguage) {
            if (empty($aVals['title'][$aLanguage['language_code']])) {
                $aVals['title'][$aLanguage['language_code']] = $aVals['title'][$sDefaultLanguageCode];
            }
        }
        $sTitleVarName = 'subscription_reason_title_' . md5($sDefaultLanguageCode . time());
        \Core\Lib::phrase()->addPhrase($sTitleVarName, $aVals['title']);

        $iLastOrderId = $this->database()->select('ordering')->from(Phpfox::getT('subscribe_reason'))->order('ordering DESC')->execute('getSlaveField');
        $iLastOrderId = empty($iLastOrderId) ?  1 : ((int)$iLastOrderId + 1);

        $aInsert = [
            'title' => $sTitleVarName,
            'ordering' => $iLastOrderId
        ];
        $iId = db()->insert(Phpfox::getT('subscribe_reason'), $aInsert);
        return $iId;
    }
    public function updateReasonActivity($iId, $iType)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $this->database()->update(Phpfox::getT('subscribe_reason'), array('is_active' => (int)($iType == '1' ? 1 : 0)),
            'reason_id = ' . (int)$iId);
    }
}