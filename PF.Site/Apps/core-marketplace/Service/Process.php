<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Marketplace\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Image;
use Phpfox_Parse_Input;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Service;
use Phpfox_Url;


defined('PHPFOX') or exit('NO DICE!');


class Process extends Phpfox_Service
{
    private $_bHasImage = false;

    private $_aCategories = [];
    private $_aInvited;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('marketplace');
    }

    public function reopenListing($iListing)
    {
        db()->update(Phpfox::getT('marketplace'), ['time_stamp' => PHPFOX_TIME], 'listing_id = ' . (int)$iListing);
        return true;
    }

    public function add($aVals)
    {
        // Plugin call
        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_process_add__start')) {
            eval($sPlugin);
        }

        if (!$this->_verify($aVals)) {
            return false;
        }

        if (!isset($aVals['privacy'])) {
            $aVals['privacy'] = 0;
        }
        if (!Phpfox::getService('ban')->checkAutomaticBan($aVals)) {
            return false;
        }
        $oParseInput = Phpfox_Parse_Input::instance();
        $bHasAttachments = (!empty($aVals['attachment']));
        $aSql = [
            'view_id'             => (Phpfox::getUserParam('marketplace.listing_approve') ? '1' : '0'),
            'privacy'             => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
            'privacy_comment'     => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
            'group_id'            => 0,
            'user_id'             => Phpfox::getUserId(),
            'title'               => $oParseInput->clean($aVals['title'], 255),
            'currency_id'         => $aVals['currency_id'],
            'price'               => $this->_price($aVals['price']),
            'country_iso'         => $aVals['country_iso'],
            'country_child_id'    => (isset($aVals['country_child_id']) ? (int)$aVals['country_child_id'] : 0),
            'location'            => !empty($aVals['location']) ? $oParseInput->clean($aVals['location'], 255) : '',
            'location_lat'        => !empty($aVals['location_lat']) ? $aVals['location_lat'] : '',
            'location_lng'        => !empty($aVals['location_lng']) ? $aVals['location_lng'] : '',
            'time_stamp'          => PHPFOX_TIME,
            'is_sell'             => (isset($aVals['is_sell']) ? (int)$aVals['is_sell'] : 0), // allow payment gateways
            'allow_point_payment' => (isset($aVals['allow_point_payment']) ? (int)$aVals['allow_point_payment'] : 0), // allow Activity Point payment
            'auto_sell'           => (isset($aVals['auto_sell']) ? (int)$aVals['auto_sell'] : 0),
            'mini_description'    => (empty($aVals['mini_description']) ? null : $oParseInput->clean($aVals['mini_description'],
                255))
        ];

        if (isset($aVals['item_id']) && isset($aVals['module_id'])) {
            $aSql['item_id'] = (int)$aVals['item_id'];
            $aSql['module_id'] = $oParseInput->clean($aVals['module_id']);
        } else {
            $aVals['module_id'] = 'marketplace';
            $aVals['item_id'] = 0;
        }

        $iId = $this->database()->insert($this->_sTable, $aSql);

        (($sPlugin = Phpfox_Plugin::get('marketplace.service_process_add')) ? eval($sPlugin) : false);

        if (!$iId) {
            return false;
        }
        //Add hashtag
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->add('marketplace', $iId, Phpfox::getUserId(), $aVals['description'],
                true);
        }
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_tag_support')) {
            if (Phpfox::isModule('tag') && isset($aVals['tag_list']) && ((is_array($aVals['tag_list']) && count($aVals['tag_list'])) || (!empty($aVals['tag_list'])))) {
                Phpfox::getService('tag.process')->add('marketplace', $iId, Phpfox::getUserId(), $aVals['tag_list']);
            }
        }
        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }
        $this->database()->insert(Phpfox::getT('marketplace_text'), [
                'listing_id'         => $iId,
                'description'        => (empty($aVals['description']) ? null : $oParseInput->clean($aVals['description'])),
                'description_parsed' => (empty($aVals['description']) ? null : $oParseInput->prepare($aVals['description']))
            ]
        );

        foreach ($this->_aCategories as $iCategoryId) {
            $this->database()->insert(Phpfox::getT('marketplace_category_data'),
                ['listing_id' => $iId, 'category_id' => $iCategoryId]);
        }
        $this->cache()->removeGroup('marketplace_category');

        if (!Phpfox::getUserParam('marketplace.listing_approve')) {
            if ($aVals['module_id'] == 'marketplace' &&
                Phpfox::isModule('feed') &&
                Phpfox::getParam('marketplace.marketplace_allow_create_feed_when_add_new_item', 1)) {
                Phpfox::getService('feed.process')->add('marketplace', $iId, $aVals['privacy'],
                    (isset($aVals['privacy_comment']) ? (int)$aVals['privacy_comment'] : 0));
            } else {
                if (Phpfox::isModule('feed') && Phpfox::getParam('marketplace.marketplace_allow_create_feed_when_add_new_item', 1)) {
                    Phpfox::getService('feed.process')
                        ->callback(Phpfox::callback($aVals['module_id'] . '.getFeedDetails', $aVals['item_id']))
                        ->add('marketplace', $iId, $aVals['privacy'],
                            (isset($aVals['privacy_comment']) ? (int)$aVals['privacy_comment'] : 0), $aVals['item_id']);

                }

                //support add notification for parent module
                if (Phpfox::isModule('notification') && $aVals['module_id'] != 'marketplace' &&
                    Phpfox::isModule($aVals['module_id']) && Phpfox::hasCallback($aVals['module_id'],
                        'addItemNotification')
                ) {
                    Phpfox::callback($aVals['module_id'] . '.addItemNotification', [
                        'page_id'      => $aVals['item_id'],
                        'item_perm'    => 'marketplace.view_browse_marketplace_listings',
                        'item_type'    => 'marketplace',
                        'item_id'      => $iId,
                        'owner_id'     => Phpfox::getUserId(),
                        'items_phrase' => 'marketplace_listings'
                    ]);
                }
            }

            Phpfox::getService('user.activity')->update(Phpfox::getUserId(), 'marketplace');
        }

        if (Phpfox::isModule('privacy') && $aVals['privacy'] == '4') {
            Phpfox::getService('privacy.process')->add('marketplace', $iId,
                (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
        }

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->add('marketplace', $iId, Phpfox::getUserId(), $aVals['description'],
                true);
        }

        //Plugin call
        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_process_add__end')) {
            eval($sPlugin);
        }

        return $iId;
    }

    private function _verify($aVals)
    {
        if (!isset($aVals['category'])) {
            return Phpfox_Error::set(_p('provide_a_category_this_listing_will_belong_to'));
        }

        if (!empty($aVals['title']) && mb_strlen($aVals['title']) > 100) {
            return Phpfox_Error::set(_p('marketplace_maximum_length_for_name_is_number_characters', ['number' => 100]));
        }

        foreach ($aVals['category'] as $iCategory) {
            $iCategory = trim($iCategory);

            if (empty($iCategory)) {
                continue;
            }

            if (!is_numeric($iCategory)) {
                continue;
            }

            $this->_aCategories[] = $iCategory;
        }

        if (!count($this->_aCategories)) {
            return Phpfox_Error::set(_p('provide_a_category_this_listing_will_belong_to'));
        }

        if (isset($_FILES['image'])) {
            foreach ($_FILES['image']['error'] as $iKey => $sError) {
                if ($sError == UPLOAD_ERR_OK) {
                    $aImage = Phpfox_File::instance()->load('image[' . $iKey . ']', [
                            'jpg',
                            'gif',
                            'png'
                        ]
                    );

                    if ($aImage === false) {
                        continue;
                    }

                    $this->_bHasImage = true;
                }
            }
        }

        return true;
    }

    private function _price($sPrice)
    {
        if (empty($sPrice)) {
            return '0.00';
        }

        $sPrice = str_replace([' ', ','], '', $sPrice);
        $aParts = explode('.', $sPrice);
        if (count($aParts) > 2) {
            $iCnt = 0;
            $sPrice = '';
            foreach ($aParts as $sPart) {
                $iCnt++;
                $sPrice .= (count($aParts) == $iCnt ? '.' : '') . $sPart;
            }
        }

        return $sPrice;
    }

    public function update($iId, $aVals)
    {
        if (!$this->_verify($aVals)) {
            return false;
        }
        $aListing = $this->database()->select('*')
            ->from($this->_sTable)
            ->where('listing_id = ' . (int)$iId)
            ->execute('getSlaveRow');
        if (!isset($aListing['listing_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_listing_you_want_to_edit'));
        }

        $oParseInput = Phpfox::getLib('parse.input');
        if (!Phpfox::getService('ban')->checkAutomaticBan($aVals['title'] . ' ' . $aVals['description'])) {
            return false;
        }

        if (empty($aVals['privacy'])) {
            $aVals['privacy'] = 0;
        }
        if (empty($aVals['privacy_comment'])) {
            $aVals['privacy_comment'] = 0;
        }
        $bHasAttachments = (!empty($aVals['attachment']));
        if ($bHasAttachments) {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
        }
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->update('music_album', $iId, $aListing['user_id'], $aVals['description'],
                true);
        }
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_tag_support')) {
            if (Phpfox::isModule('tag')) {
                Phpfox::getService('tag.process')->update('music_album', $iId, $aListing['user_id'],
                    (!Phpfox::getLib('parse.format')->isEmpty($aVals['tag_list']) ? $aVals['tag_list'] : null));
            }
        }
        $aSql = [
            'privacy'             => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
            'privacy_comment'     => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
            'title'               => $oParseInput->clean($aVals['title'], 255),
            'currency_id'         => $aVals['currency_id'],
            'price'               => $this->_price($aVals['price']),
            'country_iso'         => $aVals['country_iso'],
            'country_child_id'    => (isset($aVals['country_child_id']) ? (int)$aVals['country_child_id'] : 0),
            'location'            => (!empty($aVals['location'])) ? $oParseInput->clean($aVals['location'], 255) : '',
            'location_lat'        => !empty($aVals['location_lat']) ? $aVals['location_lat'] : '',
            'location_lng'        => !empty($aVals['location_lng']) ? $aVals['location_lng'] : '',
            'is_sell'             => (isset($aVals['is_sell']) ? (int)$aVals['is_sell'] : 0), // allow payment gateways
            'allow_point_payment' => (isset($aVals['allow_point_payment']) ? (int)$aVals['allow_point_payment'] : 0), // allow Activity Point payment
            'auto_sell'           => (isset($aVals['auto_sell']) ? (int)$aVals['auto_sell'] : 0),
            'mini_description'    => (empty($aVals['mini_description']) ? null : $oParseInput->clean($aVals['mini_description'],
                255))
        ];
        if (isset($aVals['view_id']) && ($aVals['view_id'] == '0' || $aVals['view_id'] == '2')) {
            $aSql['view_id'] = $aVals['view_id'];
        }
        $this->database()->update($this->_sTable, $aSql, 'listing_id = ' . (int)$iId);
        $this->database()->update(Phpfox::getT('marketplace_text'), [
            'description'        => (empty($aVals['description']) ? null : $oParseInput->clean($aVals['description'])),
            'description_parsed' => (empty($aVals['description']) ? null : $oParseInput->prepare($aVals['description']))
        ], 'listing_id = ' . (int)$iId
        );

        (($sPlugin = Phpfox_Plugin::get('marketplace.service_process_update')) ? eval($sPlugin) : false);

        $this->database()->delete(Phpfox::getT('marketplace_category_data'), 'listing_id = ' . (int)$iId);
        foreach ($this->_aCategories as $iCategoryId) {
            $this->database()->insert(Phpfox::getT('marketplace_category_data'),
                ['listing_id' => $iId, 'category_id' => $iCategoryId]);
        }
        $this->cache()->removeGroup('marketplace_category');

        $aListing = $this->database()->select('*')
            ->from($this->_sTable)
            ->where('listing_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if ($this->_bHasImage) {
            $oImage = Phpfox_Image::instance();
            $oFile = Phpfox_File::instance();

            $aSizes = [50, 120, 200, 400];

            $iFileSizes = 0;
            foreach ($_FILES['image']['error'] as $iKey => $sError) {
                if ($sError == UPLOAD_ERR_OK) {
                    if ($aImage = $oFile->load('image[' . $iKey . ']', [
                        'jpg',
                        'gif',
                        'png'
                    ],
                        (Phpfox::getUserParam('marketplace.max_upload_size_listing') === 0 ? null : (Phpfox::getUserParam('marketplace.max_upload_size_listing') / 1024))
                    )
                    ) {
                        $sFileName = Phpfox_File::instance()->upload('image[' . $iKey . ']',
                            Phpfox::getParam('marketplace.dir_image'), $iId);

                        $iFileSizes += filesize(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, ''));

                        $this->database()->insert(Phpfox::getT('marketplace_image'), [
                            'listing_id' => $iId,
                            'image_path' => $sFileName,
                            'server_id'  => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
                        ]);

                        foreach ($aSizes as $iSize) {
                            $oImage->createThumbnail(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName,
                                    ''), Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, '_' . $iSize),
                                $iSize, $iSize);
                            $oImage->createThumbnail(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName,
                                    ''), Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName,
                                    '_' . $iSize . '_square'), $iSize, $iSize, false);

                            $iFileSizes += filesize(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName,
                                    '_' . $iSize));
                        }
                        //Crop max width
                        if (Phpfox::isAppActive('Core_Photos')) {
                            Phpfox::getService('photo')->cropMaxWidth(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName,
                                    ''));
                        }
                    }
                }
            }

            if ($iFileSizes === 0) {
                return false;
            }

            $this->database()->update($this->_sTable, [
                'image_path' => $sFileName,
                'server_id'  => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
            ], 'listing_id = ' . $iId);

            (($sPlugin = Phpfox_Plugin::get('marketplace.service_process_update__1')) ? eval($sPlugin) : false);

            // Update user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'marketplace', $iFileSizes);
        }

        if (isset($aVals['emails']) || isset($aVals['invite'])) {
            $aInvites = $this->database()->select('invited_user_id, invited_email')
                ->from(Phpfox::getT('marketplace_invite'))
                ->where('listing_id = ' . (int)$iId)
                ->execute('getSlaveRows');
            $aInvited = [];
            foreach ($aInvites as $aInvite) {
                $aInvited[(empty($aInvite['invited_email']) ? 'user' : 'email')][(empty($aInvite['invited_email']) ? $aInvite['invited_user_id'] : $aInvite['invited_email'])] = true;
            }
        }

        $aCachedEmails = [];
        if (isset($aVals['invite']) && is_array($aVals['invite'])) {
            $sUserIds = '';
            foreach ($aVals['invite'] as $iUserId) {
                if (!is_numeric($iUserId)) {
                    continue;
                }
                $sUserIds .= $iUserId . ',';
            }
            $sUserIds = rtrim($sUserIds, ',');

            $aUsers = $this->database()->select('user_id, email, language_id, full_name')
                ->from(Phpfox::getT('user'))
                ->where('user_id IN(' . $sUserIds . ')')
                ->execute('getSlaveRows');

            foreach ($aUsers as $aUser) {
                if (isset($aCachedEmails[$aUser['email']])) {
                    continue;
                }

                if (isset($aInvited['user'][$aUser['user_id']])) {
                    continue;
                }

                $sLink = Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'], $aListing['title']);
                $sMessage = _p('full_name_invited_you_to_view_the_marketplace_listing_title', [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title'     => $oParseInput->clean($aVals['title'], 255),
                    'link'      => $sLink
                ], $aUser['language_id']);
                if (!empty($aVals['personal_message'])) {
                    $sMessage .= "\n\n" . _p('full_name_added_the_following_personal_message', ['full_name' => Phpfox::getUserBy('full_name')], $aUser['language_id']);
                    $sMessage .= $aVals['personal_message'];
                }

                Phpfox::getLib('mail')->to($aUser['user_id'])
                    ->subject(_p(
                        'full_name_invited_you_to_view_the_listing_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aVals['title'], 255)
                        ],
                        $aUser['language_id']
                    ))
                    ->message($sMessage)
                    ->notification('marketplace.email_notification')
                    ->translated()
                    ->send();

                $aCachedEmails[$aUser['email']] = true;
                $this->_aInvited[] = ['user' => $aUser['full_name']];
                $this->database()->insert(Phpfox::getT('marketplace_invite'), [
                        'listing_id'      => $iId,
                        'user_id'         => Phpfox::getUserId(),
                        'invited_user_id' => $aUser['user_id'],
                        'time_stamp'      => PHPFOX_TIME
                    ]
                );

                (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('marketplace_invite', $iId, $aUser['user_id']) : null);
            }
        }

        if (isset($aVals['emails'])) {
            $aEmails = explode(',', $aVals['emails']);
            foreach ($aEmails as $sEmail) {
                $sEmail = trim($sEmail);
                if (!Phpfox::getLib('mail')->checkEmail($sEmail)) {
                    continue;
                }

                if (isset($aCachedEmails[$sEmail])) {
                    continue;
                }

                if (isset($aInvited['email'][$sEmail])) {
                    continue;
                }

                $sLink = Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'], $aListing['title']);
                $sMessage = _p('full_name_invited_you_to_view_the_marketplace_listing_title', [
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title'     => $oParseInput->clean($aVals['title'], 255),
                    'link'      => $sLink
                ]);
                if (!empty($aVals['personal_message'])) {
                    $sMessage .= "\n\n" . _p('full_name_added_the_following_personal_message', ['full_name' => Phpfox::getUserBy('full_name')]);
                    $sMessage .= $aVals['personal_message'];
                }

                $oMail = Phpfox::getLib('mail');
                $bSent = $oMail->to($sEmail)
                    ->subject(_p('marketplace.full_name_invited_you_to_view_the_listing_title',
                        [
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title'     => $oParseInput->clean($aVals['title'], 255)
                        ]
                    ))
                    ->message($sMessage)
                    ->translated()
                    ->send();

                if ($bSent) {
                    $this->_aInvited[] = ['email' => $sEmail];

                    $aCachedEmails[$sEmail] = true;

                    $this->database()->insert(Phpfox::getT('marketplace_invite'), [
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

        if (empty($aListing['module_id']) || $aListing['module_id'] == 'marketplace') {
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('marketplace', $iId, $aVals['privacy'], $aVals['privacy_comment']) : null);
        }

        if (Phpfox::isModule('privacy')) {
            if ($aVals['privacy'] == '4') {
                Phpfox::getService('privacy.process')->update('marketplace', $iId,
                    (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
            } else {
                Phpfox::getService('privacy.process')->delete('marketplace', $iId);
            }
        }

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
            Phpfox::getService('tag.process')->update('marketplace', $iId, Phpfox::getUserId(), $aVals['description'],
                true);
        }

        return true;
    }

    /**
     * @param      $iId
     * @param null $aListing
     * @param bool $bForce
     *
     * @return bool
     * @throws \Exception
     */
    public function delete($iId, &$aListing = null, $bForce = false)
    {
        if ($aListing === null) {
            $aListing = $this->database()->select('user_id, image_path, view_id, is_sponsor, is_featured')
                ->from($this->_sTable)
                ->where('listing_id = ' . (int)$iId)
                ->execute('getSlaveRow');
        }

        if (!isset($aListing['user_id'])) {
            return $bForce ? false : Phpfox_Error::set(_p('unable_to_find_the_listing_you_want_to_delete'));
        }

        if (!$bForce && !Phpfox::getService('user.auth')->hasAccess('listing', 'listing_id', $iId,
                'marketplace.can_delete_own_listing', 'marketplace.can_delete_other_listings', $aListing['user_id'])
        ) {
            return Phpfox_Error::set(_p('you_do_not_have_sufficient_permission_to_delete_this_listing'));
        }

        $iFileSizes = 0;
        $aImages = $this->database()->select('image_id, image_path, server_id')
            ->from(Phpfox::getT('marketplace_image'))
            ->where('listing_id = ' . $iId)
            ->execute('getSlaveRows');
        foreach ($aImages as $aImage) {
            $this->deleteImage($aImage['image_id']);
        }

        if ($iFileSizes > 0) {
            Phpfox::getService('user.space')->update($aListing['user_id'], 'marketplace', $iFileSizes, '-');
        }

        // Delete attachment
        (Phpfox::isModule('attachment') ? Phpfox::getService('attachment.process')->deleteForItem($aListing['user_id'], $iId, 'marketplace') : null);

        (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem(null, $iId, 'marketplace') : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('marketplace', $iId) : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_marketplace', $iId) : null);
        (Phpfox::isModule('like') ? Phpfox::getService('like.process')->delete('marketplace', (int)$iId, 0,
            true) : null);
        (Phpfox::isModule('notification') ? Phpfox::getService('notification.process')->deleteAllOfItem([
            'marketplace_like',
            'marketplace_approved'
        ], (int)$iId) : null);

        //close all sponsorships
        (Phpfox::isAppActive('Core_BetterAds') ? Phpfox::getService('ad.process')->closeSponsorItem('marketplace', (int)$iId) : null);

        $this->database()->delete($this->_sTable, 'listing_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('marketplace_text'), 'listing_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('marketplace_category_data'), 'listing_id = ' . (int)$iId);
        $this->cache()->removeGroup('marketplace_category');

        if ((int)$aListing['view_id'] == 0) {
            Phpfox::getService('user.activity')->update($aListing['user_id'], 'marketplace', '-');
        }

        Phpfox::massCallback('deleteItem', [
            'sModule' => 'marketplace',
            'sTable'  => Phpfox::getT('marketplace'),
            'iItemId' => $iId
        ]);

        if ($aListing['is_sponsor'] == 1) {
            $this->cache()->remove('marketplace_sponsored');
        }
        if ($aListing['is_featured'] == 1) {
            $this->cache()->remove('marketplace_featured');
        }

        (($sPlugin = Phpfox_Plugin::get('marketplace.service_process_delete__1')) ? eval($sPlugin) : false);
        return true;
    }

    public function deleteImage($iImageId, $bReturnDefault = false)
    {
        $aListing = $this->database()->select('mi.image_id, mi.image_path, mi.server_id, m.user_id, m.listing_id, m.image_path AS default_image_path')
            ->from(Phpfox::getT('marketplace_image'), 'mi')
            ->join($this->_sTable, 'm', 'm.listing_id = mi.listing_id')
            ->where('mi.image_id = ' . (int)$iImageId)
            ->execute('getSlaveRow');

        if (!isset($aListing['user_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_image_dot'));
        }

        if (!Phpfox::getService('user.auth')->hasAccess('listing', 'listing_id', $aListing['listing_id'],
            'marketplace.can_edit_own_listing', 'marketplace.can_edit_other_listing', $aListing['user_id'])
        ) {
            return Phpfox_Error::set(_p('you_do_not_have_sufficient_permission_to_modify_this_listing'));
        }
        $aImage = [];
        if ($aListing['default_image_path'] == $aListing['image_path']) {
            $aImage = $this->database()->select('image_path, server_id, image_id')
                ->from(Phpfox::getT('marketplace_image'))
                ->where("listing_id = $aListing[listing_id] && image_id != $iImageId")
                ->execute('getSlaveRow');

            $this->database()->update($this->_sTable, [
                'image_path' => (isset($aImage['image_path']) ? $aImage['image_path'] : null),
                'server_id'  => (isset($aImage['server_id']) ? $aImage['server_id'] : 0)
            ], 'listing_id = ' . $aListing['listing_id']);
        }
        $aParams = Phpfox::getService('marketplace')->getUploadParams();
        $aParams['type'] = 'marketplace';
        $aParams['path'] = $aListing['image_path'];
        $aParams['user_id'] = $aListing['user_id'];
        $aParams['update_space'] = ($aListing['user_id'] ? true : false);
        $aParams['server_id'] = $aListing['server_id'];

        if (Phpfox::getService('user.file')->remove($aParams)) {
            $this->database()->delete(Phpfox::getT('marketplace_image'), 'image_id = ' . $aListing['image_id']);
        } else {
            return false;
        }
        (($sPlugin = Phpfox_Plugin::get('marketplace.service_process_deleteimage__1')) ? eval($sPlugin) : false);

        if ($bReturnDefault) {
            return $aImage;
        }
        return true;
    }

    public function setVisit($iId, $iUserId)
    {
        $this->database()->update(Phpfox::getT('marketplace_invite'), ['visited_id' => 1],
            'listing_id = ' . (int)$iId . ' AND invited_user_id = ' . (int)$iUserId);

        (Phpfox::isModule('request') ? Phpfox::getService('request.process')->delete('marketplace_invite', $iId,
            $iUserId) : null);
    }

    public function feature($iId, $iType)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('marketplace.can_feature_listings', true);

        $this->database()->update($this->_sTable, ['is_featured' => ($iType ? '1' : '0')],
            'listing_id = ' . (int)$iId);


        return true;
    }

    public function sponsor($iId, $iType)
    {
        if (!Phpfox::getUserParam('marketplace.can_sponsor_marketplace') && !Phpfox::getUserParam('marketplace.can_purchase_sponsor') && !defined('PHPFOX_API_CALLBACK')) {
            return Phpfox_Error::set(_p('hack_attempt'));
        }
        $iType = (int)$iType;
        $iId = (int)$iId;
        if ($iType != 0 && $iType != 1) {
            return false;
        }
        $this->database()->update($this->_sTable, ['is_sponsor' => $iType],
            'listing_id = ' . $iId
        );

        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_sponsor__end')) {
            eval($sPlugin);
        }
        return true;
    }

    public function approve($iId)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('marketplace.can_approve_listings', true);

        $aListing = $this->database()->select(Phpfox::getUserField() . ', m.*')
            ->from($this->_sTable, 'm')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
            ->where('m.listing_id = ' . (int)$iId . ' AND m.view_id = 1')
            ->execute('getSlaveRow');

        if (!isset($aListing['listing_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_listing_you_want_to_approve'));
        }

        $this->database()->update($this->_sTable, ['view_id' => '0', 'time_stamp' => PHPFOX_TIME],
            'listing_id = ' . $aListing['listing_id']);

        if (Phpfox::isModule('notification')) {
            Phpfox::getService('notification.process')->add('marketplace_approved', $aListing['listing_id'],
                $aListing['user_id']);
        }

        // Send the user an email
        $sLink = Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'], $aListing['title']);

        // update activity point
        Phpfox::getService('user.activity')->update($aListing['user_id'], 'marketplace');

        (($sPlugin = Phpfox_Plugin::get('marketplace.service_process_approve__1')) ? eval($sPlugin) : false);

        Phpfox::getLib('mail')->to($aListing['user_id'])
            ->subject([
                'marketplace.your_listing_has_been_approved_on_site_title',
                ['site_title' => Phpfox::getParam('core.site_title')]
            ])
            ->message([
                'marketplace.your_listing_has_been_approved_on_site_title_message',
                ['site_title' => Phpfox::getParam('core.site_title'), 'link' => $sLink]
            ])
            ->notification('marketplace.email_notification')
            ->send();

        if ($aListing['module_id'] == 'marketplace' &&
            Phpfox::isModule('feed') &&
            Phpfox::getParam('marketplace.marketplace_allow_create_feed_when_add_new_item', 1)) {
            Phpfox::getService('feed.process')->add('marketplace', $iId, $aListing['privacy'],
                (isset($aListing['privacy_comment']) ? (int)$aListing['privacy_comment'] : 0), 0, $aListing['user_id']);
        } else {
            if (Phpfox::isModule('feed') && Phpfox::getParam('marketplace.marketplace_allow_create_feed_when_add_new_item', 1)) {
                Phpfox::getService('feed.process')
                    ->callback(Phpfox::callback($aListing['module_id'] . '.getFeedDetails', $aListing['item_id']))
                    ->add('marketplace', $iId, $aListing['privacy'],
                        (isset($aListing['privacy_comment']) ? (int)$aListing['privacy_comment'] : 0), $aListing['item_id'], $aListing['user_id']);

            }

            //support add notification for parent module
            if (Phpfox::isModule('notification') && $aListing['module_id'] != 'marketplace' &&
                Phpfox::isModule($aListing['module_id']) && Phpfox::hasCallback($aListing['module_id'], 'addItemNotification')
            ) {
                Phpfox::callback($aListing['module_id'] . '.addItemNotification', [
                    'page_id'      => $aListing['item_id'],
                    'item_perm'    => 'marketplace.view_browse_marketplace_listings',
                    'item_type'    => 'marketplace',
                    'item_id'      => $iId,
                    'owner_id'     => $aListing['user_id'],
                    'items_phrase' => 'marketplace_listings'
                ]);
            }
        }
        return true;
    }

    public function addInvoice($iId, $sCurrency, $sCost)
    {
        $iInvoiceId = $this->database()->insert(Phpfox::getT('marketplace_invoice'), [
                'listing_id'  => $iId,
                'user_id'     => Phpfox::getUserId(),
                'currency_id' => $sCurrency,
                'price'       => $sCost,
                'time_stamp'  => PHPFOX_TIME
            ]
        );

        return $iInvoiceId;
    }

    public function sendExpireNotifications()
    {
        if (Phpfox::getParam('marketplace.days_to_expire_listing') < 1 || Phpfox::getParam('marketplace.days_to_notify_expire') < 1) {
            return true;
        }

        // Lets use caching to make sure we dont check too often
        $sCacheId = $this->cache()->set('marketplace_notify_expired');
        if (!($bCheck = $this->cache()->get($sCacheId, 86400))) {
            $iDaysToExpireSinceAdded = (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400);
            $iExpireDaysInSeconds = (Phpfox::getParam('marketplace.days_to_notify_expire') * 86400);
            /* We should notify them when it is
             *
             * I added the listing today at 13:00 and I set it to expire in 2 days and to notify in 1 day.
             * Right now it is 13:05, it should not send a notification
             * Right now it is 1 day and 2 minutes, it has not sent a notification, it should send a notification
             * */
            // Get the listings to notify
            $aNotify = $this->database()->select('m.listing_id, m.title, u.full_name, u.email, m.user_id')
                ->from(Phpfox::getT('marketplace'), 'm')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
                ->where('(m.is_notified = 0) AND ((m.time_stamp + ' . $iExpireDaysInSeconds . ') < ' . PHPFOX_TIME . ') AND ((m.time_stamp + ' . $iDaysToExpireSinceAdded . ') >= ' . PHPFOX_TIME . ')')
                ->execute('getSlaveRows');

            if (!empty($aNotify)) {
                $aUpdate = [];
                foreach ($aNotify as $aRow) {
                    Phpfox::getLib('mail')
                        ->to($aRow['user_id'])
                        ->sendToSelf(true)
                        ->subject([
                            'marketplace.listing_expiring_subject',
                            [
                                'title'      => $aRow['title'],
                                'site_title' => Phpfox::getParam('core.site_title'),
                                'days'       => (Phpfox::getParam('marketplace.days_to_expire_listing') - Phpfox::getParam('marketplace.days_to_notify_expire'))
                            ]
                        ])
                        ->message([
                            'marketplace.listing_expiring_message',
                            [
                                'site_name'  => Phpfox::getParam('core.site_title'),
                                'title'      => $aRow['title'],
                                'site_title' => Phpfox::getParam('core.site_title'),
                                'link'       => Phpfox_Url::instance()->permalink('marketplace', $aRow['listing_id'],
                                    $aRow['title']),
                                'days'       => (Phpfox::getParam('marketplace.days_to_expire_listing') - Phpfox::getParam('marketplace.days_to_notify_expire'))
                            ]
                        ])
                        ->send();

                    $aUpdate[] = $aRow['listing_id'];
                }

                $this->database()->update(Phpfox::getT('marketplace'), ['is_notified' => 1],
                    'listing_id IN (' . implode(',', $aUpdate) . ')');
            }
        }
        return null;
    }

    public function __destruct()
    {
        $this->_aCategories = [];
    }

    /**
     * @return array
     */
    public function getACategories()
    {
        return $this->_aCategories;
    }

    /**
     * @param array $aCategories
     */
    public function setACategories($aCategories)
    {
        $this->_aCategories = $aCategories;
    }

    /**
     * @param int $iId
     *
     * @return bool
     */
    public function updateView($iId)
    {
        $this->database()->update($this->_sTable, ['total_view' => 'total_view + 1'], ['listing_id' => (int)$iId],
            false);

        return true;
    }

    public function setDefault($iImageId)
    {
        $aListing = $this->database()->select('mi.image_path, mi.server_id, m.user_id, m.listing_id')
            ->from(Phpfox::getT('marketplace_image'), 'mi')
            ->join($this->_sTable, 'm', 'm.listing_id = mi.listing_id')
            ->where('mi.image_id = ' . (int)$iImageId)
            ->execute('getSlaveRow');

        if (!isset($aListing['user_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_image_dot'));
        }

        if (!Phpfox::getService('user.auth')->hasAccess('listing', 'listing_id', $aListing['listing_id'],
            'marketplace.can_delete_own_listing', 'marketplace.can_delete_other_listings', $aListing['user_id'])
        ) {
            return Phpfox_Error::set(_p('you_do_not_have_sufficient_permission_to_modify_this_listing'));
        }

        $this->database()->update($this->_sTable,
            ['image_path' => $aListing['image_path'], 'server_id' => $aListing['server_id']],
            'listing_id = ' . $aListing['listing_id']);

        (($sPlugin = Phpfox_Plugin::get('marketplace.service_process_setdefault__1')) ? eval($sPlugin) : false);


        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('marketplace', $aListing['listing_id']) : null);

        return true;
    }

    public function convertOldLocation($aParams)
    {
        $iLastId = isset($aParams['last_id']) ? $aParams['last_id'] : 0;
        $iLimit = 50;
        $aOldListings = db()->select('*')
            ->from(':marketplace')
            ->where([
                'location_lat IS NULL',
                'AND location_lng IS NULL',
                'AND listing_id > ' . (int)$iLastId
            ])->order('listing_id ASC')->limit($iLimit)->executeRows();
        if (!count($aOldListings)) {
            return false;
        }
        $newLastId = $aOldListings[count($aOldListings) - 1]['listing_id'];
        foreach ($aOldListings as $sKey => $aListing) {
            $sFullAddress = '';
            if ($aListing['city']) {
                $sFullAddress .= $aListing['city'];
            }
            if ($aListing['postal_code']) {
                $sFullAddress .= ' ' . $aListing['postal_code'];
            }
            if ($aListing['country_child_id']) {
                $sFullAddress .= ', ' . Phpfox::getService('core.country')->getChild($aListing['country_child_id']);
            }
            if ($aListing['country_iso']) {
                $sFullAddress .= ', ' . Phpfox::getService('core.country')->getCountry($aListing['country_iso']);
            }
            $sFullAddress = ltrim($sFullAddress, ',');
            $sFullAddress = trim($sFullAddress);
            $aLocation = Phpfox::getLib('location.gmap')->convertToLatLng($sFullAddress);
            if (!$aLocation) {
                db()->update(':marketplace', [
                    'location'     => $sFullAddress,
                    'location_lat' => '',
                    'location_lng' => '',
                ], 'listing_id = ' . (int)$aListing['listing_id']);
            } else {
                db()->update(':marketplace', [
                    'location'     => $sFullAddress,
                    'location_lat' => $aLocation['latitude'],
                    'location_lng' => $aLocation['longitude'],
                ], 'listing_id = ' . (int)$aListing['listing_id']);
            }
        }
        $iRemain = db()->select('COUNT(*)')->from(':marketplace')->where([
            'location_lat IS NULL',
            'AND location_lng IS NULL',
            'AND listing_id > ' . (int)$newLastId
        ])->executeField();

        return [
            'last_id'      => $newLastId,
            'total_remain' => $iRemain
        ];
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}