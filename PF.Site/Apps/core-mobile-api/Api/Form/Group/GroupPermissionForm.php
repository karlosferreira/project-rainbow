<?php


namespace Apps\Core_MobileApi\Api\Form\Group;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;

class GroupPermissionForm extends GeneralForm
{

    protected $action = "group-permission";
    protected $permissions;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this
            ->addPermsField()
            ->addField('submit', SubmitType::class, [
                'label' => 'update',
            ]);
    }

    /**
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param mixed $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return $this
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function addPermsField()
    {
        $permissions = $this->getPermissions();
        $hiddenPermissions = [
            'groups.view_browse_widgets',
        ];
        foreach ($permissions as $permission) {
            if ((!empty($permission['id']) && in_array($permission['id'], $hiddenPermissions))
                || !empty($permission['is_hidden'])) {
                $this->addField(str_replace('.', '__', $permission['id']), HiddenType::class, [
                    'value' => $permission['is_active']
                ]);
            } else {
                $this->addField(str_replace('.', '__', $permission['id']), RadioType::class, [
                    'label'    => $permission['phrase'],
                    'required' => true,
                    'options'  => [
                        [
                            'label' => $this->getLocal()->translate('members_only'),
                            'value' => 1
                        ],
                        [
                            'label' => $this->getLocal()->translate('admins_only'),
                            'value' => 2
                        ],
                    ],
                    'value'    => $permission['is_active']
                ], [new NumberRangeValidator(0, 2)]);
            }
        }
        return $this;
    }
}