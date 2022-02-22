<?php

namespace Apps\P_SavedItems\Ajax;

use Phpfox_Ajax;
use Phpfox;
use Phpfox_Template;

class Ajax extends Phpfox_Ajax
{
    public function processItemStatus()
    {
        Phpfox::isUser(true);

        $savedId = $this->get('id');
        $status = $this->get('status');
        if (Phpfox::getService('saveditems.process')->processItemStatus($savedId, $status)) {
            $this->call('$(".js_saved_item_' . $savedId . '").find("[data-target=\'saveditems_status\']").' . ($status == 1 ? 'removeClass("hide");' : 'addClass("hide");'));
            $this->call('$(".js_saved_item_' . $savedId . '").find("[data-status=\'' . $status . '\'].js_saveditems_status").addClass("hide");');
            $this->call('$(".js_saved_item_' . $savedId . '").find("[data-status=\'' . ($status ? 0 : 1) . '\'].js_saveditems_status").removeClass("hide");');
        }
    }

    public function openAddCollectionPopup()
    {
        Phpfox::getBlock('saveditems.collection.add-collection-popup', [
            'savedId' => $this->get('saved_id')
        ]);
        echo '<script type="text/javascript">appSavedItem.toggleCollectionOnPopupSavedItem();</script>';
    }

    public function processItem()
    {
        Phpfox::getUserParam('saveditems.can_save_item', true);
        $typeId = $this->get('type_id');
        $itemId = $this->get('item_id');
        $isSave = $this->get('is_save');
        $feedId = $this->get('feed_id');
        $link = Phpfox::getLib('parse.format')->unhtmlspecialchars(urldecode($this->get('link')));
        $savedId = $this->get('saved_id');
        $isDetail = $this->get('is_detail');
        $noConfirmationWithCollection = $this->get('no_confirmation_with_collection');
        if ((empty($typeId) || empty($itemId)) && empty($savedId)) {
            return false;
        }

        if (!empty($savedId)) {
            $params = [
                'saved_id' => (int)$savedId,
                'is_save' => $isSave,
                'collection_id' => (int)$this->get('collection_id')
            ];
            if (Phpfox::getService('saveditems.process')->save($params)) {
                $this->call('$(".js_saved_item_' . $savedId . '").remove();if(!$(\'[data-target="saved_item"]\').length) {$Core.reloadPage();}');
            }
        } else {
            $params = [
                'type_id' => $typeId,
                'item_id' => $itemId,
                'link' => $link,
                'is_save' => $isSave
            ];

            if (!$isSave && !$isDetail && !$noConfirmationWithCollection) {
                if (Phpfox::getService('saveditems')->isItemBelongedToCollection([
                    'type_id' => $params['type_id'],
                    'item_id' => $params['item_id']
                ])) {
                    return $this->call('$Core.jsConfirm({message: "' . _p('saveditems_unsave_from_collection_notice') . '"}, function () {$.ajaxCall(\'saveditems.processItem\', \'feed_id=' . $feedId . '&type_id=' . $typeId . '&item_id=' . $itemId . '&is_save=0&link=' . $link . '&is_detail=' . $isDetail . '&no_confirmation_with_collection=1\');});');
                }
            }

            if ($savedId = Phpfox::getService('saveditems.process')->save($params)) {
                if (!empty($feedId)) {
                    if ($isSave) {
                        \Phpfox_Template::instance()->assign([
                            'savedId' => $savedId,
                            'collections' => Phpfox::getService('saveditems.collection')->getMyCollections(),
                            'feedId' => $feedId
                        ])->getTemplate('saveditems.block.saved-alert');
                    } else {
                        $this->call('$(\'.js_saved_item_' . $feedId . '\').closest(\'.js_feed_view_more_entry_holder\').find(\'.js_saved_alert_item:first\').remove();');
                        \Phpfox_Template::instance()->assign([
                            'unsaved' => true,
                            'savedId' => $savedId
                        ])->getTemplate('saveditems.block.saved-alert');
                    }
                    $content = $this->getContent(false);
                    $this->call('appSavedItem.appendSavedAlert(' . $this->get('feed_id') . ', \'' . addslashes($content) . '\');');

                    if ($isSave && !empty($isDetail) && Phpfox::getParam('saveditems.open_popup_in_item_detail')) {
                        $this->call('tb_show(\'' . _p('saveditems_item_saved_without_symbol') . '\', $.ajaxBox(\'saveditems.openAddCollectionPopup\', \'width=300&saved_id=' . $savedId . '\'));');
                    }
                }

                $targetId = !empty($feedId) ? $feedId : $itemId;
                if (!empty($isDetail)) {
                    $this->call('appSavedItem.showSuccessfulMessage(' . $targetId . ', "' . ($isSave ? _p('saveditems_saved_successfully') : _p('saveditems_unsaved_item_successfully')) . '");');
                }

                Phpfox_Template::instance()->assign([
                    'saveItemParams' => [
                        'id' => $targetId,
                        'type_id' => $typeId,
                        'item_id' => $itemId,
                        'is_saved' => $isSave,
                        'link' => urlencode($link)
                    ]
                ])->getTemplate('saveditems.block.save-action');
                $content = $this->getContent(false);
                $this->html('.js_saved_item_' . $targetId, $content);
            }
        }
    }

