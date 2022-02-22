<?php

namespace Apps\PHPfox_Facebook\Model;

use Core\Hash as Hash;
use Core\Model as Model;
use Facebook\GraphNodes\GraphNode;
use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;

/**
 * Service class for Facebook Connect App
 *
 * @package Apps\PHPfox_Facebook\Model
 */
class Service extends Model
{

    /**
     * Create a new user or log them in if they exist
     *
     * @param GraphNode $fb
     * @return bool
     * @throws \Exception
     */
    public function create(GraphNode  $fb)
    {
        $email = $fb->getField('email');
        $url = null;
        $blank_email = false;
        $bSkipPass = false;
        if (!$email && $fb->getField('link')) {
            stream_context_set_default(
                array(
                    'http' => array(
                        'header' => "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n"
                    )
                )
            );
            $headers = array();
            $filename = rtrim(str_replace('app_scoped_user_id/', '', $fb->getField('link')), '/');

            if ($filename) {
                $headers = get_headers($filename);
            }


            if (isset($headers[1])) {
                $url = trim(str_replace('Location: https://www.facebook.com/', '', $headers[1]));
                $email = strtolower($url) . '@facebook.com';
                $blank_email = true;
            }
        }

        $fbId = $fb->getField('id');
        if (!$email) {
            $email = $fbId . '@fb';
            $blank_email = true;
        }

        $cached = storage()->get('fb_users_' . $fbId);
        if ($cached) {
            $user = $this->db->select('*')->from(':user')->where(['user_id' => $cached->value->user_id])->get();
            if (isset($user['email'])) {
                $email = $user['email'];
            } else {
                storage()->del('fb_users_' . $fbId);
            }
        } else {
            $user = $this->db->select('*')->from(':user')->where(['email' => $email])->get();
        }

        if (isset($user['user_id'])) {
            //don't reset current user password if account existed
            $_password = $user['password'];
            $bSkipPass = true;
        } else {
            if (!Phpfox::getParam('user.allow_user_registration')) {
                return false;
            }
            if (Phpfox::getParam('user.invite_only_community') && !Phpfox::getService('invite')->isValidInvite($user['email'])) {
                return false;
            }
            $_password = $fbId . uniqid();
            $password = (new Hash())->make($_password);
            if ($fb->getField('gender') == 'male') {
                $iGender = 1;
            } elseif ($fb->getField('gender') == 'female') {
                $iGender = 2;
            } else {
                $iGender = 0;
            }

            $aInsert = [
                'user_group_id' => Phpfox::getParam('user.on_register_user_group', NORMAL_USER_ID),
                'email' => $email,
                'password' => $password,
                'gender' => $iGender,
                'full_name' => ($fb->getField('first_name') == null ? $fb->getField('name') : $fb->getField('first_name') . ' ' . $fb->getField('middle_name') . ' ' . $fb->getField('last_name')),
                'user_name' => ($url === null ? 'fb-' . $fbId : str_replace('.', '-', $url)),
                'user_image' => '',
                'joined' => PHPFOX_TIME,
                'last_activity' => PHPFOX_TIME
            ];

            if (Phpfox::getParam('user.approve_users')) {
                $aInsert['view_id'] = '1';// 1 = need to approve the user
            }

            $id = $this->db->insert(':user', $aInsert);

            // Get user's avatar
            $sImage = fox_get_contents("https://graph.facebook.com/" . $fbId . "/picture?width=400&height=400");
            $sFileName = md5('user_avatar' . time()) . '.jpg';
            $sImagePath = Phpfox::getParam('core.dir_user') . $sFileName;
            file_put_contents($sImagePath, $sImage);
            Phpfox::getService('user.process')->uploadImage($id, true, $sImagePath);

            if ($blank_email) {
                storage()->set('fb_force_email_' . $id, $fbId);
            } else {
                //Set cache to show popup notify
                storage()->set('fb_user_notice_' . $id, ['email' => $email]);
            }

            storage()->set('fb_users_' . $fbId, [
                'user_id' => $id,
                'email' => $email
            ]);

            //Storage account login by Facebook, in the first time this user change password, he/she doesn't need confirm old password.
            storage()->set('fb_new_users_' . $id, [
                'fb_id' => $fbId,
                'email' => $email
            ]);

            $aExtras = array(
                'user_id' => $id
            );

            (($sPlugin = Phpfox_Plugin::get('user.service_process_add_extra')) ? eval($sPlugin) : false);
            
            $tables = [
                'user_activity',
                'user_field',
                'user_space',
                'user_count'
            ];
            foreach ($tables as $table) {
                $this->db->insert(':' . $table, $aExtras);
            }

            $iFriendId = (int)Phpfox::getParam('user.on_signup_new_friend');
            if ($iFriendId > 0 && Phpfox::isModule('friend')) {
                $iCheckFriend = db()->select('COUNT(*)')
                    ->from(Phpfox::getT('friend'))
                    ->where('user_id = ' . (int)$id . ' AND friend_user_id = ' . (int)$iFriendId)
                    ->execute('getSlaveField');

                if (!$iCheckFriend) {
                    db()->insert(Phpfox::getT('friend'), array(
                            'list_id' => 0,
                            'user_id' => $id,
                            'friend_user_id' => $iFriendId,
                            'time_stamp' => PHPFOX_TIME
                        )
                    );

                    db()->insert(Phpfox::getT('friend'), array(
                            'list_id' => 0,
                            'user_id' => $iFriendId,
                            'friend_user_id' => $id,
                            'time_stamp' => PHPFOX_TIME
                        )
                    );

                    if (!Phpfox::getParam('user.approve_users')) {
                        Phpfox::getService('friend.process')->updateFriendCount($id, $iFriendId);
                        Phpfox::getService('friend.process')->updateFriendCount($iFriendId, $id);
                    }
                }
            }

            $iId = $id; // add for plugin use

            $this->initDefaultProfileSetting($iId);
            $this->initDefaultNotificationSettings($iId);

            (($sPlugin = Phpfox_Plugin::get('user.service_process_add_end')) ? eval($sPlugin) : false);

            $this->db->insert(':user_ip', [
                    'user_id' => $iId,
                    'type_id' => 'register',
                    'ip_address' => Phpfox::getIp(),
                    'time_stamp' => PHPFOX_TIME
                ]
            );

            //Auto pick a package if required on sign up
            if (!defined('PHPFOX_INSTALLER') && Phpfox::isAppActive('Core_Subscriptions') && Phpfox::getParam('subscribe.enable_subscription_packages') && Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up'))  {
                $aPackages = Phpfox::getService('subscribe')->getPackages(true);
                if (count($aPackages)) {
                    //Get first package
                    $aPackage = $aPackages[0];
                    $iPurchaseId = Phpfox::getService('subscribe.purchase.process')->add([
                        'package_id' => $aPackage['package_id'],
                        'currency_id' => $aPackage['default_currency_id'],
                        'price' => $aPackage['default_cost']
                    ], $iId);
                    $iDefaultCost = (int)str_replace('.', '', $aPackage['default_cost']);
                    if ($iPurchaseId) {
                        if ($iDefaultCost > 0) {
                            define('PHPFOX_MUST_PAY_FIRST', $iPurchaseId);
                            Phpfox::getService('user.field.process')->update($iId, 'subscribe_id', $iPurchaseId);
                        } else {
                            Phpfox::getService('subscribe.purchase.process')->update($iPurchaseId, $aPackage['package_id'], 'completed', $iId, $aPackage['user_group_id']);
                        }
                    }
                }
            }

            if(Phpfox::isAppActive('Core_Activity_Points')) {
                Phpfox::getService('activitypoint.process')->updatePoints($id, 'user_signup');
            }
        }
        Phpfox::getService('user.auth')->login($email, $_password, true, 'email', $bSkipPass);
        if (!Phpfox_Error::isPassed()) {
            throw new \Exception(implode('', Phpfox_Error::get()));
        }

        return true;
    }

