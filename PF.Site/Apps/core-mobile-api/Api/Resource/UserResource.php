<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\Core_MobileApi\Service\UserApi;
use Phpfox;

class UserResource extends ResourceBase implements UserInterface
{
    const RESOURCE_NAME = "user";
    const STATUS_ACTIVE = 0;
    const STATUS_UNVERIFY = 1;

    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'user';

    public $user_name;

    public $full_name;

    public $last_name;

    public $first_name;

    public $gender;

    public $custom_gender;

    public $avatar;

    public $avatar_id;

    public $email;

    public $country_iso;

    public $language_id;

    public $time_zone;

    public $time_zone_gmt;

    public $joined;

    public $city_location;

    public $default_currency;

    public $cover;

    public $cover_id;

    public $profile_menus;

    public $post_types;

    public $summary;

    public $is_owner;

    public $activity_total;

    public $activity_points;

    public $status_id;

    public $is_featured;

    public $friendship;

    public $statistic;

    public $age;

    protected $user_group_id;

    protected $activity;

    protected $profile_page = null;

    protected $isMe = false;

    public $is_blocked;

    public $profile_page_id;

    /**
     * @var \User_Service_User
     */
    private $userService;

    public $friend_id;

    public $image;

    public $phone_number;

    public function __construct($data)
    {
        parent::__construct($data);
        $this->getProfilePage();
    }

    public function getIsOwner()
    {
        return $this->getId() == Phpfox::getUserId();
    }

    public function getFullName()
    {
        $this->full_name = isset($this->rawData['full_name']) ? $this->parse->cleanOutput($this->rawData['full_name']) : '';
        return $this->full_name;
    }

    /**
     * Get detail url
     *
     * @return string
     */
    public function getLink()
    {
        if (empty($this->user_name)) {
            return null;
        }
        return Phpfox::getLib('url')->makeUrl($this->user_name);
    }

    public function getAvatar()
    {
        $image = Image::createFrom([
            'user' => $this->rawData,
        ], ["200_square"]);

        if ($image == null) {
            if (!empty($this->profile_page) && $this->profile_page['default_avatar']) {
                return $this->profile_page['default_avatar'];
            }
            return $this->getDefaultImage();
        }
        return !empty($image->sizes['200_square']) ? $image->sizes['200_square'] : null;
    }

    public function getAvatarId()
    {
        $avatar = storage()->get('user/avatar/' . $this->getId());
        if (!empty($avatar)) {
            $this->avatar_id = (int)$avatar->value;
        }
        return $this->avatar_id;
    }

    public function getImage()
    {
        return $this->getAvatar();
    }

    public function getCover($returnDefault = true)
    {
        if (empty($this->cover) && $returnDefault) {
            return $this->getDefaultImage(true);
        }
        return $this->cover;
    }

    public function getCoverId()
    {
        if (isset($this->rawData['cover_photo_exists'])) {
            $this->cover_id = (int)$this->rawData['cover_photo_exists'];
        } else if ($cover = storage()->get('user/cover/' . $this->getId())) {
            $this->cover_id = isset($cover->value) ? (int)$cover->value : 0;
        }
        return $this->cover_id;
    }

    /**
     * Limit user data when mapping resource properties
     *
     * @param array  $rawData
     * @param array  $fields
     * @param string $suffix
     *
     * @return array
     */
    public static function filterData(
        $rawData,
        $fields = ['user_id', 'user_name', 'full_name', 'user_image', 'user_server_id', 'email', 'gender', 'profile_page_id'],
        $suffix = ""
    )
    {
        $result = [];
        foreach ($fields as $field) {
            if (isset($rawData[$suffix . $field])) {
                $result[$field] = $rawData[$suffix . $field];
            }
        }

        return $result;
    }

    /**
     * Get user joined date
     *
     * @return mixed
     */
    public function getJoined()
    {
        return $this->convertDatetime($this->joined);
    }

    /**
     * Get fields for listing or child resource
     *
     * @return array
     */
    public function getShortFields()
    {
        return [
            'id',
            'module_name',
            'resource_name',
            'full_name',
            'avatar',
            'friend_id',
            'is_featured',
            'user_name'
        ];
    }


    public function getProfileMenus()
    {
        return (new UserApi())->getProfileMenus($this->getId());
    }

