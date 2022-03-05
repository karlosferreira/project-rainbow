<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Page\PageInviteForm;
use Apps\Core_MobileApi\Api\Resource\PageInviteResource;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Pages\Service\Facade;
use Apps\Core_Pages\Service\Pages;
use Apps\Core_Pages\Service\Process;
use Phpfox;


class PageInviteApi extends AbstractResourceApi
{

    /**
     * @var Facade
     */
    private $facadeService;

    /**
     * @var Pages
     */
    private $pageService;
    /**
     * @var Process
     */
    private $processService;

    public function __construct()
    {
        parent::__construct();
        $this->facadeService = Phpfox::getService('pages.facade');
        $this->pageService = Phpfox::getService('pages');
        $this->processService = Phpfox::getService('pages.process');
    }

    public function __naming()
    {
        return [
            'page-invite/:id' => [
                'post'  => 'create',
                "where" => [
                    'id' => '(\d+)',
                ],
            ]
        ];
    }

    /**
     * @param array $params
     *
     * @return mixed
     */

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'page_id', 'visited_id', 'limit', 'page'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('visited_id', 'int')
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('page_id', 'int')
            ->setRequired(['page_id'])
            ->setDefault(['page' => 1, 'limit' => Pagination::DEFAULT_ITEM_PER_PAGE, 'visited_id' => 0])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('pages.can_view_browse_pages')) {
            return $this->permissionError();
        }
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($params['page_id']);
        if (!$page) {
            return $this->notFoundError();
        }
        if ($page['user_id'] != Phpfox::getUserId() && !$this->pageService->isAdmin($page) && !Phpfox::getUserParam('pages.can_edit_all_pages')) {
            return $this->permissionError();
        }
        $cnt = $this->database()
            ->select('COUNT(*)')
            ->from(':pages_invite', 'pi')
            ->join(':user', 'u', 'u.user_id = pi.invited_user_id')
            ->where('pi.page_id = ' . (int)$params['page_id'] . ' AND pi.visited_id = ' . (int)$params['visited_id'] . ' AND pi.invited_user_id != 0')
            ->execute('getSlaveField');
        $invites = [];
        if ($cnt) {
            $invites = $this->database()
                ->select('pi.*, pi.invited_user_id as user_id')
                ->from(':pages_invite', 'pi')
                ->join(':user', 'u', 'u.user_id = pi.invited_user_id')
                ->where('pi.page_id = ' . (int)$params['page_id'] . ' AND pi.visited_id = ' . (int)$params['visited_id'] . ' AND pi.invited_user_id != 0')
                ->limit($params['page'], $params['limit'], $cnt)
                ->order('pi.invite_id DESC')
                ->execute('getSlaveRows');
        }
        $this->processRows($invites);
        return $this->success($invites);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        return $this->findAll(['page_id' => $id]);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        /** @var PageInviteForm $form */
        $form = $this->createForm(PageInviteForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($values['page_id']);
            if (empty($page)) {
                return $this->notFoundError();
            }
            $this->denyAccessUnlessGranted(PageAccessControl::EDIT, PageResource::populate($page));
            $id = $this->processCreate($values, $page);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => PageResource::populate([])->getResourceName()
                ], [], $this->getLocalization()->translate('invitation_s_successfully_sent'));
            } else {
                return $this->success([]);
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($aVals, $aPage)
    {
        $iId = $aVals['page_id'];
        $bHasSent = false;
        if ((isset($aVals['user_ids']) && (is_array($aVals['user_ids']) || is_numeric($aVals['user_ids']))) || (isset($aVals['emails']) && $aVals['emails'])) {
            // get invited friends, emails
            $aInvites = $this->database()->select('invited_user_id, invited_email')
                ->from(':pages_invite')
                ->where('page_id = ' . (int)$iId)
                ->execute('getSlaveRows');
            $aInvited = [];
            foreach ($aInvites as $aInvite) {
                $aInvited[(empty($aInvite['invited_email']) ? 'user' : 'email')][(empty($aInvite['invited_email']) ? $aInvite['invited_user_id'] : $aInvite['invited_email'])] = true;
            }

            // invite friends
            if (isset($aVals['user_ids']) && is_array($aVals['user_ids'])) {
                $sUserIds = '';
                foreach ($aVals['user_ids'] as $iUserId) {
                    if (!is_numeric($iUserId)) {
                        continue;
                    }
                    if (!Phpfox::getService('user')->isUser($iUserId, true)) {
                        continue;
                    }
                    $sUserIds .= $iUserId . ',';
                }
                $sUserIds = rtrim($sUserIds, ',');
            } else if (is_numeric($aVals['user_ids']) && Phpfox::getService('user')->isUser($aVals['user_ids'], true)) {
                $sUserIds = $aVals['user_ids'];
            }
            if (!empty($sUserIds)) {
                $aUsers = $this->database()->select('user_id, email, language_id, full_name')
                    ->from(Phpfox::getT('user'))
                    ->where('user_id IN(' . $sUserIds . ')')
                    ->execute('getSlaveRows');

                $sLink = $this->facadeService->getItems()->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']);

                list(, $aMembers) = $this->facadeService->getItems()->getMembers($aPage['page_id']);

                foreach ($aUsers as $aUser) {
                    if (in_array($aUser['user_id'], array_column($aMembers, 'user_id'))) {
                        continue;
                    }

                    if (isset($aCachedEmails[$aUser['email']])) {
                        continue;
                    }

                    if (isset($aInvited['user'][$aUser['user_id']])) {
                        continue;
                    }

                    $sMessage = $this->getLocalization()->translate('full_name_invited_you_to_the_page_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $aPage['title']
                    ]);
                    $sMessage .= "\n" . $this->getLocalization()->translate('to_view_this_page_click_the_link_below_a_href_link_link_a',
                            ['link' => $sLink]) . "\n";

                    // add personal message
                    if (!empty($aVals['personal_message'])) {
                        $sMessage .= _p('full_name_added_the_following_personal_message',
                                ['full_name' => Phpfox::getUserBy('full_name')], $aUser['language_id'])
                            . $aVals['personal_message'];
                    }
                    // send email to user
                    $bHasSent = true;
                    Phpfox::getLib('mail')->to($aUser['user_id'])
                        ->subject([
                            'full_name_sent_you_a_page_invitation',
                            ['full_name' => Phpfox::getUserBy('full_name')]
                        ])
                        ->message($sMessage)
                        ->translated()
                        ->send();
                    // add to table pages_invite
                    $this->database()->insert(':pages_invite', [
                            'page_id'         => $iId,
                            'type_id'         => $this->facadeService->getItemTypeId(),
                            'user_id'         => Phpfox::getUserId(),
                            'invited_user_id' => $aUser['user_id'],
                            'time_stamp'      => PHPFOX_TIME
                        ]
                    );
                    // send notification
                    (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add($this->facadeService->getItemType() . '_invite',
                        $iId, $aUser['user_id']) : null);
                }
            }

            // invite emails
            if (isset($aVals['emails']) && $aVals['emails']) {
                $aEmails = explode(',', $aVals['emails']);
                foreach ($aEmails as $sEmail) {
                    $sEmail = trim($sEmail);
                    if (!Phpfox::getLib('mail')->checkEmail($sEmail)) {
                        continue;
                    }

                    if (isset($aInvited['email'][$sEmail])) {
                        continue;
                    }

                    $sLink = $this->facadeService->getItems()->getUrl($iId, $aPage['title'], $aPage['vanity_url']);

                    $sMessage = _p('full_name_invited_you_to_the_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $aPage['title'],
                        'link'      => $sLink
                    ]);
                    if (!empty($aVals['personal_message'])) {
                        $sMessage .= _p('full_name_added_the_following_personal_message',
                                ['full_name' => Phpfox::getUserBy('full_name')])
                            . $aVals['personal_message'];
                    }
                    $bHasSent = true;
                    $oMail = Phpfox::getLib('mail');
                    if (isset($aVals['invite_from']) && $aVals['invite_from'] == 1) {
                        $oMail->fromEmail(Phpfox::getUserBy('email'))
                            ->fromName(Phpfox::getUserBy('full_name'));
                    }
                    $bSent = $oMail->to($sEmail)
                        ->subject([
                            'full_name_invited_you_to_the_page_title',
                            [
                                'full_name' => Phpfox::getUserBy('full_name'),
                                'title'     => $aPage['title']
                            ]
                        ])
                        ->message($sMessage)
                        ->send();

                    if ($bSent) {
                        // cache email for not duplicate invite.
                        $aCachedEmails[$sEmail] = true;

                        $this->database()->insert(Phpfox::getT('pages_invite'), [
                                'page_id'       => $iId,
                                'type_id'       => $this->facadeService->getItemTypeId(),
                                'user_id'       => Phpfox::getUserId(),
                                'invited_email' => $sEmail,
                                'time_stamp'    => PHPFOX_TIME
                            ]
                        );
                    }
                }
            }
            // notification message
            Phpfox::addMessage($this->getLocalization()->translate('invitations_sent_out'));
        }
        return $bHasSent ? $iId : false;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $itemId = $params['id'];
        $item = $this->loadResourceById($params['id']);
        if (!$itemId || !$item) {
            return $this->notFoundError();
        }
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($item['page_id']);
        if (!$page) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('pages.can_view_browse_pages') && ($page['user_id'] == Phpfox::getUserId() || $this->pageService->isAdmin($page) || Phpfox::getUserParam('pages.can_edit_all_pages'))) {
            $this->database()->delete(':pages_invite', 'invite_id = ' . (int)$params['id']);
            return $this->success([], [], $this->getLocalization()->translate('page_successfully_updated'));
        }
        return $this->permissionError();
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $editId = $this->resolver->setRequired(['id'])->resolveId($params);
        /** @var PageInviteForm $form */
        $form = $this->createForm(PageInviteForm::class, [
            'title'  => 'invite_friends',
            'action' => UrlUtility::makeApiUrl('page-invite'),
            'method' => 'POST'
        ]);
        $form->setItemId($editId);
        $page = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->loadResourceById($editId, true);
        if (!$editId || empty($page)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PageAccessControl::EDIT, $page);

        return $this->success($form->getFormStructure());
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->database()->select('pi.*')
            ->from(':pages_invite', 'pi')
            ->join(':user', 'u', 'pi.invited_user_id = u.user_id')
            ->where('pi.invite_id = ' . (int)$id)
            ->execute('getSlaveRow');
        return $item;
    }

    public function processRow($item)
    {
        return PageInviteResource::populate($item)->lazyLoad(['user'])->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new PageAccessControl($this->getSetting(), $this->getUser());
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }
}