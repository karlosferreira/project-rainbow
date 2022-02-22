$Behavior.addThreadMail = function () {
  if ($('.core-messages-admincp-messages').length) {
    coreMessagesAdmincp.init();
  }

  if ($('#page_mail_index').length) {
    if (!coreMessages.bContinueLoadMoreConversationContent) {
      coreMessages.bContinueLoadMoreConversationContent = true;
    }
    coreMessages.loadMailThread();
    coreMessagesHelper.addTargetBlank();
    coreMessages.initCustomScrollConversation();
    coreMessages.composeMailThread();
    coreMessages.changeGroupTitle();
    coreMessages.filterConversationByName();
    coreMessages.markConversation();
    coreMessagesCustomConversationMassActions.init();

    if (parseInt($('#js_compose_for_customlist').val())) {
      coreMessageScreen.checkComposeMessageForCustomlist();
    }
  }
  if ($('#page_mail_customlist_index').length) {
    coreMessages.initMembersCustomList();
    coreMessages.initCustomScrollCustomList();
    coreMessages.customListAction();
    coreMessages.changeCustomListTitle();
    coreMessages.initCustomListItemAction();
  }
  if ($('.js_box_content').find('#js_compose_new_message').length) {
    coreMessages.composeMailThread();
  }
  if ($('#js_main_mail_thread_holder').length) {
    coreMessages.processDeleteAttachment();
    coreMessages.processAttachmentCannotDelete();
    coreMessagesCustomAttachment.detectInvalidFile();
    if ($('#js_main_mail_thread_holder .attachment_holder_view').length) {
      $('#js_main_mail_thread_holder .attachment_holder_view').mCustomScrollbar('destroy');
    }
    if ($('.mail_user_image').length) {
      $('.mail_user_image').find('a:first').removeAttr('title');
    }
  }
  coreMessages.initMailThread();
}

