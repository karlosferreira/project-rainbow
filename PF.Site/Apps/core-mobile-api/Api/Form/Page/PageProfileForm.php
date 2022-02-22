<?php


namespace Apps\Core_MobileApi\Api\Form\Page;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class PageProfileForm extends GeneralForm
{
    protected $action = "page_profile";
    protected $editing = false;
    protected $categories;

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
                'label'       => 'name',
                'placeholder' => 'fill_name_for_page',
                'order'       => 1,
            ], [new StringLengthValidator(1, 64)])
            ->addField('type_category', HierarchyType::class, [
                'rawData'    => $this->getCategories(),
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
                'label' => 'update',
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