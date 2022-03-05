<?php

namespace Apps\Core_Messages\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

class ManageMessageController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::getUserParam('mail.can_read_private_messages', true);

        $iThreadId = $this->request()->get('id');

        if (!$iThreadId) {
            return Phpfox_Error::set(_p('mail_invalid_conversation'));
        }
        $iPage = $this->request()->get('page');
        if (empty($iPage)) {
            $iPage = 1;
        }
        $iSize = 10;
        $aSearch = $this->request()->get('search');
        if (!empty($aSearch['from_month']) && !empty($aSearch['to_month'])) {
            $aSearch['date_from'] = $this->request()->get('js_from__datepicker');
            $aSearch['date_to'] = $this->request()->get('js_to__datepicker');
        } else {
            $aSearch['date_to'] = date('n/j/Y');
            $aSearch['to_day'] = date('j');
            $aSearch['to_month'] = date('n');
            $aSearch['to_year'] = date('Y');

            $to = date_create($aSearch['date_to']);
            $from = date_sub($to, date_interval_create_from_date_string("1 month"));

            $aSearch['date_from'] = $from->format('n/j/Y');
            $aSearch['from_day'] = $from->format('j');
            $aSearch['from_month'] = $from->format('n');
            $aSearch['from_year'] = $from->format('Y');
        }

        if (!empty($aSearch['member_name'])) {
            $this->search()->setCondition('AND (u.full_name LIKE "%' . $aSearch['member_name'] . '%")');
        }
        if (!empty($aSearch['member_id'])) {
            if (!is_numeric($aSearch['member_id'])) {
                Phpfox_Error::set(_p('mail_member_id_validation'));
            } else {
                $this->search()->setCondition('AND (u.user_id =' . $aSearch['member_id'] . ')');
            }
        }
        if (!empty($aSearch['date_from']) || !empty($aSearch['date_to'])) {
            $fromTime = $toTime = 0;
            if (!empty($aSearch['date_from'])) {
                $fromTime = Phpfox::getLib('date')->mktime(0, 0, 0, $aSearch['from_month'], $aSearch['from_day'], $aSearch['from_year']);
                if ($fromTime <= 0) {
                    Phpfox_Error::set(_p('mail_time_from_validation'));
                }
            }
            if (!empty($aSearch['date_to'])) {
                $toTime = Phpfox::getLib('date')->mktime(23, 59, 59, $aSearch['to_month'], $aSearch['to_day'], $aSearch['to_year']);
                if ($toTime <= 0) {
                    Phpfox_Error::set(_p('mail_time_to_validation'));
                }
            }
            if (!empty($aSearch['date_from']) && !empty($aSearch['date_to']) && $fromTime > $toTime) {
                Phpfox_Error::set(_p('mail_time_from_must_be_smaller_than_time_to'));
            }
            $conversation = '';
            if (!empty($aSearch['date_from']) && empty($aSearch['date_to'])) {
                $conversation = 'AND mtt.time_stamp >= ' . $fromTime;
            } elseif (!empty($aSearch['date_to']) && empty($aSearch['date_from'])) {
                $conversation = 'AND mtt.time_stamp <= ' . $toTime;
            } elseif (!empty($aSearch['date_from']) && !empty($aSearch['date_to'])) {
                $conversation = 'AND (mtt.time_stamp BETWEEN ' . $fromTime . ' AND ' . $toTime . ')';
            }
            if ($conversation) {
                $this->search()->setCondition($conversation);
            }
        }
        if (!empty($aSearch['keyword'])) {
            $this->search()->setCondition('AND (mtt.text LIKE "%' . $aSearch['keyword'] . '%")');
        }
        if (!empty($aSearch['status'])) {
            $this->search()->setCondition('AND (mtt.is_show = ' . ($aSearch['status'] == "showing" ? 1 : 0) . ')');
        }

        $aMessages = null;
        if (Phpfox_Error::isPassed()) {
            $this->search()->setCondition('AND (mtt.is_deleted = 0 AND mtt.thread_id = ' . $iThreadId . ')');
            list($iCnt, $aMessages) = Phpfox::getService('mail')->getMessagesForAdmin($this->search()->getConditions(), $iPage, $iSize);

            $this->search()->browse()->setPagingMode('pagination');
            Phpfox_Pager::instance()->set([
                'page' => $iPage,
                'size' => $iSize,
                'count' => $iCnt,
                'paging_mode' => $this->search()->browse()->getPagingMode(),
                'params' => [
                    'paging_show_icon' => true // use icon only
                ]
            ]);
        }
        $this->template()->setTitle(_p('mail_manage_messages'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('mail_app_title'), $this->url()->makeUrl('admincp.mail'))
            ->setBreadCrumb(_p('conversation') . ': ' . phpFox::getService('mail')->getConversationName($iThreadId, null, ' & '))
            ->assign([
                'aMessages' => $aMessages,
                'iId' => $iThreadId,
                'aForms' => $aSearch,
                'sCalendarImagePath' => Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-messages/assets/images/calendar.gif'
            ]);
    }
}