var coreMessages = {
  bContinueLoadMore: true,
  bContinueLoadMoreConversationContent: true,
  iInitConversationsHeight: $('#js_conversation_load_more').innerHeight(),
  iScrollHeight: 0,
  aSelectedCustomlistUsers: {},
  onSendMessage: function (form) {
    var oOldLastMessage = $('.mail_thread_holder:last');
    var iAttachmentHolderId = $('.attachment-holder').attr('id');
    var $this = $(form);
    var url = $('#js_core_messages_url_send_message').val();
    var view = $('#js_search_view').val();
    var query = {
      'val[message]': '',
      'attachment_holder_id': iAttachmentHolderId,
      'val[attachment]': '',
      'val[thread_id]': '',
      'is_ajax_popup': ($this.closest('.js_box_content').length ? 1 : 0),
      'view': view
    };
    var aDatas = $this.serializeArray();
    $(aDatas).each(function (index, value) {
      query[value.name] = value.value;
    });
    $('#js_compose_message_textarea').val('');
    $.ajax({
      type: 'POST',
      url: url,
      data: query,
      timeout: 5 * 60 * 1000,
      success: function (data) {
        eval(data);
        $this.find('.js_attachment_list').hide();
        $this.find('.js_attachment:first').val('');
        var oCurrentLastMessage = $('.mail_thread_holder:last');
        if (oOldLastMessage.hasClass('is_user')) {
          if (parseInt(oOldLastMessage.data('order')) == 1) {
            oOldLastMessage.addClass('is_first_message');
          } else {
            oOldLastMessage.removeClass('is_last_message').addClass('is_middle_message');
          }
          var LastRealOrder = parseInt(oOldLastMessage.data('order'));
          oCurrentLastMessage.data('order', LastRealOrder);
        }
      }
    }).fail(function () {
      var oTemplate = $('.js_template_message_is_user').clone();
      var sFailText = query['val[message]'];
      oTemplate.find('.mail_text:first').html(sFailText);
      oTemplate.append('<div class="no_connection_message">This Message didn\'t send.</div>');
      $('#mail_threaded_new_message').append('<div class="mail_thread_holder is_user">' + oTemplate.html() + '</div>');
      $('.js_mail_messages_content').mCustomScrollbar('scrollTo', 'bottom', {scrollInertia: 0});
    });
    return false;
  },
  initMailThread: function () {
    coreMessages.onEnterSubmit();
    coreMessages.customEmojiPopup();
  },
  customEmojiPopup: function () {
    if ($('#js_compose_new_message').length) {
      $('#js_compose_new_message .emoji-popup').off('click').click(function () {
        var editorId = $(this).closest('.global_attachment_list').data('id');
        lastEmojiObject = $(this).closest('#js_compose_new_message').find('textarea#' + editorId),
          t = $(this);
        var js_box = $('.js_box_content'),
          is_popup = false;
        if (js_box.length) {
          if (js_box.find('#js_compose_new_message').length) {
            is_popup = true;
          }
        }
        $('#global_attachment_list_inline').remove();
        $(this).closest('.global_attachment_list').find('li > a.active:not(.emoji-popup)').removeClass('active');

        if (is_popup) {
          if (t.hasClass('active')) {
            t.removeClass('active');
            js_box.find('.emoji-list').hide();
            return false;
          }

          t.addClass('active');

          if (!t.hasClass('is_built')) {
            t.find('i').addClass('fa-spin');
            $.ajax({
              url: $(this).attr('href'),
              success: function (e) {
                js_box.find('.global_attachment').prepend(e.content);
                t.addClass('is_built');
                t.find('i').removeClass('fa-spin');
                js_box.find('.emoji-list').css('bottom', '40px');
                $Core.loadInit();
              },
            });
          } else {
            js_box.find('.emoji-list').show();
          }

          return false;
        }

        $(this).addClass('popup');
        tb_show('', $(this).attr('href'), $(this));
        $(this).removeClass('popup');

        return false;
      });
      $('#js_compose_new_message ul.emoji-list li').click(function () {
        $(this).closest('#js_compose_new_message').find('#js_compose_message_textarea').trigger('input');
      });
      $('.js_box_content ul.emoji-list li').click(function () {
        if ($('#js_compose_new_message').length) {
          $('#js_compose_new_message').find('#js_compose_message_textarea').trigger('input');
        }
      });
    }
  },
  initCustomScrollConversation: function () {
    $('#js_conversation_load_more').not('.mCustomScrollbar').mCustomScrollbar({
      autoHideScrollbar: true,
      theme: 'minimal',
      mouseWheel: {preventDefault: true},
      callbacks: {
        onScroll: function () {
          if (this.mcs.topPct == 100) {
            var canLoadMore = parseInt($('#js_check_load_more').val());
            if (coreMessages.bContinueLoadMore && canLoadMore) {
              coreMessages.bContinueLoadMore = false;
              $('#js_core_messages_conversation_list').append('<div class="mt-1 text-center js_core_messages_load_more_icon">\n' + '<i class="fa fa-spin fa-circle-o-notch"></i>\n' + '</div>');
              var page = $('#js_load_more_page').val();
              var data = decodeURI($('.core-messages-index-js #js_message_filter').serialize());
              $.ajaxCall('mail.loadMore', 'page=' + page + (!empty(data) ? '&' : '') + data);
            }
          }
        }
      }
    });
  },
  initCustomScrollCustomList: function () {
    $('#js_core_messages_custom_list_load_more').not('.mCustomScrollbar').mCustomScrollbar({
      autoHideScrollbar: true,
      theme: 'minimal',
      mouseWheel: {preventDefault: true},
      callbacks: {
        onScroll: function () {
          if (this.mcs.topPct == 100) {
            var canLoadMore = parseInt($('#js_check_load_more').val());
            if (coreMessages.bContinueLoadMore && canLoadMore) {
              coreMessages.bContinueLoadMore = false;
              $('#js_core_messages_custom_list_content').append('<div class="mt-1 text-center js_core_messages_load_more_icon">\n' + '<i class="fa fa-spin fa-circle-o-notch"></i>\n' + '</div>');
              var page = $('#js_load_more_page').val();
              var data = decodeURI($('.js_core_messages_custom_list #js_form_custom_list_search').serialize());
              $.ajaxCall('mail.loadMoreCustomList', 'page=' + page + (!empty(data) ? '&' : '') + data);
            }
          }
        }
      }
    });
  },
  initConversationContentCustomScroll: function () {
    let mScrollPreventDefault = true;
    if (window.matchMedia('(max-width: 767px)').matches) {
      mScrollPreventDefault = false;
    }
    $('.js_mail_messages_content').not('.mCustomScrollbar').mCustomScrollbar({
      autoHideScrollbar: false,
      theme: 'minimal',
      mouseWheel: {
        preventDefault: mScrollPreventDefault,
      },
      setTop: "-999999px",
      callbacks: {
        onScroll: function () {
          if (this.mcs.topPct == 0) {
            var canLoadMore = parseInt($(this).find('#js_check_load_more_conversation_content').val());
            var sId = $(this).attr('id');
            if (coreMessages.bContinueLoadMoreConversationContent && canLoadMore) {
              coreMessages.bContinueLoadMoreConversationContent = false;
              var oLastMailThread = $(this).find('.mail_thread_holder:first');
              var iUserId = 0;
              var iId = 0;
              var sDate = '';
              var iType = 0;
              if (oLastMailThread.length) {
                iUserId = oLastMailThread.data('user');
                iId = oLastMailThread.data('id');
                sDate = oLastMailThread.data('date');
                iType = oLastMailThread.hasClass('is_first_message') ? 1 : 2;

              }

              $(this).prepend('<div class="mt-1 text-center js_core_messages_load_more_icon">\n' + '<i class="fa fa-spin fa-circle-o-notch"></i>\n' + '</div>');
              var offset = $(this).find('.mCSB_container').find('.mail_thread_holder').length;
              var iThreadId = $(this).data('id');
              $.ajaxCall('mail.viewMoreThreadMail', 'offset=' + offset + '&thread_id=' + iThreadId + '&object_id=' + sId + '&user_id=' + iUserId + '&message_id=' + iId + '&date=' + sDate + '&type=' + iType);
            }
          }
        }
      },
      advanced: {
        updateOnContentResize: true
      }
    });
  },
  loadMailThread: function () {
    $('.js_item_click').off('click').on('click', function () {
      var thread_id = $(this).data('id');
      var view = $(this).data('view');
      $.ajaxCall('mail.loadThreadController', 'thread_id=' + thread_id + '&view=' + view);
      coreMessages.renewSelectedConversation(thread_id);
      coreMessageScreen.checkScreenForMobile();
      coreMessagesHelper.addTargetBlank();
      return false;
    });
    coreMessagesCustomConversationMassActions.checkSelect();
  },
  onEnterSubmit: function () {
    $('.on_enter_submit').off('keydown');
    $('#js_send_message_btn').off('click').click(function () {
      window.onbeforeunload = null;
      var $this = $(this);

      var $parent = $this.closest('#js_compose_new_message');
      var $form = $('.' + $parent.data('form'));
      $form.find('.js_attachment:first').val($parent.find('.js_attachment:first').val());
      var value = $parent.find('#js_compose_message_textarea').val();
      value = $.trim(value);

      var bTo = false;
      var bMessage = false;
      var bAttachment = false;
      var bCustomList = false;

      $form.find('#message').val(value);

      var aFormData = $form.serializeArray();
      $.each(aFormData, function (index, value) {
        var name = value.name;
        var posTo = parseInt(name.search(/val\[to\]/));
        var posMessage = parseInt(name.search(/val\[message\]/));
        var posAttachment = parseInt(name.search(/val\[attachment\]/));
        var postCustomList = parseInt(name.search(/val\[customlist\]/));

        if (posTo != -1 && value.value != '') {
          bTo = true;
        }
        if (posMessage != -1 && value.value != '') {
          bMessage = true;
        }
        if (posAttachment != -1 && value.value != '') {
          bAttachment = true;
        }
        if (postCustomList != -1 && value.value != '') {
          bCustomList = true;
        }
      });

      // user must enter captcha for send message
      if ($('.captcha_holder', $form).length > 0) {
        if ($('#image_verification').val() == '') {
          return false;
        }
      }

      $form.trigger('submit');
      if (((bTo == true) && (bAttachment == true || bMessage == true)) || ((bCustomList == true) && (bAttachment == true || bMessage == true))) {
        $form.find('#js_compose_message_textarea').val('');
        $parent.find('#js_compose_message_textarea').val('');
        $('.js_attachment', $form).val('');
      }
      return false;

    });
    if ($('#js_compose_new_message').length) {
      if (($('#js_compose_message_textarea').val().length) || ($('#js_compose_new_message').find('.attachment-row').length)) {
        $('#js_send_message_btn').removeClass('button_not_active');
      } else {
        $('#js_send_message_btn').addClass('button_not_active');
      }
    }

    $('#js_compose_message_textarea').on('input', function () {
      if ($(this).val().length || $('#js_compose_new_message .attachment-row').length) {
        $('#js_send_message_btn').removeClass('button_not_active');
      } else {
        $('#js_send_message_btn').addClass('button_not_active');
      }
    });
    $('#js_compose_new_message .js_attachment_add_inline a').off('click').on('click', function () {
      if ($('#js_send_message_btn').hasClass('button_not_active')) {
        $('#js_send_message_btn').removeClass('button_not_active');
      }
    });
    $('#js_compose_new_message .js_attachment_remove_inline a').off('click').on('click', function () {
      if (!$('#js_send_message_btn').hasClass('button_not_active') && !$('#js_compose_message_textarea').val().length) {
        $('#js_send_message_btn').addClass('button_not_active');
      }
    });
  },
  composeMailThread: function () {
    $('.js_ajax_compose_message').off('submit').submit(function () {
      var iCount = 0;
      var aData = $(this).serializeArray();

      $(aData).each(function (index, value) {
        if (parseInt(value.name.search(/val\[to\]/)) != -1 && !empty(value.value)) {
          iCount++;
        }
      });

      var iLimit = parseInt($('#js_check_numbers_member_for_group').val());
      if (iCount > iLimit) {
        $('#js_ajax_compose_error_message').html('<div class="error_message ">' + oTranslations['mail_number_of_members_over_limitation'].replace('{number}', iLimit) + '</div>');
      } else {
        if ($(this).parents('#js_core_messages_content_title').length) {
          var data = $(this).serialize();
          $.ajaxCall('mail.composeMessageWithAjax', data);
        } else if ($(this).closest('.js_box_content').length > 0) {
          var sType = $(this).data('type');
          var sParams = 'ajax_popup=1';
          if (!empty(sType)) {
            sParams += '&type=' + sType;
          }
          $Core.processForm('#js_mail_compose_submit');
          $(this).ajaxCall('mail.composeProcess', sParams);
        }
        coreMessages.search.reset();
      }

      return false;
    });
    coreMessages.onEnterSubmit();
    coreMessages.customEmojiPopup();
  },
  changeGroupTitle: function () {
    $('.js_group_conversation_title').click(
      function () {
        if (!$(this).find('.js_group_conversation_title_text:first').hasClass('hide_it')) {
          $(this).find('.js_group_conversation_title_text:first').addClass('hide_it');
          $(this).find('.js_group_conversation_title_change:first').show().trigger('focus');
        }
      });
    $('.js_group_conversation_title_change').blur(
      function () {
        if ($('.js_group_conversation_title_text').hasClass('hide_it')) {
          var sNewTitle = $(this).val();
          var iThreadId = $(this).closest('.js_group_conversation_title').data('id');
          $(this).ajaxCall('mail.changeGroupTitle', 'val[thread_id]=' + iThreadId + '&val[title]=' + encodeURIComponent(sNewTitle));
          $('.js_group_conversation_title_text').removeClass('hide_it').html(sNewTitle);
          $(this).hide();
        }
      });
  },
  filterConversationByName: function () {
    $('.core-messages-index-js .js_filter_default_folder').on('click', function () {
      var sView = $(this).data('view');
      $('#js_search_view').val(sView);
      $('#js_search_custom').val('');
      $('#js_message_filter').trigger('submit');
    });
    $('.core-messages-index-js .js_filter_custom_list').on('click', function () {
      var iId = $(this).data('id');
      $('#js_search_view').val('');
      $('#js_search_custom').val(iId);
      $('#js_message_filter').trigger('submit');
    });
    $('.core-messages-index-js #js_search_title').off('keydown').keydown(function (e) {
      if (e.which == 13) {
        if ($(this).val().length) {
          $(this).closest('form').trigger('submit');
        }
      }
    });

  },
  BackToList: function () {
    $('#back-to-list-js').off('click').on('click', function () {
      $(this).parents('.core-messages').removeClass('active-messages');
    });
  },
  initCustomListItemAction: function () {
    $('.js_core_messages_custom_list .js_customlist_item_click').off('click').click(function () {
      var iId = $(this).data('id');
      $.ajaxCall('mail.loadEditCustomList', 'folder_id=' + iId);
      $('.js_core_messages_custom_list .js_customlist_item_click').removeClass('is_selected_thread');
      $(this).addClass('is_selected_thread');
      coreMessageScreen.checkScreenForMobile();
    });
    coreMessagesCustomListAction.checkSelect();
  },
  customListAction: function () {
    $('#js_btn_submit').off('click').click(function () {
      var form = $(this).closest('form');
      var aFormData = form.serializeArray();
      var iCheckName = 0;
      var iCheckUsers = 0;
      var iCheckEdit = 0;
      var iCustomListMaximum = parseInt($('#js_setting_custom_list_maximum').val());
      var iMemberCustomListMaximum = parseInt($('#js_setting_custom_list_member_maximum').val());
      var iCurrentCustomListUser = parseInt($('#js_setting_current_customlist_user').val());

      $.each(aFormData, function (index, value) {
        if (parseInt(value.name.search(/val\[name\]/)) != -1 && value.value != "") {
          if (coreMessagesHelper.strip_tags(value.value)) {
            iCheckName++;
          }

        }
        if (parseInt(value.name.search(/val\[invite\]/)) != -1 && value.value != "") {
          iCheckUsers++;
        }
        if (parseInt(value.name.search(/id/)) != -1 && value.value != "") {
          iCheckEdit++;
        }
      });

      var sError = '';
      if (iCheckName == 0 && iCheckEdit == 0) {
        sError += '<div class="error_message ">' + oTranslations['mail_invalid_name'] + '</div>';
      }
      if (iCheckUsers == 0) {
        sError += '<div class="error_message ">' + oTranslations['mail_friend_list_cannot_be_null'] + '</div>';
      }

      if (iCheckUsers > iMemberCustomListMaximum) {
        sError += '<div class="error_message ">' + oTranslations['mail_limitation_custom_list_members'].replace('{number}', iMemberCustomListMaximum) + '</div>';
        if (iCheckEdit) {
          $('.js_global_item_moderate', $('.js_core_messages_add_customlist')).each(function () {
            $(this).prop('checked', false);
            $(this).closest('.item-outer').removeClass('active');
            $Core.searchFriend.addFriendToSelectList($("#js_friends_checkbox_" + $(this).val()), $(this).val());
          });

          coreMessages.aSelectedCustomlistUsers = {};
          coreMessages.initMembersCustomList();
        }
      }

      if (sError.length > 0) {
        if (!$('#js_content').hasClass('pt-2')) {
          $('#js_content').addClass('pt-2');
        }
        $('.js_core_messages_custom_list #js_content .js_core_messages_add_customlist .error-list').html(sError);
      } else {
        if (iCheckEdit == 0 && (iCurrentCustomListUser + 1 > iCustomListMaximum)) {
          sError = '<div class="error_message ">' + oTranslations['mail_cannot_create_more_custom_list_because_of_limitation'].replace('{number}', iCustomListMaximum) + '</div>';
          $('.js_core_messages_custom_list #js_content .js_core_messages_add_customlist .error-list').html(sError);
        } else {
          form.trigger('submit');
        }
      }
      return false;
    });
    $('.js_core_messages_custom_list #js_search_name').off('keydown').keydown(function (e) {
      if (e.which == 13) {
        $(this).closest('form').trigger('submit');
      }
    });

    $('#js_check_all_custom_list').off('click').click(function () {
      $('.js_custom_item_check').prop('checked', $(this).prop('checked'));
      if ($(this).prop('checked')) {
        $('#js_core_messages_customlist_actions').removeClass('hidden');
      }
      else {
        $('#js_core_messages_customlist_actions').addClass('hidden');
      }
    });

    $('.js_custom_list_mass_action').off('click').click(function () {
      var action = $(this).data('action');
      var sIds = '';
      $('.js_custom_item_check:checked').each(function () {
        sIds += $(this).val() + ',';
      });
      sIds = trim(sIds, ',');
      if (!empty(sIds)) {
        $.ajaxCall('mail.customlist', 'action=' + action + '&list_id=' + sIds);
      }
    });
  },
  changeCustomListTitle: function () {
    $('.core-messages-customlist-title-block #js_message_customlist_title').on('click', function () {
      var sTitle = $(this).parent().find('input[name=title]').val();
      var iFolder = $(this).parent().data('id');
      $(this).ajaxCall('mail.changeCustomListTitle', 'val[folder_id]=' + iFolder + '&val[title]=' + encodeURIComponent(sTitle));
    });

    $('.js_custom_list_title').off('click').click(
      function () {
        var iId = $(this).data('id');
        if (!$('.js_customlist_name_' + iId).hasClass('hide_it')) {
          $(this).find('.js_customlist_name_' + iId).addClass('hide_it');
          $(this).find('.js_custom_list_title_change:first').show().trigger('focus');
        }
      });
    $('.js_custom_list_title_change').blur(
      function () {
        var iId = $(this).closest('.js_custom_list_title').data('id');
        if ($('.js_customlist_name_' + iId).hasClass('hide_it')) {
          var sNewTitle = $(this).val();
          var sOldTitle = $('.js_customlist_name_' + iId).html();
          $(this).ajaxCall('mail.changeCustomListTitle', 'val[folder_id]=' + iId + '&val[title]=' + encodeURIComponent(sNewTitle) + '&val[old_title]=' + encodeURIComponent(sOldTitle));
        }
      });
  },
  search: {
    iSelectedUserCount: 0,
    iSelectedCustomListCount: 0,
    aFoundUsers: {},
    aFoundCustomList: {},
    aUsers: {},
    aCustomList: {},
    buildList: function () {
      $.ajaxCall('mail.buildList');
    },
    getSearch: function (obj) {
      var oObject = $(obj);
      var sSearch = oObject.val();
      var sFriendTitle = $('#js_core_messages_compose_message #js_compose_message_friend_title').val();
      var sCustomlistTitle = $('#js_core_messages_compose_message #js_compose_message_custom_list_title').val();
      if (sSearch.length) {
        if (typeof coreMessages.search.aUsersBuild !== 'undefined') {
          var aTempUsers = JSON.parse(JSON.stringify(coreMessages.search.aUsersBuild));
        }
        if (typeof coreMessages.search.aCustomListBuild !== 'undefined') {
          var aTempCustomList = JSON.parse(JSON.stringify(coreMessages.search.aCustomListBuild));
        }
        var sUserHtml = '';
        var iUserCount = 0;
        if (this.iSelectedCustomListCount == 0) {
          var sUserTemp = '<ul>';

          $(aTempUsers).each(function (sKey, aUser) {
            if ((aUser['user_image'].substr(0, 5) == 'http:') ||
              (aUser['user_image'].substr(0, 6) == 'https:')) {
              aUser['user_image'] = '<img src="' + aUser['user_image'] + '">';
            }
            var $mRegSearch = new RegExp(sSearch, 'i');
            var bExist = coreMessages.search.checkFoundUser(aUser.user_id);
            if (aUser['full_name'].match($mRegSearch) && !bExist) {
              iUserCount++;
              sUserTemp += '<li><div rel="' + aUser['user_id'] +
                '" class="js_core_messages_search_friend ' +
                '" onclick="coreMessages.search.processClick(' + aUser['user_id'] + ', \'user' +
                '\');"><span class="image">' +
                aUser['user_image'] + '</span><span class="user">' +
                aUser['full_name'] + ' (' + sFriendTitle + ')</span></div></li>';
              coreMessages.search.addUser(aUser['user_id'], aUser);
            }
          });
          sUserTemp += '</ul>';
          sUserHtml += sUserTemp;
        }


        var iCustomListCount = 0;
        var sCustomListHtml = '';
        if (this.iSelectedUserCount == 0) {
          if (this.iSelectedCustomListCount == 0) {
            var sCustomListTemp = '<ul>';

            $(aTempCustomList).each(function (sKey, aCustom) {
              var $mRegSearch = new RegExp(sSearch, 'i');
              var bExist = coreMessages.search.checkFoundCustomList(aCustom['folder_id']);
              if (aCustom['name'].match($mRegSearch) && !bExist) {
                iCustomListCount++;
                sCustomListTemp += '<li><div rel="' + aCustom['folder_id'] +
                  '" class="js_core_messages_search_custom_list ' +
                  '" onclick="coreMessages.search.processClick(' + aCustom['folder_id'] + ', \'customlist' +
                  '\');"><span class="image"><i class="fa fa-users" aria-hidden="true"></i></span><span class="customlist">' +
                  aCustom['name'] + '</span></div></li>';
                coreMessages.search.addCustom(aCustom['folder_id'], aCustom);
              }
            });
            sCustomListHtml += sCustomListTemp;
          }
        }

        if (iUserCount > 0 || iCustomListCount > 0) {
          $('.js_core_messages_search_list').show().html(sUserHtml + sCustomListHtml);
        }

        if (iUserCount == 0 && iCustomListCount == 0) {
          $('.js_core_messages_search_list').hide().html('');
        }
      } else {
        $('.js_core_messages_search_list').hide().html('');
      }
    },
    rememberSelectedUser: function (iId) {
      this.aFoundUsers[iId] = 1;
    },
    rememberSelectedCustomList: function (iId) {
      this.aFoundCustomList[iId] = 1;
    },
    removeSelectedUser: function (obj, iId) {
      if (!empty(this.aFoundUsers)) {
        $(obj).closest('.item-search-friend-selected').remove();
        this.iSelectedUserCount--;
        delete this.aFoundUsers[iId];
      }
    },
    removeSelectedCustomList: function (obj, iId) {
      if (!empty(this.aFoundCustomList)) {
        $(obj).closest('.item-search-custom-list-selected').remove();
        this.iSelectedCustomListCount--;
        delete this.aFoundCustomList[iId];
      }
    },
    addUser: function (iId, aData) {
      this.aUsers[iId] = aData;
    },
    addCustom: function (iId, aData) {
      this.aCustomList[iId] = aData;
    },
    checkFoundUser: function (iId) {
      return this.aFoundUsers[iId];
    },
    checkFoundCustomList: function (iId) {
      return this.aFoundCustomList[iId];
    },
    processClick: function (iId, type) {
      if (type == "user") {
        var aUser = this.aUsers[iId];
        var sHtml = '<span id="js_item_search_friend_' + aUser['user_id'] + '" class="item-search-friend-selected"><span class="item-search-friend-name">' + aUser['full_name'] + '</span><a role="button" class="item-search-friend-remove" title="Remove" onclick="coreMessages.search.removeSelectedUser(this, ' + aUser['user_id'] + ');"><i class="ico ico-close"></i></a><input type="hidden" value="' + aUser['user_id'] + '" name="val[to][]"> ' + '</span>';
        $('#js_core_messages_custom_search_friend_placement').prepend(sHtml);
        coreMessages.search.rememberSelectedUser(iId);
        this.iSelectedUserCount++;
      } else {
        var aCustom = this.aCustomList[iId];
        var sHtml = '<span id="js_item_search_custom_list_' + aCustom['folder_id'] + '" class="item-search-custom-list-selected"><span class="item-search-custom-list-name">' + aCustom['name'] + '</span><a role="button" class="item-search-custom-list-remove" title="Remove" onclick="coreMessages.search.removeSelectedCustomList(this, ' + aCustom['folder_id'] + ');"><i class="ico ico-close"></i></a><input type="hidden" value="' + aCustom['folder_id'] + '" name="val[customlist][]"> ' + '</span>';
        $('#js_core_messages_custom_search_friend_placement').prepend(sHtml);
        coreMessages.search.rememberSelectedCustomList(iId);
        this.iSelectedCustomListCount++;
      }
      $('#js_core_messages_search').val('');
      $('.js_core_messages_search_list').hide().html('');
    },
    reset: function () {
      this.iSelectedUserCount = 0;
      this.iSelectedCustomListCount = 0;
      this.aUsers = {};
      this.aCustomList = {};
      this.aFoundUsers = {};
      this.aFoundCustomList = {};
    }
  },
  resetMemberCustomList: function() {
    if (typeof $Core !== "undefined") {
      $Core.searchFriend.showOverflow = false;
      $Core.searchFriend.itemCount = 0;
    }
    coreMessages.aSelectedCustomlistUsers = {};
  },
  initMembersCustomList: function () {
    var container = $('#js_friend_loader');
    container.imagesLoaded(function () {
      if (!empty(aCustomlistMembers) && Array.isArray(aCustomlistMembers)) {
        $Core.searchFriend.showOverflow = true;
        $(aCustomlistMembers).each(function (index, value) {
          if (!coreMessages.aSelectedCustomlistUsers[value['user_id']]) {
            var oObject = $("#js_friends_checkbox_" + value['user_id']);
            if (oObject.length) {
              oObject.prop("checked", true);
              oObject.closest('.item-outer').addClass('active');
              $Core.searchFriend.addFriendToSelectList($("#js_friends_checkbox_" + value['user_id']), value['user_id'], $("#js_friends_checkbox_" + value['user_id']).prop('checked'));
            }
            else {
              coreMessages.cloneSelectedUser(value);
            }
            coreMessages.aSelectedCustomlistUsers[value['user_id']] = 1;
          }
        });
        $('.js_core_messages_add_customlist').on('click', '#selected_friends_list li', function () {
          let user_id = $(this).data('id');
          let parent = $('.js_core_messages_add_customlist');
          if ($('[data-id=' + user_id + ']', $('#selected_friends_list')).length) {
            if (function_exists('plugin_removeFriendToSelectList')) {
              plugin_removeFriendToSelectList(user_id);
            }
            $('.js_cached_friend_id_' + user_id, parent).remove();
            if ($('#js_friend_input_' + user_id).length) {
              $('#js_friend_input_' + user_id).remove();
            }
            var checkbox = $("#js_friends_checkbox_" + user_id);
            if (checkbox.length) {
              checkbox.prop("checked", false);
              checkbox.closest('.item-outer').removeClass('active');
            }
            $('[data-id=' + user_id + ']', $('#selected_friends_list')).remove();
            var selectedCount = $('#selected_friends_list li', parent).length - 2;
            $('#deselect_all_friends span', parent).html(selectedCount);
            selectedCount === 0 && $('#deselect_all_friends', parent).addClass('hide');
            $('#selected_friends_list li', parent).length === 1 && $('#deselect_all_friends', parent).addClass('hide');
            $Core.searchFriend.itemCount--;
          }
        });
      }
    });

  },
  cloneSelectedUser: function (value) {
    let parent = $('.js_core_messages_add_customlist');
    let selectedFriend = $('#selected_friend_template', parent).clone();
    selectedFriend.data('id', value['user_id']);
    let count = $('.js_cached_friend_name').length;
    $('#js_selected_friends', parent).append('<div class="js_cached_friend_name row1 js_cached_friend_id_' +
      value['user_id'] + '' + (count ? '' : ' row_first') +
      '"><span style="display:none;">' + value['user_id'] +
      '</span><input type="hidden" name="val[' + $Core.searchFriend.sPrivacyInputName +
      '][]" value="' + value['user_id'] +
      '" /><a role="button" onclick="$(\'#search-friend-' + value['user_id'] + '\').trigger(\'click\')"></a> ' +
      value['full_name'] + '</div>');
    $('.img-wrapper', selectedFriend).prepend($Core.b64DecodeUnicode(value['user_image']));
    selectedFriend.removeAttr('id');
    selectedFriend.attr('data-id', value['user_id']);
    selectedFriend.removeClass('hide');
    $('#selected_friends_list', parent).append(selectedFriend);
    $('#deselect_all_friends', parent).removeClass('hide');
    $('#deselect_all_friends span', parent).html($('#selected_friends_list li', parent).length - 2);
    $Core.searchFriend.itemCount++;
  },
  markConversation: function () {
    $('.js_mail_mark_unread_action').off('click').click(function (e) {
      e.stopPropagation();
      var iId = $(this).closest('.js_item_click').data('id');
      $.ajaxCall('mail.toggleRead', 'id=' + iId, 'GET');
      $(this).parent().addClass('hidden');
      $(this).closest('.core-messages__list-time').find('.js_mail_mark_read').removeClass('hidden');
      $(this).closest('.mail_holder').addClass('mail_is_new');
      return false;
    });

    $('.js_mail_mark_read_action').off('click').click(function (e) {
      e.stopPropagation();
      var iId = $(this).closest('.js_item_click').data('id');
      $.ajaxCall('mail.toggleRead', 'id=' + iId, 'GET');
      $(this).parent().addClass('hidden');
      $(this).closest('.core-messages__list-time').find('.js_mail_mark_unread').removeClass('hidden');
      $(this).closest('.mail_holder').removeClass('mail_is_new');
      return false;
    });
  },
  renewUnreadMessagesPanelCount: function () {
    var iUnreadMessagesCount = parseInt($("#js_total_new_messages").html());
    if (iUnreadMessagesCount == 1) {
      $("#js_total_unread_messages").html("").hide();
      $("#js_total_new_messages").html("").hide();
    } else if (iUnreadMessagesCount > 1) {
      $("#js_total_unread_messages").html('(' + (iUnreadMessagesCount - 1) + ' unread)');
      $("#js_total_new_messages").html(iUnreadMessagesCount - 1);
    } else {
      $("#js_total_unread_messages").html("").hide();
      $("#js_total_new_messages").html("").hide();
    }
  },
  renewSelectedConversation: function (iId) {
    var oConversation = $('#js_message_' + iId);
    if (oConversation.length) {
      $('.mail_holder').removeClass('is_selected_thread');
      oConversation.closest('.mail_holder').removeClass('mail_is_new').addClass('is_selected_thread');
      oConversation.find('.js_mail_mark_read').addClass('hidden');
      oConversation.find('.js_mail_mark_unread').removeClass('hidden');
    }
    var oPanelConversation = $('#js_panel_item_' + iId);
    if (oPanelConversation.length) {
      oPanelConversation.closest('.panel-item').removeClass('is_new').find('.message-unread:first').removeClass('is_new');
      oPanelConversation.closest('.panel-item').find('.notification-delete:first').removeClass('is_new');
      oPanelConversation.removeClass('is_new');
    }
  },
  processDeleteAttachment: function () {
    $('#js_main_mail_thread_holder .attachment-row:not(".rebuilt")').each(function () {
      var sId = $(this).attr('id');
      var iId = parseInt(sId.split('_')[3]);
      var oAttachmentRowLink = $(this).find('.attachment_row_link:first');
      var sAttachmentName = encodeURIComponent(oAttachmentRowLink.html());
      if (Number.isInteger(iId)) {
        var oMessage = $(this).closest('.mail_thread_holder');
        var iMessageId = parseInt(oMessage.data('id'));
        var params = "attachment_id=" + iId + "&attachment_name=" + sAttachmentName + "&message_id=" + iMessageId;
        if (oMessage.hasClass('is_first_message')) {

          if (oMessage.next('#mail_threaded_new_message').length) {
            var oNextMessage = oMessage.next('#mail_threaded_new_message').find('.mail_thread_holder:first');
          } else {
            var oNextMessage = oMessage.next('.mail_thread_holder');
          }

          if (oNextMessage.length && (oNextMessage.data('date') === oMessage.data('date'))) {
            params += '&next_message_id=' + oNextMessage.data('id');
          }
        } else if (oMessage.hasClass('is_last_message')) {
          if (oMessage.closest('#mail_threaded_new_message').length && oMessage.closest('#mail_threaded_new_message').find('.mail_thread_holder').length == 1) {
            var oPreviousMessage = oMessage.closest('#mail_threaded_new_message').prev('.mail_thread_holder');
          } else {
            var oPreviousMessage = oMessage.prev('.mail_thread_holder');
          }

          if (oPreviousMessage.length && (oPreviousMessage.data('date') === oMessage.data('date'))) {
            params += '&previous_message_id=' + oPreviousMessage.data('id');
          }
        }
        var sFunction = "$Core.jsConfirm({},function(){  $.ajaxCall('mail.deleteAttachmentText','" + params + "');},function(){}); return false;";
        $(this).find('.attachment-row-actions:first').find('a:first').attr('onclick', sFunction);
      }
      $(this).addClass('rebuilt');
    });
  },
  processAttachmentCannotDelete: function () {
    $('.attachment-row', $('#js_main_mail_thread_holder')).each(function () {
      var oActions = $(this).find('.attachment-row-actions:first');
      if (!oActions.find('a:first').length) {
        if (!$(this).find('.attachment-body').hasClass('no_delete_action')) {
          $(this).find('.attachment-body').addClass('no_delete_action');
        }
      }
    });
  }

};

