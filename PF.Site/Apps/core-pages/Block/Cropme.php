<?php

namespace Apps\Core_Pages\Block;

use Phpfox;
use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

class Cropme extends Phpfox_Component
{
    public function process()
    {
        $iPage = $this->request()->get('id', $this->getParam('id'));
        $bAllowUpload = $this->getParam('allow_upload', false);
        $aPage = Phpfox::getService('pages')->getForEdit($iPage, !$bAllowUpload);

        //load temp thumbnail image
        if ($aPage['image_server_id'] != 0) {
            $sPageImageUrl = sprintf($aPage['image_path'], '');
            $oTempImage = storage()->get('page/thumbnail/' . $iPage);
            if (empty($oTempImage) || !file_exists(Phpfox::getParam('pages.dir_image') . 'temp' . PHPFOX_DS . $oTempImage->value)) {
                $aPage['image_path'] = 'temp/' . Phpfox::getService('pages.process')->saveTempFileToLocalServer($iPage, $sPageImageUrl, $aPage['image_server_id']);
            } else {
                $aPage['image_path'] = 'temp/' . $oTempImage->value;
            }
            $aPage['image_server_id'] = 0;
        }

        if (!empty($bAllowUpload)) {
            $this->template()->assign('uploadParams', [
                'from_profile' => true,
                'url_params' => [
                    'page_id' => $aPage['page_id'],
                ],
            ]);
        }

        $this->template()->assign([
            'aPageCropMe' => $aPage,
            'bAllowUploadPageProfilePhoto' => $bAllowUpload,
        ]);
    }
}
