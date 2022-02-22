<?php

namespace Apps\Core_BetterAds\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;

/**
 * Class Report
 * @package Apps\Core_BetterAds\Service
 */
class Report extends \Phpfox_Service
{
    private function timeToString($iTimeStamp, $bHasString = true)
    {
        if ($bHasString && $iTimeStamp == strtotime('00:00:00')) {
            return _p('today');
        }

        if ($bHasString && $iTimeStamp == strtotime('-1 day', strtotime('00:00:00'))) {
            return _p('yesterday');
        }

        return date('M-d-Y', $iTimeStamp);
    }

    /**
     * Get List user/ip view this ads
     *
     * @param int $iAdsId
     * @param int $iStartTime
     * @param int $iEndTime
     *
     * @param int $iType
     * @return array
     */
    private function getReport($iAdsId, $iStartTime, $iEndTime, $iType = 1)
    {
        $aReport = db()->select('SUM(count) AS total_view, SUM(click) AS total_click')
            ->from(':better_ads_view')
            ->where('ads_id=' . (int)$iAdsId . ' AND timestamp>=' . (int)$iStartTime . ' AND timestamp<=' . (int)$iEndTime)
            ->executeRow();
        if (!isset($aReport['total_view'])) {
            $aReport['total_view'] = 0;
        }
        if (!isset($aReport['total_click'])) {
            $aReport['total_click'] = 0;
        }
        $aReport['start_time'] = $iStartTime;
        $aReport['end_time'] = $iEndTime;

        if ($iType == 1) {
            //Mean day
            $aReport['day_string'] = $this->timeToString($aReport['start_time']);
        } else {
            $aReport['day_string'] = $this->timeToString($aReport['start_time'], false) . ' - ' .
                $this->timeToString($aReport['end_time'], false);
        }

        return $aReport;
    }

    /**
     * @param int $iAdsId
     * @param $iStartTimestamp
     * @param $iEndTimestamp
     * @return array
     */
    public function getReportByDay($iAdsId, $iStartTimestamp, $iEndTimestamp)
    {
        $iOneDay = 86400;
        $aReports = [];
        $iTime = $iStartTimestamp;

        while ($iTime < $iEndTimestamp) {
            $aReports[] = $this->getReport($iAdsId, $iTime, $iTime + $iOneDay);
            $iTime += $iOneDay;
        }

        return $aReports;
    }

    /**
     * @param int $iAdsId
     * @param $iStartTimestamp
     * @param $iEndTimestamp
     * @return array
     */
    public function getReportByWeek($iAdsId, $iStartTimestamp, $iEndTimestamp)
    {
        $aReports = [];
        $iTime = $iStartTimestamp;

        while ($iTime < $iEndTimestamp) {
            $iEndTime = strtotime(date('Y-m-d',
                    strtotime('Sunday', $iTime)) . ' 23:59:59'); // get sunday of current week ($iTime)
            if ($iEndTime > $iEndTimestamp) {
                $iEndTime = $iEndTimestamp;
                $bBreak = true;
            }
            $aReports[] = $this->getReport($iAdsId, $iTime, $iEndTime, 2);
            $iTime = $iEndTime + 1;

            if (!empty($bBreak)) {
                break;
            }
        }

        return $aReports;
    }

    /**
     * @param int $iAdsId
     * @param $iStartTimestamp
     * @param $iEndTimestamp
     * @return array
     */
    public function getReportByMonth($iAdsId, $iStartTimestamp, $iEndTimestamp)
    {
        $aReports = [];
        $iStartTime = $iStartTimestamp;
        do {
            $dayNumberOfMonth = cal_days_in_month(CAL_GREGORIAN, date('m', $iStartTime), date('Y', $iStartTime));
            $iEndTime = strtotime(date('Y-m', $iStartTime) . "-{$dayNumberOfMonth} 23:59:59");
            if ($iEndTime > $iEndTimestamp) {
                $iEndTime = $iEndTimestamp;
            }

            $aReports[] = $this->getReport($iAdsId, $iStartTime, $iEndTime, 3);
            $iStartTime = $iEndTime + 1;
        } while ($iStartTime <= $iEndTimestamp);

        return $aReports;
    }

    /**
     * Get daily reports
     * @param $iAdId
     * @param int $iLimit
     * @param int $iPage
     * @return array
     */
    public function getDailyReports($iAdId, $iLimit = 5, $iPage = 1)
    {
        $aDailyReports = [];
        foreach (range(($iPage - 1) * $iLimit, $iPage * $iLimit - 1) as $day) {
            $iBeginDay = strtotime('00:00:00');
            $iEndDay = strtotime('23:59:59');
            $iPeriod = (int)$day;

            if ($iPeriod == 0) {
                $iStartTime = $iBeginDay;
                $iEndTime = PHPFOX_TIME;
            } elseif ($iPeriod == 1) {
                $iStartTime = strtotime('-1 day', $iBeginDay);
                $iEndTime = strtotime('-1 day', $iEndDay);
            } else {
                $iStartTime = strtotime('-' . $iPeriod . ' days', $iBeginDay);
                $iEndTime = strtotime('-' . $iPeriod . ' days', $iEndDay);
            }

            $aDailyReports[] = $this->getReport($iAdId, $iStartTime, $iEndTime);
        }

        return $aDailyReports;
    }

