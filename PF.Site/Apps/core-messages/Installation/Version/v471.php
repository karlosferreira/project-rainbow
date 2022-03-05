<?php
namespace Apps\Core_Messages\Installation\Version;
use Phpfox;

class v471
{
    public function process()
    {
        $tableName = Phpfox::getT('mail_thread_folder');
        if(db()->tableExists($tableName)) {
            if(db()->isIndex($tableName, 'name')) {
                db()->dropIndex($tableName, 'name');
                db()->addIndex($tableName, '`name`(128),`user_id`', 'name_128_user_id');
            }
            if(db()->isIndex($tableName, 'folder_id')) {
                db()->dropIndex($tableName, 'folder_id');
                db()->addIndex($tableName, '`name`(128),`folder_id`', 'name_128_folder_id');
            }
        }
    }
}