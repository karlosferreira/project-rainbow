<?php
namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

class DeleteReasonController extends Phpfox_Component
{
    public function process()
    {
        if($aReason = Phpfox::getService('subscribe.reason')->getReasonById($this->request()->getInt('id')))
        {
            if($aReason['is_default'])
            {
                return Phpfox_Error::display(_p('Can not delete default reason'));
            }
            $isAjaxPopup = $this->request()->getInt('is_ajax_popup');
            $aReasonOptions = Phpfox::getService('subscribe.reason')->getReasonOptions($aReason['reason_id']);
            $this->template()->assign([
                'aReasonOptions' => $aReasonOptions,
                'isAjaxPopup' => $isAjaxPopup,
                'iReasonId' => $aReason['reason_id']
            ]);
        }
        elseif ($aVals = $this->request()->getArray('val'))
        {
            $bDelete = Phpfox::getService('subscribe.reason.process')->deteleReason($aVals);
            if($bDelete)
            {
                $this->url()->send('admincp.subscribe.reason',[],_p('Delete reason successfully'));
            }
        }
        else
        {
            return Phpfox_Error::display(_p('Can not delete reason with empty ID'));
        }
        $this->template()->setTitle(_p('subscribe_delete_reason'))
            ->setBreadCrumb(_p('subscriptions'),$this->url()->makeUrl('admincp.subscribe'))
            ->setBreadCrumb(_p('subscribe_delete_reason'), null, true)
            ->setActiveMenu('admincp.member.subscribe')
            ->setHeader('cache', [
                'jscript/admincp/admincp.js' => 'app_core-subscriptions',
                'head' => ['colorpicker/css/colpick.css' => 'static_script'],
            ]);
    }
}