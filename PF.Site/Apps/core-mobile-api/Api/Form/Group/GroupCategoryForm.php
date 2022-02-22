<?php


namespace Apps\Core_MobileApi\Api\Form\Group;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;

class GroupCategoryForm extends GeneralForm
{
    protected $types;
    protected $action = "group-category";
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
        if ($this->getEditing()) {
            $this->addField('name', HiddenType::class, [], [new RequiredValidator()]);
        }
        $this->addMultipleLanguageFields('name', TextType::class,
            [new StringLengthValidator(1, 250)], [
                'order'    => 1,
                'label'    => 'name',
                'required' => true
            ])
            ->addField('type_id', HierarchyType::class, [
                'label'      => 'parent_category',
                'rawData'    => $this->types,
                'multiple'   => false,
                'field_maps' => [
                    'field_id' => 'type_id'
                ],
                'order'      => 2,
                'required' => true
            ])
            ->addField('submit', SubmitType::class, [
                'label' => $this->getLocal()->translate("save"),
            ]);
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param mixed $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
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