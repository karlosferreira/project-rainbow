<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Events\Service\Event;
use Apps\Core_Events\Service\Process;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Event\EventInviteForm;
use Apps\Core_MobileApi\Api\Resource\EventInviteResource;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Event\EventAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Phpfox_Url;

/**
 * Class EventInviteApi
 * @package Apps\Core_MobileApi\Service
 */
class EventInviteApi extends AbstractResourceApi
{
    const ERROR_EVENT_NOT_FOUND = "Event not found";
    /**
     * @var Event
     */
    private $eventService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    /**
     * EventInviteApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->eventService = Phpfox::getService('event');
        $this->processService = Phpfox::getService('event.process');
        $this->userService = Phpfox::getService('user');
    }

    public function __naming()
    {
        return [
            'event/rsvp/:id'   => [
                'put'   => 'updateRsvp',
                "where" => [
                    'id' => '(\d+)',
                ],
            ],
            'event-invite/:id' => [
                'post'  => 'create',
                "where" => [
                    'id' => '(\d+)',
                ],
            ]
        ];
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'event_id', 'rsvp_id', 'limit', 'page'
        ])
            ->setAllowedValues('rsvp_id', ['0', '1', '2', '3'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('event_id', 'int')
            ->setDefault([
                'limit'   => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'    => 1,
                'rsvp_id' => 0
            ])
            ->setRequired(['event_id'])->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('event.can_access_event')) {
            return $this->permissionError();
        }
        $item = $this->eventService->getEvent($params['event_id']);
        if (!$item) {
            return $this->notFoundError();
        }
        if (!EventApi::checkPermission($item)) {
            return $this->permissionError();
        }
        $cnt = $this->database()
            ->select('COUNT(*)')
            ->from(':event_invite', 'ei')
            ->join(':user', 'u', 'u.user_id = ei.invited_user_id')
            ->where('ei.event_id = ' . (int)$params['event_id'] . ' AND ei.rsvp_id = ' . (int)$params['rsvp_id'] . ' AND ei.invited_user_id != 0')
            ->execute('getSlaveField');
        $invites = [];
        if ($cnt) {
            $invites = $this->database()
                ->select('ei.*, ei.invited_user_id as user_id')
                ->from(':event_invite', 'ei')
                ->join(':user', 'u', 'u.user_id = ei.invited_user_id')
                ->where('ei.event_id = ' . (int)$params['event_id'] . ' AND ei.rsvp_id = ' . (int)$params['rsvp_id'] . ' AND ei.invited_user_id != 0')
                ->limit($params['page'], $params['limit'], $cnt)
                ->order('ei.invite_id DESC')
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
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $invite = $this->loadResourceById($params['id']);
        if (empty($invite)) {
            return $this->notFoundError();
        }
        return $this->success(EventInviteResource::populate($invite)->lazyLoad(['user'])->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $eventId = $this->resolver->setRequired(['id'])->resolveId($params);
        /** @var EventInviteForm $form */
        $form = $this->createForm(EventInviteForm::class, [
            'title'  => 'invite_guests',
            'method' => 'POST'
        ]);
        $form->setItemId($eventId);
        $event = NameResource::instance()->getApiServiceByResourceName(EventResource::RESOURCE_NAME)->loadResourceById($eventId, true);
        $this->denyAccessUnlessGranted(EventAccessControl::INVITE, $event);

