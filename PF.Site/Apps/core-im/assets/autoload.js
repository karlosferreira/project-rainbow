var $Core_IM = {
  sound_path: '',
  host_failed: false,
  socket_built: false,
  thread_cnt: 0,
  thread_total: 0,
  thread_show: 0,
  users: '',
  all_users: '',
  load_first_time: true,
  scrollBottom: 0,
  im_debug_mode: false, // remember to turn it off
  is_mobile: false,
  is_small_media: false,
  deleted_users: [],
  chat_form_min_height: 0,
  chat_form_max_height: 150,
  file_preview: '[' + oTranslations['im_file'] + ']',
  host: '',
  searching: null,
  new_message: null,
  is_load_more: false,
  dropzoneInit: {},
  activeChatWindow: {},
  is_focus_chat_on_mobile: false,
  cmds: {
    'back_to_conversations': function (t) {
      $('.pf-im-panel[data-thread-id="' + t.data('thread-id') + '"]').trigger('click');
      $('body').addClass('p-im-buddy-screen');
    },
    'close_dock': function (t) {
      $('.p-im-float-dock-item[data-thread-id="' + t.data('thread-id') + '"]').remove();
      $('.pf-im-panel[data-thread-id="' + t.data('thread-id') + '"]').removeClass('active');
      //remove in dock_list
      for (var i = 0; i < $Core_IM.dock_list.length; i++){
        var thread_id = t.data('thread-id');
        if ($Core_IM.dock_list[i] === $Core_IM.stripThreadId(thread_id)) {
          $Core_IM.dock_list.splice(i, 1);
          $Core_IM.cookieActiveChat(thread_id, true);
          break;
        }
      }
    }
  },
  dock_list: [],
  messages_room_active: '',
  debouncePosition: null,
  search_message_tmpl:
    '<div class="pf-im-search">' +
    '<div class="pf-im-search-top">' +
    '<div class="pf-im-search-title">' +
    '<i class="fa fa-search" aria-hidden="true"></i>&nbsp;${Title}' +
    '</div>' +
    '<div class="pf-im-search-action">' +
    '<span class="pf-im-search-close" title="Close search box"><i class="ico ico-close" aria-hidden="true"></i></span>' +
    '</div>' +
    '</div>' +
    '<div class="pf-im-search-main">' +
    '<input type="text" placeholder="${SearchPlaceholder}" id="pf-im-search-input" class="form-control">' +
    '<div class="pf-im-search-result"></div>' +
    '</div>' +
    '</div>',

  dropdown_message_tmpl:
    '<div class="p-im-DropdownContainerPosition">' +
    '<div class="p-im-DropdownContainer dont-bind">' +
    '<div class="p-im-DropdownMessageWrapper">' +
    '<div class="p-im-DropdownMessageHeader">' +
    '<div class="item-title">' + oTranslations['messages'] +
    '</div>' +
    '</div>' +
    '<div class="p-im-DropdownMessageContent">' +
    '<div class="p-im-DropdownMessageItems">' +
    '<div class="pf-im-main">' +
    '</div>' +
    '<div class="p-im-loading-wrapper js_p_im_loading_buddy_visible">' +
    '<i class="fa fa-spinner fa-pulse"></i>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '<div class="p-im-DropdownMessageFooter">' +
    '<div class="p-im-DropdownMessageFooterWrapper">' +
    '<a role="button" class="js_p_im_view_all_message">' + oTranslations['view_all_messages'] + '</a>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>',
  dock_message_tmpl:
    '<div class="p-im-AppDock">' +
    '<div class="p-im-AppDock-outer">' +
    '<div class="p-im-float-dock-item p-im-AppDock-BuddyList dock-item-collapsed">' +
    '<div class="dock-item-outer">' +
    '<div class="dock-item-inner">' +
    '<div class="p-im-dock-header js_p_im_dock_header dont-unbind js_p_im_main_dock">' +
    '<div class="item-title">' +
    '${Title}' +
    '</div>' +
    '<span class="chat-row-close">' +
    '<span class="item-action-btn js_p_im_toggle_search_dock" >' +
    '<i class="ico ico-search-o "></i>' +
    '</span>' +
    '<span class="p-im-close-dock-item item-action-btn js_p_im_minimize_dock">' +
    '<i class="ico ico-minus"></i>' +
    '</span>' +
    '</span>' +
    '</div>' +
    '<div class="p-im-dock-search">' +
    '<div class="p-im-dock-search_item _pf_im_friend_search">' +
    '<i class="fa fa-spinner fa-pulse" style="display: none;"></i><i class="fa fa-search"></i><input type="text" name="user" autocomplete="off" placeholder="${SearchFriendPlaceholder}" readonly="true">' +
    '</div>' +
    '</div>' +
    '<div class="p-im-dock-body">' +
    '<div class="item-buddy-list pf-im-main"></div>' +
    '<div class="pf-im-search-user item-buddy-list" style="display: none;"></div>' +
    '<div class="p-im-loading-wrapper js_p_im_loading_buddy_visible">' +
    '<i class="fa fa-spinner fa-pulse"></i>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '<div class="p-im-AppDock-RoomList dont-unbind-children">' +
    '</div>' +
    '</div>' +
    '</div>' +
    '<audio id="pf-im-notification-sound" src="${SoundPath}" autostart="false" />',
  core_tmpl:
    '<span id="pf-im-total-messages">0</span>' +
    '<div id="pf-open-im"><i class="fa fa-comments"></i></div>' +
    '<div id="pf-im-wrapper"></div>' +
    '<div id="pf-im">' +
    '<div class="p-im-chat-room-block p-im-chat-room-block-empty">' +
    '<div class="p-im-ChatAllMessageEmpty">' +
        '<i class="ico ico-comment-square-o"></i>' +
        '<div class="item-title">' + oTranslations['im_pick_a_contact_from_the_list_and_start_your_conversation'] +
        '</div>' +
      '</div>' +
    '</div>' +
    '<div class="p-im-sidebar-wrapper">' +
    '<div class="pf-im-title">' +
    '${Title}' +
    '<span class="close-im-window" title="${CloseChatBox}"><i class="fa fa-times" aria-hidden="true"></i></span>' +
    '<span class="popup-im-window" title="${OpenNewTab}"><i class="fa fa-external-link" aria-hidden="true"></i></span>' +
    '</div>' +
    '<div class="p-im-friend-search-wrapper">' +
    '<div class="p-im-friend-search _pf_im_friend_search">' +
    '<i class="fa fa-spinner fa-pulse" style="display: none;"></i><i class="fa fa-search"></i><input type="text" name="user" autocomplete="off" placeholder="${SearchFriendPlaceholder}" readonly="true">' +
    '</div>' +
    '</div>' +
    '<div class="p-im-main-wrapper">'+
    '<div class="p-im-main pf-im-main"></div>' +
    '<div class="pf-im-search-user" style="display: none;"></div>' +
    '<div class="p-im-loading-wrapper js_p_im_loading_buddy_visible">' +
    '<i class="fa fa-spinner fa-pulse"></i>' +
    '</div>' +
    '</div>' +
    '</div>'+
    '<audio id="pf-im-notification-sound" src="${SoundPath}" autostart="false" />' +
    '</div>',

  chat_load_more_tmpl:
    '<div class="pf-chat-row-loading"><i class="fa fa-spinner fa-pulse"></i>&nbsp;${LoadingMessage}</div>',

  panel_tmpl:
    '<div class="pf-im-panel" data-friend-id="${UserId}" data-thread-id="${ThreadId}">' +
    '<div class="item-outer">' +
    '<div class="pf-im-panel-image">{{html PhotoLink}}</div>' +
    '<div class="pf-im-panel-content">' +
    '<span class="__thread-name" data-users="">${Name}</span>' +
    '<div class="pf-im-panel-preview"></div>' +
    '</div>' +
    '</div>' +
    '</div>',

  chat_link_tmpl:
    '<div class="p-im-chat-link-outer"><div class="pf-im-chat-link">' +
    '<div class="pf-im-chat-image">{{html LinkPreview}}</div>' +
    '<div class="pf-im-chat-content">' +
    '<a href="${Link}" target="_blank">${Title}</a>' +
    '<div class="pf-im-chat-description">${Description}</div>' +
    '</div>' +
    '</div></div>',

  chat_message_tmpl:
    '<div class="pf-chat-message${OwnerClass}" data-user-id="${UserId}" id="${MessageTimestamp}" data-message-id="${MessageId}">' +
    '<div class="pf-chat-image">{{html UserPhoto}}</div>' +
    '<div class="pf-chat-body"><div class="pf-chat-body-inner">{{html ChatMessage}}' +
    '<time class="set-moment p-im-time-tooltip${TooltipTimeClass}" data-time="${MessageTimestamp}"></time>' +
    '</div>' +
    '</div>' +
    '</div>',

  chat_action_tmpl:
    '<div class="chat-row-title js_p_im_dock_header dont-unbind">' +
    '<span class="chat-row-users">${Users}</span>' +
    '<span class="chat-row-close">' +
    '<span class="item-action-btn" >' +
    '<i class="ico ico-search-o chat-action-search" data-thread-id="${ThreadId}" aria-hidden="true" id="chat-action-search-${ThreadIdParsed}" title="${SearchThread}"></i>' +
    '</span>' +
    '<div class="dropdown"><a class="item-action-btn btn dropdown-toggle" type="button" data-toggle="dropdown">' +
    '<i class="ico ico-dottedmore-vertical-o"></i></a>' +
    '<ul class="dropdown-menu dropdown-menu-right">' +
    '<li><a aria-hidden="true" id="chat-action-noti-${ThreadIdParsed}" data-thread-id="${ThreadId}" class="chat-action-noti"><i class="ico ico-bell2"  title="${ThreadNotification}"></i>${ThreadNotification}</a></li>' +
    '<li><a id="chat-action-delete-${ThreadIdParsed}" class="chat-action-delete" data-thread-id="${ThreadId}"><i class="fa fa-user-times"  title="${HideThread}"></i>${HideThread}</a></li>' +
    '</ul>' +
    '</a></div>' +
    '<span class="chat-row-back item-action-btn" data-cmd="core-im" data-action="back_to_conversations" data-thread-id="${ThreadId}">' +
    '<i class="ico ico-close"></i>' +
    '</span>' +
    '<span class="p-im-close-dock-item item-action-btn" data-cmd="core-im" data-action="close_dock" data-thread-id="${ThreadId}">' +
    '<i class="ico ico-close"></i>' +
    '</span>' +
    '</span>' +
    '</div>' +
    '<form action="{{html AttachmentUrl}}" method="post" enctype="multipart/form-data" style="display: none;">' +
    '<div class="fallback">' +
    '<input name="file" id="im_attachment-${ThreadIdParsed}" data-thread-id="${ThreadId}" data-thread-parsed-id="${ThreadIdParsed}" class="im_attachment"/>' +
    '</div>' +
    '</form>' +
    '<div class="chat-row"></div>' +
    '<div class="chat-form-actions" style="display:none"></div>' +
    '<div class="chat-form pf-im-chat-bottom-wrapper">' +
    '<div class="pf-im-chat-bottom-input-form">' +
    '<textarea class="p-im-contenteditable js_p_im_auto_resize" name="chat" id="im_chat_box-${ThreadIdParsed}"></textarea>' +
    '<span class="chat-row-action">' +
    '<span class="chat-attachment-preview">' +
    '<span class="chat-attachment-preview-uploading"><i class="fa fa-spinner fa-pulse"></i></span>' +
    '&nbsp;&nbsp;<span class="chat-attachment-file-name" id="chat-attachment-file-name-${ThreadIdParsed}"></span>&nbsp;&nbsp;<span id="chat-attachment-result-${ThreadIdParsed}"></span>&nbsp;&nbsp;<i class="fa fa-close chat-attachment-remove" id="chat-attachment-remove-${ThreadIdParsed}" data-thread-parsed-id="${ThreadIdParsed}"></i>' +
    '</span>' +
    '</span>' +
    '</div>' +
    '<div class="chat-bottom-action-wrapper">' +
    '{{html Twemoji}}' +
    '{{html Attachment}}' +
    '<button class="btn btn-primary p-im-btn-send" id="im_send_btn-${ThreadIdParsed}" autofocus="false" title="${Send}"><i class="ico ico-paperplane" aria-hidden="true"></i></button>' +
    '</div>' +
    '</div>',

  chat_action_deleted_user_tmpl:
    '<div class="chat-row-title js_p_im_dock_header  dont-unbind">' +
    '<span class="chat-row-users">${Users}</span>' +
    '<span class="chat-row-close">' +
    '<span class="item-action-btn" >' +
    '<i class="ico ico-search-o chat-action-search" data-thread-id="${ThreadId}" aria-hidden="true" id="chat-action-search-${ThreadIdParsed}" title="${SearchThread}"></i>' +
    '</span>' +
    '<div class="dropdown"><a class="item-action-btn btn dropdown-toggle" type="button" data-toggle="dropdown">' +
    '<i class="ico ico-dottedmore-vertical-o"></i></a>' +
    '<ul class="dropdown-menu dropdown-menu-right">' +
    '<li><a id="chat-action-delete-${ThreadIdParsed}" data-thread-id="${ThreadId}" class="chat-action-delete"><i class="fa fa-user-times"  title="${HideThread}"></i>${HideThread}</a></li>' +
    '</ul>' +
    '</a></div>' +
    '<span class="chat-row-back item-action-btn" data-cmd="core-im" data-action="back_to_conversations" data-thread-id="${ThreadId}">' +
    '<i class="ico ico-close"></i>' +
    '</span>' +
    '<span class="p-im-close-dock-item item-action-btn" data-cmd="core-im" data-action="close_dock" data-thread-id="${ThreadId}">' +
    '<i class="ico ico-close"></i>' +
    '</span>' +
    '</span>' +
    '</div>' +
    '<div class="chat-row"></div>' +
    '<div class="chat-form-actions" style="display:none"></div>' +
    '<div class="chat-form">' +
    '<p class="p-im-info-gray">${CannotReply}</p>' +
    '</div>',

  thread_tmpl:
    '<div class="pf-im-panel ${NewHidden}" data-thread-id="${ThreadId}" style="display:none;">' +
    '<div class="item-outer">' +
    '<div class="pf-im-panel-image"><span class="__thread-image" data-users="${Users}"></span></div>' +
    '<div class="pf-im-panel-content">' +
    '<span class="__thread-name" data-users="${Users}"></span>' +
    '<div class="pf-im-panel-preview">{{html MessagePreview}}</div>' +
    '</div>' +
    '<div class="pf-im-panel-info">' +
    '<span class="badge"></span>' +
    '</div>' +
    '</div>' +
    '</div>',

  loading_conversation_tmpl:
    '<div class="pf-chat-window-loading"><i class="fa fa-spinner fa-pulse"></i>&nbsp;${LoadingConversation}</div>',

  deleted_user_tmpl:
    '<span><a class="no_ajax_link" role="button" target="_blank"><span class="no_image_user  _size__120 _gender_ _first_du" title="${UserName}"><span class="js_hover_info hidden">${UserName}</span><span>${ShortName}</span></span></a></span>',

  invalid_user_tmpl:
    '<span><a class="no_ajax_link" target="_blank"><span class="no_image_user  _size__120 _gender_ _first_du" title="${UserName}"><span class="js_hover_info hidden">${UserName}</span><span>${ShortName}</span></span></a></span>',

  init: function () {
    $Core_IM.im_debug_mode && console.log('init()');
    var u = $('#auth-user'),
      im = $('#pf-im');
    var im_dock = $('.p-im-AppDock');

    if ($('#admincp_base').length || pf_im_node_server === '' || !u.length) {
      $Core_IM.host_failed = true;
      return;
    }

    $Core_IM.thread_cnt = 0;
    $Core_IM.thread_total = 0;
    $Core_IM.users = '';

    $(document).off('click', '[data-cmd="core-im"]');
    $(document).on('click', '[data-cmd="core-im"]', function (evt) {
      var t = $(this),
        action = t.data('action');
      if ($Core_IM.cmds.hasOwnProperty(action) &&
        typeof $Core_IM.cmds[action] === 'function') {
        $Core_IM.cmds[action](t, evt);
      }
    });

    $('.pf_chat_delete_message').on('click', function () {
      var t = $(this),
        thread_id = t.closest('.chat-row').data('thread-id');
      t.hide();
      $('.pf-im-panel[data-thread-id="' + thread_id + '"]').find('.pf-im-panel-preview').text(oTranslations['this_message_has_been_deleted']);
      // remove attachment
      t.siblings('.im_attachment').remove();
      $Core_IM.socket.emit('chat_delete', thread_id, t.data('key'));
      return false;
    });

    if (typeof (twemoji_selectors) !== 'undefined') {
      twemoji_selectors += ', .pf-chat-body, .pf-im-panel-preview';
    }

    $Core_IM.sound_path = (typeof (pf_im_custom_sound) !== 'undefined')
      ? pf_im_custom_sound
      : PF.url.make('/PF.Site/Apps/core-im/assets/sounds/noti.wav').replace('/index.php/', '/');
    $Core_IM.sound_path = $Core_IM.sound_path.indexOf('http') === -1
      ? PF.url.make($Core_IM.sound_path).replace('/index.php/', '/')
      : $Core_IM.sound_path;

    // Hide emoji panel when click on below elements
    $(document).on('click',
      '.chat-row, .chat-row-title, .pf-im-main, ._pf_im_friend_search, #im_chat_box',
      function () {
        $Core_IM.emoji.hide();
      });

    // remove attachment
    $(document).on('click', '.chat-attachment-remove', function () {
      var thread_parsed_id = $(this).data('thread-parsed-id'),
        textarea = $('#pf-chat-window-' + thread_parsed_id).find('.chat-form .p-im-contenteditable'),
        attachment_id = textarea.data('attachment-id');
      $(this).parent('.chat-attachment-preview').hide();
      if (typeof attachment_id !== 'undefined' && attachment_id > 0) {
        $Core_IM.im_debug_mode &&
        console.log('Remove attachment ' + attachment_id);
        // remove attachment in server
        $.ajaxCall('attachment.delete', 'id=' + attachment_id);
        // remove attachment id in textarea
        textarea.removeData('attachment-id');
      }
    });

    $('.chat-action-delete').on('click', function () {
      var t = $(this).data('thread-id');
      $(this).closest('.js_p_im_dock_header').find('span[data-action="close_dock"]').trigger('click');
      $('.pf-im-panel[data-thread-id="' + t + '"]').remove();
      //All message page
      if (window.location.href.search('/im/popup') > 0) {
        if($Core_IM.messages_room_active) {
          $('#pf-chat-window-' + $Core_IM.messages_room_active).remove();
        }
        $Core_IM.messages_room_active = '';
        $('#pf-chat-window-active').remove();
      }
      $Core_IM.socket.emit('hideThread', {
        id: t,
        user_id: $Core_IM.getUser().id,
      });
      $Core_IM.cookieActiveChat(t, true);
    });

    $('.chat-action-noti').on('click', function () {
      var bell = $(this).find('i.ico');
      bell.attr('class', 'ico ' +
        (bell.hasClass('ico-bell2') ? 'ico-bell2-off' : 'ico-bell2'));
      $Core_IM.socket.emit('toggleNoti', {
        id: $(this).data('thread-id'),
        noti: bell.hasClass('ico-bell2'),
        userId: $Core_IM.getUser().id,
      });
    });
    
    $('.chat-action-search').on('click', function () {
      var thread_id = $(this).data('thread-id');
      $('#pf-im-search-input').data('thread-id', thread_id).val('');
      $('.pf-im-search-result').empty();
      $('.pf-im-search').show();
    });

    $('.pf-im-search-close').on('click', function () {
      $('.pf-im-search').hide();
    });

    $('#pf-im-search-input').off().on('keyup', function (e) {
      var pis = $('#pf-im-search-input'), thread_id = $(this).data('thread-id'), stext = pis.val(), valid = $Core_IM.checkKeyUp(e.keyCode);
      // search when input from 3 chars
      if (valid && stext.length > 2) {
        $('.pf-im-search-result').data('thread-id', thread_id).empty();
        $Core_IM.socket.emit('search_message', thread_id, stext, 0);
      }
    });

    $('.pf-im-search-result').on('scroll',function () {
      if ($(this).children().length > 0 && $(this).scrollTop() + $(this).innerHeight() === $(this)[0].scrollHeight) {
        var pis = $('#pf-im-search-input'), stext = pis.val();
        // search when input from 3 chars
        if (stext.length > 2) {
          $Core_IM.socket.emit('search_message',
            $(this).data('thread-id'), stext,
            $('.pf-im-search-result').find('.pf-chat-message').length);
        }
      }
    });

    $('.chat-row, .chat-form').on('mousedown',function () {
      $('.p-im-chat-room-block').css('opacity', 1);
    });

    var hd_mess = $('#hd-message a'),
      span_new = $('span#js_total_new_messages');

    if (hd_mess.length || (!hd_mess.length && span_new.length)) {
      var obj = (hd_mess.length ? hd_mess : span_new.parents('a:first'));

      obj.each(function () {
        var m = $(this);
        m.addClass('built');
        m.addClass('no_ajax');
        m.parent().find('.dropdown-panel').empty();
        m.parent().on("shown.bs.dropdown", function () {
          $('body').addClass('im-is-dropdown');
          var parentFix = m.closest('.sticky-bar');
          if (parentFix && parentFix.css("position") === "fixed") {
            $('.p-im-DropdownContainerPosition').attr('style', "position: fixed;z-index: 99;transform:translateX(-50%);top: " + (m.offset().top + m.height() - $(window).scrollTop()) + "px;left: " + m.offset().left + "px");
          } else {
            $('.p-im-DropdownContainerPosition').attr('style', "position: absolute;z-index: 99;transform:translateX(-50%);top: " + (m.offset().top + m.height()) + "px;left: " + m.offset().left + "px");
          }
        });
        m.parent().on("hidden.bs.dropdown", function () {
          $('body').removeClass('im-is-dropdown');
        });


        // m.click(function () {
        //   $Core_IM.im_debug_mode && console.log('click!');
        //   $('#pf-open-im').trigger('click');
        //   return false;
        // });
      });
    }

    $(document).on('click', '.pf-im-title .close-im-window, #pf-im-wrapper',
      function () {
        var body = $('body');
        body.removeClass('im-is-active');
        body.css('overflow-y', 'scroll');
        body.css('position', 'initial');
        $('#pf-im, #pf-chat-window, #pf-chat-window-active, .pf-im-search, #pf-im-wrapper').hide();
        deleteCookie('pf_im_active');
      });

    $('.popup-im-window,.js_p_im_view_all_message').on('click',function () {
      window.location.href = PF.url.make('/im/popup');
      $('#pf-im-wrapper').trigger('click');
    });

    $('._pf_im_friend_search input').on('keyup',function (e) {
      var t = $(this),
        im_main = $('.pf-im-main'),
        im_search = $('.pf-im-search-user'),
        valid = $Core_IM.checkKeyUp(e.keyCode);

      if (!valid) {
        return;
      }

      im_main.hide();
      im_search.empty().show();
      if (t.val() === '') {
        im_search.hide();
        im_main.show();
        $Core_IM.searching = null;
        return;
      }

      $('.fa-search', '._pf_im_friend_search').hide();
      $('.fa-pulse', '._pf_im_friend_search').show();
      clearTimeout($Core_IM.searching);
      $Core_IM.searching = setTimeout(function () {
        $.ajax({
          url: PF.url.make('/im/search-friends'),
          data: 'search=' + t.val(),
          contentType: 'application/json',
          success: function (e) {
            // im_main.hide();
            $('.fa-pulse', '._pf_im_friend_search').hide();
            $('.fa-search', '._pf_im_friend_search').show();
            im_search.empty();
            if (e !== '') {
              im_search.append(e);

              // update search preview
              $('.pf-im-search-user').find('.pf-im-panel').each(function () {
                var friend_id = $(this).data('friend-id');
                $Core_IM.socket.emit('loadSearchPreview', {
                  'friend_id': friend_id,
                  'user_id': $Core_IM.getUser().id,
                });
                var thread_ids = [$Core_IM.getUser().id, friend_id],
                  thread_id = thread_ids.sort($Core_IM.sortNumber).join(':');
                $Core_IM.socket.emit('update_new', friend_id, thread_id);
              });
            } else {
              im_search.append('<p class="p-2 p-im-info-gray">' + oTranslations['no_friends_found'] + '</p>');
            }
            $Core.loadInit();
          },
        });
      }, 1e3);
    });

    $('.im_action_emotion').off().on('click', function () {
      var thread_id = $(this).data('thread-id'),
      thread = $('#pf-chat-window-' + $Core_IM.stripThreadId(thread_id));
      if (thread.find('.chat-form-actions').is(':visible')) {
        $Core_IM.emoji.hide();
      } else {
        $Core_IM.emoji.hide();
        $Core_IM.emoji.show($(this).data('action'), thread_id);
      }
    });

    $('#pf-open-im').on('click',function () {
      $Core_IM.im_debug_mode && console.log('#pf-open-im click');
      if ($Core_IM.host_failed !== false) {
        window.parent.sCustomMessageString = $Core_IM.host_failed;
        tb_show(oTranslations['error'],
          $.ajaxBox('core.message', 'height=150&width=300'));
        return;
      }
      var b = $(this),
        body = $('body');

      // lock scroll on ios
      if ($Core_IM.isIos()) {
        $('body').css('position', 'fixed');
      }

      $('#pf-im').show();
      $('#pf-im-wrapper').show();

      if (!b.data('fake-click') ||
        (b.data('fake-click') && b.data('fake-click') == '0')) {
        body.addClass('im-is-active');
      }

      setCookie('pf_im_active', 1);

      $('.pf-im-panel.active').removeClass('active');
      // $('span#js_total_new_messages').html('0').hide();
    });
    if (u.length && !im_dock.length && !im.length) {
      if (window.location.href.search('/im/popup') > 0) {
        $.tmpl($Core_IM.core_tmpl, {
          'Title': oTranslations['all_chats'],
          'CloseChatBox': oTranslations['close_chat_box'],
          'OpenNewTab': oTranslations['open_in_new_tab'],
          'SearchFriendPlaceholder': oTranslations['search_friends_dot_dot_dot'],
          'SoundPath': $Core_IM.sound_path
        }).prependTo('body');
        //Disable ajax loading
        $('a').addClass('no_ajax_link');
        close_warning_checked = true;
      } else {
        $.tmpl($Core_IM.dock_message_tmpl, {
          'Title': oTranslations['chat'],
          'SearchFriendPlaceholder': oTranslations['search_friends_dot_dot_dot'],
          'SoundPath': $Core_IM.sound_path
        }).prependTo('body');
      }
      $.tmpl($Core_IM.search_message_tmpl, {
        'Title': oTranslations['search_message'],
        'SearchPlaceholder': oTranslations['enter_search_text']
      }).prependTo('body');
      $.tmpl($Core_IM.dropdown_message_tmpl, {
        'Title': oTranslations['all_chats'],
        'CloseChatBox': oTranslations['close_chat_box'],
        'OpenNewTab': oTranslations['open_in_new_tab'],
        'SearchFriendPlaceholder': oTranslations['search_friends_dot_dot_dot'],
        'SoundPath': $Core_IM.sound_path,
      }).prependTo('body');


      $Core.loadInit();
    }

    // Search message draggable init
    // var im_search = $('.pf-im-search');
    
    $Core_IM.chatWithUser();

    // Load more messages when scroll up
    var chat_row = $('.chat-row');
    chat_row.off('scroll').on('scroll',function () {
      var ele = $(this), thread_id = ele.data('thread-id');
      if (ele.scrollTop() === 0) {
        if (!ele.find('.pf-chat-row-loading').length) {
          $.tmpl($Core_IM.chat_load_more_tmpl, {
            'LoadingMessage': oTranslations['loading_messages'],
          }).prependTo(ele);
        }
        $Core_IM.socket.emit('loadMore', thread_id, ele.find('.pf-chat-message').length);
      }
    });
    //On click load more
    $(document).off('click', '.pf-im-more-conversation').on('click', '.pf-im-more-conversation', function (e) {
      //prevent close dropdown when click on this loadmore
      e.stopPropagation();
      var _clicked = $(this),
          loadingEle=$('.js_p_im_loading_buddy_visible');
      loadingEle.show();
      _clicked.remove();
      $Core_IM.is_load_more = true;
      $Core_IM.socket.emit('loadThreads', $Core_IM.getUser().id, pf_total_conversations, $('.pf-im-panel').length);
    });
    // $Core_IM.load_first_time = false;

    //set position for window chat
    $Core_IM.allChatGetPosition();
    //auto resize textarea
    $Core_IM.resizeTextarea();
    $Core_IM.tooltipTime();
    $Core_IM.initDockBuddySearch();
    $Core_IM.initChatDock();
  },
  initDockBuddySearch: function () {
    $(document).off('click', '.js_p_im_toggle_search_dock').on('click', '.js_p_im_toggle_search_dock', function(){
      var buddyDockEle= $(this).closest(".p-im-AppDock-BuddyList");
      buddyDockEle.toggleClass('open-search');
      //clear input search
      buddyDockEle.find('._pf_im_friend_search input').focus().val('');
      buddyDockEle.find('.pf-im-main').show();
      buddyDockEle.find('.pf-im-search-user').hide();
    });
  },
  initChatDock: function (is_firebase) {
    $(document).off('click', '.js_p_im_dock_header').on("click", '.js_p_im_dock_header', function (e) {
      var action_row = $(this).find('.chat-row-close'),
        isClosed = $(this).closest(".p-im-float-dock-item").hasClass('dock-item-collapsed'),
        mainDock = $(this).hasClass('js_p_im_main_dock'), parent = $(this).closest(".p-im-float-dock-item"),
        user_id = $Core_IM.getUser().id;
      if (!action_row.is(e.target) && action_row.has(e.target).length === 0) {
        parent.toggleClass("dock-item-collapsed");
        if (mainDock) {
          if (!isClosed) {
            setCookie('pf_im_main_dock_hide_user_' + user_id, 1);
          } else {
            setCookie('pf_im_main_dock_hide_user_' + user_id, 0);
          }
        } else {
          if (isClosed) {
            $Core_IM.updateNotificationCount(parent.data('thread-id'));
            var chat_row = parent.find('.chat-row');
            if (chat_row.length) {
              chat_row.scrollTop(chat_row[0].scrollHeight);
            }
          }
          $Core_IM.cookieActiveChat(parent.data('thread-id'), false, !isClosed)
        }
      }

    });
    $(document).off('click', '.js_p_im_minimize_dock').on('click', '.js_p_im_minimize_dock', function() {
      $(this).closest('.js_p_im_dock_header').trigger('click');
    });
    if ($Core_IM.is_small_media) {
      if (!$Core_IM.is_focus_chat_on_mobile && window.location.href.search('/im/popup') > 0) {
        var hrefParam = window.location.search;
        hrefParam = hrefParam.replace('?', '');
        if (hrefParam && hrefParam.indexOf('thread_id=') !== -1) {
          var thread_id = hrefParam.replace('thread_id=', '');
          if (!is_firebase) {
            thread_id.split('_').map(function (id) {
              if (parseInt(id) !== parseInt($Core_IM.getUser().id)) {
                $Core_IM.composeMessage({user_id: id});
              }
              return false;
            });
          } else {
            thread_id = $Core_IM.stripThreadId(thread_id, true);
            var friend_uid = thread_id.replace($Core_IM_Firebase.current_user.uid, '');
            if (friend_uid) {
              IMFirebaseComposeMessage({user_id: $Core_IM_Firebase.decodeId(friend_uid)});
            }
          }
        }
        $Core_IM.is_focus_chat_on_mobile = true;
      }
      return false;
    }

    if (window.location.href.search('/im/popup') > 0) {
      return false;
    }
    var user_id = $Core_IM.getUser().id || 0, hideMainDock = getCookie('pf_im_main_dock_hide_user_' + user_id);
    if (hideMainDock === null && typeof pf_minimise_chat_dock !== "undefined") {
      hideMainDock = pf_minimise_chat_dock;
    }
    if (hideMainDock === "0" || hideMainDock === 0) {
      $('.js_p_im_main_dock').closest('.p-im-float-dock-item').removeClass('dock-item-collapsed');
    }
    var activeChatWindow = getCookie((pf_im_chat_server === 'nodejs' ? 'pf_im_active_chat_dock' : 'pf_im_active_chat_dock_firebase') + '_user_' + user_id) || '';
    if (activeChatWindow.length) {
      this.activeChatWindow = JSON.parse(activeChatWindow);
    }
    if (Object.keys(this.activeChatWindow).length) {
      var dock_length_max = 3;
      if (window.matchMedia('(max-width: 1200px)').matches) {
        dock_length_max = 1;
      }
      if ($('.p-im-AppDock-Room').length >= dock_length_max) {
        return false;
      }
      for (var t_id in this.activeChatWindow) {
        var t_id_parsed = $Core_IM.stripThreadId(t_id), c = $('#pf-chat-window-' + t_id_parsed);
        if (c.length || t_id === undefined || t_id === "undefined") {
          continue;
        }

        var html = $.tmpl($Core_IM.chat_action_tmpl, {
          'ThreadNotification': oTranslations['noti_thread'],
          'SearchThread': oTranslations['search_thread'],
          'HideThread': oTranslations['hide_thread'],
          'AttachmentUrl': PF.url.make('/im/attachment'),
          'Send': oTranslations['send'],
          'ThreadId': t_id,
          'Users': oTranslations['loading'] + '...',
          'ThreadIdParsed': t_id_parsed,
          'Attachment': (typeof pf_im_attachment_enable !== 'undefined')
            ? '<i class="ico ico-plus item-action-btn" onclick="$Core_IM.imAttachFile(\'' + t_id + '\',\'' + t_id_parsed + '\')" title="' +
            oTranslations['add_attachment'] + ' (' + pf_im_attachment_types +
            ')"></i>'
            : '',
          'Twemoji': (typeof pf_im_twemoji_enable !== 'undefined')
            ? '<i class="ico ico-smile-o im_action_emotion item-action-btn" data-thread-id="' + t_id + '" id="im_action_emotion-' + t_id_parsed + '" data-action="' +
            PF.url.make('/emojis?id=im_chat_box-' + t_id_parsed) + '"></i>'
            : '',
          'UploadingMessage': oTranslations['uploading'] + '...'
        });
        html = $('<div></div>').append(html).html();
        if (!is_firebase) {
          $Core_IM.socket.emit('loadConversation', {
            user_id: $Core_IM.getUser().id,
            partner_id: $Core_IM.getPartnerId(t_id),
            thread_id: t_id,
            ignore_notify: true
          });
        }
        //init Room Dock chat
        $('.p-im-AppDock-RoomList').prepend('<div id="pf-chat-window-' + $Core_IM.stripThreadId(t_id) + '" data-thread-id="' + t_id + '" class="p-im-float-dock-item p-im-AppDock-Room p-im-chat-room-block '
          + (typeof this.activeChatWindow[t_id] !== "undefined" && this.activeChatWindow[t_id] === 0 ? 'dock-item-collapsed' : '') + '"><div class="dock-item-outer"><div class="dock-item-inner">' +
          html + '</div></div></div>');

        $Core_IM.dock_list.push($Core_IM.stripThreadId(t_id));
        if($Core_IM.dock_list.length > dock_length_max) {
          var id_remove = $Core_IM.dock_list[0];
          $Core_IM.dock_list.splice(0,1);
          $('#pf-chat-window-' + id_remove).remove();
          $Core_IM.cookieActiveChat($Core_IM.stripThreadId(id_remove, true), true);
        }

        $('#pf-chat-window-' + t_id_parsed).find('.chat-row').attr('data-thread-id', t_id);

        var l = $('#pf-chat-window-'+ t_id_parsed +' .fa-external-link');
        l.data('action', l.data('action') + '?thread_id=' + t_id);
        if (is_firebase) {
          $Core_IM_Firebase.loadMessages(t_id);
          $Core_IM_Firebase.chatTextArea(t_id);
        } else {
          $Core_IM.chatTextArea(t_id);
        }
        $Core.loadInit();
      }
    }
  },
  cookieActiveChat: function (thread_id, is_remove, is_close) {
    var activeChatWindow = $Core_IM.activeChatWindow,
      user_id = $Core_IM.getUser().id || 0, cookieName = (pf_im_chat_server === 'nodejs' ? 'pf_im_active_chat_dock' : 'pf_im_active_chat_dock_firebase') + '_user_' + user_id;
    if (is_remove) {
      if (typeof activeChatWindow[thread_id] !== "undefined") {
        delete activeChatWindow[thread_id];
        setCookie(cookieName, JSON.stringify(activeChatWindow));
      }
    } else {
      activeChatWindow[thread_id] = is_close ? 0 : 1;
      setCookie(cookieName, JSON.stringify(activeChatWindow));
    }
    return true;
  },
  removeToolTipTime: function() {
    var tooltipTimeEle= $('body').children('.p-im-time-tooltip');
    if(tooltipTimeEle.length) {
      var idMsg = tooltipTimeEle.data('time');
      $('#'+idMsg+' .pf-chat-body-inner').trigger('mouseleave');
    }
  },
  tooltipTime: function () {
    window.removeEventListener('scroll', this.removeToolTipTime);
    window.addEventListener('scroll', this.removeToolTipTime);

    $('.pf-chat-body-inner').addClass('dont-unbind').off('mouseenter mouseleave').on('mouseenter', function () {
      // grab the menu
      var chatRowEle= $(this).closest('.chat-row');
      if (!chatRowEle.length) {
        //Is in search box
        chatRowEle = $(this).closest('.pf-im-search-result')
      }
      if(chatRowEle.offset().top > $(this).offset().top) {
        return false;
      }
      var tooltipTimeWrapper = $(this).find('.p-im-time-tooltip'), body = $('body');
      // detach it and append it to the body
      body.append(tooltipTimeWrapper.detach());

      // grab the new offset position
      var eOffset = $(this).offset(),
        parentOffset = body.offset(),
        eLeft;
     
      if( $("html[dir=rtl]").length){
        eLeft = eOffset.left - tooltipTimeWrapper.outerWidth() + $(this).outerWidth() - parentOffset.left;
        if($(this).closest('.pf-chat-owner').length) {
          eLeft = eOffset.left - parentOffset.left;
        }
      }else {
        eLeft = eOffset.left - parentOffset.left;
        if($(this).closest('.pf-chat-owner').length){
          eLeft = eOffset.left - tooltipTimeWrapper.outerWidth() + $(this).outerWidth() - parentOffset.left;
        }
      }
      if($Core_IM.is_small_media){
        if(eLeft < 0){
          eLeft = eLeft + tooltipTimeWrapper.outerWidth() - 32;
        }
      }
      // make sure to place it where it would normally go (this could be improved)
      tooltipTimeWrapper.css({
        'display': 'block',
        'top': eOffset.top - tooltipTimeWrapper.outerHeight() - parentOffset.top - 10,
        'left': eLeft,
        'opacity': '1',
        'visibility': 'visible',
        'right': 'auto',
        'transform': 'none',
        'margin': '0',
        'transition': 'none',
        'position': 'absolute',
        'z-index': '999'
      });

    }).on('mouseleave', function () {
      // grab the menu
      var tooltipTimeWrapper = $('body').children('.p-im-time-tooltip');
      $(this).append(tooltipTimeWrapper.detach());
      tooltipTimeWrapper.hide();

    });
  },
  allChatGetPosition: function () {
    clearTimeout($Core_IM.debouncePosition);
    $Core_IM.debouncePosition = setTimeout(function() {
      if (window.location.href.search('/im/popup') > 0) {
        var mainContainer = $('#main');
        var offsetMain = mainContainer.offset();
        $('#pf-im').css('top', offsetMain.top);
      }
    },200);
  },
  isIos: function () {
    var iDevices = [
      'iPad Simulator',
      'iPhone Simulator',
      'iPod Simulator',
      'iPad',
      'iPhone',
      'iPod',
    ];
    while (iDevices.length) {
      if (navigator.platform === iDevices.pop()) {
        return true;
      }
    }
    return false;
  },

  // click send message on user profile
  composeMessage: function (param) {
    if ($Core_IM.host_failed !== false) {
      window.parent.sCustomMessageString = $Core_IM.host_failed;
      tb_show('error', $.ajaxBox('core.message', 'height=150&width=300'));
      return;
    }

    if (typeof param.user_id == "undefined" && typeof param.id != "undefined") {
      param.user_id = param.id;
    }

    if (typeof param.message != "undefined") {
      $Core_IM.new_message = param.message;
    } else {
      $Core_IM.new_message = null;
    }

    $Core_IM.socket.emit('showThread', $Core_IM.getUser().id + ':' +
      param.user_id, $Core_IM.getUser().id);

    // open IM
    $('#pf-open-im').trigger('click');

    // clear search
    var search_user = $('._pf_im_friend_search input');
    search_user.val('');
    $('.pf-im-main').show();
    $('.pf-im-search-user').hide();

    var thread = $('.pf-im-panel[data-thread-id="' + $Core_IM.getUser().id +
      ':' + param.user_id + '"]');
    if (thread.length > 0) {
      thread.trigger('click');
      return false;
    }

    thread = $('.pf-im-panel[data-thread-id="' + param.user_id + ':' +
      $Core_IM.getUser().id + '"]');
    if (thread.length > 0) {
      thread.trigger('click');
      return false;
    }

    var f = $('.pf-im-menu a[data-type="2"]');
    if (f.hasClass('active')) {
      f.removeClass('active');
      $('.pf-im-menu a[data-type="1"]').addClass('active');
    }

    var is_listing = (typeof (param.listing_id) === 'number');
    $.ajax({
      url: PF.url.make('/im/conversation') + '?user_id=' + param.user_id +
        '&listing_id=' + (is_listing ? param.listing_id : '0'),
      contentType: 'application/json',
      success: function (resp) {
        if (typeof (resp.error) === 'string') {
          $Core_IM.imFailed();
          return;
        }

        var e = resp.user,
          thread_ids = [$Core_IM.getUser().id, e.id],
          thread_id = thread_ids.sort($Core_IM.sortNumber).join(':'),
          m = $('.pf-im-panel[data-thread-id="' + thread_id + '"]');

        if (!m.length) {
          $.tmpl($Core_IM.panel_tmpl, {
            'UserId': e.id,
            'ThreadId': thread_id,
            'PhotoLink': e.photo_link,
            'Name': e.name
          }).prependTo('.pf-im-main');
        }
        $Core_IM.socket.emit('loadSearchPreview', {
          'friend_id': e.id,
          'user_id': $Core_IM.getUser().id,
        });

        $Core.loadInit();

        m = $('.pf-im-panel[data-thread-id="' + thread_id + '"]');
        m.removeClass('active');
        if (is_listing) {
          m.data('listing-id', param.listing_id);
        }
        m.trigger('click');
      }
    });

    return false;
  },

  imAttachFile: function (thread_id, thread_parsed_id) {
    if (typeof (myDropzone) === 'undefined' &&
      typeof pf_im_attachment_enable !== 'undefined' && typeof this.dropzoneInit[thread_id] == "undefined") {
      $Core_IM.initDropzone(thread_id, thread_parsed_id);
    }
    $('#im_attachment-' + thread_parsed_id).trigger('click');
  },

  imFailed: function () {
    $('.chat-action-delete').trigger('click');
    var popup = $('<a class="popup" href="' + PF.url.make('/im/failed') +
      '"></a>');
    tb_show('', PF.url.make('/im/failed'), popup);
    $('#pf-im').hide();
    $('body').css('position', 'initial')
    $('#pf-im-wrapper').hide();
  },

  addTargetBlank: function (photo_link) {
    var user_image = $($.parseHTML(photo_link));
    user_image.attr('target', '_blank');
    user_image.addClass('no_ajax_link');

    return user_image.prop('outerHTML');
  },

  getUser: function () {
    var u = $('#auth-user');

    return {
      id: u.data('id'),
      name: u.data('name'),
      photo_link: $Core_IM.addTargetBlank(u.data('image')),
    };
  },

  filterWords: function (text) {
    for (var m in window.ban_filters) {
      var convertBan = '' + m.replace('&#42;', '*') + '';
      convertBan = convertBan.replace('/', '\/');
      convertBan = convertBan.replace('&#42;', '*');
      convertBan = convertBan.replace('*', '([a-zA-Z@]?)*');
      var regex = new RegExp(convertBan, 'i');
      if (text.match(regex) !== null) {
        if (ban_users.hasOwnProperty(m)) {
          // ban user
          $.ajax({
            url: PF.url.make('/im/ban-user'),
            data: {
              ban_id: ban_users[m]
            },
            contentType: 'application/json',
            'success': function (data) {
              if (data.success) window.location.reload();
            }
          });
        }
        text = text.replace(regex, window.ban_filters[m]);
      }
    }

    return text;
  },

  preventXss: function (text) {
    var regScript = /<script/ig,
      regEndScript = /<\/script/ig,
      replaceScript = regScript.exec(text),
      replaceEndScript = regEndScript.exec(text);
    if (replaceScript !== null && replaceScript.length > 0) {
      text = text.replace(replaceScript[0],
        replaceScript[0].split('').join(' '));
    }
    if (replaceEndScript !== null && replaceEndScript.length > 0) {
      text = text.replace(replaceEndScript[0],
        replaceEndScript[0].split('').join(' '));
    }

    return text;
  },

  fixChatMessage: function (text, bReplaceLink) {
    if (text === null) {
      return '';
    }

    var map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      '\'': '&#039;',
      '\r': '<br />',
      '\n': '<br />',
      '\n\r': '<br />',
    };

    if (bReplaceLink) {
      text = text.replace(/[&<>"'\r\n]/g, function (m) {
        return map[m];
      });
      var patt = /(https?):\/\/[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]/ig,
        match = patt.exec(text);
      if (match !== null) {
        text = text.replace(match[0], '<a target="_blank" href="' + match[0] + '">' + match[0] +
          '</a>');
      }
    }

    return text;
  },

  getPartnerId: function (thread_id) {
    var users = thread_id.split(':');
    for (var i in users) {
      if (users[i] != $Core_IM.getUser().id) {
        return users[i];
      }
    }
    return false;
  },

  buildMessage: function (message, do_scroll, force, no_trash) {
    if (typeof (message) === 'string') {
      message = JSON.parse(message);
    }
    if (!message.deleted) {
      var patt = /(https?):\/\/[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]/ig,
        match = patt.exec(message.text);
      if (match !== null) {
        // parse link for preview
        $.ajax({
          url: PF.url.make('/im/link'),
          data: 'url=' + match[0] + '&time_stamp=' + message.time_stamp,
          contentType: 'application/json',
          'success': function (e) {
            var message = $('#' + e.time_stamp),
              link_preview = '';
            if (message.length === 1) {
              if (e.link.has_embed > 0) {
                link_preview = '<a href="' + e.link.link +
                  '" target="_blank" class="play_link no_ajax_link" onclick="$Core.box(\'link.play\', 700, \'id=' +
                  e.link.link_id +
                  '&popup=true\', \'GET\'); return false;"><span class="play_link_img">' +
                  oTranslations['play'] + '</span><span class="item-media-src" style="background-image: url(' + e.link.image +
                  ');"></span></a>';
              } else {
                link_preview = e.link.image
                  ? '<a href="' + e.link.link +
                  '" target="_blank"><span class="item-media-src" style="background-image: url(' + e.link.image + ');"></span></a>'
                  : '';
              }

              // check if link already have preview
              if (message.find('.pf-im-chat-link').length === 0) {
                message.find('.pf-chat-body').prepend($.tmpl($Core_IM.chat_link_tmpl, {
                  'LinkPreview': link_preview,
                  'Link': e.link.link,
                  'Title': e.link.title,
                  'Description': e.link.description,
                }));
              }

              message.find('.fa-pulse').parent().remove();
            }
          },
        });
      }

      var attachment = '';
      if (typeof (message.attachment_id) === 'number' && message.attachment_id > 0) {
        attachment = '<div id="im_attachment_' + message.attachment_id +
          '" class="im_attachment"></div>';
        $.ajax({
          url: PF.url.make('/im/get-attachment'),
          data: 'id=' + message.attachment_id,
          contentType: 'application/json',
          success: function (e) {
            if (e.is_image) {
              attachment =
                '<a href="' + e.path + '" class="thickbox">' +
                '<img alt="' + e.file_name + '" src="' + e.thumb + '" data-src="' + e.thumb + '">' +
                '</a>';
            } else {
              attachment =
                '<a target="_blank" href="' + e.path +
                '" class="attachment_row_link no_ajax_link">' + e.file_name +
                '</a>';
            }
            $('#im_attachment_' + e.id).html(attachment);
            $Core.loadInit();
          },
        });
      }
    }

    var icon = '',
      time_stamp_ms = message.time_stamp,
      user_image;
    // support old data
    if (time_stamp_ms < 1000000000000) {
      time_stamp_ms *= 1000;
    }

    if ($.inArray(message.user.id.toString(), $Core_IM.deleted_users) !== -1) {
      user_image = $('<div></div>').append($.tmpl($Core_IM.deleted_user_tmpl, {
        'UserName': oTranslations['deleted_user'],
        'ShortName': 'DU',
      })).html();
    } else if (typeof pf_im_blocked_users !== 'undefined' && pf_im_blocked_users.indexOf(message.user.id) !== -1) {
      user_image = $('<div></div>').append($.tmpl($Core_IM.invalid_user_tmpl, {
        'UserName': oTranslations['invalid_user'],
        'ShortName': 'IU',
      })).html();
    } else {
      user_image = force ? message.user.photo_link : '';
    }

    if (message.user.id === $Core_IM.getUser().id &&
      typeof (no_trash) === 'undefined' &&
      (typeof (window.pf_time_to_delete_message) === 'undefined' ||
        Date.now() - time_stamp_ms <= window.pf_time_to_delete_message)) {
      icon = '<a href="#" class="pf_chat_delete_message" data-key="' +
        message.time_stamp + '"><i class="fa fa-trash"></i></a>';
    }
    if (do_scroll === true) {
      var c = $('.chat-row');
      c.scrollTop(c[0].scrollHeight);
    }

    return $.tmpl($Core_IM.chat_message_tmpl, {
      'OwnerClass': message.user.id === $Core_IM.getUser().id
        ? ' pf-chat-owner'
        : '',
      'TooltipTimeClass': message.user.id === $Core_IM.getUser().id
      ? ' owner'
      : '',
      'UserId': message.user.id,
      'MessageId': message.id ? message.id : message.time_stamp,
      'MessageTimestamp': message.time_stamp,
      'UserPhoto': user_image,
      'ChatMessage': (message.deleted
        ? '<span class="pf-im-chat-text"><i>' + oTranslations['this_message_has_been_deleted'] + '</i></span>'
        : '<span class="pf-im-chat-text">' + (match !== null
        ? '<div><i class="fa fa-spinner fa-pulse"></i></div>'
        : '') + $Core_IM.fixChatMessage(message.text, true) + '</span>' +
        attachment + icon),
    });
  },

  updateChatPreview: function (thread_id, text) {
    var old_thread = $('.pf-im-panel[data-thread-id="' + thread_id + '"]'),
      old_thread_first = $('.pf-im-panel[data-thread-id="' + thread_id + '"]:first'),
      thread = old_thread_first.clone();
    if (text === '') {
      text = $Core_IM.file_preview;
    }
    thread.find('.pf-im-panel-preview').removeClass('twa_built').text(text);
    old_thread.remove();
    $('.pf-im-main').prepend(thread);
  },

  chatTextArea: function (thread_id) {
    var threadEle = $('#pf-chat-window-' + $Core_IM.stripThreadId(thread_id));
    threadEle.find('.chat-form textarea').addClass('dont-unbind').off().on('focus', function () {
      //Hide action when focus chat
      $('.chat-form-actions').hide();
      $Core_IM.updateNotificationCount(thread_id);
    }).on('keydown',function (e) {
      if (e.which === 13 && !e.shiftKey && !e.ctrlKey) {
        e.preventDefault();
        $Core_IM.submitChat(thread_id);
      }
    });
    threadEle.find('.p-im-btn-send').addClass('dont-unbind').off('click').on('click', function (e) {
      e.preventDefault();
      $Core_IM.submitChat(thread_id);
    });
  },
  resizeTextarea: function () {
    var text = $('.js_p_im_auto_resize');

    text.each(function(){
        $(this).attr('rows',1);
        resize($(this));
    });

    $(document).on('input', '.js_p_im_auto_resize', function() {
        resize($(this));
    });

    function resize ($text) {
        $text.css('height', 'auto');
        if ($text[0].scrollHeight) {
          $text.css('height', $text[0].scrollHeight + 'px');
        }
    }
  },
  updateNotificationCount: function (thread_id) {
    var t_all = $('.pf-im-panel[data-thread-id="' + thread_id + '"]'), total_new_messages = $('#js_total_new_messages');
    if (t_all.hasClass('new')) {
      t_all.removeClass('new');
      // update notification counter
      var message_counter_txt = total_new_messages.text();
      if (message_counter_txt) {
        var message_counter = parseInt(total_new_messages.text());
        message_counter--;
        if (message_counter === 0) {
          total_new_messages.text(0).hide();
        } else {
          total_new_messages.text(message_counter);
        }
      }
    }
    t_all.find('.badge').text('0');
    return true;
  },
  chatWithUser: function () {
    var chat_form = $('.chat-form');
    if (chat_form.length && chat_form.hasClass('ui-resizable')) {
      chat_form.resizable('destroy');
    }

    $('.pf-im-panel').addClass('dont-unbind').off('click').on('click', function () {
      $Core_IM.load_first_time = true;
      var isImPopup = window.location.href.search('/im/popup'),
        t = $(this),
        t_id = t.data('thread-id'), friend_id = t.data('friend-id');
      if (!t_id && friend_id) {
        var thread_ids = [$Core_IM.getUser().id, friend_id];
        t_id = thread_ids.sort($Core_IM.sortNumber).join(':');
      }
      var t_id_parsed = $Core_IM.stripThreadId(t_id), c = $('#pf-chat-window-' + t_id_parsed),
        t_all = $('.pf-im-panel[data-thread-id="' + t_id + '"]'), html = '';
      // remove count when view conversation
      t.removeClass('count');

      if (c.length) {
        // Already show chat window, open it
        c.removeClass('dock-item-collapsed');
        c.find('.chat-form textarea').trigger('focus');
        //responsive switch screen
        $('body').removeClass('p-im-buddy-screen');
        if(t.closest('.p-im-DropdownContainer').length ){
          $('#hd-message.open a').dropdown('toggle');
        }
        $Core_IM.updateNotificationCount(t_id);
        if ($Core_IM.new_message) {
          $Core_IM.insertAtCaret('im_chat_box-' + t_id_parsed, $Core_IM.new_message, true);
          $Core_IM.new_message = null;
        }
        return false;
      }

      if (t.data('user-deleted') || t.data('user-banned') || t.data('user-blocked') || t.data('not-friend')) {
        html = $.tmpl($Core_IM.chat_action_deleted_user_tmpl, {
          'SearchThread': oTranslations['search_thread'],
          'HideThread': oTranslations['hide_thread'],
          'CannotReply': oTranslations['you_cannot_reply_this_conversation'],
          'ThreadId': t_id,
          'Users': oTranslations['deleted_user'],
          'ThreadIdParsed': t_id_parsed
        });
      } else {
        html = $.tmpl($Core_IM.chat_action_tmpl, {
          'ThreadNotification': oTranslations['noti_thread'],
          'SearchThread': oTranslations['search_thread'],
          'HideThread': oTranslations['hide_thread'],
          'AttachmentUrl': PF.url.make('/im/attachment'),
          'Send': oTranslations['send'],
          'ThreadId': t_id,
          'Users': t.find('.pf-im-panel-content .__thread-name').text(),
          'ThreadIdParsed': t_id_parsed,
          'Attachment': (typeof pf_im_attachment_enable !== 'undefined')
            ? '<i class="ico ico-plus item-action-btn" onclick="$Core_IM.imAttachFile(\'' + t_id + '\',\'' + t_id_parsed + '\')" title="' +
            oTranslations['add_attachment'] + ' (' + pf_im_attachment_types +
            ')"></i>'
            : '',
          'Twemoji': (typeof pf_im_twemoji_enable !== 'undefined')
            ? '<i class="ico ico-smile-o im_action_emotion item-action-btn" data-thread-id="' + t_id + '" id="im_action_emotion-' + t_id_parsed + '" data-action="' +
            PF.url.make('/emojis?id=im_chat_box-' + t_id_parsed) + '"></i>'
            : '',
          'UploadingMessage': oTranslations['uploading'] + '...'
        });
      }
      html = $('<div></div>').append(html).html();
      if (isImPopup !== -1) {
        document.title = window.pf_im_site_title;
      }

      c.css('opacity', '1');

      t_all.removeClass('is_hidden');
      if (t_all.hasClass('active')) {
        t_all.removeClass('active');
        c.hide();
        $('#pf-chat-window-active').hide();

        return false;
      }

      $('.pf-im-panel.active').removeClass('active');
      t_all.addClass('active');
      $('body').removeClass('p-im-buddy-screen');

      if (!t_id) {
        function get_thread_id(numArray) {
          numArray = numArray.sort(function (a, b) {
            return a - b;
          });

          return numArray.join(':');
        }

        t.data('thread-id',
          get_thread_id([t.data('user-id'), $Core_IM.getUser().id]));
      }

      $Core_IM.socket.emit('loadConversation', {
        user_id: $Core_IM.getUser().id,
        partner_id: $Core_IM.getPartnerId(t_id),
        thread_id: t_id,
      });

      if (c.length) {
        c.html(html).show();
      } else {
        if (window.location.href.search('/im/popup') > 0) {
          //page all message
          $('#pf-im').prepend('<span id="pf-chat-window-active"></span><div data-thread-id="' + t_id + '" class="p-im-chat-room-block"  id="pf-chat-window-' + t_id_parsed + '" >' +
          html + '</div>');
          if($Core_IM.messages_room_active){
            $('#pf-chat-window-' + $Core_IM.messages_room_active).remove();
          }
          $Core_IM.messages_room_active = t_id_parsed;
        } else {
          if ($Core_IM.is_small_media) {
            window.location.href = PF.url.make('/im/popup') + '?thread_id=' + t_id_parsed;
            return true;
          }
          //init Room Dock chat
          var dock_length_max =3;
          if (window.matchMedia('(max-width: 1200px)').matches) {
            dock_length_max = 1;
          }
          $('.p-im-AppDock-RoomList').prepend('<div id="pf-chat-window-' + t_id_parsed + '" data-thread-id="' + t_id + '" class="p-im-float-dock-item p-im-AppDock-Room p-im-chat-room-block"><div class="dock-item-outer"><div class="dock-item-inner">' +
          html + '</div></div></div>');

          $Core_IM.dock_list.push(t_id_parsed);
          if($Core_IM.dock_list.length > dock_length_max) {
            var id_remove = $Core_IM.dock_list[0];
            $Core_IM.dock_list.splice(0,1);
            $Core_IM.cookieActiveChat($Core_IM.stripThreadId(id_remove, true), true);
            $('#pf-chat-window-' + id_remove).remove();
          }
        }
      }

      $('#pf-chat-window-active').css('top', ((t.offset().top - $(window).scrollTop()) +
        (t.height() / 2)) - 5).show();
      $('#pf-chat-window-' + t_id_parsed).find('.chat-row').attr('data-thread-id', t_id);

      if (t.data('listing-id')) {
        c.find('.chat-form input').before('<div><input type="hidden" name="listing_id" id="pf_im_listing_id" value="' +
          t.data('listing-id') + '">');
      }

      var l = $('#pf-chat-window-'+ t_id_parsed +' .fa-external-link');
      l.data('action', l.data('action') + '?thread_id=' + t_id);

      $Core_IM.chatTextArea(t_id);
      $Core.loadInit();
      if (!$Core_IM.is_mobile) {
        $('#pf-chat-window-' + t_id_parsed).find('.chat-form input').trigger('focus');
      }

      if ($Core_IM.chat_form_min_height === 0) {
        $Core_IM.chat_form_min_height = $('.chat-form').height();
      }
      // update new message counter
      $Core_IM.updateNotificationCount(t_id);
      // storage active chat
      if (isImPopup === -1) {
        $Core_IM.cookieActiveChat(t_id);
      }
    });
  },

  updateChatTime: function () {
    $('.set-moment:not(.built)').each(function () {
      var t = $(this),
        time = 0,
        start = new Date();
      t.addClass('built');
      start.setHours(0, 0, 0, 0);
      if (t.data('time') > 1000000000000) {
        if (t.data('time') < start.getTime()) {
          var date = new Date(t.data('time')),
            df = new DateFormatter();
          t.html(df.formatDate(date, window.global_update_time));
          return;
        } else {
          time = t.data('time') / 1000;
        }
      } else {
        // support old version
        if (t.data('time') * 1000 < start.getTime()) {
          var date = new Date(t.data('time') * 1000),
            df = new DateFormatter();
          t.html(df.formatDate(date, window.global_update_time));
          return;
        } else {
          time = t.data('time');
        }
      }
      // support old timestamp in second, new timestamp in milisecond
      t.html($Core_IM.convertTime(time));
    });
  },

  build_thread: function (message, users) {
    var is_new = '', is_hidden = '';
    if ($('.pf-im-panel[data-thread-id="' + message.thread_id + '"]').length) return '';
    if (typeof (message.is_new) === 'string' && message.is_new === '1' &&
      (message.user != undefined && message.user.id !==
        $Core_IM.getUser().id)) {
      is_new = ' new';
    }

    if (typeof (message.is_hidden) === 'string' && message.is_hidden == '1') {
      is_hidden = ' is_hidden';
    }

    if (message.preview === '') {
      message.preview = $Core_IM.file_preview;
    }

    return $('<div></div>').append($.tmpl($Core_IM.thread_tmpl, {
      'NewHidden': is_new + is_hidden,
      'ThreadId': message.thread_id,
      'Users': users,
      'MessagePreview': (typeof (message.is_deleted) !== 'undefined' &&
      message.is_deleted
        ? '<span class="pf-im-chat-preview-text">' +
        oTranslations['this_message_has_been_deleted'] + '</span>'
        : $Core_IM.fixChatMessage(message.preview, false)),
    })).html();
  },

  start_im: function (force) {
    if (!force && ($('#admincp_base').length || !$('#auth-user').length ||
      pf_im_node_server === '' ||
      (pf_im_token === '' && $('#pf-im-host').length === 0))) {
      $Core_IM.host_failed = true;
      return;
    }

    if (typeof pf_im_using_host !== 'undefined') {
      $Core_IM.host = window.location.hostname + '@';
    }

    if ($Core_IM.socket_built === false && typeof io !== 'undefined') {
      $Core_IM.socket_built = true;
      $.ajaxSetup({cache: true});
      if (typeof (pf_im_token) === 'undefined' || pf_im_token == '') {
        $Core_IM.host_failed = true;
        return;
      }

      // connect to chat server
      $Core_IM.socket = io(pf_im_node_server, {
        query: 'token=' + pf_im_token,
      });

      $Core_IM.socket.on('host_failed', function (message) {
        $Core_IM.im_debug_mode && console.log('On host_failed');
        // destroy socket
        $Core_IM.socket.disconnect();
        // set failed message
        $Core_IM.host_failed = message;
        // hide im panel
        var im = $('#pf-im');
        if (im.length && im.is(':visible')) {
          im.hide();
          $('#pf-im-wrapper').hide();
        }
      });

      $Core_IM.socket.on('retry', function (data) {
        $Core_IM.im_debug_mode && console.log('On retry');
        if (data.retry) {
          // can retry
          $Core_IM.im_debug_mode && console.log('RETRY TO CONNECT:', 'get token');
          $.ajax({
            url: PF.url.make('/im/get-token'),
            data: 'timestamp=' + data.im_timestamp,
            success: function (token) {
              $Core_IM.im_debug_mode && console.log('RETRY TO CONNECT:', 're-verify');
              $Core_IM.socket = io(pf_im_node_server, {
                query: 'token=' + token + '&retry=1',
                forceNew: true
              });
              $Core_IM.initSocketEvents();
            },
            error: function () {
              $Core_IM.retryFailed('Unable to connect to the IM server.');
            }
          });
        } else {
          // cannot retry
          $Core_IM.im_debug_mode && console.log('CANNOT RETRY');
          $Core_IM.retryFailed(data.message);
        }
      });

      $Core_IM.initSocketEvents();

      $.ajaxSetup({cache: false});

      //Overwrite when available
      $Core.composeMessage = $Core_IM.composeMessage;
      if (typeof $Core.marketplace !== "undefined") {
        $Core.marketplace.contactSeller = $Core_IM.composeMessage;
      }

      $Core.loadInit();
    }
  },

  reloadImages: function () {
    $('.image_deferred:not(.built)').each(function () {
      var t = $(this),
        src = t.data('src'),
        i = new Image();

      t.addClass('built');
      if (!src) {
        t.addClass('no_image');
        return;
      }

      t.addClass('has_image');
      i.onerror = function () {
        t.replaceWith('');
      };
      i.onload = function () {
        t.attr('src', src);
      };
      i.src = src;
    });
  },

  insertAtCaret: function (areaId, text, focus) {
    var txtarea = document.getElementById(areaId);
    if (!txtarea) {
      return;
    }

    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0')
      ? 'ff'
      : (document.selection ? 'ie' : false));
    if (br === 'ie') {
      var range = document.selection.createRange();
      range.moveStart('character', -txtarea.value.length);
      strPos = range.text.length;
    } else if (br === 'ff') {
      strPos = txtarea.selectionStart;
    }

    var front = (txtarea.value).substring(0, strPos);
    var back = (txtarea.value).substring(strPos, txtarea.value.length);
    txtarea.value = front + text + back;
    strPos = strPos + text.length;
    if (br === 'ie') {
      var ieRange = document.selection.createRange();
      ieRange.moveStart('character', -txtarea.value.length);
      ieRange.moveStart('character', strPos);
      ieRange.moveEnd('character', 0);
      ieRange.select();
    } else if (br === 'ff') {
      txtarea.selectionStart = strPos;
      txtarea.selectionEnd = strPos;
    }
    if (focus) {
      txtarea.focus();
    }
    $(txtarea).trigger('input');
    txtarea.scrollTop = scrollPos;
  },

  initDropzone: function (thread_id, thread_parsed_id) {
    if ($('#im_attachment-' + thread_parsed_id).length !== 0) {
      this.dropzoneInit[thread_id] = new Dropzone('div#pf-chat-window-' + thread_parsed_id + ' .chat-row', {
        maxFiles: 1,
        url: PF.url.make('/im/attachment'),
        clickable: '#im_attachment-' + thread_parsed_id,
        addedfile: function (file) {
          var clickAble = this.clickableElements[0],
          thread_parsed_id = $(clickAble).data('thread-parsed-id'),
          holder = $('#pf-chat-window-' + thread_parsed_id);
          holder.find('.chat-attachment-preview').css('display', 'inline-flex');
          $('#chat-attachment-file-name-' + thread_parsed_id).text(file.name);
          holder.find('.chat-attachment-preview-uploading').show();
        },
        success: function (file, res) {
          var clickAble = this.clickableElements[0],
            thread_parsed_id = $(clickAble).data('thread-parsed-id'),
            holder = $('#pf-chat-window-' + thread_parsed_id);
          if (res === '') {
            // upload failed
            $('#chat-attachment-result-' + thread_parsed_id).text(oTranslations['im_failed']);
          } else {
            holder.find('.chat-form textarea').data('attachment-id', res.id);
            $('#chat-attachment-result-' + thread_parsed_id).empty();
          }
          holder.find('.chat-attachment-preview-uploading').hide();
          $('#chat-attachment-file-name-' + thread_parsed_id).show();
          this.removeAllFiles();
        },
      });
    }
  },

  sortNumber: function (a, b) {
    return a - b;
  },

  notificationIsEnabled: function (notification) {
    notification = notification.split(':');
    return (notification.indexOf($Core_IM.getUser().id.toString()) !== -1);
  },

  checkKeyUp: function (keycode) {
    return (keycode > 47 && keycode < 58) || // number keys
      keycode === 32 || keycode === 13 || // spacebar & return key(s) (if you
      // want to allow carriage returns)
      (keycode > 64 && keycode < 91) || // letter keys
      (keycode > 95 && keycode < 112) || // numpad keys
      (keycode > 185 && keycode < 193) || // ;=,-./` (in order)
      (keycode > 218 && keycode < 223) || // [\]' (in order)
      keycode === 8;                      // backspace
  },

  convertTime: function (timestamp) {
    if (timestamp === 0) {
      return false;
    }
    var n = new Date(),
      now = Math.round(n.getTime() / 1000),
      iSeconds = Math.round(now - timestamp),
      iMinutes = Math.round(iSeconds / 60),
      hour = Math.round(parseFloat(iMinutes) / 60.0);
    if (hour >= 48) {
      return false;
    }
    if (iMinutes < 1) {
      return oTranslations['just_now'];
    }
    if (iMinutes < 60) {
      if (iMinutes === 0 || iMinutes === 1) {
        return oTranslations['a_minute_ago'];
      }
      return iMinutes + ' ' + oTranslations['minutes_ago'];
    }
    if (hour < 24) {
      if (hour === 0 || hour === 1) {
        return oTranslations['a_hour_ago'];
      }
      return hour + ' ' + oTranslations['hours_ago'];
    }
  },

  submitChat: function (id) {
    var id_parsed = $Core_IM.stripThreadId(id),
      t = $('.p-im-chat-room-block[data-thread-id="' + id + '"] #im_chat_box-' + id_parsed),
      panel = $('#pf-chat-window-' + id_parsed),
      c = panel.find('.chat-row'),
      timeNow = Math.floor(Date.now()),
      l = $('#pf_im_listing_id'),
      attachment_id = (typeof t.data('attachment-id') === 'undefined')
        ? 0
        : parseInt(t.data('attachment-id')) || 0,
      text = $Core_IM.preventXss($Core_IM.filterWords(trim(t.val())));
    if (text.length <= 0 && attachment_id === 0) {
      return;
    }

    $Core_IM.im_debug_mode && console.log('Submit...', text);
    c.append($Core_IM.buildMessage({
      text: text,
      time_stamp: timeNow,
      attachment_id: t.data('attachment-id'),
      user: {
        photo_link: $Core_IM.getUser().photo_link,
        id: $Core_IM.getUser().id,
      },
    }, false, true));

    $Core_IM.updateChatTime();
    c.scrollTop(c[0].scrollHeight);

    var receiver = $('.pf-im-panel[data-thread-id="' + id + '"] a');
    $Core_IM.socket.emit('chat', {
      text: text,
      user: $Core_IM.getUser(),
      receiver: {
        'id': $Core_IM.getPartnerId(id),
        'name': receiver.attr('title'),
        'photo_link': receiver.clone().wrap('<div></div>').parent().html(),
      },
      time_stamp: timeNow,
      thread_id: id,
      attachment_id: t.data('attachment-id'),
      listing_id: (l.length ? l.val() : 0),
      deleted: false,
    });
    $Core_IM.updateChatPreview(id, text);

    t.data('attachment-id', '');
    t.val('').trigger('focus');
    // hide attachment preview
    panel.find('.chat-attachment-preview').hide();
    $Core.loadInit();
    $Core_IM.init();
  },

  threadMessageCounter: function (thread_id) {
    var thread = $('.pf-im-panel[data-thread-id="' + thread_id + '"]'),
      thread_first = $('.pf-im-panel[data-thread-id="' + thread_id + '"]:first')
    badge = thread.find('.badge'),
      badge_first = thread_first.find('.badge'),
      message_counter = parseInt(badge_first.text()) || 0;

    !thread.hasClass('new') && thread.addClass('new');
    badge.text(message_counter + 1);
  },

  emoji: {
    init: function (thread_id) {
      var thread_parsed = $Core_IM.stripThreadId(thread_id),
        im = $('#pf-chat-window-' + thread_parsed),
        emoji_list = im.find('.emoji-list li');
      im.find('.emoji-list li').off('click').on('click', function () {
        var emoji = $(this).find('span').text();
        $Core_IM.insertAtCaret('im_chat_box-' + thread_parsed, emoji, false);
        //Don't auto hide
        // $('.chat-form-actions').hide();
      });
      if (!emoji_list.hasClass('dont-unbind')) {
        emoji_list.addClass('dont-unbind');
      }
    },
    hide: function () {
      $('.chat-form-actions').hide();
      $('.chat-form-actions-arrow').hide();
    },
    show: function (url, thread_id) {
      var holder = $('#pf-chat-window-' + $Core_IM.stripThreadId(thread_id)),
        c = holder.find('.chat-form-actions');
      c.show();
      c.html('<i class="fa fa-spinner fa-pulse"></i>').css('bottom', '0px').show().animate({
        bottom: '45px',
      });
      holder.find('.chat-form-actions-arrow').show();
      $.ajax({
        url: url,
        type: 'GET',
        contentType: 'application/json',
        success: function (e) {
          lastEmojiObject = $('.chat-form input.form-control');
          c.html(e.content);
          $Core_IM.emoji.init(thread_id);
        },
      });
    },
  },

  retryFailed: function (message) {
    $Core_IM.im_debug_mode && console.log('RETRY TO CONNECT:', 'connect failed');

    var im = $('#pf-im');
    if (typeof message !== 'undefined') {
      $Core_IM.host_failed = message;
    }
    // hide im panel
    if (im.length && im.is(':visible')) {
      im.hide();
      $('#pf-im-wrapper').hide();
      // show alert
      window.parent.sCustomMessageString = $Core_IM.host_failed;
      tb_show(oTranslations['error'],
        $.ajaxBox('core.message', 'height=150&width=300'));
    }
  },

  initSocketEvents: function () {
    $Core_IM.socket.on('connect_successfully', function () {
      $Core_IM.im_debug_mode && console.log('connect_successfully');
      // clear threads
      $('.pf-im-main').empty();
      // load threads
      $Core_IM.socket.emit('loadThreads', $Core_IM.getUser().id,
        pf_total_conversations);
      // unlock search field
      var input = $('._pf_im_friend_search').find('input');
      input.length && input.attr('readonly', false);
      // reset error message
      $Core_IM.host_failed = false;
    });

    $Core_IM.socket.on('loadThreads', function (thread) {
      $Core_IM.im_debug_mode && console.log('On loadThreads');
      $Core_IM.thread_cnt++;
      
      thread = JSON.parse(thread);
      
      if (isset(thread.is_hidden) && thread.is_hidden === '1') {
        $Core_IM.im_debug_mode && console.log('no new count...');
      } else {
        $Core_IM.thread_show++;
      }
      
      // in case thread to show
      if (pf_total_conversations !== '0' && pf_total_conversations !== '' &&
      $Core_IM.thread_show > pf_total_conversations && !$Core_IM.is_load_more) {
        thread.is_hidden = '1';
      }
      
      var users = $Core_IM.users.split(',');
      for (var i in thread.users) {
        (users.indexOf(thread.users[i]) === -1) &&
        users.push(thread.users[i]);
      }
      $Core_IM.users = users.join();
      $('.pf-im-main').append($Core_IM.build_thread(thread, thread.users.join()));
      
      $Core_IM.socket.emit('update_new', $Core_IM.getPartnerId(thread.thread_id), thread.thread_id);
    });
    
    $Core_IM.socket.on('lastThread', function (thread, threadLength) {
      $Core_IM.im_debug_mode && console.log('On lastThread');
      var get_friends = -1;
      $Core_IM.is_load_more = false;
      var showLoadMore = false;
      // get friends
      if (pf_total_conversations === '0' || pf_total_conversations === '') {
        get_friends = 0;
      } else {
        get_friends = (pf_total_conversations - $Core_IM.thread_show > 0)
        ? pf_total_conversations - $Core_IM.thread_show
        : -1;
        if (get_friends <= 0 && thread !== undefined && threadLength !== undefined && $('.pf-im-main .pf-im-panel').length < parseInt(threadLength)) {
          showLoadMore = true;
        }
      }
      // get avatar of users
      $.ajax({
        url: PF.url.make('/im/panel'),
        data: 'users=' + $Core_IM.users,
        contentType: 'application/json',
        'success': function (e) {
          $('.pf-im-panel').each(function () {
            var t = $(this),
            names = t.data('thread-id').split(':');
            for (var i in names) {
              var n = names[i];
              if (!n) {
                continue;
              }
              if (typeof (e[n]) === 'object') {
                var u = e[n];
                if (u.id == $Core_IM.getUser().id) {
                  continue;
                }
                // check for blocked users
                if (typeof pf_im_blocked_users !== 'undefined' && pf_im_blocked_users.indexOf(u.id) !== -1) {
                  t.find('.pf-im-panel-image').html($.tmpl($Core_IM.invalid_user_tmpl, {
                    'UserName': oTranslations['invalid_user'],
                    'ShortName': 'IU',
                  }));
                  t.find('.__thread-name').html(oTranslations['invalid_user']);
                  t.attr('data-user-blocked', '1');
                } else {
                  t.find('.pf-im-panel-image').html($Core_IM.addTargetBlank(u.photo_link));
                  t.find('.__thread-name').html(u.name);
                }
                
                // banned user
                if (typeof e[n].is_banned !== 'undefined' && e[n].is_banned) {
                  t.attr('data-user-banned', '1');
                }
                //no more friend
                if (typeof e[n].is_friend !== 'undefined' && !e[n].is_friend) {
                  t.attr('data-not-friend', '1');
                }
              } else if (e[n] === false) {
                $Core_IM.deleted_users.push(n);
                t.find('.pf-im-panel-image').html($.tmpl($Core_IM.deleted_user_tmpl, {
                  'UserName': oTranslations['deleted_user'],
                  'ShortName': 'DU',
                }));
                t.find('.__thread-name').html(oTranslations['deleted_user']);
                t.attr('data-user-deleted', '1');
              } else {
                $Core_IM.socket.emit('deleteUser', n);
              }
            }
            
            t.show();
            if (showLoadMore) {
              $('.pf-im-main .pf-im-more-conversation').remove();
              $('.pf-im-main').append('<div class="pf-im-more-conversation">' + oTranslations['im_load_more'] + '</div>');
            } else {
              $('.pf-im-main .pf-im-more-conversation').remove();
            }
          });
          
          $('#pf-im > i').remove();
          $Core_IM.updateChatTime();
          if (get_friends < 0) {
            $('#pf-im .fa-spin,.js_p_im_loading_buddy_visible').hide();
          }
          $Core.loadInit();
        },
      });

      if (get_friends >= 0) {
        $.ajax({
          url: PF.url.make('/im/friends'),
          data: 'limit=' + (get_friends > 0 ? get_friends : 0) + '&threads=' +
            $Core_IM.users,
          contentType: 'application/json',
          'success': function (e) {
            $('.pf-im-main').append(e);
            $Core.loadInit();
            $('#pf-im .fa-spinner,.js_p_im_loading_buddy_visible').hide();
          },
        });
      }
      // update message counter
      if (typeof thread !== 'undefined') {
        $Core_IM.socket.emit('update_new',
          $Core_IM.getPartnerId(thread.thread_id), thread.thread_id, true);
      }
    });

    $Core_IM.socket.on('hiddenThread', function (thread_id) {
      $Core_IM.users = $Core_IM.users.split(',').concat(thread_id.split(':')).join();
    });

    $Core_IM.socket.on('failed', function (data) {
      $('.chat-action-delete').trigger('click');
      $('.pf-im-panel[data-thread-id="' + data.thread + '"]').remove();
      var popup = $('<a class="popup" href="' + PF.url.make('/im/failed') +
        '"></a>');
      tb_show('', PF.url.make('/im/failed'), popup);
      $('#pf-im').hide();
      $('body').css('position', 'initial')
    });

    $Core_IM.socket.on('loadNewConversation', function (thread) {
      $Core_IM.im_debug_mode && console.log('On loadNewConversation');

      // add image of users on conversation
      var users = thread.thread_id.split(':').filter(function (v) {
        return v !== '';
      });
      if (users.length < 2) {
        return;
      }

      $.ajax({
        url: PF.url.make('/im/panel'),
        data: 'users=' + users.join(),
        contentType: 'application/json',
        'success': function (e) {
          for (var i in e) {
            var u = e[i];
            if (u === false) {
              $.tmpl($Core_IM.deleted_user_tmpl, {
                'UserName': oTranslations['deleted_user'],
                'ShortName': 'DU',
              }).prependTo('.chat-row-users');
            } else {
              if ($Core_IM.getUser().id !== u.id) {
                $('#pf-chat-window-' + $Core_IM.stripThreadId(thread.thread_id) +' .chat-row-users').html('<span class="item-chat-user-name">' + $Core_IM.addTargetBlank(u.name_link) + '</span>');
              }
            }
          }
          $('.pf-chat-window-loading').remove();

          $Core_IM.reloadImages();
          $Core.loadInit();
        },
      });
    });

    $Core_IM.socket.on('loadSearchPreview', function (message) {
      $Core_IM.im_debug_mode && console.log('On loadSearchPreview');
      // update search preview text
      var users = message.thread_id.split(':');
      for (var i = 0; i < users.length; i++) {
        if (users[i] == $Core_IM.getUser().id) {
          continue;
        }
        if (message.deleted) {
          message.text = oTranslations['this_message_has_been_deleted'];
        }
        if (message.text === '') {
          message.text = $Core_IM.file_preview;
        }
        var preview = $('.pf-im-panel[data-friend-id="' + users[i] + '"]').find('.pf-im-panel-preview');
        preview.removeClass('twa_built');
        preview.text(message.text);
        $Core.loadInit();
      }
    });

    $Core_IM.socket.on('loadConversation', function (threads, thread_id) {
      $Core_IM.im_debug_mode && console.log('loadConversation', thread_id);
      var u = '',
        thread_ele_id = '#pf-chat-window-' + $Core_IM.stripThreadId(thread_id),
        thread_holder = $(thread_ele_id),
        c = thread_holder.find('.chat-row'),
        cache = {},
        iteration = false;

      // This case is newly chat or load more but have no messages
      if (threads.length === 0) {
        $('.pf-chat-row-loading').remove();
        c.off('scroll');
        return;
      }

      if ($Core_IM.load_first_time) {
        $.tmpl($Core_IM.loading_conversation_tmpl, {
          'LoadingConversation': oTranslations['loading_conversation'],
        }).prependTo(c);
      }

      threads.reverse();
      if(c.length) {
        $Core_IM.scrollBottom = c[0].scrollHeight - c[0].scrollTop;
      }
      for (var i in threads) {
        var thread = JSON.parse(threads[i]);

        if (!iteration) {
          iteration = true;
          var k = thread.thread_id.split(':');
          for (var i2 in k) {
            if (typeof (cache[k[i2]]) !== 'string') {
              cache[k[i2]] = '1';
              u += k[i2] + ',';
            }
          }
        }

        c.prepend($Core_IM.buildMessage(thread));
      }
      $Core.loadInit();

      $.ajax({
        url: PF.url.make('/im/panel'),
        data: 'users=' + u,
        contentType: 'application/json',
        'success': function (e) {
          $('.pf-chat-message').each(function () {
            var t = $(this), id = t.data('user-id'), u = e[id];
            if (typeof u !== 'undefined' && typeof u.photo_link !== 'undefined' &&
              (typeof pf_im_blocked_users === 'undefined' ||
                pf_im_blocked_users.indexOf(id) === -1)) {
              t.find('.pf-chat-image').html($Core_IM.addTargetBlank(u.photo_link));
            }
            $Core_IM.updateChatTime();

            t.show();
          });
          var c = thread_holder.find('.chat-row'), chat_row_users = thread_holder.find('.chat-row-users'),
            chat_form = thread_holder.find('.chat-form');

          c.show();
          if ($Core_IM.load_first_time || chat_row_users.html() === '' || !chat_row_users.find('span').length) {
            for (var i in e) {
              var u = e[i];
              if (u === false) {
                chat_row_users.html('<span class="item-chat-user-name">' + oTranslations['deleted_user'] + '</span>');
                chat_form.html('<p class="p-im-info-gray">' + oTranslations['you_cannot_reply_this_conversation'] + '</p>');
              } else if (typeof pf_im_blocked_users !== 'undefined' && pf_im_blocked_users.indexOf(u.id) !== -1) {
                chat_row_users.html('<span class="item-chat-user-name">' + oTranslations['invalid_user'] + '</span>');
                chat_form.html('<p class="p-im-info-gray">' + oTranslations['you_cannot_reply_this_conversation'] + '</p>');
              } else if (!u.is_friend || u.is_banned) {
                chat_row_users.html('<span class="item-chat-user-name">' + $Core_IM.addTargetBlank(u.name_link) + '</span>');
                chat_form.html('<p class="p-im-info-gray">' + oTranslations['you_cannot_reply_this_conversation'] + '</p>');
              } else if (u.name_link !== 'undefined' && $Core_IM.getUser().id !== u.id) {
                chat_row_users.html('<span class="item-chat-user-name">' + $Core_IM.addTargetBlank(u.name_link) + '</span>');
              }
            }
            if (c.length) {
              c.scrollTop(c[0].scrollHeight);
            }
            $('.pf-chat-window-loading').remove();
            $Core_IM.load_first_time = false;
          } else {
            if(c.length) {
              c.scrollTop(c[0].scrollHeight - $Core_IM.scrollBottom);
            }
            $('.pf-chat-row-loading').remove();
          }

          $Core_IM.reloadImages();
          $Core.loadInit();
        }
      });
      if ($Core_IM.new_message) {
        $Core_IM.insertAtCaret('im_chat_box-' + thread_id, $Core_IM.new_message, true);
        $Core_IM.new_message = null;
      }
    });

    $Core_IM.socket.on('loadNotification', function (notification_enable, thread_id) {
      if (notification_enable === false) {
        $('#chat-action-noti-'+ $Core_IM.stripThreadId(thread_id) +' .ico').attr('class', 'ico ico-bell2-off-o');
      }
    });

    $Core_IM.socket.on('chat_delete', function (key, id) {
      var message = $('#' + key);
      if (message.length === 1) {
        message.find('.pf-im-chat-text').html('<i>' + oTranslations['this_message_has_been_deleted'] +
          '</i>');
        message.find('.pf-im-chat-link').parent().remove();
        message.find('.im_attachment').remove();
      }
      $('.pf-im-panel[data-thread-id="' + id + '"]').find('.pf-im-panel-preview').text(oTranslations['this_message_has_been_deleted']);
    });

    $Core_IM.socket.on($Core_IM.host + 'chat', function (chat) {
      var sameUser = chat.user.id === $Core_IM.getUser().id,
        isImPopup = window.location.href.search('/im/popup'),
        t_id = chat.thread_id ? chat.thread_id : chat.notification,
        c = $('#pf-chat-window-' + $Core_IM.stripThreadId(t_id)).find('.chat-row'),
        total_new = 0;
      if (chat.user.id !== $Core_IM.getUser().id &&
        chat.thread_id.indexOf($Core_IM.getUser().id) !== -1 &&
        $Core_IM.notificationIsEnabled(chat.notification)) {
        var sound = $('#pf-im-notification-sound').get(0);
        sound.volume = 0.5;
        sound.play();
      }
      if (chat.user.id !== $Core_IM.getUser().id &&
        chat.thread_id.indexOf($Core_IM.getUser().id) !== -1 &&
        (isImPopup !== -1)) {
        document.title = '(' + chat.new + ') ' + chat.user.name + ' ' +
          oTranslations['messaged_you'];
      }

      var users = chat.thread_id.split(':'), total_friends = 0;
      for (var i in users) {
        if ($Core_IM.getUser().id == users[i]) {
          total_friends++;
        }
      }

      if (!total_friends) {
        $Core_IM.im_debug_mode &&
        console.log('Unable to chat with this user.');
        return;
      }

      if (!$('#pf-im').is(':visible')) {
        $Core_IM.im_debug_mode && console.log('not visible...');
      }

      // newly chat
      var chat_row = $('.chat-row[data-thread-id="' + chat.thread_id + '"]'),
      chat_form = $('#pf-chat-window-' + $Core_IM.stripThreadId(chat.thread_id)).find('.pf-im-chat-bottom-input-form textarea');
      if (
        (!chat_row.length) ||
        (chat_row.length && !chat_row.is(':visible')) ||
        (chat_form.length && !chat_form.is(':focus'))
      ) {
        $Core_IM.im_debug_mode &&
        console.log('thread does not exist: ' + chat.thread_id);
        if (chat.text === '') {
          chat.text = $Core_IM.file_preview;
        }
        if (!$('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]').length) {

          $Core_IM.im_debug_mode &&
          console.log('does not exist in panel either: ' + chat.thread_id);
          var html;
          if (sameUser) {
            html = '<div class="pf-im-panel" data-user-id="' +
              chat.receiver.id + '" data-thread-id="' + chat.thread_id +
              '">' +
              '<div class="item-outer">' +
              '<div class="pf-im-panel-image">' + chat.receiver.photo_link +
              '</div>' +
              '<div class="pf-im-panel-content">' + chat.receiver.name +
              '<div class="pf-im-panel-preview">' +
              ((typeof (chat.deleted) !== 'undefined' && chat.deleted)
                ? '<i>' + oTranslations['this_message_has_been_deleted'] +
                '</i>'
                : $Core_IM.fixChatMessage(chat.text, false)) +
              '</div></div>' +
              '<div class="pf-im-panel-info"><span class="badge"></span></div></div>' +
              '</div>';
          } else {
            html = '<div class="pf-im-panel new" data-user-id="' +
              chat.user.id + '" data-thread-id="' + chat.thread_id + '">' +
              '<div class="item-outer">' +
              '<div class="pf-im-panel-image">' + chat.user.photo_link +
              '</div>' +
              '<div class="pf-im-panel-content">' + chat.user.name +
              '<div class="pf-im-panel-preview">' +
              ((typeof (chat.deleted) !== 'undefined' && chat.deleted)
                ? '<i>' + oTranslations['this_message_has_been_deleted'] +
                '</i>'
                : $Core_IM.fixChatMessage(chat.text, false)) +
              '</div></div>' +
              '<div class="pf-im-panel-info"><span class="badge"></span></div>' +
              '</div>' +
              '<div></div>';
          }
          $('.pf-im-main').prepend(html);
          $Core_IM.updateChatTime();
        }

        var panel = $('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]'),
          old_panel_first = $('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]:first'),
          t = old_panel_first.clone();
        panel.remove();
        //t.prependTo('.pf-im-main');

        if (typeof (chat.deleted) !== 'undefined' && !chat.deleted) {
          // update preview message on left side chat
          var preview = t.find('.pf-im-panel-preview');
          preview.html($Core_IM.fixChatMessage(chat.text, false));
          preview.removeClass('twa_built');

        }
        if (!sameUser) {
          if ($Core_IM.notificationIsEnabled(chat.thread_id)) {
            t.addClass('count');
          }
        }
        t.prependTo('.pf-im-main');
        if (!sameUser) {
          // update counter
          $Core_IM.threadMessageCounter(chat.thread_id);

        }
        $Core.loadInit();
        total_new = $('.p-im-DropdownMessageWrapper .pf-im-panel.count').length;
        if (!sameUser && total_new &&
          $Core_IM.notificationIsEnabled(chat.thread_id)) {
          $('span#js_total_new_messages').html(total_new).show();
        }
        if (!chat_form.length) return;
      }

      var pre = $('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]').find('.pf-im-panel-preview');
      if (typeof (chat.deleted) !== 'undefined' && !chat.deleted) {
        pre.removeClass('twa_built');
        pre.html($Core_IM.fixChatMessage(chat.text, false));
      } else {
        pre.html(oTranslations['this_message_has_been_deleted']);
      }
      c.append($Core_IM.buildMessage(chat, false, true));
      $Core_IM.updateChatTime();
      setTimeout(function(){
        c.scrollTop(c[0].scrollHeight);
      }, 100);
      $Core.loadInit();
    });

    $Core_IM.socket.on('search_message', function (result, index) {
      var pisr = $('.pf-im-search-result');
      (index == 0) && pisr.empty();
      for (var i = 0; i < result.length; i++) {
        var message = JSON.parse(result[i]),
          html = $Core_IM.buildMessage(message, false, true, true);
        pisr.append(html);
      }
      if (pisr.is(':empty')) {
        pisr.append('<span class="pf-im-no-message">' +
          oTranslations['no_message'] + '</span>');
      }
      $Core_IM.updateChatTime();
      $Core.loadInit();
    });

    $Core_IM.socket.on('update_new', function (thread, total, is_last) {
      $Core_IM.im_debug_mode && console.log('On update_new');
      thread = JSON.parse(thread);
      var thread_id = thread.thread_id,
        notification;
      if (typeof thread.notification === 'undefined') {
        notification = thread.thread_id.split(':');
        $Core_IM.socket.emit('add_notification', thread.thread_id);
      } else {
        notification = thread.notification.split(':');
      }
      if ($('.chat-row[data-thread-id="' + thread_id + '"]').length === 0) {
        var p = $('.pf-im-panel[data-thread-id="' + thread_id + '"]');
        if (parseInt(total) > 0) {
          p.find('.badge').text(total);
          if (!p.hasClass('new')) {
            p.addClass('new');
            if (notification.indexOf($Core_IM.getUser().id.toString()) !==
              -1) {
              p.addClass('count');
              // update message counter last
              if (typeof is_last !== 'undefined') {
                $('span#js_total_new_messages').html($('.pf-im-panel.count').length).show();
              }
            }
            if ((parseInt(total) > 0 || 0) > 0) {
              p.find('.badge').text(total);
            }
          }
        } else {
          p.removeClass('new');
        }
      }
      if (total && $Core_IM.searching && $('.pf-im-search-user .pf-im-panel[data-friend-id="' + $Core_IM.getPartnerId(thread_id) + '"]').length) {
        var searchPanel = $('.pf-im-search-user .pf-im-panel[data-friend-id="' + $Core_IM.getPartnerId(thread_id) + '"]');
        if (searchPanel.find('.badge').length) {
          searchPanel.find('.badge').text(total);
        } else {
          searchPanel.append('<div class="pf-im-panel-info"><span class="badge">' + total + '</span></div>');
        }
        searchPanel.addClass('new');
      }
    });

    $Core_IM.socket.on($Core_IM.host + 'resetCounterAndTitle',
      function (user_id, thread_id) {
        if (user_id == $Core_IM.getUser().id) {
          // reset counter
          var panel = $('.pf-im-panel[data-thread-id="' + thread_id + '"]'),
            badge = panel.find('.badge'),
            isImPopup = window.location.href.search('/im/popup');

          badge.text('0');
          panel.removeClass('new');
          // reset title
          if (isImPopup !== -1) {
            document.title = window.pf_im_site_title;
          }
        }
      });
  },
  stripThreadId: function(id, isReverse) {
    if (id) {
      if (isReverse) {
        id = id.replace(/_{2}/g, '=');
        id = id.replace(/-/g, ':');
      } else {
        id = id.replace(/:/g, '_');
        id = id.replace(/=/g, '__');
      }
    }
    return id;
  }
};

