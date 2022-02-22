<?php


namespace Apps\Core_MobileApi\Api\Resource;

class PollResultResource extends ResourceBase
{
    const RESOURCE_NAME = "poll-result";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "answer_id";

    /**
     * @var UserResource
     */

    public $user;

    public $item_id;


    public function getItemId()
    {
        return $this->rawData['answer_id'] . '.' . $this->rawData['user_id'];
    }

    /**
     * Get detail url
     *
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'search_input'  => [
                'can_search' => false,
            ],
            'schema'        => [
                'idAttribute' => 'item_id'
            ],
            'list_view'     => [
                'item_view'     => 'poll_result',
                'noItemMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results')
                ]
            ]
        ]);
    }
}