<?php


namespace Apps\Core_MobileApi\Api\Form\Music;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\AttachmentType;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\MultiFileType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;


class MusicSongForm extends GeneralForm
{

    protected $action = "music-song";
    protected $albums;
    protected $editing;
    protected $genres;
    protected $albumId;

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
        $sectionName = 'basic';
        $this->addSection($sectionName, 'basic_info');
        if ($this->editing) {
            $this->addField('title', TextType::class, [
                'label'       => 'name',
                'placeholder' => 'fill_name_for_song',
                'required'    => true
            ], [new StringLengthValidator(1, self::MAX_TITLE_LENGTH, null, $this->getLocal()->translate('maximum_length_for_name_is_number_characters', ['number' => self::MAX_TITLE_LENGTH]))], $sectionName)
                ->addField('text', TextareaType::class, [
                    'label' => 'description',
                ], null, $sectionName)
                ->addField('attachment', AttachmentType::class, [
                    'label'               => 'attachment',
                    'item_type'           => "music_song",
                    'item_id'             => (isset($this->data['id']) ? $this->data['id'] : null),
                    'current_attachments' => $this->getAttachments()
                ], [new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)], $sectionName)
                ->addField('file', FileType::class, [
                    'label'               => 'Photo',
                    'item_type'           => 'music_image',
                    'file_type'           => 'photo',
                    'preview_url'         => $this->getPreviewImage(),
                    'required'            => false,
                    'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('photo.photo_max_upload_size'))
                ], null, $sectionName);
        } else {
            $this->addField('files', MultiFileType::class, [
                'label'               => 'songs',
                'min_files'           => 1,
                'max_files'           => $this->getSetting()->getUserSetting('music.max_songs_per_upload'),
                'item_type'           => 'music_song',
                'file_type'           => 'music',
                'required'            => true,
                'max_upload_filesize' => $this->getSizeLimit($this->getSetting()->getUserSetting('music.music_max_file_size'), true)
            ], [new RequiredValidator()], $sectionName);
        }
        if ($this->albumId && !$this->editing) {
            $this->addField('album', HiddenType::class, [
                'label'         => 'album',
                'value_default' => $this->albumId
            ], null, $sectionName);
        } else if (!empty($this->albums)) {
            $this
                ->addField('album', ChoiceType::class, [
                    'label'   => 'album',
                    'options' => array_map(function ($item) {
                        return [
                            'value' => (int)$item['album_id'],
                            'label' => $item['name']
                        ];
                    }, $this->albums),
                    'order'   => 2
                ], null, $sectionName);
        }
        $this->addField('genres', HierarchyType::class, [
            'label'      => 'genre',
            'multiple'   => true,
            'rawData'    => $this->getGenres(),
            'field_maps' => [
                'field_id' => 'genre_id'
            ]
        ], null, $sectionName);

        $sectionName = 'settings';
        $this
            ->addSection($sectionName, 'settings');
        if (empty($this->data['item_id'])) {
            $this->addPrivacyField([
                'description' => 'control_who_can_see_these_song_s'
            ], $sectionName, $this->getPrivacyDefault('music.default_privacy_setting_song'));
        }
        $this->addModuleFields()
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }

    /**
     * @return mixed
     */
    public function getAlbums()
    {
        return $this->albums;
    }

    /**
     * @param mixed $albums
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

    /**
     * @return mixed
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * @param mixed $genres
     */
    public function setGenres($genres)
    {
        $this->genres = $genres;
    }

    /**
     * @return mixed
     */
    public function getAlbumId()
    {
        return $this->albumId;
    }

    /**
     * @param mixed $albumId
     */
    public function setAlbumId($albumId)
    {
        $this->albumId = $albumId;
    }

    public function buildValues()
    {
        if (!$this->isPost) {
            //Is get edit form
            if (!empty($this->data['album'])) {
                $this->data['album'] = $this->data['album']['id'];
            }
        }
        parent::buildValues();
    }

    public function getPreviewImage()
    {
        if (isset($this->data['image'])) {
            if (isset($this->data['image']['200'])) {
                return $this->data['image']['200'];
            } else if (isset($this->data['image']['image_url'])) {
                return $this->data['image']['image_url'];
            }
        }
        return null;
    }

    public function getAttachments()
    {
        return (isset($this->data['attachments']) ? $this->data['attachments'] : null);
    }
}