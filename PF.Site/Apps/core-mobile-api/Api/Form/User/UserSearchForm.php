<?php


namespace Apps\Core_MobileApi\Api\Form\User;

use Apps\Core_MobileApi\Api\Form\SearchForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\DateType;
use Apps\Core_MobileApi\Api\Form\Type\MultiChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\RangeType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\StringLengthValidator;
use Phpfox;


class UserSearchForm extends SearchForm
{
    private $minAge;
    private $maxAge;


    public function __construct()
    {
        parent::__construct();
        $this->minAge = $this->getAgeRange();
        $this->maxAge = $this->getAgeRange(false);
    }

    /**
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function addExtraField()
    {
        if ($this->getSetting()->getUserSetting('user.can_search_user_gender')) {
            $this->addField('gender', RadioType::class, [
                'label'   => 'browse_for',
                'options' => $this->genderOptions()
            ]);
        }
        if ($this->getSetting()->getUserSetting('user.can_search_user_age')) {
            $this->addField('age', RangeType::class, [
                'label'          => 'between_ages',
                'value'          => [
                    'from' => null,
                    'to'   => null
                ],
                'min_value'      => $this->minAge,
                'max_value'      => $this->maxAge,
                'from_field_key' => 'age_from',
                'to_field_key'   => 'age_to',
                'jump_step'      => 1
            ]);
        }
        $this->addCountryField(false, 'country', null, true)
            ->addField('city', TextType::class, [
                'label'       => 'city',
                'inline'      => true,
                'placeholder' => 'city_name'
            ]);

        if ($this->getSetting()->getUserSetting('user.can_search_by_zip')) {
            $this->addField('zip_code', TextType::class, [
                'label'       => 'postal_code',
                'inline'      => true,
                'placeholder' => '- - - - - -'
            ]);
        }
        $this->buildCustomFields();
    }

    private function genderOptions()
    {
        $genders = Phpfox::getService('core')->getGenders();
        $options = [
            [
                'value' => 0,
                'label' => $this->getLocal()->translate('all_members')
            ]
        ];
        foreach ($genders as $key => $gender) {
            $options[] = [
                'value' => $key,
                'label' => $this->getLocal()->translate($gender)
            ];
        }
        return $options;
    }

    private function getAgeRange($bMin = true)
    {
        if ($bMin) {
            return Phpfox::getService('user')->age(Phpfox::getService('user')->buildAge(1, 1, Phpfox::getParam('user.date_of_birth_end')));
        }
        return Phpfox::getService('user')->age(Phpfox::getService('user')->buildAge(1, 1, Phpfox::getParam('user.date_of_birth_start')));
    }

    /**
     * @param null $sectionName
     *
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    protected function buildCustomFields($sectionName = null)
    {
        list(, $customFields) = Phpfox::getService('custom')->getForPublic('user_profile', 0, true);

        if (count($customFields)) {
            foreach ($customFields as $customGroup) {
                if (!empty($customGroup['fields'])) {
                    foreach ($customGroup['fields'] as $field) {
                        $fieldName = "custom_" . $field['field_id'];
                        switch ($field['var_type']) {
                            case "textarea":
                                $this->addField($fieldName, TextareaType::class, [
                                    'label'       => $field['phrase_var_name'],
                                    'placeholder' => 'type_something_dot'
                                ], null, $sectionName);
                                break;
                            case "text":
                                $this->addField($fieldName, TextType::class, [
                                    'label'       => $field['phrase_var_name'],
                                    'placeholder' => 'type_something_dot',
                                    'maxLength'   => 60
                                ], [new StringLengthValidator(0, 60)], $sectionName);
                                break;
                            case "date":
                                $this->addField($fieldName, DateType::class, [
                                    'label'       => $field['phrase_var_name'],
                                    'placeholder' => 'YYYY-MM-DD',
                                    'separate'    => true,
                                    'prefix'      => $fieldName . '_',
                                ], null, $sectionName);
                                break;
                            case "select":
                                $options = [];
                                foreach ($field['options'] as $key => $option) {
                                    $options[] = [
                                        'label' => $this->getLocal()->translate($option['phrase_var_name']),
                                        'value' => $option['option_id']
                                    ];
                                }
                                $this->addField($fieldName, ChoiceType::class, [
                                    'options' => $options,
                                    'label'   => $field['phrase_var_name'],
                                ], null, $sectionName);
                                break;
                            case "radio":
                                $options = [];
                                foreach ($field['options'] as $key => $option) {
                                    $options[] = [
                                        'label' => $this->getLocal()->translate($option['phrase_var_name']),
                                        'value' => $option['option_id']
                                    ];
                                }
                                $this->addField($fieldName, RadioType::class, [
                                    'options' => $options,
                                    'label'   => $field['phrase_var_name'],
                                ], null, $sectionName);
                                break;
                            case "multiselect":
                            case "checkbox":
                                $options = [];
                                foreach ($field['options'] as $key => $option) {
                                    $options[] = [
                                        'label' => $this->getLocal()->translate($option['phrase_var_name']),
                                        'value' => $option['option_id']
                                    ];
                                }
                                $this->addField($fieldName, MultiChoiceType::class, [
                                    'options' => $options,
                                    'label'   => $field['phrase_var_name'],
                                ], null, $sectionName);
                                break;
                        }
                    }
                }
            }
        }
    }
}