        return $this->success($form->getFormStructure());
    }

    private function processCreate($aVals)
    {
        $iId = $aVals['event_id'];
        $oParseInput = Phpfox::getLib('parse.input');
        $aEvent = $this->eventService->getForEdit($iId, true);
        $rEvent = EventResource::populate($aEvent);
        if (!$this->getAccessControl()->isGranted(EventAccessControl::EDIT, $rEvent) && !$this->getAccessControl()->isGranted(EventAccessControl::VIEW, $rEvent)) {
            return $this->permissionError();
        }
        if (isset($aVals['emails']) || isset($aVals['user_ids'])) {
            $aInvites = $this->database()->select('invited_user_id, invited_email')
                ->from(Phpfox::getT('event_invite'))
                ->where('event_id = ' . (int)$iId)
                ->execute('getSlaveRows');
            $aInvited = [];
            foreach ($aInvites as $aInvite) {
                $aInvited[(empty($aInvite['invited_email']) ? 'user' : 'email')][(empty($aInvite['invited_email']) ? $aInvite['invited_user_id'] : $aInvite['invited_email'])] = true;
            }
        }
        if (isset($aVals['emails'])) {
            $aEmails = explode(',', $aVals['emails']);
            $aCachedEmails = [];
            foreach ($aEmails as $sEmail) {
                $sEmail = trim($sEmail);
                if (!Phpfox::getLib('mail')->checkEmail($sEmail)) {
                    continue;
                }

                if (isset($aInvited['email'][$sEmail])) {
                    continue;
                }

                $sLink = Phpfox_Url::instance()->permalink('event', $iId, $aEvent['title']);

                $sMessage = $this->getLocalization()->translate('full_name_invited_you_to_the_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $oParseInput->clean($aEvent['title'], 255),
                        'link'      => $sLink
                    ]
                );
                if (!empty($aVals['personal_message'])) {
                    $sMessage .= "\n\n" . $this->getLocalization()->translate('mobile_full_name_added_the_following_personal_message', [
                                'full_name' => Phpfox::getUserBy('full_name')
                            ]
                        ) . "\n";
                    $sMessage .= $aVals['personal_message'];
                }
                $oMail = Phpfox::getLib('mail');
                if (isset($aVals['invite_from']) && $aVals['invite_from'] == 1) {
                    $oMail->fromEmail(Phpfox::getUserBy('email'))
                        ->fromName(Phpfox::getUserBy('full_name'));
                }
                $bSent = $oMail->to($sEmail)
                    ->subject([
                        'event.full_name_invited_you_to_the_event_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aEvent['title'], 255)
                        ]
                    ])
                    ->message($sMessage)
                    ->send();

                if ($bSent) {
                    $aCachedEmails[$sEmail] = true;

                    $this->database()->insert(':event_invite', [
                            'event_id'      => $iId,
                            'type_id'       => 1,
                            'user_id'       => Phpfox::getUserId(),
                            'invited_email' => $sEmail,
                            'time_stamp'    => PHPFOX_TIME
                        ]
                    );
                }
            }
        }

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

            $aUsers = $this->database()->select('user_id, email, language_id, full_name')
                ->from(':user')
                ->where('user_id IN(' . $sUserIds . ')')
                ->execute('getSlaveRows');

            foreach ($aUsers as $aUser) {
                if (isset($aCachedEmails[$aUser['email']])) {
                    continue;
                }

                if (isset($aInvited['user'][$aUser['user_id']])) {
                    continue;
                }

                if (Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriend(Phpfox::getUserId(),
                        $aUser['user_id'])
                ) {
                    continue;
                }

                $sLink = Phpfox_Url::instance()->permalink('event', $aEvent['event_id'], $aEvent['title']);

                $sMessage = $this->getLocalization()->translate('full_name_invited_you_to_the_title', [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title'     => $oParseInput->clean($aEvent['title'], 255),
                    'link'      => $sLink
                ], $aUser['language_id']);
                if (!empty($aVals['personal_message'])) {
                    $sMessage .= "\n\n" . $this->getLocalization()->translate('mobile_full_name_added_the_following_personal_message', [
                            'full_name' => Phpfox::getUserBy('full_name')
                        ], $aUser['language_id']
                        ) . "\n" . $aVals['personal_message'];
                }
                $bSent = Phpfox::getLib('mail')->to($aUser['user_id'])
                    ->subject([
                        'event.full_name_invited_you_to_the_event_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aEvent['title'], 255)
                        ]
                    ])
                    ->message($sMessage)
                    ->notification('event.invite_to_event')
                    ->send();

                if ($bSent) {
                    $this->database()->insert(':event_invite', [
                            'event_id'        => $iId,
                            'user_id'         => Phpfox::getUserId(),
                            'invited_user_id' => $aUser['user_id'],
                            'time_stamp'      => PHPFOX_TIME
                        ]
                    );

                    (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('event_invite', $iId,
                        $aUser['user_id']) : null);
                }
            }
        }
        return $iId;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        /** @var EventInviteForm $form */
        $form = $this->createForm(EventInviteForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $event = NameResource::instance()->getApiServiceByResourceName(EventResource::RESOURCE_NAME)->loadResourceById($values['event_id'], true);
            if (empty($event)) {
                return $this->notFoundError();
            }
            $this->denyAccessUnlessGranted(EventAccessControl::INVITE, $event);
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => EventResource::populate([])->getResourceName()
                ], [], $this->getLocalization()->translate('invitation_s_successfully_sent'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        return $this->create($params);
    }

    public function updateRsvp($params)
    {
        $params = $this->resolver
            ->setDefined(['id', 'rsvp'])
            ->setAllowedTypes('rsvp', 'int')
            ->setAllowedValues('rsvp', ['1', '2', '3'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $userId = $this->getUser()->getId();

        if ($this->processService->addRsvp($params['id'], $params['rsvp'], $userId)) {
            return $this->success([
                'rsvp' => $params['rsvp']
            ]);
        }
        return $this->error();
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
        $itemId = $this->resolver->resolveId($params);
        $invite = $this->loadResourceById($itemId);
        if (!$invite) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('event.can_access_event') && $this->processService->deleteGuest($itemId)) {
            return $this->success([], [], $this->getLocalization()->translate('Invite deleted successfully'));
        }
        return $this->permissionError();
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $invite = $this->database()->select('ei.*, ei.invited_user_id as user_id')
            ->from(':event_invite', 'ei')
            ->join(':user', 'u', 'ei.invited_user_id = u.user_id')
            ->where('invite_id = ' . (int)$id)
            ->execute('getSlaveRow');
        return $invite;
    }

    public function getUserInvite($eventId, $userId)
    {
        return $this->database()->select('ei.*')
            ->from(':event_invite', 'ei')
            ->where('ei.invited_user_id = ' . (int)$userId . ' AND ei.event_id = ' . (int)$eventId)
            ->execute('getSlaveRow');
    }

    public function processRow($item)
    {
        return EventInviteResource::populate($item)->lazyLoad(['user'])->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new EventAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get("module_id");
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