var coreMessagesCustomConversationMassActions = {
  init: function () {
    coreMessagesCustomConversationMassActions.initCheckAll();
    coreMessagesCustomConversationMassActions.initAction();
  },
  initCheckAll: function () {
    $('#js_select_all_conversation').off('click').click(function () {
      $('.js_conversation_item_check').prop('checked', $(this).prop('checked'));
      if ($(this).prop('checked')) {
        $('#js_core_messages_conversation_select_action').removeClass('hidden');
      }
      else {
        $('#js_core_messages_conversation_select_action').addClass('hidden');
      }
    });
  },
  initAction: function () {
    $('.js_conversation_mass_actions').off('click').click(function (e) {
      var holder = $('#js_mass_action_ids');
      holder.html('');
      $('.js_conversation_item_check:checked').each(function (index, value) {
        $('<input type="hidden" name="conversation_action[]" class="js_conversation_item_check"/>').val($(value).val()).appendTo(holder);
      })
      var action = $(this).data('action');
      if (action == "export") {
        $('#js_core_messages_form_mass_actions').submit();
      } else if (action == "delete") {
        $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
          $('#js_core_messages_form_mass_actions').ajaxCall('mail.archive', 'action=' + action);
        }, function () {
          return false;
        });
      } else {
        $('#js_core_messages_form_mass_actions').ajaxCall('mail.archive', 'action=' + action);
      }
      e.preventDefault();
      return false;
    });
  },
  checkSelect: function () {
    $('.js_conversation_item_check').off('click').click(function (e) {
      if ($('.js_conversation_item_check:checked').length === $('.js_conversation_item_check').length) {
        $('#js_select_all_conversation').prop('checked', true);
      } else if ($('.js_conversation_item_check:checked').length === 0 || ($('.js_conversation_item_check:checked').length < $('.js_conversation_item_check').length)) {
        $('#js_select_all_conversation').prop('checked', false);
      }
      if ($('.js_conversation_item_check:checked').length > 0) {
        $('#js_core_messages_conversation_select_action').removeClass('hidden');
      }
      else {
        $('#js_core_messages_conversation_select_action').addClass('hidden');
      }
    });
  }
}

