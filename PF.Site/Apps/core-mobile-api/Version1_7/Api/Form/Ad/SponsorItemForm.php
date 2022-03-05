<?php

namespace Apps\Core_MobileApi\Version1_7\Api\Form\Ad;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\DateTimeType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\IntegerType;
use Apps\Core_MobileApi\Api\Form\Type\MultiChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\RangeType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;
use Phpfox;

class SponsorItemForm extends GeneralForm
{
    protected $action = "ad";

    protected $withoutPaying = false;

    protected $selectedGender = [];
    protected $selectedLanguage = [];

    protected $costInfo;

    protected $sponsorItem;

    protected $section;
    protected $itemId;
    protected $sponsorFeed;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $section = 'info';
        $this->addField('id', HiddenType::class, [
            'value'    => $this->getItemId(),
            'required' => true
        ])->addField('section', HiddenType::class, [
            'value'    => $this->getSection(),
            'required' => true
        ])->addField('is_sponsor_feed', HiddenType::class, [
            'value' => $this->getSponsorFeed()
        ]);

        $this->addSection($section, $section);
        $aSponsorItem = $this->getSponsorItem();
        $this->addField('name', TextType::class, [
            'required' => true,
            'label'    => 'ad_name',
            'value'    => isset($aSponsorItem['title']) ? $this->getParse()->cleanOutput($aSponsorItem['title']) : ''
        ], [], $section);
        $section = 'target';
        $this->addSection($section, $section);
        $this
            ->addField('gender', MultiChoiceType::class, [
                'options' => $this->genderOptions(),
                'label'   => 'gender',
                'value'   => $this->selectedGender
            ], [], $section)
            ->addField('country_iso_custom', MultiChoiceType::class, [
                'label'   => 'country',
                'options' => $this->getAllCountries()
            ], [], $section)
            ->addField('city_location', TextType::class, [
                'label'       => 'city',
                'placeholder' => 'city_name',
                'description' => 'better_ads_separate_multiple_cities_by_a_comma'
            ], [], $section)
            ->addField('postal_code', TextType::class, [
                'label'       => 'postal_code',
                'placeholder' => '- - - - - -',
                'description' => 'better_ads_separate_multiple_postal_codes_by_a_comma'
            ], [], $section)
            ->addField('age', RangeType::class, [
                'label'          => 'between_ages',
                'value'          => [
                    'from' => null,
                    'to'   => null
                ],
                'min_value'      => 1,
                'max_value'      => 120,
                'from_field_key' => 'age_from',
                'to_field_key'   => 'age_to',
                'jump_step'      => 1
            ], [], $section)
            ->addField('language_id', MultiChoiceType::class, [
                'options'     => $this->getLanguagePackages(),
                'label'       => 'languages',
                'value'       => $this->selectedLanguage,
                'description' => $this->getLocal()->translate('better_ads_notice_choose_languages', ['title' => strtolower($this->getLocal()->translate('better_ads_sponsorship'))])
            ], [], $section);

        $section = 'detail';
        $this->addSection($section, $section);

