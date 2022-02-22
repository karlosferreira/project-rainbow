<?php

namespace Apps\P_Reaction\Installation\Version;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v410
 * @package Apps\P_Reaction\Installation\Version
 */
class v410
{
    private $_aDefaultReactions;
    private $_aDefaultColors;

    public function __construct()
    {
        $this->_aDefaultReactions = ['like__u', 'love__u', 'haha__u', 'wow__u', 'sad__u', 'angry__u'];
        $this->_aDefaultColors = ['009fe2', 'ff314c', 'ffc84d', 'ffc84d', 'ffc84d', 'e95921'];
    }

    public function process()
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(':preaction_reactions')
            ->execute('getField');
        if (!$iCnt) { // ONLY run for first install
            // Check has 3rd party and import old data
            if (db()->tableExists(Phpfox::getT('yncreaction_reactions'))) {
                // migrate reactions
                $tableNameFrom = Phpfox::getT('yncreaction_reactions');
                $tableNameTo = Phpfox::getT('preaction_reactions');
                $sql = "INSERT INTO `" . $tableNameTo . "` (`id`, `title`, `is_active`, `is_deleted`, `icon_path`, `color`, `server_id`, `view_id`, `ordering`) SELECT `id`, `title`, `is_active`, `is_deleted`, `icon_path`, `color`, `server_id`, `view_id`, `ordering` FROM `" . $tableNameFrom . "`;";
                db()->query($sql);
                // disable 3rd party apps
                db()->update(Phpfox::getT('apps'), ['is_active' => 0], 'apps_id="YNC_Reaction"');
                db()->update(Phpfox::getT('module'), ['is_active' => 0], 'module_id="yncreaction"');
                // clear cache of app
                Phpfox::getLib('cache')->remove();
                Phpfox::getLib('template.cache')->remove();
                Phpfox::getLib('cache')->removeStatic();
            } else {
                //Insert default reactions
                $i = 1;
                foreach ($this->_aDefaultReactions as $iKey => $sReaction) {
                    //view_id | 0: reaction add by admin | 1: reaction default, can delete, de-active | 2: reaction default, cannot delete, de-active
                    $aInsert = [
                        'id' => $i,
                        'title' => $sReaction,
                        'is_active' => 1,
                        'is_deleted' => 0,
                        'view_id' => 1,
                        'icon_path' => str_replace('__u', '', $sReaction) . '.svg',
                        'server_id' => 0,
                        'ordering' => $i,
                        'color' => $this->_aDefaultColors[$iKey]
                    ];
                    $i++;
                    //Like is default, cannot delete, de-active
                    if (preg_match('/like/', $sReaction)) {
                        $aInsert['view_id'] = 2;
                    }
                    db()->insert(':preaction_reactions', $aInsert);
                }
            }
        }
        if (!db()->isField(':like', 'react_id')) { // ONLY run for first install
            db()->addField([
                'table' => Phpfox::getT('like'),
                'field' => 'react_id',
                'type' => 'INT:10',
                'null' => true,
                'default' => '1', // 1 is id of Like action
            ]);

            // Check has 3rd party and import old data
            if (db()->isField(':like', 'ync_react_id')) {
                $sql = "UPDATE `" . Phpfox::getT('like') . "` SET `react_id` = `ync_react_id`";
                db()->query($sql);
            }
        }
    }
}