var coreMessagesCustomAttachment = {
  bCheckInitEventClick: false,
  bIsUploadPhoto: false,
  initAttachmentHolder: function () {
    $('#attachment-dropzone_js_compose_message_textarea').data('on-sending', 'coreMessagesCustomAttachment.initUploadAttachment').data('on-success', 'coreMessagesCustomAttachment.onSuccessUploadAttachment');
    $('#js_compose_new_message').find('.global_attachment_manage a:first').attr('onclick', 'coreMessagesCustomAttachment.toggleAttachmentHolder(this);');
    $('.attachment-close').attr('onclick', 'coreMessagesCustomAttachment.toggleAttachmentHolder(this);');
    $('.attachment-delete-all').attr('onclick', 'coreMessagesCustomAttachment.deleteAll(this);');
    $('.js_global_position_photo').attr('onclick', 'coreMessagesCustomAttachment.attachPhoto(this);');
  },
  initUploadAttachment: function (data, xhr, formData, ele) {
    if (this.bIsUploadPhoto) {
      $('[name="custom_attachment"]', $('#js_compose_new_message')).val('photo');
    }
    $('#attachment_params', ele.closest('#js_compose_new_message')).find('input').each(function () {
      formData.append($(this).prop('name'), $(this).val());
    });
  },
  toggleAttachmentHolder: function (t) {
    t = $(t);
    let editor = t.closest('#js_compose_new_message'),
      oAttachmentHolder = editor.find('.attachment-holder:first'),
      isUsingUpload = false;
    if (oAttachmentHolder.find('.attachment-form-holder').length) {
      oAttachmentHolder.removeClass('js_attachment_holder');
      oAttachmentHolder.find('.attachment-form-holder').addClass('js_attachment_holder');
      oAttachmentHolder.find('.attachment-form-holder').data('element-id', 'js_compose_message_textarea');
      oAttachmentHolder.find('.attachment-form-holder').prependTo(editor);
      isUsingUpload = true;
    } else {
      oAttachmentHolder.addClass('js_attachment_holder');
      editor.find('.attachment-form-holder').removeClass('js_attachment_holder');
      editor.find('.attachment-form-holder').data('element-id', '');
      editor.find('.attachment-form-holder').prependTo(oAttachmentHolder);
    }
    if (isUsingUpload) {
      $('.attachment-form-holder', editor).slideToggle();
    } else {
      $('.attachment-form-holder', editor).hide();
    }
    $('.global_attachment', editor).toggleClass('attachment-form-open');
    return false;
  },
  onSuccessUploadAttachment: function (ele, file, response) {
    eval(response);
    var attachmentCounter = $('#js_compose_new_message .attachment-counter'),
      counter = attachmentCounter.html(),
      number = parseInt(counter.substr(1, counter.length - 1));
    if (number === 1) {
      $('#js_compose_new_message .attachment-delete-all').removeClass('hide');
    }
    $Core.Attachment.resetForm('js_compose_new_message', 'js_compose_message_textarea');
    $('[name="custom_attachment"]', $('#js_compose_new_message')).val('');
    var inputBox = $('textarea#js_compose_message_textarea');
    if (inputBox.length) {
      inputBox.trigger('input');
      if (typeof inputBox[0].selectionStart != 'undefined') {
        inputBox[0].selectionStart = inputBox.val().length;
        inputBox[0].focus();
      }
    }
    this.bIsUploadPhoto = false;
    var oParent = $(ele).closest('#js_compose_new_message');
    coreMessagesCustomAttachment.deleteAttachment();
    if (oParent.find('#js_send_message_btn').hasClass('button_not_active')) {
      oParent.find('#js_send_message_btn').removeClass('button_not_active');
    }
  },
  deleteAll: function (ele) {
    var th = $(ele),
      editorHolder = $('#js_compose_new_message'),
      attachments = $('.attachment-row', editorHolder),
      textarea = $('textarea', editorHolder);

    $Core.jsConfirm({}, function () {
      attachments.each(function (key, attachment) {
        $.ajaxCall('attachment.delete', $.param({
          id: $(attachment).prop('id').replace('js_attachment_id_', ''),
          editorHolderId: editorHolder.attr('id')
        }));
      });
      // empty counter
      $('.attachment-counter', editorHolder).empty();
      $('.attachment-delete-all', editorHolder).addClass('hide');
      $('.no-attachment', editorHolder).removeClass('hide');

      var attachmentUploader = $Core.dropzone.instance['attachment_' + textarea.attr('id')];
      if (typeof attachmentUploader !== 'undefined') {
        attachmentUploader.removeAllFiles();
      }

      setInterval(function () {
        if (!$('.attachment-row', $('#js_compose_new_message')).length) {
          editorHolder.find('#js_compose_message_textarea').trigger('input');
          return false;
        }
      }, 1000);

    }, function () {
    })
  },
  attachPhoto: function (ele) {
    var holder = $(ele).closest('#js_compose_new_message');
    this.bIsUploadPhoto = true;
    $('.dropzone-button-attachment', holder).trigger('click');
    $('[name="custom_attachment"]', holder).val('photo');
  },
  deleteAttachment: function () {
    $('#js_compose_new_message .attachment-row:not(".rebuilt")').each(function () {
      var sId = $(this).attr('id');
      var iId = parseInt(sId.split('_')[3]);
      if (iId > 0) {
        var sFunction = "coreMessagesCustomAttachment.removeInlineWithDelete(" + iId + ");";
        $(this).find('.js_attachment_add_inline:first').next().attr('onclick', sFunction);
      }

      $(this).find('.js_attachment_remove_inline').find('a:first').off('click').click(function () {
        $('#js_compose_message_textarea').trigger('input');
      });

      $(this).addClass('rebuilt');
    });
  },
  checkAttachment: function () {
    if (!$('#js_compose_new_message .attachment-row').length) {
      $('#js_compose_new_message').find('#js_compose_message_textarea').trigger('input');
      $('#js_compose_new_message .attachment-delete-all').addClass('hide');
    }
  },
  removeInlineWithDelete: function (iAttachmentId) {
    $Core.jsConfirm({}, function () {
        var oAttachmentRow = $('#js_attachment_id_' + iAttachmentId);
        var oRemoveInline = oAttachmentRow.find('.js_attachment_remove_inline:first').find('a:first');
        if (oRemoveInline) {
          var oDomRemoveInlineElement = oRemoveInline.get(0);
          if (oDomRemoveInlineElement) {
            var sAttachmentPath = oRemoveInline.data('inline-path');
            if (empty(sAttachmentPath)) {
              sAttachmentPath = iAttachmentId;
            }

            if (!empty(sAttachmentPath)) {
              $Core.Attachment.removeInline(oDomRemoveInlineElement, sAttachmentPath, 'js_compose_message_textarea');
            }

          }
        }
        $.ajaxCall('attachment.delete', 'id=' + iAttachmentId);
        setTimeout(function () {
          coreMessagesCustomAttachment.checkAttachment();
        }, 100);
      },
      function () {
      });
    return false;
  },
  detectInvalidFile: function () {
    var sDropzoneId = $('[data-component="dropzone"]', $('#js_compose_new_message')).data('dropzone-id');
    var oDropzone = $Core.dropzone.instance[sDropzoneId];
    if (oDropzone) {
      oDropzone.on('error', function (file, message) {
        $('.dz-attachment-upload-again', $('#js_compose_new_message')).off('click').click(function () {
          oDropzone.hiddenFileInput.click();
        });
      });
    }
  }
}

