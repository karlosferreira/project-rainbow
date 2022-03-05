<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Music\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class FrameController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (!Phpfox::isUser() || !Phpfox::getService('music')->canUploadNewSong(Phpfox::getUserId(), false)) {
            echo json_encode([
                'error' => _p('you_have_reached_your_limit_you_are_currently_unable_to_create_new_music_song')
            ]);
            exit;
        }
        $aVals = $this->request()->getArray('val');
        if (empty($_FILES['file'])) {
            echo json_encode([
                'error' => _p('cannot_find_the_uploaded_file_please_try_again'),
                'file_name' => _p('Unknown'),
            ]);
        }
        $aParams = Phpfox::getService('music')->getUploadParams();
        $aParams['user_id'] = Phpfox::getUserId();
        $aParams['type'] = 'music';
        $aLoadFile = Phpfox::getService('user.file')->load('file', $aParams);

        if (!$aLoadFile) {
            echo json_encode([
                'error' => _p('cannot_find_the_uploaded_file_please_try_again')
            ]);
            exit;
        }

        if (!empty($aLoadFile['error'])) {
            echo json_encode([
                'error' => $aLoadFile['error']
            ]);
            exit;
        }

        $aFile = Phpfox::getService('user.file')->upload('file', $aParams, true);
        if (empty($aFile) || !empty($aFile['error'])) {
            if (empty($aFile)) {
                echo json_encode([
                    'error' => _p('cannot_find_the_uploaded_file_please_try_again')
                ]);
                exit;
            }

            if (!empty($aFile['error'])) {
                echo json_encode([
                    'error' => $aFile['error']
                ]);
                exit;
            }
        }

        $aVals = array_merge($aVals, $aFile);
        $aVals['file_name'] = $aLoadFile['name'];
        if ($iId = Phpfox::getService('music.process')->upload($aVals, $aVals['album_id'])) {
            echo json_encode([
                'id' => $iId,
            ]);
        }
        exit;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('music.component_controller_frame_clean')) ? eval($sPlugin) : false);
    }
}