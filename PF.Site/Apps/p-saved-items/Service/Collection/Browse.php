<?php

namespace Apps\P_SavedItems\Service\Collection;

use Phpfox;
use Phpfox_Service;

/**
 * Class Browse
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Service\Collection
 */
class Browse extends Phpfox_Service
{

    public function query()
    {
        if ($this->request()->get('saved_id')) {
            db()->join(Phpfox::getT('saved_collection_data'), 'scd', 'scd.collection_id = collection.collection_id');
        }
    }

    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        db()->leftJoin(Phpfox::getT('saved_collection_friend'), 'cf', 'collection.collection_id = cf.collection_id')
            ->group('collection_id');
    }

    public function processRows(&$collections)
    {
        if (!empty($collections)) {
            foreach ($collections as $key => $collection) {
                Phpfox::getService('saveditems.collection')->getPermissions($collections[$key]);
            }
        }
    }
}