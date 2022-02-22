<?php

namespace Apps\Core_Events\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Plugin;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Event
 * @package Apps\Core_Events\Service
 */
class Event extends \Phpfox_Service
{
    /**
     * @var string
     */
    protected $_sTable = '';
    /**
     * @var bool|array
     */
    private $_aCallback = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('event');
    }

    /**
     * @param array $aCallback
     *
     * @return $this
     */
    public function callback($aCallback)
    {
        $this->_aCallback = $aCallback;
        return $this;
    }

    /**
     * Check if user is invited to event by event's owner
     *
     * @param null $iEventId
     * @param null $iOwnerUserId
     * @param null $iInvitedUserId
     *
     * @return bool
     */
    public function isInvitedByOwner($iEventId = null, $iOwnerUserId = null, $iInvitedUserId = null)
    {
        if (empty($iEventId) || empty($iOwnerUserId) || empty($iInvitedUserId)) {
            return false;
        }
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('event_invite'))
            ->where('event_id = ' . (int)$iEventId . ' AND user_id = ' . (int)$iOwnerUserId . ' AND invited_user_id = ' . (int)$iInvitedUserId)
            ->execute('getSlaveField');

        return $iCnt > 0;
    }

    /**
     * Check if user is invited to event by event's owner
     *
     * @param null $iEventId
     * @param null $iOwnerUserId
     * @param null $aUser [user_id, email]
     *
     * @return bool
     */
    public function isInvitedByOwnerIdOREmail($iEventId = null, $iOwnerUserId = null, $aUser = null)
    {
        if (empty($iEventId) || empty($iOwnerUserId) || empty($aUser)) {
            return false;
        }

        $bIsInvited = $this->isInvitedByOwner($iEventId, $iOwnerUserId, $aUser['user_id']);

        if (!$bIsInvited) {
            $iCnt = db()->select('COUNT(*)')
                ->from(Phpfox::getT('event_invite'))
                ->where('event_id = ' . (int)$iEventId . ' AND user_id = ' . (int)$iOwnerUserId .
                    ' AND invited_email = \'' . $aUser['email'] . '\'')
                ->execute('getSlaveField');

            $bIsInvited = $iCnt > 0;
        }

        return $bIsInvited;
    }

    /**
     * @param int $iId
     *
     * @return string
     */
    public function getTimeLeft($iId)
    {
        $aEvent = $this->getEvent($iId, true);

        return ($aEvent['mass_email'] + (Phpfox::getUserParam('event.total_mass_emails_per_hour') * 60));
    }

    /**
     * @param int  $iEvent
     * @param bool $bUseId , deprecated, remove in 4.7.0
     * @param bool $bNoCache
     *
     * @return array|int|null|string
     */
    public function getEvent($iEvent, $bUseId = false, $bNoCache = false)
    {
        static $aEvent = null;

        if ($aEvent !== null && isset($aEvent['event_id']) && $aEvent['event_id'] == $iEvent && $bNoCache === false) {
            return $aEvent;
        }


        if (Phpfox::isUser()) {
            $this->database()->select('ei.invite_id, ei.rsvp_id, ')->leftJoin(Phpfox::getT('event_invite'), 'ei',
                'ei.event_id = e.event_id AND ei.invited_user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('friend')) {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f',
                "f.user_id = e.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        } else {
            $this->database()->select('0 as is_friend, ');
        }

        if (Phpfox::isModule('like')) {
            $this->database()->select('l.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'event\' AND l.item_id = e.event_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('track')) {
            $sJoinQuery = Phpfox::isUser() ? 'pt.user_id = ' . Phpfox::getUserBy('user_id') : 'pt.ip_address = \'' . $this->database()->escape(Phpfox::getIp()) . '\'';
            $this->database()->select('pt.item_id AS is_viewed, ')
                ->leftJoin(Phpfox::getT('track'), 'pt',
                    'pt.item_id = e.event_id AND pt.type_id=\'event\' AND ' . $sJoinQuery);
        }

        $aEvent = $this->database()->select('e.*, e.country_iso, ' . (Phpfox::getParam('core.allow_html') ? 'et.description_parsed' : 'et.description') . ' AS description, ' . (Phpfox::getUserField() ? Phpfox::getUserField() . ', ' : '') . 'e.country_iso')
            ->from($this->_sTable, 'e')
            ->innerJoin(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
            ->where('e.event_id = ' . (int)$iEvent)
            ->execute('getSlaveRow');

        if (!isset($aEvent['event_id'])) {
            return false;
        }

        if (!isset($aEvent['is_viewed'])) {
            $aEvent['is_viewed'] = 0;
        }

        if (!isset($aEvent['is_friend'])) {
            $aEvent['is_friend'] = 0;
        }
        if (!Phpfox::isUser()) {
            $aEvent['invite_id'] = 0;
            $aEvent['rsvp_id'] = 0;
        }

        if ($aEvent['view_id'] == '1') {
            if ($aEvent['user_id'] == Phpfox::getUserId() || Phpfox::getUserParam('event.can_approve_events')) {

            } else {
                return false;
            }
        }

        $aEvent['event_date'] = Phpfox::getTime(Phpfox::getParam('event.event_basic_information_time'),
            $aEvent['start_time']);
        $aEvent['start_day'] = strftime('%d', $aEvent['start_time']);
        $aEvent['start_month'] = Phpfox::getTime('F', $aEvent['start_time']);
        $aEvent['start_hour_minute'] = Phpfox::getTime(Phpfox::getParam('event.event_time_format'), $aEvent['start_time']);

        if ($aEvent['start_time'] < $aEvent['end_time']) {
            $aEvent['event_date'] .= ' - ';
            if ($this->_isTheSameDate($aEvent['start_time'], $aEvent['end_time'])) {
                $aEvent['event_date'] .= Phpfox::getTime(Phpfox::getParam('event.event_time_format'), $aEvent['end_time']);
            } else {
                $aEvent['event_date'] .= Phpfox::getTime(Phpfox::getParam('event.event_basic_information_time'),
                    $aEvent['end_time']);
            }
        }

        if (isset($aEvent['gmap']) && !empty($aEvent['gmap'])) {
            $aEvent['gmap'] = unserialize($aEvent['gmap']);
        }

        $aEvent['categories'] = Phpfox::getService('event.category')->getCategoriesById($aEvent['event_id']);
        $aEvent['categories_id'] = Phpfox::getService('event.category')->getCategoryIds($aEvent['event_id']);
        if (!empty($aEvent['location']) && !empty($aEvent['location_lat']) && !empty($aEvent['location_lng'])) {
            $aEvent['map_location'] = urlencode($aEvent['location']);
        } else if (!empty($aEvent['address'])) {
            $aEvent['map_location'] = $aEvent['address'];
            if (!empty($aEvent['city'])) {
                $aEvent['map_location'] .= ',' . $aEvent['city'];
            }
            if (!empty($aEvent['postal_code'])) {
                $aEvent['map_location'] .= ',' . $aEvent['postal_code'];
            }
            if (!empty($aEvent['country_child_id'])) {
                $aEvent['map_location'] .= ',' . Phpfox::getService('core.country')->getChild($aEvent['country_child_id']);
            }
            if (!empty($aEvent['event_country_iso'])) {
                $aEvent['map_location'] .= ',' . Phpfox::getService('core.country')->getCountry($aEvent['event_country_iso']);
            }

            $aEvent['map_location'] = urlencode($aEvent['map_location']);
        }
        $aEvent['bookmark'] = Phpfox::getLib('url')->permalink('event', $aEvent['event_id'], $aEvent['title']);
        $aEvent['start_time_micro'] = Phpfox::getTime('Y-m-d', $aEvent['start_time']);

        switch ($aEvent['privacy']) {
            case 0:
            {
                $aEvent['privacy_title'] = _p('everyone');
                break;
            }
            case 1:
            {
                $aEvent['privacy_title'] = _p('friends');
                break;
            }
            case 2:
            {
                $aEvent['privacy_title'] = _p('friends_of_friends');
                break;
            }
            case 3:
            {
                $aEvent['privacy_title'] = _p('only_me');
                break;
            }
            case 5:
            {
                $aEvent['privacy_title'] = _p('invitee_only_privacy');
                break;
            }
            default:
            {
                $aEvent['privacy_title'] = _p('custom');
                break;
            }
        }

        $aUser = Phpfox::getService('user')->getUser(Phpfox::getUserId());
        $bIsInvited = $this->isInvitedByOwnerIdOREmail($aEvent['event_id'], $aEvent['user_id'], $aUser);

        $aEvent['is_invited'] = $bIsInvited;

        return $aEvent;
    }

    /**
     * @param int  $iId
     * @param bool $bNoCache
     *
     * @return bool
     */
    public function canSendEmails($iId, $bNoCache = false)
    {
        if (Phpfox::getUserParam('event.total_mass_emails_per_hour') === 0) {
            return true;
        }
        $aEvent = $this->getEvent($iId, true, $bNoCache);
        return (($aEvent['mass_email'] + (Phpfox::getUserParam('event.total_mass_emails_per_hour') * 60) > PHPFOX_TIME) ? false : true);
    }

    /**
     * @param int  $iId
     * @param bool $bForce
     *
     * @return array|bool
     */
    public function getForEdit($iId, $bForce = false)
    {
        $aEvent = $this->database()->select('e.*, et.description')
            ->from($this->_sTable, 'e')
            ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
            ->where('e.event_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aEvent)) {
            return false;
        }
        if ($bForce === true || (($aEvent['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('event.can_edit_own_event')) || Phpfox::getUserParam('event.can_edit_other_event'))) {
            $aEvent['start_time'] = Phpfox::getLib('date')->convertFromGmt($aEvent['start_time'], $aEvent['start_gmt_offset']);
            $aEvent['end_time'] = Phpfox::getLib('date')->convertFromGmt($aEvent['end_time'], $aEvent['end_gmt_offset']);

            $aEvent['start_month'] = date('n', $aEvent['start_time']);
            $aEvent['start_day'] = date('j', $aEvent['start_time']);
            $aEvent['start_year'] = date('Y', $aEvent['start_time']);
            $aEvent['start_hour'] = date('H', $aEvent['start_time']);
            $aEvent['start_minute'] = date('i', $aEvent['start_time']);

            $aEvent['end_month'] = date('n', $aEvent['end_time']);
            $aEvent['end_day'] = date('j', $aEvent['end_time']);
            $aEvent['end_year'] = date('Y', $aEvent['end_time']);
            $aEvent['end_hour'] = date('H', $aEvent['end_time']);
            $aEvent['end_minute'] = date('i', $aEvent['end_time']);

            $aEvent['categories'] = Phpfox::getService('event.category')->getCategoryIds($aEvent['event_id']);
            if (!empty($aEvent['image_path'])) {
                $aEvent['current_image'] = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aEvent['server_id'],
                    'path'       => 'event.url_image',
                    'file'       => $aEvent['image_path'],
                    'suffix'     => '',
                    'return_url' => true
                ]);
            }
            return $aEvent;
        }

        Phpfox_Error::display(_p('You don\'t have permission to {{ action }} this {{ item }}.',
            ['action' => _p('edit__l'), 'item' => _p('event__l')]));
        return false;
    }

    public function getEventSimple($iEventId)
    {
        return $this->database()->select('e.*')
            ->from($this->_sTable, 'e')
            ->where('e.event_id = ' . (int)$iEventId)
            ->execute('getSlaveRow');
    }

    /**
     * @param int $iEvent
     * @param int $iRsvp
     * @param int $iPage
     * @param int $iPageSize
     *
     * @return array
     */
    public function getInvites($iEvent, $iRsvp, $iPage = 0, $iPageSize = 8)
    {
        $aInvites = [];
        $iCnt = $this->database()
            ->select('COUNT(*)')
            ->from(Phpfox::getT('event_invite'), 'ei')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ei.invited_user_id')
            ->where('ei.event_id = ' . (int)$iEvent . ' AND ei.rsvp_id = ' . (int)$iRsvp . ' AND ei.invited_user_id != 0')
            ->execute('getSlaveField');

        if ($iCnt) {
            $aInvites = $this->database()
                ->select('ei.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('event_invite'), 'ei')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = ei.invited_user_id')
                ->where('ei.event_id = ' . (int)$iEvent . ' AND ei.rsvp_id = ' . (int)$iRsvp . ' AND invited_user_id != 0')
                ->limit($iPage, $iPageSize, $iCnt)
                ->order('ei.invite_id DESC')
                ->execute('getSlaveRows');
        }

        return [$iCnt, $aInvites];
    }

    /**
     * @param $iEvent
     * @param $iRsvp
     *
     * @return array|int|string
     */
    public function getTotalRsvp($iEvent, $iRsvp)
    {
        return $this->database()
            ->select('COUNT(*)')
            ->from(Phpfox::getT('event_invite'), 'ei')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ei.invited_user_id')
            ->where('ei.event_id = ' . (int)$iEvent . ' AND ei.rsvp_id = ' . (int)$iRsvp . ' AND ei.invited_user_id != 0')
            ->execute('getSlaveField');
    }

    /**
     * @param $iEvent
     *
     * @return array|int|string
     */
    public function getRsvp($iEvent)
    {
        return $this->database()
            ->select('rsvp_id')
            ->from(Phpfox::getT('event_invite'), 'ei')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ei.invited_user_id')
            ->where('ei.event_id = ' . (int)$iEvent . ' AND ei.invited_user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveField');
    }

    /**
     * @param int $iLimit
     *
     * @return array
     */
    public function getInviteForUser($iLimit = 6)
    {
        if (Phpfox::getParam('core.allow_html')) {
            $sDescriptionQuery = 'et.description_parsed as description';
        } else {
            $sDescriptionQuery = 'et.description';
        }
        $aRows = $this->database()
            ->select(Phpfox::getUserField() . ', e.*, ' . $sDescriptionQuery)
            ->from(Phpfox::getT('event_invite'), 'ei')
            ->join(Phpfox::getT('event'), 'e', 'e.event_id = ei.event_id')
            ->join(Phpfox::getT('event_text'), 'et', 'e.event_id = et.event_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.view_id = 0 AND ei.rsvp_id = 0 AND ei.invited_user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveRows');
        shuffle($aRows);

        $aInvites = [];
        foreach ($aRows as $iKey => $aRow) {
            if ($iKey == $iLimit) {
                break;
            }

            $aRows[$iKey] = $this->getEventTimeForDisplay($aRow);
            $aInvites[] = $aRows[$iKey];
        }

        return $aInvites;
    }

    /**
     * @param int $iUserId
     * @param int $iLimit
     *
     * @return array|int|string
     */
    public function getForProfileBlock($iUserId, $iLimit = 4)
    {
        $aConds = $this->getWhenConds(null, 'm');
        $aEvents = $this->database()->select('m.*')
            ->from($this->_sTable, 'm')
            ->join(Phpfox::getT('event_invite'), 'ei',
                'ei.event_id = m.event_id AND ei.rsvp_id = 1 AND ei.invited_user_id = ' . (int)$iUserId)
            ->where($aConds)
            ->limit($iLimit)
            ->order('m.start_time ASC')
            ->executeRows();

        foreach ($aEvents as $iKey => $aEvent) {
            $aEvents[$iKey]['url'] = Phpfox_Url::instance()->permalink('event', $aEvent['event_id'], $aEvent['title']);
            $aEvents[$iKey]['start_time_stamp'] = Phpfox::getTime('F j, Y',
                $aEvent['start_time']);
            $aEvents[$iKey]['location_clean'] = Phpfox::getLib('parse.output')->split(Phpfox::getLib('parse.output')->clean($aEvent['location']),
                10);
            $aEvents[$iKey] = $this->getEventTimeForDisplay($aEvent);
        }

        return $aEvents;
    }

    /**
     * @param string $sModule
     * @param int    $iItemId
     * @param int    $iLimit
     *
     * @return array|int|string
     */
    public function getForParentBlock($sModule, $iItemId, $iLimit = 5)
    {
        $iTimeDisplay = Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d'),
            Phpfox::getTime('Y'));

        $aEvents = $this->database()->select('m.event_id, m.title, m.image_path, m.server_id, m.start_time, m.location, m.country_iso, m.city, m.module_id, m.item_id, m.user_id, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'm')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
            ->where('m.view_id = 0 AND m.module_id = \'' . $this->database()->escape($sModule) . '\' AND m.item_id = ' . (int)$iItemId . ' AND m.start_time >= \'' . $iTimeDisplay . '\'')
            ->limit($iLimit)
            ->order('m.start_time ASC')
            ->execute('getSlaveRows');

        foreach ($aEvents as $iKey => $aEvent) {
            $aEvents[$iKey]['url'] = Phpfox_Url::instance()->makeUrl('event', ['redirect' => $aEvent['event_id']]);
            $aEvents[$iKey]['start_time_stamp'] = Phpfox::getTime('F j, Y',
                $aEvent['start_time']);
            $aEvents[$iKey]['location_clean'] = Phpfox::getLib('parse.output')->split(Phpfox::getLib('parse.output')->clean($aEvent['location']),
                10);
            $aEvents[$iKey] = $this->getEventTimeForDisplay($aEvent);
        }

        return $aEvents;
    }

    /**
     * @param int $iLimit
     * @param int $iCacheTime
     *
     * @return array|bool
     */
    public function getRandomSponsored($iLimit = 4, $iCacheTime = 4)
    {
        $sCacheId = $this->cache()->set('event_sponsored');
        if (($sEventIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sEventIds = '';
            $aConds = $this->getWhenConds();
            $aConds[] = $this->getConditionsForSettingPageGroup();
            $aEventIds = $this->database()->select('e.event_id')
                ->from($this->_sTable, 'e')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = e.event_id AND s.is_custom = 3')
                ->where('e.view_id = 0 AND e.is_sponsor = 1 AND s.module_id = \'event\' AND s.is_active = 1 ' . implode(' ', $aConds))
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aEventIds as $key => $aId) {
                if ($key != 0) {
                    $sEventIds .= ',' . $aId['event_id'];
                } else {
                    $sEventIds = $aId['event_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sEventIds);
            }

        }
        if (empty($sEventIds)) {
            return [];
        }
        $aEventIds = explode(',', $sEventIds);
        shuffle($aEventIds);
        $aEventIds = array_slice($aEventIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));
        $aEvents = $this->database()->select(Phpfox::getUserField() . ', e.*, e.total_view as total_view_event, s.*')
            ->from($this->_sTable, 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = e.event_id AND s.module_id = \'event\' AND s.is_custom = 3')
            ->where('e.event_id IN (' . implode(',', $aEventIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        if (!isset($aEvents[0]) || empty($aEvents[0])) {
            return [];
        }

        if (Phpfox::isAppActive('Core_BetterAds')) {
            $aEvents = Phpfox::getService('ad')->filterSponsor($aEvents);
        }

        foreach ($aEvents as $key => $aEvent) {
            $aEvents[$key] = $this->getEventTimeForDisplay($aEvent);
            $aEvents[$key]['total_view'] = $aEvent['total_view_event'];
        }
        shuffle($aEvents);

        return $aEvents;
    }

    public function getWhenConds(
        $sWhen = null,
        $sTableAlias = 'e',
        $sWhenField = 'start_time',
        $sWhenEndField = 'end_time'
    )
    {
        $aConds = [];
        $iTimeDisplay = Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d'),
            Phpfox::getTime('Y'));
        if (!$sWhen) {
            $sWhen = Phpfox::getParam('event.event_default_sort_time', 'ongoing');
        }
        switch ($sWhen) {
            case 'today':
                $iEndDay = Phpfox::getLib('date')->mktime(23, 59, 0, Phpfox::getTime('m'), Phpfox::getTime('d'),
                    Phpfox::getTime('Y'));

                $aConds[] = ' AND (' . $sTableAlias . '.' . $sWhenField . ' >= \'' . Phpfox::getLib('date')->convertToGmt($iTimeDisplay) . '\' AND ' . $sTableAlias . '.' . $sWhenField . ' < \'' . Phpfox::getLib('date')->convertToGmt($iEndDay) . '\')';
                break;
            case 'this-week':
                $aConds[] = ' AND ' . $sTableAlias . '.' . $sWhenField . ' >= ' . (int)Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->getWeekStart());
                $aConds[] = ' AND ' . $sTableAlias . '.' . $sWhenField . ' <= ' . (int)Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->getWeekEnd());
                break;
            case 'this-month':
                $aConds[] = ' AND ' . $sTableAlias . '.' . $sWhenField . ' >= \'' . Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->getThisMonth()) . '\'';
                $iLastDayMonth = Phpfox::getLib('date')->mktime(0, 0, 0, date('n'),
                    Phpfox::getLib('date')->lastDayOfMonth(date('n')), date('Y'));
                $aConds[] = ' AND ' . $sTableAlias . '.' . $sWhenField . ' <= \'' . Phpfox::getLib('date')->convertToGmt($iLastDayMonth) . '\'';
                break;
            case 'upcoming':
                $aConds[] = ' AND ' . $sTableAlias . '.' . $sWhenField . ' >= \'' . PHPFOX_TIME . '\'';
                break;
            case 'ongoing':
                $aConds[] = ' AND ' . $sTableAlias . '.' . $sWhenField . ' <= \'' . PHPFOX_TIME . '\'';
                $aConds[] = ' AND ' . $sTableAlias . '.' . $sWhenEndField . ' > \'' . PHPFOX_TIME . '\'';
                break;
            default:

                break;
        }
        return $aConds;
    }

    /**
     * Apply settings show music of pages / groups
     *
     * @param string $sPrefix
     *
     * @return string
     */
    public function getConditionsForSettingPageGroup($sPrefix = 'e')
    {
        $aModules = [];
        // Apply settings show blog of pages / groups
        if (Phpfox::getParam('event.event_display_event_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (Phpfox::getParam('event.event_display_event_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        if (count($aModules)) {
            return ' AND (' . $sPrefix . '.module_id IN (\'' . implode('\',\'',
                    $aModules) . '\') OR ' . $sPrefix . '.module_id = \'event\')';
        } else {
            return ' AND ' . $sPrefix . '.module_id = \'event\'';
        }
    }

    /**
     * @param int   $iItemId
     * @param array $aFriends
     *
     * @return array|bool
     */
    public function isAlreadyInvited($iItemId, $aFriends)
    {
        if ((int)$iItemId === 0) {
            return false;
        }

        if (is_array($aFriends)) {
            if (!count($aFriends)) {
                return false;
            }

            $sIds = [];
            foreach ($aFriends as $aFriend) {
                if (!isset($aFriend['user_id'])) {
                    continue;
                }

                $sIds[] = $aFriend['user_id'];
            }

            $aInvites = $this->database()->select('invite_id, rsvp_id, invited_user_id')
                ->from(Phpfox::getT('event_invite'))
                ->where('event_id = ' . (int)$iItemId . ' AND invited_user_id IN(' . implode(', ', $sIds) . ')')
                ->execute('getSlaveRows');

            $aCache = [];
            foreach ($aInvites as $aInvite) {
                $aCache[$aInvite['invited_user_id']] = ($aInvite['rsvp_id'] > 0 ? _p('responded') : _p('invited'));
            }

            if (count($aCache)) {
                return $aCache;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getSiteStatsForAdmins()
    {
        $iToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

        return [
            'phrase' => _p('events'),
            'value'  => $this->database()
                ->select('COUNT(*)')
                ->from(Phpfox::getT('event'))
                ->where('view_id = 0 AND time_stamp >= ' . $iToday)
                ->execute('getSlaveField')
        ];
    }

    /**
     * @param int $iLimit
     * @param int $iCacheTime
     *
     * @return array
     */
    public function getFeatured($iLimit = 4, $iCacheTime = 5)
    {
        static $aFeatured = null;
        static $iTotal = null;

        if ($aFeatured !== null) {
            return [$iTotal, $aFeatured];
        }

        $aFeatured = [];
        $sCacheId = $this->cache()->set('event_featured');
        if (($sEventIds = $this->cache()->get($sCacheId, $iCacheTime)) === false) {
            $sEventIds = '';
            $aConds = $this->getWhenConds();
            $aConds[] = $this->getConditionsForSettingPageGroup();
            $aEventIds = $this->database()->select('e.event_id')
                ->from($this->_sTable, 'e')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->where('e.is_featured = 1 AND e.view_id = 0 ' . implode(' ', $aConds))
                ->order('rand()')
                ->limit(Phpfox::getParam('core.cache_total'))
                ->execute('getSlaveRows');
            foreach ($aEventIds as $key => $aId) {
                if ($key != 0) {
                    $sEventIds .= ',' . $aId['event_id'];
                } else {
                    $sEventIds = $aId['event_id'];
                }
            }
            if ($iCacheTime) {
                $this->cache()->save($sCacheId, $sEventIds);
            }
        }
        if (empty($sEventIds)) {
            return [0, []];
        }
        $aEventIds = explode(',', $sEventIds);
        shuffle($aEventIds);
        $aEventIds = array_slice($aEventIds, 0, round($iLimit * Phpfox::getParam('core.cache_rate')));

        $aRows = $this->database()->select(Phpfox::getUserField() . ', e.*')
            ->from($this->_sTable, 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.event_id IN (' . implode(',', $aEventIds) . ')')
            ->limit($iLimit)
            ->execute('getSlaveRows');
        if (empty($sEventIds)) {
            return [];
        }

        $iTotal = 0;
        if (is_array($aRows) && count($aRows)) {
            $iTotal = count($aRows);
            shuffle($aRows);
            foreach ($aRows as $iKey => $aRow) {
                $aRow = $this->getEventTimeForDisplay($aRow);
                $aFeatured[] = $aRow;
            }
        }
        return [$iTotal, $aFeatured];
    }

    /**
     * @param int $iLimit
     *
     * @return array
     */
    public function getForRssFeed($iLimit = 20)
    {
        $iTimeDisplay = Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d'), Phpfox::getTime('Y'));
        $aConditions = [];
        $aConditions[] = "e.view_id = 0";
        $aConditions[] = "AND e.start_time >= '" . $iTimeDisplay . "'";

        if ((Phpfox::getParam('event.event_display_event_created_in_page') || Phpfox::getParam('event.event_display_event_created_in_group'))) {
            $aModules = [];
            if (Phpfox::getParam('event.event_display_event_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                $aModules[] = 'groups';
            }
            if (Phpfox::getParam('event.event_display_event_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                $aModules[] = 'pages';
            }
            if (count($aModules)) {
                $aConditions[] = ' AND (e.module_id IN ("' . implode('","', $aModules) . '") OR e.module_id = \'event\')';
            } else {
                $aConditions[] = ' AND e.module_id = \'event\'';
            }
        } else {
            $aConditions[] = ' AND e.item_id = 0';
        }

        if (Phpfox::isAppActive('PHPfox_Groups')) {
            //Don't get events post in close, secret group
            $aNotInclude = $this->database()->select('e.event_id')
                ->from(':event', 'e')
                ->join(':pages', 'p', 'p.page_id = e.item_id AND e.module_id = \'groups\'')
                ->where('p.item_type = 1 AND p.reg_method != 0')->execute('');
            $aConditions[] = ' AND e.event_id NOT IN (' . $aNotInclude . ')';
        }

        (($sPlugin = Phpfox_Plugin::get('event.component_service_event_get_for_rss_feed')) ? eval($sPlugin) : false);

        $aRows = $this->database()->select(Phpfox::getUserField() . ', e.*, et.description_parsed AS description')
            ->from(Phpfox::getT('event'), 'e')
            ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where($aConditions)
            ->limit($iLimit)
            ->order('e.time_stamp DESC')
            ->executeRows();

        foreach ($aRows as $iKey => $aRow) {
            $aRows[$iKey]['link'] = Phpfox::permalink('event', $aRow['event_id'], $aRow['title']);
            $aRows[$iKey]['creator'] = $aRow['full_name'];
        }

        return $aRows;
    }

    /**
     * @param array $aItem
     *
     * @return array
     */
    public function getInfoForAction($aItem)
    {
        if (is_numeric($aItem)) {
            $aItem = ['item_id' => $aItem];
        }
        $aRow = $this->database()->select('e.event_id, e.title, e.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('event'), 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.event_id = ' . (int)$aItem['item_id'])
            ->executeRow();

        $aRow['link'] = Phpfox_Url::instance()->permalink('event', $aRow['event_id'], $aRow['title']);
        return $aRow;
    }

    /**
     * @description: check permission to view an event
     *
     * @param int  $iId
     * @param bool $bReturnItem
     *
     * @return array|bool|int|null|string
     */
    public function canViewItem($iId, $bReturnItem = false)
    {

        if (!Phpfox::getUserParam('event.can_access_event')) {
            return false;
        }

        if (!($aEvent = Phpfox::getService('event')->getEvent($iId, false, $bReturnItem))) {
            Phpfox_Error::set(_p('This {{ item }} cannot be found.', ['item' => _p('event__l')]));
            return false;
        }

        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $aEvent['user_id'])) {
            Phpfox_Error::set(_p('Sorry, this content isn\'t available right now'));
            return false;
        }

        if (Phpfox::isModule('privacy')) {
            if (!Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'],
                $aEvent['privacy'], $aEvent['is_friend'], true)
            ) {
                return false;
            }
        }

        if (isset($aEvent['module_id']) && Phpfox::isModule($aEvent['module_id']) && Phpfox::hasCallback($aEvent['module_id'],
                'checkPermission')
        ) {
            if (!Phpfox::callback($aEvent['module_id'] . '.checkPermission', $aEvent['item_id'],
                'event.view_browse_events')
            ) {
                Phpfox_Error::set(_p('unable_to_view_this_item_due_to_privacy_settings'));
                return false;
            }
        }

        return $bReturnItem ? $aEvent : true;
    }

    public function buildSectionMenu()
    {
        $iMyTotal = $this->getMyTotal();
        $aFilterMenu = [
            _p('all_events')                                                                                                             => '',
            _p('my_events') . (($iMyTotal > 0) ? '<span class="my count-item">' . ($iMyTotal > 99 ? '99+' : $iMyTotal) . '</span>' : '') => 'my'
        ];

        if (Phpfox::isModule('friend') && !Phpfox::getParam('core.friends_only_community')) {
            $aFilterMenu[_p('friends_events')] = 'friend';
        }

        if (Phpfox::getUserParam('event.can_approve_events')) {
            $iPendingTotal = Phpfox::getService('event')->getPendingTotal();

            if ($iPendingTotal) {
                $aFilterMenu[_p('pending_events') . '<span id="pending-event" class="pending count-item">' . ($iPendingTotal > 99 ? '99+' : $iPendingTotal) . '</span>'] = 'pending';
            }
        }
        $iAttendingTotal = $iMayAttendingTotal = $iInviteTotal = 0;
        if (Phpfox::getUserId()) {
            $iAttendingTotal = $this->getAttendingTotal(1);
            $iMayAttendingTotal = $this->getAttendingTotal(2);
            $iInviteTotal = $this->getAttendingTotal(0);
        }
        $aFilterMenu[_p('events_i_m_attending') . (($iAttendingTotal > 0) ? '<span class="count-item">' . ($iAttendingTotal > 99 ? '99+' : $iAttendingTotal) . '</span>' : '')] = 'attending';
        $aFilterMenu[_p('events_i_may_attend') . (($iMayAttendingTotal > 0) ? '<span class="count-item">' . ($iMayAttendingTotal > 99 ? '99+' : $iMayAttendingTotal) . '</span>' : '')] = 'may-attend';
        $aFilterMenu[_p('invited_events') . (($iInviteTotal > 0) ? '<span class="invites count-item">' . ($iInviteTotal > 99 ? '99+' : $iInviteTotal) . '</span>' : '')] = 'invites';

        Phpfox::getLib('template')->buildSectionMenu('event', $aFilterMenu);
    }

    /**
     * @return int
     */
    public function getMyTotal()
    {
        $sWhere = 'user_id = ' . Phpfox::getUserId();
        $aModules = ['user'];
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (!Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        $sWhere .= ' AND (module_id NOT IN ("' . implode('","', $aModules) . '") OR module_id = \'event\')';

        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    /**
     * @return array|int|string
     */
    public function getPendingTotal()
    {
        return $this->database()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('view_id = 1')
            ->execute('getSlaveField');
    }

    /**
     * @param $iRsvpId
     *
     * @return array|int|string
     */
    public function getAttendingTotal($iRsvpId)
    {
        $sWhere = 'e.view_id = 0 AND ei.rsvp_id = ' . $iRsvpId . ' AND ei.invited_user_id =' . (int)Phpfox::getUserId();
        $aModules = ['user'];
        if (!Phpfox::isAppActive('PHPfox_Groups')) {
            $aModules[] = 'groups';
        }
        if (!Phpfox::isAppActive('Core_Pages')) {
            $aModules[] = 'pages';
        }
        $sWhere .= ' AND (module_id NOT IN ("' . implode('","', $aModules) . '") OR module_id = \'event\')';

        return db()->select('COUNT(*)')
            ->from($this->_sTable, 'e')
            ->join(':event_invite', 'ei', 'ei.event_id = e.event_id')
            ->where($sWhere)
            ->execute('getSlaveField');
    }

    public function getPendingSponsorItems()
    {
        $sCacheId = $this->cache()->set('event_pending_sponsor');
        if (false === ($aItems = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('m.event_id')
                ->from(Phpfox::getT('event'), 'm')
                ->join(Phpfox::getT('better_ads_sponsor'), 's', 's.item_id = m.event_id')
                ->where('m.is_sponsor = 0 AND s.is_custom = 2 AND s.module_id = "event"')
                ->execute('getSlaveRows');
            $aItems = array_column($aRows, 'event_id');
            $this->cache()->save($sCacheId, $aItems);
        }
        return $aItems;
    }

    public function canPurchaseSponsorItem($iItemId)
    {
        $aIds = $this->getPendingSponsorItems();
        return in_array($iItemId, $aIds) ? false : true;
    }


    /**
     * @param $aRow
     */
    public function getPermissions(&$aRow)
    {
        $aRow['canEdit'] = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('event.can_edit_own_event')) || Phpfox::getUserParam('event.can_edit_other_event'));
        $aRow['canInvite'] = $aRow['canEdit'] && $aRow['view_id'] == 0;
        $aRow['canDelete'] = $this->canDelete($aRow);
        $aRow['canMassEmail'] = $aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('event.can_mass_mail_own_members');
        $aRow['iSponsorInFeedId'] = Phpfox::isModule('feed') && (Phpfox::getService('feed')->canSponsoredInFeed('event', $aRow['event_id']) === true);

        $aRow['canSponsor'] = $aRow['canSponsorInFeed'] = $aRow['canPurchaseSponsor'] = false;
        if (Phpfox::isAppActive('Core_BetterAds') && Phpfox::getUserBy('profile_page_id') == 0) {
            $aRow['canSponsor'] = (Phpfox::getUserParam('event.can_sponsor_event') && (int)$aRow['view_id'] == 0);
            $aRow['canSponsorInFeed'] = (int)$aRow['view_id'] == 0 && (Phpfox::isModule('feed') && (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('feed.can_purchase_sponsor')) || Phpfox::getUserParam('feed.can_sponsor_feed')) && Phpfox::getService('feed')->canSponsoredInFeed('event', $aRow['event_id']));
            $bCanPurchaseSponsor = $this->canPurchaseSponsorItem($aRow['event_id']);
            $aRow['canPurchaseSponsor'] = ((int)$aRow['view_id'] == 0 && $aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('event.can_purchase_sponsor') && $bCanPurchaseSponsor);
        }

        $aRow['canApprove'] = (Phpfox::getUserParam('event.can_approve_events') && $aRow['view_id'] == 1);
        $aRow['canFeature'] = (Phpfox::getUserParam('event.can_feature_events') && $aRow['view_id'] == 0);
        $aRow['hasPermission'] = ($aRow['canEdit'] || $aRow['canDelete'] || $aRow['canSponsor'] || $aRow['canApprove'] || $aRow['canFeature'] || $aRow['canPurchaseSponsor'] || $aRow['canSponsorInFeed']);
    }

    private function canDelete($aRow)
    {
        $bCanDelete = (($aRow['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('event.can_delete_own_event')) || Phpfox::getUserParam('event.can_delete_other_event'));
        if (!$bCanDelete && Phpfox::isModule($aRow['module_id'])) {
            if ($aRow['module_id'] == 'pages' && Phpfox::getService('pages')->isAdmin($aRow['item_id'])) {
                $bCanDelete = true; // is owner of page
            } else if ($aRow['module_id'] == 'groups' && Phpfox::getService('groups')->isAdmin($aRow['item_id'])) {
                $bCanDelete = true; // is owner of group
            }
        }
        return $bCanDelete;
    }

    /**
     * Check if current user is admin of event's parent item
     *
     * @param $iEventId
     *
     * @return bool|mixed
     */
    public function isAdminOfParentItem($iEventId)
    {
        $aEvent = Phpfox::getService('event')->getEvent($iEventId);
        if (!$aEvent) {
            return false;
        }
        if ($aEvent['module_id'] && Phpfox::hasCallback($aEvent['module_id'], 'isAdmin')) {
            return Phpfox::callback($aEvent['module_id'] . '.isAdmin', $aEvent['item_id']);
        }
        return false;
    }

    public function getSuggestionEvents($aEvent, $iLimit = 4)
    {
        static $aSuggestion;

        if ($aSuggestion !== null) {
            return $aSuggestion;
        }


        $aSuggestion = [];
        $aConds = $this->getWhenConds();
        $aConds[] = $this->getConditionsForSettingPageGroup();
        $aRows = $this->database()->select(Phpfox::getUserField() . ', e.*')
            ->from($this->_sTable, 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->join(Phpfox::getT('event_category_data'), 'ecd', 'ecd.event_id = e.event_id')
            ->where('e.privacy = 0 AND e.view_id = 0 ' . implode(' ',
                    $aConds) . ' AND ecd.category_id IN (' . $aEvent['categories_id'] . ') AND e.event_id <> ' . $aEvent['event_id'])
            ->group('e.event_id')
            ->limit($iLimit)
            ->execute('getSlaveRows');

        if (is_array($aRows) && count($aRows)) {
            shuffle($aRows);
            foreach ($aRows as $iKey => $aRow) {
                $aRow = $this->getEventTimeForDisplay($aRow);
                $aSuggestion[] = $aRow;
            }
        }
        return $aSuggestion;
    }

    public function getEventTimeForDisplay($aEvent, $bEndTime = false)
    {
        if (isset($aEvent['start_time']) && (!$bEndTime || (isset($aEvent['end_time'])))) {
            $aEvent['start_time_month'] = Phpfox::getTime('F', $aEvent['start_time']);
            $aEvent['start_time_short_month'] = Phpfox::getTime('M', $aEvent['start_time'], true, true);
            $aEvent['start_time_short_day'] = Phpfox::getTime('j', $aEvent['start_time']);
            $aEvent['start_time_phrase'] = Phpfox::getTime('l, F j', $aEvent['start_time']);
            $aEvent['start_time_micro'] = Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aEvent['start_time'], true, true);
            $aEvent['start_time_phrase_stamp'] = Phpfox::getTime(Phpfox::getParam('event.event_time_format'), $aEvent['start_time']);

            if ($bEndTime) {
                $aEvent['end_time_micro'] = Phpfox::getTime(Phpfox::getParam('core.global_update_time'), $aEvent['end_time'], true, true);
                $aEvent['end_time_phrase_stamp'] = Phpfox::getTime(Phpfox::getParam('event.event_time_format'), $aEvent['end_time']);

            }
        }
        return $aEvent;
    }

    /**
     * @return array
     */
    public function getUploadParams()
    {
        $iMaxFileSize = Phpfox::getUserParam('event.max_upload_size_event');
        $iMaxFileSize = $iMaxFileSize > 0 ? $iMaxFileSize / 1024 : 0;
        $iMaxFileSize = Phpfox::getLib('file')->getLimit($iMaxFileSize);
        $sMaxFileDesc = $iMaxFileSize === 0 ? '' : (_p('the_file_size_limit_is_file_size_if_your_upload_does_not_work_try_uploading_a_smaller_picture',
                ['file_size' => Phpfox_File::filesize($iMaxFileSize * 1048576)]) . '<br/><br/>');
        $sMaxFileDesc .= _p('event_photo_recommended');
        return [
            'max_size'             => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'type_list'            => ['jpg', 'jpeg', 'gif', 'png'],
            'upload_dir'           => Phpfox::getParam('event.dir_image'),
            'upload_path'          => Phpfox::getParam('event.url_image'),
            'thumbnail_sizes'      => Phpfox::getParam('event.thumbnail_sizes'),
            'label'                => _p('banner'),
            'max_size_description' => $sMaxFileDesc
        ];
    }

    public function getAllDataEventById($event_id)
    {
        return db()->select('e.*, txt.description, txt.description_parsed, GROUP_CONCAT(cdt.category_id) as categories')
            ->from(Phpfox::getT('event'), 'e')
            ->leftJoin(Phpfox::getT('event_text'), 'txt', 'txt.event_id = e.event_id')
            ->leftJoin(Phpfox::getT('event_category_data'), 'cdt', 'cdt.event_id = e.event_id')
            ->where('e.event_id = ' . (int)$event_id)
            ->group('e.event_id')
            ->execute('getSlaveRow');
    }

    public function getSameDayInNextMonth($day, $month, $year)
    {
        //  if date is invalid, date will be become first date in next month
        $iTheoryYear = $year;
        $iTheoryDay = $iTrueDay = $day;
        if ($month == 12) {
            $iTheoryMonth = 1;
            $iTheoryYear = $year + 1;
        } else {
            $iTheoryMonth = $month + 1;
        }

        $iTrueMonth = $iTheoryMonth;
        if(!checkdate($iTheoryMonth, $iTheoryDay, $iTheoryYear) == true) {
            $iTrueDay = 1;
            $iTrueMonth = $iTheoryMonth + 1;
        }

        return [
            'theory_date' => [
                'day' => $iTheoryDay,
                'month' => $iTheoryMonth,
                'year' => $iTheoryYear
            ],
            'true_date' => [
                'day' => $iTrueDay,
                'month' => $iTrueMonth,
                'year' => $iTheoryYear
            ]
        ];
    }

    public function getBrotherEventByEventId($event_id, $org_event_id, $aConds = [])
    {
        $sWhere = 'e.event_id != ' . (int)$event_id;
        $sWhere .= ' AND e.org_event_id = ' . (int)$org_event_id;
        if (count($aConds) > 0) {
            $sCond = implode('  ', $aConds);
            $sWhere .= ' ' . $sCond;
        }

        return db()->select('e.event_id')->from(Phpfox::getT('event'), 'e')->where($sWhere)->execute('getSlaveRows');
    }

    /**
     * Check if 2 timestamps in the same date (base on user's timezone)
     *
     * @param $iFirstTimestamp
     * @param $iSecondTimestamp
     *
     * @return bool
     */
    private function _isTheSameDate($iFirstTimestamp, $iSecondTimestamp)
    {
        $aTimezones = Phpfox::getService('core')->getTimeZones();
        $userTimezone = Phpfox::getUserBy('time_zone');
        $userTimezone = $userTimezone ? $userTimezone : Phpfox::getParam('core.default_time_zone_offset');
        $userTimezone = $userTimezone ? $userTimezone : 'z0';
        $timezone = $aTimezones[$userTimezone];

        $firstDate = new \DateTime();
        $firstDate->setTimestamp($iFirstTimestamp);
        $firstDate->setTimezone(new \DateTimeZone($timezone));

        $secondDate = clone $firstDate;
        $secondDate->setTimestamp($iSecondTimestamp);

        return $firstDate->format('Y-m-d') === $secondDate->format('Y-m-d');
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
        if ($sPlugin = Phpfox_Plugin::get('event.service_event__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}