var $Core_IM_Firebase = {
  IM_Firebase: null,
  thread_cnt: 0,
  thread_total: 0,
  thread_show: 0,
  users: null,
  users_data: {},
  sound_path: '',
  current_user: null,
  usersCollectionRef: null,
  currentUserDocRef: null,
  messageCollectionRef: null,
  roomCollectionRef: null,
  notificationCollectionRef: null,
  load_first_time: false,
  deleted_users: [],
  last_loaded_message: {},
  thread_notification: {},
  current_rooms: {},
  hidden_current_rooms: {},
  is_load_more: false,
  unread_count: {},
  algolia_index: null,
  last_search_thread: {},
  noticed_threads: {},
  noticed_messages: {},
  init_first_time: true,
  init_step_3: false,
  unseen_messages: {},
  core_tmpl:
    '<span id="pf-im-total-messages">0</span>' +
    '<div id="pf-open-im"><i class="fa fa-comments"/></div>' +
    '<div id="pf-im-wrapper"></div>' +
    '<div id="pf-im">' +
    '<div class="p-im-chat-room-block p-im-chat-room-block-empty">' +
    '<div class="p-im-ChatAllMessageEmpty">' +
        '<i class="ico ico-comment-square-o"/>' +
        '<div class="item-title">' + oTranslations['im_pick_a_contact_from_the_list_and_start_your_conversation'] +
        '</div>' +
      '</div>' +
    '</div>' +
    '<div class="p-im-sidebar-wrapper">' +
    '<div class="pf-im-title">' +
    '${Title}' +
    '<span class="close-im-window" title="${CloseChatBox}"><i class="fa fa-times" aria-hidden="true"/></span>' +
    '<span class="popup-im-window" title="${OpenNewTab}"><i class="fa fa-external-link" aria-hidden="true"/></span>' +
    '</div>' +
    '<div class="p-im-friend-search-wrapper">' +
    '<div class="p-im-friend-search _pf_im_friend_search">' +
    '<i class="fa fa-spinner fa-pulse" style="display: none;"/><i class="fa fa-search"/><input type="text" name="user" autocomplete="off" placeholder="${SearchFriendPlaceholder}" readonly="true">' +
    '</div>' +
    '</div>' +
    '<div class="p-im-main-wrapper">'+
    '<div class="p-im-main pf-im-main"></div>' +
    '<div class="pf-im-search-user" style="display: none;"></div>' +
    '<div class="p-im-loading-wrapper js_p_im_loading_buddy_visible">' +
    '<i class="fa fa-spinner fa-pulse"/>' +
    '</div>' +
    '</div>' +
    '</div>'+
    '<audio id="pf-im-notification-sound" src="${SoundPath}" autostart="false" ></audio>' +
    '</div>',
  dock_message_tmpl:
    '<div class="p-im-AppDock">' +
    '<div class="p-im-AppDock-outer">' +
    '<div class="p-im-float-dock-item p-im-AppDock-BuddyList dock-item-collapsed">' +
    '<div class="dock-item-outer">' +
    '<div class="dock-item-inner">' +
    '<div class="p-im-dock-header js_p_im_dock_header dont-unbind js_p_im_main_dock">' +
    '<div class="item-title">' +
    '${Title}' +
    '</div>' +
    '<span class="chat-row-close">' +
    '<span class="item-action-btn js_p_im_toggle_search_dock" >' +
    '<i class="ico ico-search-o"/>' +
    '</span>' +
    '<span class="p-im-close-dock-item item-action-btn js_p_im_minimize_dock">' +
    '<i class="ico ico-minus"/>' +
    '</span>' +
    '</span>' +
    '</div>' +
    '<div class="p-im-dock-search">' +
    '<div class="p-im-dock-search_item _pf_im_friend_search">' +
    '<i class="fa fa-spinner fa-pulse" style="display: none;"/><i class="fa fa-search"/><input type="text" name="user" autocomplete="off" placeholder="${SearchFriendPlaceholder}" readonly="true">' +
    '</div>' +
    '</div>' +
    '<div class="p-im-dock-body">' +
    '<div class="item-buddy-list pf-im-main"></div>' +
    '<div class="pf-im-search-user item-buddy-list" style="display: none;"></div>' +
    '<div class="p-im-loading-wrapper js_p_im_loading_buddy_visible">' +
    '<i class="fa fa-spinner fa-pulse"/>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '<div class="p-im-AppDock-RoomList dont-unbind-children">' +
    '</div>' +
    '</div>' +
    '</div>' +
    '<audio id="pf-im-notification-sound" src="${SoundPath}" autostart="false" />',
  init: function () {
    $Core_IM.im_debug_mode && console.log('init() firebase');
    var _this = $Core_IM_Firebase;
    var u = $('#auth-user'),
      im = $('#pf-im');
    var im_dock = $('.p-im-AppDock');

    if ($('#admincp_base').length || !u.length || typeof firebaseConfig === "undefined") {
      $Core_IM.host_failed = true;
      return;
    }

    _this.thread_cnt = 0;
    _this.thread_total = 0;
    _this.users = '';

    $(document).off('click', '[data-cmd="core-im"]').on('click', '[data-cmd="core-im"]', function (evt) {
      var t = $(this),
        action = t.data('action');
      if ($Core_IM.cmds.hasOwnProperty(action) &&
        typeof $Core_IM.cmds[action] === 'function') {
        $Core_IM.cmds[action](t, evt);
      }
    });

    $(document).off('click', '.pf_chat_delete_message').on('click', '.pf_chat_delete_message', function () {
      var t = $(this),
        message = t.closest('.pf-chat-message'),
        message_id = message.data('message-id'),
        thread_id = t.closest('.chat-row').data('thread-id');
      t.hide();
      if (message.is(':last-child')) {
        $('.pf-im-panel[data-thread-id="' + thread_id + '"]').find('.pf-im-panel-preview').text(oTranslations['this_message_has_been_deleted']);
      }

      // remove attachment
      t.siblings('.im_attachment').remove();
      _this.roomCollectionRef.doc(thread_id).collection('messages')
        .doc(message_id).set({deleted: true}, {merge: true});

      if (_this.algolia_index) {
        _this.algolia_index.deleteObject(message_id).then(function () {
          $Core_IM.im_debug_mode && console.log('Firebase object deleted from Algolia', message_id);
        }).catch(function () {
          console.error('Error when deleting contact from Algolia', error);
        });
      }

      //Delete preview message
      _this.roomCollectionRef.doc(thread_id).get().then(function (doc) {
        if (doc.exists) {
          var data = doc.data()
          if (data.preview_id === message_id) {
            _this.roomCollectionRef.doc(thread_id).set({
              preview_deleted: true
            }, {merge: true})
          }
        }
      });
      _this.currentUserDocRef.get().then(function (doc) {
        if (doc.exists) {
          var data = doc.data()
          if (data.rooms && data.rooms[thread_id] && data.rooms[thread_id]['preview_id'] === message_id) {
            var room_update = {};
            room_update[thread_id] = data.rooms[thread_id];
            room_update[thread_id].preview_deleted = true;
            _this.currentUserDocRef.set({rooms: room_update}, {merge: true})
          }
        }
      });
      _this.usersCollectionRef.doc(thread_id.replace(_this.current_user.uid, '')).get().then(function (doc) {
        if (doc.exists) {
          var data = doc.data()
          if (data.rooms && data.rooms[thread_id] && data.rooms[thread_id]['preview_id'] === message_id) {
            var room_update = {};
            room_update[thread_id] = data.rooms[thread_id];
            room_update[thread_id].preview_deleted = true;
            _this.usersCollectionRef.doc(thread_id.replace(_this.current_user.uid, '')).set({rooms: room_update}, {merge: true})
          }
        }
      });

      if (message.length === 1) {
        message.find('.pf-im-chat-text').html('<i>' + oTranslations['this_message_has_been_deleted'] +
          '</i>');
        message.find('.pf-im-chat-link').parent().remove();
        message.find('.im_attachment').remove();
      }
      return false;
    });

    if (typeof (twemoji_selectors) !== 'undefined') {
      twemoji_selectors += ', .pf-chat-body, .pf-im-panel-preview';
    }

    // remove attachment
    $(document).off('click', '.chat-attachment-remove').on('click', '.chat-attachment-remove', function () {
      var thread_parsed_id = $(this).data('thread-parsed-id'),
        textarea = $('#pf-chat-window-' + thread_parsed_id).find('.chat-form .p-im-contenteditable'),
        attachment_id = textarea.data('attachment-id');
      $(this).parent('.chat-attachment-preview').hide();
      if (typeof attachment_id !== 'undefined' && attachment_id > 0) {
        $Core_IM.im_debug_mode &&
        console.log('Remove attachment ' + attachment_id);
        // remove attachment in server
        $.ajaxCall('attachment.delete', 'id=' + attachment_id);
        // remove attachment id in textarea
        textarea.removeData('attachment-id');
      }
    });

    _this.sound_path = (typeof (pf_im_custom_sound) !== 'undefined')
      ? pf_im_custom_sound
      : PF.url.make('/PF.Site/Apps/core-im/assets/sounds/noti.wav').replace('/index.php/', '/');
    _this.sound_path = _this.sound_path.indexOf('http') === -1
      ? PF.url.make(_this.sound_path).replace('/index.php/', '/')
      : $Core_IM_Firebase.sound_path;

    // Hide emoji panel when click on below elements
    $(document).on('click', '.chat-row, .chat-row-title, .pf-im-main, ._pf_im_friend_search, #im_chat_box', function () {
        $Core_IM.emoji.hide();
    });

    $(document).off('click', '.chat-action-delete').on('click', '.chat-action-delete', function () {
      var t = $(this).data('thread-id');
      $(this).closest('.js_p_im_dock_header').find('span[data-action="close_dock"]').trigger('click');
      $('.pf-im-panel[data-thread-id="' + t + '"]').remove();
      //All message page
      if (window.location.href.search('/im/popup') > 0) {
        if($Core_IM.messages_room_active) {
          $('#pf-chat-window-' + $Core_IM.messages_room_active).remove();
        }
        $Core_IM.messages_room_active = '';
        $('#pf-chat-window-active').remove();
      }
      var room = {};
      _this.usersCollectionRef.doc(_this.current_user.uid).get().then(function (doc) {
        if (doc.exists) {
          var data = doc.data();
          if (data.hasOwnProperty('rooms')) {
            var old_rooms = data.rooms;
            if (old_rooms.hasOwnProperty(t)) {
              room[t] = old_rooms[t];
              room[t].active = false;
            }
          }
        } else {
          room[t] = false;
        }
        _this.usersCollectionRef.doc(_this.current_user.uid).set({
          rooms: room
        }, {merge: true});
      }).catch(function (e) {
        console.log('Error get current user on click delete (init)', e);
      });
      $Core_IM.cookieActiveChat(t, true);
    });

    $(document).off('click', '.chat-action-search').on('click', '.chat-action-search', function () {
      var thread_id = $(this).data('thread-id');
      $('#pf-im-search-input').data('thread-id', thread_id).val('');
      $('.pf-im-search-result').empty();
      $('.pf-im-search').show();
    });

    $(document).off('click', '.pf-im-search-close').on('click', '.pf-im-search-close', function () {
      $('.pf-im-search').hide();
    });

    $(document).off('keyup', '#pf-im-search-input').on('keyup', '#pf-im-search-input', function (e) {
      var pis = $('#pf-im-search-input'),
        thread_id = $(this).data('thread-id'),
        stext = pis.val(),
        valid = $Core_IM.checkKeyUp(e.keyCode);
      // search when input from 3 chars
      if (valid && stext.length > 2) {
        $Core_IM_Firebase.searchMessage(thread_id, stext);
        $('.pf-im-search-result').data('thread-id', thread_id).empty();
      }
    });

    $(document).off('scroll', '.pf-im-search-result').on('scroll', '.pf-im-search-result',function () {
      if ($(this).children().length > 0 &&
        $(this).scrollTop() + $(this).innerHeight() ===
        $(this)[0].scrollHeight) {
        var pis = $('#pf-im-search-input'), stext = pis.val();
        // search when input from 3 chars
        if (stext.length > 2) {
          $Core_IM_Firebase.searchMessage(pis.data('thread-id'), stext, $('.pf-im-search-result').find('.pf-chat-message').length);
        }
      }
    });

    $(document).off('mousedown', '.chat-row, .chat-form').on('mousedown', '.chat-row, .chat-form',function () {
      $('.p-im-chat-room-block').css('opacity', 1);
    });

    $Core_IM_Firebase.loadWhenCoreInit();

    $(document).on('click', '.pf-im-title .close-im-window, #pf-im-wrapper', function () {
        var body = $('body');
        body.removeClass('im-is-active');
        body.css('overflow-y', 'scroll');
        body.css('position', 'initial');
        $('#pf-im, #pf-chat-window, #pf-chat-window-active, .pf-im-search, #pf-im-wrapper').hide();
        deleteCookie('pf_im_active');
      });

    $(document).off('click', '.popup-im-window,.js_p_im_view_all_message').on('click', '.popup-im-window,.js_p_im_view_all_message',function () {
      window.location.href = PF.url.make('/im/popup');
      $('#pf-im-wrapper').trigger('click');
    });

    $(document).off('keyup', '._pf_im_friend_search input').on('keyup', '._pf_im_friend_search input',function (e) {
      var t = $(this),
        im_main = $('.pf-im-main'),
        im_search = $('.pf-im-search-user'),
        valid = $Core_IM.checkKeyUp(e.keyCode);

      if (!valid) {
        return;
      }

      im_main.hide();
      im_search.empty().show();
      if (t.val() === '') {
        im_search.hide();
        im_main.show();
        return;
      }

      $('.fa-search', '._pf_im_friend_search').hide();
      $('.fa-pulse', '._pf_im_friend_search').show();
      clearTimeout($Core_IM.searching);
      $Core_IM.searching = setTimeout(function () {
        $.ajax({
          url: PF.url.make('/im/search-friends'),
          data: 'search=' + t.val(),
          contentType: 'application/json',
          success: function (e) {
            // im_main.hide();
            $('.fa-pulse', '._pf_im_friend_search').hide();
            $('.fa-search', '._pf_im_friend_search').show();
            im_search.empty();
            if (e !== '') {
              im_search.append(e);

              // update search preview
              $('.pf-im-search-user').find('.pf-im-panel').each(function () {
                var friend_id = $(this).data('friend-id'),
                  new_thread_id = _this.combineRoomIds(friend_id, _this.current_user.id);
                _this.roomCollectionRef.doc(new_thread_id).get().then(function (doc) {
                  if (doc.exists && doc.data()) {
                    var data = doc.data();
                    var preview_id = data.preview_id || 0;
                    if (preview_id) {
                      _this.loadSearchPreview({
                        thread_id: new_thread_id,
                        text: data.preview_text || '',
                        deleted: data.preview_deleted || false
                      }, preview_id, new_thread_id);
                      //Load unread count
                      _this.loadCountUnreadMessages(null, data.users[_this.current_user.uid] || 0, friend_id, true);
                    }
                  }
                }).catch(function (e) {
                  console.log('Error get room load search (init)', e);
                });
              });
            } else {
              im_search.append('<p class="p-2 p-im-info-gray">' + oTranslations['no_friends_found'] + '</p>');
            }
            $Core.loadInit();
          },
        });
      }, 1e3);
    });

    $(document).off('click', '.im_action_emotion').on('click', '.im_action_emotion', function () {
      var thread_id = $(this).data('thread-id'),
      thread = $('#pf-chat-window-' + $Core_IM.stripThreadId(thread_id));
      if (thread.find('.chat-form-actions').is(':visible')) {
        $Core_IM.emoji.hide();
      } else {
        $Core_IM.emoji.hide();
        $Core_IM.emoji.show($(this).data('action'), thread_id);
      }
    });

    $(document).off('click', '#pf-open-im').on('click', '#pf-open-im', function () {
      $Core_IM.im_debug_mode && console.log('#pf-open-im click');
      var b = $(this),
        body = $('body');

      if ($Core_IM_Firebase.init_first_time) {
        $Core_IM_Firebase.init_first_time = false;
        $Core_IM_Firebase.initStep3();
      }

      // lock scroll on ios
      if ($Core_IM.isIos()) {
        $('body').css('position', 'fixed');
      }

      $('#pf-im').show();
      $('#pf-im-wrapper').show();

      if (!b.data('fake-click') ||
        (b.data('fake-click') && b.data('fake-click') == '0')) {
        body.addClass('im-is-active');
      }

      setCookie('pf_im_active', 1);

      $('.pf-im-panel.active').removeClass('active');
      // $('span#js_total_new_messages').html('0').hide();
    });

    if (u.length && !im_dock.length && !im.length) {
      if (window.location.href.search('/im/popup') > 0) {
        $.tmpl($Core_IM_Firebase.core_tmpl, {
          'Title': oTranslations['all_chats'],
          'CloseChatBox': oTranslations['close_chat_box'],
          'OpenNewTab': oTranslations['open_in_new_tab'],
          'SearchFriendPlaceholder': oTranslations['search_friends_dot_dot_dot'],
          'SoundPath': _this.sound_path,
        }).prependTo('body');
        //Disable ajax loading
        $('a').addClass('no_ajax_link');
        close_warning_checked = true;
      } else {
        $.tmpl($Core_IM_Firebase.dock_message_tmpl, {
          'Title': oTranslations['chat'],
          'SearchFriendPlaceholder': oTranslations['search_friends_dot_dot_dot'],
          'SoundPath': _this.sound_path
        }).prependTo('body');
      }

      $.tmpl($Core_IM.search_message_tmpl, {
        'Title': oTranslations['search_message'],
        'SearchPlaceholder': oTranslations['enter_search_text']
      }).prependTo('body');
      $.tmpl($Core_IM.dropdown_message_tmpl, {
        'Title': oTranslations['all_chats'],
        'CloseChatBox': oTranslations['close_chat_box'],
        'OpenNewTab': oTranslations['open_in_new_tab'],
        'SearchFriendPlaceholder': oTranslations['search_friends_dot_dot_dot'],
        'SoundPath': _this.sound_path,
      }).prependTo('body');
    }

    $Core_IM_Firebase.chatWithUser();

    if ($Core_IM_Firebase.IM_Firebase == null && firebaseConfig) {
      _this.IM_Firebase = firebase.initializeApp(firebaseConfig, 'pf_im');
      _this.usersCollectionRef = _this.IM_Firebase.firestore().collection('users');
      _this.notificationCollectionRef = _this.IM_Firebase.firestore().collection('notifications');
      _this.roomCollectionRef = _this.IM_Firebase.firestore().collection('rooms');
      _this.current_user = $Core_IM_Firebase.getUser();
      _this.currentUserDocRef = _this.usersCollectionRef.doc(_this.encodeId(_this.current_user.id));

      var loginFirebaseUser = function () {
        if (typeof firebasePassword === "undefined" || !firebasePassword) {
          return false;
        }
        _this.IM_Firebase.auth().signInWithEmailAndPassword(_this.getFirebaseEmail(), firebasePassword)
          .then(function (data) {
            $Core_IM.im_debug_mode && console.log('Firebase signInWithEmailAndPassword success', data);
            _this.initStep2();
          })
          .catch(function (error) {
            console.log('Firebase signInWithEmailAndPassword error', error);
            if (error.code === 'auth/user-not-found') {
              $Core_IM_Firebase.createFirebaseUser();
            }
          });
      }
      loginFirebaseUser(false);

      if (pf_im_algolia_app_id && pf_im_algolia_api_key) {
        var client = algoliasearch(pf_im_algolia_app_id, pf_im_algolia_api_key);
        _this.algolia_index = client.initIndex('im_chat');
        _this.algolia_index.setSettings({
          attributesForFaceting: ['thread_id'],
          searchableAttributes: ['text'],
          attributesToRetrieve: ['text', 'thread_id', 'time_stamp', 'sender', 'receiver', 'attachment_id', 'listing_id', 'deleted', 'id'],
          customRanking: ['desc(time_stamp)'],
        });
      }
      if (window.location.href.search('/im/popup') !== -1) {
        _this.init_first_time = false;
        _this.initStep3();
      }
    }

    $(document).off('click', '.chat-action-noti').on('click', '.chat-action-noti', function () {
      var bell = $(this).find('i.ico'), thread_id = $('.chat-row').data('thread-id');
      bell.attr('class', 'ico ' +
        (bell.hasClass('ico-bell2') ? 'ico-bell2-off' : 'ico-bell2'));
      var noti = bell.hasClass('ico-bell2');
      _this.notificationCollectionRef.doc(_this.current_user.uid).collection('rooms').doc(thread_id).set({
        thread_id: thread_id,
        noti: noti,
        user_id: _this.current_user.id,
        user_uid: _this.current_user.uid
      }, {merge: true});
      _this.thread_notification[thread_id] = noti;
    });

    $(document).off('click', '.pf-im-more-conversation').on('click', '.pf-im-more-conversation', function (e) {
      //prevent close dropdown when click on this loadmore
      e.stopPropagation();
      var _clicked = $(this),
      loadingEle = $('.js_p_im_loading_buddy_visible');
      loadingEle.show();
      if (!$Core_IM_Firebase.hidden_current_rooms.length) {
        loadingEle.hide();
        _clicked.remove();
        return false;
      }
      _clicked.remove();
      var hiddenRooms = $Core_IM_Firebase.hidden_current_rooms,
        limit_rooms = pf_total_conversations == '0' ? 0 : parseInt(pf_total_conversations);
      var new_rooms_data = hiddenRooms.splice(0, limit_rooms);
      $Core_IM_Firebase.hidden_current_rooms = hiddenRooms;
      $Core_IM.im_debug_mode && console.log('new_rooms_data', new_rooms_data);
      new_rooms_data.forEach(function (data, index) {
        if (index === new_rooms_data.length - 1) {
          $Core_IM_Firebase.addOldRooms(data, new_rooms_data);
        } else {
          $Core_IM_Firebase.addOldRooms(data);
        }
      });
    });

    //set position for window chat
    $Core_IM.allChatGetPosition();
    //auto resize textarea
    $Core_IM.resizeTextarea();
    $Core_IM.tooltipTime();
    $Core_IM.initDockBuddySearch();
  },
  createFirebaseUser: function () {
    $Core_IM.im_debug_mode && console.log('creating FirebaseUser...');
    if (typeof firebasePassword === "undefined" || !firebasePassword) {
      $Core_IM.im_debug_mode && console.log('createFirebaseUser invalid password');
      return false;
    }
    var _this = $Core_IM_Firebase;
    _this.IM_Firebase.auth().createUserWithEmailAndPassword(_this.getFirebaseEmail(), firebasePassword)
      .then(function (data) {
        $Core_IM.im_debug_mode && console.log('createFirebaseUser success', data);
        _this.initStep2();
      }).catch(function (error) {
        console.log('createFirebaseUser error', error);
    });
  },
  getFirebaseEmail: function () {
    return 'user_0' + this.current_user.id + '@' + getParam('sJsHostname')
  },
  initStep2: function () {
    var _this = $Core_IM_Firebase;
    // _this.initSnapshotMessage();
    _this.notificationCollectionRef.doc(_this.current_user.uid).collection('rooms').get().then(function (querySnapshot) {
      querySnapshot.forEach(function (doc) {
        var data = doc.data();
        _this.thread_notification[data.thread_id] = data.noti;
      });
      //If init success, overwrite function
      $Core.composeMessage = IMFirebaseComposeMessage;
      if (typeof $Core.marketplace !== "undefined") {
        $Core.marketplace.contactSeller = IMFirebaseComposeMessage;
      }
    }).catch(function (e) {
      console.log('Error get notifications (init)', e);
    }).finally(function() {
      if ($Core_IM_Firebase.init_first_time) {
        $Core_IM_Firebase.init_first_time = false;
        $Core_IM_Firebase.initStep3();
      }
    });
  },
  loadWhenCoreInit: function () {
    if ($Core_IM.host_failed) {
      return false;
    }
    var hd_mess = $('#hd-message a'), span_new = $('span#js_total_new_messages');

    if (hd_mess.length || (!hd_mess.length && span_new.length)) {
      var obj = (hd_mess.length ? hd_mess : span_new.parents('a:first'));

      obj.each(function () {
        var m = $(this);
        m.addClass('built');
        m.addClass('no_ajax');
        m.parent().find('.dropdown-panel').empty();
        m.parent().on("shown.bs.dropdown", function () {
          $('body').addClass('im-is-dropdown');
          var parentFix = m.closest('.sticky-bar');
          if (parentFix && parentFix.css("position") === "fixed") {
            $('.p-im-DropdownContainerPosition').attr('style', "position: fixed;z-index: 99;transform:translateX(-50%);top: " + (m.offset().top + m.height() - $(window).scrollTop()) + "px;left: " + m.offset().left + "px");
          } else {
            $('.p-im-DropdownContainerPosition').attr('style', "position: absolute;z-index: 99;transform:translateX(-50%);top: " + (m.offset().top + m.height()) + "px;left: " + m.offset().left + "px");
          }
        });
        m.parent().on("hidden.bs.dropdown", function () {
          $('body').removeClass('im-is-dropdown');
        });

      });
    }

    // Load more messages when scroll up
    $('.chat-row').off('scroll').on('scroll',  function () {
      var chat_row = $(this), _this = $Core_IM_Firebase;
      if (chat_row.scrollTop() === 0 && !_this.is_load_more) {
        $.tmpl($Core_IM.chat_load_more_tmpl, {
          'LoadingMessage': oTranslations['loading_messages'],
        }).prependTo($(this));
        _this.is_load_more = true;
        _this.loadMoreMessage(chat_row.data('thread-id'));
      }
    });
    //init some function layout
    $Core_IM.allChatGetPosition();
    $Core_IM.tooltipTime();
    $Core_IM.resizeTextarea();
  },
  initStep3: function () {
    $Core_IM.im_debug_mode && console.log('Firebase initStep3');
    var _this = $Core_IM_Firebase;
    _this.init_step_3 = true;
    if (Object.keys($Core_IM_Firebase.current_rooms).length) {
      //Don't need connect firebase
      _this.loadOldRooms($Core_IM_Firebase.current_rooms);
      return true;
    }
    _this.currentUserDocRef.get().then(function (doc) {
      var input = $('._pf_im_friend_search').find('input');
      if (doc.exists) {
        $Core_IM.im_debug_mode && console.log("Loaded data");
        _this.setUserDoc(true);
        var data = doc.data();
        if (data.rooms) {
          _this.loadOldRooms(data.rooms);
        } else {
          _this.validateOldRooms([]);
          input.length && input.attr('readonly', false);
          $('#pf-im .fa-spinner,.js_p_im_loading_buddy_visible').hide();
        }
      } else {
        input.length && input.attr('readonly', false);
        $('#pf-im .fa-spinner,.js_p_im_loading_buddy_visible').hide();
        _this.validateOldRooms([]);
        _this.setUserDoc();
      }
    }).catch(function (error) {
      console.log("Error getting user document (init)", error);
    });
  },
  loadOldRooms: function (rooms) {
    try {
      IMFirebaseValidOldRooms(rooms);
    } catch (e) {
      console.log('Error load old rooms', e)
    }
  },
  dynamicSort: function (property) {
    var sortOrder = 1;
    if (property[0] === "-") {
      sortOrder = -1;
      property = property.substr(1);
    }
    return function (a, b) {
      /* next line works with strings and numbers,
       * and you may want to customize it to your needs
       */
      var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
      return result * sortOrder;
    }
  },
  searchMessage: function (thread_id, text, result_length) {
    $Core_IM.im_debug_mode && console.log('searchMessage...', thread_id, text);
    var _this = $Core_IM_Firebase, pisr = $('.pf-im-search-result'),
      last_time_stamp = result_length ? $('.pf-im-search-result').find('.pf-chat-message:last').attr('id') : 0;
    if (!result_length && _this.last_search_thread.hasOwnProperty(thread_id)) {
      _this.last_search_thread[thread_id] = null;
    }
    if (_this.algolia_index) {
      _this.algolia_index.search({
        query: text,
        hitsPerPage: 10,
        offset: result_length ? result_length - 1 : 0,
        filters: 'thread_id:"' + thread_id + '" AND time_stamp < ' + (last_time_stamp ? last_time_stamp : Math.floor(Date.now()))
      }).then(function (responses) {
        var results = responses.hits;
        if (results.length) {
          results.forEach(function (message) {
            if (!pisr.find('.pf-chat-message[data-message-id="' + message.id + '"]').length) {
              message.receiver = _this.users_data[message.receiver];
              message.user = _this.users_data[message.sender];
              var html = $Core_IM.buildMessage(message, false, true, true);
              pisr.append(html);
            }
          });
        } else if (pisr.is(':empty')) {
          pisr.append('<span class="pf-im-no-message">' + oTranslations['no_message'] + '</span>');
        }
      }).then(function () {
        $Core_IM.updateChatTime();
        $Core.loadInit();
      });
    } else {
      var strLength = text.length,
        strFrontCode = text.slice(0, strLength - 1),
        strEndCode = text.slice(strLength - 1, text.length),
        startCode = text,
        endCode = strFrontCode + String.fromCharCode(strEndCode.charCodeAt(0) + 1);
      var query;
      if (last_time_stamp && _this.last_search_thread[thread_id]) {
        query = _this.roomCollectionRef.doc(thread_id).collection('messages')
          .where('text', '>=', startCode)
          .where('text', '<', endCode)
          .orderBy('text', 'asc')
          .where('deleted', '==', false)
          .orderBy('time_stamp', 'desc')
          .startAfter(_this.last_search_thread[thread_id])
          .limit(10)
      } else {
        query = _this.roomCollectionRef.doc(thread_id).collection('messages')
          .where('text', '>=', startCode)
          .where('text', '<', endCode)
          .orderBy('text', 'asc')
          .where('deleted', '==', false)
          .orderBy('time_stamp', 'desc')
          .limit(10)
      }
      query.get().then(function (snapShot) {
        if (!snapShot.size) {
          if (pisr.is(':empty')) {
            pisr.append('<span class="pf-im-no-message">' + oTranslations['no_message'] + '</span>')
          }
        } else {
          snapShot.forEach(function (doc) {
            var message = doc.data();
            if (!pisr.find('.pf-chat-message[data-message-id="' + message.id + '"]').length) {
              message.receiver = _this.users_data[message.receiver];
              message.user = _this.users_data[message.sender];
              var html = $Core_IM.buildMessage(message, false, true, true);
              pisr.append(html);
            }
            _this.last_search_thread[thread_id] = doc;
          });
        }
      }).then(function () {
        $Core_IM.updateChatTime();
        $Core.loadInit();
      });
    }
  },
  validateOldRooms: function (rooms_data) {
    var _this = $Core_IM_Firebase;
    rooms_data.forEach(function (data, index) {

      if (!data.messages) return false;

      if (pf_total_conversations == '0' || index < parseInt(pf_total_conversations)) {
        _this.users += _this.decodeId(data.id.replace(_this.current_user.uid, '')) + ',';
      }
    });
    if (_this.users) {
      $.ajax({
        url: PF.url.make('/im/panel'),
        data: 'users=' + _this.users,
        contentType: 'application/json',
        success: function (e) {
          $('.pf-im-panel').each(function () {
            var t = $(this),
              names = [_this.decodeId(_this.current_user.uid), _this.decodeId(t.data('thread-id').replace(_this.current_user.uid, ''))];
            for (var i in names) {
              var n = names[i];
              if (!n) {
                continue;
              }
              if (typeof (e[n]) === 'object') {
                var u = e[n];
                if (u.id === _this.current_user.id) {
                  continue;
                }
                _this.updateUserDoc(u);
                // check for blocked users
                if (typeof pf_im_blocked_users !== 'undefined' && pf_im_blocked_users.indexOf(u.id) !== -1) {
                  t.find('.pf-im-panel-image').html($.tmpl($Core_IM.invalid_user_tmpl, {
                    'UserName': oTranslations['invalid_user'],
                    'ShortName': 'IU',
                  }));
                  t.find('.__thread-name').html(oTranslations['invalid_user']);
                  t.attr('data-user-blocked', '1');
                } else {
                  t.find('.pf-im-panel-image').html($Core_IM.addTargetBlank(u.photo_link));
                  t.find('.__thread-name').html(u.name);
                }

                // banned user
                if (typeof e[n].is_banned !== 'undefined' && e[n].is_banned) {
                  t.attr('data-user-banned', '1');
                }
                //no more friend
                if (typeof e[n].is_friend !== 'undefined' && !e[n].is_friend) {
                  t.attr('data-not-friend', '1');
                }
              } else if (e[n] === false) {
                _this.deleted_users.push(n);
                t.find('.pf-im-panel-image').html($.tmpl($Core_IM.deleted_user_tmpl, {
                  'UserName': oTranslations['deleted_user'],
                  'ShortName': 'DU',
                }));
                t.find('.__thread-name').html(oTranslations['deleted_user']);
                t.attr('data-user-deleted', '1');
              }
            }

            t.show();
          });

          $('#pf-im > i').remove();
          $Core_IM.updateChatTime();
          $Core.loadInit();
        }
      });
    }
    var get_friends;
    // get friends
    if (pf_total_conversations === '0' || pf_total_conversations === '') {
      get_friends = 0;
    } else {
      get_friends = (pf_total_conversations - _this.thread_show > 0)
        ? pf_total_conversations - _this.thread_show
        : -1;
    }
    if (get_friends >= 0) {
      $.ajax({
        url: PF.url.make('/im/friends-firebase'),
        data: 'limit=' + (get_friends > 0 ? get_friends : 0) + '&threads=' +
          _this.users,
        contentType: 'application/json',
        'success': function (e) {
          if (e.match(/class="pf-im-panel"/)) {
            $('.pf-im-main').append(e);
          }
          $('#pf-im .fa-spinner').hide();
          $Core.loadInit();
          _this.init_step_3 = false;
        }
      });
    } else {
      _this.init_step_3 = false;
      $('#pf-im .fa-spinner').hide();
    }
    if (!rooms_data.length) {
      $Core_IM.initChatDock(true);
    }
  },
  addOldRooms: function (room_data, all_rooms) {
    // $Core_IM.im_debug_mode && console.log('addOldRooms', room_data);
    if (!room_data.id) return;
    var _this = $Core_IM_Firebase,
      id = room_data.id,
      preview_id = room_data.preview_id ? room_data.preview_id : '',
      has_messages = !!room_data.messages,
      receiver = id.replace(_this.current_user.uid, '');
    var finalRoomLoaded = function (all_rooms) {
      if (all_rooms) {
        var obj = $Core_IM_Firebase;
        obj.validateOldRooms(all_rooms);
        obj.initSnapshotMessage();
        $Core_IM.initChatDock(true);
        var input = $('._pf_im_friend_search').find('input');
        input.length && input.attr('readonly', false);
        $('#pf-im .fa-spinner, .js_p_im_loading_buddy_visible').hide();
        //Check append load more
        if (obj.hidden_current_rooms.length) {
          $('.pf-im-main').append('<div class="pf-im-more-conversation">' + oTranslations['im_load_more'] + '</div>');
        }
      }
      $Core.loadInit();
    }
    if (!has_messages) {
      if (all_rooms) {
        finalRoomLoaded(all_rooms);
      }
      return false;
    }
    _this.thread_show++;
    var m = $('.pf-im-panel[data-thread-id="' + id + '"]');
    _this.usersCollectionRef.doc(receiver).get().then(function (doc) {
      if (doc.exists) {
        var data = doc.data();
        $Core_IM_Firebase.loadUserFromServer(data);
        if (!m.length) {
          $.tmpl($Core_IM.panel_tmpl, {
            'UserId': data.id,
            'ThreadId': id,
            'PhotoLink': data.photo_link ? data.photo_link : '',
            'Name': data.name
          }).appendTo('.pf-im-main');
          var message = null;
          if (data.rooms && data.rooms[id]) {
            message = {
              text: data.rooms[id].preview_text || '',
              deleted: data.rooms[id].preview_deleted || false,
              thread_id: id
            }
          }
          $Core_IM_Firebase.loadSearchPreview(message, preview_id, id);
        }
        if (window.location.href.search('/im/popup') === -1) {
          if (typeof room_data.users !== "undefined" && room_data.users[$Core_IM_Firebase.current_user.uid]) {
            var panel = $('.pf-im-panel[data-thread-id="' + id + '"]');
            $Core_IM_Firebase.loadCountUnreadMessages(id, panel.length && panel.hasClass('active') ? 0 : room_data.users[$Core_IM_Firebase.current_user.uid]);
          }
        }
      }
    }).catch(function (e) {
      console.log('Error (addOldRooms)', e);
    }).then(function () {
      finalRoomLoaded(all_rooms);
    });
  },
  loadCountUnreadMessages: function (thread_id, size, friend_id, is_search) {
    //Friend id support when search
    var thread = thread_id ? $('.pf-im-panel[data-thread-id="' + thread_id + '"]') : (is_search ? $('.pf-im-search-user .pf-im-panel[data-friend-id="' + friend_id + '"]') : $('.pf-im-panel[data-friend-id="' + friend_id + '"]')),
      badge = thread.find('.badge');
    if (!thread.length) return false;
    if (!size) {
      badge.text('0');
      thread.removeClass('new');
      thread.removeClass('count');
      return false;
    }
    if (!badge.length) {
      thread.append('<div class="pf-im-panel-info"><span class="badge">' + size + '</span></div>');
    } else {
      badge.text(size);
    }
    !thread.hasClass('new') && thread.addClass('new');
  },
  loadSearchPreview: function (message, message_id, thread_id) {
    // update search preview text
    var _this = $Core_IM_Firebase;
    var processPreview = function (message) {
      var user_id = _this.decodeId(message.thread_id.replace(_this.current_user.uid, ''));
      if (message.deleted) {
        message.text = oTranslations['this_message_has_been_deleted'];
      }
      if (message.text === '') {
        message.text = $Core_IM.file_preview;
      }
      var preview = $('.pf-im-panel[data-friend-id="' + user_id + '"]').find('.pf-im-panel-preview');
      preview.removeClass('twa_built');
      preview.text(message.text);
      $Core.loadInit();
    };
    if (message_id && thread_id && !message) {
      _this.roomCollectionRef.doc(thread_id).collection('messages').doc(message_id)
        .get().then(function (doc) {
        if (doc.exists) {
          processPreview(doc.data());
        }
      }).catch(function (e) {
        console.log('Error get preview (loadSearchPreview)', e);
      });
    } else {
      processPreview(message);
    }
  },
  encodeId: function (id) {
    return window.btoa(id);
  },
  decodeId: function (id) {
    return window.atob(id);
  },
  setUserDoc: function (is_update) {
    if (!is_update) {
      $Core_IM_Firebase.currentUserDocRef.set($Core_IM_Firebase.getUser())
    } else {
      $Core_IM_Firebase.currentUserDocRef.update($Core_IM_Firebase.getUser())
    }
    $Core_IM_Firebase.loadUserFromServer($Core_IM_Firebase.getUser());
  },
  combineRoomIds: function (id_1, id_2) {
    var _this = $Core_IM_Firebase;
    if (id_1 < id_2) {
      return _this.encodeId(id_1) + _this.encodeId(id_2);
    } else {
      return _this.encodeId(id_2) + _this.encodeId(id_1);
    }
  },
  setRoomDoc: function (thread_id, user_ids, receiver) {
    var _this = $Core_IM_Firebase;
    var room_data = {
      last_update: Math.floor(Date.now()),
      users: {},
      id: thread_id
    };
    user_ids.forEach(function (id) {
      var room = {};
      room[thread_id] = {
        id: thread_id,
        active: false
      };
      if (receiver && receiver.id === id) {
        _this.updateUserDoc(receiver, room);
      } else {
        _this.usersCollectionRef.doc(_this.encodeId(id)).set({
          rooms: room,
          uid: _this.encodeId(id)
        }, {merge: true})
      }
      room_data.users[_this.encodeId(id)] = 0;
    });
    _this.roomCollectionRef.doc(thread_id).set(room_data, {merge: true});
  },
  updateUserDoc: function (data, room) {
    var _this = $Core_IM_Firebase,
      link = data.photo_link ? data.photo_link.match(/<a[^<]*href="(.*?)"[^<]+>(.*?)/) : '',
      image = data.photo_link ? data.photo_link.match(/<img[^<]*data-src="(.*?)"/) : '',
      uid = _this.encodeId(data.id);
    var value = {
      uid: uid,
      id: data.id
    };
    if (data.name) {
      value.name = data.name;
    }
    if (data.photo_link) {
      value.photo_link = data.photo_link;
    }
    if (image && image.length > 1) {
      value.image = image[1];
    }
    if (link && link.length > 1) {
      value.link = link[1];
    }
    if (typeof room !== "undefined") {
      value.rooms = room;
    }
    _this.usersCollectionRef.doc(uid).set(value, {merge: true});
    return value;
  },
  loadMessages: function (thread_id, last_message) {
    var _this = $Core_IM_Firebase,
      messagesRef = _this.roomCollectionRef.doc(thread_id).collection('messages'),
      thread_id_parsed = $Core_IM.stripThreadId(thread_id),
      messageLimit = 10,
      c = $('#pf-chat-window-' + thread_id_parsed).find('.chat-row');
    if (c.length) {
      $Core_IM.scrollBottom = c[0].scrollHeight - c[0].scrollTop;
    }
    if (_this.load_first_time && !c.find('.pf-chat-row-loading').length) {
      $.tmpl($Core_IM.loading_conversation_tmpl, {
        'LoadingConversation': oTranslations['loading_conversation'],
      }).prependTo(c);
    }

    if (c.length) {
      $Core_IM.scrollBottom = c[0].scrollHeight - c[0].scrollTop;
    }
    var messageSnapshot;
    if (last_message) {
      messageSnapshot = messagesRef
        .orderBy('time_stamp', 'desc')
        .startAfter(last_message)
        .limit(messageLimit).get();
    } else {
      messageSnapshot = messagesRef
        .orderBy('time_stamp', 'desc')
        .limit(messageLimit)
        .get();
    }
    messageSnapshot.then(function (querySnapshot) {
      // Get the last visible document
      if (querySnapshot.docs.length) {
        _this.last_loaded_message[thread_id] = querySnapshot.docs[querySnapshot.docs.length - 1];
      }
      var index = 0;
      querySnapshot.forEach(function (doc) {
        var data = doc.data();
        //Set seen
        if (data.seen === false && data.receiver === _this.current_user.uid) {
          messagesRef.doc(data.id).set({seen: true}, {merge: true});
          $Core_IM_Firebase.current_rooms[thread_id]['users'][data.receiver] = $Core_IM_Firebase.current_rooms[thread_id]['users'][data.receiver] > 0 ? $Core_IM_Firebase.current_rooms[thread_id]['users'][data.receiver] - 1 : 0;
          $Core_IM_Firebase.roomCollectionRef.doc(thread_id).set({users: $Core_IM_Firebase.current_rooms[thread_id]['users']}, {merge: true});
        }
        //Load messages
        data.user = _this.users_data.hasOwnProperty(data.sender) ? _this.users_data[data.sender] : _this.loadUserFromServer(data.sender);
        if (!c.find('.pf-chat-message[data-message-id="' + data.id + '"]').length) {
          c.prepend($Core_IM.buildMessage(data, false, true));
          if (window.location.href.search('/im/popup') > 0 && index === messageLimit - 1
            && c.get(0) && c.get(0).scrollHeight <= c.get(0).clientHeight) {
            $('.pf-chat-row-loading').remove();
            _this.loadMoreMessage(thread_id);
          }
        }
        index++;
      });
    }).catch(function (e) {
      console.log('Error get load message (loadMessage)', e);
    }).then(function () {
      _this.processLoadMessage(thread_id);
    });
    if ($Core_IM.new_message) {
      $Core_IM.insertAtCaret('im_chat_box-' + thread_id_parsed, $Core_IM.new_message, true);
      $Core_IM.new_message = null;
    }
  },
  loadUserFromServer: function (uid) {
    var _this = $Core_IM_Firebase;
    var loadUserAjax = function (uid) {
      $.ajax({
        url: PF.url.make('/im/panel'),
        async: false,
        data: 'users=' + _this.decodeId(uid),
        contentType: 'application/json',
        'success': function (e) {
          for (var i in e) {
            var u = e[i];
            if (u !== false) {
              _this.users_data[uid] = _this.updateUserDoc({
                id: parseInt(_this.decodeId(uid)),
                name: u.name || '',
                photo_link: u.photo_link || ''
              });
            }
          }
        }
      });
      return _this.users_data[uid];
    };
    var result;
    if (typeof uid === 'object') {
      if (!uid.link) {
        result = loadUserAjax(uid.uid);
      } else {
        result = _this.users_data[uid.uid] = uid;
      }
    } else {
      result = loadUserAjax(uid);
    }
    return result;
  },
  initSnapshotMessage: function () {
    var _this = $Core_IM_Firebase, unread_size = 0;
    _this.currentUserDocRef.onSnapshot(function (userSnapshot) {
      var data = userSnapshot.data();
      if (!data) return false;
      var rooms = data.rooms;
      for (var key in rooms) {
        if (!$Core_IM_Firebase.current_rooms.hasOwnProperty(key)) {
          //Doesn't snapshot
          $Core_IM_Firebase.current_rooms[key] = rooms[key];
          $Core_IM_Firebase.current_rooms[key]['users'] = {};
          $Core_IM_Firebase.current_rooms[key]['first_time'] = true;
          $Core_IM_Firebase.roomCollectionRef.doc(key)
            .onSnapshot(function (querySnapshot) {
              $Core_IM_Firebase.noticed_threads = {};
              if (!querySnapshot.exists) return false;
              var snap_room = querySnapshot.data();
              if (typeof $Core_IM_Firebase.current_rooms[snap_room.id] === "undefined") {
                $Core_IM_Firebase.current_rooms[snap_room.id] = snap_room;
              }
              var from_cache = $Core_IM_Firebase.current_rooms[snap_room.id] && $Core_IM_Firebase.current_rooms[snap_room.id]['first_time'] || false;
              $Core_IM_Firebase.current_rooms[snap_room.id]['users'] = snap_room.users ? snap_room.users : {};
              $Core_IM_Firebase.current_rooms[snap_room.id]['first_time'] = false;
              if ($Core_IM_Firebase.current_rooms[snap_room.id]['users'][_this.current_user.uid] && !$Core_IM_Firebase.noticed_messages.hasOwnProperty(snap_room.id) && $Core_IM_Firebase.init_first_time) {
                $Core_IM_Firebase.noticed_messages[snap_room.id] = {};
                $('span#js_total_new_messages').html(Object.keys($Core_IM_Firebase.noticed_messages).length).show();
                return true;
              }

              $Core_IM_Firebase.roomCollectionRef.doc(snap_room.id)
                .collection('messages')
                .where('receiver', '==', _this.current_user.uid)
                .where('seen', '==', false)
                .orderBy('time_stamp', 'asc')
                .get().then(function (messageSnapshot) {
                unread_size = messageSnapshot.size;
                messageSnapshot.forEach(function (doc) {
                  var message = doc.data(), allowHandle = true;
                  message.receiver = $Core_IM_Firebase.current_user;

                  if ($Core_IM_Firebase.noticed_messages.hasOwnProperty(message.thread_id)) {
                    allowHandle = !$Core_IM_Firebase.noticed_messages[message.thread_id][message.id];
                  } else {
                    $Core_IM_Firebase.noticed_messages[message.thread_id] = {};
                  }
                  if (allowHandle) {
                    message.user = $Core_IM_Firebase.users_data.hasOwnProperty(message.sender) ? $Core_IM_Firebase.users_data[message.sender] : $Core_IM_Firebase.loadUserFromServer(message.sender);
                    if (message.user) {
                      $Core_IM_Firebase.onChatHandle(message, from_cache);
                    }
                  }
                  $Core_IM_Firebase.noticed_messages[message.thread_id][message.id] = true;
                });
              }).catch(function (e) {
                console.log('Error get snap message (initSnapshotMessage)', e);
              }).then(function () {
                if (!$Core_IM_Firebase.init_first_time) {
                  var panel = $('.pf-im-panel[data-thread-id="' + snap_room.id + '"]');
                  $Core_IM_Firebase.loadCountUnreadMessages(snap_room.id, panel.length && panel.hasClass('active') ? 0 : unread_size);
                }
              });
              if (snap_room.preview_id && !$Core_IM_Firebase.init_first_time) {
                $Core_IM_Firebase.roomCollectionRef.doc(snap_room.id)
                  .collection('messages')
                  .doc(snap_room.preview_id)
                  .get().then(function (doc) {
                  if (doc.exists) {
                    var message = doc.data(), allowHandle = true;
                    if (message.receiver === $Core_IM_Firebase.current_user.uid) {
                      message.receiver = $Core_IM_Firebase.current_user;
                      message.user = $Core_IM_Firebase.users_data.hasOwnProperty(message.sender) ? $Core_IM_Firebase.users_data[message.sender] : $Core_IM_Firebase.loadUserFromServer(message.sender);
                    } else {
                      message.user = $Core_IM_Firebase.current_user;
                      message.receiver = $Core_IM_Firebase.users_data.hasOwnProperty(message.receiver) ? $Core_IM_Firebase.users_data[message.receiver] : $Core_IM_Firebase.loadUserFromServer(message.receiver);
                    }
                    if (allowHandle && message.receiver && message.user) {
                      $Core_IM_Firebase.onChatHandle(message, from_cache, true);
                    }
                  }
                }).catch(function (e) {
                  console.log('Error get snap message (initSnapshotMessage) preview', e);
                });
              }
            });
        } else {
          //Listening update from server
          $Core_IM_Firebase.current_rooms[key] = rooms[key];
          $Core_IM_Firebase.roomCollectionRef.doc(key).get().then(function (doc) {
            if (doc.exists) {
              var data = doc.data();
              $Core_IM_Firebase.current_rooms[data.id]['users'] = data.users ? data.users : {};
            }
          })
        }
      }
    });
    return true;
  },
  onChatHandle: function (chat, no_prepend, no_notice) {
    //Message loaded
    if ($('.pf-chat-message[data-message-id="' + chat.id + '"]').length) {
      return false;
    }
    var _this = $Core_IM_Firebase,
      sameUser = chat.user.id === _this.current_user.id,
      isImPopup = window.location.href.search('/im/popup'),
      c = $('#pf-chat-window-' + $Core_IM.stripThreadId(chat.thread_id)).find('.chat-row'),
      total_new = 0;
    if (!_this.noticed_threads.hasOwnProperty(chat.thread_id) && !no_prepend && chat.user.id !== _this.current_user.id &&
      chat.thread_id.indexOf(_this.current_user.uid) !== -1 && _this.notificationIsEnabled(chat.thread_id)) {
      var sound = $('#pf-im-notification-sound').get(0);
      sound.volume = 0.5;
      sound.play();
      _this.noticed_threads[chat.thread_id] = true;
    }
    if (chat.user.id !== _this.current_user.id &&
      chat.thread_id.indexOf(_this.current_user.uid) !== -1 &&
      (isImPopup !== -1) && !no_notice) {
      _this.updateUnreadCount(chat.thread_id, false, chat.id);
      document.title = '(' + _this.getUnreadCount(chat.thread_id) + ') ' + chat.user.name + ' ' +
        oTranslations['messaged_you'];
    }
    if ($Core_IM_Firebase.init_first_time) {
      if (!sameUser && !no_notice) {
        $('span#js_total_new_messages').html(Object.keys($Core_IM_Firebase.noticed_messages).length).show();
      }
      return;
    }
    var users = [chat.user.id, chat.receiver.id], total_friends = 0;
    for (var i in users) {
      if (_this.current_user.id === users[i]) {
        total_friends++;
      }
    }
    if (!total_friends) {
      $Core_IM.im_debug_mode &&
      console.log('Unable to chat with this user.');
      return;
    }

    if (!$('#pf-im').is(':visible')) {
      $Core_IM.im_debug_mode && console.log('not visible...');
    }

    // newly chat
    var chat_row = $('.chat-row[data-thread-id="' + chat.thread_id + '"]'),
      chat_form = $('#pf-chat-window-' + $Core_IM.stripThreadId(chat.thread_id)).find('.pf-im-chat-bottom-input-form textarea');
    if (
      !chat_row.length ||
      (chat_row.length && !chat_row.is(':visible')) ||
      (chat_form.length && !chat_form.is(':focus'))
    ) {
      $Core_IM.im_debug_mode && console.log('thread does not exist ', chat);
      if (chat.text === '') {
        chat.text = $Core_IM.file_preview;
      }
      if (!$('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]').length && !no_notice) {
        $Core_IM.im_debug_mode && console.log('does not exist in panel either: ' + chat.thread_id);
        var html;
        if (sameUser) {
          html = '<div class="pf-im-panel" data-user-id="' +
            chat.receiver.id + '" data-thread-id="' + chat.thread_id +
            '">' +
            '<div class="item-outer">' +
            '<div class="pf-im-panel-image">' + chat.receiver.photo_link +
            '</div>' +
            '<div class="pf-im-panel-content">' + chat.receiver.name +
            '<div class="pf-im-panel-preview">' +
            ((typeof (chat.deleted) !== 'undefined' && chat.deleted)
              ? '<i>' + oTranslations['this_message_has_been_deleted'] +
              '</i>'
              : $Core_IM.fixChatMessage(chat.text, false)) +
            '</div></div>' +
            '<div class="pf-im-panel-info"><span class="badge"></span></div></div>' +
            '</div>';
        } else {
          html = '<div class="pf-im-panel new" data-friend-id="' +
            chat.user.id + '" data-thread-id="' + chat.thread_id + '">' +
            '<div class="item-outer">' +
            '<div class="pf-im-panel-image">' + chat.user.photo_link +
            '</div>' +
            '<div class="pf-im-panel-content">' + chat.user.name +
            '<div class="pf-im-panel-preview">' +
            ((typeof (chat.deleted) !== 'undefined' && chat.deleted)
              ? '<i>' + oTranslations['this_message_has_been_deleted'] +
              '</i>'
              : $Core_IM.fixChatMessage(chat.text, false)) +
            '</div></div>' +
            '<div class="pf-im-panel-info"><span class="badge"></span></div></div>' +
            '</div>' +
            '</div>';
        }
        $('.pf-im-main').prepend(html);
        $Core_IM.updateChatTime();
        //Check thread is hidden > remove from hidden list
        if ($Core_IM_Firebase.hidden_current_rooms.length) {
          $Core_IM_Firebase.hidden_current_rooms = $Core_IM_Firebase.hidden_current_rooms.filter(function (thread) {
            return thread.id !== chat.thread_id
          });
          if (!$Core_IM_Firebase.hidden_current_rooms.length) {
            $('.pf-im-more-conversation').remove();
          }
        }
      }
      var t = null;
      if (!no_prepend) {
        var panel = $('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]'),
          old_panel_first = $('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]:first'),
          t = old_panel_first.clone();
        panel.remove();
        t.prependTo('.pf-im-main');
      } else {
        t = $('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]:first');
      }
      if (!sameUser && !no_notice) {
        // update counter
        // $Core_IM.threadMessageCounter(chat.thread_id);
        if (_this.notificationIsEnabled(chat.thread_id)) {
          t.addClass('count');
        }
      }
      if (typeof (chat.deleted) !== 'undefined' && !chat.deleted) {
        // update preview message on left side chat
        var preview = t.find('.pf-im-panel-preview');
        preview.html($Core_IM.fixChatMessage(chat.text, false));
        preview.removeClass('twa_built');
      }

      total_new = $('.p-im-DropdownMessageWrapper .pf-im-panel.count').length;
      if (!sameUser && total_new &&
        _this.notificationIsEnabled(chat.thread_id)) {
        $('span#js_total_new_messages').html(total_new).show();
      }

      setTimeout(function () {
        $Core.loadInit();
      }, 100);
      if (!chat_form.length) return;
    }

    if ($('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]').hasClass('active') && chat.receiver.uid === _this.current_user.uid) {
      if (isImPopup !== -1) {
        document.title = window.pf_im_site_title;
      }
      _this.roomCollectionRef.doc(chat.thread_id).collection('messages').doc(chat.id).set({seen: true}, {merge: true});
    }
    if (chat.receiver.uid === _this.current_user.uid) {
      //Save it
      if (typeof _this.unseen_messages[chat.thread_id] === "undefined") {
        _this.unseen_messages[chat.thread_id] = [];
      }
      _this.unseen_messages[chat.thread_id].push(chat.id);
    }

    var pre = $('.pf-im-panel[data-thread-id="' + chat.thread_id + '"]').find('.pf-im-panel-preview');
    if (typeof (chat.deleted) !== 'undefined' && !chat.deleted) {
      pre.removeClass('twa_built');
      pre.html($Core_IM.fixChatMessage(chat.text, false));
    } else {
      pre.html(oTranslations['this_message_has_been_deleted']);
    }

    c.append($Core_IM.buildMessage(chat, false, true));
    $Core_IM.updateChatTime();
    setTimeout(function(){
      c.scrollTop(c[0].scrollHeight);
      $Core.loadInit();
    }, 100);
  },
  processLoadMessage: function (thread_id) {
    var _this = $Core_IM_Firebase;
    $.ajax({
      url: PF.url.make('/im/panel'),
      data: 'users=' + _this.current_user.id + ',' + _this.decodeId(thread_id.replace(_this.current_user.uid, '')),
      contentType: 'application/json',
      'success': function (e) {
        setTimeout(function () {
          $('.pf-chat-message').each(function () {
            var t = $(this), id = t.data('user-id'), u = e[id];
            if (typeof u !== 'undefined' && typeof u.photo_link !== 'undefined' &&
              (typeof pf_im_blocked_users === 'undefined' ||
                pf_im_blocked_users.indexOf(id) === -1)) {
              t.find('.pf-chat-image').html($Core_IM.addTargetBlank(u.photo_link));
            }
            $Core_IM.updateChatTime();
            t.show();
          });
          var thread_id_parsed = $Core_IM.stripThreadId(thread_id), holder = $('#pf-chat-window-' + thread_id_parsed),
            c = holder.find('.chat-row'), chat_row_users = holder.find('.chat-row-users'), chat_form = holder.find('.chat-form');
          c.show();
          if (_this.load_first_time || chat_row_users.html() === '' || !chat_row_users.find('span').length) {
            for (var i in e) {
              var u = e[i];
              if (u === false) {
                chat_row_users.html('<span class="item-chat-user-name">' + oTranslations['deleted_user'] + '</span>');
                chat_form.html('<p class="p-im-info-gray">' + oTranslations['you_cannot_reply_this_conversation'] + '</p>');
              } else if (typeof pf_im_blocked_users !== 'undefined' && pf_im_blocked_users.indexOf(u.id) !== -1) {
                chat_row_users.html('<span class="item-chat-user-name">' + oTranslations['invalid_user'] + '</span>');
                chat_form.html('<p class="p-im-info-gray">' + oTranslations['you_cannot_reply_this_conversation'] + '</p>');
              } else if (!u.is_friend || u.is_banned) {
                chat_row_users.html('<span class="item-chat-user-name">' + $Core_IM.addTargetBlank(u.name_link) + '</span>');
                chat_form.html('<p class="p-im-info-gray">' + oTranslations['you_cannot_reply_this_conversation'] + '</p>');
              } else if (u.name_link !== 'undefined' && $Core_IM_Firebase.getUser().id !== u.id) {
                chat_row_users.html('<span class="item-chat-user-name">' + $Core_IM.addTargetBlank(u.name_link) + '</span>');
              }
            }
            if (c.length) c.scrollTop(c[0].scrollHeight);
            holder.find('.pf-chat-window-loading').remove();
            _this.load_first_time = false;
            _this.is_load_more = false;
          } else {
            holder.find('.pf-chat-row-loading').remove();
            _this.is_load_more = false;
            if(c.length) c.scrollTop(c[0].scrollHeight - $Core_IM.scrollBottom);
          }

          $Core_IM.reloadImages();
          $Core.loadInit();
        }, 500);
      }
    });
  },
  loadMoreMessage: function (thread_id) {
    var _this = $Core_IM_Firebase,
      last_message = _this.last_loaded_message.hasOwnProperty(thread_id) ? _this.last_loaded_message[thread_id] : null;
    if (!last_message) {
      return _this.loadMessages(thread_id);
    } else {
      return _this.loadMessages(thread_id, last_message);
    }
  },
  getUser: function () {
    var u = $('#auth-user');
    var link = u.data('image').match(/<a[\s]+href="(.*?)"[^<]+>(.*?)/);
    var image = u.data('image').match(/<img[^<]data-src="(.*?)"/);
    return {
      id: u.data('id'),
      uid: $Core_IM_Firebase.encodeId(u.data('id')),
      name: u.data('name'),
      link: link && link.length > 1 ? link[1] : '',
      image: image && image.length > 1 ? image[1] : '',
      photo_link: $Core_IM.addTargetBlank(u.data('image'))
    };
  },
  chatWithUser: function () {
    $Core_IM.im_debug_mode && console.log('chatWithUser');
    var chat_form = $('.chat-form');
    if (chat_form.hasClass('ui-resizable')) {
      chat_form.resizable('destroy');
    }

    $(document).off('click', '.pf-im-panel').on('click', '.pf-im-panel',function () {
      var isImPopup = window.location.href.search('/im/popup'),
        t = $(this),
        _this = $Core_IM_Firebase,
        html = '',
        t_id = t.data('thread-id'), friend_id = t.data('friend-id');
      if (!t_id && friend_id) {
        t_id = _this.combineRoomIds(friend_id, _this.current_user.id);
      }
      var t_id_parsed = $Core_IM.stripThreadId(t_id), c = $('#pf-chat-window-' + t_id_parsed);
      _this.load_first_time = true;
      // remove count when view conversation
      if (c.length) {
        // Already show chat window, open it
        c.removeClass('dock-item-collapsed');
        c.find('.chat-form textarea').trigger('focus');
        //responsive switch screen
        $('body').removeClass('p-im-buddy-screen');
        if(t.closest('.p-im-DropdownContainer').length ){
          $('#hd-message.open a').dropdown('toggle');
        }
        _this.updateUnreadCount(t_id, true);
        if ($Core_IM.new_message) {
          $Core_IM.insertAtCaret('im_chat_box-' + t_id_parsed, $Core_IM.new_message, true);
          $Core_IM.new_message = null;
        }
        return false;
      }

      t.removeClass('count');
      _this.updateUnreadCount(t_id, true);

      if (t.data('user-deleted') || t.data('user-banned') || t.data('user-blocked') || t.data('not-friend')) {
        html = $.tmpl($Core_IM.chat_action_deleted_user_tmpl, {
          'SearchThread': oTranslations['search_thread'],
          'HideThread': oTranslations['hide_thread'],
          'CannotReply': oTranslations['you_cannot_reply_this_conversation'],
          'ThreadId': t_id,
          'Users': oTranslations['deleted_user'],
          'ThreadIdParsed': t_id_parsed
        });
      } else {
        html = $.tmpl($Core_IM.chat_action_tmpl, {
          'ThreadNotification': oTranslations['noti_thread'],
          'SearchThread': oTranslations['search_thread'],
          'HideThread': oTranslations['hide_thread'],
          'AttachmentUrl': PF.url.make('/im/attachment'),
          'Send': oTranslations['send'],
          'ThreadId': t_id,
          'Users': t.find('.pf-im-panel-content .__thread-name').text(),
          'ThreadIdParsed': t_id_parsed,
          'Attachment': (typeof pf_im_attachment_enable !== 'undefined')
            ? '<i class="ico ico-plus item-action-btn" onclick="$Core_IM.imAttachFile(\'' + t_id + '\',\'' + t_id_parsed + '\')" title="' +
            oTranslations['add_attachment'] + ' (' + pf_im_attachment_types +
            ')"></i>'
            : '',
          'Twemoji': (typeof pf_im_twemoji_enable !== 'undefined')
            ? '<i class="ico ico-smile-o im_action_emotion item-action-btn" data-thread-id="' + t_id + '" id="im_action_emotion-' + t_id_parsed + '" data-action="' +
            PF.url.make('/emojis?id=im_chat_box-' + t_id_parsed) + '"></i>'
            : '',
          'UploadingMessage': oTranslations['uploading'] + '...',
        });
      }
      html = $('<div></div>').append(html).html();
      if (isImPopup !== -1) {
        document.title = window.pf_im_site_title;
      }

      c.css('opacity', '1');

      t.removeClass('is_hidden');
      if (t.hasClass('active')) {
        t.removeClass('active');
        c.hide();
        $('#pf-chat-window-active').hide();

        return false;
      }

      $('.pf-im-panel.active').removeClass('active');
      t.addClass('active');
      $('body').removeClass('p-im-buddy-screen');
      if (!t.data('thread-id')) {
        t.data('thread-id', t_id);
      }

      if (c.length) {
        c.html(html).show();
      } else {
        if (window.location.href.search('/im/popup') > 0) {
          //page all message
          $('#pf-im').prepend('<span id="pf-chat-window-active"></span><div data-thread-id="' + t_id + '" class="p-im-chat-room-block"  id="pf-chat-window-' + t_id_parsed + '" >' +
          html + '</div>');
          if($Core_IM.messages_room_active) {
            $('#pf-chat-window-' + $Core_IM.messages_room_active).remove();
          }
          $Core_IM.messages_room_active = t_id_parsed;
        } else {
          if ($Core_IM.is_small_media) {
            window.location.href = PF.url.make('/im/popup') + '?thread_id=' + t_id_parsed;
            return true;
          }
          //init Room Dock chat
          var dock_length_max =3;
          if (window.matchMedia('(max-width: 1200px)').matches){
            dock_length_max = 1;
          }
          $('.p-im-AppDock-RoomList').prepend('<div id="pf-chat-window-' + t_id_parsed + '" data-thread-id="' + t_id + '" class="p-im-float-dock-item p-im-AppDock-Room p-im-chat-room-block"><div class="dock-item-outer"><div class="dock-item-inner">' +
          html + '</div></div></div>');

          $Core_IM.dock_list.push(t_id_parsed);
          if($Core_IM.dock_list.length > dock_length_max) {
            var id_remove = $Core_IM.dock_list[0];
            $Core_IM.dock_list.splice(0,1);
            $Core_IM.cookieActiveChat($Core_IM.stripThreadId(id_remove, true), true);
            $('#pf-chat-window-' + id_remove).remove();
          }
        }
      }
      //Load partner chat
      _this.loadUserFromServer(t_id.replace(_this.current_user.uid, ''));

      //Load old message
      _this.loadMessages(t_id);

      if (!_this.notificationIsEnabled(t_id)) {
        $('#chat-action-noti-'+ t_id_parsed +' .ico').attr('class', 'ico ico-bell2-off');
      }
      $('#pf-chat-window-active').css('top', ((t.offset().top - $(window).scrollTop()) +
        (t.height() / 2)) - 5).show();

        $('#pf-chat-window-' + t_id_parsed).find('.chat-row').attr('data-thread-id', t_id);

      if (t.data('listing-id')) {
        $('.chat-form input').before('<div><input type="hidden" name="listing_id" id="pf_im_listing_id" value="' +
          t.data('listing-id') + '">');
      }

      var l = $('#pf-chat-window .fa-external-link');
      l.data('action', l.data('action') + '?thread_id=' + t_id);

      $Core_IM_Firebase.chatTextArea(t_id);
      $Core.loadInit();
      if (!$Core_IM.is_mobile) {
        $('#pf-chat-window-' + t_id_parsed).find('.chat-form input').trigger('focus');
      }

      if ($Core_IM.chat_form_min_height === 0) {
        $Core_IM.chat_form_min_height = $('.chat-form').height();
      }

      // update new message counter
      t.find('.badge').text('0');

      // update new message counter
      $Core_IM.updateNotificationCount(t_id);
      // storage active chat
      if (isImPopup === -1) {
        $Core_IM.cookieActiveChat(t_id);
      }
    });

  },
  chatTextArea: function (thread_id) {
    var threadEle = $('#pf-chat-window-' + $Core_IM.stripThreadId(thread_id));
    threadEle.find('.chat-form textarea').addClass('dont-unbind').off().on('focus', function () {
      //Hide action when focus chat
      $('.chat-form-actions').hide();
      $Core_IM.updateNotificationCount(thread_id);
      if ($Core_IM_Firebase.unseen_messages[thread_id]) {
        $Core_IM_Firebase.unseen_messages[thread_id].forEach(function (message_id) {
          $Core_IM_Firebase.roomCollectionRef.doc(thread_id).collection('messages').doc(message_id).set({seen: true}, {merge: true});
        });
      }
      //Reset unseen
      $Core_IM_Firebase.unseen_messages[thread_id] = [];
    }).on('keydown',function (e) {
      if (e.which === 13 && !e.shiftKey && !e.ctrlKey) {
        e.preventDefault();
        $Core_IM_Firebase.submitChat(thread_id);
      }
    });
    threadEle.find('.p-im-btn-send').addClass('dont-unbind').off('click').on('click', function (e) {
      e.preventDefault();
      $Core_IM_Firebase.submitChat(thread_id);
    });
  },
  submitChat: function (id) {
    var id_parsed = $Core_IM.stripThreadId(id),
      t = $('.p-im-chat-room-block[data-thread-id="' + id + '"] #im_chat_box-' + id_parsed),
      panel = $('#pf-chat-window-' + id_parsed),
      c = panel.find('.chat-row'),
      timeNow = Math.floor(Date.now()),
      l = $('#pf_im_listing_id'),
      attachment_id = (typeof t.data('attachment-id') === 'undefined')
        ? 0
        : parseInt(t.data('attachment-id')) || 0,
      text = $Core_IM.preventXss($Core_IM.filterWords(trim(t.val()))),
      _this = $Core_IM_Firebase;
    if (text.length <= 0 && attachment_id === 0) {
      return;
    }
    var receiver = $('.pf-im-panel[data-thread-id="' + id + '"]');
    var thread_id = c.data('thread-id');

    var messagesCollection = _this.roomCollectionRef.doc(thread_id).collection('messages'),
      message_id = timeNow + _this.encodeId(_this.current_user.id);
    $Core_IM.im_debug_mode && console.log('Submit...');
    c.append($Core_IM.buildMessage({
      text: text,
      id: message_id,
      time_stamp: timeNow,
      attachment_id: attachment_id,
      user: {
        photo_link: _this.current_user.photo_link,
        id: _this.current_user.id
      }
    }, false, true));

    $Core_IM.updateChatTime();
    c.scrollTop(c[0].scrollHeight);

    var message = {
      id: message_id,
      thread_id: thread_id,
      text: text,
      sender: _this.encodeId(_this.current_user.id),
      receiver: c.data('thread-id').replace(_this.current_user.uid, ''),
      time_stamp: timeNow,
      attachment_id: attachment_id,
      listing_id: (l.length ? l.val() : 0),
      deleted: false,
      seen: false
    }
    messagesCollection.doc(message_id).set(Object.assign(message, {
      server_key: pf_im_firebase_server_key,
      sender_id: pf_im_firebase_sender_id
    }));
    if (_this.algolia_index) {
      message.delete = 0;
      message.objectID = message_id;
      _this.algolia_index.saveObject(message).then(function () {
        $Core_IM.im_debug_mode && console.log('Contacts imported into Algolia');
      }).catch(function (error) {
        console.error('Error when importing contact into Algolia', error);
      });
    }
    var room_update = {
      id: thread_id,
      last_update: Math.floor(Date.now()),
      preview_text: text,
      preview_id: message_id,
      preview_deleted: false,
      users: {},
      messages: true
    };
    room_update.users[_this.current_user.uid] = 0;
    room_update.users[_this.encodeId(receiver.data('friend-id'))] = 1;
    _this.roomCollectionRef.doc(thread_id).get().then(function (doc) {
      var room = {};
      room[thread_id] = {
        id: thread_id,
        active: true,
        last_update: Math.floor(Date.now()),
        preview_id: message_id,
        preview_deleted: false,
        preview_text: text,
        messages: true
      };
      if (!doc.exists) {
        _this.updateUserDoc({
          id: receiver.data('friend-id'),
          name: receiver.find('.__thread-name').text(),
          photo_link: receiver.find('.pf-im-panel-image').html()
        }, room);
        _this.roomCollectionRef.doc(thread_id).set(room_update);
      } else {
        var data = doc.data();
        _this.updateUserDoc({
          id: receiver.data('friend-id')
        }, room);
        room_update.users[_this.encodeId(receiver.data('friend-id'))] = data.users[_this.encodeId(receiver.data('friend-id'))] + 1;
        _this.roomCollectionRef.doc(thread_id).set(room_update, {merge: true});
      }
      _this.updateUserDoc({
        id: _this.current_user.id
      }, room);
    }).catch(function (e) {
      console.log('Error get room (submitChat)', e);
    });
    $Core_IM.updateChatPreview(thread_id, text);

    t.data('attachment-id', '');
    t.val('').trigger('focus');
    // hide attachment preview
    panel.find('.chat-attachment-preview').hide();
    $Core.loadInit();
    _this.init();
  },
  notificationIsEnabled: function (thread_id) {
    return !$Core_IM_Firebase.thread_notification.hasOwnProperty(thread_id) || $Core_IM_Firebase.thread_notification[thread_id];
  },
  updateUnreadCount: function (thread_id, reset, message_id) {
    var _this = $Core_IM_Firebase;
    if (reset) {
      _this.unread_count[thread_id] = 0;
    } else {
      if (!message_id || !_this.noticed_messages.hasOwnProperty(thread_id) || !_this.noticed_messages[thread_id].hasOwnProperty(message_id)) {
        if (_this.unread_count.hasOwnProperty(thread_id)) {
          _this.unread_count[thread_id] = _this.unread_count[thread_id] + 1;
        } else {
          _this.unread_count[thread_id] = 1;
        }
      }
    }
    return true;
  },
  getUnreadCount: function (thread_id) {
    return $Core_IM_Firebase.unread_count[thread_id] || 0;
  },
  exportDataToChatPlus: function (ele) {
    var _this = this, exportError = false, exportedThreadId = [];
    if (!getParam('bIsAdminCP')) {
      return false;
    }
    _this.roomCollectionRef.get().then(function(snapshot) {
      snapshot.docs.map(function(doc, index) {
        var room = doc.data(), users = room.users || [], roomUsers = [], last_thread = index === snapshot.docs.length - 1, invalidData = false,
          roomId = room.id, time_stamp = room.last_update > 1000000000000 ? Math.round(room.last_update / 1000) : room.last_update;

        if (!roomId || users.length < 2) {
          invalidData = true;
        }

        if (exportError || exportedThreadId.indexOf(roomId) !== -1) return false;

        for (var userId in users) {
          try {
            roomUsers.push(_this.decodeId(userId));
          } catch (e) {
            console.error('Invalid user: ' + userId, e);
            invalidData = true;
          }
        }
        if (invalidData) {
          if (last_thread) {
            setTimeout(function(){
              window.location.reload();
            }, 10000);
          }
          return false;
        }
        var thread = {
          conversation_id: roomId,
          conversation_name: '',
          time_stamp: time_stamp,
          is_group: 0,
          users: roomUsers,
          last_thread: last_thread,
          messages: []
        }
        _this.roomCollectionRef.doc(roomId).collection('messages').orderBy('time_stamp', 'desc')
          .get().then(function(messages) {
          if (messages.size) {
            messages.forEach(function (message) {
              var data = message.data(), message_time = data.time_stamp > 1000000000000 ? Math.round(data.time_stamp / 1000) : data.time_stamp;
              thread.messages.push({
                message_id: data.id,
                user_id: $Core_IM_Firebase.decodeId(data.sender),
                text: data.text,
                time_stamp: message_time,
                total_attachment: data.attachment_id !== null && parseInt(data.attachment_id) > 0 ? 1 : 0,
                attachment_id: data.attachment_id || null,
                listing_id: data.listing_id,
                is_show: 1,
                is_deleted: data.deleted
              });
            });
            $.ajax({
              url: PF.url.make('/im/import-chat-plus'),
              data: {
                thread: JSON.stringify(thread)
              },
              method: 'POST',
              success: function (data) {
                if (data.success) {
                  if (last_thread) {
                    setTimeout(function(){
                      window.location.reload();
                    }, 10000);
                  }
                } else {
                  exportError = true;
                  ele.removeClass('disabled').attr('disabled', false);
                  $('#js_export_warning').fadeOut();
                  $('#global_ajax_message').hide();
                  $('#js_export_error').html(data.message).show();
                }
              },
              error: function () {
                exportError = true;
                ele.removeClass('disabled').attr('disabled', false);
                $('#js_export_warning').fadeOut();
                $('#global_ajax_message').hide();
                $('#js_export_error').html(oTranslations['opps_something_went_wrong']).show();
              }
            });
            exportedThreadId.push(roomId);
          } else if (last_thread) {
            setTimeout(function(){
              var oMess = $('#public_message');
              if (!oMess.length) {
                $('#main').prepend('<div class="public_message" id="public_message"></div>');
              }
              oMess.html(oTranslations['done_messages_will_be_exported_soon']);
              $Behavior.addModerationListener();
              window.location.reload();
            }, 10000);
          }
        });
      });
    });
  }
};

