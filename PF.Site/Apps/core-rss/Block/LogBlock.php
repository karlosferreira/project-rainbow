<?php

namespace Apps\Core_RSS\Block;

use Phpfox;
use Phpfox_Plugin;
use Phpfox_Component;


class LogBlock extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $aParam = $this->getParam('rss');

        $aLogs = Phpfox::getService('rss.log')->get($aParam);
        $sNames = '';
        $sCounts = '';
        $iMaxLogDisplay = 5;
        $iCnt = 0;
        $iOtherCount = 0;
        foreach ($aLogs as $aLog) {
            $iCnt++;

            if ($iCnt <= $iMaxLogDisplay) {
                $sNames .= $aLog['user_agent_chart'] . '|';
                $sCounts .= $aLog['total_agent_count'] . ',';
            } else {
                $iOtherCount += $aLog['total_agent_count'];
            }
        }
        if ($iOtherCount > 0) {
            $sNames .= _p('other') . '|';
            $sCounts .= $iOtherCount . ',';
        }

        $aUsers = array();
        if (isset($aParam['users'])) {
            list($iCnt, $aUsers) = Phpfox::getService('rss.log')->getUsers($aParam, $this->request()->get('page'), 20);

            Phpfox::getLib('pager')->set(array(
                'page' => $this->request()->get('page'),
                'size' => 20,
                'count' => $iCnt
            ));
        }

        $this->template()->assign(array(
                'sNames' => rtrim($sNames, '|'),
                'sCounts' => rtrim($sCounts, ','),
                'aLogs' => $aLogs,
                'aUsers' => $aUsers
            )
        );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('rss.component_block_log_clean')) ? eval($sPlugin) : false);
    }
}
