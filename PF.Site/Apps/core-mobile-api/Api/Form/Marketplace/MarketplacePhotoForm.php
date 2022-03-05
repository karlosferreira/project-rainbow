<?php


namespace Apps\Core_MobileApi\Api\Form\Marketplace;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\MultiFileType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;


class MarketplacePhotoForm extends GeneralForm
{
    protected $action = "marketplace-photo";
    protected $maxFiles;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this
            ->addField('files', MultiFileType::class, [
                'label'               => 'photos',
                'min_files'           => 0,
                'max_files'           => $this->getMaxFiles(),
                'file_type'           => 'photo',
                'item_type'           => 'marketplace',
                'current_files'       => $this->getCurrentImages(),
                'value'               => $this->getCurrentValue(),
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('marketplace.max_upload_size_listing'))
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }

    private function getCurrentImages()
    {
        if (!empty($this->data) && is_array($this->data)) {
            $current = [];
            foreach ($this->data as $image) {
                if (isset($image['image'])) {
                    $current[] = [
                        'id'      => $image['id'],
                        'url'     => isset($image['image']['200']) ? $image['image']['200'] : $image['image']['image_url'],
                        'default' => isset($image['main']) ? $image['main'] : false
                    ];
                }
            }
            return $current;
        }
        return null;
    }

    private function getCurrentValue()
    {
        if (!empty($this->data) && is_array($this->data)) {
            $current = [];
            foreach ($this->data as $image) {
                if (isset($image['id'])) {
                    $current['order'][] = $image['id'];
                    if (!empty($image['main'])) {
                        $current['default'] = $image['id'];
                    }
                }
            }
            return $current;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getMaxFiles()
    {
        return $this->maxFiles;
    }

    /**
     * @param mixed $maxFiles
     */
    public function setMaxFiles($maxFiles)
    {
        $this->maxFiles = $maxFiles;
    }

}