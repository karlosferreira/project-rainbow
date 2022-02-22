<?php


namespace Apps\Core_MobileApi\Api\Form\Forum;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class ForumPostForm extends GeneralForm
{

    protected $forums;
    protected $action = "mobile/forum-post";
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
        $this
            ->addField('text', TextareaType::class, [
                'label'       => 'content',
                'placeholder' => 'your_reply_dot_dot_dot',
                'required'    => true
            ]);
        if (!$this->getEditing()) {
            $this
                ->addField('is_subscribed', CheckboxType::class, [
                    'label'         => 'subscribe',
                    'options'       => [
                        [
                            'value' => 1,
                            'label' => $this->getLocal()->translate('yes')
                        ],
                        [
                            'value' => 0,
                            'label' => $this->getLocal()->translate('no')
                        ]
                    ],
                    'value_default' => 0,
                    'order'         => 4
                ])
                ->addField('thread_id', HiddenType::class, [
                    'required' => true
                ], [new RequiredValidator()]);
        }
        $this
            ->addField('attachment', AttachmentType::class, [
                'label'               => 'attachment',
                'item_type'           => "forum",
                'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                'current_attachments' => $this->getAttachments()
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)])
            ->addField('submit', SubmitType::class, [
                'label' => $this->getLocal()->translate("save"),
            ]);
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