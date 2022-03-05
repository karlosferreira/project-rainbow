<?php

namespace Apps\Core_MobileApi\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

class AddController extends Phpfox_Component
{
    public function process()
    {
        $bIsEdit = false;
        if ($iId = $this->request()->getInt('edit')) {
            $bIsEdit = true;
            $aForms = Phpfox::getService('mobile.admincp.menu')->getForEdit($iId);
            $this->template()->assign([
                'aForms' => $aForms,
                'aAccess' => (empty($aForms['disallow_access']) ? null : unserialize($aForms['disallow_access']))
            ]);
        }
        if ($aVals = $this->request()->getArray('val')) {
            if ($aVals = $this->_validate($aVals)) {
                if ($bIsEdit) {
                    if (Phpfox::getService('mobile.admincp.menu')->update($aVals)) {
                        $this->url()->send('admincp.app', ['id' => 'Core_MobileApi'], _p('menu_updated_successfully'));
                    }
                } else {
                    //TODO implement add menu later
                }
            }
        }

        $this->template()->assign([
            'bIsEdit' => $bIsEdit,
            'iEditId' => $iId,
            'aUserGroups' => Phpfox::getService('user.group')->get()
        ])->setHeader([
            'jscript/admin.js'   => 'app_core-mobile-api',
            'jscript/colpick.js' => 'app_core-mobile-api', //Must use custom lib to show picker on popup
        ])
            ->setBreadCrumb(($bIsEdit ? _p('edit_menu') : _p('add_new_menu')))
            ->setTitle($bIsEdit ? _p('edit_menu') : _p('add_new_menu'));
    }

    /**
     * validate input value
     *
     * @param $aVals
     *
     * @return bool
     */
    private function _validate($aVals)
    {
        $return = Phpfox::getService('language')->validateInput($aVals, 'name', false, false);
        if (!$return) {
            Phpfox_Error::reset();
            Phpfox_Error::set(_p('menu_name_is_required'));
        }
        return $return;
    }

}