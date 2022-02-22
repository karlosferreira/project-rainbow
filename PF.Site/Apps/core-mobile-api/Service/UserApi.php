<?php

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Adapter\PushNotification\PushNotificationInterface;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Exception\ErrorException;
use Apps\Core_MobileApi\Api\Exception\NotFoundErrorException;
use Apps\Core_MobileApi\Api\Exception\PermissionErrorException;
use Apps\Core_MobileApi\Api\Exception\UndefinedResourceName;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Exception\ValidationErrorException;
use Apps\Core_MobileApi\Api\Form\User\ChangePasswordForm;
use Apps\Core_MobileApi\Api\Form\User\DeleteAccountForm;
use Apps\Core_MobileApi\Api\Form\User\EditProfileForm;
use Apps\Core_MobileApi\Api\Form\User\ForgetPasswordRequest;
use Apps\Core_MobileApi\Api\Form\User\UserRegisterForm;
use Apps\Core_MobileApi\Api\Form\User\UserSearchForm;
use Apps\Core_MobileApi\Api\Resource\ActivityPointResource;
use Apps\Core_MobileApi\Api\Resource\BlockedUserResource;
use Apps\Core_MobileApi\Api\Resource\BlogResource;
use Apps\Core_MobileApi\Api\Resource\EmailNotificationSettingsResource;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\FriendResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Resource\ItemPrivacySettingsResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Resource\MusicAlbumResource;
use Apps\Core_MobileApi\Api\Resource\MusicSongResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Resource\PhotoAlbumResource;
use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\ProfilePrivacySettingsResource;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Resource\UserInfoResource;
use Apps\Core_MobileApi\Api\Resource\UserPhotoResource;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Resource\UserStatisticResource;
use Apps\Core_MobileApi\Api\Resource\VideoResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Exception;
use Phpfox;
use Phpfox_Error;

class UserApi extends AbstractResourceApi implements MobileAppSettingInterface
{
    /**
     * @var \User_Service_User
     */
    private $userService;

    /**
     * @var \User_Service_Process
     */
    private $processService;

    public function __construct()
    {
        parent::__construct();
    }

    public function __naming()
    {
        return [
            'user/profile-menu'         => [
                'get' => 'getProfileMenus',
            ],
            'user/blocked'              => [
                'get'    => 'getBlockedUsers',
                'delete' => 'unblockUser',
            ],
            'user/activity'             => [
                'get' => 'getUserActivity',
            ],
            'user/activity/:id'         => [
                'get' => 'getUserActivity',
            ],
            'user/privacy'              => [
                'get' => 'getUserSetting',
            ],
            'user/password'             => [
                'get' => 'changePassword',
                'put' => 'changePassword',
            ],
            'account/password'          => [
                'get' => 'changePassword',
                'put' => 'changePassword',
            ],
            'user/password/request'     => [
                'get'  => 'passwordRequest',
                'put'  => 'passwordRequest',
                'post' => 'passwordRequest',
            ],
            'user/password/verify-request'     => [
                'put' => 'verifyPasswordRequest'
            ],
            'user/account'              => [
                'get' => 'getUserAccount',
            ],
            'user/info/:id'             => [
                'get' => 'getUserInfo',
            ],
            'me'                        => [
                'get' => 'findMe',
            ],
            'me/blocked'                => [
                'get'    => 'getBlockedUsers',
                'delete' => 'unblockUser',
            ],
            'me/activity'               => [
                'get' => 'getUserActivity',
            ],
            'me/privacy'                => [
                'get' => 'getUserSetting',
            ],
            'me/account'                => [
                'get' => 'getUserAccount',
            ],
            'user/post-type/:id'        => [
                'get'   => 'getPostTypes',
                'where' => [
                    'id' => '(\d+)',
                ],
            ],
            'user/search-form'          => [
                'get' => 'searchForm',
            ],
            'user/profile/form'         => [
                'get' => 'getEditProfileForm',
            ],
            'user/profile/form/:id'     => [
                'get' => 'getEditProfileForm',
            ],
            'user/profile'              => [
                'put' => 'updateUserProfile',
            ],
            'user/profile/:id'          => [
                'put'   => 'updateUserProfile',
                'where' => [
                    'id' => '(\d+)',
                ],
            ],
            'user/device-token'         => [
                'post'   => 'addUserDeviceToken',
                'delete' => 'deleteUserDeviceToken',
            ],
            'user/avatar/:id'           => [
                'post' => 'uploadAvatar',
            ],
            'user/cover/:id'            => [
                'post' => 'uploadCover',
            ],
            'user/simple/:id'           => [
                'get' => 'getSimpleUser',
            ],
            'user/extra-permission/:id' => [
                'get' => 'getPermissionWithUser'
            ],
            'user/remove-cover'         => [
                'put' => 'removeCover'
            ],
            'user/cancel-account'       => [
                'get'  => 'getCancelAccountForm',
                'post' => 'cancelAccount'
            ],
            'user/sms'                  => [
                'post' => 'sendRegistrationSms',
                'put'  => 'verifyRegistration'
            ],
            'user/cancel-subscription'  => [
                'get' => 'getCancelSubscriptionForm'
            ],
            'user/validate-email'       => [
                'post' => 'validateEmail'
            ],
            'user/poke'                 => [
                'post' => 'pokeUser'
            ],
            'user/validate-fb-id'       => [
                'post' => 'validateFbId'
            ],
            'user/ban-user'             => [
                'post' => 'banFromChat'
            ],
            'user-statistic/:id'            => [
                'get'  => 'getActivityStatistics'
            ]
        ];
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed
     * @throws NotFoundErrorException
     * @throws PermissionErrorException
     * @throws UndefinedResourceName
     * @throws ValidationErrorException
     * @throws ErrorException
     */
    public function getUserInfo($params)
    {
        $id = $this->resolver->resolveSingle($params, 'id');
        if (!$id) {
            $id = $this->getUser()->getId();
        }
        $user = $this->getUserService()->get($id);
        $resource = UserResource::populate($user);
        $oParsed = Phpfox::getService(ParseInterface::class);
        $oLocation = $this->getLocalization();
        if (empty($user)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(UserAccessControl::VIEW_PROFILE_INFO, $resource, null);

        $user['bRelationshipHeader'] = true;
        $relationship = Phpfox::getService('custom')->getRelationshipPhrase($user);
        $data = [
            'id'       => (int)$id,
            'sections' => [],
        ];

        $deniedViewBasic = false;
        if ($this->getAccessControl()->isGranted(UserAccessControl::VIEW_BASIC_INFO, $resource)) {
            $data['sections']['basic_info']['label'] = $oLocation->translate('basic_info');
            if (!empty(trim($relationship)) && $relationship != '_new') {
                $data['sections']['basic_info']['fields']['relationship'] = [
                    'label' => $oLocation->translate('custom_relationship_status'),
                    'value' => $oParsed->cleanOutput($relationship),
                ];
            }
            if (!empty($user['gender'])) {
                if (empty($user['custom_gender'])) {
                    $gender = $this->getUserService()->gender($user['gender']);
                } else {
                    $aCustomGenders = Phpfox::getLib('parse.format')->isSerialized($user['custom_gender']) ? unserialize($user['custom_gender']) : $user['custom_gender'];
                    $gender = '';
                    if (is_array($aCustomGenders)) {
                        if (count($aCustomGenders) > 2) {
                            $sLastGender = $aCustomGenders[count($aCustomGenders) - 1];
                            unset($aCustomGenders[count($aCustomGenders) - 1]);
                            $gender = implode(', ', $aCustomGenders) . ' ' . $oLocation->translate('and') . ' ' . $sLastGender;
                        } else {
                            $gender = implode(' ' . $oLocation->translate('and') . ' ', $aCustomGenders);
                        }
                    }
                }
                $data['sections']['basic_info']['fields']['gender'] = [
                    'label' => $oLocation->translate('gender'),
                    'value' => $oParsed->cleanOutput($gender),
                ];
            }
            $user['birthday_time_stamp'] = $user['birthday'];
            $user['birthday'] = $this->getUserService()->age($user['birthday']);
            $birthday = $this->getUserService()->getProfileBirthDate($user);
            if (!empty($birthday)) {
                $data['sections']['basic_info']['fields']['birthdate'] = [
                    'label' => $oParsed->cleanOutput(key($birthday)),
                    'value' => $oParsed->cleanOutput(isset(array_values($birthday)[0]) ? array_values($birthday)[0] : ''),
                ];
            }

            $extraLocation = '';
            if (!empty($user['city_location'])) {
                $extraLocation .= $user['city_location'] . ' » ';
            }

            if ($user['country_child_id'] > 0 && $sChild = Phpfox::getService('core.country')->getChild($user['country_child_id'])) {
                $extraLocation .= $sChild . ' »';
            }

            if (!empty($user['country_iso']) && Phpfox::getService('user.privacy')->hasAccess($user['user_id'], 'profile.view_location')) {
                $data['sections']['basic_info']['fields']['location'] = [
                    'label' => $oLocation->translate('location'),
                    'value' => $oParsed->cleanOutput($extraLocation . ' ' . Phpfox::getPhraseT(Phpfox::getService('core.country')->getCountry($user['country_iso']), 'country')),
                ];
            }

            if ((int)$user['last_login'] > 0 && ((!$user['is_invisible']) || ($this->getSetting()->getUserSetting('user.can_view_if_a_user_is_invisible') && $user['is_invisible']))) {
                $data['sections']['basic_info']['fields']['last_login'] = [
                    'label' => $oLocation->translate('last_login'),
                    'value' => $oParsed->cleanOutput(Phpfox::getLib('date')->convertTime($user['last_login'], 'core.global_update_time')),
                ];
            }

            if ((int)$user['joined'] > 0) {
                $data['sections']['basic_info']['fields']['member_since'] = [
                    'label' => $oLocation->translate('member_since'),
                    'value' => $oParsed->cleanOutput(Phpfox::getLib('date')->convertTime($user['joined'], 'core.global_update_time')),
                ];
            }

            if (Phpfox::getUserGroupParam($user['user_group_id'], 'profile.display_membership_info')) {
                $data['sections']['basic_info']['fields']['membership'] = [
                    'label' => $oLocation->translate('membership'),
                    'value' => $oParsed->cleanOutput($user['prefix'] . \Phpfox_Locale::instance()->convert($user['title']) . $user['suffix']),
                ];
            }

            $data['sections']['basic_info']['fields']['profile_views'] = [
                'label' => $oLocation->translate('profile_views'),
                'value' => $user['total_view'],
            ];

            if (Phpfox::isAppActive('Core_RSS') && $this->getSetting()->getAppSetting('rss.display_rss_count_on_profile')
                && Phpfox::getService('user.privacy')->hasAccess($user['user_id'], 'rss.display_on_profile')
            ) {
                $data['sections']['basic_info']['fields']['rss_subscribers'] = [
                    'label' => $oLocation->translate('rss_subscribers'),
                    'value' => $user['rss_count'],
                ];
            }
            if ($this->getUser()->getId() == $user['user_id']) {
                if (Phpfox::isAppActive('Core_Activity_Points') && $this->getSetting()->getAppSetting('activitypoint.enable_activity_points')) {
                    $data['sections']['basic_info']['fields']['activity_points'] = [
                        'label' => $oLocation->translate('activity_points'),
                        'value' => $user['activity_points'],
                    ];
                }

                // check total space has used
                $totalUploadSpace = $this->getSetting()->getUserSetting('user.total_upload_space');
                $totalSpaceUsed = $user['space_total'];
                if ($totalUploadSpace > 0 && $totalSpaceUsed > $totalUploadSpace * 1048576) {
                    $totalSpaceUsed = $totalUploadSpace * 1048576;
                }
                $data['sections']['basic_info']['fields']['space_used'] = [
                    'label' => $oLocation->translate('space_used'),
                    'value' => ($this->getSetting()->getUserSetting('user.total_upload_space') === 0
                        ? $oLocation->translate('space_total_out_of_unlimited', ['space_total' => \Phpfox_File::filesize($totalSpaceUsed)])
                        : $oLocation->translate('space_total_out_of_total_unit',
                            ['space_total' => \Phpfox_File::filesize($totalSpaceUsed), 'total_unit' => \Phpfox_File::filesize($totalUploadSpace)])),
                ];
            }
        } else {
            $deniedViewBasic = true;
        }
        $customDataMain = Phpfox::getService('custom')->getForDisplay('user_main', $id, $user['user_group_id']);
        $customDataBasic = Phpfox::getService('custom')->getForDisplay('user_panel', $id, $user['user_group_id']);
        $disabledGroup = [];
        $enabledGroup = [];

        foreach (array_merge($customDataMain, $customDataBasic) as $customKey => $value) {
            if (empty($value['value'])) {
                continue;
            }
            if (!empty($value['group_id']) && !in_array($value['group_id'], $enabledGroup)) {
                if (in_array($value['group_id'], $disabledGroup)) {
                    //Group disabled
                    continue;
                }
                $group = Phpfox::getService('custom.group')->getGroup($value['group_id']);
                if (!$group['is_active']) {
                    $disabledGroup[] = $group['group_id'];
                    continue;
                } else {
                    $enabledGroup[] = $group['group_id'];
                }
            }
            if ($value && is_array($value) && $value['is_active']) {
                $data['sections'][$customKey]['label'] = $oLocation->translate($value['phrase_var_name']);
                if (is_array($value['value'])) {
                    foreach ($value['value'] as $key => $item) {
                        $value['value'][$key] = $oLocation->translate($item);
                    }
                    $result = implode(', ', $value['value']);
                } else {
                    if (in_array($value['var_type'], ['radio', 'select'])) {
                        $result = $oLocation->translate($value['value']);
                    } elseif ($value['var_type'] == 'date') {
                        $result = $value['value'] ? Phpfox::getService('custom.process')->formatDateFieldOrder($value['value'], '/') : '';
                    } else {
                        $result = $value['value'];
                    }
                }
                $data['sections'][$customKey]['fields'][$customKey] = [
                    'label' => '',
                    'value' => $result,
                ];
            }
        }
        if ($deniedViewBasic && empty($data['sections'])) {
            return $this->permissionError($this->getLocalization()->translate('you_are_not_allowed_to_view_this_basic_information'));
        }
        return $this->success($data);
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed
     * @throws ErrorException
     * @throws NotFoundErrorException
     * @throws ValidationErrorException
     */
    public function getUserActivity($params)
    {
        $id = $this->resolver->resolveSingle($params, 'user_id');
        if (!$id) {
            $id = Phpfox::getUserId();
        }
        $user = $this->getUserService()->get($id, true);
        if (empty($user['user_id'])) {
            return $this->notFoundError();
        }
        $modules = Phpfox::massCallback('getDashboardActivity');
        $items = [];
        $secondItems = [];
        $secondSection = [
            'invite'     => [
                'icon_name'   => 'list-plus',
                'icon_family' => 'Lineficon',
                'icon_color'  => '#555555'
            ],
            'comment'    => [
                'icon_name'   => 'comment-o',
                'icon_family' => 'Lineficon',
                'icon_color'  => '#555555'
            ],
            'attachment' => [
                'icon_name'   => 'paperclip-alt',
                'icon_family' => 'Lineficon',
                'icon_color'  => '#555555'
            ]
        ];
        $allMenus = Phpfox::getService('mobile.admincp.menu')->getForBrowse();
        $allSimpleMenus = [];
        if (!empty($allMenus['item'])) {
            foreach ($allMenus['item'] as $menu) {
                $allSimpleMenus[$menu['module_id']] = [
                    'icon_name'   => $menu['icon_name'],
                    'icon_family' => $menu['icon_family'],
                    'icon_color'  => $menu['icon_color']
                ];
            }
        }
        $defaultIcon = [
            'icon_name'   => 'box',
            'icon_family' => 'Lineficon',
            'icon_color'  => '#555555'
        ];
        foreach ($modules as $key => $aModule) {
            foreach ($aModule as $sPhrase => $point) {
                $sPhrase = html_entity_decode($sPhrase, ENT_QUOTES);
                if (isset($secondSection[$key])) {
                    $secondItems[] = array_merge([
                        'label' => $sPhrase,
                        'value' => $point
                    ], $secondSection[$key]);
                } else {
                    $subPoint = [
                        'label' => $sPhrase,
                        'value' => $point
                    ];
                    $subPoint = array_merge($subPoint, isset($allSimpleMenus[$key]) ? $allSimpleMenus[$key] : $defaultIcon);
                    $items[] = $subPoint;
                }
            }
        }

        $activities = [
            'id'             => (int)$id,
            'total_items'    => [
                'label' => $this->getLocalization()->translate('total_items'),
                'value' => $user['activity_total'],
            ],
            'total_points'   => [
                'label' => $this->getLocalization()->translate('activity_points'),
                'value' => $user['activity_points'],
            ],
            'items'          => $items,
            'addition_items' => $secondItems,
        ];


        return $this->success($activities);
    }

    public function getUserAccount($params)
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);

        $user = $this->getUserService()->get(Phpfox::getUserId(), true);

        return $this->success(UserResource::populate($user)->toArray([
            'full_name',
            'user_name',
            'email',
            'language_id',
            'time_zone',
            'default_currency',
        ]));
    }

    public function getUserSetting($params = [])
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);

