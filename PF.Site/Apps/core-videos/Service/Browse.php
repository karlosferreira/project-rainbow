<?php

namespace Apps\PHPfox_Videos\Service;

use Phpfox;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Browse extends Phpfox_Service
{
    private $isApi;
    private $isCategorySearch;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('video');
    }

    public function query()
    {

    }

    public function setIsCategorySearch($value = true)
    {
        $this->isCategorySearch = $value;
    }

    public function setIsApi($value = true)
    {
        $this->isApi = $value;
    }

    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Phpfox::getService('friend')->queryJoin($bNoQueryFriend)) {
            db()->join(Phpfox::getT('friend'), 'friends',
                'friends.user_id = video.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }

        if (!empty($this->isCategorySearch) || ($this->request()->get((defined('PHPFOX_IS_USER_PROFILE') ? 'req3' : 'req2')) == 'category' || $this->request()->get('category',
                null))) {
            db()->select('vc.category_id, vc.name, ')
                ->leftJoin(Phpfox::getT('video_category_data'), 'vcd', 'vcd.video_id = video.video_id')
                ->leftJoin(Phpfox::getT('video_category'), 'vc',
                    'vc.category_id = vcd.category_id AND vc.is_active = 1')
                ->group('video.video_id');
        }

        if (!empty($this->isApi)) {
            db()->select('vem.video_url, vem.embed_code, ')
                ->leftJoin(':video_embed', 'vem', 'vem.video_id = video.video_id');
        }

        if(\Phpfox_Search::instance()->isSearch()) {
            db()->join(Phpfox::getT('video_text'),'video_text', 'video_text.video_id = video.video_id');
        }
    }

    public function processRows(&$aRows, $iSize = 500)
    {
        foreach ($aRows as $iKey => $aRow) {
            $aRow['link'] = Phpfox::permalink('video.play', $aRow['video_id'], $aRow['title']);
            Phpfox::getService('v.video')->convertImagePath($aRow, $iSize);
            Phpfox::getService('v.video')->getPermissions($aRow);
            if ($aRow['duration']) {
                $aRow['duration'] = Phpfox::getService('v.video')->getDuration($aRow['duration']);
            }
            $aRows[$iKey] = $aRow;
        }
    }
}
