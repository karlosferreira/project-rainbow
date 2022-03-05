<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7_3\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_MobileApi\Version1_7_3\Api\Form\Quiz\QuizForm;
use Apps\Core_MobileApi\Version1_7_3\Api\Security\Quiz\QuizAccessControl;
use Phpfox;
use Phpfox_Error;

class QuizApi extends \Apps\Core_MobileApi\Service\QuizApi
{
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(QuizAccessControl::VIEW);
        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'profile_id', 'limit', 'page', 'when', 'module_id', 'item_id'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'friend', 'pending', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
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
            'module_id' => 'quiz',
            'alias'     => 'q',
            'field'     => 'quiz_id',
            'table'     => Phpfox::getT('quiz'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'quiz.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'q'
        ]);
        switch ($view) {
            case 'my':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND q.user_id = ' . (int)Phpfox::getUserId());
                } else {
                    return $this->permissionError();
                }
                break;
            case 'pending':
                if (Phpfox::isUser() && Phpfox::getUserParam('quiz.can_approve_quizzes')) {
                    $this->search()->setCondition('AND q.view_id = 1');
                } else {
                    return $this->permissionError();
                }
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition('AND q.view_id IN(' . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ') AND q.user_id = ' . (int)$user['user_id'] . ' AND  q.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ')');
                } elseif ($parentModule !== null) {
                    $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND q.module_id = \'' . \Phpfox_Database::instance()->escape($parentModule['module_id']) . '\' AND q.item_id = ' . (int)$parentModule['item_id'] . '');
                } else {
                    if (($this->getSetting()->getAppSetting('quiz.display_quizzes_created_in_page') || $this->getSetting()->getAppSetting('quiz.display_quizzes_created_in_group'))) {
                        $modules = [];
                        if ($this->getSetting()->getAppSetting('quiz.display_quizzes_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                            $modules[] = 'groups';
                        }
                        if ($this->getSetting()->getAppSetting('quiz.display_quizzes_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                            $modules[] = 'pages';
                        }
                        if (count($modules)) {
                            $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND (q.module_id IN ("' . implode('","', $modules) . '") OR q.module_id = \'quiz\')');
                        } else {
                            $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND q.module_id = \'quiz\'');
                        }
                    } else {
                        $this->search()->setCondition('AND q.item_id = 0 AND q.view_id = 0 AND q.privacy IN(%PRIVACY%)');
                    }
                }
                break;
        }

        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'q.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'q.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'q.total_comment DESC';
                break;
            default:
                $sort = 'q.time_stamp DESC';
                break;
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND q.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
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
            new QuizAccessControl($this->getSetting(), $this->getUser());
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get("item_id");

        if ($moduleId && $itemId) {
            $context = AppContextFactory::create($moduleId, $itemId);
            if ($context === null) {
                return $this->notFoundError();
            }
            $this->accessControl->setAppContext($context);
        }
        return false;
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var QuizForm $form */
        $form = $this->createForm(QuizForm::class, [
            'title'  => 'add_new_quiz',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('quiz')
        ]);

        $quiz = $this->loadResourceById($editId, true);
        if ($editId && empty($quiz)) {
            return $this->notFoundError();
        }
        if ($quiz) {
            $this->denyAccessUnlessGranted(QuizAccessControl::EDIT, $quiz);
            $form->setTitle('editing_quiz')
                ->setAction(UrlUtility::makeApiUrl('quiz/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($quiz);
        } else {
            $this->denyAccessUnlessGranted(QuizAccessControl::ADD);
        }
        return $this->success($form->getFormStructure());
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(QuizAccessControl::ADD);
        /** @var QuizForm $form */
        $form = $this->createForm(QuizForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => QuizResource::populate([])->getResourceName()
                ], [], $this->localization->translate('quiz_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var QuizForm $form */
        $form = $this->createForm(QuizForm::class);
        $quiz = $this->loadResourceById($id, true);
        if (empty($quiz)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(QuizAccessControl::EDIT, $quiz);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success && Phpfox_Error::isPassed()) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => QuizResource::populate([])->getResourceName()
                ], [], $this->localization->translate('quiz_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    protected function processUpdate($id, $values)
    {
        $this->convertSubmitForm($values, true);
        $values['quiz_id'] = $id;
        list($id,) = $this->processService->update($values, $this->getUser()->getId());

        return $id;
    }
}