<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 3:43 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


class QuizQuestionType extends GeneralType
{
    protected $componentName = "QuizQuestion";

    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            'returnKeyType',
            'min_questions',
            'max_questions',
            'min_answers',
            'max_answers',
        ];
    }

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        $minQuest = $this->getAttr('min_questions');
        $maxQuest = $this->getAttr('max_questions');
        $minAns = $this->getAttr('min_answers');
        $maxAns = $this->getAttr('max_answers');
        $value = $this->getValue();
        $totalQuest = isset($value) ? count($value) : 0;
        //Check max min
        if ($minQuest < 0 || $maxQuest < $minQuest || $minAns < 0 || $maxAns < $minAns
            || $totalQuest < $minQuest || $totalQuest > $maxQuest
        ) {
            return false;
        }
        //Check value format
        foreach ($value as $item) {
            //Check question
            if ((!empty($item['question_id']) && !is_numeric($item['question_id'])) || empty($item['question']) || empty($item['answers'])
            ) {
                return false;
            }
            $hasCorrect = false;
            $totalAns = count($item['answers']);
            if ($totalAns < $minAns || $totalAns > $maxAns) {
                return false;
            }
            foreach ($item['answers'] as $data) {
                //Check answers
                if ((!empty($data['answer_id']) && !is_numeric($data['answer_id'])) || empty($data['answer'])) {
                    return false;
                }
                //2 correct answers is invalid
                if (isset($data['is_correct'])) {
                    if ((int)$data['is_correct'] > 0 && $hasCorrect) {
                        return false;
                    }
                    if ((int)$data['is_correct'] > 0) {
                        $hasCorrect = true;
                    }
                }
            }
            if (!$hasCorrect) {
                return false;
            }
        }
        return true;
    }

    public function getMetaValueFormat()
    {
        return "{'question': 'Question 1','question_id':0,'answers':[{'answer': 'Answer 1A', 'answer_id': 0, 'is_correct': false}, {'answer': 'Answer 1B', 'answer_id': 0, 'is_correct': false}}";
    }

    public function getMetaDescription()
    {
        return "Multiple question and answer";
    }
}