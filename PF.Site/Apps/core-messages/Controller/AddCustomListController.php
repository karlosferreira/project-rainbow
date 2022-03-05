<?php

namespace Apps\Core_Messages\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Module;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

class AddCustomListController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        $bIsEdit = false;
        $iId = !empty($this->request()->get('id')) ? $this->request()->get('id') : $this->getParam('id');
        $this->template()->assign([
            'iCustomListMaximum' => setting('mail.custom_list_maximum') ? setting('mail.custom_list_maximum') : 0,
            'iCustomListMemberMaximum' => setting('mail.custom_list_member_maximum') ? setting('mail.custom_list_member_maximum') : 0,
            'iCurrentCustomListOfUser' => Phpfox::getService('mail.customlist')->getUserFolderCount(Phpfox::getUserId())
        ]);
        if (($aCustom = Phpfox::getService('mail.customlist')->getCustomList($iId))) {
            $bIsEdit = true;
            $this->template()->assign([
                'bIsEdit' => $bIsEdit,
                'iId' => $aCustom['folder_id'],
                'aCustom' => $aCustom,
            ]);
        } else {
            $iLimit = setting('mail.custom_list_maximum');
            $iFoldersOfUser = Phpfox::getService('mail.customlist')->getUserFolderCount(Phpfox::getUserId());
            if ($iFoldersOfUser >= $iLimit) {
                if (PHPFOX_IS_AJAX) {
                    Phpfox_Error::set(_p('mail_cannot_create_more_custom_list_because_of_limitation', ['number' => $iLimit]));
                    $this->template()->assign([
                        'error' => _p('mail_cannot_create_more_custom_list_because_of_limitation', ['number' => $iLimit]),
                    ]);
                } else {
                    $this->url()->send('mail.customlist', [], _p('mail_cannot_create_more_custom_list_because_of_limitation', ['number' => $iLimit]), null, 'warning');
                }
            }

        }
        $aVals = $this->request()->getArray('val') ? $this->request()->getArray('val') : $this->getParam('val');

        if (!empty($aVals)) {
            $aVals['name'] = db()->escape(strip_tags($aVals['name']));
            if (empty($aVals['name']) && !$bIsEdit) {
                Phpfox_Error::set(_p('mail_invalid_name'));
            }

            if (empty($aVals['invite'])) {
                Phpfox_Error::set(_p('mail_friend_list_cannot_be_null'));
            }

            if (!empty($aVals['invite']) && (count($aVals['invite']) > setting('mail.custom_list_member_maximum'))) {
                Phpfox_Error::set(_p('mail_limitation_custom_list_members', ['number' => setting('mail.custom_list_member_maximum')]));
            }
            if (!Phpfox_Error::isPassed()) {
                $this->template()->assign([
                    'aForms' => $aVals
                ]);
                return false;
            }

            if ($bIsEdit) {
                $aVals['id'] = $iId;
                if (Phpfox::getService('mail.customlist.process')->add($aVals, true)) {
                    $message =  _p('mail_update_custom_list_successfully');
                }
            } else {
                $iId = Phpfox::getService('mail.customlist.process')->add($aVals);
                $message = _p('mail_add_custom_list_successfully');
            }
            $this->url()->send('mail', ['customlist_id' => $iId], $message);
        }
    }
}