    public function showCreateCollectionPopup()
    {
        if ($collectionId = $this->get('collection_id')) {
            Phpfox::getUserParam('saveditems.can_edit_collection', true);
        } else {
            Phpfox::getUserParam('saveditems.can_create_collection', true);
        }
        Phpfox::getBlock('saveditems.collection.form', [
            'collectionId' => $collectionId,
            'detail' => $this->get('detail')
        ]);
    }

    public function createCollection()
    {
        if ($collectionId = $this->get('collection_id')) {
            Phpfox::getUserParam('saveditems.can_edit_collection', true);
        } else {
            Phpfox::getUserParam('saveditems.can_create_collection', true);
        }

        $title = trim(urldecode($this->get('title')));
        $id = $this->get('collection_id');
        $privacy = $this->get('privacy');
        $privacy_list = explode(',', $this->get('privacy_list'));
        $aVals = [
            'id' => $id,
            'title' => $title,
            'privacy' => $privacy,
            'privacy_list' => $privacy_list
        ];
        $selector = !empty($id) ? '[data-target="collection_form_' . $id . '"]' : '.js_create_collection_container';
        if ($title == "" || mb_strlen($title) > 128) {
            $this->call('$(\'' . $selector . ' .js_error' . '\').removeClass("hide");');
            $this->html($selector . ' .js_error',
                _p('saveditems_collection_title_can_not_be_empty_and_maximum_128_character'));
            return false;
        }
        if ($collectionId) {
            if (Phpfox::getService('saveditems.collection.process')->update($aVals)) {
                $updatedTitle = htmlspecialchars($title);
                if ($this->get('detail')) {
                    $this->call('appSavedItem.changeCollectionTitleInDetail(\'' . Phpfox::getLib('url')->makeUrl("saved.collection." . (int)$collectionId) . '\', \'' . $updatedTitle . '\');');
                } else {
                    $aCollection = Phpfox::getService('saveditems.collection')->getForEdit($collectionId);
                    $sIconClass = 'ico ';
                    switch ((int)$aCollection['privacy']) {
                        case 0:
                            $sIconClass .= 'ico-globe';
                            break;
                        case 1:
                            $sIconClass .= 'ico-user3-two';
                            break;
                        case 2:
                            $sIconClass .= 'ico-user-man-three';
                            break;
                        case 3:
                            $sIconClass .= 'ico-lock';
                            break;
                        case 4:
                            $sIconClass .= 'ico-gear-o';
                            break;
                        case 6:
                            $sIconClass .= 'ico-user-circle-alt-o';
                            break;
                    }
                    $updatedTitle .= " <span></span>";
                    $this->html('#js_saved_collection_' . $collectionId . ' .js_collection_title', $updatedTitle);
                    $this->call("$('#js_saved_collection_" . $collectionId . " .js_collection_title span').removeClass();");
                    $this->call("$('#js_saved_collection_" . $collectionId . " .js_collection_title span').addClass('" . $sIconClass . "');");
                }
                $this->call('tb_remove();');
            }
        } elseif ($collectionId = Phpfox::getService('saveditems.collection.process')->add($aVals)) {
            $keepPopup = $this->get('keep_popup');
            if (empty($keepPopup)) {
                $this->call('tb_remove();');
            }
            if ($this->get('no_reload') && ($savedId = $this->get('id'))) {
                $feedId = $this->get('feed_id');
                $this->call('appSavedItem.cancelCreateCollection(' . $savedId . ');');
                $checked = Phpfox::getService('saveditems.collection.process')->processSavedItem($collectionId,
                    $savedId);
                Phpfox_Template::instance()->assign([
                    'savedId' => $savedId,
                    'collectionId' => $collectionId,
                    'feedId' => $feedId,
                    'collectionName' => Phpfox::getLib('parse.output')->clean($title),
                    'checked' => !!$checked
                ])->getTemplate('saveditems.block.collection.quick-checkbox');
                $content = $this->getContent(false);
                $this->prepend('[data-target="saved_alert_item_' . $savedId . '"] .js_quick_list_collection', $content);
                $this->call('setTimeout(function(){$(\'[data-target="saved_alert_item_' . $savedId . '"] .js_quick_list_collection\').scrollTop(0);},50);');
                $this->call('if($(\'[data-target="saved_alert_item_' . $savedId . '"] .no-collections\').length) {$(\'[data-target="saved_alert_item_' . $savedId . '"] .no-collections\').remove();$(\'[data-target="saved_alert_item_' . $savedId . '"] .js_quick_list_collection\').removeClass("hide")}');
                $this->call('$(\'' . $selector . ' .js_error\').addClass("hide");');
                $this->html($selector . ' .js_error', '');

                if (!empty($feedId)) {
                    $count = Phpfox::getService('saveditems.collection')->getAddedToCollectionOfSavedItem($savedId);
                    $this->call('$(\'' . $selector . '\').closest(".js_add_to_collection_container").find(".js_add_to_collection_btn").html("' . _p($count == 1 ? 'saveditems_added_to_collection_one' : 'saveditems_added_to_collection_more',
                            ['number' => $count]) . '");');
                }

            } else {
                $this->call('window.location.href = \'' . Phpfox::getLib('url')->makeUrl('saved.collections') . '\'');
            }
        }
    }

