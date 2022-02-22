<?php


namespace Apps\Core_MobileApi\Api\Form\Music;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;


class MusicPlaylistAddSongForm extends GeneralForm
{

    protected $action = "mobile/music-playlist/song";
    private $playlists = [];
    private $songId;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this
            ->addField('playlist_id', ChoiceType::class, [
                'label'        => '',
                'display_type' => 'inline',
                'multiple'     => true,
                'options'      => $this->getPlaylistsOption(),
            ])
            ->addField('song_id', HiddenType::class, [
                'value' => $this->getSongId()
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
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

    /**
     * @return mixed
     */
    public function getPlaylistsOption()
    {
        $playlists = $this->getPlaylists();
        $options = [];
        if (count($playlists)) {
            foreach ($playlists as $playlist) {
                $options[] = [
                    'value' => (int)$playlist['playlist_id'],
                    'label' => $playlist['name']
                ];
            }
        }
        return $options;
    }

    /**
     * @param mixed $playlists
     */
    public function setPlaylists($playlists)
    {
        $this->playlists = $playlists;
    }

    /**
     * @param mixed $songId
     */
    public function setSongId($songId)
    {
        $this->songId = $songId;
    }

    /**
     * @return mixed
     */
    public function getSongId() {
        return $this->songId;
    }

    /**
     * @return mixed
     */
    public function getPlaylists() {
        return $this->playlists;
    }

}