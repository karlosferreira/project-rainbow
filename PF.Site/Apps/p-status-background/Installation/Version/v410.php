<?php

namespace Apps\P_StatusBg\Installation\Version;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v410
 * @package Apps\P_StatusBg\Installation\Version
 */
class v410
{

    public function __construct()
    {
    }

    public function process()
    {
        $iTotalCollection = db()->select('COUNT(*)')
            ->from(':pstatusbg_collections')
            ->execute('getField');
        if (!$iTotalCollection) { // ONLY run for first install
            // Check has 3rd party and import old data
            if (db()->tableExists(Phpfox::getT('yncstatusbg_collections')) && db()->tableExists(Phpfox::getT('yncstatusbg_backgrounds'))) {
                // migrate collections
                $tableNameFrom = Phpfox::getT('yncstatusbg_collections');
                $tableNameTo = Phpfox::getT('pstatusbg_collections');
                $sql = "INSERT INTO `" . $tableNameTo . "` (`collection_id`, `title`, `is_active`, `is_default`, `is_deleted`, `main_image_id`, `time_stamp`, `view_id`, `total_background`) SELECT `collection_id`, `title`, `is_active`, `is_default`, `is_deleted`, `main_image_id`, `time_stamp`, `view_id`, `total_background` FROM `" . $tableNameFrom . "`;";
                db()->query($sql);

                // migrate backgrounds
                $tableNameFrom = Phpfox::getT('yncstatusbg_backgrounds');
                $tableNameTo = Phpfox::getT('pstatusbg_backgrounds');
                $sql = "INSERT INTO `" . $tableNameTo . "` (`background_id`, `collection_id`, `image_path`, `server_id`, `ordering`, `is_deleted`, `time_stamp`, `view_id`) SELECT `background_id`, `collection_id`, `image_path`, `server_id`, `ordering`, `is_deleted`, `time_stamp`, `view_id` FROM `" . $tableNameFrom . "`;";
                db()->query($sql);

                // migrate status background
                $tableNameFrom = Phpfox::getT('yncstatusbg_status_background');
                $tableNameTo = Phpfox::getT('pstatusbg_status_background');
                $sql = "INSERT INTO `" . $tableNameTo . "` (`item_id`, `user_id`, `type_id`, `module_id`, `background_id`, `is_active`, `time_stamp`) SELECT `item_id`, `user_id`, `type_id`, `module_id`, `background_id`, `is_active`, `time_stamp` FROM `" . $tableNameFrom . "` ORDER BY `time_stamp` ASC;";
                db()->query($sql);

                // disable 3rd party apps
                db()->update(Phpfox::getT('apps'), ['is_active' => 0], 'apps_id="YNC_StatusBg"');
                db()->update(Phpfox::getT('module'), ['is_active' => 0], 'module_id="yncstatusbg"');
                // clear cache
                Phpfox::getLib('cache')->remove();
                Phpfox::getLib('template.cache')->remove();
                Phpfox::getLib('cache')->removeStatic();
            } else {
                //Insert default collection
                $aInsert = [
                    'title' => 'default_status_theme',
                    'view_id' => 1,
                    'is_default' => 1,
                    'is_active' => 1,
                    'is_deleted' => 0,
                    'time_stamp' => PHPFOX_TIME,
                    'total_background' => 30
                ];
                $iId = db()->insert(':pstatusbg_collections', $aInsert);
                if ($iId) {
                    for ($i = 1; $i <= 30; $i++) {
                        $aData = [
                            'collection_id' => $iId,
                            'image_path' => 'bg' . ($i < 10 ? '0' . $i : $i) . '-min.png',
                            'server_id' => 0,
                            'ordering' => $i,
                            'time_stamp' => PHPFOX_TIME,
                            'view_id' => 1
                        ];
                        $iBgId = db()->insert(':pstatusbg_backgrounds', $aData);
                        if ($iBgId && $i == 1) {
                            db()->update(':pstatusbg_collections', ['main_image_id' => $iBgId], 'collection_id =' . $iId);
                        }
                    }
                }
            }
        }
    }
}
