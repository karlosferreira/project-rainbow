<?php

namespace Apps\Core_MobileApi\Api\Form\ActivityPoint;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\ActivityPointPackageType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class ActivityPointForm extends GeneralForm
{
    protected $packages;
    protected $tags;
    protected $action = "blog";

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
        $this->addField('point_package', ActivityPointPackageType::class, [
            'required' => true,
            'options' => $this->getPackages(),
            'display_type' => 'inline',
            'disable_detail' => true,
            'label' => 'activitypoint_points_package'
        ]);
        $this->addField('submit', SubmitType::class, [
            'label' => 'publish',
            'value' => 1
        ]);
    }

    /**
     * @return mixed
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param mixed $packages
     */
    public function setPackages($packages)
    {
        $this->packages = $packages;
    }


}