function IMFirebaseValidOldRooms(rooms) {
  var rooms_data = [], new_rooms_data = [],
    limit_rooms = pf_total_conversations == '0' ? 0 : parseInt(pf_total_conversations);
  var input = $('._pf_im_friend_search').find('input');
  for (var key in rooms) {
    if (rooms[key].active && rooms[key].messages) {
      rooms_data.push(rooms[key]);
    }
  }
  var _this = $Core_IM_Firebase;
  rooms_data.sort(_this.dynamicSort('-last_update'));
  if (limit_rooms && rooms_data.length > limit_rooms) {
    new_rooms_data = rooms_data.splice(0, limit_rooms);
    _this.hidden_current_rooms = rooms_data;
  } else {
    new_rooms_data = rooms_data;
  }
  if (!new_rooms_data.length) {
    input.length && input.attr('readonly', false);
    _this.validateOldRooms([]);
    _this.initSnapshotMessage();
    $('#pf-im .fa-spinner').hide();
    return false;
  }
  new_rooms_data.forEach(function (data, index) {
    if (index === new_rooms_data.length - 1) {
      _this.addOldRooms(data, new_rooms_data);
    } else {
      _this.addOldRooms(data);
    }
  });
  return new_rooms_data;
}

function IMFirebaseComposeMessage(param) {
  if (typeof param.user_id == "undefined" && typeof param.id != "undefined") {
    param.user_id = param.id;
  }

  if (typeof param.message != "undefined") {
    $Core_IM.new_message = param.message;
  } else {
    $Core_IM.new_message = null;
  }

  var user_id = param.user_id,
    _this = $Core_IM_Firebase;
  var thread_id = _this.combineRoomIds(_this.current_user.id, user_id);

  // open IM
  var b = $('#pf-open-im'), body = $('body');
  if ($Core_IM_Firebase.init_first_time) {
    $Core_IM_Firebase.init_first_time = false;
    _this.init_step_3 = true;
    _this.currentUserDocRef.get().then(function (doc) {
      var input = $('._pf_im_friend_search').find('input');
      if (doc.exists) {
        $Core_IM.im_debug_mode && console.log("Loaded data");
        _this.setUserDoc(true);
        var data = doc.data();
        if (data.rooms) {
          _this.loadOldRooms(data.rooms);
        } else {
          _this.validateOldRooms([]);
          input.length && input.attr('readonly', false);
          $('#pf-im .fa-spinner').hide();
        }
      } else {
        input.length && input.attr('readonly', false);
        $('#pf-im .fa-spinner').hide();
        _this.validateOldRooms([]);
        _this.setUserDoc();
      }
    }).catch(function (error) {
      console.log("Error getting user document (init)", error);
    }).then(function () {
      processComposeNextStep();
    });
  } else {
    processComposeNextStep();
  }

  function processComposeNextStep() {
    // lock scroll on ios
    if ($Core_IM.isIos()) {
      body.css('position', 'fixed');
    }
    $('#pf-im').show();
    $('#pf-im-wrapper').show();
    if (!b.data('fake-click') ||
      (b.data('fake-click') && b.data('fake-click') == '0')) {
      body.addClass('im-is-active');
    }

    $('.pf-im-panel.active').removeClass('active');
    // clear search
    var search_user = $('._pf_im_friend_search input');
    search_user.val('');
    $('.pf-im-main').show();
    $('.pf-im-search-user').hide();
    var IMFirebaseComposeMessageInterval = window.setInterval(function () {
      if (!$Core_IM_Firebase.init_step_3) {
        window.clearInterval(IMFirebaseComposeMessageInterval);
        var thread = $('.pf-im-panel[data-thread-id="' + thread_id + '"]');
        if (thread.length > 0) {
          thread.trigger('click');
          return false;
        }

        var f = $('.pf-im-menu a[data-type="2"]');
        if (f.hasClass('active')) {
          f.removeClass('active');
          $('.pf-im-menu a[data-type="1"]').addClass('active');
        }

        var is_listing = (typeof (param.listing_id) === 'number');
        $.ajax({
          url: PF.url.make('/im/conversation') + '?user_id=' + param.user_id +
            '&listing_id=' + (is_listing ? param.listing_id : '0'),
          contentType: 'application/json',
          success: function (resp) {
            if (typeof (resp.error) === 'string') {
              $Core_IM.imFailed();
              return;
            }

            var e = resp.user,
              new_thread_id = _this.combineRoomIds(_this.current_user.id, e.id),
              m = $('.pf-im-panel[data-thread-id="' + new_thread_id + '"]');
            if (!m.length) {
              $.tmpl($Core_IM.panel_tmpl, {
                'UserId': e.id,
                'ThreadId': new_thread_id,
                'PhotoLink': e.photo_link,
                'Name': e.name,
              }).prependTo('.pf-im-main');
            }
            _this.roomCollectionRef.doc(new_thread_id).get().then(function (doc) {
              if (doc.exists && doc.data()) {
                var data = doc.data();
                var preview_id = data.preview_id || 0;
                if (preview_id) _this.loadSearchPreview({
                  text: data.preview_text || '',
                  thread_id: new_thread_id,
                  deleted: data.preview_deleted || false
                }, preview_id, new_thread_id);
              } else {
                _this.setRoomDoc(thread_id, [_this.current_user.id, user_id], e);
              }
            }).catch(function (e) {
              console.log('Error get room (composeMessage)', e);
            });

            $Core.loadInit();
            m = $('.pf-im-panel[data-thread-id="' + new_thread_id + '"]');
            m.removeClass('active');
            if (is_listing) {
              m.data('listing-id', param.listing_id);
            }
            m.trigger('click');
          }
        });
      }
    }, 1000);
  }
}

