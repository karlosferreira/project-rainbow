<?php
namespace Apps\P_StatusBg\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Request;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class FrameUploadController
 * @package Apps\P_StatusBg\Controller\Admin
 */
class FrameUploadController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $sTimeStamp = $_REQUEST['time_stamp'];
        $iId = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        if (!$sTimeStamp && !$iId) {
            echo json_encode([
                'error_add' => _p('opps_something_went_wrong')
            ]);
            exit;
        }
        $aParams = Phpfox::getService('pstatusbg')->getUploadParams();
        $aParams['type'] = 'pstatusbg';
        $aParams['update_space'] = false;
        $aImage = Phpfox::getService('user.file')->load('file', $aParams);
        if (!$aImage) {
            echo json_encode([
                'errors' => [_p('cannot_find_the_uploaded_photo_please_try_again')]
            ]);
            exit;
        }

        if (!empty($aImage['error'])) {
            echo json_encode([
                'errors' => [$aImage['error']]
            ]);
            exit;
        }
        $aFile = Phpfox::getService('user.file')->upload('file', $aParams, true);
        if (empty($aFile) || !empty($aFile['error'])) {
            if (empty($aFile)) {
                echo json_encode([
                    'errors' => [_p('cannot_find_the_uploaded_file_please_try_again')]
                ]);
                exit;
            }

            if (!empty($aFile['error'])) {
                echo json_encode([
                    'errors' => [$aFile['error']]
                ]);
                exit;
            }
        }
        $iImageId = db()->insert(Phpfox::getT('pstatusbg_backgrounds'), array(
            'time_stamp' => $sTimeStamp,
            'collection_id' => $iId,
            'image_path' => 'pstatusbg/' . $aFile['name'],
            'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
            'ordering' => 99
        ));
        if ($iId && $iImageId) {
            $aCollection = Phpfox::getService('pstatusbg')->getForEdit($iId);
            db()->updateCounter('pstatusbg_collections', 'total_background', 'collection_id', $iId);
            if (empty($aCollection['main_image_id'])) {
                db()->update(':pstatusbg_collections', ['main_image_id' => $iImageId],
                    'collection_id = ' . $aCollection['collection_id']);
            }
        }
        echo json_encode([
            'id' => $iImageId,
        ]);
        exit;
    }
}