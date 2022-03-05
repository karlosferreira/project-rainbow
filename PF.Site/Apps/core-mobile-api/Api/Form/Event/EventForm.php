<?php


namespace Apps\Core_MobileApi\Api\Form\Event;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\DateTimeType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;
use Core\Lib;

class EventForm extends GeneralForm
{
    protected $categories;
    protected $countries;
    protected $tags;
    protected $action = "event";

    const MAX_TITLE_LENGTH = 250;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws ErrorException
     * @throws ValidationErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $sectionName = 'basic';
        $this->addSection($sectionName, 'basic_info')
            ->addField('title', TextType::class, [
                'label'       => 'event_name',
                'placeholder' => 'fill_title_for_event',
                'required'    => true
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_name_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], $sectionName)
            ->addField('categories', HierarchyType::class, [
                'label'    => 'categories',
                'rawData'  => $this->getCategories(),
                'multiple' => false
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName)
            ->addField('text', TextareaType::class, [
                'label'       => 'description',
                'placeholder' => 'add_description_to_event',
            ], null, $sectionName)
            ->addField('attachment', AttachmentType::class, [
                'label'               => 'attachment',
                'item_type'           => "event",
                'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                'current_attachments' => $this->getAttachments()
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName);

        $sectionName = 'additional_info';
        $this->addSection($sectionName, 'additional_info')
            ->addField('start_time', DateTimeType::class, [
                'label'       => 'start_time',
                'placeholder' => 'select_time',
                'required'    => true
            ], [new RequiredValidator()], $sectionName)
            ->addField('end_time', DateTimeType::class, [
                'label'       => 'end_time',
                'placeholder' => 'select_time',
                'required'    => true
            ], [new RequiredValidator()], $sectionName)
            ->addField('location', TextType::class, [
                'label'       => 'location_venue',
                'placeholder' => 'enter_location',
                'required'    => true
            ], [new RequiredValidator()], $sectionName)
            ->addField('address', TextType::class, [
                'label'       => 'address',
                'placeholder' => 'enter_address'
            ], null, $sectionName)
            ->addField('city', TextType::class, [
                'label'       => 'city',
                'inline'      => true,
                'placeholder' => 'city_name'
            ], null, $sectionName)
            ->addField('postal_code', TextType::class, [
                'label'       => 'postal_code',
                'inline'      => true,
                'placeholder' => '- - - - - -'
            ], null, $sectionName)
            ->addCountryField(false, 'country', $sectionName)
            ->addField('file', FileType::class, [
                'label'               => 'banner',
                'file_type'           => 'photo',
                'item_type'           => 'event',
                'preview_url'         => $this->getPreviewImage(),
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('event.max_upload_size_event')),
                'description'         => 'mobile_event_photo_recommended'
            ], null, $sectionName);

        $sectionName = 'settings';
        $this
            ->addSection($sectionName, 'settings');
        if (empty($this->data['item_id'])) {
            $this->getPrivacyField([
                'description'    => 'control_who_can_see_this_event',
            ], $sectionName, $this->getPrivacyDefault('event.display_on_profile'));
        }
        $this
            ->addModuleFields([
                'module_value' => 'event'
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }


    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     *
     * @codeCoverageIgnore
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
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

    public function getPrivacyField($options = [], $section = null, $defaultValue = '')
    {
        $aEventApp = Lib::appInit('Core_Events');
        if (!empty($aEventApp) && version_compare($aEventApp->version, '4.7.4', '>=')) {
            $this->addPrivacyField($options, $section, $defaultValue, [
                [
                    'label' => 'invitee_only_privacy',
                    'value' => 5,
                    'index' => 3
                ]
            ]);
        } else {
            $this->addPrivacyField($options, $section, $defaultValue);
        }
    }
}