<?php
namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

class AddCompareController extends Phpfox_Component
{
    public function process()
    {
        $bIsEdit = false;
        $isAjaxPopup = $this->request()->getInt('is_ajax_popup');
        $this->template()->assign([
            'isAjaxPopup' => $isAjaxPopup
        ]);
        if($aFeature = Phpfox::getService('subscribe.compare')->getFeature($this->request()->getInt('id')))
        {
            $bIsEdit = true;
            $this->template()->assign([
                'iCompareId' => $aFeature['compare_id'],
                'bIsEdit' => $bIsEdit,
                'sPhraseTitle' => $aFeature['feature_title']
            ]);
        }
        if($aVals = $this->request()->getArray('val'))
        {

            if($bIsEdit)
            {
                $aVals['compare_id'] = $this->request()->getInt('id');
                $bUpdate = Phpfox::getService('subscribe.compare.process')->updateFeature($aVals);
                if($bUpdate)
                {
                    $this->url()->send('admincp.subscribe.compare',[],_p('Update feature infomation successfully'));
                }

            }
            else
            {
                $bAdd = Phpfox::getService('subscribe.compare.process')->AddFeature($aVals);
                if($bAdd)
                {
                    $this->url()->send('admincp.subscribe.compare',[],_p('Add feature infomation successfully'));
                }
            }
        }
        $aPackages = Phpfox::getService('subscribe')->getPackages(false, true, true);
        if($bIsEdit)
        {
            $aTexts = [];
            $aFeatureForPackages = json_decode($aFeature['feature_value'], true);
            foreach($aPackages as $iKey => $aPackage)
            {
                foreach($aFeatureForPackages as $packageid => $feature)
                {
                    if(!empty($feature['text']))
                    {
                        $aTexts[$feature['text']] = $feature['text'];
                    }
                    if((int)$aPackage['package_id'] == (int)$packageid)
                    {
                        $feature['text_length'] = !empty($sText) ? strlen($sText) : 0;
                        $aPackages[$iKey]['compare_feature'] = $feature;
                    }
                }
            }

            $this->template()->assign([
               'aForms' => $aTexts
            ]);
        }
        $aDefaultLanguage  = array_shift(Phpfox::getService('language')->getAll(true));
        $this->template()->setTitle(($bIsEdit) ? _p('subscribe_edit_compare_feature').': '._p($aFeature['feature_title']) : _p('subscribe_add_compare_feature'))
            ->setBreadCrumb(_p('subscriptions'),$this->url()->makeUrl('admincp.subscribe'))
            ->setBreadCrumb(($bIsEdit ? _p('subscribe_edit_compare_feature') : _p('subscribe_add_compare_feature')), null, true)
            ->setActiveMenu('admincp.member.subscribe')
            ->setHeader('cache', [
                'jscript/admincp/admincp.js' => 'app_core-subscriptions',
                'head' => ['colorpicker/css/colpick.css' => 'static_script'],
            ])
            ->assign([
            'aPackages' => $aPackages,
            'sDefaultLanguage' => $aDefaultLanguage['language_id']
        ]);
    }
}