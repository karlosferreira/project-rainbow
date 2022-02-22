<?php


namespace Apps\Core_MobileApi\Api\Form\Event;

use Apps\Core_MobileApi\Api\Form\SearchForm;

class EventSearchForm extends SearchForm
{

    public function addExtraField()
    {
//        $this->addCountryField(false, 'country');
    }

    public function getWhenOptions()
    {
        return [
            [
                'value' => 'all-time',
                'label' => $this->getLocal()->translate('all_time')
            ],
            [
                'value' => 'today',
                'label' => $this->getLocal()->translate('today')
            ],
            [
                'value' => 'this-week',
                'label' => $this->getLocal()->translate('this_week')
            ],
            [
                'value' => 'this-month',
                'label' => $this->getLocal()->translate('this_month')
            ],
            [
                'value' => 'upcoming',
                'label' => $this->getLocal()->translate('upcoming')
            ],
            [
                'value' => 'ongoing',
                'label' => $this->getLocal()->translate('ongoing')
            ],
        ];
    }
}