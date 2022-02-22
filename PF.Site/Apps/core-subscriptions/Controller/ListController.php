<?php

namespace Apps\Core_Subscriptions\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Pager;

defined('PHPFOX') or exit('NO DICE!');

class ListController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        $aPurchases = Phpfox::getService('subscribe.purchase')->getPackagesWithUser(Phpfox::getUserId());
        foreach ($aPurchases as $iKey => $aPurchase) {
            $aPurchases[$iKey]['title_parse'] = Phpfox::isPhrase($aPurchase['title']) ? _p($aPurchase['title']) : $aPurchase['title'];
            $aPurchases[$iKey]['s_title'] = Phpfox::isPhrase($aPurchase['s_title']) ? _p($aPurchase['s_title']) : $aPurchase['s_title'];
            $aPurchases[$iKey]['f_title'] = Phpfox::isPhrase($aPurchase['f_title']) ? _p($aPurchase['f_title']) : $aPurchase['f_title'];
        }

        $iPage = $this->request()->get('page', 1);
        $iSize = 10;
        $aStatus = Phpfox::getService('subscribe')->getStatusList();
        $aVals = $this->request()->getArray('val');

        list($aFilters, $iCnt) = Phpfox::getService('subscribe.purchase')->getMySubscriptions(Phpfox::getUserId(), $aVals, $iPage, $iSize, true);

        $this->search()->browse()->setPagingMode('pagination');
        Phpfox_Pager::instance()->set([
            'page' => $iPage,
            'size' => $iSize,
            'count' => $iCnt,
            'paging_mode' => $this->search()->browse()->getPagingMode()
        ]);

        $this->template()->assign([
            'aFilters' => $aFilters,
            'aSearchData' => $aVals
        ]);


        if (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_list__1'))) {
            eval($sPlugin);
            if (isset($mReturnPlugin)) {
                return $mReturnPlugin;
            }
        }

        $this->template()->setTitle(_p('subscriptions'))
            ->setBreadCrumb(_p('membership_packages'), $this->url()->makeUrl('subscribe'))
            ->setBreadCrumb(_p('subscriptions'), $this->url()->makeUrl('subscribe.list'), true)
            ->assign([
                    'aPurchases' => $aPurchases,
                    'aStatuses' => $aStatus,
                    'sDefaultPhoto' => Phpfox::getParam('subscribe.default_photo_package')
                ]
            );

        $this->template()->buildSectionMenu('subscribe', Phpfox::getService('subscribe')->getSectionMenu());

        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_list_clean')) ? eval($sPlugin) : false);
    }
}