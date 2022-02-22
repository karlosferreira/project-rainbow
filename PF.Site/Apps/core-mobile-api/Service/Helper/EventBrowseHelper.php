<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service\Helper;

use Apps\Core_Events\Service\Browse as Browse;
use Phpfox;

class EventBrowseHelper extends Browse
{
    /**
     * @var null|array
     */
    private $_sCategory = null;

    /**
     * @var null|int
     */
    private $_iAttending = null;

    /**
     * @var bool|array
     */
    private $_aCallback = false;

    /**
     * @var bool
     */
    private $_bFull = false;

    private $_aCondition = [];
    /**
     * @return void
     */
    public function query()
    {
        if ($this->_iAttending !== null) {
            $this->database()->select('ei.rsvp_id, ')->where('')->group('m.event_id');
        }

        if (empty($this->_aCallback) && !in_array($this->request()->get('view'), ['my', 'pending'])) {
            if ($this->request()->get('view') == 'friend') {
                db()->join(Phpfox::getT('friend'), 'f', 'f.is_page = 0 AND f.user_id = m.user_id AND f.friend_user_id = ' . Phpfox::getUserId());
            }

            if (!empty($this->_sCategory)) {
                db()->join(Phpfox::getT('event_category_data'), 'mcd', 'mcd.event_id = m.event_id AND mcd.category_id =' . $this->_sCategory);
            }

            if (is_array($this->_aCondition) && count($this->_aCondition)) {
                $sConditions = implode(' ', $this->_aCondition);
                $sConditions = preg_replace('/^((\s)*AND)/', '', $sConditions);
                $sConditions = str_replace('%PRIVACY%', '5', $sConditions);
            } else {
                $sConditions = 'm.privacy = 5 AND m.view_id = 0';
            }

            db()->select('m.*')
                ->from(Phpfox::getT('event'), 'm')
                ->join(Phpfox::getT('event_invite'), 'ei',
                    'ei.event_id = m.event_id' . (!is_null($this->_iAttending) ? (' AND ei.rsvp_id = ' . (int)$this->_iAttending) : '') . ' AND ei.invited_user_id = ' . Phpfox::getUserId())
                ->where($sConditions)
                ->union();
        }

        if (!in_array($this->request()->get('view'), ['my', 'pending'])) {
            $this->database()->unionFrom('m');
        }
    }

    /**
     * @param string $sCategory
     *
     * @return $this
     */
    public function category($sCategory)
    {
        parent::category($sCategory);
        $this->_sCategory = $sCategory;
        return $this;
    }

    /**
     * @param int $iAttending
     *
     * @return $this
     */
    public function attending($iAttending)
    {
        parent::attending($iAttending);
        $this->_iAttending = $iAttending;
        return $this;
    }

    /**
     * @param array $aCallback
     *
     * @return $this
     */
    public function callback($aCallback)
    {
        parent::callback($aCallback);
        $this->_aCallback = $aCallback;
        return $this;
    }

    /**
     * @param bool $bFull
     *
     * @return $this
     */
    public function full($bFull)
    {
        parent::full($bFull);
        $this->_bFull = $bFull;
        return $this;
    }

    public function conditions($aConditions)
    {
        $this->_aCondition = $aConditions;
        return $this;
    }
}