        list($aUserPrivacy,
            $aNotifications,
            $aProfiles,
            $aItems)
            = Phpfox::getService('user.privacy')->get(Phpfox::getUserId());
        return $this->success([
            'privacy'      => $aUserPrivacy['privacy'],
            'notification' => $aNotifications,
            'profile'      => $aProfiles,
            'item'         => $aItems,
        ]);
    }

    /**
     * @param $params
     *
     * @return array|bool
     * @throws ValidationErrorException
     * @throws ErrorException
     */
    public function unblockUser($params)
    {
        $userId = $this->resolver->resolveSingle($params, 'id', 'int');
        Phpfox::getService('user.block.process')->delete($userId);
        return $this->success();
    }

    /**
     * @param array $params
     *
     * @return array|bool
     * @throws PermissionErrorException
     */
    public function getBlockedUsers($params = [])
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);

        $blocked = Phpfox::getService('user.block')->get(Phpfox::getUserId());
        if (empty($blocked)) {
            return $this->success([]);
        }
        $result = array_map(function ($user) {
            return UserResource::populate($user)->displayShortFields()->toArray();
        }, $blocked);

        return $this->success($result);
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws Exception
     */
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(UserAccessControl::VIEW);
        $params = $this->resolver->setDefined(['limit', 'page', 'q', 'gender', 'age_from', 'age_to', 'sort', 'view', 'city', 'zip_code', 'country', 'state', 'country_state', 'age'])
            ->setDefault([
                'limit' => 10,
                'page'  => 1,
            ])
            ->setAllowedValues('sort', ['name', 'active'])
            ->setAllowedValues('view', ['online', 'featured', 'recommend'])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        //Get customs search
        $form = $this->createForm(UserSearchForm::class);
        $customs = $form->getGroupValues('custom');
        $customs = array_filter($customs);

        if (!empty($params['country_state']) && is_array($params['country_state'])) {
            $params['country'] = isset($params['country_state'][0]) ? $params['country_state'][0] : '';
            $params['state'] = isset($params['country_state'][1]) ? $params['country_state'][1] : 0;
        }

        if (!empty($params['age']) && is_array($params['age'])) {
            $params['age_from'] = isset($params['age']['from']) ? $params['age']['from'] : null;
            $params['age_to'] = isset($params['age']['to']) ? $params['age']['to'] : null;
        }
        if ($params['view'] != 'recommend' && $params['view'] != 'featured' && $params['view'] != 'online') {
            $search = $params['q'];
            $gender = $params['gender'];
            $from = $params['age_from'];
            $to = $params['age_to'];
            $country = $params['country'];
            $state = $params['state'];
            $city = $params['city'];
            $zipcode = $params['zip_code'];
            $year = intval(date('Y'));
            $sort = ($params['sort'] == 'active' ? 'u.last_activity DESC' : 'u.full_name ASC');

            $bIsGender = false;

            $aConditions = [];

            $aConditions[] = "AND (u.profile_page_id = 0) ";

            $oDb = Phpfox::getLib('database');

            if ($search) {
                $aConditions[] = 'AND (u.user_name LIKE \'%' . $oDb->escape($search) . '%\' OR u.full_name LIKE \'%' . $oDb->escape($search) . '%\' OR u.email LIKE \'%' . $oDb->escape($search)
                    . '%\')';
            }

            if ($gender) {
                $aConditions[] = 'AND u.gender = \'' . $oDb->escape($gender) . '\'';
            }
            if ($country != 'null' && $country) {
                $aConditions[] = 'AND u.country_iso = \'' . $oDb->escape($country) . '\'';
            }

            if ((int)$state > 0) {
                $aConditions[] = 'AND ufield.country_child_id = ' . (int)$state;
            }

            if ($city) {
                $aConditions[] = 'AND ufield.city_location = \'' . $oDb->escape(Phpfox::getLib('parse.input')->convert($city)) . '\'';
            }

            if ($zipcode) {
                $aConditions[] = 'AND ufield.postal_code = ' . $zipcode;
            }
            $bAgeSearch = false;
            if ($from) {
                $aConditions[] = 'AND u.birthday_search <= \'' . Phpfox::getLib('date')->mktime(0, 0, 0, 1, 1, $year - $from) . '\'' . ((defined('PHPFOX_IS_ADMIN_SEARCH')
                        && $this->getSetting()->getUserSetting('user.remove_users_hidden_age')) ? '' : ' AND ufield.dob_setting IN(0,1,2)');
                $bIsGender = true;
                $bAgeSearch = true;
            }

            if ($to) {
                $aConditions[] = 'AND u.birthday_search >= \'' . Phpfox::getLib('date')->mktime(0, 0, 0, 1, 1, $year - $to) . '\'' . ((defined('PHPFOX_IS_ADMIN_SEARCH')
                        && $this->getSetting()->getUserSetting('user.remove_users_hidden_age')) ? '' : ' AND ufield.dob_setting IN(0,1,2)');
                $bIsGender = true;
                $bAgeSearch = true;
            }
            if ($bAgeSearch) {
                $aConditions[] = 'AND u.birthday IS NOT NULL';
            }
            if ($this->getUser()->getId()) {
                $aBlockedUserIds = Phpfox::getService('user.block')->get(null, true);
                if (!empty($aBlockedUserIds)) {
                    $aConditions[] = 'AND u.user_id NOT IN (' . implode(',', $aBlockedUserIds) . ')';
                }
            }
            $aConditions[] = 'AND u.status_id = 0 AND u.view_id = 0';

            // search by conditions.


            /** @var \User_Service_Browse $browseService */
            $browseService = Phpfox::getService('user.browse');

            list($iCnt, $aUsers) = $browseService
                ->extend(true)
                ->conditions($aConditions)
                ->page($params['page'])
                ->limit($params['limit'])
                ->gender($bIsGender)
                ->sort($sort)
                ->custom($customs)
                ->get();

            if ($iCnt < ($params['page'] - 1) * $params['limit']) {
                return $this->success();
            }
        } else {
            $isNoFriend = false;
            if ($params['view'] == "recommend") {
                $isNoFriend = true;
                if ($this->getUser()->getId() > 0 && Phpfox::isModule('friend')) {
                    $aUsers = Phpfox::getService('friend.suggestion')->get();
                } else {
                    $aUsers = [];
                }
            } else if ($params['view'] == "featured") {
                list($aUsers) = Phpfox::getService('user.featured')->get();
                uasort($aUsers, function ($a, $b) {
                    return ($a['full_name'] > $b['full_name']);
                });
                $aUsers = array_values($aUsers);

            } else {
                $aUsers = Phpfox::getService('user.featured')->getRecentActiveUsers();
            }
            if(Phpfox::isModule('friend')) {
                if (count($aUsers)) {
                    foreach ($aUsers as $key => $aUser) {
                        $aUsers[$key]['is_friend'] = $isNoFriend ? 0 : Phpfox::getService('friend')->isFriend($this->getUser()->getId(), $aUser['user_id']);
                    }
                }
            }
        }
        if ($aUsers) {
            $this->processRows($aUsers);
        }

        return $this->success($aUsers);
    }

    public function processRow($item)
    {
        /** @var UserResource $resource */
        $resource = $this->populateResource(UserResource::class, $item);
        $resource->setSelf([
            UserAccessControl::VIEW   => $this->createHyperMediaLink(UserAccessControl::VIEW,
                $resource,
                HyperLink::GET, 'user/:id',
                ['id' => $resource->getId()]),
            UserAccessControl::DELETE => $this->createHyperMediaLink(UserAccessControl::DELETE,
                $resource, HyperLink::DELETE,
                'user/:id',
                ['id' => $resource->getId()]),
        ]);

        return $resource
            ->setViewMode(ResourceBase::VIEW_LIST)
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->toArray();
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws Exception
     */
    public function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $extraOnly = $this->resolver->resolveSingle($params, 'only_extra');

        $user = $this->getUserService()->get($id);
        $getSimple = !empty($params['simple_data']);
        if (empty($user)) {
            $this->notFoundError('User Not Found');
        }
        $purchase = [];
        if (!empty($params['is_me'])) {
            if ($user['view_id'] == 1) {
                $this->permissionError($this->getLocalization()->translate('your_account_is_pending_approval'));
            }
            //Check banned
            if ($aBanned = Phpfox::getService('ban')->isUserBanned()) {
                if (isset($aBanned['ban_data_id'])) {
                    if (isset($aBanned['is_expired']) && $aBanned['is_expired'] == 0
                        && isset($aBanned['end_time_stamp']) && ($aBanned['end_time_stamp'] == 0 || $aBanned['end_time_stamp'] >= PHPFOX_TIME)
                    ) {
                        if (isset($aBanned['reason']) && !empty($aBanned['reason'])) {
                            $sReason = html_entity_decode(Phpfox::getLib('parse.output')->parse($aBanned['reason']), ENT_QUOTES);
                            $banMessage = $this->getLocalization()->translate('you_have_been_banned_for_the_following_reason', ['reason' => $sReason]) . '.';
                        } else {
                            $banMessage = $this->getLocalization()->translate('global_ban_message');
                        }
                        if ($aBanned['end_time_stamp']) {
                            $banMessage .= ' ' . $this->getLocalization()->translate('the_ban_will_be_expired_on_datetime', ['datetime' => Phpfox::getTime($this->getSetting()->getAppSetting('core.global_update_time'), $aBanned['end_time_stamp'])]);
                        }
                        $this->permissionError($banMessage);
                    } else {
                        if (isset($aBanned['return_user_group']) && !empty($aBanned['returned_user_group'])) {
                            $this->database()->update(Phpfox::getT('user'), ['user_group_id' => $aBanned['return_user_group']], 'user_id = ' . $user['user_id']);
                        } else {
                            $this->database()->update(Phpfox::getT('user'), ['user_group_id' => NORMAL_USER_ID], 'user_id = ' . $user['user_id']);
                        }
                        $this->database()->update(Phpfox::getT('ban_data'), ['is_expired' => '1'], 'user_id = ' . $user['user_id']);
                    }
                }
            }
            //Check subscription pending
            if (!empty($user['subscribe_id']) && (int)$user['subscribe_id'] > 0) {
                $valueDefault = Phpfox::getService('subscribe.purchase')->getSubscriptionsIdPurchasedByUser($user['user_id']);
                if (empty($valueDefault)) {
                    $purchase = (new SubscriptionApi())->loadPurchaseById($user['subscribe_id'], true);
                }
            }
        }

        if (!empty($user['cover_photo_exists'])) {
            $user['cover'] = $this->getUserCover($user['cover_photo_exists']);
        }
        /** @var UserResource $user */
        $user = $this->populateResource(UserResource::class, $user);
        $canViewProfile = $this->getAccessControl()->isGranted(UserAccessControl::VIEW_PROFILE, $user);
        if (!$getSimple && $canViewProfile) {
            $permission = $this->getAccessControl()->getPermissions($user);
            if (!empty($params['is_me'])) {
                $permission['can_' . UserAccessControl::USE_GLOBAL_SEARCH] = $this->getAccessControl()->isGranted(UserAccessControl::USE_GLOBAL_SEARCH);
                $permission['can_' . UserAccessControl::USE_INVISIBLE_MODE] = $this->getAccessControl()->isGranted(UserAccessControl::USE_INVISIBLE_MODE);
                $permission['mature_age_limit'] = $this->getSetting()->getUserSetting('photo.photo_mature_age_limit');
                $user->setIsMe(true);
            }
            $user->setExtra($permission);
            // Implement resource self reference
            $user->setSelf([
                UserAccessControl::VIEW   => $this->createHyperMediaLink(UserAccessControl::VIEW, $user, HyperLink::GET, 'user/:id', ['id' => $user->getId()]),
                UserAccessControl::DELETE => $this->createHyperMediaLink(UserAccessControl::DELETE, $user, HyperLink::DELETE, 'user/:id', ['id' => $user->getId()]),
            ]);
            // Implement resource self reference
            $user->setLinks([
                'photos'  => $this->createHyperMediaLink(null, $user, HyperLink::GET, 'photo', ['profile_id' => $user->getId()]),
                'friends' => $this->createHyperMediaLink(null, $user, HyperLink::GET, 'friend', ['user_id' => $user->getId()]),
            ]);
            $result = $user->setViewMode(ResourceBase::VIEW_DETAIL)->toArray();
        } else {
            if (!$canViewProfile && !$getSimple) {
                if ($user->getIsBlocked()) {
                    $this->error($this->getLocalization()->translate('sorry_information_of_this_user_isn_t_available_for_you'));
                }
                $permission = $this->getAccessControl()->getPermissions($user);
                $user->setExtra($permission);
                $result = $user->toArray(['id', 'module_name', 'resource_name', 'user_name', 'friendship', 'full_name', 'avatar', 'friend_id', 'is_featured', 'extra', 'profile_menus', 'statistic', 'cover']);
            } else {
                if ($extraOnly) {
                    $permission = $this->getAccessControl()->getPermissions($user);
                    $user->setExtra($permission);
                    $result = $user->toArray(['id', 'module_name', 'resource_name', 'user_name', 'friendship', 'full_name', 'avatar', 'friend_id', 'is_featured', 'extra']);
                } else {
                    $result = $user->displayShortFields()->toArray();
                }
            }
        }
        if (!empty($purchase) && !empty($params['is_me'])) {
            $result['pending_purchase'] = $purchase;
            $result['pending_purchase']['extra_action'] = [
                'label'  => $this->getLocalization()->translate('change_membership_package'),
                'action' => 'subscription/change-package',
                'params' => [
                    'module_name' => 'subscription'
                ]
            ];
        }
        return $this->success($result);
    }

    public function findMe()
    {
        if ($this->getUser()->getId()) {
            return $this->findOne(['id' => $this->getUser()->getId(), 'is_me' => true]);
        }
        return $this->success([]);
    }

    public function getSimpleUser($params)
    {
        $id = $this->resolver->resolveId($params);
        $user = $this->getUserService()->getUser($id);

        $image = Image::createFrom([
            'user' => $user,
        ], ["50_square"]);

        $resourceName = 'user';
        if ((!$user || $user['profile_page_id'] > 0) && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups'))) {
            $page = $this->database()->select('p.*, pu.vanity_url')
                ->from(':pages', 'p')
                ->leftJoin(':pages_url', 'pu', 'pu.page_id = p.page_id')
                ->where('p.page_id = ' . intval($user ? $user['profile_page_id'] : $id))
                ->execute('getSlaveRow');
            if ($page) {
                $resourceName = $page['item_type'] == 0 ? 'pages' : 'groups';
                $image = Image::createFrom([
                    'file'      => $page['image_path'],
                    'server_id' => $page['image_server_id'],
                    'path'      => 'pages.url_image'
                ], ["50"]);
                $avatar = $image ? ($image->sizes['50'] ? $image->sizes['50'] : $image->image_url) : ($resourceName == 'pages' ? PageResource::populate([])->getDefaultImage() : GroupResource::populate([])->getDefaultImage());

                return $this->success([
                    'id'              => intval($page['page_id']),
                    'full_name'       => $page['title'],
                    'avatar'          => $avatar,
                    'resource_name'   => $resourceName,
                    'profile_page_id' => (int)$user['profile_page_id']
                ]);
            } else {
                return $this->success([
                    'id'              => intval($id),
                    'full_name'       => $this->getLocalization()->translate('deleted_user'),
                    'avatar'          => UserResource::populate([])->getDefaultImage(),
                    'profile_page_id' => 0,
                    'resource_name'   => $resourceName
                ]);
            }
        }

        $avatar = $image ? ($image->sizes['50_square'] ? $image->sizes['50_square'] : $image->image_url) : UserResource::populate([])->getDefaultImage();

        return $this->success([
            'id'              => intval($id),
            'full_name'       => isset($user['full_name']) ? $user['full_name'] : $this->getLocalization()->translate('deleted_user'),
            'avatar'          => $avatar,
            'resource_name'   => $resourceName,
            'profile_page_id' => (int)$user['profile_page_id'],
            'user_name'       => $user['user_name']
        ]);
    }

    /**
     * Register user
     *
     * @param $params
     *
     * @return mixed
     * @throws Exception
     */
    public function create($params)
    {
        // by pass Anti-Spam Security Questions
        if (!defined('PHPFOX_IS_FB_USER')) {
            define('PHPFOX_IS_FB_USER', true);
        }
        $this->denyAccessUnlessGranted(UserAccessControl::ADD);
        $form = $this->createForm(UserRegisterForm::class);
        if ($form->isValid() && $values = $form->getValues()) {
            // force subscription
            $values['package_id'] = 9999;
            $values['custom'] = $form->getGroupValues('custom');
            if (!empty($values['gender']) && $values['gender'] == '127') {
                $values['gender'] = 'custom';
            }
            if (!$id = $this->processCreate($values)) {
                return $this->error($this->getErrorMessage());
            }
            $user = $this->getUserService()->get($id, true);
            if ($user) {
                return $this->success([
                    'id'                  => (int)$user['user_id'],
                    'email'               => $user['email'],
                    'password'            => $values['password'],
                    'status_id'           => (int)$user['status_id'],
                    'default_country_iso' => Phpfox::getLib('request')->getIpInfo(null, 'country_code'),
                ], []);
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->error($this->getErrorMessage());
    }

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws Exception
     */
    public function update($params)
    {
        // TODO: Implement update() method.
    }

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws Exception
     */
    public function delete($params)
    {
        $id = $this->resolver->resolveId($params);

        if (!($user = $this->loadResourceById($id, true))) {
            $this->notFoundError($this->getLocalization()->translate('unable_to_find_the_user_you_want_to_delete'));
        }

        $this->denyAccessUnlessGranted(UserAccessControl::DELETE, $this->getUser());

        define('PHPFOX_CANCEL_ACCOUNT', true);
        Phpfox::getService('user.auth')->setUserId($id);
        Phpfox::massCallback('onDeleteUser', $id);
        Phpfox::getService('user.auth')->setUserId(null);

        if ($this->isPassed()) {
            return $this->success([
                'id' => $id,
            ]);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    /**
     * Load detail user resource data
     *
     * @param      $id
     * @param bool $resource
     * @param bool $shortData
     *
     * @return ResourceBase|UserResource|null
     */
    public function loadResourceById($id, $resource = false, $shortData = false)
    {
        $userArray = $shortData ? $this->getUserService()->getUser($id) : $this->getUserService()->get($id);
        if (empty($userArray)) {
            return null;
        }
        if (!$resource) {
            return $userArray;
        }
        return $this->populateResource(UserResource::class, $userArray);

    }

    /**
     * Get user register form
     *
     * @param array $params
     *
     * @return mixed
     * @throws Exception
     */
    public function form($params = [])
    {
        $this->denyAccessUnlessGranted(UserAccessControl::ADD);
        $form = $this->createForm(UserRegisterForm::class, [
            'title'  => $this->getLocalization()->translate('sign_up'),
            'action' => UrlUtility::makeApiUrl('user'),
            'method' => 'post',
        ]);

        return $this->success($form->getFormStructure());
    }

    /**
     * User Edit profile form
     *
     * @param array $params
     *
     * @return array|bool|mixed
     * @throws NotFoundErrorException
     * @throws UndefinedResourceName
     * @throws PermissionErrorException
     */
    public function getEditProfileForm($params = [])
    {
        $userId = $this->resolver->resolveId($params);

        if ($userId) {
            $user = $this->getUserService()->getUser($userId);
            if (!$user) {
                $this->notFoundError();
            }
            $user = UserResource::populate($user);
        } else {
            $user = $this->getUser();
        }
        $this->denyAccessUnlessGranted(UserAccessControl::EDIT, $user);

        /** @var EditProfileForm $form */
        $form = $this->createForm(EditProfileForm::class, [
            'title'  => $this->getLocalization()->translate('edit_profile'),
            'action' => UrlUtility::makeApiUrl('user/profile'),
            'method' => 'put',
        ]);
        $form->setUserId($user->getId());
        $form->setUserGroupId($user->getGroupId());
        $form->assignValues($this->loadResourceById($user->getId()));

        return $this->success($form->getFormStructure());
    }

    /**
     * Update User Profile
     *
     * @param array $params
     *
     * @return array|bool|mixed
     * @throws NotFoundErrorException
     * @throws UndefinedResourceName
     * @throws UnknownErrorException
     * @throws ValidationErrorException
     * @throws PermissionErrorException
     */
    public function updateUserProfile($params = [])
    {
        /** @var EditProfileForm $form */
        $form = $this->createForm(EditProfileForm::class);
        $userId = $this->resolver->resolveId($params);

        if ($userId) {
            $user = $this->getUserService()->getUser($userId);
            if (!$user) {
                return $this->notFoundError();
            }
            $user = UserResource::populate($user);
        } else {
            $user = $this->getUser();
            $userId = $this->getUser()->getId();
        }
        $form->setUserId($userId);
        $form->setUserGroupId($user->getGroupId());

        $this->denyAccessUnlessGranted(UserAccessControl::EDIT, $user);

        if ($form->isValid() && ($values = $form->getValues())) {
            define('PHPFOX_IS_CUSTOM_FIELD_UPDATE', true);
            if (!empty($values['relation_with']) && is_array($values['relation_with'])) {
                $values['relation_with'] = $values['relation_with'][0];
            } else if (isset($values['previous_relation_with'])) {
                $values['relation_with'] = $values['previous_relation_with'];
            }
            if (!empty($values['gender']) && $values['gender'] == '127') {
                $values['gender'] = 'custom';
            }
            $return = ($this->getProcessService()->update($userId, $values)
                && Phpfox::getService('custom.process')
                    ->updateFields($userId, Phpfox::getUserId(), $form->getGroupValues('custom')));

            if ($return && $this->isPassed()) {
                return $this->success([], [], 'profile_successfully_updated');
            }
            return $this->error($this->getErrorMessage());
        }

        return $this->validationParamsError($form->getInvalidFields());

    }

    /**
     * Request password form and submit
     *
     * @param $params
     *
     * @return array|bool|mixed
     * @throws UnknownErrorException
     * @throws ValidationErrorException
     */
    public function passwordRequest($params)
    {
        $form = $this->createForm(ForgetPasswordRequest::class);
        if ($this->request()->isPut()) {
            if ($form->isValid()) {
                Phpfox::getService('user.password')->requestPassword($form->getValues());

                if ($this->isPassed()) {
                    return $this->success([], [],
                        'password_request_successfully_sent_check_your_email_to_verify_your_request');
                }
                return $this->error($this->getErrorMessage());
            }
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->success($form->getFormStructure());
    }

    /**
     * Change User Password
     *
     * @param $params
     *
     * @return array|bool|mixed
     * @throws UnknownErrorException
     * @throws ValidationErrorException
     * @throws PermissionErrorException
     */
    public function changePassword($params)
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);
        /** @var ChangePasswordForm $form */
        $form = $this->createForm(ChangePasswordForm::class);
        //Pass Fb user
        $user = storage()->get('fb_new_users_' . $this->getUser()->getId());
        $form->setPassOldPassword(!empty($user));

        if ($this->request()->isPut()) {
            if ($form->isValid()) {
                $this->getProcessService()->updatePassword($form->getValues());

                if ($this->isPassed()) {
                    return $this->success([], [
                        'message' => $this->getLocalization()
                            ->translate(
                                'password_successfully_updated'),
                    ]);
                }

                return $this->error($this->getErrorMessage());

            }
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->success($form->getFormStructure());
    }


    /**
     * @return \User_Service_Process
     */
    protected function getProcessService()
    {
        if (!$this->processService) {
            $this->processService = Phpfox::getService("user.process");
        }
        return $this->processService;
    }

    /**
     * @return \User_Service_User
     */
    protected function getUserService()
    {
        if (!$this->userService) {
            $this->userService = Phpfox::getService('user');
        }
        return $this->userService;
    }

    public function getUserCover($photoId, $suffix = '')
    {
        $photo = $this->database()->select('*')
            ->from(":photo")
            ->where("photo_id = " . (int)$photoId)
            ->execute("getSlaveRow");
        return !empty($photo) ? Image::createFrom([
            'server_id' => $photo['server_id'],
            'path'      => 'photo.url_photo',
            'file'      => $photo['destination'],
            'suffix'    => $suffix,
        ]) : null;
    }

    public function createAccessControl()
    {
        $this->accessControl = new UserAccessControl($this->getSetting(), $this->getUser());
    }

    protected function processCreate($values)
    {
        if (!empty($values['custom'])) {
            // Hard code to bypass custom fields checking
            \Phpfox_Request::instance()->set('custom', $values['custom']);
        }
        if (isset($values['user_name']) && !$this->getSetting()->getAppSetting('user.profile_use_id') && ($this->getSetting()->getAppSetting('user.disable_username_on_sign_up') != 'full_name')) {
            Phpfox::getService('user.validate')->user($values['user_name'], true);
        }
        $this->validateSignupEmail($values['email']);
        if (!Phpfox_Error::isPassed()) {
            return false;
        }
        return $this->getProcessService()->add($values);
    }

    protected function validateSignUpEmail($email)
    {
        $iCnt = $this->database()->select('COUNT(*)')
            ->from(':user')
            ->where("email = '" . $this->database()->escape($email) . "'")
            ->execute('getSlaveField');
        if ($iCnt) {
            Phpfox_Error::set(_p('mobile_email_is_in_use_and_user_can_login', array('email' => trim(strip_tags($email)))));
        }
    }

    public function getProfileMenus($id)
    {
        $user = $this->loadResourceById($id);
        $userResource = UserResource::populate($user);
        if (empty($user['user_id']) || Phpfox::getService('user.block')->isBlocked($user['user_id'], $this->getUser()->getId())) {
            return [];
        }

        $query = ['user_id' => $id, 'profile_id' => $id, 'limit' => 12];

        $defaultMenu = $this->defaultProfileMenu();

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_userapi_getprofilemenu_start')) ? eval($sPlugin) : false);

        $result = [];
        $showEmpty = $this->getSetting()->getAppSetting('profile.show_empty_tabs');
        $local = $this->getLocalization();

        foreach ($defaultMenu as $menu) {
            if (!$showEmpty && (!isset($user["total_{$menu['total_type']}"]) || !$user["total_{$menu['total_type']}"])) {
                continue;
            }
            if (!empty($menu['perm']) && !$this->getAccessControl()->isGrantedSetting($menu['perm'])) {
                continue;
            }
            if (!empty($menu['access_perm']) && !$this->getAccessControl()->isGranted($menu['access_perm'], $userResource)) {
                continue;
            }
            $label = $local->translate($menu['label']);
            $result[$menu['resource_name']] = [
                'label'  => $label,
                'path'   => !empty($menu['path']) ? $menu['path'] : "{$menu['resource_name']}/list-item",
                'params' => [
                    'headerTitle' => $local->translate('full_name_s_item', ['full_name' => $user['full_name'], 'item' => $label]),
                    'query'       => $query,
                ],
            ];
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_userapi_getprofilemenu_end')) ? eval($sPlugin) : false);

        return $result;
    }

    public function defaultProfileMenu()
    {
        return [
            [
                'module_name'   => 'photo',
                'resource_name' => PhotoResource::RESOURCE_NAME,
                'total_type'    => 'photo',
                'perm'          => 'photo.can_view_photos',
                'access_perm'   => UserAccessControl::VIEW_PHOTO,
                'label'         => 'photos',
            ],
            [
                'module_name'   => 'photo',
                'resource_name' => PhotoAlbumResource::RESOURCE_NAME,
                'total_type'    => 'photo',
                'perm'          => ['photo.can_view_photos', 'photo.can_view_photo_albums'],
                'access_perm'   => UserAccessControl::VIEW_PHOTO,
                'label'         => 'photo_albums',
            ],
            [
                'module_name'   => 'friend',
                'resource_name' => FriendResource::RESOURCE_NAME,
                'total_type'    => 'friend',
                'label'         => 'friends',
                'access_perm'   => UserAccessControl::VIEW_FRIEND,
                'path'          => 'userFriends',
            ],
            [
                'module_name'   => 'video',
                'resource_name' => VideoResource::RESOURCE_NAME,
                'total_type'    => 'video',
                'perm'          => 'v.pf_video_view',
                'label'         => 'Videos',
            ],
            [
                'module_name'   => 'blog',
                'resource_name' => BlogResource::RESOURCE_NAME,
                'total_type'    => 'blog',
                'perm'          => 'blog.view_blogs',
                'label'         => 'blogs',
            ],
            [
                'module_name'   => 'poll',
                'resource_name' => PollResource::RESOURCE_NAME,
                'total_type'    => 'poll',
                'perm'          => 'poll.can_access_polls',
                'label'         => 'polls',
            ],
            [
                'module_name'   => 'quiz',
                'resource_name' => QuizResource::RESOURCE_NAME,
                'total_type'    => 'quiz',
                'perm'          => 'quiz.can_access_quiz',
                'label'         => 'quizzes',
            ],
            [
                'module_name'   => 'event',
                'resource_name' => EventResource::RESOURCE_NAME,
                'total_type'    => 'event',
                'perm'          => 'event.can_access_event',
                'label'         => 'events',
            ],
            [
                'module_name'   => 'marketplace',
                'resource_name' => MarketplaceResource::RESOURCE_NAME,
                'total_type'    => 'listing',
                'perm'          => 'marketplace.can_access_marketplace',
                'label'         => 'listings',
            ],
            [
                'module_name'   => 'group',
                'resource_name' => GroupResource::RESOURCE_NAME,
                'total_type'    => 'groups',
                'perm'          => 'groups.pf_group_browse',
                'label'         => 'groups',
            ],
            [
                'module_name'   => 'page',
                'resource_name' => PageResource::RESOURCE_NAME,
                'total_type'    => 'pages',
                'perm'          => 'pages.can_view_browse_pages',
                'label'         => 'pages',
            ],
            [
                'module_name'   => 'music',
                'resource_name' => MusicSongResource::RESOURCE_NAME,
                'total_type'    => 'song',
                'perm'          => 'music.can_access_music',
                'label'         => 'music_songs',
            ],
            [
                'module_name'   => 'music',
                'resource_name' => MusicAlbumResource::RESOURCE_NAME,
                'total_type'    => 'song',
                'perm'          => 'music.can_access_music',
                'label'         => 'music_albums',
            ],
        ];
    }

    public function getPostTypes($id)
    {
        $postOptions = [];
        $userId = $this->getUser()->getId();

        if (!$userId) {
            return $this->permissionError();
        }
        if (!Phpfox::isModule('feed') || !Phpfox::getService('user.privacy')->hasAccess($id, 'feed.share_on_wall') || !Phpfox::getService('user.privacy')->hasAccess($id, 'feed.view_wall')
            || Phpfox::getService('user.block')->isBlocked($id, $this->getUser()->getId())
        ) {
            return [];
        }
        if ($userId == $id || ($this->getSetting()->getUserSetting('profile.can_post_comment_on_profile'))) {
            $postOptions[] = (new CoreApi())->getPostOption('status');

            if (Phpfox::isAppActive('Core_Photos') && $this->getSetting()->getUserSetting('photo.can_upload_photos')) {
                $postOptions[] = (new CoreApi())->getPostOption('photo');
            }
            if (Phpfox::isAppActive('PHPfox_Videos') && $this->getSetting()->getUserSetting('v.pf_video_share')
                && $this->getSetting()->getUserSetting('v.pf_video_view')) {
                $postOptions[] = (new CoreApi())->getPostOption('video');
            }
            if ($this->getSetting()->getAppSetting('feed.enable_check_in') && $this->getSetting()->getAppSetting('core.google_api_key')) {
                $postOptions[] = (new CoreApi())->getPostOption('checkin');
            }
        }
        (($sPlugin = \Phpfox_Plugin::get('mobile.service_userapi_getposttype_end')) ? eval($sPlugin) : false);

        return $postOptions;
    }

    /**
     * @param array $params
     *
     * @return mixed
     * @throws PermissionErrorException
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(UserAccessControl::VIEW);
        /** @var UserSearchForm $form */
        $form = $this->createForm(UserSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('user'),
        ]);
        $form->setAllowSort(false);
        $form->setAllowWhen(false);

        return $this->success($form->getFormStructure());
    }

    public function addUserDeviceToken($params)
    {
        $params = $this->resolver->setDefined(['device_token', 'device_platform', 'token_source', 'device_id', 'device_uid'])
            ->setRequired(['device_token'])
            ->setDefault([
                'token_source' => 'firebase',
            ])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $userId = $this->getUser()->getId();
        if (!$userId) {
            return $this->success([]);
        }

        if (Phpfox::getService('mobile.device')->addDeviceToken([
            'user_id'   => $userId,
            'token'     => $params['device_token'],
            'platform'  => isset($params['device_platform']) ? $params['device_platform'] : '',
            'device_id' => !empty($params['device_uid']) ? $params['device_uid'] : (isset($params['device_id']) ? $params['device_id'] : ''),
            'source'    => $params['token_source'],
        ])
        ) {
            Phpfox::getService(PushNotificationInterface::class)
                ->updateUserTokenDeviceGroup($userId, $params['device_token'], null);
            return $this->success([
                'token' => $params['device_token'],
            ]);
        }
        return $this->permissionError();
    }

    public function deleteUserDeviceToken($params)
    {
        $params = $this->resolver->setDefined(['device_token', 'device_uid', 'device_id'])
            ->setRequired(['device_token'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $userId = !empty($params['user_id']) ? $params['user_id'] : $this->getUser()->getId();

        if ($userId) {
            Phpfox::getService(PushNotificationInterface::class)
                ->updateUserTokenDeviceGroup($userId, null, $params['device_token']);
        }

        if (Phpfox::getService('mobile.device')->removeDeviceToken($params['device_token'], $params['device_id'], $params['device_uid'])) {
            //Should reset error to support case Cancel Account from App
            Phpfox_Error::reset();

            return $this->success([]);
        }
        return $this->permissionError();
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', UserResource::RESOURCE_NAME);
        $module = 'user';
        return [
            [
                'path'      => 'profile-:id',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ],
            ],
        ];
    }

    public function getActions()
    {
        $l = $this->getLocalization();
        return [
            'user/block'                 => [
                'url'             => 'mobile/account/blocked-user',
                'method'          => 'post',
                'data'            => 'user_id=:user, user_id=:user',
                'new_state'       => 'is_blocked=1',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure_you_want_to_block_this_user'),
                'actionSuccess'   => [
                    [
                        'action' => 'goRoute', 'routeName' => 'viewBlockedUser'
                    ]
                ],
            ],
            'user/unblock'               => [
                'url'       => 'mobile/account/blocked-user',
                'method'    => 'delete',
                'data'      => 'id',
                'new_state' => 'is_blocked=0',
            ],
            'user/remove_cover_photo'    => [
                'url'             => 'mobile/user/remove-cover',
                'method'          => 'put',
                'data'            => 'id',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
                'new_state'       => 'can_remove_cover=false, cover=' . UserResource::populate([])->getDefaultImage(true),
            ],
            'user/edit_profile'          => [
                'routeName' => 'formEdit',
                'params'    => [
                    'formType'      => 'editProfile',
                    'module_name'   => 'user',
                    'resource_name' => 'user',
                ],

            ],
            'user/edit_account'          => [
                'routeName' => 'formEdit',
                'params'    => [
                    'formType'      => 'editAccount',
                    'module_name'   => 'user',
                    'resource_name' => 'user',
                    'formName'      => 'formAddItem'
                ],
            ],
            'user/unfriend'              => [
                'method'    => 'delete',
                'url'       => 'mobile/friend',
                'data'      => 'friend_user_id=:id, ignore_error=1',
                'new_state' => 'friendship=0',
            ],
            'user/cancel_friend_request' => [
                'method'        => 'delete',
                'url'           => 'mobile/friend/request',
                'data'          => 'friend_user_id=:id, ignore_error=1',
                'new_state'     => 'friendship=0',
                'actionSuccess' => [
                    ['action' => 'loadDetail', 'module_name' => 'user', 'resource_name' => 'user', 'data' => 'id=:id,only_extra=1,simple_data=1']
                ],
            ],
            'user/add_friend_request'    => [
                'method'    => 'post',
                'url'       => 'mobile/friend/request',
                'data'      => 'friend_user_id=:id, ignore_error=1',
                'new_state' => 'friendship=3'
            ],
            'user/accept_friend_request' => [
                'url'       => 'mobile/friend/request',
                'method'    => 'put',
                'data'      => 'action=approve, ignore_error=1, friend_user_id=:id',
                'new_state' => 'friendship=1',
            ],
            'user/deny_friend_request'   => [
                'url'       => 'mobile/friend/request',
                'method'    => 'put',
                'data'      => 'action=deny, ignore_error=1, friend_user_id=:id',
                'new_state' => 'friendship=0',
            ],
            'user/cancel-account'        => [
                'routeName' => 'formEditItem',
                'params'    => [
                    'module_name'   => 'user',
                    'resource_name' => 'user',
                    'formType'      => 'cancelAccount',
                    'formName'      => 'formEditItem'
                ]
            ],
            'user/poke'                  => [
                'url'             => 'mobile/user/poke',
                'method'          => 'post',
                'data'            => 'user_id=:id',
                'new_state'       => 'can_poke=false',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('are_you_sure'),
            ],
            'user/ban-user'              => [
                'url'           => UrlUtility::makeApiUrl('user/ban-user'),
                'method'        => 'post',
                'data'          => 'ban_id=:id',
                'actionSuccess' => [
                    ['action' => 'logout']
                ]
            ],
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        return new MobileApp('user', [
            'title'           => $l->translate('members'),
            'home_view'       => 'tab',
            'main_resource'   => new UserResource([]),
            'other_resources' => [
                new ActivityPointResource([]),
                new UserInfoResource([]),
                new BlockedUserResource([]),
                new EmailNotificationSettingsResource([]),
                new ItemPrivacySettingsResource([]),
                new ProfilePrivacySettingsResource([]),
                new UserPhotoResource([]),
                new UserStatisticResource([])
            ]
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed
     * @throws ErrorException
     * @throws PermissionErrorException
     * @throws UndefinedResourceName
     * @throws UnknownErrorException
     * @throws ValidationErrorException
     */
    public function uploadAvatar($params)
    {
        $params = $this->resolver
            ->setDefined(['id', 'image'])
            ->setRequired(['image'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $userId = isset($params['id']) ? $params['id'] : $this->getUser()->getId();

        //Check permission when update avatar of other
        if (!$userId || ($userId != $this->getUser()->getId() && !$this->getSetting()->getUserSetting('user.can_change_other_user_picture'))) {
            return $this->permissionError();
        }
        $sTempPath = PHPFOX_DIR_CACHE . md5('user_avatar' . Phpfox::getUserId()) . '.png';
        list($header, $data) = explode(';', $params['image']);

        $aImageData = explode(',', $data);
        if (isset($aImageData[1])) {
            $data = base64_decode($aImageData[1]);
            if (!empty($data)) {
                //Check file type
                $imageExt = str_replace('data:image/', '', $header);
                $accept = ['jpg', 'gif', 'png', 'jpeg'];
                if (!in_array($imageExt, $accept)) {
                    return $this->error(_p('not_a_valid_image_we_only_accept_the_following_file_extensions_support',
                        ['support' => implode(', ', $accept)]));
                }

                //Check file size
                $length = strlen($data);
                $size = round($length / 1024, 2);
                $maxSize = $this->getSetting()->getUserSetting('user.max_upload_size_profile_photo');
                if ($maxSize !== 0 && $size > $maxSize) {
                    return $this->error($this->getLocalization()->translate('upload_failed_your_file_size_is_larger_then_our_limit_file_size',
                        [
                            'size'      => $size . 'kb',
                            'file_size' => $maxSize . 'kb',
                        ]));
                }
                file_put_contents($sTempPath, $data);
                if (!Phpfox::getService('user.space')->isAllowedToUpload($userId, filesize($sTempPath))) {
                    return $this->error($this->getErrorMessage());
                }
                if ($userImage = $this->getProcessService()->uploadImage($userId, true, $sTempPath, true)) {
                    $user = $this->getUserService()->get($userId);
                    $user['user_image'] = $userImage['user_image'];
                    @unlink($sTempPath);

                    return $this->success(UserResource::populate($user)->toArray(), [], $this->getLocalization()->translate(!empty($userImage['pending_photo'])
                        ? 'the_profile_photo_is_pending_please_waiting_until_the_approval_process_is_done' : 'profile_photo_successfully_updated'));
                }
            }
        }

        return $this->error();
    }

    public function uploadCover($params)
    {
        $id = $this->resolver->resolveId($params);

        $userId = !empty($id) ? $id : $this->getUser()->getId();

        //Check permission when update cover of other
        if (!$this->getSetting()->getUserSetting('photo.can_upload_photos')
            || (!$userId
                || ($userId != $this->getUser()->getId()
                    && !$this->getSetting()->getUserSetting('user.can_change_other_user_picture')))
        ) {
            return $this->permissionError();
        }

        if (isset($_FILES['Filedata']) && !isset($_FILES['image'])) // photo.enable_mass_uploader == true
        {
            $_FILES['image'] = [];
            $_FILES['image']['error'] = UPLOAD_ERR_OK;
            $_FILES['image']['name'] = $_FILES['Filedata']['name'];
            $_FILES['image']['type'] = $_FILES['Filedata']['type'];
            $_FILES['image']['tmp_name'] = $_FILES['Filedata']['tmp_name'];
            $_FILES['image']['size'] = $_FILES['Filedata']['size'];
        }

        if (empty($_FILES['image'])) {
            return $this->validationParamsError(['image']);
        }
        $user = $this->getUserService()->get($userId);
        $userResource = UserResource::populate($user)->setViewMode(ResourceBase::VIEW_DETAIL)->toArray();
        $oFile = \Phpfox_File::instance();
        $oImage = \Phpfox_Image::instance();
        $maxSize = $this->getSetting()->getUserSetting('photo.photo_max_upload_size');
        $uploadDir = $this->getSetting()->getAppSetting('photo.dir_photo');
        if ($_FILES['image']['error'] == UPLOAD_ERR_OK) {
            if ($aImage = $oFile->load('image', ['jpg', 'gif', 'png'],
                ($maxSize === 0 ? null : ($maxSize / 1024)))
            ) {
                $aVals = [
                    'type_id'        => 0,
                    'is_cover_photo' => 1,
                ];
                if ($iId = Phpfox::getService('photo.process')->add($userId, array_merge($aVals, $aImage))) {
                    $sFileName = $oFile->upload('image', $uploadDir, $iId, true);
                    $sFile = $uploadDir . sprintf($sFileName, '');
                    $iFileSizes = filesize($sFile);
                    // Get the current image width/height
                    $aSize = getimagesize($sFile);
                    $iServerId = \Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');
                    // Update the image with the full path to where it is located.
                    $aUpdate = [
                        'destination'    => $sFileName,
                        'width'          => $aSize[0],
                        'height'         => $aSize[1],
                        'server_id'      => $iServerId,
                        'allow_rate'     => 1,
                        'description'    => null,
                        'allow_download' => 1,
                    ];
                    Phpfox::getService('photo.process')->update($userId, $iId, $aUpdate);
                    if (file_exists($sFile)
                        && !$this->getSetting()->getAppSetting('core.keep_files_in_server')
                    ) {
                        if ($iServerId > 0) {
                            $sActualFile = Phpfox::getLib('image.helper')->display([
                                    'server_id'  => $iServerId,
                                    'path'       => 'photo.url_photo',
                                    'file'       => $sFileName,
                                    'suffix'     => '',
                                    'return_url' => true,
                                ]
                            );

                            $aExts = preg_split("/[\/\\.]/", $sActualFile);
                            $iCnt = count($aExts) - 1;
                            $sExt = strtolower($aExts[$iCnt]);

                            $aParts = explode('/', $sFileName);
                            $sFile = $uploadDir . $aParts[0] . '/' . $aParts[1] . '/' . md5($sFileName) . '.' . $sExt;

                            // Create a temp copy of the original file in local server
                            if (filter_var($sActualFile, FILTER_VALIDATE_URL) !== false) {
                                file_put_contents($sFile, fox_get_contents($sActualFile));
                            } else {
                                copy($sActualFile, $sFile);
                            }
                            //Delete file in local server
                            register_shutdown_function(function () use ($sFile) {
                                @unlink($sFile);
                            });
                        }
                    }
                    list($width, $height, ,) = getimagesize($sFile);
                    foreach (Phpfox::getService('photo')->getPhotoPicSizes() as $iSize) {
                        // Create the thumbnail
                        if ($oImage->createThumbnail($sFile,
                                $uploadDir . sprintf($sFileName, '_' . $iSize), $iSize,
                                $height, true,
                                false) === false
                        ) {
                            continue;
                        }

                        if (defined('PHPFOX_IS_HOSTED_SCRIPT')) {
                            unlink($uploadDir . sprintf($sFileName, '_' . $iSize));
                        }
                    }
                    //Crop original image
                    $iWidth = (int)$this->getSetting()->getUserSetting('photo.maximum_image_width_keeps_in_server');
                    if ($iWidth < $width) {
                        $bIsCropped = $oImage->createThumbnail($uploadDir . sprintf($sFileName,
                                ''), $uploadDir . sprintf($sFileName, ''), $iWidth, $height,
                            true,
                            false);
                        if ($bIsCropped !== false) {
                            //Rename file
                            if (defined('PHPFOX_IS_HOSTED_SCRIPT')) {
                                unlink($sFile);
                            }
                        }
                    }

                    Phpfox::getService('user.space')->update($userId, 'photo', $iFileSizes);
                    $this->getProcessService()->updateCoverPhoto($iId, $userId);
                    $directlyPublic = $this->getSetting()->getUserSetting('photo.photo_must_be_approved');

                    if (!$directlyPublic) {
                        $userResource['cover'] = Image::createFrom([
                            'file'      => $sFileName,
                            'server_id' => $iServerId,
                            'path'      => 'photo.url_photo',
                        ], ['_1024'])->toArray();
                    }

                    return $this->success($userResource, [], $this->getLocalization()->translate(!$directlyPublic
                        ? 'cover_photo_successfully_updated' : 'the_cover_photo_is_pending_please_waiting_until_the_approval_process_is_done'));
                }
            }
        }
        return $this->error();
    }

    public function populateResource($class, $data)
    {
        return parent::populateResource($class, $data); // TODO: Change the autogenerated stub
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

    public function searchFriendFilter($id, $friends)
    {
        return $friends;
    }

    public function getPermissionWithUser($params)
    {
        $id = $this->resolver->resolveId($params);

        $user = $this->loadResourceById($id, false, true);

        $currentUserId = $this->getUser()->getId();

        if (empty($user)) {
            return $this->success([
                'id'            => (int)$id,
                'resource_name' => 'user',
                'extra'         => [
                    'can_chat' => false
                ]
            ]);
        }
        $permission = [];

        //Get can_chat permission

        if ($user['profile_page_id'] > 0) {
            $permission['can_chat'] = true;
            $resourceName = 'user';
        } else {
            $isFriend = ((Phpfox::isModule('friend')) && Phpfox::getService('friend')->isFriend($currentUserId, $id)) || setting('pf_im_allow_non_friends');
            $isBlocked = Phpfox::getService('user.block')->isBlocked($currentUserId, $id);
            $permission['can_chat'] = $isFriend && !$isBlocked;
            $resourceName = 'user';
        }
        //Other permission ....

        return $this->success([
            'id'            => (int)$id,
            'resource_name' => $resourceName,
            'extra'         => $permission
        ]);
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('user', []);
        $resourceName = UserResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME, [
            ScreenSetting::LOCATION_HEADER => [
                'component'       => 'module_header',
                'enableTitleMenu' => false,
                'headerTitle'     => 'members',
                'unNewScreen' => true
            ],
            ScreenSetting::LOCATION_BOTTOM => [
                'component' => ScreenSetting::SORT_FILTER_FAB
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_TABS,
                'tabs'      => [
                    [
                        'label'         => 'all',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'resource_name' => $resourceName,
                        'query'         => ['sort' => 'name'],
                        'hiddenSearchFilter' => false,
                        'destroyForm' => true
                    ],
                    [
                        'label'         => 'recently_active',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'resource_name' => $resourceName,
                        'query'         => ['sort' => 'active'],
                        'hiddenSearchFilter' => false,
                        'destroyForm' => true
                    ],
                    [
                        'label'         => 'featured',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'resource_name' => $resourceName,
                        'query'         => ['view' => 'featured'],
                        'hiddenSearchFilter' => true,
                        'destroyForm' => true
                    ],
                    [
                        'label'         => 'suggestions',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'resource_name' => $resourceName,
                        'query'         => ['view' => 'recommend'],
                        'hiddenSearchFilter' => true,
                        'destroyForm' => true
                    ],
                ]
            ],
            ScreenSetting::LOCATION_RIGHT  => [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'module_name'   => 'friend',
                'resource_name' => FriendResource::populate([])->getResourceName(),
                'title'         => 'friends',
                'limit'         => 5
            ],
            'screen_title'                 => $l->translate('users') . ' > ' . $l->translate('user') . ' - ' . $l->translate('mobile_home_page')
        ]);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);
        $embedComponents = [
            'stream_user_header_info',
            'stream_profile_menus',
            'stream_profile_photos',
            'stream_profile_friends',
        ];
        if (Phpfox::isModule('feed')) {
            $embedComponents[] = 'stream_composer';
        }
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => ScreenSetting::STREAM_PROFILE_FEEDS,
                'embedComponents' => $embedComponents
            ],
            'screen_title'                 => $l->translate('users') . ' > ' . $l->translate('profile') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addSetting($resourceName, 'viewBlockedUser', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'blocked_users'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                'module_name'   => 'user',
                'resource_name' => BlockedUserResource::populate([])->getResourceName(),
                'pagingName'    => 'blocked_users'
            ],
            'screen_title'                 => $l->translate('users') . ' > ' . $l->translate('blocked_users') . ' - ' . $l->translate('mobile_home_page')
        ]);

        $screenSetting->addSetting($resourceName, 'settings', [
            ScreenSetting::LOCATION_TOP  => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'system_settings'
            ],
            ScreenSetting::LOCATION_MAIN => ['component' => 'user_settings'],
            'no_ads'                     => true,
            'screen_title'               => $l->translate('users') . ' > ' . $l->translate('system_settings') . ' - ' . $l->translate('mobile_home_page')
        ]);

        $screenSetting->addSetting($resourceName, 'viewUserActivityStatistics', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title' => 'activity_statistics'
            ],
            ScreenSetting::LOCATION_MAIN => [
                'component' => 'item_activity_point_view',
            ],
            'no_ads' => true
        ]);

        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'user.browse',
            ScreenSetting::MODULE_LISTING => 'user.browse',
            ScreenSetting::MODULE_DETAIL  => 'profile.index'
        ];
    }

    public function removeCover($params)
    {
        $this->denyAccessUnlessGranted(UserAccessControl::IS_AUTHENTICATED);
        $id = $this->getUser()->getId();
        $this->getProcessService()->removeLogo($id);
        return $this->success([
            'cover' => UserResource::populate([])->getDefaultImage(true)
        ], [], $this->getLocalization()->translate('profile_cover_removed_successfully'));
    }

    public function getCancelAccountForm($params)
    {
        $form = $this->createForm(DeleteAccountForm::class, [
            'title'  => $this->getLocalization()->translate('cancel_account'),
            'action' => UrlUtility::makeApiUrl('user/cancel-account'),
            'method' => 'post',
        ]);

        return $this->success($form->getFormStructure());
    }

    public function cancelAccount($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        if (!$this->getSetting()->getUserSetting('user.can_delete_own_account')) {
            return $this->permissionError($this->getLocalization()->translate('you_are_not_allowed_to_delete_your_own_account'));
        }
        $form = $this->createForm(DeleteAccountForm::class);
        if ($form->isValid() && $values = $form->getValues()) {
            if ($this->processCancelAccount($values)) {
                return $this->success([]);
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->error();
    }

    private function processCancelAccount($aVal)
    {
        define('PHPFOX_CANCEL_ACCOUNT', true);
        // confirm $aVal[password] == user password
        // get user's data
        $aRow = $this->database()
            ->select('password_salt, password')
            ->from(Phpfox::getT('user'))
            ->where('user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveRow');

        if (!Phpfox::getUserBy('fb_user_id') && !Phpfox::getUserBy('janrain_user_id')) {
            $error = false;
            if (strlen($aRow['password']) > 32) {
                $Hash = new \Core\Hash();
                if (!$Hash->check($aVal['password'], $aRow['password'])) {
                    $error = true;
                }
            } else {
                if (Phpfox::getLib('hash')->setHash($aVal['password'], $aRow['password_salt']) != $aRow['password']) {
                    $error = true;
                }
            }

            if ($sPlugin = \Phpfox_Plugin::get('user.service_cancellations_process_cancelaccount_invalid_password')) {
                eval($sPlugin);
            }

            if ($error) {
                return $this->error($this->getLocalization()->translate('invalid_password'));
            }
        }
        Phpfox::getService('user.cancellations.process')->feedbackCancellation($aVal);

        // mass callback
        Phpfox::massCallback('onDeleteUser', Phpfox::getUserId());
        // log out after having deleted all the info
        Phpfox::getService('user.auth')->logout();
        return true;
    }

    public function sendRegistrationSms($params)
    {
        $params = $this->resolver
            ->setRequired(['phone', 'email'])
            ->setDefined(['user_id'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $phoneLib = Phpfox::getLib('phone');
        $phoneLib->setRawPhone($params['phone']);
        if ($phoneLib->isValidPhone()) {
            $phone = $phoneLib->getPhoneE164();
        } else {
            return $this->error($this->getLocalization()->translate('invalid_phone_number_or_contact_admin', ['phone' => $params['phone']]));
        }
        if (!Phpfox::getService('user.validate')->phone($phone, true, true, isset($params['user_id']) ? (int)$params['user_id'] : $this->getUser()->getId())) {
            Phpfox_Error::reset();
            return $this->error($this->getLocalization()->translate('mobile_phone_is_in_use_and_user_can_login', ['phone' => $params['phone']]));
        }

        $sendToken = Phpfox::getService('user.verify')->getVerifyHashByEmail($params['email']);
        $sendToken = substr($sendToken, 0, 3) . ' ' . substr($sendToken, 3);
        $message = _p('sms_registration_verification_message', ['token' => $sendToken]);
        if (Phpfox::getLib('phpfox.verify')->sendSMS($phone, $message)) {
            return $this->success([]);
        }

        return $this->error($this->getLocalization()->translate('invalid_phone_number_or_contact_admin', ['phone' => $params['phone']]));
    }

    public function verifyRegistration($params)
    {
        $code = $this->resolver->setRequired(['code'])->resolveSingle($params, 'code');

        if (empty($code)) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (Phpfox::getService('user.verify.process')->verify($code)) {
            if ($this->getSetting()->getAppSetting('user.approve_users')) {
                return $this->success([], [], $this->getLocalization()->translate('your_account_is_pending_approval'));
            }
            return $this->success([], [], $this->getLocalization()->translate('your_account_has_been_verified_please_log_in_with_the_information_you_provided_during_sign_up'));
        }

        return $this->error($this->getLocalization()->translate('invalid_verification_token'));
    }

    public function validateEmail($params)
    {
        $email = $this->resolver->resolveSingle($params, 'email');
        if (empty($email)) {
            return $this->error($this->getLocalization()->translate('invalid_email_address'));
        }
        $users = $this->database()->select('COUNT(*)')->from(':user')->where(['email' => $email])->execute('getField');
        if ($users) {
            return $this->success([
                'email_used' => true
            ], [], $this->getLocalization()->translate('Email is already in use.'));
        }
        return $this->success([
            'email_used' => false
        ]);
    }

    public function pokeUser($params)
    {
        $id = $this->resolver->resolveSingle($params, 'user_id', 'int');
        if (!$id) {
            return $this->notFoundError();
        }
        $user = $this->loadResourceById($id, true);
        $this->denyAccessUnlessGranted(UserAccessControl::POKE, $user);
        if (Phpfox::getService('poke.process')->sendPoke($id) && $this->isPassed()) {
            return $this->success([], [], $this->getLocalization()->translate('your_poke_successfully_sent'));
        }
        return $this->error($this->getLocalization()->translate('poke_could_not_be_sent'));
    }

    public function validateFbId($params)
    {
        $fbId = $this->resolver->resolveSingle($params, 'facebook_id');
        if (!$fbId) {
            return $this->notFoundError();
        }
        $cached = storage()->get('fb_users_' . $fbId);
        $user = [];
        if (!empty($cached) && isset($cached->value->user_id)) {
            $user = db()->select('user_id, user_name, email')->from(':user')
                ->where(['user_id' => $cached->value->user_id])
                ->execute('getRow');
        }
        if (isset($user['email'])) {
            return $this->success([
                'email' => $user['email']
            ]);
        }

        return $this->success([
            'email' => null
        ]);
    }

    public function banFromChat($params)
    {
        $banId = $this->resolver->resolveSingle($params, 'ban_id', 'int');
        $userId = $this->getUser()->getId();
        if (!$userId || !$banId) {
            return $this->permissionError();
        }
        $banItem = Phpfox::getLib('database')->select('*')->from(':ban')->where(['ban_id' => $banId])->executeRow();
        if (empty($banItem)) {
            return $this->notFoundError();
        }
        $userGroupsAffected = unserialize($banItem['user_groups_affected']);
        if (is_array($userGroupsAffected) && !empty($userGroupsAffected) && !in_array($this->getUser()->getGroupId(), $userGroupsAffected)) {
            return $this->permissionError();
        }
        Phpfox::getService('ban.process')->banUser($userId, $banItem['days_banned'], $banItem['return_user_group'], $banItem['reason'], $banId);
        if (empty($banItem['reason'])) {
            $reason = $this->getLocalization()->translate('you_are_banned_because_you_used_banned_word', ['word' => html_entity_decode($banItem['find_value'])]);
        } else {
            $banItem['reason'] = str_replace('&#039;', "'", $banItem['reason']);
            if (strpos($banItem['reason'], '{phrase') > -1) {
                $reason = preg_replace_callback('/\{phrase var=\'(.*)\'\}/is', function ($m) {
                    return $this->getLocalization()->translate($m[1], [], Phpfox::getUserBy('language_id'));
                }, $banItem['reason']);
            } else {
                $reason = preg_replace_callback('/\{_p var=\'(.*)\'\}/is', function ($m) {
                    return $this->getLocalization()->translate($m[1], [], Phpfox::getUserBy('language_id'));
                }, $banItem['reason']);
            }
            $reason = $this->getLocalization()->translate('you_have_been_banned_for_the_following_reason', ['reason' => $reason]) . '.';
        }
        return $this->success([], [], $reason);
    }

    public function getActivityStatistics($params)
    {
        $id = $this->resolver->resolveSingle($params, 'user_id');
        if (!$id) {
            $id = $this->resolver->resolveId($params);
        }
        $user = $this->getUserService()->get($id, true);
        $modules = Phpfox::massCallback('getDashboardActivity');
        $items = [];
        $secondItems = [];
        $secondSection = [
            'invite' => [
                'icon_name' => 'list-plus',
                'icon_family' => 'Lineficon',
                'icon_color' => '#555555'
            ],
            'comment' => [
                'icon_name' => 'comment-o',
                'icon_family' => 'Lineficon',
                'icon_color' => '#555555'
            ],
            'attachment' => [
                'icon_name' => 'paperclip-alt',
                'icon_family' => 'Lineficon',
                'icon_color' => '#555555'
            ]
        ];
        $allMenus = Phpfox::getService('mobile.admincp.menu')->getForBrowse();
        $allSimpleMenus = [];
        if (!empty($allMenus['item'])) {
            foreach ($allMenus['item'] as $menu) {
                $allSimpleMenus[$menu['module_id']] = [
                    'icon_name' => $menu['icon_name'],
                    'icon_family' => $menu['icon_family'],
                    'icon_color' => $menu['icon_color']
                ];
            }
        }
        $defaultIcon = [
            'icon_name' => 'box',
            'icon_family' => 'Lineficon',
            'icon_color' => '#555555'
        ];
        foreach ($modules as $key => $aModule) {
            foreach ($aModule as $sPhrase => $point) {
                $sPhrase = html_entity_decode($sPhrase, ENT_QUOTES);
                if (isset($secondSection[$key])) {
                    $secondItems[] = array_merge([
                        'label' => $sPhrase,
                        'value' => $point
                    ], $secondSection[$key]);
                } else {
                    $subPoint = [
                        'label' => $sPhrase,
                        'value' => $point
                    ];
                    $subPoint = array_merge($subPoint, isset($allSimpleMenus[$key]) ? $allSimpleMenus[$key] : $defaultIcon);
                    $items[] = $subPoint;
                }
            }
        }

        $activities = [
            'id' => (int)$id,
            'total_items' => [
                'label' => $this->getLocalization()->translate('total_items'),
                'value' => $user['activity_total'],
            ],
            'items' => $items,
            'addition_items' => $secondItems,
        ];


        return $this->success($activities);
    }
}