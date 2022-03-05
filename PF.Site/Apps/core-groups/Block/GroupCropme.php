<?php

namespace Apps\PHPfox_Groups\Block;

use Phpfox_Component;
use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class GroupCropme extends Phpfox_Component
{
    public function process()
    {
        $iGroupId = $this->request()->get('id');
        $bAllowUpload = $this->getParam('allow_upload', false);
        $aGroup = \Phpfox::getService('groups')->getForEdit($iGroupId, !$bAllowUpload);

        if (empty($aGroup['page_id']) && $bAllowUpload) {
            return false;
        }

        //load temp thumbnail image
        if($aGroup['image_server_id'] != 0) {
            $sGroupImageUrl = str_replace("%s","",$aGroup['image_path']);
            $oTempImage = storage()->get('group/thumbnail/'.$iGroupId);
            if(empty($oTempImage) || !file_exists(Phpfox::getParam('pages.dir_image') . 'temp/' . $oTempImage->value)) {
                $sImageName = \Phpfox::getService('groups.process')->saveTempFileToLocalServer($iGroupId, $sGroupImageUrl, $aGroup['image_server_id']);
                $aGroup['image_path'] = 'temp/'.$sImageName;
            } else {
                $aGroup['image_path'] = 'temp/'.$oTempImage->value;
            }

            $aGroup['image_server_id'] = 0;
        }

        if (!empty($bAllowUpload)) {
            $this->template()->assign('uploadParams', [
                'from_profile' => true,
                'url_params' => [
                    'page_id' => $aGroup['page_id'],
                ],
            ]);
        }

        $this->template()->assign([
            'aGroupCropMe' => $aGroup,
            'bAllowUploadGroupProfilePhoto' => $bAllowUpload,
        ]);
    }
}