        $this->addField('start_time', DateTimeType::class, [
            'label'       => 'start_time',
            'placeholder' => 'select_time',
            'required'    => true,
            'description' => 'better_ads_note_the_time_is_set_to_your_registered_time_zone'
        ], [new RequiredValidator()], $section);
        if (!$this->withoutPaying) {
            $this->addField('has_total_view', HiddenType::class, [
                'value' => 1
            ]);
        } else {
            $this
                ->addField('end_option', CheckboxType::class, [
                    'label'         => 'limit_end_time',
                    'value_default' => 0,
                    'options'       => [
                        [
                            'value' => 0,
                            'label' => $this->getLocal()->translate('no')
                        ],
                        [
                            'value' => 1,
                            'label' => $this->getLocal()->translate('yes')
                        ],
                    ],
                ], [], $section)
                ->addField('end_time', DateTimeType::class, [
                    'label'        => 'end_time',
                    'placeholder'  => 'select_time',
                    'description'  => 'leave_it_empty_if_you_dont_want_set_an_end_time',
                    'hidden_by'    => '!end_option',
                    'hidden_value' => ['1', 1]
                ], [], $section)
                ->addField('has_total_view', CheckboxType::class, [
                    'label'         => 'limit_total_view',
                    'value_default' => 0,
                    'options'       => [
                        [
                            'value' => 0,
                            'label' => $this->getLocal()->translate('no')
                        ],
                        [
                            'value' => 1,
                            'label' => $this->getLocal()->translate('yes')
                        ],
                    ],
                ], [], $section);
        }
        $this
            ->addField('total_view', IntegerType::class, [
                'label'         => 'number_of_views',
                'placeholder'   => '',
                'value_default' => 1000,
                'description'   => $this->isWithoutPaying() ? '' : $this->getLocal()->translate('sponsorship_cost_per_each_thousand_views_is_cost', ['cost' => $this->getCostInfo()]),
                'hidden_by'     => '!has_total_view',
                'hidden_value'  => ['1', 1]
            ], [new NumberRangeValidator(1)], $section);
        $this->addField('submit', SubmitType::class, [
            'label' => 'save',
            'value' => 1
        ]);
    }

    private function isEdit()
    {
        return !empty($this->data['id']);
    }

    private function genderOptions()
    {
        $genders = \Phpfox::getService('core')->getGenders();
        $options = [];
        $options[] = [
            'value' => '0',
            'label' => $this->getLocal()->translate('any')
        ];
        $this->selectedGender[] = '0';
        foreach ($genders as $key => $gender) {
            $options[] = [
                'value' => (string)$key,
                'label' => $this->getLocal()->translate($gender)
            ];
            $this->selectedGender[] = (string)$key;
        }
        $this->selectedGender[] = '127';
        $options[] = [
            'value' => '127',
            'label' => $this->getLocal()->translate('better_ads_custom_genders')
        ];
        return $options;
    }

    private function getAllCountries()
    {
        $countries = $this->getLocal()->getAllCountry();
        $allCountries = [];
        $allCountries[] = [
            'value' => '',
            'label' => $this->getLocal()->translate('anywhere'),
        ];
        foreach ($countries as $ios => $country) {
            if (Phpfox::isPhrase('translate_country_iso_' . strtolower($ios))) {
                $country = $this->getLocal()->translate('translate_country_iso_' . strtolower($ios));
            }
            $allCountries[] = [
                'value' => $ios,
                'label' => str_replace('&#039;', '\'', $this->getParse()->cleanOutput($country))
            ];
        }
        return $allCountries;
    }

    public function getLanguagePackages()
    {
        $languages = \Phpfox::getService('language')->get(['l.user_select = 1']);
        $options = [];
        if ($languages) {
            $options = array_map(function ($lang) {
                $this->selectedLanguage[] = $lang['language_id'];
                return [
                    'value' => $lang['language_id'],
                    'label' => $this->getLocal()->translate($lang['title']),
                ];
            }, $languages);
        }
        return $options;
    }

    /**
     * @return bool
     */
    public function isWithoutPaying()
    {
        return $this->withoutPaying;
    }

    /**
     * @param bool $withoutPaying
     */
    public function setWithoutPaying($withoutPaying)
    {
        $this->withoutPaying = $withoutPaying;
    }

    /**
     * @return mixed
     */
    public function getCostInfo()
    {
        return $this->costInfo;
    }

    /**
     * @param mixed $costInfo
     */
    public function setCostInfo($costInfo)
    {
        $this->costInfo = $costInfo;
    }

    /**
     * @return mixed
     */
    public function getSponsorItem()
    {
        return $this->sponsorItem;
    }

    /**
     * @param mixed $sponsorItem
     */
    public function setSponsorItem($sponsorItem)
    {
        $this->sponsorItem = $sponsorItem;
    }

    /**
     * @param mixed $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param mixed $sponsorFeed
     */
    public function setSponsorFeed($sponsorFeed)
    {
        $this->sponsorFeed = $sponsorFeed;
    }

    /**
     * @return mixed
     */
    public function getSponsorFeed()
    {
        return $this->sponsorFeed;
    }
}