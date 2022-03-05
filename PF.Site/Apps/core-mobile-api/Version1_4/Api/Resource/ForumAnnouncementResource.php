<?php

namespace Apps\Core_MobileApi\Version1_4\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Resource\AttachmentResource;
use Apps\Core_MobileApi\Api\Resource\ForumPostResource;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\Core_MobileApi\Version1_4\Service\ForumPostApi;
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
        if (!($this->post_starter instanceof ForumPostResource) && isset($this->rawData['post_starter'])) {
            $this->post_starter = (new ForumPostApi())->processRow($this->rawData['post_starter']);
        }
        return $this->post_starter;
    }

    /**
     * @param array $params
     *
     * @return \Apps\Core_MobileApi\Adapter\MobileApp\SettingParametersBag
     * @throws \Exception
     */
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
                'noItemMessage' => $l->translate('no_threads_found'),
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