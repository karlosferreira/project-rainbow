<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_6\Service;

use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Security\Comment\CommentAccessControl;
use Phpfox;
use Phpfox_Plugin;

class CommentApi extends \Apps\Core_MobileApi\Service\CommentApi
{
    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function update($params)
    {
        if (!$this->bIsNewComment) {
            return parent::update($params);
        }
        $params = $this->resolver
            ->setRequired(['id'])
            ->setDefined(['photo_id', 'sticker_id', 'attach_changed', 'text'])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $comment = $this->loadResourceById($params['id'], true);
        if (!$comment) {
            return $this->notFoundError();
        }

        // Permission checking
        $this->denyAccessUnlessGranted(CommentAccessControl::EDIT, $comment);

        $result = $this->processUpdate($params['id'], $params);
        if ($result && $this->isPassed()) {
            $comment = $this->loadResourceById($params['id'], true);
            return $this->success([
                'id'   => (int)$params['id'],
                'item' => $comment->toArray(),
            ], [], $this->getLocalization()->translate('edit_comment_successfully'));
        }

        return $this->error($this->getErrorMessage());
    }

    /**
     * @param $id
     * @param $params
     *
     * @return bool
     * @throws \Exception
     */
    private function processUpdate($id, $params)
    {
        $text = $params['text'];
        $validText = preg_replace('/([^>\s]?)(\s)/', '', $text);
        $bHasAttach = !empty($params['sticker_id']) || !empty($params['photo_id']);
        if (!$bHasAttach && (Phpfox::getLib('parse.format')->isEmpty($validText)
            || strlen(preg_replace('/([^>\n]?)(\n)/', '', $validText)) === 0
            || strlen(preg_replace('/([^>\r]?)(\r)/', '', $validText)) === 0)) {
            return $this->error($this->getLocalization()->translate('add_some_text_to_your_comment'));
        }
        $text = preg_replace('/([^>\n]?)(\n)/', '&#10;', $text);
        $text = preg_replace('/([^>\r]?)(\r)/', '&#13;', $text);
        $params['text'] = stripslashes($text);

        return Phpfox::getService("comment.process")->updateText($id, $params);
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);

        $comment = $this->loadResourceById($id);
        if (empty($comment)) {
            return $this->notFoundError();
        }
        if (isset($comment['child_total']) && $comment['child_total'] > 0) {
            $comment['children'] = $this->getComments([
                'parent_id'   => $comment['comment_id'],
                'last_id'     => 0,
                'first_child' => true,
                'limit'       => $this->getSetting()->getAppSetting('comment.thread_comment_total_display', 3),
            ]);
        }
        $comment = $this->processRow($comment);
        $this->denyAccessUnlessGranted(CommentAccessControl::VIEW, $comment);

        return $this->success($comment->toArray());
    }

    /**
     * Post comment
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        $params = $this->resolver
            ->setDefined(['table_prefix', 'parent_id', 'is_via_feed', 'sticker_id', 'photo_id', 'text'])
            ->setRequired(['item_type', 'item_id'])
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->setAllowedTypes('parent_id', 'int', ['min' => 0])
            ->setDefault([
                'parent_id' => 0
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $parentItem = $this->predictParentItemId($params);

        $this->denyAccessUnlessGranted(CommentAccessControl::ADD, null, [
            'item_type' => $params['item_type'],
            'item_id'   => $parentItem ? $parentItem : $params['item_id']
        ]);

        $id = $this->processCreate($params);

        if ($this->isPassed()) {
            if ($id == 'pending_comment') {
                return $this->success([
                    'feed_id' => $this->getFeedId($params['item_type'], $params['item_id']),
                    'pending_comment' => true,
                    'item' => []
                ],[], $this->getLocalization()->translate('your_comment_has_been_added_successfully_it_is_waiting_for_an_admin_approval'));
            }
            $comment = $this->loadResourceById($id);
            if (empty($comment)) {
                return $this->privacyError($this->getLocalization()->translate('unable_to_post_a_comment_on_this_item_due_to_privacy_settings'));
            }
            $comment = $this->processRow($comment);

            $data = [
                'item'    => $comment->toArray(),
                'feed_id' => $this->getFeedId($params['item_type'], $params['item_id']),
            ];

            if ($params['parent_id']) {
                $data['parent_id'] = intval($params['parent_id']);
                $data['child_total'] = $this->getTotalChild($params['parent_id']);
            } else {
                $data['total_comment'] = $this->getTotalComment($params['item_type'], $params['item_id']);
            }

            (($sPlugin = Phpfox_Plugin::get('mobile.service_comment_api_create_success')) ? eval($sPlugin) : false);

            return $this->success($data);
        }
        return $this->error($this->getErrorMessage());

    }

    /**
     * @param $params
     *
     * @return array|bool|int
     * @throws UnknownErrorException
     */
    private function processCreate($params)
    {
        $validText = preg_replace('/([^>\s]?)(\s)/', '', $params['text']);
        $bHasAttach = !empty($params['sticker_id']) || !empty($params['photo_id']);
        if (!$bHasAttach && (Phpfox::getLib('parse.format')->isEmpty($validText)
                || strlen(preg_replace('/([^>\n]?)(\n)/', '', $validText)) === 0
                || strlen(preg_replace('/([^>\r]?)(\r)/', '', $validText)) === 0)) {
            return $this->error($this->getLocalization()->translate('add_some_text_to_your_comment'));
        }
        $params['type'] = $params['item_type'];
        $params['is_api'] = true;
        $params['text'] = preg_replace('/([^>\n]?)(\n)/', '&#10;', $params['text']);
        $params['text'] = preg_replace('/([^>\r]?)(\r)/', '&#13;', $params['text']);
        $params['text'] = stripslashes($params['text']);
        return $this->processService()->add($params);

    }
}