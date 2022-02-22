<?php

namespace Apps\Core_MobileApi\Api\Form\Event;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;

class EventCategoryForm extends GeneralForm
{
    protected $categories;
    protected $action = "event-category";
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
            [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_title_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], [
                'label'    => 'title',
                'order'    => 1,
                'required' => true
            ])
            ->addField('name', HiddenType::class, [], $this->getEditing() ? [new RequiredValidator()] : null)
            ->addField('parent_id', HierarchyType::class, [
                'label'    => 'parent_category',
                'rawData'  => $this->getCategories(),
                'multiple' => false,
                'order'    => 2
            ], [new NumberRangeValidator(0)])
            ->addField('submit', SubmitType::class, [
                'label' => 'submit',
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

    public function setEditing($edit)
    {
        $this->editing = $edit;
    }

    public function getEditing()
    {
        return $this->editing;
    }
}