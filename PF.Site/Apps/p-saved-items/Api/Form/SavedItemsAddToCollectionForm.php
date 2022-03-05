<?php

namespace Apps\P_SavedItems\Api\Form;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\ClickableType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Phpfox;

class SavedItemsAddToCollectionForm extends GeneralForm
{
    private $collections;
    private $savedId;

    /**
     * @param null $options
     * @param array $data
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        if (empty($this->collections)) {
            $this->setCollections();
        }

        $this->addField('add_collection', ClickableType::class, [
            'label' => '',
            'value' => '+ ' . _p('saveditems_create_new_collection_uc_first'),
            'action' => 'custom_action',
            'params' => [
                'actionName' => 'saveditems-collection/create_collection'
            ],
            'noSeparator' => true,
        ]);

        if (!empty($this->collections)) {
            $this->addField('collection', ChoiceType::class, [
                'label' => '',
                'display_type' => 'inline',
                'multiple' => true,
                'options' => $this->collections,
            ]);
        }

        $this->addField('id', HiddenType::class, [
            'value' => $this->savedId,
        ]);
    }

    private function setCollections()
    {
        $myCollections = Phpfox::getService('saveditems.collection')->getMyCollections();
        if (!empty($myCollections)) {
            $parsed = [];
            foreach ($myCollections as $collection) {
                $parsed[] = [
                    'label' => $collection['name'],
                    'value' => $collection['collection_id']
                ];
            }
            $this->collections = $parsed;
        }

    }

    public function setSavedId($savedId)
    {
        $this->savedId = (int)$savedId;
    }
}