var coreMessagesAdmincp = {
  init: function () {
    coreMessagesAdmincp.searchMessagesByTime();
  },
  searchMessagesByTime: function () {
    $('.core-messages-admincp-messages #js_clear_data').on('click', function () {
      $('.core-messages-admincp-messages input[type="text"]').each(function () {
        $(this).val('');
      });
      $('#js_select_status').val('');
    });
    $('.core-messages-admincp-messages span.user_profile_link_span a').attr('target', '_blank');
    $('.core-messages-admincp-messages #js_select_all').on('click', function () {
      if ($(this).prop('checked')) {
        $('.core-messages-admincp-messages .js_select_message').prop('checked', true);
      } else {
        $('.core-messages-admincp-messages .js_select_message').prop('checked', false);
      }
      var iCount = 0;
      $('.core-messages-admincp-messages .js_select_message').each(function () {
        if ($(this).prop('checked')) {
          iCount++;
        }
      });
      $('.core-messages-admincp-messages .mass-actions .left .number-selected .number').html(iCount);
      if(iCount > 0) {
        $('.core-messages-admincp-messages #js_unselect_all').removeClass('disabled').removeProp('disabled');
        $('.core-messages-admincp-messages #js_delete_messages').removeClass('disabled').removeProp('disabled');
        $('.core-messages-admincp-messages #js_hidden_messages').removeClass('disabled').removeProp('disabled');
      }
      else {
        $('.core-messages-admincp-messages #js_unselect_all').addClass('disabled').prop('disabled', true);
        $('.core-messages-admincp-messages #js_delete_messages').addClass('disabled').prop('disabled', true);
        $('.core-messages-admincp-messages #js_hidden_messages').addClass('disabled').prop('disabled', true);
      }
    });
    $('.core-messages-admincp-messages .js_select_message').on('click', function () {
      var iCount = 0;
      $('.core-messages-admincp-messages .js_select_message').each(function () {
        if ($(this).prop('checked')) {
          iCount++;
        }
      });
      $('.core-messages-admincp-messages .mass-actions .left .number-selected .number').html(iCount);
      if(iCount > 0) {
        $('.core-messages-admincp-messages #js_unselect_all').removeClass('disabled').removeProp('disabled');
        $('.core-messages-admincp-messages #js_delete_messages').removeClass('disabled').removeProp('disabled');
        $('.core-messages-admincp-messages #js_hidden_messages').removeClass('disabled').removeProp('disabled');
      }
      else {
        $('.core-messages-admincp-messages #js_unselect_all').addClass('disabled').prop('disabled', true);
        $('.core-messages-admincp-messages #js_delete_messages').addClass('disabled').prop('disabled', true);
        $('.core-messages-admincp-messages #js_hidden_messages').addClass('disabled').prop('disabled', true);
      }
    });
    $('.core-messages-admincp-messages #js_unselect_all').on('click', function () {
      $('.core-messages-admincp-messages .js_select_message').prop('checked', false);
      $('.core-messages-admincp-messages .mass-actions .left .number-selected .number').html(0);
      $('.core-messages-admincp-messages #js_select_all').prop('checked', false);
      $('.core-messages-admincp-messages #js_unselect_all').addClass('disabled').prop('disabled', true);
      $('.core-messages-admincp-messages #js_delete_messages').addClass('disabled').prop('disabled', true);
      $('.core-messages-admincp-messages #js_hidden_messages').addClass('disabled').prop('disabled', true);
    });
    $('.core-messages-admincp-messages #js_delete_messages').off('click').on('click', function () {
      var sId = '';
      $('.core-messages-admincp-messages .js_select_message').each(function () {
        if ($(this).prop('checked')) {
          sId += $(this).val() + ',';
        }
      });
      $.ajaxCall('mail.actionMessagesMultiple', 'action=delete&data=' + sId + '&thread_id=' + $(this).data('id'));
    });
    $('.core-messages-admincp-messages #js_hidden_messages').off('click').on('click', function () {
      var sId = '';
      $('.core-messages-admincp-messages .js_select_message').each(function () {
        if ($(this).prop('checked')) {
          sId += $(this).val() + ',';
        }
      });
      $.ajaxCall('mail.actionMessagesMultiple', 'action=hide&data=' + sId + '&thread_id=' + $(this).data('id'));
    });
  },
}
var coreMessagesCustomListAction = {
  checkSelect: function () {
    $('.js_custom_item_check').off('click').click(function (e) {
      if ($('.js_custom_item_check:checked').length === $('.js_custom_item_check').length) {
        $('#js_check_all_custom_list').prop('checked', true);
      } else if ($('.js_custom_item_check:checked').length === 0 || ($('.js_custom_item_check:checked').length < $('.js_custom_item_check').length)) {
        $('#js_check_all_custom_list').prop('checked', false);
      }

      if ($('.js_custom_item_check:checked').length > 0) {
        $('#js_core_messages_customlist_actions').removeClass('hidden');
      }
      else {
        $('#js_core_messages_customlist_actions').addClass('hidden');
      }
    });
  }
}
var coreMessagesHelper = {
  strip_tags: function (str, allowed_tags) {
    var key = '',
      allowed = false;
    var matches = [];
    var allowed_array = [];
    var allowed_tag = '';
    var i = 0;
    var k = '';
    var html = '';

    var replacer = function (search, replace, str) {
      return str.split(search).join(replace);
    };
    // Build allowes tags associative array
    if (allowed_tags) {
      allowed_array = allowed_tags.match(/([a-zA-Z0-9]+)/gi);
    }

    str += '';

    // Match tags
    matches = str.match(/(<\/?[\S][^>]*>)/gi);

    // Go through all HTML tags
    for (key in matches) {
      if (isNaN(key)) {
        // IE7 Hack
        continue;
      }

      // Save HTML tag
      html = matches[key].toString();

      // Is tag not in allowed list ? Remove from str !
      allowed = false;

      // Go through all allowed tags
      for (k in allowed_array) {
        // Init
        allowed_tag = allowed_array[k];
        i = -1;

        if (i != 0) {
          i = html.toLowerCase().indexOf('<' + allowed_tag + '>');
        }
        if (i != 0) {
          i = html.toLowerCase().indexOf('<' + allowed_tag + ' ');
        }
        if (i != 0) {
          i = html.toLowerCase().indexOf('</' + allowed_tag);
        }

        // Determine
        if (i == 0) {
          allowed = true;
          break;
        }
      }

      if (!allowed) {
        str = replacer(html, "", str);
        // Custom replace. No regexing
      }
    }
    return str;
  },
  redirect: function (sUrl) {
    window.location.href = sUrl;
  },
  addTargetBlank: function () {
    $('.mail_text a:not(.target_built)').each(function () {
      if (empty($(this).attr('target'))) {
        var oThis = $(this);
        oThis.attr('target', '_blank');
        oThis.addClass('target_built');
        setTimeout(function () {
          oThis.off('click');
        }, 10);

      }
    });
  }
}

