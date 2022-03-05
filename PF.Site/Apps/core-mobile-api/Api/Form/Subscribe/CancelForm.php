<?php


namespace Apps\Core_MobileApi\Api\Form\Subscribe;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\ClickableType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class CancelForm extends GeneralForm
{

    protected $action = "mobile/subscription/cancel";
    private $reasons;
    private $purchaseId;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        if (!empty($this->getReasons())) {
            $this->addField('reason', ChoiceType::class, [
                'label'        => 'can_you_tell_us_why',
                'options'      => $this->reasons,
                'required'     => true,
                'display_type' => 'inline'
            ]);
        } else {
            $this->addField('reason', ClickableType::class, [
                'label' => '',
                'value' => 'can_you_tell_us_why',
            ]);
        }
        $this->addField('purchase_id', HiddenType::class, [
            'value'    => $this->purchaseId,
            'required' => true
        ])
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }

    /**
     * @return mixed
     */
    public function getReasons()
    {
        if (!$this->reasons) {
            $reasons = \Phpfox::getService('subscribe.reason')->getReasonForCancelSubscription();
            if (count($reasons)) {
                foreach ($reasons as $reason) {
                    $this->reasons[] = [
                        'value' => $reason['reason_id'],
                        'label' => $this->getLocal()->translate($reason['title'])
                    ];
                }
            } else {
                $this->reasons[] = [
                    'value' => 0,
                    'label' => $this->getLocal()->translate('no_reason')
                ];
            }
        }
        return $this->reasons;
    }

    /**
     * @param mixed $reasons
     */
    public function setReasons($reasons)
    {
        $this->reasons = $reasons;
    }

    /**
     * @param mixed $purchaseId
     */
    public function setPurchaseId($purchaseId)
    {
        $this->purchaseId = $purchaseId;
    }


}