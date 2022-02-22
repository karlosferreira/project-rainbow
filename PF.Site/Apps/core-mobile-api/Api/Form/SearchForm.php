<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 14/6/18
 * Time: 10:32 AM
 */

namespace Apps\Core_MobileApi\Api\Form;


use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;

class SearchForm extends GeneralForm
{
    protected $whenOptions;
    protected $whenDefault;
    protected $allowWhen = true;
    protected $sortOptions;
    protected $sortDefault;
    protected $allowSort = true;

    public function __construct()
    {
        $this->whenDefault = 'all-time';
        $this->sortDefault = 'latest';
    }

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this->addField('q', TextType::class, [
            'label'       => 'keywords',
            'autoFocus'   => true,
            'placeholder' => 'search_dot'
        ]);
        if ($this->allowSort) {
            $this->addField('sort', RadioType::class, [
                'label'         => 'sort',
                'options'       => $this->getSortOptions(),
                'value_default' => $this->getSortDefault()
            ]);
        }
        if ($this->allowWhen) {
            $this->addField('when', RadioType::class, [
                'label'         => 'when',
                'options'       => $this->getWhenOptions(),
                'value_default' => $this->getWhenDefault()
            ]);
        }

        //Add more field base on each apps
        $this->addExtraField();

        $this->addField('submit', SubmitType::class, [
            'label' => 'search'
        ]);
    }

    /**
     * @return array
     */
    public function getWhenOptions()
    {
        if (empty($this->whenOptions)) {
            $this->whenOptions = [
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
            ];
        }
        return $this->whenOptions;
    }

    /**
     * @param array $whenOptions
     *
     * @codeCoverageIgnore
     */
    public function setWhenOptions($whenOptions)
    {
        $this->whenOptions = $whenOptions;
    }

    /**
     * @return array
     */
    public function getSortOptions()
    {
        if (empty($this->sortOptions)) {
            $this->sortOptions = [
                [
                    'value' => 'latest',
                    'label' => $this->getLocal()->translate('latest')
                ],
                [
                    'value' => 'most_viewed',
                    'label' => $this->getLocal()->translate('most_viewed')
                ],
                [
                    'value' => 'most_liked',
                    'label' => $this->getLocal()->translate('most_liked')
                ],
                [
                    'value' => 'most_discussed',
                    'label' => $this->getLocal()->translate('most_discussed')
                ],
            ];
        }
        return $this->sortOptions;
    }

    /**
     * @param array $sortOptions
     *
     * @codeCoverageIgnore
     */
    public function setSortOptions($sortOptions)
    {
        $this->sortOptions = $sortOptions;
    }

    /**
     * @return string
     */
    public function getWhenDefault()
    {
        return $this->whenDefault;
    }

    /**
     * @param string $whenDefault
     *
     * @codeCoverageIgnore
     */
    public function setWhenDefault($whenDefault)
    {
        $this->whenDefault = $whenDefault;
    }

    /**
     * @return string
     */
    public function getSortDefault()
    {
        return $this->sortDefault;
    }

    /**
     * @param string $sortDefault
     *
     * @codeCoverageIgnore
     */
    public function setSortDefault($sortDefault)
    {
        $this->sortDefault = $sortDefault;
    }

    /**
     * @param bool $allowWhen
     */
    public function setAllowWhen($allowWhen)
    {
        $this->allowWhen = $allowWhen;
    }

    /**
     * @param mixed $allowSort
     */
    public function setAllowSort($allowSort)
    {
        $this->allowSort = $allowSort;
    }

    public function addExtraField()
    {

    }
}