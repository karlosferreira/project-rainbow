<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;

class VideoUploadType extends FileType
{

    protected $componentName = 'VideoUpload';

    public function getStructure(LocalizationInterface $trans = null)
    {
        $structure = parent::getStructure($trans);
        if (!$this->getAttr('upload_endpoint')) {
            $structure['upload_endpoint'] = UrlUtility::makeApiUrl('file/upload-video');
        }
        return $structure;
    }

    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            'returnKeyType',
            'file_type', // (video)
            'item_type', // Module/App id
            'upload_endpoint', // URL for upload/delete Temporary File by ID,
            'status' // Control status of file
        ];
    }

    public function getMetaValueFormat()
    {
        return "[status : 'new' ,temp_file : '{num}']";
    }


    public function getMetaDescription()
    {
        return "Single file upload control";
    }
}