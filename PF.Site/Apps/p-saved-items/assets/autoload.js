var appSavedItem = {
    isDetailPage: null,
    turnOnConfirmationWithUnsavedAction: null,
    stopPropagation: function(event, obj) {
        event.stopPropagation();
        if($(obj).hasClass('dropdown')) {
            let dropdown = $(obj);
            if(dropdown.hasClass('open')) {
                dropdown.removeClass('open');
                dropdown.find('a:first').prop('aria-expanded', false);
                dropdown.find('.js_create_collection_container').addClass('hide');
            }
            else {
                dropdown.addClass('open');
                dropdown.find('a:first').prop('aria-expanded', true);
            }
        }
    },
    processItem: function(params) {
        if(!empty(params['type_id']) && parseInt(params['item_id']) > 0) {
            let isSave = parseInt(params['is_save']) == 1;
            let feed_id = !empty(params['feed_id']) && parseInt(params['feed_id']) > 0 ? parseInt(params['feed_id']) : 0;
            if(!isSave && !isset(params['unsave_confirmation']) && feed_id > 0 && appSavedItem.isDetailPage && appSavedItem.turnOnConfirmationWithUnsavedAction) {
                tb_show(oTranslations['saveditems_unsave_item'], $.ajaxBox('saveditems.openConfirmationPopup', 'feed_id=' + feed_id + '&type_id=' + params['type_id'] + '&item_id=' + params['item_id'] + '&is_save=' + parseInt(params['is_save']) + '&link=' + (!empty(params['link']) ? encodeURIComponent(params['link']) : '') + '&is_detail=1'));
            }
            else {
                $.ajaxCall('saveditems.processItem', 'feed_id=' + feed_id + '&type_id=' + params['type_id'] + '&item_id=' + params['item_id'] + '&is_save=' + parseInt(params['is_save']) + '&link=' + (!empty(params['link']) ? encodeURIComponent(params['link']) : '') + '&is_detail=' + appSavedItem.isDetailPage);
                if(isset(params['unsave_confirmation'])) {
                    tb_remove();
                }
            }

            return false;
        }
    },
    unsave: function(obj) {
        let _this = $(obj);

        if(_this.closest('[data-target="saved_item"]').length) {
            let parent = _this.closest('[data-target="saved_item"]');
            let savedId = parseInt(parent.data('id'));

            if(!empty(savedId) && savedId > 0) {
                if(parent.data('added') && empty(_this.data('remove-from-collection'))) {
                    $Core.jsConfirm({message: oTranslations['saveditems_unsave_from_collection_notice']}, function () {
                        $.ajaxCall('saveditems.processItem', 'saved_id=' + savedId + '&is_save=0');
                    });
                }
                else {
                    let collectionId = parseInt(parent.data('collection'));
                    $.ajaxCall('saveditems.processItem', 'saved_id=' + savedId + '&is_save=0' + (collectionId > 0 && !empty(_this.data('remove-from-collection')) ? ('&collection_id=' + collectionId + '&no_confirmation_with_collection=1') : ''));
                }
            }


        }

        return false;
    },
    appendSavedAlert: function(feedId, content) {
        let actionBtn = $('.js_saved_item_' + feedId);
        if(actionBtn.length) {
            let parent = actionBtn.closest('.js_feed_view_more_entry_holder');
            let contentObject = $(content);
            if(parent.find('.js_saved_alert_item:first').length) {
                parent.find('.js_saved_alert_item:first').remove();
            }
            contentObject.prependTo(parent);
        }
    },
    appendCollectionList: function(savedId, content) {
        if($('.js_saved_item_' + savedId).length) {
            let targetEle = $('.js_saved_item_' + savedId).find('.js_collection_information');
            if(targetEle.find('.js_collection_item').length) {
                targetEle.find('.js_collection_item').remove();
            }
            if(!empty(content)) {
                targetEle.append(content);
            }
        }
    },
    processFormAddCollection: function(event, obj) {
        event.stopPropagation();
        let parent = $(obj).closest('.js_saved_alert_item');
        let form = parent.find('.js_create_collection_container');
        if(form.hasClass('hide')) {
            form.removeClass('hide');
        } else {
            form.addClass('hide');
        }
    },
    createCollection: function(obj) {
        let parent = $(obj).closest('.js_create_collection_container'),
         collectionTitle = parent.find('.js_saveditems_collection_title_input').val(),
         noReload = $(obj).closest('.js_add_to_collection_container').length,
         id = parseInt($(obj).data('id')),
         collectionId = parseInt($(obj).data('collection')),
         detail = $(obj).data('detail'),
         feedId = $(obj).data('feed'),
         privacy = parent.find('input[name="val[privacy]"]').val(),
         privacy_list = [];
         parent.find('input[name="val[privacy_list][]"]').each(function() {
            privacy_list.push($(this).val());
         });


        $.ajaxCall('saveditems.createCollection', 'title=' + encodeURIComponent(collectionTitle) + (noReload ? ('&no_reload=' + noReload) : '') + (id ? ('&id=' + id) : '') + (collectionId ? ('&collection_id=' + collectionId) : '') + (detail ? '&detail=1' : '') + (feedId ? '&feed_id='+ feedId : '') + (privacy ? '&privacy='+ privacy : '') + (privacy_list? '&privacy_list='+ privacy_list : '') + ($(obj).data('keeppopup') ? '&keep_popup=1' : ''));
    },
    cancelCreateCollection: function(id) {
        let parent = $('[data-target="collection_form_'+ parseInt(id) +'"]');
        parent.addClass('hide');
        parent.find('.js_saveditems_collection_title_input').val('');
        parent.find('.js_error').addClass('hide').html('');
    },
    addItemToCollection: function(obj) {
        let savedId = $(obj).data('id');
        let collectionId = $(obj).data('collection');
        let feedId = $(obj).data('feed');
        if(parseInt(savedId) > 0 && parseInt(collectionId) > 0) {
            setTimeout(function() {
                let inputCheckbox = $('[data-target="saved_alert_item_' + savedId + '"] [data-collection="' + collectionId + '"] input[type="checkbox"]'),
                  isAdd = !inputCheckbox.prop('checked');
                inputCheckbox.prop('checked', isAdd);
                $.ajaxCall('saveditems.processAddItemToCollection', 'saved_id=' + parseInt(savedId) + '&collection_id=' + parseInt(collectionId) + '&is_add=' + (isAdd ? 1 : 0) + (parseInt(feedId) > 0 ? '&feed_id=' + feedId : ''));
            },50);
        }
        else {
            $(obj).find('input[type="checkbox"]:first').prop('checked', false)
        }
        return false;
    },
    deleteCollection: function(collectionId) {
        if(parseInt(collectionId) > 0) {
            $Core.jsConfirm({message: oTranslations['saveditems_are_you_sure_you_want_to_delete_this_collection']}, function(){
                $.ajaxCall('saveditems.deleteCollection', 'collection_id=' + parseInt(collectionId));
            });
        }
        return false;
    },
    processItemStatus: function(obj) {
        let status = $(obj).data('status');
        let savedId = parseInt($(obj).parent().data('id'));
        if(savedId > 0) {
            $.ajaxCall('saveditems.processItemStatus', 'id=' + savedId + '&status=' + parseInt(status));
        }
        return false;
    },
    showSuccessfulMessage: function(target_id, message) {
        let objectThis = $('.js_saved_item_' + target_id);
        if(appSavedItem.isDetailPage && !objectThis.closest('.js_feed_view_more_entry_holder').length) {
            if ($('#public_message').length == 0) {
                $('#main').prepend('<div class="public_message" id="public_message"></div>');
            }
            $('#public_message').html(message);
            $Behavior.addModerationListener();
        }
    },
    checkInDetailPage: function() {
        if(isset(oTranslations['saveditemscheckdetail_1'])) {
            appSavedItem.isDetailPage = 1;
            oTranslations["saveditemscheckdetail_1"] = null;
        }
        else {
            appSavedItem.isDetailPage = 0;
            oTranslations["saveditemscheckdetail_0"] = null;
        }
    },
    checkTurnOnConfirmationWithUnsavedAction: function() {
        if(isset(oTranslations['saveditemscheckturnonconfirmationwithunsavedaction_1'])) {
            appSavedItem.turnOnConfirmationWithUnsavedAction = 1;
            oTranslations["saveditemscheckturnonconfirmationwithunsavedaction_1"] = null;
        }
        else {
            appSavedItem.turnOnConfirmationWithUnsavedAction = 0;
            oTranslations["saveditemscheckturnonconfirmationwithunsavedaction_0"] = null;
        }
    },
    checkSetting: function() {
      appSavedItem.checkInDetailPage();
      appSavedItem.checkTurnOnConfirmationWithUnsavedAction();
    },
    changeCollectionTitleInDetail: function(url, title) {
        $('a[href="' + url + '"]').each(function(){
           if($(this).find('.item-name').length) {
               $(this).find('.item-name').html(title);
           }
           else {
               $(this).html(title);
           }
        });
    },
    toggleCollectionOnPopupSavedItem: function(){
        if($('.js_p_saveditems_action_collection_toggle').length){
            var btn = $('.js_p_saveditems_action_collection_toggle'),
                parent = btn.closest('.p-saveditems-dropdown-addto-collection-popup-content'),
                toggleContent = parent.find('.js_p_saveditems_wrapper_collection_toggle');
            btn.on('click',function(){
                btn.toggleClass('toggle');
                toggleContent.toggleClass('hide');
            });
        }
    },
    addFriendToCollection: function (form){
        var _form = $(form),
          btn = _form.find('button');
        btn.prop('disabled', true);

        $.fn.ajaxCall('saveditems.addFriendToCollection', _form.serialize(), null, null, function() {
            btn.prop('disabled', false);
            js_box_remove();
        });

        return false;
    },
    unShowDeleteFriend: function (iFriendId, iCollectionId) {
        if ($('#js_collection_' + iCollectionId + '_friend_' + iFriendId).length){
            $('#js_collection_' + iCollectionId + '_friend_' + iFriendId).remove();
        }
    },

    buildFriends: function($oObj) {
        if (this.isBeingBuilt === false &&
          !isset(this.aParams['is_mail']) && ((this.aParams['include_current_user'] !== $Core.searchFriendsInput.bIsIncludeCurrentUser) || empty($Cache.friends))) {
            $($oObj).val('');
            this.isBeingBuilt = true;
            $Core.searchFriendsInput.bIsIncludeCurrentUser = this.aParams['include_current_user'];
            $.ajaxCall('friend.buildCache',(this._get('include_current_user') ? 'include_current_user=1' : '') +
              (this._get('allow_custom') ? '&allow_custom=1' : ''), 'GET');
        }
    },
}