    public function getNumberOfDayFromStartDay($iAdId)
    {
        $iStartTimestamp = db()->select('start_date')->from(':better_ads')->where(['ads_id' => $iAdId])->executeField();

        if (!$iStartTimestamp || $iStartTimestamp > time()) {
            return 0;
        }

        $start = new \DateTime(date('Y-m-d H:i:s', $iStartTimestamp));
        $today = new \DateTime();
        $interval = $start->diff($today);

        return intval($interval->days) + 1;
    }

    public function getTodayTimestamp()
    {
        return strtotime('00:00:00');
    }

    public function getAds($iAdsId)
    {
        $aAds = db()->select('*')
            ->from(':better_ads')
            ->where('ads_id=' . (int)$iAdsId)
            ->executeRow();

        $aCountry = db()->select('c.name')
            ->from(':better_ads_country', 'ac')
            ->singleData('name')
            ->join(':country', 'c', 'c.country_iso=ac.country_id')
            ->where('ac.ads_id=' . (int)$iAdsId)
            ->executeRows();
        $aAds['country'] = $aCountry;

        if (isset($aAds['html_code']) && !empty($aAds['html_code'])) {
            $aHtmlInfo = json_decode($aAds['html_code'], true);
            $aAds = array_merge($aHtmlInfo, $aAds);
            $aAds['url_link'] = $aAds['trimmed_url'];
        }

        $aAds['status'] = Phpfox::getService('ad.get')->getStatusPhrase($aAds['is_custom'], $aAds['start_date'], $aAds['end_date']);

        if ($aAds['start_date']) {
            $aAds['start'] = Phpfox::getTime(BETTERADS_DATETIME_FORMAT, $aAds['start_date']);
        }

        if ($aAds['end_date']) {
            $aAds['end'] = Phpfox::getTime(BETTERADS_DATETIME_FORMAT, $aAds['end_date']);
        }

        $aAds['postal_code'] = json_decode($aAds['postal_code'], true);
        $aAds['city_location'] = json_decode($aAds['city_location'], true);

        $aAllGenders = Phpfox::getService('core')->getGenders(false);
        if (!isset($aAds['gender'])) {
            $aAds['gender'] = $aAllGenders;
        } else {
            foreach (explode(',', $aAds['gender']) as $genderId) {
                if (!is_array($aAds['gender'])) {
                    $aAds['gender'] = [];
                }

                $aAds['gender'][] = (int)$genderId == 0 ? _p('any') : ((int)$genderId == 127 ? _p('better_ads_custom_genders') : $aAllGenders[$genderId]);
            }
        }

        $aLanguages = Phpfox::getService('language')->getAll();
        $aLanguages = array_combine(array_column($aLanguages,'language_id'),array_column($aLanguages,'title'));

        if(empty($aAds['languages']))
        {
            $aAds['languages'] = $aLanguages;
        }
        else
        {
            foreach (explode(',', $aAds['languages']) as $languageId) {
                if (!is_array($aAds['languages'])) {
                    $aAds['languages'] = [];
                }

                $aAds['languages'][] = $aLanguages[$languageId];
            }
        }
        sort($aAds['languages']);

        return $aAds;
    }

    /**
     * @param int $iAdsId
     * @param bool $bClick
     */
    public function updateAdsCount($iAdsId, $bClick = false)
    {
        if ($bClick) {
            $sSelect = 'click';
        } else {
            $sSelect = 'count';
        }
        $aRow = db()->select('*')
            ->from(':better_ads_view')
            ->where('ads_id= ' . (int)$iAdsId . ' AND timestamp=' . (int)$this->getTodayTimestamp())
            ->executeRow();

        if (isset($aRow[$sSelect])) {
            db()->update(':better_ads_view', [$sSelect => $aRow[$sSelect] + 1],  ['view_id' => $aRow['view_id']]);
        } else {
            db()->insert(':better_ads_view', [
                    $sSelect => 1,
                    'ads_id' => (int)$iAdsId,
                    'timestamp' => (int)$this->getTodayTimestamp()
                ]);
        }
    }

    /**
     * @param int $iAdsId
     * @param $aVals
     * @return array
     */
    public function getReportForView($iAdsId, $aVals)
    {
        $iType = $aVals['type'];
        if (!in_array($iType, [1, 2, 3])) {
            $iType = 1;
        }

        $iStartTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aVals['from_day'], $aVals['from_month'], $aVals['from_year']);
        $iEndTimestamp = Phpfox::getService('ad.process')->convertToTimestamp($aVals['to_day'], $aVals['to_month'], $aVals['to_year'], 'end');

        switch ($iType) {
            case 1:
                $aReports = $this->getReportByDay($iAdsId, $iStartTimestamp, $iEndTimestamp);
                break;
            case 2:
                $aReports = $this->getReportByWeek($iAdsId, $iStartTimestamp, $iEndTimestamp);
                break;
            case 3:
                $aReports = $this->getReportByMonth($iAdsId, $iStartTimestamp, $iEndTimestamp);
                break;
            default:
                $aReports = [];
        }

        return $aReports;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('ad.service_report__call')) {
            return eval($sPlugin);
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);

        return null;
    }
}
