<?php


namespace Apps\Core_MobileApi\Api\Form\Page;

use Apps\Core_MobileApi\Api\Form\SearchForm;


class PageSearchForm extends SearchForm
{
    public function getSortOptions()
    {
        return [
            [
                'value' => 'latest',
                'label' => $this->getLocal()->translate('latest')
            ],
            [
                'value' => 'most_liked',
                'label' => $this->getLocal()->translate('most_liked')
            ],
        ];
    }
}