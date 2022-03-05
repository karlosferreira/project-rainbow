<?php


namespace Apps\Core_MobileApi\Api\Form\Group;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class GroupForm extends GeneralForm
{
    protected $action = "group";
    protected $editing = false;
    protected $categories;

    const MAX_TITLE_LENGTH = 64;

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
            ->addField('title', TextType::class, [
                'required'    => true,
                'order'       => 1,
                'label'       => 'group_name',
                'placeholder' => 'fill_name_for_group'
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_name_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))])
            ->addField('reg_method', RadioType::class, [
                'label'         => 'group_privacy',
                'options'       => [
                    [
                        'value' => 0,
                        'label' => $this->getLocal()->translate('public_group')
                    ],
                    [
                        'value' => 1,
                        'label' => $this->getLocal()->translate('closed_group')
                    ],
                    [
                        'value' => 2,
                        'label' => $this->getLocal()->translate('secret_group')
                    ],
                ],
                'order'         => 2,
                'value_default' => 0,
                'required'      => true,
            ])
            ->addField('type_category', HierarchyType::class, [
                'rawData'    => $this->categories,
                'multiple'   => false,
                'field_maps' => [
                    'field_type' => 'string',
                    'field_sub'  => 'categories'
                ],
                'order'      => 3,
                'required'   => true,
                'label'      => 'category'
            ], [new RequiredValidator(), new TypeValidator(TypeValidator::IS_ARRAY_STRING)])
            ->addField('submit', SubmitType::class, [
                'label' => $this->getLocal()->translate("create_group"),
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
}