    public function getPostTypes()
    {
        return (new UserApi())->getPostTypes($this->getId());
    }

    /**
     * @return mixed
     */
    public function getActivity()
    {
        if (empty($this->activity)) {
            $this->activity = [];
            foreach ($this->rawData as $key => $value) {
                if (strpos($key, 'activity_') === 0) {
                    $this->activity[$key]
                        = ResourceMetadata::convertValue($value, ['type' => ResourceMetadata::INTEGER]);
                }
            }
        }
        return $this->activity;
    }

    public function getSummary()
    {
        if (empty($this->summary)) {
            $summary = "";
            $endDot = true;
            if (!empty($this->gender)) {
                $endDot = false;
                if (empty($this->custom_gender)) {
                    $gender = $this->getUserService()->gender($this->gender);
                } else {
                    $aCustomGenders = Phpfox::getLib('parse.format')->isSerialized($this->custom_gender) ? unserialize($this->custom_gender) : $this->custom_gender;
                    $gender = '';
                    if (is_array($aCustomGenders)) {
                        if (count($aCustomGenders) > 2) {
                            $sLastGender = $aCustomGenders[count($aCustomGenders) - 1];
                            unset($aCustomGenders[count($aCustomGenders) - 1]);
                            $gender = implode(', ', $aCustomGenders) . ' ' . $this->getLocalization()->translate('and') . ' ' . $sLastGender;
                        } else {
                            $gender = implode(' ' . $this->getLocalization()->translate('and') . ' ', $aCustomGenders);
                        }
                    }
                }
                $summary .= $gender;
                $summary .= ".";
            }
            if (Phpfox::getService('user.privacy')->hasAccess($this->getId(), 'profile.view_location')) {
                $hasChild = !empty($this->rawData['country_child_id']);
                $hasCountry = !empty($this->rawData['country_iso']);
                $hasCityLocation = !empty($this->city_location);
                if ($hasCityLocation || $hasCountry || $hasChild) {
                    $summary .= (!empty($summary) ? " " : "")
                        . $this->getTranslator()->translate('lives_in') . ($hasCityLocation ? " " . $this->city_location : "");
                    if ($hasCityLocation && ($hasChild || $hasCountry)) {
                        $summary .= ',';
                    }
                    if ($hasChild) {
                        $summary .= " " . Phpfox::getService('core.country')->getChild($this->rawData['country_child_id']) . ',';
                    }
                    if ($hasCountry) {
                        $summary .= " " . Phpfox::getService('core.country')->getCountry($this->rawData['country_iso']);
                    }
                    $endDot = false;
                    $summary .= '.';
                }
            }

            //Get birthday
            if (!empty($this->rawData['birthday'])) {
                $birthdayString = $this->getBirthDay();
                if (!empty($birthdayString) && isset($this->rawData['dob_setting'])) {
                    if ($this->rawData['dob_setting'] == 2) {
                        if ($birthdayString == 1) {
                            $summary .= " " . $this->getTranslator()->translate('1_year_old');
                        } else {
                            $summary .= " " . $this->getTranslator()->translate('age_years_old', ['age' => $birthdayString]);
                        }
                    } else {
                        $summary .= " " . $this->getTranslator()->translate('born_on_birthday', ['birthday' => $birthdayString]);
                    }
                    $endDot = false;
                    $summary .= '.';
                }
            }

            //Support on feed
            if (!array_key_exists('relation_data_id', $this->rawData) && !array_key_exists('with_user_id', $this->rawData)) {
                $relation = Phpfox::getService('custom.relation')->getLatestForUser($this->getId(), null, true);
                $this->rawData['relation_data_id'] = isset($relation['relation_data_id']) ? $relation['relation_data_id'] : 0;
            }
            $relationship = (!empty($this->rawData['relation_data_id']) || !empty($this->rawData['with_user_id'])
                ? Phpfox::getService('custom')->getRelationshipPhrase($this->rawData) : null);
            if (!empty(trim($relationship)) && trim($relationship) != '_new') {
                $summary = rtrim($summary, '. ');
                $summary .= (!empty($summary) ? ", " : " ") . $relationship;
                $endDot = false;
                $summary .= '.';
            }
            if (isset($this->rawData['category_name'])) {
                $summary = rtrim($summary, '. ');
                $summary .= (!empty($summary) ? " - " : " ") . $this->rawData['category_name'];
                $endDot = false;
                $summary .= '.';
            }
            if ($endDot && !empty($summary)) {
                $summary .= '.';
            }
            $this->summary = ltrim(rtrim($summary));
        }

        return html_entity_decode($this->summary, ENT_QUOTES);
    }

