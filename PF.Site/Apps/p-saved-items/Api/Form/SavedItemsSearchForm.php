<?php

namespace Apps\P_SavedItems\Api\Form;

use Apps\Core_MobileApi\Api\Form\SearchForm;

class SavedItemsSearchForm extends SearchForm
{
    public function getSortOptions()
    {
        $sortOptions = [
            [
                'value' => 'latest',
                'label' => 'saveditems_latest'
            ],
            [
                'value' => 'oldest',
                'label' => 'saveditems_oldest'
            ],
            [
                'value' => 'unopened',
                'label' => 'saveditems_unopened'
            ],
            [
                'value' => 'opened',
                'label' => 'saveditems_opened'
            ],
        ];

        return $sortOptions;
    }
}