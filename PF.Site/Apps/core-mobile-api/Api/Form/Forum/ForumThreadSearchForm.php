<?php


namespace Apps\Core_MobileApi\Api\Form\Forum;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;


class ForumThreadSearchForm extends GeneralForm
{
    protected $forums;
    protected $forumId;

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        $this->addField('q', TextType::class, [
            'label'       => 'keywords',
            'placeholder' => 'search_this_forum',
        ])->addField('sort', RadioType::class, [
            'label'         => 'sort',
            'value_default' => 'time_stamp',
            'options'       => $this->getSortOptions()
        ])->addField('sort_type', RadioType::class, [
            'label'         => 'sort_type',
            'value_default' => 'desc',
            'options'       => [
                [
                    'value' => 'desc',
                    'label' => $this->getLocal()->translate('descending')
                ],
                [
                    'value' => 'asc',
                    'label' => $this->getLocal()->translate('ascending')
                ],
            ]
        ])->addField('author', TextType::class, [
            'label'       => 'author',
            'placeholder' => 'search_for_author'
        ])->addField('forums', HierarchyType::class, [
            'label'      => 'find_in_forum',
            'rawData'    => $this->getForums(),
            'field_maps' => [
                'field_id'  => 'forum_id',
                'field_sub' => 'sub_forum'
            ],
            'order'      => 2,
            'multiple'   => true,
            'value_type' => 'mixed',
            'value'      => $this->forumId ? [$this->forumId] : []
        ])->addField('days_prune', ChoiceType::class, [
            'label'         => 'from',
            'value_default' => '-1',
            'options'       => $this->getDaysPrune()
        ])->addField('view', HiddenType::class)->addModuleFields();
    }

    /**
     * @return mixed
     */
    public function getForums()
    {
        return $this->forums;
    }

    /**
     * @param mixed $forums
     */
    public function setForums($forums)
    {
        $this->forums = $forums;
    }

    private function getDaysPrune()
    {
        return [
            [
                'value' => '-1',
                'label' => $this->getLocal()->translate('beginning')
            ],
            [
                'value' => '1',
                'label' => $this->getLocal()->translate('last_day')
            ],
            [
                'value' => '2',
                'label' => $this->getLocal()->translate('last_2_days')
            ],
            [
                'value' => '7',
                'label' => $this->getLocal()->translate('last_week')
            ],
            [
                'value' => '10',
                'label' => $this->getLocal()->translate('last_10_days')
            ],
            [
                'value' => '14',
                'label' => $this->getLocal()->translate('last_2_weeks')
            ],
            [
                'value' => '30',
                'label' => $this->getLocal()->translate('last_month')
            ],
            [
                'value' => '45',
                'label' => $this->getLocal()->translate('last_45_days')
            ],
            [
                'value' => '60',
                'label' => $this->getLocal()->translate('last_2_months')
            ],
            [
                'value' => '75',
                'label' => $this->getLocal()->translate('last_75_days')
            ],
            [
                'value' => '100',
                'label' => $this->getLocal()->translate('last_100_days')
            ],
            [
                'value' => '365',
                'label' => $this->getLocal()->translate('last_year')
            ],
        ];
    }

    public function getSortOptions()
    {
        return [
            [
                'value' => 'time_stamp',
                'label' => $this->getLocal()->translate('post_time')
            ],
            [
                'value' => 'full_name',
                'label' => $this->getLocal()->translate('author')
            ],
            [
                'value' => 'total_post',
                'label' => $this->getLocal()->translate('total_replies')
            ],
            [
                'value' => 'title',
                'label' => $this->getLocal()->translate('subject')
            ],
            [
                'value' => 'total_view',
                'label' => $this->getLocal()->translate('total_views')
            ],
        ];
    }

    /**
     * @param mixed $forumId
     */
    public function setForumId($forumId)
    {
        $this->forumId = $forumId;
    }


}