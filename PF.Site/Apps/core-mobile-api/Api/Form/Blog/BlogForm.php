<?php

namespace Apps\Core_MobileApi\Api\Form\Blog;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TagsType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class BlogForm extends GeneralForm
{
    protected $categories;
    protected $tags;
    protected $action = "blog";

    const MAX_TITLE_LENGTH = 255;

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
        $sectionName = 'basic';
        $this
            ->addSection($sectionName, 'basic_info')
            ->addField('title', TextType::class, [
                'label'       => 'title',
                'placeholder' => 'fill_title_for_blog',
                'required'    => true
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_title_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], $sectionName)
            ->addField('text', TextareaType::class, [
                'label'       => 'post',
                'placeholder' => 'add_content_to_blog',
                'required'    => true
            ], [new StringLengthValidator(1)], $sectionName)
            ->addField('attachment', AttachmentType::class, [
                'label'               => 'attachment',
                'item_type'           => "blog",
                'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                'current_attachments' => $this->getAttachments()
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName);

        $sectionName = 'additional_info';
        $this
            ->addSection($sectionName, 'additional_info')
            ->addField('categories', HierarchyType::class, [
                'rawData'    => $this->getCategories(),
                'multiple'   => true,
                'label'      => 'categories',
                'value_type' => 'array'
            ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName)
            ->addField('tags', TagsType::class, [
                'label'       => 'topics',
                'placeholder' => 'keywords',
                'description' => 'separate_multiple_topics_with_commas'
            ], [new TypeValidator(TypeValidator::IS_STRING)], $sectionName)
            ->addField('file', FileType::class, [
                'label'               => 'photo',
                'file_type'           => 'photo',
                'item_type'           => 'blog',
                'preview_url'         => $this->getPreviewImage(),
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('blog.blog_photo_max_upload_size'))
            ], null, $sectionName);

        $sectionName = 'settings';
        $this->addSection($sectionName, 'settings');

        if (empty($this->data['item_id'])) {
            $this->addPrivacyField(['description' => 'control_who_can_see_this_blog'], $sectionName, $this->getPrivacyDefault('blog.default_privacy_setting'));
        }

        $this->addModuleFields(['module_value' => 'blog']);

        if (!$this->isEdit()) {
            $this->addField('draft', CheckboxType::class, [
                'label' => 'save_as_draft',
                'value' => 0
            ]);
        } else {
            if ($this->data['post_status'] == 2) {
                $this->addField('draft', CheckboxType::class, [
                    'label' => 'save_as_draft',
                    'value' => 1
                ]);
            }
        }
        $this->addField('submit', SubmitType::class, [
            'label' => 'publish',
            'value' => 1
        ]);
    }

    private function isEdit()
    {
        return !empty($this->data['id']);
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
     * @todo not use.
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     *
     * @codeCoverageIgnore
     * @todo not use.
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    public function getPreviewImage()
    {
        return (isset($this->data['image']) && strpos($this->data['image'], 'no_image') === false ? $this->data['image'] : null);
    }

    public function getAttachments()
    {
        return (isset($this->data['attachments']) ? $this->data['attachments'] : null);
    }
}