<?php


namespace Apps\Core_MobileApi\Api\Form\Music;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;


class MusicPlaylistForm extends GeneralForm
{
    protected $action = "music-playlist";

    const MAX_TITLE_LENGTH = 250;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $sectionName = 'basic';
        $this
            ->addSection($sectionName, 'basic_info')
            ->addField('name', TextType::class, [
                'label'       => 'name',
                'placeholder' => 'fill_name_for_playlist',
                'required'    => true
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_name_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], $sectionName);

        $sectionName = 'additional_info';
        $this
            ->addSection($sectionName, 'additional_info')
            ->addField('text', TextareaType::class, [
                'label'       => 'description',
                'placeholder' => 'add_description_to_playlist'
            ], null, $sectionName)
            ->addField('attachment', AttachmentType::class, [
                'label'               => 'attachment',
                'item_type'           => "music_playlist",
                'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                'current_attachments' => $this->getAttachments()
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName)
            ->addField('file', FileType::class, [
                'label'               => 'Photo',
                'item_type'           => 'music_playlist_image',
                'file_type'           => 'photo',
                'preview_url'         => $this->getPreviewImage(),
                'required'            => false,
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('photo.photo_max_upload_size'))
            ], null, $sectionName);

        $sectionName = 'settings';
        $this
            ->addSection($sectionName, 'settings');
        if (empty($this->data['item_id'])) {
            $this->addPrivacyField([
                'description' => 'music_control_who_can_see_this_playlist_and_any_songs_connected_to_it',
                'disable_custom' => true
            ], $sectionName, $this->getPrivacyDefault('music.default_privacy_setting_playlist'));
        }

        $this
            ->addModuleFields()
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
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

    public function getAttachments()
    {
        return (isset($this->data['attachments']) ? $this->data['attachments'] : null);
    }
}