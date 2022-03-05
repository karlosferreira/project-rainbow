<?php


namespace Apps\Core_MobileApi\Api\Form\Marketplace;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\CheckboxType;
use Apps\Core_MobileApi\Api\Form\Type\FriendPickerType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;

class MarketplaceInviteForm extends GeneralForm
{
    protected $action = "mobile/marketplace-invite";
    protected $editing = false;
    protected $itemId;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this->addField('user_ids', FriendPickerType::class, [
            'label'     => 'invite_friends',
            'item_id'   => $this->itemId,
            'item_type' => 'marketplace'
        ])
            ->addField('listing_id', HiddenType::class, [
                'value'    => $this->itemId,
                'required' => true
            ], [new RequiredValidator()])
            ->addField('emails', TextType::class, [
                'label'          => 'invite_people_via_email',
                'autoCapitalize' => 'none',
                'placeholder'    => 'enter_email_address'
            ])
            ->addField('invite_from', CheckboxType::class, [
                'label'         => 'send_from_my_own_email_address',
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
                'value_default' => 0
            ])
            ->addField('personal_message', TextType::class, [
                'label'       => 'add_a_personal_message',
                'placeholder' => 'enter_your_message'
            ]);
        $this->addField('submit', SubmitType::class, [
            'label' => 'save'
        ]);
    }

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @param $edit
     *
     * @codeCoverageIgnore
     * @todo not used anywhere.
     */
    public function setEditing($edit)
    {
        $this->editing = $edit;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     * @todo not used anywhere.
     */
    public function getEditing()
    {
        return $this->editing;
    }

}