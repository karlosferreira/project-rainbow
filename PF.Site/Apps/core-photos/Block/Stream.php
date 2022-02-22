<?php

namespace Apps\Core_Photos\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

class Stream extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $aForms = $this->getParam('aForms');
        $aCallback = $this->getParam('aCallback');
        $aForms['hasAction'] = false;
        if ($aForms['user_id'] == Phpfox::getUserId()
            || (Phpfox::getUserParam('photo.can_download_user_photos') && $aForms['allow_download'])
            || Phpfox::getUserParam('photo.can_edit_other_photo')
            || (isset($aCallback) && ($aCallback['module_id'] == 'pages' || $aCallback['module_id'] == 'groups') && $aForms['canSetCover'])
        ) {
            $aForms['hasAction'] = true;
        }

        $aForms['noRotation'] = !Phpfox::getService('photo')->canRotate($aForms['extension']);

        $this->template()->assign([
                'aForms' => $aForms
            ]
        );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('photo.component_block_stream_clean')) ? eval($sPlugin) : false);

        $this->template()->clean([
                'aStreams'
            ]
        );
    }
}