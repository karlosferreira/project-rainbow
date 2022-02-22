<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class ManageController
 * @package Apps\Core_BetterAds\Controller
 */
class ManageController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        // delete ad
        $this->_processDeleteAd();
        $aAds = $this->_getAds();
        $this->_setPaging();

        if (user('better_can_create_ad_campaigns', false)) {
            sectionMenu(_p('better_ads_create_an_ad'), url('ad/add'));
        }

        $this->template()->setTitle(_p('manage_ads'))
            ->setBreadCrumb(_p('manage_ads'), $this->url()->makeUrl('ad.manage'))
            ->assign([
                'aAllAds' => $aAds,
                'bNewPurchase' => $this->request()->get('payment'),
                'aPlacements' => Phpfox::getService('ad.get')->getPlans()
            ]);
        Phpfox::getService('ad.get')->getSectionMenu();

        return 'controller';
    }

    private function _processDeleteAd()
    {
        $iDeleteAd = $this->request()->get('delete');
        if ($iDeleteAd && Phpfox::getService('ad.process')->delete($iDeleteAd, true)) {
            $this->url()->send('ad.manage', null, _p('better_ads_ad_successfully_deleted'));
        }
    }

    private function _getAds()
    {
        // get ads of current user
        $this->search()->setCondition('ads.user_id = ' . Phpfox::getUserId());
        $sSort = $this->request()->get('sort');
        $aSearch = $this->request()->getArray('search');

        $this->template()->assign(['sCurrentSort' => empty($sSort) ? '' : $sSort]);
        if ($sSort) {
            $this->search()->setSort($sSort);
        }

        if (!empty($aSearch)) {
            if (!empty($aSearch['from_month']) && !empty($aSearch['from_day']) && !empty($aSearch['from_year'])) {
                $iFromTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aSearch['from_day'],
                    $aSearch['from_month'], $aSearch['from_year']);
                $this->search()->setCondition(" AND ads.start_date >= $iFromTimestamp");
            }
            if (!empty($aSearch['to_month']) && !empty($aSearch['to_day']) && !empty($aSearch['to_year'])) {
                $iToTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aSearch['to_day'],
                    $aSearch['to_month'], $aSearch['to_year'], 'end');
                $this->search()->setCondition(" AND ads.start_date <= $iToTimestamp");
            }
            if (!empty($aSearch['location'])) {
                $this->search()->setCondition(" AND ads.location = {$aSearch['location']}");
            }
            if (!empty($aSearch['is_custom'])) {
                if (is_numeric($aSearch['is_custom'])) {
                    $this->search()->setCondition(" AND ads.is_custom = {$aSearch['is_custom']}");
                } else {
                    $this->search()->setCondition(' AND ads.is_custom = 3 AND ' . Phpfox::getService('ad.get')->getApprovedCond($aSearch['is_custom'], 'ads'));
                }
            }

            $this->template()->assign('aForms', $aSearch);
        } else {
            $aForms = [
                'from_day' => Phpfox::getTime('j', PHPFOX_TIME - 30*24*3600),
                'from_month' => Phpfox::getTime('n', PHPFOX_TIME - 30*24*3600),
                'from_year' => Phpfox::getTime('Y', PHPFOX_TIME - 30*24*3600),
                'to_day' => Phpfox::getTime('j', PHPFOX_TIME),
                'to_month' => Phpfox::getTime('n', PHPFOX_TIME),
                'to_year' => Phpfox::getTime('Y', PHPFOX_TIME),
            ];
            $this->template()->assign('aForms', $aForms);
            $iFromTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aForms['from_day'], $aForms['from_month'], $aForms['from_year']);
            $this->search()->setCondition(" AND ads.start_date >= $iFromTimestamp");
            $iToTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aForms['to_day'], $aForms['to_month'], $aForms['to_year'], 'end');
            $this->search()->setCondition(" AND ads.start_date <= $iToTimestamp");
        }

        $this->search()->set([
            'type' => 'ads',
            'field' => 'ads.name',
            'ignore_blocked' => false,
            'filters' => [
                'sort' => [
                    'options' => [
                        'ads.start_date desc' => '',
                        'ads.start_date asc' => '',
                        'ads.count_view desc' => '',
                        'ads.count_view asc' => '',
                        'ads.count_click desc' => '',
                        'ads.count_click asc' => '',
                    ],
                    'default' => 'ads.ads_id asc'
                ],
                'sort_by' => [
                    'default' => ''
                ]
            ],
            'search_tool' => [
                'table_alias' => 'ads',
                'search' => array(
                    'action' => '',
                    'default_value' => _p('search_ads_dot'),
                    'name' => 'search',
                    'field' => ['ads.name'],
                    'hidden' => ''
                ),
                'no_filters' => [_p('when')]
            ]
        ]);

        $this->search()->browse()->params([
            'module_id' => 'ad',
            'alias' => 'ads',
            'field' => 'ads_id',
            'table' => Phpfox::getT('better_ads'),
        ])->setPagingMode('pagination')->execute();

        return $this->search()->browse()->getRows();
    }

    private function _setPaging()
    {
        $aParamsPager = array(
            'page' => $this->search()->getPage(),
            'size' => $this->search()->getDisplay(),
            'count' => $this->search()->browse()->getCount(),
            'paging_mode' => $this->search()->browse()->getPagingMode()
        );

        Phpfox::getLib('pager')->set($aParamsPager);
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_manage_clean')) ? eval($sPlugin) : false);
    }
}
