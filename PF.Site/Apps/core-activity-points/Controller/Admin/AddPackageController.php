<?php

namespace Apps\Core_Activity_Points\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class AddPackageController
 * @package Apps\Core_Activity_Points\Controller\Admin
 */
class AddPackageController extends Phpfox_Component
{
    public function process()
    {
        $bIsEdit = false;
        $bIsAjaxPopup = $this->request()->get('is_ajax_popup');
        if ($iEditId = $this->request()->getInt('id')) {
            if ($aPackage = Phpfox::getService('activitypoint.package')->getPackage($iEditId)) {
                $bIsEdit = true;
                $this->template()->assign([
                    'aForms' => $aPackage,
                    'bIsEdit' => $bIsEdit,
                    'sPhraseTitle' => $aPackage['title']
                ]);
                $this->setParam('currency_value_val[price]', unserialize($aPackage['price']));
            }
        }

        if (!empty($iEditId) && empty($aPackage)) {
            return Phpfox_Error::set(_p('Invalid Package'));
        }

        if ($aVals = $this->request()->getArray('val')) {
            $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();
            if (!isset($aVals['title'][$sDefaultLanguageCode]) || empty($aVals['title'][$sDefaultLanguageCode])) {
                Phpfox_Error::set(_p('provide_a_message_for_the_package'));
            }

            $aValidation = [
                'price' => array(
                    'title' => _p('activitypoint_invalid_price_add_package'),
                    'def' => 'currency',
                )
            ];

            $oValid = Phpfox_Validator::instance()->set(array(
                    'sFormName' => 'js_admincp_add_package_form',
                    'aParams' => $aValidation
                )
            );

            if ($oValid->isValid($aVals)) {
                if (empty($aVals['points'])
                    || (!empty($aVals['points']) && !is_numeric($aVals['points']))
                    || (!empty($aVals['points']) && (intval($aVals['points']) < 1))) {
                    Phpfox_Error::set(_p('activitypoint_invalid_points_add_package'));
                }

                if (Phpfox_Error::isPassed()) {
                    $aVals['points'] = intval($aVals['points']);

                    if ($bIsEdit) {
                        if (Phpfox::getService('activitypoint.package.process')->add($aVals, $iEditId)) {
                            $this->url()->send('admincp.activitypoint.package', null, _p('Update package successfully'));
                        }
                    } else {
                        if (Phpfox::getService('activitypoint.package.process')->add($aVals)) {
                            $this->url()->send('admincp.activitypoint.package', null, _p('Create package successfully'));
                        }
                    }
                }
            }
        }
        $sAddPhrase = '<script type="text/javascript">oTranslations["activitypoint_invalid_price_add_package"] = \'' . _p('activitypoint_invalid_price_add_package') . '\'; oTranslations["activitypoint_invalid_points_add_package"] = \'' . _p('activitypoint_invalid_points_add_package') . '\';</script>';

        $this->template()
            ->setTitle(($bIsEdit) ? _p('activitypoint_edit_package') : _p('activitypoint_create_new_package'))
            ->assign([
                'bIsAjaxPopup' => $bIsAjaxPopup,
                'sScript' => $sAddPhrase
            ]);
    }
}