    public function processAddItemToCollection()
    {
        Phpfox::isUser(true);
        $savedId = $this->get('saved_id');
        $collectionId = $this->get('collection_id');
        $isAdd = !!($this->get('is_add'));
        if (empty($savedId) || empty($collectionId)) {
            return false;
        }
        if (Phpfox::getService('saveditems.collection.process')->processSavedItem($collectionId, $savedId, $isAdd)) {
            if (($feedId = $this->get('feed_id'))) {
                $count = Phpfox::getService('saveditems.collection')->getAddedToCollectionOfSavedItem($savedId);
                if ($count > 0) {
                    $this->call('$(\'[data-target="saved_alert_item_' . $savedId . '"]\').find(".js_add_to_collection_btn:first").html("' . _p($count == 1 ? 'saveditems_added_to_collection_one' : 'saveditems_added_to_collection_more',
                            ['number' => $count]) . '");');
                } else {
                    $this->call('$(\'[data-target="saved_alert_item_' . $savedId . '"]\').find(".js_add_to_collection_btn:first").html("' . _p('saveditems_add_to_collection') . '");');
                }
            } elseif (empty($feedId)) {
                $collections = Phpfox::getService('saveditems')->getCollectionRelatedToSavedItem($savedId);
                $content = '';
                $addedToCollection = 0;
                if (!empty($collections[$savedId])) {
                    $collections = $collections[$savedId];
                    $addedToCollection = 1;
                    $collectionsId = array_column($collections, 'collection_id');
                    $defaultCollection = array_shift($collections);
                    $itemCollections = [
                        'default' => $defaultCollection,
                        'count' => !empty($collections) ? count($collections) : 0,
                        'other_collections' => !empty($collections) ? $collections : [],
                        'id' => $collectionsId
                    ];
                    Phpfox_Template::instance()->assign([
                        'itemCollections' => $itemCollections
                    ])->getTemplate('saveditems.block.collection.list');
                    $content = $this->getContent(false);
                }
                $this->call('appSavedItem.appendCollectionList(' . $savedId . ', \'' . ($content ? addslashes($content) : '') . '\');');
                $this->call('$(".js_saved_item_' . $savedId . '").data("added", ' . $addedToCollection . ');');
            }
        } else if (!\Phpfox_Error::isPassed()) {
            $this->call('$(\'[data-target="saved_alert_item_' . $savedId . '"] [data-collection="' . $collectionId . '"] input[type="checkbox"]\').prop(\'checked\', false);');
        }
    }

