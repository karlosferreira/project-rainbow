<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class PollAnswerResource extends ResourceBase
{
    const RESOURCE_NAME = "poll-answer";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "answer_id";

    public $answer;
    public $total_votes;
    public $ordering;
    public $voted;

    public $vote_percentage;
    public $some_votes;

    /**
     * PollResource constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getAnswer()
    {
        return $this->parse->parseOutput($this->answer);
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    public function getSomeVotes()
    {
        if (!empty($this->some_votes)) {
            $some_votes = [];
            foreach ($this->some_votes as $some_vote) {
                $some_votes[] = UserResource::populate($some_vote)->toArray();
            }
            return $some_votes;
        }
        return null;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'search_input'  => false,
            'schema'        => [
            ],
            'list_view'     => [
                'item_view'     => 'poll_answer',
                'noItemMessage' => $l->translate('no_results'),
            ],
        ]);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('total_votes', ['type' => ResourceMetadata::INTEGER])
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('vote_percentage', ['type' => ResourceMetadata::FLOAT]);
    }
}