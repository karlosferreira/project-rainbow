<?php


namespace Apps\Core_MobileApi\Api\Form\Forum;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;

class ForumForm extends GeneralForm
{
    protected $forums;
    protected $action = "forum";
    protected $editing = false;

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
        $this->addMultipleLanguageFields('name', TextType::class,
            [new StringLengthValidator(1, 250, null, $this->getLocal()->translate('maximum_length_for_name_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], [
                'order'    => 1,
                'label'    => 'name',
                'required' => true
            ])
            ->addField('name', HiddenType::class, $this->getEditing() ? [
                'required' => true,
            ] : [], $this->getEditing() ? [new RequiredValidator()] : null)
            ->addField('parent_id', HierarchyType::class, [
                'label'      => 'parent_forum',
                'rawData'    => $this->getForums(),
                'field_maps' => [
                    'field_id'  => 'forum_id',
                    'field_sub' => 'sub_forum'
                ],
                'order'      => 2,
                'multiple'   => false
            ])
            ->addMultipleLanguageFields('description', TextareaType::class,
                null, [
                    'label'       => 'description',
                    'placeholder' => 'type_something_dot',
                    'order'       => 3
                ])
            ->addField('is_category', RadioType::class, [
                'options'       => [
                    [
                        'value' => 0,
                        'label' => $this->getLocal()->translate('no')
                    ],
                    [
                        'value' => 1,
                        'label' => $this->getLocal()->translate('yes')
                    ],
                ],
                'value_default' => 0,
                'label'         => 'is_a_category',
                'order'         => 4
            ])
            ->addField('is_closed', RadioType::class, [
                'options'       => [
                    [
                        'value' => 0,
                        'label' => $this->getLocal()->translate('no')
                    ],
                    [
                        'value' => 1,
                        'label' => $this->getLocal()->translate('yes')
                    ],
                ],
                'value_default' => 0,
                'label'         => 'closed',
                'order'         => 5
            ])
            ->addField('submit', SubmitType::class, [
                'label' => $this->getLocal()->translate("save"),
            ]);
    }

    /**
     * @return mixed
     */
    public function getForums()
    {
        return $this->forums;
    }

    /**
     * @param mixed $forums
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
}