    /**
     * @return object|\User_Service_User
     */
    private function getUserService()
    {
        if (!$this->userService) {
            $this->userService = Phpfox::getService("user");
        }

        return $this->userService;
    }

    /**
     * @return LocalizationInterface
     */
    private function getTranslator()
    {
        return Phpfox::getService(LocalizationInterface::class);
    }

    /**
     * Get User's email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get user Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->profile_page !== null ? (int)$this->profile_page['id'] : (int)$this->id;
    }

    /**
     * @return int
     */
    function getRawId()
    {
        return $this->id;
    }

    /**
     * Get User Group ID
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->user_group_id;
    }

    /**
     * Get UserName string
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool True if the same user Id
     */
    function compareWith($user)
    {
        if ($user && $user instanceof UserInterface) {
            return ($this->getId() === $user->getId());
        }
        return false;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('gender', ['type' => ResourceMetadata::INTEGER])
            ->mapField('email', ['type' => ResourceMetadata::STRING])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('user_name', ['type' => ResourceMetadata::STRING])
            ->mapField('full_name', ['type' => ResourceMetadata::STRING])
            ->mapField('activity_total', ['type' => ResourceMetadata::INTEGER])
            ->mapField('activity_points', ['type' => ResourceMetadata::INTEGER])
            ->mapField('friend_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('profile_page_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('status_id', ['type' => ResourceMetadata::INTEGER]);
    }

    const FRIENDSHIP_CAN_ADD_FRIEND = 0;
    const FRIENDSHIP_IS_FRIEND = 1;
    const FRIENDSHIP_CONFIRM_AWAIT = 2;
    const FRIENDSHIP_REQUEST_SENT = 3;
    const FRIENDSHIP_CAN_NOT_ADD_FRIEND = 4;
    const FRIENDSHIP_IS_OWNER = 5;
    const FRIENDSHIP_IS_UNKNOWN = 6;
    const FRIENDSHIP_IS_DENY_REQUEST = 7;

    public function getFriendship()
    {
        return ($this->friendship !== null) ? $this->friendship : ($this->friendship = $this->_getFriendship());
    }

    public function _getFriendship()
    {
        if ($this->getIsOwner()) {
            return self::FRIENDSHIP_IS_OWNER;
        }

        if (!$this->accessControl) {
            $this->setAccessControl((new UserApi())->getAccessControl());
        }
        $status = self::FRIENDSHIP_CAN_NOT_ADD_FRIEND;
        $isModule = Phpfox::isModule('friend');
        $userId = Phpfox::getUserId();
        $iFriendRequestAwait = $isModule ? Phpfox::getService('friend.request')->isRequested($this->getId(), $userId, false, true) : false;
        $iFriendRequestSent = $iFriendRequestAwait ? false : ($isModule ? Phpfox::getService('friend.request')->isRequested($userId, $this->getId(), false, true) : false);

        if (!$userId) {
            $status = self::FRIENDSHIP_IS_UNKNOWN;
        } else if (!$isModule) {
            $status = self::FRIENDSHIP_IS_UNKNOWN;
        } else if (Phpfox::getService('friend')->isFriend($userId, $this->getId()) || !empty($this->rawData['is_friend'])) {
            $status = self::FRIENDSHIP_IS_FRIEND;
        } else if (Phpfox::getService('friend.request')->isDenied($userId, $this->getId())) {
            $status = self::FRIENDSHIP_IS_DENY_REQUEST;
        } else if (Phpfox::getService('friend.request')->isDenied($this->getId(), $userId) && $this->accessControl->isGranted(UserAccessControl::ADD_FRIEND, $this) && $userId != $this->getId()) {
            $status = self::FRIENDSHIP_CAN_ADD_FRIEND;
        } else if ($iFriendRequestAwait) {
            $status = self::FRIENDSHIP_CONFIRM_AWAIT;
        } else if ($iFriendRequestSent) {
            $status = self::FRIENDSHIP_REQUEST_SENT;
        } else if ($this->accessControl->isGranted(UserAccessControl::ADD_FRIEND, $this) && $userId != $this->getId()) {
            $status = self::FRIENDSHIP_CAN_ADD_FRIEND;
        }
        return $status;
    }

    public function toArray($displayFields = null)
    {
        $data = parent::toArray($displayFields);

        if (!$this->isMe && $this->accessControl && $this->accessControl->isGranted(UserAccessControl::SYSTEM_ADMIN) == false) {
            unset($data['email']);
            unset($data['gender']);
            unset($data['status_id']);
        }
        return $data;
    }

    public function loadResource()
    {
        $resourceData = NameResource::instance()
            ->getApiServiceByResourceName(self::RESOURCE_NAME)->loadResourceById($this->id, false, true);

        $this->rawData = $resourceData;
        $this->autoMapToProperties(self::STATE_POPULATED);

        return $this;
    }


    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        $detailMenu = [
            ['value' => 'user/edit_profile', 'label' => $l->translate('edit_profile'), 'show' => 'is_owner'],
            ['label' => $l->translate('update_cover'), 'value' => Screen::ACTION_EDIT_COVER, 'show' => 'is_owner', 'acl' => 'can_change_cover'],
            ['label' => $l->translate('update_avatar'), 'value' => Screen::ACTION_EDIT_AVATAR, 'show' => 'is_owner'],
            ['label' => $l->translate('poke'), 'value' => 'user/poke', 'show' => '!is_owner', 'acl' => 'can_poke'],
            ['label' => $l->translate('report_this_user'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report'],
            ['label' => $l->translate('remove_cover_photo'), 'style' => 'danger', 'value' => 'user/remove_cover_photo', 'show' => 'is_owner', 'acl' => 'can_remove_cover'],
            ['label' => $l->translate('block_this_user'), 'style' => 'danger', 'value' => 'user/block', 'show' => '!is_owner&&!is_blocked', 'acl' => 'can_block']
        ];
        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            $detailMenu = array_merge([
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_owner&&!is_featured', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_owner&&is_featured', 'acl' => 'can_feature'],
            ], $detailMenu);
        }
        return self::createSettingForResource([
            'resource_name'   => $this->resource_name,
            'can_add'         => false,
            'search_input'    => [
                'placeholder'   => $l->translate('search_members_three_dot'),
                'resource_name' => 'user'
            ],
            'sort_menu'       => false,
            'filter_menu'     => false,
            'list_view'       => [
                'item_view'       => 'user',
                'noItemMessage'   => [
                    'image' => $this->getAppImage('no-member'),
                    'label' => $l->translate('no_members_found')
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ]
            ],
            'fab_buttons'     => [
                [
                    'label'            => $this->getLocalization()->translate('filter'),
                    'icon'             => 'filter',
                    'action'           => '@app/FILTER_MEMBER',
                    'resource_name'    => $this->getResourceName(),
                    'module_name'      => $this->getModuleName(),
                    'destroyOnUnmount' => false,
                    'unNewScreen'      => true
                ],
            ],
            'detail_view'     => [
                'component_name' => 'user_detail',
            ],
            'forms'           => [
                'changePassword' => [
                    'apiUrl' => 'mobile/user/password',
                ],
                'editAvatar'     => [
                    'submitApiUrl' => UrlUtility::makeApiUrl('user/avatar/:id'),
                ],
                'editCover'      => [
                    'submitApiUrl'  => UrlUtility::makeApiUrl('user/cover/:id'),
                    'succeedAction' => '@shouldUpdateViewer',
                ],
                'editAccount'    => [
                    'apiUrl'        => UrlUtility::makeApiUrl('account/setting/form'),
                    'succeedAction' => '@shouldUpdateViewer',
                ],
                'forgotPassword' => [
                    'apiUrl'            => UrlUtility::makeApiUrl('user/password/request'),
                    'headerTitle'       => $l->translate('request_password'),
                    'formName'          => 'form/forgot-password',
                    'submitButtonLabel' => 'Request New Password',
                    'submitPosition'    => 'append',
                ],
                'editProfile'    => [
                    'apiUrl'        => UrlUtility::makeApiUrl('user/profile/form'),
                    'headerTitle'   => $l->translate('edit_profile'),
                    'succeedAction' => '@shouldUpdateViewer',
                ],
                'createAccount'  => [
                    'apiUrl'        => UrlUtility::makeApiUrl('user/form'),
                    'headerTitle'   => $l->translate('create_account'),
                    'succeedAction' => '@shouldUpdateViewer',
                ],
                'cancelAccount'  => [
                    'apiUrl'         => UrlUtility::makeApiUrl('user/cancel-account'),
                    'headerTitle'    => $l->translate('cancel_account'),
                    'succeedAction'  => '@auth/logout',
                    'confirmTitle'   => $l->translate('confirm'),
                    'confirmMessage' => $l->translate('are_you_absolutely_sure_this_operation_cannot_be_undone'),
                ],
                'updateLanguage' => [
                    'apiUrl'         => UrlUtility::makeApiUrl('account/language'),
                    'headerTitle'    => $l->translate('language'),
                    'succeedAction'  => '@shouldRestartApp',
                    'confirmTitle'   => $l->translate('change_language_to_language_name'),
                    'confirmMessage' => $l->translate('app_name_will_restart_to_complete_this_change'),
                ]
            ],
            'tagged_friend'   => [
                'apiUrl'          => UrlUtility::makeApiUrl('feed/tagged-friend'),
                'item_view'       => 'tagged_friend',
                'noItemMessage'   => [
                    'image' => $this->getAppImage(),
                    'label' => $l->translate('no_friends_found'),
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ]
            ],
            'membership_menu' => [
                ['value' => 'user/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1', 'acl' => 'can_view_remove_friend_link'],
                ['value' => 'user/add_friend_request', 'label' => $l->translate('add_friend'), 'show' => 'friendship==0'],
                ['value' => 'user/accept_friend_request', 'label' => $l->translate('accept_friend_request'), 'show' => 'friendship==2'],
                ['value' => 'user/cancel_friend_request', 'label' => $l->translate('cancel_request'), 'show' => 'friendship==3', 'style' => 'danger'],
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
            ],
            'action_menu'     => [
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
                ['value' => 'user/add_friend_request', 'label' => $l->translate('add_friend'), 'show' => 'friendship==0'],
                ['value' => 'user/accept_friend_request', 'label' => $l->translate('accept_friend_request'), 'show' => 'friendship==2'],
                ['value' => 'user/cancel_friend_request', 'label' => $l->translate('cancel_request'), 'show' => 'friendship==3', 'style' => 'danger'],
                ['value' => 'user/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1', 'acl' => 'can_view_remove_friend_link'],
            ],
            'detail_menu'     => $detailMenu,
            'app_menu'        => [
                ['label' => $l->translate('all_members'), 'params' => ['initialQuery' => ['sort' => 'name']]],
                ['label' => $l->translate('recently_active'), 'params' => ['initialQuery' => ['view' => 'online']]],
                ['label' => $l->translate('featured_members'), 'params' => ['initialQuery' => ['view' => 'featured']]],
                ['label' => $l->translate('friend_suggestions'), 'params' => ['initialQuery' => ['view' => 'recommend']]],
            ],
        ]);
    }

