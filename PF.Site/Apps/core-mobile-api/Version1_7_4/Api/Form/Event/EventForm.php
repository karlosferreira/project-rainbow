<?php
namespace Apps\Core_MobileApi\Version1_7_4\Api\Form\Event;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\DateTimeType;
use Apps\Core_MobileApi\Api\Form\Type\DateType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\IntegerType;
use Apps\Core_MobileApi\Api\Form\Type\LocationType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Type\UrlType;
use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;
use Core\Lib;
use Phpfox;

class EventForm extends GeneralForm
{
    protected $categories;
    protected $countries;
    protected $tags;
    protected $action = "event";
    protected $editing = false;
    protected $bIsRepeat = false;

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
        $this->addSection($sectionName, 'basic_info');
        if ($this->editing) {
            if ($this->bIsRepeat) {
                $this->addField('event_editconfirmboxoption_value', ChoiceType::class,
                    [
                        'label' => 'apply_edits_for_cap',
                        'required' => true,
                        'value_default' => 'only_this_event',
                        'options' => [
                            [
                                'label' => $this->local->translate('only_this_event'),
                                'value' => 'only_this_event',
                            ],
                            [
                                'label' => $this->local->translate('all_events_uppercase'),
                                'value' => 'all_events_uppercase',
                            ],
                        ]
                    ], [new RequiredValidator()], $sectionName);
            } else {
                $this->addField('event_editconfirmboxoption_value', HiddenType::class, [
                    'value' => 'only_this_event',
                ], null, $sectionName);
            }
        }
        $this->addField('title', TextType::class, [
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

        $sectionName = 'event_type';
        $this->addSection($sectionName, 'event_type')
            ->addField('is_online', CheckboxType::class, [
                'label'         => 'online_event',
                'value_default' => 0
            ], null, $sectionName)
            ->addField('online_link', UrlType::class, [
                'label' => 'online_link',
                'placeholder' => 'online_link_placeholder',
                'hidden_by' => '!is_online',
                'required' => false,
            ], null, $sectionName);

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
            ->addField('location_non_req', LocationType::class, [
                'label'               => 'location_venue',
                'placeholder'         => 'enter_location',
                'required'            => false,
                'hidden_by'           => '!is_online',
                'use_transform'       => true,
                'group_transform'     => true,
                'include_country_iso' => true,
                'value'               => $this->getLocation()
            ], null, $sectionName)
            ->addField('location_req', LocationType::class, [
                'label'               => 'location_venue',
                'placeholder'         => 'enter_location',
                'required'            => true,
                'hidden_by'           => 'is_online',
                'use_transform'       => true,
                'group_transform'     => true,
                'include_country_iso' => true,
                'value'               => $this->getLocation()
            ], [new RequiredValidator()], $sectionName)
            ->addField('file', FileType::class, [
                'label'               => 'banner',
                'file_type'           => 'photo',
                'item_type'           => 'event',
                'preview_url'         => $this->getPreviewImage(),
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('event.max_upload_size_event')),
                'description'         => 'mobile_event_photo_recommended'
            ], null, $sectionName);

        if (!$this->editing) {
            $sectionName = 'repeat';
            $this->addSection($sectionName, '')
                ->addField('isrepeat', ChoiceType::class,
                    $this->getRepeatOptions(), [new RequiredValidator()], $sectionName)
                ->addField('repeat_type', ChoiceType::class,[
                    'label' => 'repeat_type',
                    'options' => [
                        [
                            'label' => $this->local->translate('end_repeat_after'),
                            'value' => 0,
                        ],
                        [
                            'label' => $this->local->translate('end_repeat_at'),
                            'value' => 1,
                        ],
                    ],
                    'disable_uncheck' => true,
                    'value_default' => 0
                ], null, $sectionName)
                ->addField('after_number_event', IntegerType::class, [
                    'label' => 'end_repeat_after',
                    'placeholder' => 'enter_number_of_recurring_events',
                    'description' => $this->local->translate('maximum_number_events', [
                        'number' => Phpfox::getParam('event.event_max_instance_repeat_event')
                    ]),
                    'hidden_by' => 'repeat_type',
                    'returnKeyType' => 'default'
                ], [new NumberRangeValidator(1, $this->getSetting()->getAppSetting('event.event_max_instance_repeat_event'))], $sectionName)
                ->addField('timerepeat', DateType::class, [
                    'label' => 'end_repeat_at',
                    'placeholder' => 'select_end_repeat_time',
                    'hidden_by' => '!repeat_type'
                ], null, $sectionName);
        }

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
     * @param bool $edit
     *
     * @codeCoverageIgnore
     */
    public function setEditing($edit)
    {
        $this->editing = $edit;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    public function getEditing()
    {
        return $this->editing;
    }

    /**
     * @param bool $bIsRepeat
     *
     * @codeCoverageIgnore
     */
    public function setIsRepeat($bIsRepeat)
    {
        $this->bIsRepeat = $bIsRepeat;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    public function getIsRepeat()
    {
        return $this->bIsRepeat;
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

    public function getRepeatOptions()
    {
        return [
            'label' => 'repeat',
            'required' => false,
            'value_default' => -1,
            'options' => [
                [
                    'label' => $this->getLocal()->translate('no_repeat'),
                    'value' => -1
                ],
                [
                    'label' => $this->getLocal()->translate('daily'),
                    'value' => 0
                ],
                [
                    'label' => $this->getLocal()->translate('weekly'),
                    'value' => 1
                ],
                [
                    'label' => $this->getLocal()->translate('monthly'),
                    'value' => 2
                ],
            ],
            'disable_uncheck' => true,
        ];
    }

    public function getLocation() {
        return [
            "address" => !empty($this->data['location']) ? $this->data['location'] : '',
            "lat" => $this->data['coordinate']['latitude'],
            "lng" => $this->data['coordinate']['longitude']
        ];

    }

    public function isValid()
    {
        if (!$this->isBuild) {
            $this->buildForm();
            $this->buildValues();
        }
        $bValid = true;
        foreach ($this->fields as $field) {
            if (!$field->isValid()) {
                if ($field->getName() == 'location_req') {
                    if (!$this->getField('is_online')->getValue()) {
                        $bValid = false;
                        $this->invalidFields[$field->getName()] = $field->getErrorMessage();
                    }
                } else {
                    $bValid = false;
                    $this->invalidFields[$field->getName()] = $field->getErrorMessage();
                }
            }
        }
        return $bValid;
    }
}