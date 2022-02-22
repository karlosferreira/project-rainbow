<?php


namespace Apps\Core_MobileApi\Api\Form\Photo;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;

class PhotoAlbumForm extends GeneralForm
{
    protected $categories;
    protected $albums;
    protected $tags;
    protected $action = "photo_album";
    protected $canEditName = true;

    const MAX_TITLE_LENGTH = 250;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this->addField('name', TextType::class, [
            'label'       => 'name',
            'placeholder' => 'album_name',
            'editable'    => $this->canEditName,
            'required'    => true,
        ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_name_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))])
            ->addField('text', TextareaType::class, [
                'label'       => 'description',
                'placeholder' => 'type_something_dot',
                'order'       => 5
            ]);
        if ($this->getSetting()->getUserSetting('photo.can_use_privacy_settings') && empty($this->data['item_id'])) {
            $this->addPrivacyField(['description' => $this->getLocal()->translate('control_who_can_see_this_photo_album_and_any_photos_associated_with_it')],
                '');
        }
        $this
            ->addModuleFields()
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }

    /**
     * @param bool $canEditName
     */
    public function setCanEditName($canEditName)
    {
        $this->canEditName = $canEditName;
    }

    /**
     * @return bool
     */
    public function isCanEditName()
    {
        return $this->canEditName;
    }


}