<?php


namespace Apps\Core_MobileApi\Api\Form\Group;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;

class GroupTypeForm extends GeneralForm
{
    protected $action = "group-type";
    protected $editing = false;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        if ($this->getEditing()) {
            $this->addField('name', HiddenType::class, [], [new RequiredValidator()]);
        }
        $this->addMultipleLanguageFields('name', TextType::class,
            [new StringLengthValidator(1, 250)], [
                'order'    => 1,
                'label'    => 'name',
                'required' => true
            ])
            ->addField('file', FileType::class, [
                'label'               => 'image',
                'item_type'           => 'groups',
                'file_type'           => 'photo',
                'preview_url'         => $this->getPreviewImage(),
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('groups.pf_group_max_upload_size'))
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'save',
            ]);
    }

    public function setEditing($edit)
    {
        $this->editing = $edit;
    }

    public function getEditing()
    {
        return $this->editing;
    }

    public function getPreviewImage()
    {
        return isset($this->data['image']) ? $this->data['image'] : null;
    }
}