function CoreImInitServer() {
  if (pf_im_chat_server === 'nodejs') {
    $Core_IM.init();
  } else {
    $Core_IM_Firebase.init();
  }
  if (typeof pf_im_using_host !== 'undefined' && pf_im_using_host && ($('.pf_im_is_hosted').length === 0)) {
    var html = '<div class="pf_im_is_hosted"><span>Active Hosting: Starter at $5 / month</span></div>';
    $('input[name="val[value][pf_im_node_server]"]').val(pf_im_node_server).attr('disabled', true).after(html);
    $('.app_grouping').show();
  }
  if (window.location.href.search('/im/popup') > 0) {
    $('body').addClass('p-im-buddy-screen');
  }
  return true;
}

if (/iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
  navigator.userAgent)) {
  $Core_IM.is_mobile = true;
}
if (window.matchMedia('(max-width: 767px)').matches) {
  $Core_IM.is_small_media = true;
}
$Ready(function () {
  $(document).off('click', '#js-submit_delete_all_nodejs').on('click', '#js-submit_delete_all_nodejs', function () {
    var ele = $(this), form = ele.closest('form');
    $Core.jsConfirm({message: oTranslations['are_you_sure_all_message_will_be_deleted']}, function () {
      ele.addClass('disabled').attr('disabled', true);
      $('#global_ajax_message').html('<i class="fa fa-spinner fa-pulse"></i>').show();
      $.ajax({
        url: PF.url.make('/im/login-admin'),
        data: {
          email: form.find('#email').val(),
          password: form.find('#password').val()
        },
        contentType: 'application/json',
        success: function (data) {
          if (!data.success) {
            window.parent.sCustomMessageString = data.message;
            tb_show(oTranslations['error'], $.ajaxBox('core.message', 'height=150&width=300'));
            ele.removeClass('disabled').attr('disabled', false);
            $('#global_ajax_message').hide();
            return false;
          }
          $Core_IM.start_im(true);
          setTimeout(function () {
            if ($Core_IM.socket) {
              $Core_IM.socket.emit('delete_all');
              $Core_IM.socket.on('delete_all', function () {
                $('#js-submit_delete_all_nodejs').removeClass('disabled').attr('disabled', false);
                $('#global_ajax_message').hide();
                window.parent.sCustomMessageString = oTranslations['all_old_messages_removed_successfully'];
                tb_show(oTranslations['notice'], $.ajaxBox('core.message', 'height=150&width=300'));
              });
            }
          }, 500);
        }
      })
    }, function () {

    });
  })

  $(document).off('click', '#js-submit_export_all_nodejs').on('click', '#js-submit_export_all_nodejs', function () {
    var ele = $(this), exportError = false, exportedThreadId = [];
    $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
      ele.addClass('disabled').attr('disabled', true);
      $('#js_export_warning').fadeIn();
      $('#global_ajax_message').html('<i class="fa fa-spinner fa-pulse"></i>').show();
      $Core_IM.start_im(true);
      if ($Core_IM.socket) {
        $Core_IM.socket.emit('loadAllThreads');
        $Core_IM.socket.on('loadAllThreads', function (thread, thread_id) {
          if (exportError || exportedThreadId.indexOf(thread_id) !== -1) return false;

          var messages = thread.messages, last_thread = thread.last_thread || false;
          if (!messages.length) {
            if (last_thread) {
              setTimeout(function() {
                window.location.reload();
              }, 10000);
            }
            return false;
          }
          messages.reverse();
          var convertedMessages = [], last_update = '';
          messages.forEach(function(message, index) {
            var data = JSON.parse(message), time_stamp = data.time_stamp > 1000000000000 ? Math.round(data.time_stamp / 1000) : data.time_stamp;
            if (!index) {
              last_update = time_stamp;
            }
            convertedMessages.push({
              message_id: data.thread_id + ':' + time_stamp,
              user_id: data.user.id || 0,
              text: data.text,
              time_stamp: time_stamp,
              total_attachment: data.attachment_id !== null && parseInt(data.attachment_id) > 0 ? 1 : 0,
              attachment_id: data.attachment_id || null,
              listing_id: data.listing_id,
              is_show: 1,
              is_deleted: data.deleted
            })
          });
          thread.messages = convertedMessages;
          thread.is_group = 0;
          thread.last_thread = last_thread;
          thread.conversation_name = '';
          thread.time_stamp = last_update;
          thread.conversation_id = thread_id;

          delete thread.thread_id;

          $.ajax({
            url: PF.url.make('/im/import-chat-plus'),
            data: {
              thread: JSON.stringify(thread)
            },
            method: 'POST',
            success: function (data) {
              if (data.success) {
                if (last_thread) {
                  setTimeout(function(){
                    window.location.reload();
                  }, 10000);
                }
              } else {
                exportError = true;
                ele.removeClass('disabled').attr('disabled', false);
                $('#js_export_warning').fadeOut();
                $('#global_ajax_message').hide();
                $('#js_export_error').html(data.message).show();
              }
            },
            error: function () {
              exportError = true;
              ele.removeClass('disabled').attr('disabled', false);
              $('#js_export_warning').fadeOut();
              $('#global_ajax_message').hide();
              $('#js_export_error').html(oTranslations['opps_something_went_wrong']).show();
            }
          });
          exportedThreadId.push(thread_id);
        });
      }
    }, function () {

    });
  })

  $(document).off('click', '#js-submit_export_all_firebase').on('click', '#js-submit_export_all_firebase', function () {
    var ele = $(this);
    $Core.jsConfirm({message: oTranslations['are_you_sure']}, function () {
      ele.addClass('disabled').attr('disabled', true);
      $('#js_export_warning').fadeIn();
      $('#global_ajax_message').html('<i class="fa fa-spinner fa-pulse"></i>').show();
      var _this = $Core_IM_Firebase;
      if ($Core_IM_Firebase.IM_Firebase == null && firebaseConfig) {
        _this.IM_Firebase = firebase.initializeApp(firebaseConfig, 'pf_im');
        _this.usersCollectionRef = _this.IM_Firebase.firestore().collection('users');
        _this.roomCollectionRef = _this.IM_Firebase.firestore().collection('rooms');
        _this.current_user = $Core_IM_Firebase.getUser();
        _this.currentUserDocRef = _this.usersCollectionRef.doc(_this.encodeId(_this.current_user.id));
        var loginFirebaseUserForExport = function () {
          if (typeof firebasePassword === "undefined" || !firebasePassword) {
            return false;
          }
          _this.IM_Firebase.auth().signInWithEmailAndPassword(_this.getFirebaseEmail(), firebasePassword)
            .then(function (data) {
              $Core_IM.im_debug_mode && console.log('Firebase signInWithEmailAndPassword success', data);
              _this.exportDataToChatPlus(ele);
            })
            .catch(function (error) {
              console.log('Firebase signInWithEmailAndPassword error', error);
              if (error.code === 'auth/user-not-found') {
                _this.IM_Firebase.auth().createUserWithEmailAndPassword(_this.getFirebaseEmail(), firebasePassword)
                  .then(function (data) {
                    $Core_IM.im_debug_mode && console.log('createFirebaseUser success', data);
                    _this.exportDataToChatPlus(ele);
                  }).catch(function (error) {
                    console.log('createFirebaseUser error', error);
                });
              }
            });
        }
      } else {
        _this.exportDataToChatPlus(ele);
      }
    }, function () {});
  });
});

$(window).on('resize',function() {
  $Core_IM.allChatGetPosition();
  if (window.matchMedia('(max-width: 767px)').matches) {
    $Core_IM.is_small_media = true;
  }
});

PF.event.on('on_document_ready_end', function() {
  CoreImInitServer();
});

PF.event.on('on_page_change_end', function() {
  CoreImInitServer();
});

PF.event.on('on_page_load_init_end', function () {
  if (pf_im_chat_server === 'nodejs') {
    $Core_IM.init();
  } else {
    $Core_IM_Firebase.loadWhenCoreInit();
  }
});