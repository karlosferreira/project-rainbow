<?php


namespace Apps\Core_MobileApi\Version1_8\Api\Form\Page;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\FriendPickerType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;

class ReassignOwnerForm extends GeneralForm
{
    protected $action = "page_reassign_owner";
    protected $userId;

    /**
     * @param null $options
     * @param array $data
     *
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    function buildForm($options = null, $data = [])
    {
        $this
            ->addField('user_id', FriendPickerType::class, [
                'label'       => 'select_friend',
                'required'    => true,
                'multiple'    => false,
                'item_id'     => $this->userId,
                'item_type'   => 'user',
                'description' => $this->getLocal()->translate('mobile_reassign_owner_page_notice', ['full_name' => isset($this->data['user']['full_name']) ? $this->data['user']['full_name'] : 'Unknown'])
            ])
            ->addField('submit', SubmitType::class, [
                'label' => 'send_request',
            ]);
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}