<?php

namespace Apps\PHPfox_Groups\Installation\Version;

use Phpfox;

class v474
{
    public function process()
    {
        // update like type id from groups_photo/groups_cover_photo, to photo and remove duplicated like
        db()->update(':like', ['type_id' => 'photo'], "`type_id` IN ('groups_photo', 'groups_cover_photo')");
        db()->update(':like_cache', ['type_id' => 'photo'], "`type_id` IN ('groups_photo', 'groups_cover_photo')");

        // remove duplicated on like table
        $tableName = Phpfox::getT('like');
        $query = "DELETE l1 FROM `" . $tableName . "` l1 INNER JOIN `" . $tableName . "` l2 ON l1.type_id = l2.type_id AND l1.item_id = l2.item_id AND l1.user_id = l2.user_id AND l1.feed_table = l2.feed_table WHERE l1.like_id < l2.like_id;";
        db()->query($query);

        // remove duplicated on like cache table
        $tableName = Phpfox::getT('like_cache');
        $query = "DELETE l1 FROM `" . $tableName . "` l1 INNER JOIN `" . $tableName . "` l2 ON l1.type_id = l2.type_id AND l1.item_id = l2.item_id AND l1.user_id = l2.user_id WHERE l1.cache_id < l2.cache_id;";
        db()->query($query);

    }
}