    public function deleteCollection()
    {
        Phpfox::getUserParam('saveditems.can_delete_collection', true);
        $collectionId = $this->get('collection_id');
        if (Phpfox::getService('saveditems.collection.process')->delete($collectionId)) {
            $this->call('$("#js_saved_collection_' . $collectionId . '").remove(); if(!$(".js_saved_collection").length){window.location.href="' . \Phpfox_Url::instance()->makeUrl('saved.collections') . '";}');
        }
    }

    public function openConfirmationPopup()
    {
        Phpfox::getUserParam('saveditems.can_save_item', true);
        Phpfox::getBlock('saveditems.open-confirmation-popup', [
            'feed_id' => $this->get('feed_id'),
            'type_id' => $this->get('type_id'),
            'item_id' => $this->get('item_id'),
            'link' => urldecode($this->get('link')),
        ]);
    }

    public function addFriend()
    {
        Phpfox::isUser(true);

        Phpfox::getBlock('saveditems.collection.add-friend-popup',
            [
                'title' => htmlspecialchars($this->get('title')),
                'collection_id' => $this->get('collection_id')
            ]);
    }

    public function addFriendToCollection()
    {
        Phpfox::isUser(true);
        $aVals = $this->get('val');
        if (empty($aUsers = $aVals['user_id'])) {
            \Phpfox_Error::set(_p('saveditems_user_cannot_empty'));
        }

        $iCollectionId = $aVals['collection_id'];

        if (empty(Phpfox::getService('saveditems.collection')->getForEdit($iCollectionId))) {
            \Phpfox_Error::set(_p('saveditems_collection_not_found'));
        }

        if (!\Phpfox_Error::isPassed()) {
            $this->errorSet('.js_error_add_friend');
        } else {
            Phpfox::getService('saveditems.collection.process')->addFriendsListToCollection($aUsers, $iCollectionId);

            Phpfox::addMessage(_p('saveditems_add_friend_successfully'));
            $this->call('window.location.reload();');
        }

    }

    public function showFriendListPopup()
    {
        Phpfox::isUser(true);

        Phpfox::getBlock('saveditems.collection.friend-list-popup', [
           'collection_id' => $this->get('collection_id')
        ]);
    }

    public function removeFriendFromCollection()
    {
        $iCollectionId = $this->get('collection_id');
        $iFriendId = $this->get('friend_id');
        $bIsLeave = $this->get('is_leave');

        if (Phpfox::getService('saveditems.friend.process')->removeFriend($iFriendId, $iCollectionId)){
            $this->call('appSavedItem.unShowDeleteFriend(' . $iFriendId . ',' . $iCollectionId . ');');
            if ($bIsLeave) {
                $this->call('$("#js_saved_collection_' . $iCollectionId . '").remove(); if(!$(".js_saved_collection").length){window.location.href="' . \Phpfox_Url::instance()->makeUrl('saved.collections') . '";}');
            }
        }
    }
}