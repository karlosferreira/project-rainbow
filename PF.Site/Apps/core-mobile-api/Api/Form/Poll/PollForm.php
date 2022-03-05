<?php


namespace Apps\Core_MobileApi\Api\Form\Poll;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\PollAnswerType;
use Apps\Core_MobileApi\Api\Form\Type\PollCloseTimeType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;


class PollForm extends GeneralForm
{
    protected $action = "poll";
    private $moduleId = '';
    private $itemId = '';

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
        $sectionName = 'question';
        $this
            ->addSection($sectionName, '')
            ->addField('question', TextType::class, [
                'label'       => 'question',
                'placeholder' => 'fill_question_for_poll',
                'required'    => true
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_title_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], $sectionName);

        $sectionName = 'answers';
        $this
            ->addSection($sectionName, 'answers')
            ->addField('answers', PollAnswerType::class, [
                'max_answers'   => $this->getSetting()->getUserSetting('poll.maximum_answers_count') >= 2 ? $this->getSetting()->getUserSetting('poll.maximum_answers_count') : 2,
                'min_answers'   => 2,
                'returnKeyType' => 'next',
                'required'      => true
            ], [new RequiredValidator(), new TypeValidator(TypeValidator::IS_ARRAY)], $sectionName);

        $sectionName = 'additional_info';
        $this
            ->addSection($sectionName, 'additional_info')
            ->addField('text', TextareaType::class, [
                'label'       => 'description',
                'placeholder' => 'add_description_to_poll'
            ], null, $sectionName)
            ->addField('attachment', AttachmentType::class, [
                'label'               => 'attachment',
                'item_type'           => "poll",
                'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                'current_attachments' => $this->getAttachments()
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName);
        if ($this->getSetting()->getUserSetting('poll.poll_can_upload_image')) {
            $this
                ->addField('file', FileType::class, [
                    'label'               => 'display_photo',
                    'multiple'            => false,
                    'item_type'           => 'poll',
                    'file_type'           => 'photo',
                    'required'            => !!$this->getSetting()->getAppSetting('poll.is_image_required'),
                    'preview_url'         => $this->getPreviewImage(),
                    'value'               => !empty($this->getPreviewImage()) ? ['status' => FileType::UNCHANGED, 'temp_file' => null] : '',
                    'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('poll.poll_max_upload_size'))
                ], null, $sectionName);
        }
        $this
            ->addField('enable_close', CheckboxType::class, [
                'label'         => 'set_close_time',
                'value_default' => 0,
                'control_field' => 'close_time'
            ], null, $sectionName)
            ->addField('close_time', PollCloseTimeType::class, [
                'label'     => 'close_time',
                'hidden_by' => '!enable_close'
            ], null, $sectionName);

        $sectionName = 'settings';
        $this
            ->addSection($sectionName, 'settings')
            ->addField('hide_vote', CheckboxType::class, [
                'label'         => 'hide_votes',
                'options'       => [
                    [
                        'value' => 1
                    ],
                    [
                        'value' => 0
                    ]
                ],
                'value_default' => 0,
            ], null, $sectionName)
            ->addField('is_multiple', CheckboxType::class, [
                'label'         => 'allow_multiple_choice',
                'options'       => [
                    [
                        'value' => 1
                    ],
                    [
                        'value' => 0
                    ]
                ],
                'value_default' => 0,
            ], null, $sectionName);
        if (empty($this->data['item_id']) && empty($this->itemId)) {
            $this->addPrivacyField([
                'description' => 'control_who_can_see_this_poll'
            ], $sectionName, $this->getPrivacyDefault('poll.default_privacy_setting'));
        }
        $this
            ->addModuleFields([
                'module_value' => $this->moduleId,
                'item_value'   => $this->itemId,
            ])
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

    public function buildValues()
    {
        if (!$this->isPost) {
            //Is get edit form
            if (!empty($this->data['answers'])) {
                $answers = [];
                foreach ($this->data['answers'] as $answer) {
                    $answers[] = [
                        'value' => $answer['answer'],
                        'id'    => $answer['id'],
                        'order' => $answer['ordering']
                    ];
                }
                $this->data['answers'] = $answers;
            }
        }
        parent::buildValues();
    }

    /**
     * @param mixed $moduleId
     */
    public function setModuleId($moduleId)
    {
        $this->moduleId = $moduleId;
    }

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }


}