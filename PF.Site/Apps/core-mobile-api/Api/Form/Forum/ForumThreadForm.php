<?php


namespace Apps\Core_MobileApi\Api\Form\Forum;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TagsType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class ForumThreadForm extends GeneralForm
{
    protected $forums;
    protected $action = "mobile/forum-thread";
    protected $editing = false;
    protected $forumId = null;

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
        $this->addField('title', TextType::class, [
            'label'       => 'title',
            'placeholder' => 'title',
            'required'    => true
        ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_title_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))])
            ->addField('text', TextareaType::class, [
                'label'       => 'content',
                'placeholder' => 'your_message_dot_dot_dot',
                'required'    => true
            ]);
        if (!$this->getEditing()) {
            $this->addField('is_subscribed', CheckboxType::class, [
                'label' => 'subscribe',
                'value' => 1,
                'order' => 4
            ]);
            if (empty($this->data['item_id'])) {
                $this->addField('forum_id', HiddenType::class, [
                    'value'    => $this->forumId,
                    'required' => true
                ], [new NumberRangeValidator(0)]);
            }
        } else if ($this->getSetting()->getUserSetting('forum.can_close_a_thread')) {
            $this
                ->addField('is_closed', CheckboxType::class, [
                    'label' => 'closed',
                    'value' => 0,
                    'order' => 5
                ]);
        }
        if (empty($this->data['module_id']) || empty($this->data['item_id'])) {
            $this
                ->addField('tags', TagsType::class, [
                    'label'       => 'topics',
                    'placeholder' => 'keywords',
                    'description' => 'separate_multiple_topics_with_commas'
                ]);
        }
        $this
            ->addField('attachment', AttachmentType::class, [
                'label'               => 'attachment',
                'item_type'           => "forum",
                'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                'current_attachments' => $this->getAttachments()
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)])
            ->addModuleFields([
                'module_value' => ''
            ])
            ->addField('submit', SubmitType::class, [
                'label' => $this->getLocal()->translate("save"),
            ]);
    }

    /**
     * @param mixed $forumId
     */
    public function setForumId($forumId)
    {
        $this->forumId = $forumId;
    }

    /**
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function getForums()
    {
        return $this->forums;
    }

    /**
     * @param mixed $forums
     *
     * @codeCoverageIgnore
     */
    public function setForums($forums)
    {
        $this->forums = $forums;
    }

    public function setEditing($edit)
    {
        $this->editing = $edit;
    }

    public function getEditing()
    {
        return $this->editing;
    }

    public function getAttachments()
    {
        return (isset($this->data['attachments']) ? $this->data['attachments'] : null);
    }
}