<?php
namespace Apps\Core_Subscriptions\Service\Compare;

use Phpfox;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Compare extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_compare');
    }

    public function getFeature($iCompareId)
    {
        $aRow = db()->select('*')
            ->from(Phpfox::getT('subscribe_compare'))
            ->where('compare_id = '.$iCompareId)
            ->execute('getSlaveRow');
        return $aRow;
    }
}
