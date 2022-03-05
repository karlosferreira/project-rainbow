<?php


namespace Apps\Core_MobileApi\Api\Form\Video;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Type\VideoUploadType;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;


class VideoForm extends GeneralForm
{
    protected $action = "video";
    protected $categories;
    protected $editing = false;

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
        $setting = $this->getSetting();
        $methodUpload = $setting->getAppSetting('v.pf_video_method_upload');
        $allowUpload = $setting->getAppSetting('v.pf_video_support_upload_video')
            && (
                ($methodUpload == 2 && $setting->getAppSetting('pf_video_mux_token_id') && $setting->getAppSetting('pf_video_mux_token_secret'))
                || ($methodUpload == 1 && $setting->getAppSetting('v.pf_video_key'))
                || ($methodUpload == 0 && $setting->getAppSetting('v.pf_video_ffmpeg_path'))
            );
        $sectionName = 'basic';
        $this->addSection($sectionName, 'basic_info');
        if (!$this->isEditing()) {
            $this->addField('file', VideoUploadType::class, [
                'label'        => 'select_video_file',
                'description'  => 'you_can_upload_a_extensions',
                'file_type'    => 'video',
                'item_type'    => 'v',
                'allow_upload' => !!$allowUpload,
                'allow_url'    => true,
                'preview_url'  => $this->getPreviewImage(),
            ], null, $sectionName);

            $this
                ->addField('url', HiddenType::class, [
                    'label'            => $allowUpload ? 'or paste a URL' : 'Paste a URL',
                    'preview_endpoint' => UrlUtility::makeApiUrl('video/validate')
                ], null, $sectionName);
        }

        $sectionName = 'info';
        $this->addSection($sectionName, '');

        $this
            ->addField('title', TextType::class, [
                'label'       => 'title',
                'placeholder' => 'fill_title_for_video',
                'required'    => true
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_title_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], $sectionName)
            ->addField('text', TextareaType::class, [
                'label'       => 'description',
                'placeholder' => 'add_description_to_video',
            ], null, $sectionName);

        $sectionName = 'additional_info';
        $this->addSection($sectionName, 'additional_info');
        if ($this->isEditing()) {
            $this->addField('file', FileType::class, [
                'label'               => 'videos_image',
                'file_type'           => 'photo',
                'item_type'           => 'v_edit_video',
                'preview_url'         => $this->getPreviewImage(),
                'max_upload_filesize' => $this->getSizeLimit($setting->getUserSetting('v.pf_video_max_file_size_photo_upload'))
            ], null, $sectionName);
        }
        $this->addField('categories', HierarchyType::class, [
            'label'    => 'categories',
            'rawData'  => $this->getCategories(),
            'multiple' => true
        ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName);

        $sectionName = 'settings';
        $this
            ->addSection($sectionName, 'settings');
        if (empty($this->data['item_id'])) {
            $this
                ->addPrivacyField([
                    'description' => 'video_control_who_can_see_this_video'
                ], $sectionName, $this->getPrivacyDefault('v.default_privacy_setting'));
        }
        $this
            ->addModuleFields([
                'module_value' => 'video'
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }

    public function getPreviewImage()
    {
        return isset($this->data['image']) && strpos($this->data['image'], 'no_image') === false ? $this->data['image'] : null;
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
     * @return bool
     */
    public function isEditing()
    {
        return $this->editing;
    }

    /**
     * @param bool $editing
     */
    public function setEditing($editing)
    {
        $this->editing = $editing;
    }

}