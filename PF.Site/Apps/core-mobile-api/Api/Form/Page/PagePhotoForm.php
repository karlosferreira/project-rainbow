<?php


namespace Apps\Core_MobileApi\Api\Form\Page;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class PagePhotoForm extends GeneralForm
{
    protected $action = "page-photo";

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this
            ->addField('file', FileType::class, [
                'label'               => 'photo',
                'item_type'           => 'pages',
                'file_type'           => 'photo',
                'preview_url'         => $this->getPreviewImage(),
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('pages.max_upload_size_pages'))
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'update',
            ]);
    }

    public function getPreviewImage()
    {
        if (isset($this->data['image'])) {
            if (isset($this->data['image']['200'])) {
                return $this->data['image']['200'];
            } else if (isset($this->data['image']['image_url'])) {
                return $this->data['image']['image_url'];
            }
        }
        return null;
    }
}