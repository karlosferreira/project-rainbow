<?php

namespace Apps\Core_Comments\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Pager;
use Phpfox_Plugin;
use Phpfox_Search;

defined('PHPFOX') or exit('NO DICE!');

/**
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author           phpFox
 * @package          Phpfox_Component
 */
class SpamCommentsController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        //remove this feature
        Phpfox::getUserParam('comment.can_moderate_comments', true);

        $aVals = $this->request()->getArray('val');
        if ($aIds = $this->request()->getArray('ids')) {
            if (!empty($aVals['approve_selected'])) {
                foreach ($aIds as $iId) {
                    Phpfox::getService('comment.process')->moderate($iId, 'approve', true);
                }
                $this->url()->send('admincp.comment.spam-comments', _p('comment_s_approved_successfully'));
            } else if (!empty($aVals['deny_selected'])) {
                foreach ($aIds as $iId) {
                    Phpfox::getService('comment.process')->moderate($iId, 'deny', true);
                }
                $this->url()->send('admincp.comment.spam-comments', _p('comment_s_denied_successfully'));
            }
        }
        $iPage = $this->request()->getInt('page');

        $aPages = [20, 30, 40, 50];
        $aDisplays = [];
        foreach ($aPages as $iPageCnt) {
            $aDisplays[$iPageCnt] = _p('per_page', ['total' => $iPageCnt]);
        }

        $aFilters = [
            'search'  => [
                'type'   => 'input:text',
                'search' => "AND ls.name LIKE '%[VALUE]%'"
            ],
            'display' => [
                'type'    => 'select',
                'options' => $aDisplays,
                'default' => '20'
            ],
            'sort'    => [
                'type'    => 'select',
                'options' => [
                    'time_stamp' => _p('last_activity')
                ],
                'default' => 'time_stamp',
                'alias'   => 'cmt'
            ],
            'sort_by' => [
                'type'    => 'select',
                'options' => [
                    'DESC' => _p('descending'),
                    'ASC'  => _p('ascending')
                ],
                'default' => 'DESC'
            ]
        ];

        $oSearch = Phpfox_Search::instance()->set([
                'type'    => 'comments',
                'filters' => $aFilters,
                'search'  => 'search'
            ]
        );

        $oSearch->setCondition('AND cmt.view_id = 9');

        list($iCnt, $aComments) = Phpfox::getService('comment')->get('cmt.*', $oSearch->getConditions(), $oSearch->getSort(), $oSearch->getPage(), $oSearch->getDisplay(), null, true);

        Phpfox_Pager::instance()->set(['page' => $iPage, 'size' => $oSearch->getDisplay(), 'count' => $oSearch->getSearchTotal($iCnt)]);

        $this->template()
            ->setTitle(_p('spam_comments'))
            ->setBreadCrumb(_p('Apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p("Comments"), $this->url()->makeUrl('admincp.app', ['id' => 'Core_Comments']))
            ->setBreadCrumb(_p('admin_menu_spam_comments'), null)
            ->setHeader('cache', [
                    'comment.css' => 'style_css',
                    'pager.css'   => 'style_css',
                ]
            )
            ->assign([
                    'aComments'            => $aComments,
                    'bIsCommentAdminPanel' => true
                ]
            );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_controller_admincp_spam_clean')) ? eval($sPlugin) : false);
    }
}