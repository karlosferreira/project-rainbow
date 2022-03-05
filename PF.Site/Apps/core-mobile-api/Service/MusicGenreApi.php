<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\MusicGenreResource;
use Apps\Core_Music\Service\Genre\Genre;
use Apps\Core_Music\Service\Genre\Process;
use Phpfox;

class MusicGenreApi extends AbstractResourceApi
{
    /**
     * @var Genre
     */
    private $genreService;

    /**
     * @var Process
     */
    private $processService;

    public function __construct()
    {
        parent::__construct();
        $this->genreService = Phpfox::getService('music.genre');
        $this->processService = Phpfox::getService('music.genre.process');
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'song_id'
        ])->setAllowedTypes('song_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();
        $where = ['AND mg.is_active = 1'];
        if (!empty($params['song_id'])) {
            $where[] = 'AND mgd.song_id = ' . (int)$params['song_id'];
            $this->database()->join(':music_genre_data', 'mgd', 'mgd.genre_id = mg.genre_id');
        }

        $result = $this->database()->select('mg.*')
            ->from(':music_genre', 'mg')
            ->where($where)
            ->group('mg.genre_id')
            ->order('mg.ordering')
            ->execute('getSlaveRows');
        $this->processRows($result);
        return $this->success($result);
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $genre = $this->loadResourceById($id);
        if (empty($genre)) {
            return $this->notFoundError();
        }
        return $this->success(MusicGenreResource::populate($genre)->toArray());

    }

    function create($params)
    {
        // TODO: Implement create() method.
    }

    function update($params)
    {
        // TODO: Implement update() method.
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    function delete($params)
    {
        $params = $this->resolver
            ->setDefined([
                'delete_type', 'genre'
            ])
            ->setAllowedValues('delete_type', ['1', '2', '3'])
            ->setAllowedTypes('genre', 'int')
            ->setRequired(['id'])
            ->resolve(array_merge(['delete_type' => 1], $params))
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::isAdmin()) {
            return $this->permissionError();
        }
        $genre = $this->loadResourceById($params['id']);
        if (empty($genre)) {
            return $this->notFoundError();
        }
        //delete type
        if ($params['delete_type'] == 3 && !$params['genre']) {
            return $this->missingParamsError(['genre']);
        }

        $aVals = [
            'delete_type'  => $params['delete_type'],
            'new_genre_id' => $params['genre']
        ];
        $this->processService->delete($params['id'], $aVals);
        return $this->success([], [], $this->getLocalization()->translate('successfully_deleted_genres'));
    }

    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $returnResource = false)
    {
        $genre = $this->database()->select('*')
            ->from(':music_genre')
            ->where('genre_id = ' . (int)$id)
            ->execute('getSlaveRow');
        return $genre;
    }

    public function getBySongId($id)
    {
        $where = ['AND gd.song_id = ' . (int)$id];
        $result = $this->database()->select('g.*')
            ->from(':music_genre', 'g')
            ->join(':music_genre_data', 'gd', 'g.genre_id = gd.genre_id')
            ->where($where)
            ->group('g.genre_id')
            ->execute('getRows');
        $result = array_map(function ($item) {
            return MusicGenreResource::populate($item)->displayShortFields()->toArray();
        }, $result);
        return $result;
    }

    public function processRow($item)
    {
        return MusicGenreResource::populate($item)->displayShortFields()->toArray();
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }
}