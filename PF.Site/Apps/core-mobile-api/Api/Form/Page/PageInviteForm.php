<?php


namespace Apps\Core_MobileApi\Api\Form\Page;

use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\FriendPickerType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\RequiredValidator;

class PageInviteForm extends GeneralForm
{
    protected $action = "mobile/page-invite";
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
        if (!$this->getEditing()) {
            $this->addField('user_ids', FriendPickerType::class, [
                'label'     => 'invite_friends',
                'item_id'   => $this->getItemId(),
                'item_type' => 'pages'
            ])
                ->addField('page_id', HiddenType::class, [
                    'value'    => $this->getItemId(),
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
        }
        $this
            ->addField('submit', SubmitType::class, [
                'label' => 'save'
            ]);
    }

    /**
     * @param bool $edit
     *
     * @codeCoverageIgnore
     */
    public function setEditing($edit)
    {
        $this->editing = $edit;
    }

    public function getEditing()
    {
        return $this->editing;
    }

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @return integer
     */
    public function getItemId()
    {
        return $this->itemId;
    }
}