<?php

namespace Apps\Core_Messages\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;

defined('PHPFOX') or exit('NO DICE!');

class ManageConversationsController extends Phpfox_Component
{
    public function process()
    {
        $iPage = $this->request()->get('page');
        if (empty($iPage)) {
            $iPage = 1;
        }
        $iPageSize = 20;
        $aSearch = $this->request()->getArray('search');

        if (!empty($aSearch['keyword'])) {
            $this->search()->setCondition('AND (mtt.text LIKE "%' . $aSearch['keyword'] . '%")');
        } else {
            $aSearch['keyword'] = '';
        }
        $this->search()->setCondition('AND (mt.last_id_for_admin > 0)');
        list($iCnt, $aRows) = Phpfox::getService('mail')->getConversationForAdmin($this->search()->getConditions(), $iPage, $iPageSize);

        $this->search()->browse()->setPagingMode('pagination');
        Phpfox_Pager::instance()->set([
            'page' => $iPage,
            'size' => $iPageSize,
            'count' => (int)$iCnt,
            'paging_mode' => $this->search()->browse()->getPagingMode(),
            'params' => [
                'paging_show_icon' => true // use icon only
            ]
        ]);
        $this->template()->setTitle(_p('mail_manage_conversation'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('mail_app_title'), $this->url()->makeUrl('admincp.mail'))
            ->setBreadCrumb(_p('mail_manage_conversation'))
            ->assign([
                'aConversations' => $aRows,
                'aForms' => $aSearch
            ]);
    }
}