<?php

namespace Apps\P_SavedItems\Api\Form;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class SavedItemsCollectionForm extends GeneralForm
{
    /**
     * @param null $options
     * @param array $data
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this->addField('name', TextType::class, [
            'label' => 'saveditems_collection_name',
            'placeholder' => 'saveditems_maximum_128_characters',
            'required' => true
        ], [new StringLengthValidator(1, 250)], null)->addField('submit', SubmitType::class, [
                'label' => 'publish',
                'value' => 1
            ]);
    }
}