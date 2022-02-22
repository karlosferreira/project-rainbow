<?php

namespace Apps\Core_MobileApi\Api\Form\User;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class UpdateLanguageForm extends GeneralForm
{

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        $languageId = \Phpfox::getUserBy('language_id');
        if (!$languageId) {
            $languageId = $this->getSetting()->getAppSetting('core.default_lang_id');
        }
        $this->setTitle('language')
            ->setAction(UrlUtility::makeApiUrl('account/language'))
            ->setMethod('put')
            ->addField('language_id', ChoiceType::class, [
                'value'        => $languageId,
                'options'      => $this->getLanguagePackages(),
                'display_type' => 'inline',
                'required'     => true
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'update'
            ]);
    }

    public function getLanguagePackages()
    {
        $languages = \Phpfox::getService('language')->get(['l.user_select = 1']);
        $options = [];
        if ($languages) {
            $options = array_map(function ($lang) {
                return [
                    'value' => $lang['language_id'],
                    'label' => $this->getLocal()->translate($lang['title']),
                ];
            }, $languages);
        }
        return $options;
    }

}