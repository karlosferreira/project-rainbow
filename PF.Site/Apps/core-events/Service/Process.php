<?php

namespace Apps\Core_Events\Service;

use Exception;
use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Plugin;
use Phpfox_Queue;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');


class Process extends Phpfox_Service
{
    /**
     * @var array
     */
    private $_aInvited = [];

    /**
     * @var array
     */
    private $_aCategories = [];

    /**
     * @var bool
     */
    private $_bIsEndingInThePast = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('event');
    }

    /**
     * @param array  $aVals
     * @param string $sModule
     * @param int    $iItem
     *
     * @return int
     */
    public function add($aVals, $sModule = 'event', $iItem = 0)
    {
        if (!$this->_verify($aVals)) {
            return false;
        }

        if (!isset($aVals['privacy'])) {
            $aVals['privacy'] = 0;
        }

        $oParseInput = Phpfox::getLib('parse.input');
        if (!Phpfox::getService('ban')->checkAutomaticBan($aVals)) {
            return false;
        }
        $bHasAttachments = (!empty($aVals['attachment']));

        $iStartTime = Phpfox::getLib('date')->mktime($aVals['start_hour'], $aVals['start_minute'], 0,
            $aVals['start_month'], $aVals['start_day'], $aVals['start_year']);
        if ($this->_bIsEndingInThePast === true) {
            $aVals['end_hour'] = ($aVals['start_hour'] + 1);
            $aVals['end_minute'] = $aVals['start_minute'];
            $aVals['end_day'] = $aVals['start_day'];
            $aVals['end_year'] = $aVals['start_year'];
        }

        $iEndTime = Phpfox::getLib('date')->mktime($aVals['end_hour'], $aVals['end_minute'], 0, $aVals['end_month'],
            $aVals['end_day'], $aVals['end_year']);

        if ($iStartTime > $iEndTime) {
            $iEndTime = $iStartTime;
        }

        $isRepeatEvent = isset($aVals['isrepeat']) && $aVals['isrepeat'] != '-1';
        $timerepeat = 0;
        $after_number_event = 0;

        if ($isRepeatEvent) {
            switch ($aVals['repeat_section_end_repeat']) {
                case 'after_number_event':
                    $after_number_event = (int)$aVals['repeat_section_after_number_event'];
                    break;
                case 'repeat_until':
                    $timerepeat = Phpfox::getLib('date')->mktime(23, 59, 59, $aVals['repeat_section_repeatuntil_month'], $aVals['repeat_section_repeatuntil_day'], $aVals['repeat_section_repeatuntil_year']);
                    break;
            }
        }

        $aVals['location'] = trim($aVals['location']);

        $aSql = [
            'view_id'          => (int)Phpfox::getUserParam('event.event_must_be_approved'),
            'privacy'          => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
            'privacy_comment'  => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
            'module_id'        => $sModule,
            'item_id'          => $iItem,
            'user_id'          => Phpfox::getUserId(),
            'title'            => $oParseInput->clean($aVals['title'], 255),
            'location'         => $aVals['location'] != '' ? $aVals['location'] : '',
            'time_stamp'       => PHPFOX_TIME,
            'start_time'       => Phpfox::getLib('date')->convertToGmt($iStartTime),
            'end_time'         => Phpfox::getLib('date')->convertToGmt($iEndTime),
            'start_gmt_offset' => Phpfox::getLib('date')->getGmtOffset($iStartTime),
            'end_gmt_offset'   => Phpfox::getLib('date')->getGmtOffset($iEndTime),
            'address'          => (empty($aVals['address']) ? null : Phpfox::getLib('parse.input')->clean($aVals['address'])),
            'is_online'        => isset($aVals['is_online']) ? 1 : 0,
            'online_link'      => isset($aVals['is_online']) && isset($aVals['online_link']) && $aVals['online_link'] != '' ? $aVals['online_link'] : '',
            'is_repeat'        => $aVals['isrepeat'],
            'after_number_event'   => $after_number_event,
            'time_repeat'      => $timerepeat,
        ];

        if (!empty($aVals['location_lat'])) {
            $aSql['location_lat'] = $aVals['location_lat'];
        }
        if (!empty($aVals['location_lng'])) {
            $aSql['location_lng'] = $aVals['location_lng'];
        }
        if (!empty($aVals['country_iso'])) {
            $aSql['country_iso'] = $aVals['country_iso'];
        }
        if (!empty($aVals['country_iso']) && !empty($aVals['country_child_id'])) {
            $aSql['country_child_id'] = Phpfox::getService('core.country')->getValidChildId($aVals['country_iso'], (int)$aVals['country_child_id']);
        } else {
            $aSql['country_child_id'] = 0;
        }

        if (!empty($aVals['temp_file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            if (!empty($aFile)) {
                if (!Phpfox::getService('user.space')->isAllowedToUpload(Phpfox::getUserId(), $aFile['size'])) {
                    Phpfox::getService('core.temp-file')->delete($aVals['temp_file'], true);
                    return false;
                }
                $aSql['image_path'] = $aFile['path'];
                $aSql['server_id'] = $aFile['server_id'];
                Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'event', $aFile['size']);
                Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            }
        }
        if ($sPlugin = Phpfox_Plugin::get('event.service_process_add__start')) {
            return eval($sPlugin);
        }

        if (!Phpfox_Error::isPassed()) {
            return false;
        }

        $iId = $this->database()->insert($this->_sTable, $aSql);

        if (!$iId) {
            return false;
        }
        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }

        $this->database()->insert(Phpfox::getT('event_text'), [
                'event_id'           => $iId,
                'description'        => (empty($aVals['description']) ? null : $oParseInput->clean($aVals['description'])),
                'description_parsed' => (empty($aVals['description']) ? null : $oParseInput->prepare($aVals['description']))
            ]
        );

        foreach ($this->_aCategories as $iCategoryId) {
            $this->database()->insert(Phpfox::getT('event_category_data'),
                ['event_id' => $iId, 'category_id' => $iCategoryId]);
        }

        $this->cache()->removeGroup('event_category');

        $bAddFeed = (Phpfox::getUserParam('event.event_must_be_approved') ? false : true);

        if ($bAddFeed === true) {
            if ($sModule == 'event' && Phpfox::isModule('feed') && Phpfox::getParam('event.event_allow_create_feed_when_add_new_item', 1)) {
                //Support for invitee privacy
                if ($aVals['privacy'] == '5') {
                    $aVals['privacy'] = '0';
                }

                Phpfox::getService('feed.process')->add('event', $iId, $aVals['privacy'],
                    (isset($aVals['privacy_comment']) ? (int)$aVals['privacy_comment'] : 0));
            } else {
                if (Phpfox::isModule('feed') && Phpfox::getParam('event.event_allow_create_feed_when_add_new_item', 1)) {
                    Phpfox::getService('feed.process')
                        ->callback(Phpfox::callback($sModule . '.getFeedDetails', $iItem))
                        ->add('event', $iId, $aVals['privacy'],
                            (isset($aVals['privacy_comment']) ? (int)$aVals['privacy_comment'] : 0), $iItem);

                }

                //support add notification for parent module
                if (Phpfox::isModule('notification') && $sModule != 'event' && Phpfox::isModule($sModule) && Phpfox::hasCallback($sModule,
                        'addItemNotification')
                ) {
                    Phpfox::callback($sModule . '.addItemNotification', [
                        'page_id'      => $iItem,
                        'item_perm'    => 'event.who_can_view_browse_events',
                        'item_type'    => 'event',
                        'item_id'      => $iId,
                        'owner_id'     => Phpfox::getUserId(),
                        'items_phrase' => 'events__l'
                    ]);
                }
            }

            Phpfox::getService('user.activity')->update(Phpfox::getUserId(), 'event');
        }

        $this->addRsvp($iId, 1, Phpfox::getUserId());

        if (Phpfox::isModule('privacy') && $aVals['privacy'] == '4') {
            Phpfox::getService('privacy.process')->add('event', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
        }

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->add('event', $iId, Phpfox::getUserId(), $aVals['description'], true);
        }

        // Plugin call
        if ($sPlugin = Phpfox_Plugin::get('event.service_process_add__end')) {
            eval($sPlugin);
        }

        if ($isRepeatEvent) {
            $this->database()->update(Phpfox::getT('event'), ['org_event_id' => (int)$iId], 'event_id = ' . (int)$iId);
            $this->generateInstanceForRepeatEvent($iId, $aVals, $bHasAttachments);
        }

        return $iId;
    }

    public function generateInstanceForRepeatEvent($iEventId, $aVals, $bHasAttachments)
    {
        $aEvent = Phpfox::getService('event')->getAllDataEventById($iEventId);
        $aEvent['description_parsed'] = db()->select('description_parsed')
            ->from(':event_text')
            ->where(['event_id' => $iEventId])
            ->executeField();

        if (isset($aEvent['event_id']) == false) {
            return false;
        }
        if ((int)$aEvent['is_repeat'] < 0) {
            return false;
        }

        $aEventFields = $aEvent;
        unset($aEventFields['event_id']);
        unset($aEventFields['description']);
        unset($aEventFields['description_parsed']);
        unset($aEventFields['categories']);
        unset($aEventFields['is_featured']);
        unset($aEventFields['is_sponsor']);

        $iStartTime = (int)$aEvent['start_time'];
        $month = (int)Phpfox::getTime('n', $iStartTime, false);
        $day = (int)Phpfox::getTime('j', $iStartTime, false);
        $year = (int)Phpfox::getTime('Y', $iStartTime, false);
        $start_hour = (int)Phpfox::getTime('H', $iStartTime, false);
        $start_minute = (int)Phpfox::getTime('i', $iStartTime, false);
        $start_second = (int)Phpfox::getTime('s', $iStartTime, false);

        $iEndTime = (int)$aEvent['end_time'];
        $iDuration = $iEndTime - $iStartTime;

        $iTimeRepeat = 0;
        $len = 1;
        if ((int)$aEvent['after_number_event'] > 0) {
            $len = (int)$aEvent['after_number_event'];
        } else if (isset($aEvent['time_repeat']) && (int)$aEvent['time_repeat'] > 0) {
            $iTimeRepeat = (int)$aEvent['time_repeat'];
            if (Phpfox::getUserParam('event.max_events_created')) {
                $len = (int)Phpfox::getUserParam('event.max_events_created') - (int)Phpfox::getService('event')->getMyTotal();
            } else {
                $len = (int)Phpfox::getParam('event.event_max_instance_repeat_event');
            }
        }

        for($idx = 0; $idx < $len; $idx++){
            if ($aEvent['is_repeat'] == 0) { //  daily
                $iStartTime = $iStartTime + (1 * 24 * 60 * 60);
            } else if ($aEvent['is_repeat'] == 1) {  //  weekly
                $iStartTime = $iStartTime + (7 * 24 * 60 * 60);
            } else if ($aEvent['is_repeat'] == 2) { // monthly
                $next_start_time_obj = Phpfox::getService('event')->getSameDayInNextMonth($day, $month, $year);
                $day = $next_start_time_obj['theory_date']['day'];
                $month = $next_start_time_obj['theory_date']['month'];
                $year = $next_start_time_obj['theory_date']['year'];
                $iStartTime = Phpfox::getLib('date')->mktime($start_hour
                    , $start_minute
                    , $start_second
                    , $next_start_time_obj['true_date']['month']
                    , $next_start_time_obj['true_date']['day']
                    , $next_start_time_obj['true_date']['year']
                );
            }

            if ($iTimeRepeat && $iStartTime > $iTimeRepeat) {
                break;
            }

            $iEndTime = $iStartTime + $iDuration;
            $aEventFields['start_time'] = $iStartTime;
            $aEventFields['end_time'] = $iEndTime;

            $iId = $this->database()->insert(Phpfox::getT('event'), $aEventFields);
            $copied_image_path = $this->copyRecurringImage($iId,
                [
                    'recurring_image' => $aEvent['image_path'],
                    'server_id' => $aEvent['server_id'],
                ]);

            if ($copied_image_path) {
                $this->database()->update(
                    $this->_sTable,
                    [
                        'image_path' => $copied_image_path,
                        'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')
                    ],
                    'event_id = ' . $iId);
            }

            $this->database()->insert(Phpfox::getT('event_text'), [
                    'event_id' => $iId,
                    'description' => $aEvent['description'],
                    'description_parsed' => $aEvent['description_parsed'],
                ]
            );

            $aCategories = explode(',', $aEvent['categories']);
            foreach ($aCategories as $iCategoryId) {
                $this->database()->insert(Phpfox::getT('event_category_data'), array('event_id' => $iId, 'category_id' => $iCategoryId));
            }

            // If we uploaded any attachments make sure we update the 'item_id'
            if ($bHasAttachments) {
                Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
            }

            $this->addRsvp($iId, 1, Phpfox::getUserId());

            if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
                Phpfox::getService('tag.process')->add('event', $iId, Phpfox::getUserId(), $aVals['description'], true);
            }
        }

        return true;
    }

    public function getFileExt($sFileName)
    {
        $aExts = preg_split("/[\/\\.]/", $sFileName);
        $iCnt = count($aExts) - 1;

        return strtolower($aExts[$iCnt]);
    }


    private function _buildDir($sDestination)
    {
        if (!PHPFOX_SAFE_MODE && !defined('PHPFOX_IS_HOSTED_SCRIPT')) {
            $aParts = explode('/', 'Y/m');
            foreach ($aParts as $sPart) {
                $sDate = date($sPart) . PHPFOX_DS;
                $sDestination .= $sDate;

                if (!file_exists($sDestination)) {
                    @mkdir($sDestination, 0777, true);
                    @chmod($sDestination, 0777);
                }
            }

            // Make sure the directory was actually created, if not we use the default dir we know is working
            if (is_dir($sDestination)) {
                return $sDestination;
            } else {
                return false;
            }
        }
    }

    public function copyRecurringImage($iId, $aVals)
    {
        $sImageDb = '';
        if (!empty($aVals['recurring_image'])) {
            $sFilename = $aVals['recurring_image'];
            $aSizes = array('', 50, '50_square', 200, '200_square', 400, '400_square', 1024, '1024_square');
            $sExt = $this->getFileExt($sFilename);
            $sFileDir = $this->_buildDir(Phpfox::getParam('event.dir_image'));

            $sFileNameCopy = $sFileDir . md5($iId . PHPFOX_TIME . uniqid()) . '%s.' . $sExt;

            foreach ($aSizes as $iSize) {
                $sImage = Phpfox::getParam('event.dir_image') . sprintf($sFilename, (empty($iSize) ? '' : '_') . $iSize);
                $sImageCopy = sprintf($sFileNameCopy, (empty($iSize) ? '' : '_') . $iSize);
                if (file_exists($sImage)) {
                    if ($sFileDir !== false) {
                        if (Phpfox::getLib('file')->copy($sImage, $sImageCopy)) {
                            Phpfox::getLib('cdn')->put($sImageCopy);
                        };
                    }
                } else {
                    if (!empty($aVals['server_id'])) {
                        $sActualFile = Phpfox::getLib('image.helper')->display(array(
                                'server_id' => $aVals['server_id'],
                                'path' => 'event.url_image',
                                'file' => $sFilename,
                                'suffix' => $iSize,
                                'return_url' => true
                            )
                        );

                        if (filter_var($sActualFile, FILTER_VALIDATE_URL) !== false) {
                            file_put_contents($sImageCopy, fox_get_contents($sActualFile));
                        } else {
                            copy($sActualFile, $sImageCopy);
                        }
                        //Delete file in local server
                        register_shutdown_function(function () use ($sImageCopy) {
                            @unlink($sImageCopy);
                        });

                        if (file_exists($sImageCopy)) {
                            Phpfox::getLib('cdn')->put($sImageCopy);
                        }
                    }
                }
            }

            if ($sFileDir !== false) {
                $sImageDb = str_replace(Phpfox::getParam('event.dir_image'), "", $sFileNameCopy);
            }
        }

        return $sImageDb;
    }

    /**
     * @param array $aVals
     * @param bool  $bIsUpdate , deprecated, remove in 4.7.0
     *
     * @return bool
     */
    private function _verify(&$aVals)
    {
        if (isset($aVals['category'])) {
            if (!is_array($aVals['category'])) {
                $aVals['category'] = [$aVals['category']];
            }
            $aCategories = array_filter($aVals['category']);
            $iCategoryId = end($aCategories);
            if ($iCategoryId) {
                $sCategories = Phpfox::getService('event.category')->getParentCategories($iCategoryId);
                if (empty($sCategories)) {
                    return Phpfox_Error::set(_p('The {{ item }} cannot be found.', ['item' => _p('category__l')]));
                }
                $this->_aCategories = explode(',', trim($sCategories, ','));
            }
        }

        $iStartTime = Phpfox::getLib('date')->mktime($aVals['start_hour'], $aVals['start_minute'], 0,
            $aVals['start_month'], $aVals['start_day'], $aVals['start_year']);
        $iEndTime = Phpfox::getLib('date')->mktime($aVals['end_hour'], $aVals['end_minute'], 0, $aVals['end_month'],
            $aVals['end_day'], $aVals['end_year']);

        if ($iEndTime < $iStartTime) {
            $this->_bIsEndingInThePast = true;
        }

        if ($iStartTime < PHPFOX_TIME && $iEndTime < PHPFOX_TIME){
            return Phpfox_Error::set(_p('the_end_time_should_be_greater_than_the_current_time'));
        }

        return true;
    }

    /**
     * @param int $iEvent
     * @param int $iRsvp
     * @param int $iUserId
     *
     * @return bool
     */
    public function addRsvp($iEvent, $iRsvp, $iUserId)
    {
        if (!Phpfox::isUser()) {
            return false;
        }

        if (($iInviteId = $this->database()->select('invite_id')
            ->from(Phpfox::getT('event_invite'))
            ->where('event_id = ' . (int)$iEvent . ' AND invited_user_id = ' . (int)$iUserId)
            ->execute('getSlaveField'))
        ) {
            if ((int)$iRsvp == 0) {
                db()->delete(Phpfox::getT('event_invite'), 'invite_id = ' . $iInviteId);
            } else {
                $this->database()->update(Phpfox::getT('event_invite'), [
                    'rsvp_id'         => $iRsvp,
                    'invited_user_id' => $iUserId,
                    'time_stamp'      => PHPFOX_TIME
                ], 'invite_id = ' . $iInviteId
                );
            }
            (Phpfox::isModule('request') ? Phpfox::getService('request.process')->delete('event_invite', $iEvent,
                $iUserId) : false);
        } else if ($iRsvp != 0) {
            $this->database()->insert(Phpfox::getT('event_invite'), [
                    'event_id'        => $iEvent,
                    'rsvp_id'         => $iRsvp,
                    'user_id'         => $iUserId,
                    'invited_user_id' => $iUserId,
                    'time_stamp'      => PHPFOX_TIME
                ]
            );
        }

        return true;
    }

    /**
     * @param int        $iId
     * @param array      $aVals
     * @param null|array $aEventPost , deprecated, remove in 4.7.0
     *
     * @return bool
     * @throws Exception
     */
    public function update($iId, $aVals, $aEventPost = null)
    {
        if (!$this->_verify($aVals)) {
            return false;
        }
        $aEvent = $this->database()->select('event_id, user_id, title, module_id, 
        image_path, server_id, location, start_time, end_time, address, is_online, online_link, location_lat, location_lng,
        country_iso, country_child_id, total_attachment, org_event_id')
            ->from($this->_sTable, 'e')
            ->where('event_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        $aEvent['description_parsed'] = $this->database()->select('description_parsed')
            ->from(Phpfox::getT('event_text'))
            ->where('event_id = ' . (int)$iId)
            ->executeField();

        if (!isset($aVals['privacy'])) {
            $aVals['privacy'] = 0;
        }

        if (!isset($aVals['privacy_comment'])) {
            $aVals['privacy_comment'] = 0;
        }

        $oParseInput = Phpfox::getLib('parse.input');

        if (!Phpfox::getService('ban')->checkAutomaticBan($aVals['title'] . ' ' . $aVals['description'])) {
            return false;
        }

        $iStartTime = Phpfox::getLib('date')->mktime($aVals['start_hour'], $aVals['start_minute'], 0,
            $aVals['start_month'], $aVals['start_day'], $aVals['start_year']);
        $iEndTime = Phpfox::getLib('date')->mktime($aVals['end_hour'], $aVals['end_minute'], 0, $aVals['end_month'],
            $aVals['end_day'], $aVals['end_year']);

        if ($iStartTime > $iEndTime) {
            $iEndTime = $iStartTime;
        }

        $aVals['location'] = trim($aVals['location']);

        $aSql = [
            'privacy'          => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
            'privacy_comment'  => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
            'title'            => $oParseInput->clean($aVals['title'], 255),
            'location'         => $aVals['location'] != '' ? $oParseInput->clean($aVals['location'], 255) : '',
            'start_time'       => Phpfox::getLib('date')->convertToGmt($iStartTime),
            'end_time'         => Phpfox::getLib('date')->convertToGmt($iEndTime),
            'start_gmt_offset' => Phpfox::getLib('date')->getGmtOffset($iStartTime),
            'end_gmt_offset'   => Phpfox::getLib('date')->getGmtOffset($iEndTime),
            'address'          => (empty($aVals['address']) ? null : Phpfox::getLib('parse.input')->clean($aVals['address'])),
            'is_online'        => isset($aVals['is_online']) ? 1 : 0,
            'online_link'      => isset($aVals['is_online']) && isset($aVals['online_link']) && $aVals['online_link'] != '' ? $aVals['online_link'] : '',
        ];

        $org_start_hour = (int)Phpfox::getTime('H',  $aEvent['start_time'], false);
        $org_start_min = (int)Phpfox::getTime('i',  $aEvent['start_time'], false);
        $org_end_hour = (int)Phpfox::getTime('H',  $aEvent['end_time'], false);
        $org_end_min = (int)Phpfox::getTime('i',  $aEvent['end_time'], false);
        $aVals['diff_start_time'] = ($aSql['start_time'] - $aVals['start_hour'] * 3600 - $aVals['start_minute'] * 60) - ($aEvent['start_time'] - $org_start_hour  * 3600 - $org_start_min * 60);
        $aVals['diff_end_time'] = ($aSql['end_time'] - $aVals['end_hour'] * 3600 - $aVals['end_minute'] * 60) - ($aEvent['end_time'] - $org_end_hour * 3600 - $org_end_min * 60);

        if (!empty($aVals['location_lat'])) {
            $aSql['location_lat'] = $aVals['location_lat'];
        }
        if (!empty($aVals['location_lng'])) {
            $aSql['location_lng'] = $aVals['location_lng'];
        }
        if (!empty($aVals['country_iso'])) {
            $aSql['country_iso'] = $aVals['country_iso'];
        }
        if (!empty($aVals['country_iso']) && !empty($aVals['country_child_id'])) {
            $aSql['country_child_id'] = Phpfox::getService('core.country')->getValidChildId($aVals['country_iso'], (int)$aVals['country_child_id']);
        } else {
            $aSql['country_child_id'] = 0;
        }

        if (!empty($aEvent['image_path']) && (!empty($aVals['temp_file']) || !empty($aVals['remove_photo']))) {
            if ($this->deleteImage($iId)) {
                $aSql['image_path'] = null;
                $aSql['server_id'] = 0;
            } else {
                return false;
            }
        }

        if (!empty($aVals['temp_file'])) {
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            if (!empty($aFile)) {
                if (!Phpfox::getService('user.space')->isAllowedToUpload($aEvent['user_id'], $aFile['size'])) {
                    Phpfox::getService('core.temp-file')->delete($aVals['temp_file'], true);
                    return false;
                }
                $aSql['image_path'] = $aFile['path'];
                $aSql['server_id'] = $aFile['server_id'];
                Phpfox::getService('user.space')->update($aEvent['user_id'], 'event', $aFile['size']);
                Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            }
        }
        if ($sPlugin = Phpfox_Plugin::get('event.service_process_update__start')) {
            return eval($sPlugin);
        }
        $this->database()->update($this->_sTable, $aSql, 'event_id = ' . (int)$iId);

        $aSql['description'] = (empty($aVals['description']) ? null : $oParseInput->clean($aVals['description']));
        $aSql['description_parsed'] = (empty($aVals['description']) ? null : $oParseInput->prepare($aVals['description']));

        $this->database()->update(Phpfox::getT('event_text'), [
            'description'        => $aSql['description'],
            'description_parsed' => $aSql['description_parsed']
        ], 'event_id = ' . (int)$iId);

        $bHasAttachments = (!empty($aVals['attachment']));
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }

        if (isset($aVals['emails']) || isset($aVals['invite'])) {
            $aInvites = $this->database()->select('invited_user_id, invited_email')
                ->from(Phpfox::getT('event_invite'))
                ->where('event_id = ' . (int)$iId)
                ->execute('getSlaveRows');
            $aInvited = [];
            foreach ($aInvites as $aInvite) {
                $aInvited[(empty($aInvite['invited_email']) ? 'user' : 'email')][(empty($aInvite['invited_email']) ? $aInvite['invited_user_id'] : $aInvite['invited_email'])] = true;
            }
        }

        $aCachedEmails = [];
        if (isset($aVals['invite']) && is_array($aVals['invite'])) {
            $sUserIds = '';
            foreach ($aVals['invite'] as $iUserId) {
                if (!is_numeric($iUserId)) {
                    continue;
                }
                $sUserIds .= $iUserId . ',';
            }
            $sUserIds = rtrim($sUserIds, ',');

            $aUsers = $this->database()->select('user_id, email, language_id, full_name')
                ->from(Phpfox::getT('user'))
                ->where('user_id IN(' . $sUserIds . ')')
                ->execute('getSlaveRows');

            foreach ($aUsers as $aUser) {
                if (isset($aCachedEmails[$aUser['email']])) {
                    continue;
                }

                if (isset($aInvited['user'][$aUser['user_id']])) {
                    continue;
                }

                if (Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriend(Phpfox::getUserId(),
                        $aUser['user_id'])
                ) {
                    continue;
                }

                $sLink = Phpfox_Url::instance()->permalink('event', $aEvent['event_id'], $aEvent['title']);
                $sMessage = _p('full_name_invited_you_to_the_title', [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title'     => $oParseInput->clean($aVals['title'], 255),
                    'link'      => $sLink
                ], $aUser['language_id']);

                if (!empty($aVals['personal_message'])) {
                    $sMessage .= _p('full_name_added_the_following_personal_message', [
                            'full_name' => Phpfox::getUserBy('full_name')
                        ], $aUser['language_id']
                        ) . $aVals['personal_message'];
                }
                $bSent = Phpfox::getLib('mail')->to($aUser['user_id'])
                    ->subject(_p(
                        'event.full_name_invited_you_to_the_event_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aVals['title'], 255)
                        ], $aUser['language_id']
                    ))
                    ->message($sMessage)
                    ->notification('event.email_notification')
                    ->translated()
                    ->send();
                if ($bSent) {
                    $this->_aInvited[] = ['user' => $aUser['full_name']];
                    $aCachedEmails[$aUser['email']] = true;

                    $this->database()->insert(Phpfox::getT('event_invite'), [
                            'event_id'        => $iId,
                            'user_id'         => Phpfox::getUserId(),
                            'invited_user_id' => $aUser['user_id'],
                            'time_stamp'      => PHPFOX_TIME
                        ]
                    );

                    (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('event_invite', $iId,
                        $aUser['user_id']) : null);
                }
            }
        }

        if (isset($aVals['emails'])) {
            $aEmails = explode(',', $aVals['emails']);
            foreach ($aEmails as $sEmail) {
                $sEmail = trim($sEmail);
                if (!Phpfox::getLib('mail')->checkEmail($sEmail)) {
                    continue;
                }

                if (isset($aCachedEmails[$sEmail])) {
                    continue;
                }

                if (isset($aInvited['email'][$sEmail])) {
                    continue;
                }

                $sLink = Phpfox_Url::instance()->permalink('event', $aEvent['event_id'], $aEvent['title']);

                $sMessage = _p('full_name_invited_you_to_the_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $oParseInput->clean($aVals['title'], 255),
                        'link'      => $sLink
                    ]
                );
                if (!empty($aVals['personal_message'])) {
                    $sMessage .= _p('full_name_added_the_following_personal_message', [
                            'full_name' => Phpfox::getUserBy('full_name')
                        ]
                    );
                    $sMessage .= $aVals['personal_message'];
                }
                $oMail = Phpfox::getLib('mail');
                if (isset($aVals['invite_from']) && $aVals['invite_from'] == 1) {
                    $oMail->fromEmail(Phpfox::getUserBy('email'))
                        ->fromName(Phpfox::getUserBy('full_name'));
                }
                $bSent = $oMail->to($sEmail)
                    ->subject([
                        'event.full_name_invited_you_to_the_event_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aVals['title'], 255)
                        ]
                    ])
                    ->message($sMessage)
                    ->send();

                if ($bSent) {
                    $this->_aInvited[] = ['email' => $sEmail];
                    $aCachedEmails[$sEmail] = true;
                    $this->database()->insert(Phpfox::getT('event_invite'), [
                            'event_id'      => $iId,
                            'type_id'       => 1,
                            'user_id'       => Phpfox::getUserId(),
                            'invited_email' => $sEmail,
                            'time_stamp'    => PHPFOX_TIME
                        ]
                    );
                }
            }
        }

        if ($this->checkEventChange($aEvent, $this->_aCategories, $aSql)) {
            $aEvent['title'] = $aSql['title'];
            $this->addJobSendNotificationWhenEventChange($aEvent, Phpfox::getUserId());
        }

        $this->database()->delete(Phpfox::getT('event_category_data'), 'event_id = ' . (int)$iId);
        foreach ($this->_aCategories as $iCategoryId) {
            $this->database()->insert(Phpfox::getT('event_category_data'), ['event_id' => $iId, 'category_id' => $iCategoryId]);
        }
        $this->cache()->removeGroup('event_category');

        if (empty($aEvent['module_id']) || $aEvent['module_id'] == 'event') {
            //Support for invitee privacy
            if ($aVals['privacy'] == '5') {
                $aVals['privacy'] = '0';
            }

            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('event', $iId, $aVals['privacy'], $aVals['privacy_comment']) : null);
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->clearCache('event', $iId) : null);

        (($sPlugin = Phpfox_Plugin::get('event.service_process_update__end')) ? eval($sPlugin) : false);

        if (Phpfox::isModule('privacy')) {
            if ($aVals['privacy'] == '4') {
                Phpfox::getService('privacy.process')->update('event', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
            } else {
                Phpfox::getService('privacy.process')->delete('event', $iId);
            }
        }

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->update('event', $aEvent['event_id'], $aEvent['user_id'], $aVals['description'], true);
        }

        switch($aVals['event_editconfirmboxoption_value']) {
            case 'only_this_event':
                break;
            case 'all_events_uppercase':
                $aEventIds = Phpfox::getService('event')->getBrotherEventByEventId($iId, $aEvent['org_event_id']);
                $this->updateBrotherEvent($iId, $aEventIds, $aVals);
                break;
        }

        return true;
    }

    public function updateBrotherEvent($event_id, $aEventIds, $aVals, $start_time = null, $end_time = null)
    {
        $aEvent = Phpfox::getService('event')->getAllDataEventById($event_id);
        if (isset($aEvent['event_id']) == false) {
            return false;
        }
        if ((int)$aEvent['is_repeat'] < 0) {
            return false;
        }

        $start_hour = (int)Phpfox::getTime('H', null == $start_time ? $aEvent['start_time'] : $start_time, false);
        $start_minute = (int)Phpfox::getTime('i', null == $start_time ? $aEvent['start_time'] : $start_time, false);
        $end_hour = (int)Phpfox::getTime('H', null == $end_time ? $aEvent['end_time'] : $end_time, false);
        $end_minute = (int)Phpfox::getTime('i', null == $end_time ? $aEvent['end_time'] : $end_time, false);

        $aEventField = $aEvent;

        unset($aEventField['event_id']);
        unset($aEventField['description']);
        unset($aEventField['description_parsed']);
        unset($aEventField['categories']);
        unset($aEventField['is_featured']);
        unset($aEventField['is_sponsor']);
        unset($aEventField['image_path']);
        unset($aEventField['server_id']);
        unset($aEventField['start_time']);
        unset($aEventField['end_time']);
        unset($aEventField['total_comment']);
        unset($aEventField['total_like']);
        unset($aEventField['total_view']);
        unset($aEventField['mass_email']);
        unset($aEventField['is_repeat']);
        unset($aEventField['time_repeat']);
        unset($aEventField['after_number_event']);

        foreach ($aEventIds as $key => $value) {
            $iId = $value['event_id'];

            $aEditedEvent = Phpfox::getService('event')->getEvent($iId);
            $start_month = (int)Phpfox::getTime('n', $aEditedEvent['start_time'], false);
            $start_day = (int)Phpfox::getTime('j', $aEditedEvent['start_time'], false);
            $start_year = (int)Phpfox::getTime('Y', $aEditedEvent['start_time'], false);

            $end_month = (int)Phpfox::getTime('n', $aEditedEvent['end_time'], false);
            $end_day = (int)Phpfox::getTime('j', $aEditedEvent['end_time'], false);
            $end_year = (int)Phpfox::getTime('Y', $aEditedEvent['end_time'], false);

            $iStartTime = Phpfox::getLib('date')->mktime($start_hour, $start_minute, 0, $start_month, $start_day, $start_year);
            $iEndTime = Phpfox::getLib('date')->mktime($end_hour, $end_minute, 0, $end_month, $end_day, $end_year);

            /*update if change start time and end time*/
            $iStartTime = $iStartTime + $aVals['diff_start_time'];
            $iEndTime = $iEndTime + $aVals['diff_end_time'];

            $aEventField['start_time'] = ($iStartTime);
            $aEventField['end_time'] = ($iEndTime);

            $this->database()->update(Phpfox::getT('event'), $aEventField, ['event_id' => $iId]);
            $this->deleteImage($iId);
            $copied_image_path = $this->copyRecurringImage($iId,
                [
                    'recurring_image' => $aEvent['image_path'],
                    'server_id' => $aEvent['server_id'],
                ]);

            if ($copied_image_path) {
                $this->database()->update(
                    $this->_sTable,
                    [
                        'image_path' => $copied_image_path,
                        'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')
                    ],
                    'event_id = ' . $aEditedEvent['event_id']);
            }

            $this->database()->update(Phpfox::getT('event_text'), [
                'description' => $aEvent['description'],
                'description_parsed' => $aEvent['description_parsed'],
            ], 'event_id = ' . (int)$iId
            );

            if (isset($aVals['category'])) {
                if (is_array($aVals['category'])) {
                    $categories = $aVals['category'];
                } else {
                    $categories = array($aVals['category']);
                }
                $this->database()->delete(Phpfox::getT('event_category_data'), 'event_id = ' . (int)$iId);
                foreach ($categories as $iCategoryId) {
                    $this->database()->insert(Phpfox::getT('event_category_data'), array('event_id' => $iId, 'category_id' => $iCategoryId));
                }
            }

            if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
                Phpfox::getService('tag.process')->update('event', $aEditedEvent['event_id'], $aEditedEvent['user_id'], $aVals['description'], true);
            }
        }
        return true;
    }

    /**
     * @param int $iId
     *
     * @return bool
     */
    public function deleteImage($iId)
    {
        $aEvent = $this->database()->select('user_id, image_path, server_id')
            ->from($this->_sTable)
            ->where('event_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aEvent['user_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_event'));
        }

        if (!Phpfox::getService('user.auth')
            ->hasAccess('event', 'event_id', $iId, 'event.can_edit_own_event', 'event.can_edit_other_event',
                $aEvent['user_id'])
        ) {
            return Phpfox_Error::set(_p('you_do_not_have_sufficient_permission_to_modify_this_event'));
        }

        if (!empty($aEvent['image_path'])) {
            $aParams = Phpfox::getService('event')->getUploadParams();
            $aParams['type'] = 'event';
            $aParams['path'] = $aEvent['image_path'];
            $aParams['user_id'] = $aEvent['user_id'];
            $aParams['update_space'] = ($aEvent['user_id'] ? true : false);
            $aParams['server_id'] = $aEvent['server_id'];

            if (!Phpfox::getService('user.file')->remove($aParams)) {
                return false;
            }
        }

        $this->database()->update($this->_sTable, ['image_path' => null, 'server_id' => 0], 'event_id = ' . (int)$iId);

        (($sPlugin = Phpfox_Plugin::get('event.service_process_deleteimage__end')) ? eval($sPlugin) : false);
        return true;
    }

    /**
     * @param $iInviteId
     *
     * @return bool
     * @throws Exception
     */
    public function deleteGuest($iInviteId)
    {
        $aEvent = $this->database()->select('e.event_id, e.user_id')
            ->from(Phpfox::getT('event_invite'), 'ei')
            ->join($this->_sTable, 'e', 'e.event_id = ei.event_id')
            ->where('ei.invite_id = ' . (int)$iInviteId)
            ->execute('getSlaveRow');

        if (!isset($aEvent['user_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_event'));
        }

        if (!Phpfox::getService('user.auth')->hasAccess('event', 'event_id', $aEvent['event_id'],
            'event.can_edit_own_event', 'event.can_edit_other_event', $aEvent['user_id'])
        ) {
            return Phpfox_Error::set(_p('you_do_not_have_sufficient_permission_to_modify_this_event'));
        }

        $this->database()->delete(Phpfox::getT('event_invite'), 'invite_id = ' . (int)$iInviteId);

        return true;
    }

    /**
     * @param int        $iId
     * @param null|array $aEvent
     *
     * @param bool       $bForce
     *
     * @return bool|mixed|string
     * @throws Exception
     */
    public function delete($iId, &$aEvent = null, $bForce = false)
    {
        if ($sPlugin = Phpfox_Plugin::get('event.service_process_delete__start')) {
            return eval($sPlugin);
        }

        $mReturn = true;
        if ($aEvent === null) {
            $aEvent = $this->database()->select('user_id, module_id, item_id, image_path, is_sponsor, is_featured, server_id, view_id')
                ->from($this->_sTable)
                ->where('event_id = ' . (int)$iId)
                ->execute('getSlaveRow');

            if (empty($aEvent)) {
                return $bForce ? false : Phpfox_Error::set(_p('unable_to_find_the_event_you_want_to_delete'));
            }

            if (in_array($aEvent['module_id'],
                    ['pages', 'groups']) && Phpfox::getService($aEvent['module_id'])->isAdmin($aEvent['item_id'])
            ) {
                $mReturn = Phpfox::getService($aEvent['module_id'])->getUrl($aEvent['item_id']) . 'event/';
            } else {
                if (!isset($aEvent['user_id'])) {
                    return $bForce ? false : Phpfox_Error::set(_p('unable_to_find_the_event_you_want_to_delete'));
                }

                if (!$bForce && !Phpfox::getService('user.auth')->hasAccess('event', 'event_id', $iId, 'event.can_delete_own_event',
                        'event.can_delete_other_event', $aEvent['user_id'])
                ) {
                    return Phpfox_Error::set(_p('You don\'t have permission to {{ action }} this {{ item }}.',
                        ['action' => _p('delete__l'), 'item' => _p('event__l')]));
                }
            }
        }

        if (!empty($aEvent['image_path'])) {
            $sPath = Phpfox::getParam('event.dir_image');
            $aSizes = Phpfox::getParam('event.thumbnail_sizes');
            $aImages = [$sPath . sprintf($aEvent['image_path'], '')];
            foreach ($aSizes as $iSize) {
                $aImages[] = $sPath . sprintf($aEvent['image_path'], '_' . $iSize);
                $aImages[] = $sPath . sprintf($aEvent['image_path'], '_' . $iSize . '_square');
            }

            $iFileSizes = 0;
            foreach ($aImages as $sImage) {
                if (file_exists($sImage)) {
                    $iFileSizes += filesize($sImage);
                    if ($sPlugin = Phpfox_Plugin::get('event.service_process_delete__pre_unlink')) {
                        return eval($sPlugin);
                    }
                }

                if ($aEvent['server_id'] > 0) {
                    // Get the file size stored when the photo was uploaded
                    $sTempUrl = Phpfox::getLib('cdn')->getUrl(str_replace(Phpfox::getParam('event.dir_image'),
                        Phpfox::getParam('event.url_image'), $sImage));

                    $aHeaders = get_headers($sTempUrl, true);
                    if (preg_match('/200 OK/i', $aHeaders[0])) {
                        $iFileSizes += (int)$aHeaders["Content-Length"];
                    }
                    if ($sPlugin = Phpfox_Plugin::get('event.service_process_delete__pre_unlink')) {
                        return eval($sPlugin);
                    }
                }
                Phpfox_File::instance()->unlink($sImage);
            }

            if ($iFileSizes > 0) {
                if ($sPlugin = Phpfox_Plugin::get('event.service_process_delete__pre_space_update')) {
                    return eval($sPlugin);
                }
                Phpfox::getService('user.space')->update($aEvent['user_id'], 'event', $iFileSizes, '-');
            }
        }

        if ($sPlugin = Phpfox_Plugin::get('event.service_process_delete__pre_deletes')) {
            return eval($sPlugin);
        }

        if (Phpfox::isModule('feed')) {
            $aFeeds = db()->select('feed_id')->from(':event_feed')->where(['parent_user_id' => $iId])->executeRows();
            foreach (array_column($aFeeds, 'feed_id') as $iFeedId) {
                Phpfox::getService('feed.process')->deleteFeed($iFeedId, 'event');
            }
        }

        (Phpfox::isModule('attachment') ? Phpfox::getService('attachment.process')->deleteForItem($aEvent['user_id'],
            $iId, 'event') : null);
        (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem(null, $iId,
            'event') : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('event', $iId) : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_event', $iId) : null);
        (Phpfox::isModule('like') ? Phpfox::getService('like.process')->delete('event', (int)$iId, 0, true) : null);
        (Phpfox::isModule('notification') ? Phpfox::getService('notification.process')->deleteAllOfItem([
            'event_like',
            'event_comment',
            'event_invite'
        ], (int)$iId) : null);

        //close all sponsorships
        (Phpfox::isAppActive('Core_BetterAds') ? Phpfox::getService('ad.process')->closeSponsorItem('event', (int)$iId) : null);

        $aInvites = $this->database()->select('invite_id, invited_user_id')
            ->from(Phpfox::getT('event_invite'))
            ->where('event_id = ' . (int)$iId)
            ->execute('getSlaveRows');
        foreach ($aInvites as $aInvite) {
            (Phpfox::isModule('request') ? Phpfox::getService('request.process')->delete('event_invite',
                $aInvite['invite_id'], $aInvite['invited_user_id']) : false);
        }

        if ((int)$aEvent['view_id'] == 0) {
            Phpfox::getService('user.activity')->update($aEvent['user_id'], 'event', '-');
        }

        $this->database()->delete($this->_sTable, 'event_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('event_text'), 'event_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('event_category_data'), 'event_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('event_invite'), 'event_id = ' . (int)$iId);
        $this->cache()->removeGroup('event_category');
        $iTotalEvent = $this->database()
            ->select('total_event')
            ->from(Phpfox::getT('user_field'))
            ->where('user_id =' . (int)$aEvent['user_id'])->execute('getSlaveField');
        $iTotalEvent = $iTotalEvent - 1;

        if ($iTotalEvent > 0) {
            $this->database()->update(Phpfox::getT('user_field'),
                ['total_event' => $iTotalEvent],
                'user_id = ' . (int)$aEvent['user_id']);
        }

        if ($sPlugin = Phpfox_Plugin::get('event.service_process_delete__end')) {
            return eval($sPlugin);
        }

        if ($aEvent['is_sponsor'] == 1) {
            $this->cache()->remove('event_sponsored');
        }
        if ($aEvent['is_featured'] == 1) {
            $this->cache()->remove('event_featured');
        }

        return $mReturn;
    }

    /**
     * @param int $iId
     * @param int $iType
     *
     * @return bool
     */
    public function feature($iId, $iType)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('event.can_feature_events', true);
        $this->database()->update($this->_sTable, ['is_featured' => ($iType ? '1' : '0')], 'event_id = ' . (int)$iId);
        return true;
    }

    /**
     * @param $iId
     * @param $iType
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function sponsor($iId, $iType)
    {
        if (!Phpfox::getUserParam('event.can_sponsor_event') && !Phpfox::getUserParam('event.can_purchase_sponsor') && !defined('PHPFOX_API_CALLBACK')) {
            return Phpfox_Error::set(_p('hack_attempt'));
        }

        $iType = (int)$iType;
        if ($iType != 1 && $iType != 0) {
            return false;
        }
        $this->database()->update($this->_sTable, ['is_sponsor' => $iType], 'event_id = ' . (int)$iId);
        if ($sPlugin = Phpfox_Plugin::get('event.service_process_sponsor__end')) {
            return eval($sPlugin);
        }

        return true;
    }

    /**
     * @param int $iId
     *
     * @return bool
     */
    public function approve($iId)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('event.can_approve_events', true);

        $aEvent = $this->database()->select(Phpfox::getUserField() . ', v.*')
            ->from($this->_sTable, 'v')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
            ->where('v.event_id = ' . (int)$iId)
            ->executeRow();

        if (!isset($aEvent['event_id'])) {
            Phpfox_Error::set(_p('the_event_you_are_looking_for_does_not_exist_or_has_been_deleted'));
            return false;
        }

        $this->database()->update($this->_sTable, ['view_id' => '0'], 'event_id = ' . $aEvent['event_id']);

        if (Phpfox::isModule('notification')) {
            Phpfox::getService('notification.process')->add('event_approved', $aEvent['event_id'], $aEvent['user_id']);
        }

        // Send the user an email
        $sLink = Phpfox_Url::instance()->permalink('event', $aEvent['event_id'], $aEvent['title']);

        Phpfox::getLib('mail')->to($aEvent['user_id'])
            ->subject([
                'event.your_event_has_been_approved_on_site_title',
                ['site_title' => Phpfox::getParam('core.site_title')]
            ])
            ->message([
                'event.your_event_has_been_approved_on_site_title_link',
                ['site_title' => Phpfox::getParam('core.site_title'), 'link' => $sLink]
            ])
            ->notification('event.email_notification')
            ->send();

        Phpfox::getService('user.activity')->update($aEvent['user_id'], 'event');

        $this->addRsvp($aEvent['event_id'], 1, $aEvent['user_id']);

        (($sPlugin = Phpfox_Plugin::get('event.service_process_approve__1')) ? eval($sPlugin) : false);

        if ($aEvent['module_id'] == 'event' && Phpfox::isModule('feed') && Phpfox::getParam('event.event_allow_create_feed_when_add_new_item', 1)) {
            Phpfox::getService('feed.process')->add('event', $aEvent['event_id'], $aEvent['privacy'], $aEvent['privacy_comment'], 0,
                $aEvent['user_id']);
        } else {
            if (Phpfox::isModule('feed') && Phpfox::getParam('event.event_allow_create_feed_when_add_new_item', 1)) {
                Phpfox::getService('feed.process')
                    ->callback(Phpfox::callback($aEvent['module_id'] . '.getFeedDetails', $aEvent['item_id']))
                    ->add('event', $aEvent['event_id'], $aEvent['privacy'], $aEvent['privacy_comment'], $aEvent['item_id'],
                        $aEvent['user_id']);
            }
        }
        return true;
    }

    /**
     * @param int    $iId
     * @param int    $iPage
     * @param string $sSubject
     * @param string $sText
     *
     * @return bool|mixed
     */
    public function massEmail($iId, $iPage, $sSubject, $sText)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('event.can_mass_mail_own_members', true);

        $aEvent = Phpfox::getService('event')->getEvent($iId, true);

        if (!isset($aEvent['event_id'])) {
            return false;
        }

        if ($aEvent['user_id'] != Phpfox::getUserId()) {
            return false;
        }
        if ($sPlugin = Phpfox_Plugin::get('event.service_process_massemail__start')) {
            return eval($sPlugin);
        }
        if (!Phpfox::getService('ban')->checkAutomaticBan($sText)) {
            return false;
        }
        list($iCnt, $aGuests) = Phpfox::getService('event')->getInvites($iId, 1, $iPage, 20);

        $sLink = Phpfox_Url::instance()->permalink('event', $aEvent['event_id'], $aEvent['title']);

        foreach ($aGuests as $aGuest) {
            if ($aGuest['user_id'] == Phpfox::getUserId()) {
                continue;
            }

            $sMessage = '<br />
            ' . _p('notice_this_is_a_newsletter_sent_from_the_event', [], $aGuest['language_id']) . ': ' . $aEvent['title'] . '<br />
            <a href="' . $sLink . '">' . $sLink . '</a>
            <br /><br />
            ' . $sText;

            Phpfox::getLib('mail')->to($aGuest['user_id'])
                ->subject(_p($sSubject, [], $aGuest['language_id']))
                ->message($sMessage)
                ->notification('event.mass_emails')
                ->translated()
                ->send();
        }
        if ($sPlugin = Phpfox_Plugin::get('event.service_process_massemail__end')) {
            return eval($sPlugin);
        }
        $this->database()->update($this->_sTable, ['mass_email' => PHPFOX_TIME],
            'event_id = ' . $aEvent['event_id']);

        return $iCnt;
    }

    /**
     * @param int $iId
     *
     * @return bool
     */
    public function removeInvite($iId)
    {
        $this->database()->delete(Phpfox::getT('event_invite'),
            'event_id = ' . (int)$iId . ' AND invited_user_id = ' . Phpfox::getUserId());

        (Phpfox::isModule('request') ? Phpfox::getService('request.process')->delete('event_invite', $iId,
            Phpfox::getUserId()) : false);

        return true;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('event.service_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }

    /**
     * @param      $iId
     * @param      $sCounter
     * @param bool $bMinus
     */
    public function updateCounter($iId, $sCounter, $bMinus = false)
    {
        $this->database()->update($this->_sTable, [
            $sCounter => ['= ' . $sCounter . ' ' . ($bMinus ? '-' : '+'), 1]
        ], 'event_id = ' . (int)$iId
        );
    }

    public function convertOldLocation($aParams)
    {
        $iLastId = isset($aParams['last_id']) ? $aParams['last_id'] : 0;
        $iLimit = 50;
        $aOldEvents = db()->select('*')
            ->from(':event')
            ->where([
                'location_lat IS NULL',
                'AND location_lng IS NULL',
                'AND event_id > ' . (int)$iLastId
            ])->order('event_id asc')->limit($iLimit)->executeRows();
        if (!count($aOldEvents)) {
            return false;
        }
        $newLastId = $aOldEvents[count($aOldEvents) - 1]['event_id'];
        foreach ($aOldEvents as $sKey => $aEvent) {
            $sFullAddress = $aEvent['location'];
            if ($aEvent['address']) {
                $sFullAddress .= ', ' . $aEvent['address'];
            }
            if ($aEvent['city']) {
                $sFullAddress .= ', ' . $aEvent['city'];
            }
            if ($aEvent['postal_code']) {
                $sFullAddress .= ' ' . $aEvent['postal_code'];
            }
            if ($aEvent['country_child_id']) {
                $sFullAddress .= ', ' . Phpfox::getService('core.country')->getChild($aEvent['country_child_id']);
            }
            if ($aEvent['country_iso']) {
                $sFullAddress .= ', ' . Phpfox::getService('core.country')->getCountry($aEvent['country_iso']);
            }
            $aLocation = Phpfox::getLib('location.gmap')->convertToLatLng($sFullAddress);
            if (!$aLocation) {
                db()->update(':event', [
                    'location'     => $sFullAddress,
                    'location_lat' => '',
                    'location_lng' => '',
                ], 'event_id = ' . (int)$aEvent['event_id']);
            } else {
                db()->update(':event', [
                    'location'     => $sFullAddress,
                    'location_lat' => $aLocation['latitude'],
                    'location_lng' => $aLocation['longitude'],
                ], 'event_id = ' . (int)$aEvent['event_id']);
            }
        }
        $iRemain = db()->select('COUNT(*)')->from(':event')->where([
            'location_lat IS NULL',
            'AND location_lng IS NULL',
            'AND event_id > ' . (int)$newLastId
        ])->executeField();
        return [
            'last_id'      => $newLastId,
            'total_remain' => $iRemain
        ];
    }

    public function addJobSendNotificationForPostStatusInEvent($aEvent, $iFeedId, $sType)
    {
        Phpfox_Queue::instance()->addJob('event_add_notification_for_post_status_in_event', [
            'aEvent'  => $aEvent,
            'iFeedId' => $iFeedId,
            'sType'   => $sType
        ]);
    }

    public function sendNotificationForPostStatusInEvent($aEvent, $iFeedId, $sType)
    {
        if (Phpfox::isModule('notification')) {
            $aFeed = Phpfox::getService('feed')->getFeed($iFeedId, 'event_');

            if ($aFeed) {
                list($iCnt, $aInvites) = Phpfox::getService('event')->getInvites($aEvent['event_id'], 1,
                    0, 0);

                if ($iCnt) {
                    $aSenderUser = Phpfox::getService('user')->getUser($aFeed['user_id']);

                    if ($aSenderUser) {
                        foreach ($aInvites as $aInvite) {
                            //event_comment
                            if ($sType == 'event_comment') {
                                if ($aInvite['user_id'] == $aEvent['user_id'] || $aInvite['user_id'] == $aFeed['user_id']) {
                                    continue;
                                }

                                list($subject, $message) = $this->_prepareEventCommentMailContent($aFeed, $aEvent, $aSenderUser);

                            } else {
                                if ($aInvite['user_id'] == $aFeed['user_id']) {
                                    continue;
                                }

                                list($aFeed, $subject, $message) = $this->_prepareAddPhotoEventMailContent($aEvent, $aFeed, $aInvite['user_id'], $aSenderUser);
                            }

                            Phpfox::getLib('mail')->to($aInvite['user_id'])
                                ->subject($subject)
                                ->message($message)
                                ->notification('event.email_notification')
                                ->send();

                            Phpfox::getService('notification.process')->add($sType, $aFeed['item_id'], $aInvite['user_id'], $aFeed['user_id'], true);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $aFeed
     * @param       $aEvent
     * @param array $aSenderUser
     *
     * @return array[]
     */
    private function _prepareEventCommentMailContent($aFeed, $aEvent, $aSenderUser)
    {
        $link = Phpfox_Url::instance()->permalink(['event', 'comment-id' => $aFeed['item_id']],
            $aEvent['event_id'], $aEvent['title']);

        $subject = [
            'full_name_commented_on_event_title_email', [
                'full_name' => $aSenderUser['full_name'],
                'title'     => $aEvent['title']
            ]
        ];

        $message = [
            'full_name_commented_on_event_title_message', [
                'full_name' => $aSenderUser['full_name'],
                'title'     => $aEvent['title'],
                'link'      => $link
            ]
        ];

        if ($aFeed['user_id'] == $aEvent['user_id']) {
            $subject = [
                'full_name_commented_on_gender_own_event_title_email', [
                    'full_name' => $aSenderUser['full_name'],
                    'gender'    => Phpfox::getService('user')->gender($aSenderUser['gender'], 1),
                    'title'     => $aEvent['title']
                ]
            ];

            $message = [
                'full_name_commented_on_gender_own_event_title_message', [
                    'full_name' => $aSenderUser['full_name'],
                    'gender'    => Phpfox::getService('user')->gender($aSenderUser['gender'], 1),
                    'title'     => $aEvent['title'],
                    'link'      => $link
                ]
            ];
        }

        return [$subject, $message];
    }

    /**
     * @param       $aEvent
     * @param array $aFeed
     * @param       $user_id
     * @param array $aSenderUser
     *
     * @return array
     */
    private function _prepareAddPhotoEventMailContent($aEvent, $aFeed, $user_id, $aSenderUser)
    {
        $sLink1 = Phpfox::getLib('url')->permalink('event', $aEvent['event_id'], $aEvent['title']);
        $sLink2 = Phpfox::getLib('url')->permalink('photo', $aFeed['item_id']) . (isset($aFeed['feed_id']) ? 'feed_' . $aFeed['feed_id'] : '');

        if ($user_id == $aEvent['user_id']) {
            $subject = [
                'full_name_added_photo_s_on_your_event_title',
                [
                    'full_name' => $aSenderUser['full_name'],
                    'title'     => $aEvent['title']
                ]
            ];

            $message = [
                'full_name_added_photo_s_on_your_event_message',
                [
                    'full_name' => $aSenderUser['full_name'],
                    'link1'     => $sLink1,
                    'title'     => $aEvent['title'],
                    'link2'     => $sLink2,
                ]
            ];
        } else if ($aEvent['user_id'] == $aFeed['user_id']) {
            $subject = [
                'full_name_added_photo_s_on_gender_own_event_title',
                [
                    'full_name' => $aSenderUser['full_name'],
                    'gender'    => Phpfox::getService('user')->gender($aSenderUser['gender'], 1),
                    'title'     => $aEvent['title']
                ]
            ];

            $message = [
                'full_name_added_photo_s_on_gender_own_event_message',
                [
                    'full_name' => $aSenderUser['full_name'],
                    'gender'    => Phpfox::getService('user')->gender($aSenderUser['gender'], 1),
                    'link1'     => $sLink1,
                    'title'     => $aEvent['title'],
                    'link2'     => $sLink2,
                ]
            ];
        } else {
            $subject = [
                'full_name_added_photo_s_on_event_title',
                [
                    'full_name' => $aSenderUser['full_name'],
                    'title'     => $aEvent['title']
                ]
            ];

            $message = [
                'full_name_added_photo_s_on_event_message',
                [
                    'full_name' => $aSenderUser['full_name'],
                    'link1'     => $sLink1,
                    'title'     => $aEvent['title'],
                    'link2'     => $sLink2,
                ]
            ];
        }
        return [$aFeed, $subject, $message];
    }

    private function checkEventChange($aOldEvent, $aNewCategories, $aSql)
    {
        foreach ($aSql as $key => $value) {
            if (in_array($key, ['privacy', 'privacy_comment', 'description'])) {
                continue;
            }

            if (isset($aOldEvent[$key]) && $value != $aOldEvent[$key]) {
                return true;
            }
        }

        $aOldCategories = $this->database()->select('category_id')
            ->from(Phpfox::getT('event_category_data'))
            ->where('event_id = ' . $aOldEvent['event_id'])
            ->executeRows();

        if (!empty($aOldCategories)) {
            $aOldCategoriesId = array_column($aOldCategories, 'category_id');
            if (!empty(array_diff($aNewCategories, $aOldCategoriesId))) {
                return true;
            }
        }

        $iNewTotalAttachment = $this->database()->select('total_attachment')
            ->from($this->_sTable)
            ->where('event_id = ' . $aOldEvent['event_id'])
            ->executeField();

        if ($iNewTotalAttachment != $aOldEvent['total_attachment']) {
            return true;
        }

        return false;
    }

    public function addJobSendNotificationWhenEventChange($aEvent, $iSenderUserId)
    {
        \Phpfox_Queue::instance()->addJob('event_add_notification_when_change_event_content', [
            'aEvent'        => $aEvent,
            'iSenderUserId' => $iSenderUserId,
        ]);
    }

    public function sendNotificationWhenEventChange($aEvent, $iSenderUserId)
    {
        if (Phpfox::isModule('notification')) {
            list($iCnt, $aInvites) = Phpfox::getService('event')->getInvites($aEvent['event_id'], 1,
                0, 0);

            if ($iCnt) {
                $aSenderUser = Phpfox::getService('user')->getUser($iSenderUserId);

                if ($aSenderUser) {
                    $link = Phpfox_Url::instance()->permalink('event', $aEvent['event_id'], $aEvent['title']);

                    foreach ($aInvites as $aInvite) {
                        if ($aInvite['user_id'] == $iSenderUserId) {
                            continue;
                        }

                        if ($iSenderUserId == $aEvent['user_id']) {
                            $subject = [
                                'full_name_changed_content_on_gender_own_event_title_email', [
                                    'full_name' => $aSenderUser['full_name'],
                                    'gender'    => Phpfox::getService('user')->gender($aSenderUser['gender'], 1),
                                    'title'     => $aEvent['title']
                                ]
                            ];

                            $message = [
                                'full_name_changed_content_on_gender_own_event_title_message', [
                                    'full_name' => $aSenderUser['full_name'],
                                    'gender'    => Phpfox::getService('user')->gender($aSenderUser['gender'], 1),
                                    'title'     => $aEvent['title'],
                                    'link'      => $link
                                ]
                            ];
                        } else if ($aInvite['user_id'] == $aEvent['user_id']) {
                            $subject = [
                                'full_name_changed_content_on_your_event_title_email', [
                                    'full_name' => $aSenderUser['full_name'],
                                    'title'     => $aEvent['title']
                                ]
                            ];

                            $message = [
                                'full_name_changed_content_on_your_event_title_message', [
                                    'full_name' => $aSenderUser['full_name'],
                                    'title'     => $aEvent['title'],
                                    'link'      => $link
                                ]
                            ];
                        } else {
                            $subject = [
                                'full_name_changed_content_on_event_title_email', [
                                    'full_name' => $aSenderUser['full_name'],
                                    'title'     => $aEvent['title']
                                ]
                            ];

                            $message = [
                                'full_name_changed_content_on_event_title_message', [
                                    'full_name' => $aSenderUser['full_name'],
                                    'title'     => $aEvent['title'],
                                    'link'      => $link
                                ]
                            ];
                        }

                        Phpfox::getLib('mail')->to($aInvite['user_id'])
                            ->subject($subject)
                            ->message($message)
                            ->notification('event.email_notification')
                            ->send();

                        Phpfox::getService('notification.process')->add('event_change_content', $aEvent['event_id'], $aInvite['user_id'], $iSenderUserId, true);
                    }
                }
            }

        }
    }
}