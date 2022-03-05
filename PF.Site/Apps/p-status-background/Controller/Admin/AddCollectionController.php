<?php
namespace Apps\P_StatusBg\Controller\Admin;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

/**
 * Class AddCollectionController
 * @package Apps\P_StatusBg\Controller\Admin
 */
class AddCollectionController extends Phpfox_Component
{

    public function process()
    {
        $bIsEdit = false;
        if ($iId = $this->request()->getInt('id')) {
            $bIsEdit = true;
            $aEditItem = Phpfox::getService('pstatusbg')->getForEdit($iId);
            if (!$aEditItem) {
                return Phpfox_Error::display(_p('collection_you_are_looking_for_does_not_exists'));
            }
            $aEditItem['params'] = [
                'id' => $aEditItem['collection_id']
            ];
            $aBackgrounds = Phpfox::getService('pstatusbg')->getImagesByCollection($iId);
            $this->template()->assign([
                'aForms' => $aEditItem,
                'aBackgrounds' => $aBackgrounds,
                'iEditId' => $iId,
            ]);
        }

        if ($aVals = $this->request()->getArray('val')) {
            if ($aVals = $this->_validate($aVals)) {
                if ($bIsEdit) {
                    $aVals['id'] = $iId;
                    if (Phpfox::getService('pstatusbg.process')->add($aVals, 'title', true)) {
                        $this->url()->send('admincp.app', ['id' => 'P_StatusBg'],
                            _p('collection_updated_successfully'));
                    }
                } else {
                    $aVals['time_stamp'] = $this->request()->getInt('time_stamp');
                    if (Phpfox::getService('pstatusbg.process')->add($aVals)) {
                        $this->url()->send('admincp.app', ['id' => 'P_StatusBg'],
                            _p('collection_added_successfully'));
                    }
                }
            }
        }
        $sTitle = $bIsEdit ? _p('edit_collection') : _p('add_new_collection');
        $this->template()
            ->setTitle($sTitle)
            ->setBreadCrumb(_p("Apps"), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p("feed_status_background"),
                $this->url()->makeUrl('admincp.app', ['id' => 'P_StatusBg']))
            ->setBreadCrumb($sTitle)
            ->setHeader([
                'css/admin.css' => 'app_p-status-background',
                'jscript/admin.js' => 'app_p-status-background'
            ])
            ->setPhrase([
                'error',
                'notice',
                'collection_updated_successfully',
                'collection_added_successfully',
                'please_remove_all_error_files_first',
                'title_of_collection_is_required'
            ])
            ->assign([
                'sTitle' => $sTitle,
                'bIsEdit' => $bIsEdit,
                'sTimeStamp' => PHPFOX_TIME
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
        (($sPlugin = Phpfox_Plugin::get('pstatusbg.component_controller_admincp_add_collection_clean')) ? eval($sPlugin) : false);
    }
}