    private function initDefaultProfileSetting($iUserId)
    {
        if (method_exists('User_Service_Process', 'initDefaultProfileSetting')) {
            return Phpfox::getService('user.process')->initDefaultProfileSetting($iUserId);
        }

        if (empty($iUserId)) {
            return false;
        }

        $bIsFriendOnly = Phpfox::getParam('core.friends_only_community');
        switch (Phpfox::getParam('user.on_register_privacy_setting')) {
            case 'network':
                $iPrivacySetting = $bIsFriendOnly ? '2' : '1';
                break;
            case 'friends_only':
                $iPrivacySetting = '2';
                break;
            case 'no_one':
                $iPrivacySetting = '4';
                break;
            default:
                break;
        }

        if (isset($iPrivacySetting)) {
            $aProfiles = Phpfox::massCallback('getProfileSettings');
            $aDefaultConvertedSettingValues = [];
            $aAllowPrivacyList = [];
            $aPrivacy = [];
            foreach ($aProfiles as $aSettings) {
                $aPrivacy = array_merge($aPrivacy, array_keys($aSettings));
                foreach ($aSettings as $settingKey => $aSetting) {
                    $aAllowPrivacyList[$settingKey] = [];
                    if (!isset($aSetting['anyone']) && !$bIsFriendOnly) {
                        $aAllowPrivacyList[$settingKey][] = '0';
                    }
                    if (!isset($aSetting['no_user'])) {
                        if (!isset($aSetting['friend_only']) && !$bIsFriendOnly) {
                            $aAllowPrivacyList[$settingKey][] = '1';
                        }
                        if (Phpfox::isModule('friend')) {
                            if (!isset($aSetting['friend']) || $aSetting['friend']) {
                                $aAllowPrivacyList[$settingKey][] = '2';
                            }
                            if (!empty($aSetting['friend_of_friend'])) {
                                $aAllowPrivacyList[$settingKey][] = '3';
                            }
                        }
                    }
                    //No one is always available
                    $aAllowPrivacyList[$settingKey][] = '4';
                    if (isset($aSetting['converted_default_value'])) {
                        $aDefaultConvertedSettingValues[$settingKey] = $aSetting['converted_default_value'];
                    }
                }
            }

            foreach ($aPrivacy as $sPrivacy) {
                $a = explode('.', $sPrivacy);
                if (!isset($a[0]) || !Phpfox::isModule($a[0])) {
                    continue;
                }
                $iDefaultValue = isset($aDefaultConvertedSettingValues[$sPrivacy][$iPrivacySetting]) ? $aDefaultConvertedSettingValues[$sPrivacy][$iPrivacySetting] : $iPrivacySetting;
                if (!in_array($iDefaultValue, $aAllowPrivacyList[$sPrivacy]) && count($aAllowPrivacyList[$sPrivacy])) {
                    $iDefaultValue = $aAllowPrivacyList[$sPrivacy][0];
                }
                db()->insert(':user_privacy', [
                        'user_id'      => $iUserId,
                        'user_privacy' => $sPrivacy,
                        'user_value'   => $iDefaultValue,
                    ]
                );
            }
        }
        return true;
    }

