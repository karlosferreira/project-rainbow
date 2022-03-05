<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class QuizUserResultResource extends ResourceBase
{
    const RESOURCE_NAME = "quiz-user-result";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "quiz_id";

    /**
     * @var UserResource
     */

    public $index;
    public $total_correct;
    public $total_question;
    public $percent_correct;
    public $user;

    public $user_result;

    /**
     * PollResource constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }


    public function getUserResult()
    {
        if (!empty($this->rawData['user_results'])) {
            $results = [];
            foreach ($this->rawData['user_results'] as $user_result) {
                $results[] = [
                    'question'            => $user_result['questionText'],
                    'question_id'         => $user_result['questionId'],
                    'user_answer_text'    => $user_result['userAnswerText'],
                    'user_answer_id'      => ResourceMetadata::convertValue($user_result['userAnswer'], ['type' => ResourceMetadata::INTEGER]),
                    'correct_answer_text' => $user_result['correctAnswerText'],
                    'correct_answer_id'   => ResourceMetadata::convertValue($user_result['correctAnswer'], ['type' => ResourceMetadata::INTEGER]),
                    'user_answer_date'    => $this->convertDatetime($user_result['time_stamp'])
                ];
            }
            return $results;
        }
        return null;
    }

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'urls.base'     => 'mobile/quiz-result',
            'schema'        => [
                'idAttribute' => 'index'
            ],
            'list_view'     => [
                'item_view' => 'quiz_user_result',
            ],
            'detail_view'   => [
                'apiUrl' => 'mobile/quiz-result/:quiz_id',
            ],
            'app_menu'      => [],
        ]);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('total_correct', ['type' => ResourceMetadata::INTEGER])
            ->mapField('total_question', ['type' => ResourceMetadata::INTEGER])
            ->mapField('percent_correct', ['type' => ResourceMetadata::FLOAT]);
    }
}
