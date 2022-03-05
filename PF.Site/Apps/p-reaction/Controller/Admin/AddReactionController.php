<?php
namespace Apps\P_Reaction\Controller\Admin;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

class AddReactionController extends Phpfox_Component
{

    public function process()
    {
        $bIsEdit = false;
        if ($iId = $this->request()->getInt('id')) {
            $bIsEdit = true;
            $aEditItem = Phpfox::getService('preaction')->getForEdit($iId);
            if (!$aEditItem) {
                return Phpfox_Error::display(_p('reaction_you_are_looking_for_does_not_exists'));
            }
            if ($aEditItem['view_id'] == 2) {
                return Phpfox_Error::display(_p('you_cannot_edit_this_reaction'));
            }
            $this->template()->assign([
                'aForms' => $aEditItem,
                'iEditId' => $iId,
            ]);
        } else {
            $iTotalReactions = Phpfox::getService('preaction')->countReactions();
            if ($iTotalReactions >= 12) {
                return Phpfox_Error::display(_p('you_can_not_add_more_reaction'));
            }
        }

        if ($aVals = $this->request()->getArray('val')) {
            if ($aVals = $this->_validate($aVals)) {
                if ($bIsEdit) {
                    $aVals['id'] = $iId;
                    if (Phpfox::getService('preaction.process')->add($aVals, 'title', true)) {
                        $this->url()->send('admincp.app', ['id' => 'P_Reaction'],
                            _p('reaction_updated_successfully'));
                    }
                } else {
                    if (Phpfox::getService('preaction.process')->add($aVals)) {
                        $this->url()->send('admincp.app', ['id' => 'P_Reaction'], _p('reaction_added_successfully'));
                    }
                }
            }
        }
        $sTitle = $bIsEdit ? _p('edit_reaction') : _p('add_new_reaction');
        $this->template()
            ->setTitle($sTitle)
            ->setBreadCrumb(_p("Apps"), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p("Reaction"), $this->url()->makeUrl('admincp.app', ['id' => 'P_Reaction']))
            ->setBreadCrumb($sTitle)
            ->setHeader([
                'jscript/admin.js' => 'app_p-reaction',
                'colorpicker/js/colpick.js' => 'static_script',
                'head' => ['colorpicker/css/colpick.css' => 'static_script']
            ])
            ->assign([
                'sTitle' => $sTitle,
                'bIsEdit' => $bIsEdit,
                'iTotalReaction' => Phpfox::getService('preaction')->countReactions()
            ]);
    }

    /**
     * validate input value
     * @param $aVals
     *
     * @return bool
     */
    private function _validate($aVals)
    {
        return Phpfox::getService('language')->validateInput($aVals, 'title', false);
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('preaction.component_controller_admincp_manage_reactions_clean')) ? eval($sPlugin) : false);
    }
}