    /**
     * @param mixed $isMe
     */
    public function setIsMe($isMe)
    {
        $this->isMe = $isMe;
    }

    public function getIsBlocked()
    {
        if ($this->is_blocked === null) {
            $this->is_blocked = (bool)Phpfox::getService('user.block')->isBlocked(Phpfox::getUserId(), $this->getId());
        }
        return $this->is_blocked;
    }

    public function getStatistic()
    {
        if (Phpfox::isModule('friend')) {
            list ($iCnt,) = Phpfox::getService('friend')->getMutualFriends($this->id, 1);
            return [
                'total_friend' => isset($this->rawData['total_friend']) ? (int)$this->rawData['total_friend'] : 0,
                'total_mutual' => $iCnt,
            ];
        }
        return [
            'total_friend' => 0,
            'total_mutual' => 0,
        ];
    }

    public function getCityLocation()
    {
        return html_entity_decode($this->city_location, ENT_QUOTES);
    }

    public function getBirthDay()
    {
        $birthDay = '';
        if (isset($this->rawData['dob_setting'])) {
            $dobSetting = $this->rawData['dob_setting'];
            if ($dobSetting == 0 && Phpfox::getParam('user.default_privacy_brithdate') == 'hide') {
                $dobSetting = 3;
            }
            if (!empty($this->rawData['birthday']) && $dobSetting != '3') {
                $aBirthDay = Phpfox::getService('user')->getAgeArray($this->rawData['birthday']);

                if ($dobSetting == 0) {
                    switch (Phpfox::getParam('user.default_privacy_brithdate')) {
                        case 'show_age':
                            $dobSetting = 2;
                            break;
                        case 'full_birthday':
                            $dobSetting = 4;
                            break;
                        case 'month_day':
                            $dobSetting = 1;
                            break;
                    }
                }

                switch ($dobSetting) {
                    case '1':
                        $birthDay = Phpfox::getTime(Phpfox::getParam('user.user_dob_month_day'), mktime(0, 0, 0, $aBirthDay['month'], $aBirthDay['day'], $aBirthDay['year']), false);
                        break;
                    case '2':
                        $birthDay = $this->getAge();
                        break;
                    default:
                        $birthDay = Phpfox::getTime(Phpfox::getParam('user.user_dob_month_day_year'), mktime(0, 0, 0, $aBirthDay['month'], $aBirthDay['day'], $aBirthDay['year']), false);
                        break;
                }
            }
        }
        return $birthDay;
    }

