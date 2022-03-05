<?php


namespace Apps\Core_MobileApi\Api\Form\Photo;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\MultiFileType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TagsType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class PhotoForm extends GeneralForm
{

    protected $categories;
    protected $albums = [];
    protected $tags;
    protected $editing = false;
    protected $action = "photo";
    protected $albumId = 0;
    protected $canMature = true;

    const MAX_TITLE_LENGTH = 250;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws ErrorException
     */
    function buildForm($options = null, $data = [])
    {

        if ($this->getSetting()->getAppSetting('photo.allow_photo_category_selection')) {
            if(!in_array($this->data['module_id'], ['pages', 'groups'])) {
                $this->addField('categories', HierarchyType::class, [
                    'rawData'  => $this->getCategories(),
                    'order'    => 3,
                    'multiple' => true,
                    'label'    => 'categories',
                ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)]);
            }
        }
        if (!empty($this->getAlbums()) && empty($this->data['is_profile_photo']) && empty($this->data['is_cover_photo'])) {
            $this->addField('album', ChoiceType::class, [
                'options' => array_map(function ($item) {
                    return [
                        'value' => (int)$item['album_id'],
                        'label' => $this->getParse()->cleanOutput($item['name'])
                    ];
                }, $this->getAlbums()),
                'order'   => 2,
                'label'   => 'album',
                'disable_uncheck' => !empty($this->getAlbumId()),
                'value'   => !empty($this->getAlbumId()) ? (int)$this->getAlbumId() : null
            ]);
        }
        if ($this->getEditing()) {
            $this
                ->addField('title', TextType::class, [
                    'label'       => 'Title',
                    'placeholder' => 'title',
                    'order'       => 4
                ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_title_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))])
                ->addField('text', TextareaType::class, [
                    'label' => 'description',
                    'order' => 5
                ])
                ->addField('tags', TagsType::class, [
                    'label' => 'topics',
                    'order' => 6
                ]);
            if ($this->getCanMature()) {
                $this->addField('mature', RadioType::class, [
                    'label'         => 'mature_content',
                    'value_default' => 0,
                    'options'       => $this->_getMatureValue(),
                    'order'         => 7
                ], [new NumberRangeValidator(0, 2)]);
            }

            $this->addField('allow_download', CheckboxType::class, [
                'label'         => 'download_enabled',
                'value_default' => 1,
                'order'         => 8
            ]);
            if (empty($this->data['album']) && empty($this->data['item_id'])) {
                $this->addPrivacyField([], null, $this->privacy->getValue('photo.default_privacy_setting'));
            }
        } else {
            if (empty($this->data['item_id']) && empty($this->data['module_id'])) {
                $this->addPrivacyField(empty($this->getAlbums()) ? [] : [
                    'hidden_value' => [null, '0', 0],
                    'hidden_by'    => '!album'
                ], null, $this->getPrivacyDefault('photo.default_privacy_setting'));
            }
            $this->addField('files', MultiFileType::class, [
                'label'               => 'select_images',
                'min_files'           => 1,
                'max_files'           => $this->getSetting()->getUserSetting('photo.max_images_per_upload'),
                'item_type'           => 'photo',
                'file_type'           => 'photo',
                'required'            => true,
                'allow_temp_default'  => false,
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('photo.photo_max_upload_size')),
            ])
            //Support upload photo in feed type_id = 1 -> photo add to album timeline
            ->addField('type_id', HiddenType::class, [], [new NumberRangeValidator(0)]);
        }
        //Set photo is album cover when edit
        $this->addField('set_album_cover', HiddenType::class, [], [new NumberRangeValidator(0)]);
        //Support checkin in feed
        $this
            ->addField('location', HiddenType::class)
            ->addModuleFields()
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
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

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     *
     * @codeCoverageIgnore
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getAlbums()
    {
        return $this->albums;
    }

    /**
     * @param $albums
     */
    public function setAlbums($albums)
    {
        $this->albums = $albums;
    }

    /**
     * @param $edit
     *
     * @return $this
     */
    public function setEditing($edit)
    {
        $this->editing = $edit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEditing()
    {
        return $this->editing;
    }

    private function _getMatureValue()
    {
        $mature = [
            [
                'value' => 0,
                'label' => $this->getLocal()->translate('no')
            ],
            [
                'value' => 1,
                'label' => $this->getLocal()->translate('yes_warning')
            ],
            [
                'value' => 2,
                'label' => $this->getLocal()->translate('yes_strict')
            ],

        ];
        return $mature;
    }

    public function setCanMature($canMature)
    {
        $this->canMature = $canMature;
        return $this;
    }

    public function getCanMature()
    {
        return $this->canMature;
    }

    public function buildValues()
    {
        if (!$this->isPost) {
            //Is get edit form
            if (!empty($this->data['album'])) {
                $this->data['album'] = (int)$this->data['album']['id'];
            } else if (!empty($this->getAlbumId())) {
                $this->data['album'] = (int)$this->getAlbumId();
            }
        }
        parent::buildValues();
    }

    public function setAlbumId($id)
    {
        $this->albumId = $id;
        return $this;
    }

    public function getAlbumId()
    {
        return $this->albumId;
    }
}