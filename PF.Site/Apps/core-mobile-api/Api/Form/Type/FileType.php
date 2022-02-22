<?php

namespace Apps\Core_MobileApi\Api\Form\Type;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;

/**
 * Class FileType
 * Re-upload file type. File will be uploaded to temporary storage
 * @package Apps\Core_MobileApi\Api\Form\Type
 */
class FileType extends GeneralType
{
    /**
     * Create form
     */
    const NEW_UPLOAD = "new";

    /**
     * Update form and change new file
     */
    const CHANGE = "change";

    /**
     * Update form and unchanged file
     */
    const UNCHANGED = "unchanged";

    const DIRECT_UPLOAD = 'direct_upload';

    /**
     * Remove current photo
     */
    const REMOVE = "remove";

    const TYPE_PHOTO = "photo";
    const TYPE_VIDEO = "video";
    const TYPE_FILE = "file";

    protected $componentName = 'File';

    public function getStructure(LocalizationInterface $trans = null)
    {
        $structure = parent::getStructure($trans);
        if (!$this->getAttr('upload_endpoint') && $this->isDirectUpload() == false) {
            $structure['upload_endpoint'] = UrlUtility::makeApiUrl('file');
        }

        return $structure;
    }

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        $value = $this->getValue();
        if (!empty($value) && is_array($value)) {
            if (empty($value['status']) ||
                !in_array($value['status'], [self::NEW_UPLOAD, self::CHANGE, self::UNCHANGED, self::REMOVE])) {
                return false;
            }
        }
        $name = $this->getName();
        if ($this->isRequiredField()) {
            if ($this->isDirectUpload()) {
                if (empty($_FILES[$name]['name'])) {
                    return false;
                }
            } else if (($value['status'] == self::REMOVE || !in_array($value['status'], [self::UNCHANGED, self::NEW_UPLOAD, self::CHANGE]))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check is direct upload. This option is enable to allow form upload file directly
     * @return bool
     */
    public function isDirectUpload()
    {
        return ($this->hasAttr(self::DIRECT_UPLOAD) && $this->getAttr(self::DIRECT_UPLOAD) == true);
    }


    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            'file_type', // (photo, video, file)
            'preview_url', // The preview URL available only for Photo
            "item_type", // Module/App id
            "upload_endpoint", // URL for upload/delete Temporary File by ID,
            "status", // Control status of file,
            "max_upload_filesize" //Max upload filesize in kb
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