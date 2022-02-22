<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7\Service;

use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_MobileApi\Version1_7\Api\Security\Poll\PollAccessControl;
use Phpfox;

class PollApi extends \Apps\Core_MobileApi\Service\PollApi
{
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(PollAccessControl::VIEW);

        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'profile_id', 'limit', 'page', 'when', 'module_id', 'item_id'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'friend', 'pending', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE,
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page' => 1,
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $sort = $params['sort'];
        $view = $params['view'];
        $isProfile = $params['profile_id'];
        $user = [];

        if (in_array($view, ['feature', 'sponsor'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }
        $parentModule = null;
        if (!empty($params['module_id']) && !empty($params['item_id'])) {
            $parentModule = [
                'module_id' => $params['module_id'],
                'item_id' => $params['item_id'],
            ];
        }
        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
        }
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'poll',
            'alias' => 'poll',
            'field' => 'poll_id',
            'table' => Phpfox::getT('poll'),
            'hide_view' => ['pending', 'my'],
            'service' => 'poll.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'poll'
        ]);
        switch ($view) {
            case 'my':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND poll.user_id = ' . (int)Phpfox::getUserId());
                } else {
                    return $this->permissionError();
                }
                break;
            case 'pending':
                if (Phpfox::isUser() && Phpfox::getUserParam('poll.poll_can_moderate_polls')) {
                    $this->search()->setCondition('AND poll.view_id = 1');
                } else {
                    return $this->permissionError();
                }
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition('AND poll.item_id = 0 AND poll.user_id = ' . (int)$user['user_id'] . ' AND poll.view_id IN(' . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0')
                        . ') AND poll.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($user)) . ')');
                } elseif ($parentModule !== null) {
                    $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND poll.module_id = \'' . \Phpfox_Database::instance()->escape($parentModule['module_id']) . '\' AND poll.item_id = ' . (int)$parentModule['item_id'] . '');
                } else {
                    if (($this->getSetting()->getAppSetting('poll.display_polls_created_in_page') || $this->getSetting()->getAppSetting('poll.display_polls_created_in_group'))) {
                        $aModules = [];
                        if ($this->getSetting()->getAppSetting('poll.display_polls_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                            $aModules[] = 'groups';
                        }
                        if ($this->getSetting()->getAppSetting('poll.display_polls_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                            $aModules[] = 'pages';
                        }
                        if (count($aModules)) {
                            $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND (poll.module_id IN ("' . implode('","', $aModules) . '") OR poll.module_id IS NULL)');
                        } else {
                            $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND poll.module_id IS NULL');
                        }
                    } else {
                        $this->search()->setCondition('AND poll.item_id = 0 AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%)');
                    }
                }
                $this->search()->setCondition('AND (poll.close_time = 0 OR poll.close_time > ' . PHPFOX_TIME . ')');
                break;
        }

        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'poll.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'poll.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'poll.total_comment DESC';
                break;
            default:
                $sort = 'poll.time_stamp DESC';
                break;
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND poll.question LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);

        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $items = $this->browse()->getRows();
        //Reset key
        $items = array_values($items);

        $this->processRows($items);
        return $this->success($items);
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new PollAccessControl($this->getSetting(), $this->getUser());
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get("item_id");

        if ($moduleId) {
            $context = AppContextFactory::create($moduleId, $itemId);
            if ($context === null) {
                return $this->notFoundError();
            }
            $this->accessControl->setAppContext($context);
        }
        return true;
    }
}