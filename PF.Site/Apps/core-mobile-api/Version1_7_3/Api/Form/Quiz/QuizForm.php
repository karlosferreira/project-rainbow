<?php


namespace Apps\Core_MobileApi\Version1_7_3\Api\Form\Quiz;

use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\QuizQuestionType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;


class QuizForm extends \Apps\Core_MobileApi\Api\Form\Quiz\QuizForm
{
    private $moduleId = '';
    private $itemId = '';
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
        $sectionName = 'title';
        $this
            ->addSection($sectionName, '')
            ->addField('title', TextType::class, [
                'label'       => 'title',
                'placeholder' => 'fill_title_for_quiz',
                'required'    => true
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_title_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], $sectionName);

        $sectionName = 'questions';
        $this
            ->addSection($sectionName, '')
            ->addField('questions', QuizQuestionType::class, [
                'label'         => 'question',
                'min_questions' => $this->getSetting()->getUserSetting('quiz.min_questions'),
                'max_questions' => $this->getSetting()->getUserSetting('quiz.max_questions'),
                'max_answers'   => $this->getSetting()->getUserSetting('quiz.max_answers'),
                'min_answers'   => $this->getSetting()->getUserSetting('quiz.min_answers'),
                'required'      => true
            ], [new RequiredValidator(), new TypeValidator(TypeValidator::IS_ARRAY)], $sectionName);

        $sectionName = 'additional_info';
        $this
            ->addSection($sectionName, 'additional_info')
            ->addField('text', TextareaType::class, [
                'label'       => 'description',
                'placeholder' => 'add_description_to_quiz',
                'required'    => true
            ], null, $sectionName)
            ->addField('attachment', AttachmentType::class, [
                'label'               => 'attachment',
                'item_type'           => "poll",
                'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                'current_attachments' => $this->getAttachments()
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName);

        if ($this->getSetting()->getUserSetting('quiz.can_upload_picture')) {
            $this->addField('file', FileType::class, [
                'label'               => 'banner',
                'multiple'            => false,
                'item_type'           => 'quiz',
                'file_type'           => 'photo',
                'required'            => $this->getSetting()->getUserSetting('quiz.is_picture_upload_required'),
                'preview_url'         => $this->getPreviewImage(),
                'value'               => !empty($this->getPreviewImage()) ? ['status' => FileType::UNCHANGED, 'temp_file' => null] : '',
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('quiz.quiz_max_upload_size'))
            ], null, $sectionName);
        }
        if (empty($this->data['item_id']) && empty($this->itemId)) {
            $sectionName = 'settings';
            $this
                ->addSection($sectionName, 'settings')
                ->addPrivacyField([
                    'description' => 'control_who_can_see_this_quiz'
                ], $sectionName, $this->getPrivacyDefault('quiz.default_privacy_setting'));
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

    /**
     * @param mixed $moduleId
     * @codeCoverageIgnore
     */
    public function setModuleId($moduleId)
    {
        $this->moduleId = $moduleId;
    }

    /**
     * @param mixed $itemId
     * @codeCoverageIgnore
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }
}