    public function getAge()
    {
        return isset($this->rawData['birthday']) ? Phpfox::getService('user')->age($this->rawData['birthday']) : '';
    }

    public function getCustomGender()
    {
        if (!empty($this->custom_gender)) {
            $this->custom_gender = Phpfox::getLib('parse.format')->isSerialized($this->custom_gender) ? unserialize($this->custom_gender) : $this->custom_gender;
        }
        return $this->custom_gender;
    }

    public function getIsFeatured()
    {
        if ($this->is_featured === null) {
            $this->is_featured = Phpfox::getService('user')->isFeatured($this->getId());
        }
        return (bool)$this->is_featured;
    }

    public function getProfilePage()
    {
        if ($this->profile_page === null && !empty($this->rawData['profile_page_id']) && $this->rawData['profile_page_id'] > 0) {
            $type = Phpfox::getLib('pages.facade')->getPageItemType($this->rawData['profile_page_id']);
            $this->profile_page['module_name'] = $type == 'groups' ? GroupResource::RESOURCE_NAME : PageResource::RESOURCE_NAME;
            $this->profile_page['resource_name'] = $type == 'groups' ? GroupResource::populate([])->getResourceName() : PageResource::populate([])->getResourceName();
            $this->profile_page['default_avatar'] = $type == 'groups' ? GroupResource::populate([])->getDefaultImage() : PageResource::populate([])->getDefaultImage();
            $this->profile_page['id'] = (int)$this->rawData['profile_page_id'];
        }
        return $this->profile_page;
    }

