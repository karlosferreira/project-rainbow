<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 3:43 PM
 */

namespace Apps\Core_MobileApi\Api\Form\Type;


class PollAnswerType extends GeneralType
{
    protected $componentName = "PollAnswer";

    public function getAvailableAttributes()
    {
        return [
            'label',
            'description',
            'value',
            'returnKeyType',
            'min_answers',
            'max_answers'
        ];
    }

    public function isValid()
    {
        if (!parent::isValid()) {
            return false;
        }
        $minAns = $this->getAttr('min_answers');
        $maxAns = $this->getAttr('max_answers');
        $value = $this->getValue();
        $totalVal = is_array($value) ? count($value) : 0;
        //Check max min
        if ($minAns < 0 || $maxAns < $minAns || $totalVal < $minAns || $totalVal > $maxAns) {
            return false;
        }
        //Check value format
        foreach ($value as $item) {
            if ((!empty($item['id']) && !is_numeric($item['id'])) || (!empty($item['order']) && !is_numeric($item['order'])) || empty($item['value'])) {
                return false;
            }
        }
        return true;
    }

    public function getMetaValueFormat()
    {
        return "[{'value': 'Answer 1 text','order': 1,'id': 0}, {'value': 'Answer 2 text','order': 2,'id': 0} //New answer have id = 0 and opposite with old answer";
    }

    public function getMetaDescription()
    {
        return "Multiple answer";
    }
}