var coreMessageScreen = {
  checkScreenForMobile: function () {
    if ($(window).width() < 768) {
      $("#js_core_messages_content").addClass('active-messages');
    } else {
      $("#js_core_messages_content").removeClass('active-messages');
    }
    if (($(window).width() > 767) && ($(window).width() < 1025)) {
      if (($('#main.empty-left:not(.empty-right)').length > 0) || ($('#main.empty-right:not(.empty-left)').length > 0) || ($('#main:not(.empty-left):not(.empty-right)').length > 0)) {
        $("#js_core_messages_content").addClass('active-messages');
      } else {
        $("#js_core_messages_content").removeClass('active-messages');
      }
    }
    if ($(window).width() > 1024) {
      if ($('#main:not(.empty-left):not(.empty-right)').length > 0) {
        $("#js_core_messages_content").addClass('active-messages');
      } else {
        $("#js_core_messages_content").removeClass('active-messages');
      }
    }
  },
  checkComposeMessageForCustomlist: function () {
    var bIsComposeForCustomlist = parseInt($('#js_compose_for_customlist').val());
    if (bIsComposeForCustomlist) {
      coreMessageScreen.checkScreenForMobile();
    }
  }
}

$Ready(function () {
  if ($('#page_mail_index #layer_js_compose_message_textarea').length) {
    $('textarea#js_compose_message_textarea').each(function () {
      this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
      $(this).removeAttr('cols').attr("rows", "1");
    }).on('input', function () {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
    });
  }

  $('#back-to-list-js').off('click').on('click', function () {
    $(this).parents('.core-messages').removeClass('active-messages');
  });
  //init tooltip

  if (document.dir == "rtl"){
    $('.js_mail_messages_content .mail_user_image[data-toggle="tooltip"]').tooltip({
      template: '<div class="tooltip core-mail-tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
      container: 'body',
      placement: 'right'
    });
    $('.js_mail_messages_content .mail_thread_holder.is_user .mail_content[data-toggle="tooltip"]').tooltip({
      template: '<div class="tooltip core-mail-tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
      container: 'body',
      placement: 'right'
    });
    $('.js_mail_messages_content .mail_thread_holder:not(.is_user) .mail_content[data-toggle="tooltip"]').tooltip({
      template: '<div class="tooltip core-mail-tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
      container: 'body',
      placement: 'left'
    });
  }else {
    $('.js_mail_messages_content [data-toggle="tooltip"]').tooltip({
      template: '<div class="tooltip core-mail-tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
      container: 'body'
    });
  }
});

$Behavior.onCloseCoreMessageError = function() {
  if($('#page_mail_index').length) {
    $('div.js_box_close').find('a').click(function(){
      if($('input[name="val[customlist][]"]').length){
        if($('div.js_box_content').find('#js_custom_core_message').length) {
          window.location.reload();
        }
      }
    });
  }
}

PF.event.on('on_page_change_end', function () {
  let customListContainer = $('.js_core_messages_custom_list');
  if (!customListContainer.length) {
    coreMessages.resetMemberCustomList();
  }
});