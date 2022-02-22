<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Marketplace\Service\Marketplace;
use Apps\Core_Marketplace\Service\Process;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Marketplace\MarketplaceInviteForm;
use Apps\Core_MobileApi\Api\Resource\MarketplaceInviteResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Security\Marketplace\MarketplaceAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Phpfox_Url;

/**
 * Class EventInviteApi
 * @package Apps\Core_MobileApi\Service
 */
class MarketplaceInviteApi extends AbstractResourceApi
{
    /**
     * @var Marketplace
     */
    private $marketplaceService;

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
        $this->marketplaceService = Phpfox::getService('marketplace');
        $this->processService = Phpfox::getService('marketplace.process');
        $this->userService = Phpfox::getService('user');
    }

    public function __naming()
    {
        return [
            'marketplace-invite/:id' => [
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
            'listing_id', 'limit', 'page', 'visited'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('listing_id', 'int')
            ->setDefault([
                'limit'   => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'    => 1,
                'visited' => 1
            ])
            ->setRequired(['listing_id'])->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace')) {
            return $this->permissionError();
        }
        $item = $this->marketplaceService->getListing($params['listing_id']);
        if (!$item) {
            return $this->notFoundError();
        }
        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $item['user_id'])) {
            return $this->permissionError();
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('marketplace', $item['listing_id'], $item['user_id'],
                $item['privacy'], $item['is_friend'], true)) {
            return $this->permissionError();
        }
        list(, $invites) = $this->getInvites($item['listing_id'], $params['visited'], $params['page'], $params['limit']);

        $this->processRows($invites);
        return $this->success($invites);
    }

    private function getInvites($iListing, $iType, $iPage = 0, $iPageSize = 8)
    {
        $aInvites = [];
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('marketplace_invite'))
            ->where('listing_id = ' . (int)$iListing . ' AND visited_id = ' . (int)$iType)
            ->execute('getSlaveField');

        if ($iCnt) {
            $aInvites = $this->database()->select('ei.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('marketplace_invite'), 'ei')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = ei.invited_user_id')
                ->where('ei.listing_id = ' . (int)$iListing . ' AND ei.visited_id = ' . (int)$iType)
                ->limit($iPage, $iPageSize, $iCnt)
                ->order('ei.invite_id DESC')
                ->execute('getSlaveRows');
        }

        return [$iCnt, $aInvites];
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
        return $this->success(MarketplaceInviteResource::populate($invite)->lazyLoad(['user'])->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $listingId = $this->resolver->setRequired(['id'])->resolveId($params);
        /** @var MarketplaceInviteForm $form */
        $form = $this->createForm(MarketplaceInviteForm::class, [
            'title'  => 'invite_guests',
            'method' => 'POST'
        ]);
        $form->setItemId($listingId);
        $listing = NameResource::instance()->getApiServiceByResourceName(MarketplaceResource::RESOURCE_NAME)->loadResourceById($listingId, true);
        if (empty($listing)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::INVITE, $listing);

        return $this->success($form->getFormStructure());
    }

    private function processCreate($aVals)
    {
        $iId = $aVals['listing_id'];
        $oParseInput = Phpfox::getLib('parse.input');
        $aListing = $this->marketplaceService->getForEdit($iId, true);
        if (isset($aVals['emails']) || isset($aVals['user_ids'])) {
            $aInvites = $this->database()->select('invited_user_id, invited_email')
                ->from(Phpfox::getT('marketplace_invite'))
                ->where('listing_id = ' . (int)$iId)
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

                $sLink = Phpfox_Url::instance()->permalink('marketplace', $iId, $aListing['title']);

                $sMessage = $this->getLocalization()->translate('full_name_invited_you_to_view_the_marketplace_listing_title', [
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'title'     => $oParseInput->clean($aListing['title'], 255),
                        'link'      => $sLink
                    ]
                );
                if (!empty($aVals['personal_message'])) {
                    $sMessage .= "\n\n" . _p('mobile_full_name_added_the_following_personal_message',
                            ['full_name' => Phpfox::getUserBy('full_name')]) . "\n";
                    $sMessage .= $aVals['personal_message'];
                }
                $oMail = Phpfox::getLib('mail');
                if (isset($aVals['invite_from']) && $aVals['invite_from'] == 1) {
                    $oMail->fromEmail(Phpfox::getUserBy('email'))
                        ->fromName(Phpfox::getUserBy('full_name'));
                }
                $bSent = $oMail->to($sEmail)
                    ->subject([
                        'marketplace.full_name_invited_you_to_view_the_listing_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aListing['title'], 255)
                        ]
                    ])
                    ->message($sMessage)
                    ->translated()
                    ->send();

                if ($bSent) {
                    $aCachedEmails[$sEmail] = true;

                    $this->database()->insert(':marketplace_invite', [
                            'listing_id'    => $iId,
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

                $sLink = Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'], $aListing['title']);

                $sMessage = $this->getLocalization()->translate('full_name_invited_you_to_view_the_marketplace_listing_title', [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title'     => $oParseInput->clean($aListing['title'], 255),
                    'link'      => $sLink
                ], $aUser['language_id']);
                if (!empty($aVals['personal_message'])) {
                    $sMessage .= "\n\n" . $this->getLocalization()->translate('mobile_full_name_added_the_following_personal_message',
                            ['full_name' => Phpfox::getUserBy('full_name')], $aUser['language_id']) . "\n";
                    $sMessage .= $aVals['personal_message'];
                }
                $bSent = Phpfox::getLib('mail')->to($aUser['user_id'])
                    ->subject([
                        'full_name_invited_you_to_view_the_listing_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aListing['title'], 255)
                        ]
                    ])
                    ->message($sMessage)
                    ->notification('new_invite')
                    ->send();

                if ($bSent) {
                    $this->database()->insert(':marketplace_invite', [
                            'listing_id'      => $iId,
                            'user_id'         => Phpfox::getUserId(),
                            'invited_user_id' => $aUser['user_id'],
                            'time_stamp'      => PHPFOX_TIME
                        ]
                    );

                    (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('marketplace_invite', $iId,
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
        /** @var MarketplaceInviteForm $form */
        $form = $this->createForm(MarketplaceInviteForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $listing = NameResource::instance()->getApiServiceByResourceName(MarketplaceResource::RESOURCE_NAME)->loadResourceById($values['listing_id'], true);
            if (empty($listing)) {
                return $this->notFoundError();
            }
            $this->denyAccessUnlessGranted(MarketplaceAccessControl::INVITE, $listing);
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => MarketplaceResource::populate([])->getResourceName()
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
        return null;
    }

    /**
     * @param $id
     * @param bool $returnResource
     * @return array|int|resource|string|null
     */
    function loadResourceById($id, $returnResource = false)
    {
        $invite = $this->database()->select('ei.*, ei.invited_user_id as user_id')
            ->from(':marketplace_invite', 'ei')
            ->leftJoin(':user', 'u', 'ei.invited_user_id = u.user_id')
            ->where('invite_id = ' . (int)$id)
            ->execute('getSlaveRow');
        if (empty($invite['invite_id'])) {
            return null;
        }
        $user = Phpfox::getService('user')->getUser($invite['user_id']);
        if ((!$invite['user_id'] && !$invite['invited_email']) || ($invite['user_id'] && empty($user['user_id']))) {
            return null;
        }
        if ($returnResource) {
            return $this->populateResource(MarketplaceInviteResource::class, $invite);
        }
        return $invite;
    }

    public function getUserInvite($listingId, $userId)
    {
        return $this->database()->select('ei.*')
            ->from(':marketplace_invite', 'ei')
            ->where('ei.invited_user_id = ' . (int)$userId . ' AND ei.listing_id = ' . (int)$listingId)
            ->execute('getSlaveRow');
    }

    public function processRow($item)
    {
        return MarketplaceInviteResource::populate($item)->lazyLoad(['user'])->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MarketplaceAccessControl($this->getSetting(), $this->getUser());
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