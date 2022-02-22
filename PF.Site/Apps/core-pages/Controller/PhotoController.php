<?php

namespace Apps\Core_Pages\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_File;

class PhotoController extends \Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);

        $iPageId = $this->request()->get('page_id');
        if (!$iPageId) {
            \Phpfox_Error::display(_p('page_not_found'));
        }

        $aPage = Phpfox::getService('pages')->getPage($iPageId);
        if (!$aPage) {
            \Phpfox_Error::display(_p('page_not_found'));
        }

        header('Content-Type: application/json');
        $iUserId = Phpfox::getService('pages')->getUserId($iPageId);
        $oFile = Phpfox_File::instance();
        $isTemp = $this->request()->get('is_temp');
        $oFile->load($isTemp ? 'file' : 'ajax_upload', array('jpg', 'gif', 'png'),
            (Phpfox::getUserParam('pages.max_upload_size_pages') == 0 ? null : (Phpfox::getUserParam('pages.max_upload_size_pages') / 1024)));

        if (!\Phpfox_Error::isPassed()) {
            $sErrorMessages = implode('<br/>', \Phpfox_Error::get());
            echo json_encode([
                'run' => 'window.parent.sCustomMessageString = \'' . $sErrorMessages .'\';tb_show(\'' . _p('error') . '\', $.ajaxBox(\'core.message\', \'height=150&width=300\'));'
            ]);
            exit;
        }

        if ($this->request()->get('is_temp')) {
            $sType = 'pages';
            $aParams = array_merge(Phpfox::callback($sType . '.getUploadParams'), [
                'update_space' => false,
                'type' => $sType,
                'thumbnail_sizes' => [],
            ]);

            $aFile = Phpfox::getService('user.file')->upload('file', $aParams, false, false);

            if (!$aFile || !empty($aFile['error'])) {
                echo json_encode([
                    'error' => !empty($aFile['error']) ? $aFile['error'] : _p('upload_fail_please_try_again_later'),
                ]);
                exit;
            }

            $iServerId = 0; //Storage temp photo in local in case setting keep file in server is turned off or cross-origin
            $iTempFileId = Phpfox::getService('core.temp-file')->add([
                'type' => $sType,
                'size' => $aFile['size'],
                'path' => $aFile['name'],
                'server_id' => $iServerId,
            ]);
            $sImage = Phpfox::getLib('image.helper')->display([
                'server_id'  => $iServerId,
                'path'       => 'pages.url_image',
                'file'       =>  $aFile['name'],
                'suffix'     => '',
                'no_default' => true,
                'return_url' => true,
            ]);

            $jsonParams = [
                'imagePath' => $sImage,
                'serverId' => $iServerId,
                'tempFileId' => $iTempFileId,
            ];

            echo json_encode($jsonParams);

            exit;
        }

        Phpfox::getService('pages.process')->uploadProfilePhoto($aPage, $iUserId);

        // redirect to page
        echo json_encode([
            'run' => 'window.location.reload();'
        ]);

        exit;
    }
}