$Behavior.savedItem = function() {
    if($('.p-saveditems-status').length) {
        $('.p-saveditems-status[data-toggle="tooltip"]').tooltip();
    }
    $(document).on('click', '.js_saved_alert_item .js_add_to_collection_container', function(event) {
        event.stopPropagation();
    }).on('click', '.js_saved_alert_item .js_add_to_collection_btn', function(event) {
        $(this).closest('.js_saved_alert_item').find('.js_create_collection_container').addClass('hide');
    }).on('focus', '.js_saveditems_collection_title_input', function() {
        $(this).addClass('focus');
    }).on('blur', '.js_saveditems_collection_title_input', function() {
        $(this).removeClass('focus');
    }).off('keyup', '.js_saveditems_collection_title_input').on('keyup', '.js_saveditems_collection_title_input', function(e) {
        if(e.which == 13 && $(this).hasClass('focus')) {
            e.stopPropagation();
            appSavedItem.createCollection($(this).get(0));
        }
    });
    if(appSavedItem.isDetailPage == null && appSavedItem.turnOnPopupWithSavedAction == null && appSavedItem.turnOnConfirmationWithUnsavedAction == null) {
        appSavedItem.checkSetting();
    }
    //fix overlap layout
    $('.p-saveditems-dropdown-addto-collection.js_add_to_collection_container').on('show.bs.dropdown', function () {
        $(this).closest('.layout-middle').css('z-index','10');
    }).on('hidden.bs.dropdown', function () {
        $(this).closest('.layout-middle').css('z-index','');
    });
}

PF.event.on('on_page_change_end', function() {
    appSavedItem.checkSetting();
    //
    // if (typeof $Core.searchFriendsInput !== 'undefined') {
    //     $Core.searchFriendsInput.buildFriends = appSavedItem.buildFriends;
    // }
});