    public function getResourceName()
    {
        return $this->profile_page != null ? $this->profile_page['resource_name'] : parent::getResourceName();
    }

    public function getModuleName()
    {
        return $this->profile_page != null ? $this->profile_page['module_name'] : parent::getModuleName();
    }

    public function getFriendId()
    {
        if ($this->friend_id == null) {
            $this->friend_id = 0;
            $isFriend = db()->select('friend_id')->from(':friend')->where([
                'user_id'        => Phpfox::getUserId(),
                'friend_user_id' => $this->getId()
            ])->executeField();

            if ($isFriend) {
                $this->friend_id = 1;

                $isFriend = db()->select('friend_id')->from(':friend')->where([
                    'user_id'        => $this->getId(),
                    'friend_user_id' => Phpfox::getUserId()
                ])->executeField();

                if ($isFriend) {
                    $this->friend_id = 2;
                }
            }
        }
        return (int)$this->friend_id;
    }

    public function getTimeZoneGmt()
    {
        if ($this->time_zone_gmt === null) {
            $timezones = Phpfox::getService('core')->getTimeZones();
            $this->time_zone_gmt = isset($timezones[$this->getTimeZone()]) ? $timezones[$this->getTimeZone()] : '';
            $this->time_zone_gmt = preg_replace('/\s\(.*\)/', '', $this->time_zone_gmt);
        }
        return $this->time_zone_gmt;
    }

    public function getTimeZone()
    {
        if ($this->time_zone === null) {
            if (!empty($this->rawData['time_zone'])) {
                $this->time_zone = $this->rawData['time_zone'];
            } else {
                $this->time_zone = Phpfox::getParam('core.default_time_zone_offset');
            }
        }
        return $this->time_zone;
    }
}