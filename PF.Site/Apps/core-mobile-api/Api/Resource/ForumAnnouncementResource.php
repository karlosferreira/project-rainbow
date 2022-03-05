<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class ForumAnnouncementResource extends ForumThreadResource
{
    const RESOURCE_NAME = "forum-announcement";
    public $resource_name = self::RESOURCE_NAME;
    public $text;
    public $post_starter;

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['post_starter'])) {
            $this->text = TextFilter::pureHtml($this->rawData['post_starter']['text'], true);
        }
        return $this->text;
    }

    public function getPostStarter()
    {
        if (isset($this->rawData['post_starter'])) {
            return ForumPostResource::populate($this->rawData['post_starter'])->toArray();
        }
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->start_id, 'forum');
        }
        return null;
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $definition = [
            'last_post' => 'forum_post'
        ];
        if (Phpfox::isAppActive('Core_Polls')) {
            $definition['poll'] = 'poll.poll';
        }
        return self::createSettingForResource([
            'acl'           => $permission,
            'resource_name' => $this->getResourceName(),
            'schema'        => [
                'definition' => $definition,
            ],
            'can_search'    => false,
            'can_filter'    => false,
            'can_sort'      => false,
            'fab_buttons'   => false,
            'list_view'     => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_threads_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action'    => !empty($permission['can_add']) ? [
                        'resource_name' => $this->getResourceName(),
                        'module_name'   => $this->getModuleName(),
                        'value'         => Screen::ACTION_ADD
                    ] : null
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
            ],
            'app_menu'      => [
            ],
            'action_menu'   => [
                ['value' => 'forum/add-poll', 'label' => $l->translate('add_poll'), 'acl' => 'can_add_poll'],
                ['value' => 'forum/remove-poll', 'label' => $l->translate('remove_poll'), 'acl' => 'can_delete_poll'],
                ['value' => Screen::ACTION_EDIT_ITEM, 'label' => $l->translate('edit'), 'acl' => 'can_edit'],
                ['value' => Screen::ACTION_DELETE_ITEM, 'label' => $l->translate('delete'), 'acl' => 'can_delete', 'style' => 'danger'],
            ]
        ]);
    }
}