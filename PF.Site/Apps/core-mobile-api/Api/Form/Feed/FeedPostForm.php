<?php

namespace Apps\Core_MobileApi\Api\Form\Feed;

use Apps\Core_MobileApi\Api\Form\GeneralForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Form\Type\HiddenType;
use Apps\Core_MobileApi\Api\Form\Type\LocationType;
use Apps\Core_MobileApi\Api\Form\Type\MultiFileType;
use Apps\Core_MobileApi\Api\Form\Type\TextareaType;
use Apps\Core_MobileApi\Api\Form\Type\TextType;
use Apps\Core_MobileApi\Api\Form\Validator\AllowedValuesValidator;
use Apps\Core_MobileApi\Api\Form\Validator\TypeValidator;

class FeedPostForm extends GeneralForm
{
    const STATUS_POST = "status";
    const PHOTO_POST = "photo";
    const VIDEO_POST = "video";
    const LINK_POST = 'link';

    /**
     * Override build form to generate form
     * @return mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function buildForm()
    {
        $this->addField('post_type', HiddenType::class, [], [
            new AllowedValuesValidator([
                self::STATUS_POST, self::PHOTO_POST, self::VIDEO_POST, self::LINK_POST
            ])
        ])
            ->addField('parent_item_type', HiddenType::class)
            ->addField('parent_item_id', HiddenType::class)
            ->addField('post_as_parent', HiddenType::class)
            // Post to friend's wall
            ->addField('parent_user_id', HiddenType::class)
            // Global Fields used for all post type
            ->addPrivacyField([], null, $this->getPrivacyDefault('feed.default_privacy_setting'))
            ->addField('tagged_friends', HiddenType::class, [], [
                new TypeValidator(TypeValidator::IS_ARRAY_NUMERIC)
            ])
            ->addField('location', LocationType::class)
            // User Status Post
            ->addField('user_status', TextareaType::class, [
            ])
            // Photo Post
            // RE-Uploaded temp file ids
            ->addField('photo_files', MultiFileType::class)
            ->addField('photo_description', TextareaType::class, [
            ])
            // Video Post Field
            ->addField('video_file', FileType::class)
            ->addField('video_title', TextType::class)
            ->addField('video_url', TextType::class)
            ->addField('video_description', TextareaType::class)
            // Post link
            ->addField('link_image', FileType::class)
            ->addField('link_url', TextType::class)
            ->addField('link_description', TextareaType::class)
            ->addField('link_title', TextType::class)
            ->addField('link_embed_code', TextType::class);
    }

    /**
     * Feed posting validation
     * @return bool
     */
    public function isValid()
    {
        $b = parent::isValid();
        if ($b) {
            if (!empty($values['parent_item_type']) && empty($values['parent_item_id'])) {
                $b = false;
            }
            if (empty($values['parent_item_type']) && !empty($values['parent_item_id'])) {
                $b = false;
            }
        }
        return $b;
    }

    /**
     * Filter out parameters
     *
     * @return array
     */
    public function getValues()
    {
        $values = parent::getValues();
        // Convert tagged_friends params in to string
        if (!empty($values['tagged_friends'])) {
            $values['tagged_friends'] = implode(",", $values['tagged_friends']);
        }
        if (!empty($values['location'])) {
            $values['location']['name'] = $values['location']['address'];
            $values['location']['latlng'] = !empty($values['location']['lat']) && !empty($values['location']['lng'])
                ? $values['location']['lat'] . "," . $values['location']['lng'] : '';
        }

        // This is post to a user's wall. Filter out unused param
        if (!empty($values['parent_user_id'])) {
            unset($values['parent_item_type']);
            unset($values['parent_item_id']);
            unset($values['post_as_parent']);
        }

        if (!empty($values['parent_item_type']) && $values['parent_item_type'] == 'user' && !empty($values['parent_item_id'])) {
            $values['parent_user_id'] = $values['parent_item_id'];
            unset($values['parent_item_type']);
            unset($values['parent_item_id']);
            unset($values['post_as_parent']);
        }

        return $values;
    }

    public function getGroupValues($group)
    {
        $result = parent::getGroupValues($group);

        if (!empty($this->values['parent_item_type']) && !empty($this->values['parent_item_id'])) {
            if ($this->values['parent_item_type'] == 'user') {
                $result['parent_user_id'] = $this->values['parent_item_id'];
            } else {
                // Support for other module automatic
                $result['item_type'] = $this->values['parent_item_type'];
                $result['module_id'] = $this->values['parent_item_type'];
                $result['item_id'] = $this->values['parent_item_id'];
            }
        } else if (!empty($this->values['parent_user_id'])) {
            $result['parent_user_id'] = $this->values['parent_user_id'];
        }
        return $result;
    }

}