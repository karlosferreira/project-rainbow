<?php

namespace Apps\Core_MobileApi\Api\Form\Feed;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\SubmitType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;

class ShareFeedForm extends GeneralForm
{
    protected $postType = "wall";

    /**
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        if (isset($this->data['post_type'])) {
            $this->setPostType($this->data['post_type']);
        }

        $this->setTitle("share")
            ->setMethod("post")
            ->setAction(UrlUtility::makeApiUrl("feed/share"))
            ->addField('item_type', HiddenType::class)
            ->addField('item_id', HiddenType::class)
            ->addField('post_type', HiddenType::class, [
                'value_default' => $this->postType
            ]);

        $this->addField('post_content', TextareaType::class, [
            'label'       => 'message',
            'placeholder' => 'write_a_message',
            'autoFocus'   => true,
        ]);
        if ($this->postType == "friend") {
            $this->addField('friends', HiddenType::class);
        } else {
            $this->addPrivacyField([], null, $this->getPrivacyDefault('feed.default_privacy_setting'));
        }
        $this->addField('submit', SubmitType::class, [
            'label' => 'share'
        ]);
    }

    public function isValid()
    {
        if (empty($this->data['item_type'])
            && empty($this->data['item_id'])) {
            return false;
        }
        return parent::isValid();
    }

    public function getValues()
    {
        $values = parent::getValues();
        // Convert use for core service
        $values['module_id'] = $values['item_type'];
        $values['feed_id'] = $values['item_id'];
        return $values;
    }

    /**
     * @param string $postType
     */
    public function setPostType($postType)
    {
        $this->postType = $postType;
    }
}