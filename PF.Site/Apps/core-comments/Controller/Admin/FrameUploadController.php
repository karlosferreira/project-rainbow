<?php

namespace Apps\Core_Comments\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Request;

defined('PHPFOX') or exit('NO DICE!');

class FrameUploadController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $iId = $_REQUEST['id'];
        if (!$iId) {
            echo json_encode([
                'error_add' => _p('opps_something_went_wrong')
            ]);
            exit;
        }
        $aStickerSet = Phpfox::getService('comment.stickers')->getForEdit($iId);
        if (!$aStickerSet) {
            echo json_encode([
                'errors' => [_p('the_sticker_set_you_are_looking_for_either_does_not_exist_or_has_been_removed')]
            ]);
            exit;
        }
        $aParams = Phpfox::getService('comment')->getUploadParams(['id' => $iId]);
        $aParams['type'] = 'comment';
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
        $iImageId = db()->insert(Phpfox::getT('comment_stickers'), [
            'set_id'     => $iId,
            'image_path' => $aFile['name'],
            'server_id'  => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
        ]);
        if ($iImageId) {
            db()->updateCounter('comment_sticker_set', 'total_sticker', 'set_id', $aStickerSet['set_id']);
            if (empty($aStickerSet['thumbnail_id'])) {
                db()->update(':comment_sticker_set', ['thumbnail_id' => $iImageId],
                    'set_id = ' . $aStickerSet['set_id']);
            }
        }
        echo json_encode([
            'id' => $iImageId,
        ]);
        exit;
    }
}