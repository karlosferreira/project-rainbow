<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Phpfox;

class ReportReasonApi extends AbstractApi
{
    public function __naming()
    {
        return [
            'report/reason' => [
                'maps' => [
                    'get' => 'reason',
                ],
            ],
        ];
    }

    public function reason()
    {

        $sType = $this->request()->get('sType');

        $aReasons = Phpfox::getService('report')->getOptions($sType);

        $aResult = [];
        foreach ($aReasons as $i => $aReason) {
            $aMatches = null;
            preg_match('/\{phrase var\=&#039;(.*)&#039;\}/is', $aReason['message'], $aMatches);
            $sMessage = isset($aMatches[1]) ? $this->getLocalization()->translate($aMatches[1]) : $aReasons[$i]['message'];

            $aResult[] = [
                'iReportId' => $aReason['report_id'],
                'sMessage'  => Phpfox::getService('mobile.helper.utf8')->decodeUtf8Compat($sMessage),
            ];
        }
        return $aResult;
    }
}