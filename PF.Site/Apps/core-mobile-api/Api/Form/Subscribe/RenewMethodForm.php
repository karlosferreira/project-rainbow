<?php


namespace Apps\Core_MobileApi\Api\Form\Subscribe;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ChoiceType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class RenewMethodForm extends GeneralForm
{

    protected $action = "mobile/subscribe/renew-method";
    private $methods = [];
    private $purchaseId;

    /**
     * @param null $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this->addField('renew_type', ChoiceType::class, [
            'label'         => 'subscribe_select_renew_method_title',
            'options'       => $this->methods,
            'required'      => true,
            'display_type'  => 'inline',
            'enable_search' => false,
            'description'   => 'subscribe_select_method_for_renewing_subscription'
        ])->addField('purchase_id', HiddenType::class, [
            'value'    => $this->purchaseId,
            'required' => true
        ])->addField('submit', SubmitType::class, [
            'label' => 'save'
        ]);
    }

    /**
     * @param mixed $purchaseId
     */
    public function setPurchaseId($purchaseId)
    {
        $this->purchaseId = $purchaseId;
    }

    /**
     * @param array $methods
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

}