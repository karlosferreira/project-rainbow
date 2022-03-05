<?php

namespace Apps\Core_Photos\Installation\Version;

use Phpfox;

/**
 * Class v465
 * @package Apps\Core_Photos\Installation\Version
 */
class v465
{
    public function process()
    {
        $this->addMoreFieldsForPhotoAlbum();

        //Removed setting "enabled_watermark_on_photos"
        db()->delete(':setting', ['var_name' => 'enabled_watermark_on_photos', 'module_id' => 'photo']);
    }

    private function addMoreFieldsForPhotoAlbum()
    {
        if (db()->tableExists(Phpfox::getT('photo_album'))) {
            if (!db()->isField(Phpfox::getT('photo_album'), 'is_featured')) {
                db()->addField([
                    'table'   => Phpfox::getT('photo_album'),
                    'field'   => 'is_featured',
                    'type'    => 'TINT:1',
                    'null'    => false,
                    'default' => 0
                ]);
            }

            if (!db()->isField(Phpfox::getT('photo_album'), 'is_sponsor')) {
                db()->addField([
                    'table'   => Phpfox::getT('photo_album'),
                    'field'   => 'is_sponsor',
                    'type'    => 'TINT:1',
                    'null'    => false,
                    'default' => 0
                ]);
            }
        }
    }
}