    public function initDefaultNotificationSettings($iId)
    {
        if (!method_exists('Admincp_Service_Setting_Setting', 'getDefaultNotificationSettings')) {
            return false;
        }
        //Add default notification settings
        $aDefaultEmailNotification = Phpfox::getService('admincp.setting')->getDefaultNotificationSettings('email', true, true);
        if (count($aDefaultEmailNotification)) {
            $aDefaultEmailInsert = [];
            foreach ($aDefaultEmailNotification as $sVar => $iValue) {
                $aDefaultEmailInsert[] = [$iId, $sVar, 'email', 0];
            }
            db()->multiInsert(Phpfox::getT('user_notification'), [
                'user_id', 'user_notification', 'notification_type', 'is_admin_default'
            ], $aDefaultEmailInsert);
        }
        $aDefaultSmsNotification = Phpfox::getService('admincp.setting')->getDefaultNotificationSettings('sms', true, true);
        if (count($aDefaultSmsNotification)) {
            $aDefaultSmsInsert = [];
            foreach ($aDefaultSmsNotification as $sVar => $iValue) {
                $aDefaultSmsInsert[] = [$iId, $sVar, 'sms', 0];
            }
            db()->multiInsert(Phpfox::getT('user_notification'), [
                'user_id', 'user_notification', 'notification_type', 'is_admin_default'
            ], $aDefaultSmsInsert);
        }
        return true;
    }
}
