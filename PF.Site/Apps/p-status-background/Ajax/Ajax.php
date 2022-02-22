<?php

namespace Apps\P_StatusBg\Ajax;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Ajax;

class Ajax extends Phpfox_Ajax
{
    public function getTotalActiveCollection()
    {
        $iId = $this->get('id', 0);
        $iTotal = Phpfox::getService('pstatusbg')->countTotalActiveCollection($iId);
        echo json_encode([
            'total_active' => $iTotal,
        ]);
        exit;
    }

    public function refreshBackgrounds()
    {
        $iId = $this->get('id');
        if (!$iId) {
            return false;
        }
        $aBackgrounds = Phpfox::getService('pstatusbg')->getImagesByCollection($iId);
        $this->template()->assign([
            'aBackgrounds' => $aBackgrounds,
        ])->getTemplate('pstatusbg.block.admin.list-backgrounds');
        $this->call('$(\'#js_list_backgrounds\').html(\'' . $this->getContent() . '\');');
        $this->call('$Core.loadInit();');
        return true;
    }

    public function toggleActiveCollection()
    {
        $iId = $this->get('id');
        $iActive = $this->get('active');
        $bResult = Phpfox::getService('pstatusbg.process')->toggleActiveCollection($iId, $iActive);
        if (!$bResult) {
            $this->call('setTimeout(function(){window.location.reload();},2000);');
        }
    }

    public function updateImagesOrdering()
    {
        $aVals = $this->get('val');
        $iSetId = $this->get('collection_id');
        Phpfox::getService('pstatusbg.process')->updateImagesOrdering(array('values' => $aVals['ordering']), $iSetId);
    }

    public function deleteBackground()
    {
        $iId = $this->get('id');
        if (!$iId) {
            return false;
        }
        if (Phpfox::getService('pstatusbg.process')->deleteBackground($iId)) {
            Phpfox::addMessage(_p('image_deleted_successfully'));
            $this->call('window.location.reload();');
        }
    }

    public function loadCollectionsList()
    {
        Phpfox::getBlock('pstatusbg.collections-list');
        $this->call('PStatusBg.appendCollectionList(\'' . $this->getContent() . '\')');
    }

    public function editStatusBackground()
    {
        $iFeedId = $this->get('feed_id');
        $iDisabled = (int)$this->get('is_disabled');
        $aCallback = [];
        if ($sModule = $this->get('module')) {
            $aCallback = [
                'module' => $sModule,
                'table_prefix' => $sModule . '_',
                'item_id' => $this->get('item_id'),
            ];
        }
        if ($this->get('url_ajax') == 'feed.updatePost') {
            $aFeed = db()->select('type_id, user_id, item_id')->from(':feed')->where(['feed_id' => $iFeedId])->executeRow();
        } else {
            $aFeed = Phpfox::getService('feed')->getUserStatusFeed($aCallback, $iFeedId, false);
        }
        if ($aFeed) {
            Phpfox::getService('pstatusbg.process')->editUserStatusCheck($aFeed['item_id'], $aFeed['type_id'], $aFeed['user_id'], !$iDisabled);
        }
    }
}