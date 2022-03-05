<?php

namespace Apps\Core_MobileApi\Version1_6\Service;

use Apps\Core_MobileApi\Api\Resource\NotificationResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\CoreApi;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class NotificationApi extends \Apps\Core_MobileApi\Service\NotificationApi
{
    public function processRow($item)
    {
        try {
            /** @var NotificationResource $notification */
            $notification = $this->populateResource(NotificationResource::class, $item);
            $newLink = (new CoreApi())->parseUrlToRoute($item['link'], true);
            if (isset($item['type_id']) && (strpos($item['type_id'], 'comment_') === 0 || $item['type_id'] == 'poke_comment') && !isset($newLink['params']['query']['comment_id'])) {
                if (strpos($item['type_id'], '_tag') !== false) {
                    //Comment tag
                    $lastCommentId = $item['item_id'];
                } else {
                    $commentTypeId = trim(str_replace('comment', '', $item['type_id']), '_');
                    //Get latest comment from notification owner
                    $lastCommentId = db()->select('comment_id')->from(':comment')->where([
                        'user_id' => (int)$item['owner_user_id'],
                        'item_id' => (int)$item['item_id'],
                        'type_id' => $commentTypeId
                    ])->order('comment_id DESC')->executeField();
                }
                if ($lastCommentId) {
                    if (!isset($newLink['params']['query'])) {
                        $newLink['params']['query'] = [
                            'comment_id' => (int)$lastCommentId
                        ];
                    } else {
                        $newLink['params']['query'] = array_merge($newLink['params']['query'], ['comment_id' => (int)$lastCommentId]);
                    }
                }
            }
            if ($newLink && isset($newLink['params']['resource_name'],$newLink['params']['id'])) {
                $notification->route = $newLink;
                $param = $newLink['params']['resource_name'] . '/' . $newLink['params']['id'];
                $notification->link = \Phpfox_Url::instance()->makeUrl($param, isset($newLink['params']['query']) ? $newLink['params']['query'] : []);
            } else {
                if (isset($newLink['parsed_url'])) {
                    $notification->link = $newLink['parsed_url'];
                } elseif (preg_match('/\/link\/(\d+)/', $item['link'], $aMatch)) {
                    $link = Phpfox::getService('link')->getLinkById($aMatch[1]);
                    if (!empty($link['module_id'])) {
                        $url = $link['module_id'] . '/' . $link['item_id'];
                    } else {
                        $url = $link['user_name'];
                    }
                    $notification->link = \Phpfox_Url::instance()->makeUrl($url, ['link-id' => $aMatch[1]]);
                }
            }
            return $notification;
        } catch (\Exception $exception) {
            if ($this->isUnitTest()) {
                throw $exception;
            }
        }
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $params = $this->resolver->setDefined(['page', 'limit'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page' => 1
            ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $notifications = $this->getForBrowse($params);

        if ($notifications) {
            $this->processRows($notifications);
        }

        return $this->success($notifications);
    }
}