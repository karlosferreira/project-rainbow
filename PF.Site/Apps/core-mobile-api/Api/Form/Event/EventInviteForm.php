<?php


namespace Apps\Core_MobileApi\Api\Form\Event;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\FriendPickerType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\RadioType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\NumberRangeValidator;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;

class EventInviteForm extends GeneralForm
{
    protected $action = "mobile/event-invite";
    protected $editing = false;
    protected $itemId;

    /**
     * @param null  $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        if (!$this->getEditing()) {
            $this->addField('user_ids', FriendPickerType::class, [
                'label'     => 'invite_friends',
                'item_id'   => $this->itemId,
                'item_type' => 'event'
            ])
                ->addField('event_id', HiddenType::class, [
                    'value'    => $this->itemId,
                    'required' => true
                ], [new RequiredValidator()])
                ->addField('emails', TextType::class, [
                    'label'          => 'invite_people_via_email',
                    'autoCapitalize' => 'none',
                    'placeholder'    => 'enter_email_address'
                ])
                ->addField('personal_message', TextType::class, [
                    'label'       => 'add_a_personal_message',
                    'placeholder' => 'enter_your_message'
                ]);
        } else {
            $this
                ->addField('rsvp_id', RadioType::class, [
                    'label'         => 'rsvp',
                    'options'       => $this->getRSVP(),
                    'value_default' => 0,
                    'order'         => 7
                ], [new NumberRangeValidator(0, 3)]);
        }
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


    public function setEditing($edit)
    {
        $this->editing = $edit;
    }

    public function getEditing()
    {
        return $this->editing;
    }

    private function getRSVP()
    {
        $rsvp = [
            [
                'value' => 0,
                'label' => $this->getLocal()->translate('awaiting_reply')
            ],
            [
                'value' => 1,
                'label' => $this->getLocal()->translate('attending')
            ],
            [
                'value' => 2,
                'label' => $this->getLocal()->translate('maybe_attending')
            ],
            [
                'value' => 3,
                'label' => $this->getLocal()->translate('not_attending')
            ],

        ];
        return $rsvp;
    }
}