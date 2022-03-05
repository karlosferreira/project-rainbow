$Core.Groups = {
  searching: false,
  searchAction: null,
  cropmeImgSrc: '',
  showSuccessMessage: function(message) {
    let publicMessageObj = $('#public_message');
    if (publicMessageObj.length === 0) {
      $('#main').prepend('<div class="public_message" id="public_message"></div>');
    }
    publicMessageObj.html(message);
    $Behavior.addModerationListener();
  },
  deleteGroup: function(obj) {
    let _this = $(obj),
      groupId = _this.data('id'),
      message = _this.data('message');

    $Core.jsConfirm({message: message}, function() {
      $.ajaxCall('groups.delete', $.param({
        id: groupId
      }));
    });

    return false;
  },
  reassignOwner: function(obj) {
    let _this = $(obj),
      groupId = _this.data('id'),
      confirmMessage = _this.data('message') ? _this.data('message') : null;

    $Core.jsConfirm({message: confirmMessage}, function () {
      $('#js_group_reassign_submit').addClass('disabled').attr('disabled', true);
      $('#js_group_reassign_loading').show();
      $.fn.ajaxCall('groups.reassignOwner', 'page_id=' + groupId + '&user_id='+ $('#js_reassign_owner_group #search_friend_single_input').val(), null, null, function() {
        $('#js_group_reassign_submit').removeClass('disabled').removeAttr('disabled');
        $('#js_group_reassign_loading').hide();
      });
    }, function () {
      $('#js_group_reassign_submit').removeClass('disabled').removeAttr('disabled');
      $('#js_group_reassign_loading').hide();
    });

    return false;
  },
  profilePhoto: {
    holderId: 'groups_photo_form',
    parentContainer: null,
    dropzoneId: null,
    currentRotation: 0,
    reset: function() {
      this.currentRotation = 0;
    },
    isModified: function() {
      return $Core.reloadValidation.changedEleData.hasOwnProperty('js_form_groups_crop_me')
        && $Core.reloadValidation.changedEleData['js_form_groups_crop_me'].hasOwnProperty('crop_me');
    },
    init: function() {
      let _this = this;
      if (typeof currentGroupId !== "undefined" && $('._is_groups_view').length > 0 && $('#' + this.holderId).length === 0) {
        this.dropzoneId = 'groups-dropzone_' + currentGroupId;
        tb_show('', $.ajaxBox('groups.cropme', $.param({width: '500', allow_upload: 1, id: currentGroupId})), null, null, null, null, true, this.holderId);
      }
    },
    submit: function (obj) {
      let _form = $(obj);

      if (!_form.find('.js_rotation_degree').length) {
        _form.append('<input class="js_rotation_degree" type="hidden" name="val[rotation]" value="' + this.currentRotation + '"/>');
      } else {
        $('.js_rotation_degree', _form).val(this.currentRotation);
      }

      if ($('.image-editor').length) {
        if (!_form.find('.js_zoom').length) {
          _form.append('<input class="js_zoom" type="hidden" name="val[zoom]" value="' + $('.image-editor').cropit('zoom') + '"/>');
        } else {
          $('.js_zoom', _form).val($('.image-editor').cropit('zoom'));
        }

        let cropCoordinate = $('.image-editor').cropit('offset'),
          previewSize = $('.image-editor').cropit('previewSize');
        if (typeof cropCoordinate === "object" && cropCoordinate) {
          if (!_form.find('.js_crop_coordinate').length) {
            _form.append('<input class="js_crop_coordinate offset_x" type="hidden" name="val[crop_coordinate][x]" value="' + cropCoordinate.x + '"/>');
            _form.append('<input class="js_crop_coordinate offset_y" type="hidden" name="val[crop_coordinate][y]" value="' + cropCoordinate.y + '"/>');
          } else {
            $('.js_crop_coordinate.offset_x', _form).val(cropCoordinate.x);
            $('.js_crop_coordinate.offset_y', _form).val(cropCoordinate.y);
          }
        }

        if (typeof previewSize === "object" && previewSize) {
          if (!_form.find('.js_preview_size').length) {
            _form.append('<input class="js_preview_size width" type="hidden" name="val[preview_size][width]" value="' + previewSize.width + '"/>');
            _form.append('<input class="js_preview_size height" type="hidden" name="val[preview_size][height]" value="' + previewSize.height + '"/>');
          } else {
            $('.js_preview_size.width', _form).val(previewSize.width);
            $('.js_preview_size.height', _form).val(previewSize.height);
          }
        }
      }
      _form.ajaxCall('groups.processCropme');
      _form.find('.js_submit_btn').prop('disabled', true);

      return false;
    },
    openUploadPopup: function(obj) {
      let _this = $(obj),
        uploadForm = _this.closest('form').find('#' + this.dropzoneId);
      if (uploadForm.length) {
        uploadForm.find('.dropzone-button').trigger('click');
      }
      return false;
    },
    showHiddenBox: function () {
      $('#' + this.holderId).removeClass('hidden');
      if (typeof $Core.disableScroll !== 'undefined' && (typeof hidden === 'undefined' || !hidden)) {
        $Core.disableScroll();
      }
    },
    closeHiddenBox: function (ele) {
      $('#' + this.holderId).addClass('hidden');
      if (typeof $Core.enableScroll !== 'undefined' && (typeof hidden === 'undefined' || !hidden)) {
        $Core.enableScroll();
      }
      $Core.Groups.blockPhotoReloadValidation.reset();
    },
    update: function (hasPhoto) {
      if (hasPhoto === false) {
        $(this.parentContainer).removeClass('profile-image-error');
        $('.dropzone-button', '#' + this.dropzoneId).trigger('click');
      } else {
        this.showHiddenBox();
        $Core.Groups.initCropMe();
      }
      return false;
    },
    actionCropFormButtons: function (disabled) {
      $('.rotate_button button', $(this.parentContainer)).prop('disabled', !!disabled);
      if (disabled) {
        $('.rotate_button a', $(this.parentContainer)).addClass('disabled');
      } else {
        $('.rotate_button a', $(this.parentContainer)).removeClass('disabled');
      }
    },
    onSuccessUpload: function (ele, file, response) {
      let responseType = typeof response;
      if (['string', 'object'].indexOf(responseType) >= 0) {
        var data = typeof response === 'string' ? JSON.parse(response) : response,
          profileCropMeContainer = $(this.parentContainer);
        profileCropMeContainer.removeClass('profile-image-uploading profile-image-error');
        $Core.Groups.crop({
          imagePath: data.imagePath,
        });
        if (data.hasOwnProperty('pendingPhoto')) {
          if (data.hasOwnProperty('warningMessage')) {
            window.parent.sCustomMessageString = data['warningMessage'];
            tb_show(data.hasOwnProperty('warningTitle') ? data['warningTitle'] : '', $.ajaxBox('core.message', 'height=150&width=300'));
          }
        } else if (data.hasOwnProperty('tempFileId')) {
          if (profileCropMeContainer.find('.js_temp_file_id').length) {
            profileCropMeContainer.find('.js_temp_file_id').val(data['tempFileId']);
          } else {
            $('<input/>', {
              type: 'hidden',
              value: data['tempFileId'],
              class: 'js_temp_file_id',
              name: 'val[temp_file]',
            }).prependTo(profileCropMeContainer.find('form:first'));
          }
        }
        if ($('#js_upload_avatar_action').length) {
          $('#js_upload_avatar_action').attr('onclick', 'return $Core.Groups.profilePhoto.update(true);');
        }
      } else if (typeof response.run !== 'undefined') {
        eval(response.run);
      }
      $Core.Groups.blockPhotoReloadValidation.changedEleData['upload_new'] = 1;
      $Core.Groups.blockPhotoReloadValidation.validate();
      this.actionCropFormButtons(false);
      $('.rotate_button', $(this.parentContainer)).removeClass('hide');
      $('.cropit-image-zoom-input', $(this.parentContainer)).removeClass('hide');
    },
    onAddedFile: function () {
      $(this.parentContainer).addClass('profile-image-uploading').removeClass('profile-image-error');
      this.showHiddenBox();
      this.actionCropFormButtons(true);
      $(this.parentContainer).find('#js_upload_form_groups_wrapper').parent().removeClass('hide');
    },
    onError: function () {
      $(this.parentContainer).removeClass('profile-image-uploading').addClass('profile-image-error');
      this.actionCropFormButtons(false);
    },
  },
  initCropMe: function() {
    this.crop();
    function checkRotateReload(is_minus) {
      if (is_minus) {
        $Core.Groups.blockPhotoReloadValidation.changedEleData['rotation'] -= 1;
      } else {
        $Core.Groups.blockPhotoReloadValidation.changedEleData['rotation'] += 1;
      }

      if (Math.abs($Core.Groups.blockPhotoReloadValidation.changedEleData['rotation']) % 4 === 0) {
        $Core.Groups.blockPhotoReloadValidation.changedEleData['rotation'] = 0;
      }

      $Core.Groups.blockPhotoReloadValidation.validate();
    }

    $('.js-groups-rotate-cw').off('click').click(function() {
      $('.image-editor').cropit('rotateCW');
      $Core.Groups.profilePhoto.currentRotation = ($Core.ProfilePhoto.currentRotation + 90) % 360;
      checkRotateReload(false);
    });

    $('.js-groups-rotate-ccw').off('click').click(function() {
      $('.image-editor').cropit('rotateCCW');
      $Core.Groups.profilePhoto.currentRotation = ($Core.ProfilePhoto.currentRotation + 270) % 360;
      checkRotateReload(true);
    });

    $('.export').click(function() {
      var imageData = $('.image-editor').cropit('export');
      window.open(imageData);
    });
  },
  crop: function(params) {
    if (typeof params === 'object' && params.hasOwnProperty('imagePath')) {
      var imagePath = params['imagePath'];
      $('.image-editor').cropit('destroy');
    } else {
      var imagePath = this.cropmeImgSrc;
    }

    if (imagePath === '') {
      return false;
    }

    $('.image-editor').cropit({
      imageState: {
        src: imagePath,
      },
      smallImage: 'allow',
      maxZoom: 2,
      allowDragNDrop: false,
      onImageLoaded: function () {
        $Core.Groups.blockPhotoReloadValidation.init($('.image-editor').cropit('zoom'), 0);
      },
      onZoomChange: function () {
        if (typeof $Core.reloadValidation.initEleData['js_form_groups_crop_me'] !== 'undefined') {
          $Core.Groups.blockPhotoReloadValidation.changedEleData['zoom'] = $('.image-editor').cropit('zoom');
          $Core.Groups.blockPhotoReloadValidation.validate();
        }
      }
    });
  },
  cmds: {
    add_new_group: function(ele, evt) {
      tb_show(oTranslations['add_new_group'],
          $.ajaxBox('groups.addGroup', 'height=400&width=550&type_id=' +
              ele.data('type-id')));
      return false;
    },

    select_category: function(ele, evt) {
      $('[class^=select-category-]').hide();
      $('.select-category-' + ele.val()).show();
      $('#select_sub_category_id').val(0);
    },

    add_group_process: function(ele, evt) {
      evt.preventDefault();
      ele.ajaxCall('groups.add');
      // disable submit button
      var submit = $('input[type="submit"]', ele);
      submit.prop('disabled', true).addClass('submitted');
    },

    widget_add_form: function(ele, evt) {
      if (ele.val() === '1') {
        $('#js_groups_widget_block').slideUp('fast');
      } else {
        $('#js_groups_widget_block').slideDown('fast');
      }
    },

    init_drag: function(ele) {
      Core_drag.init({table: ele.data('table'), ajax: ele.data('ajax')});
    },

    search_member: function(ele, evt) {
      if ($Core.Groups.searching === true) {
        return;
      }

      let no_member = $('.no-members-found').length,
        admin_tab = $('.group-admins.active').length,
        pending_tab = $('.pending-memberships.active').length;

      if (($('.moderation_placeholder.hide').length && !pending_tab && !admin_tab && !no_member)){
        $('.moderation_placeholder').removeClass('hide');
      }

      clearTimeout($Core.Groups.searchAction);
      $Core.Groups.searchAction = setTimeout(function() {
        // process searching
        $Core.Groups.searching = true;

        var parentBlock = $('.groups-block-members'),
            activeTab = $('li.active a', parentBlock),
            container = $(ele.data('container')),
            resultContainer = ele.val() ? ele.data('result-container') : ele.data('listing-container'),
            spinner = $('.groups-searching', parentBlock);

        container.addClass('hide');
        spinner.removeClass('hide');
        $.ajaxCall('groups.getMembers', 'tab=' + activeTab.data('tab') + '&container=' + resultContainer + '&group_id=' + ele.data('group-id') + '&search=' + ele.val());
      }, 500);
    },

    change_tab: function(ele, evt) {
      evt.preventDefault();
      var container = $(ele.data('container')),
          resultCotainer = $(ele.data('result-container'));

      // hide search result div, show container div
      container.hasClass('hide') && container.removeClass('hide');
      !resultCotainer.hasClass('hide') && resultCotainer.addClass('hide');

      // only show moderation in `all members` tab
      if (ele.data('tab') === 'all') {
        $('.moderation_placeholder').removeClass('hide');
      } else {
        $('.moderation_placeholder').addClass('hide');
      }

      // ajax call to get tab members
      $.ajaxCall('groups.getMembers', 'tab=' + ele.data('tab') + '&container=' + ele.data('container') + '&group_id=' + ele.data('group-id'));
    },

    remove_member: function(ele, evt) {
      $Core.jsConfirm({
        message: ele.data('message')
      }, function() {
        $.ajaxCall('groups.removeMember', 'group_id=' + ele.data('group-id') + '&user_id=' + ele.data('user-id'))
      }, function() {});
    },

    remove_pending: function(ele, evt) {
      $Core.jsConfirm({
        message: ele.data('message')
      }, function() {
        $.ajaxCall('groups.removePendingRequest', 'sign_up=' + ele.data('signup-id') + '&user_id=' + ele.data('user-id'));
      }, function() {});
    },

    remove_admin: function(ele, evt) {
      $Core.jsConfirm({
        message: ele.data('message')
      }, function() {
        $.ajaxCall('groups.removeAdmin', 'group_id=' + ele.data('group-id') + '&user_id=' + ele.data('user-id'))
      }, function() {});
    },

    disable_submit: function(form) {
      $('input[type="submit"]', form).prop('disabled', true).addClass('submitted');
    },

    join_group: function(ele, evt) {
      ele.fadeOut('fast', function() {
        if (ele.data('is-closed') == 1) {
          ele.prev().fadeIn('fast');
          $.ajaxCall('groups.signup', 'page_id=' + ele.data('id')); return false;
        } else {
          ele.prev().prev().fadeIn('fast');
          $.ajaxCall('like.add', 'type_id=groups&item_id=' + ele.data('id')); return false;
        }
      });
    },

    toggleActivePageMenu: function (ele, evt) {
      if ($(ele).length) {
        $(ele).ajaxCall('groups.toggleActivePageMenu', $.param({
          'menu_id': $(ele).attr('data-menu-id'),
          'menu_name': $(ele).attr('data-menu-name'),
          'page_id': $(ele).attr('data-page-id'),
          'is_active': $(ele).is(":checked") ? 1 : 0
        }), 'post', null, function (data, self) {
          if (data) {
            $(ele).attr('data-menu-id', data)
          }
        });
        return false;
      }
    }
  },

  setAsCover: function(iPageId, iPhotoId) {
    $.ajaxCall('groups.setCoverPhoto', 'page_id=' + iPageId +
        '&photo_id=' + iPhotoId);
  },

  removeCover: function(iPageId) {
    $Core.jsConfirm({}, function() {
      $.ajaxCall('groups.removeCoverPhoto', 'page_id=' + iPageId);
    }, function() {});
  },

  resetSubmit: function() {
    $('input[type="submit"].submitted').each(function() {
      $(this).prop('disabled', false).removeClass('submitted');
    });
  },

  searchingDone: function(searchingDone) {
    $Core.Groups.searching = false;
    $('.groups-searching').addClass('hide');

    if (typeof searchingDone === 'boolean' && searchingDone) {
      $('.search-member-result').removeClass('hide');
      $('.groups-member-listing').empty();
      this.actionModeration();
    }
  },
  
  updateCounter: function(selector) {
    var ele = $(selector),
        counter = ele.html().substr(1, ele.html().length - 2);

    ele.html('('+ (parseInt(counter) - 1) +')');
  },

  hideSearchResults: function() {
    $Core.Groups.searching = false;
    $('.groups-searching').addClass('hide');
    $('.search-member-result').empty();
    this.actionModeration();
  },
  initGroupCategory: function () {
    // Creating/Editing groups
    if ($Core.exists('#js_groups_add_holder')) {
      $(document).on('change', '.groups_add_category select', function() {
        var detailBlock = $('#js_groups_block_detail');
        $('.js_groups_add_sub_category', detailBlock).hide();
        $('#js_groups_add_sub_category_' + $(this).val(), detailBlock).show();
        $('#js_category_groups_add_holder').
        val($('#js_groups_add_sub_category_' + $(this).val() +' option:first', detailBlock).val());
      });

      $(document).on('change', '.js_groups_add_sub_category select', function() {
        $('#js_category_groups_add_holder').val($(this).val());
      });
    }
  },
  processHideBtnInGroupFeed: function(type, ajaxCall, parentObject) {
    if (type === 'like') {
      if (ajaxCall === 'like.add') {
        var oppositeTypes = ['unlike'];
      } else {
        var oppositeTypes = ['request'];
      }
    } else {
      var oppositeTypes = ['like'];
    }

    $.each(oppositeTypes, function(key, value) {
      if ($('.js_group_action_btn[data-type="' + value + '"]', parentObject).length) {
        $('.js_group_action_btn[data-type="' + value + '"]', parentObject).parent().removeClass('hide');
      }
    });
  },
  processGroupFeed: function(oObj){
      var oThis = $(oObj),
        type = oThis.data('type'),
        oActionContent = oThis.closest('.js_group_feed_action_content'),
        regMethod = oActionContent.data('privacy') ? oActionContent.data('privacy') : 0,
        sAjaxCall = oThis.data('ajax'),
        groupId = oActionContent.data('group-id');

      if (!sAjaxCall || !type || !groupId) {
        return
      }

      var requestParams = ['groups.signup', 'groups.deleteRequest'].indexOf(sAjaxCall) !== -1 ? {
          page_id: groupId,
          request_inline: 1,
        } : {
          type_id: 'groups',
          item_id: groupId,
        };

      oThis.prop('disabled', true);
      $.fn.ajaxCall(sAjaxCall, $.param(requestParams), null, null, function() {
        oThis.prop('disabled', false);
        oThis.parent().addClass('hide');
        $Core.Groups.processHideBtnInGroupFeed(type, sAjaxCall, oThis.closest('.js_group_feed_action_content'));
      });

      return false;
  },
  redirectToDetailGroup: function(oObj)
  {
      var sUrl = $(oObj).data('url');
      if(sUrl)
      {
          window.location.href = sUrl;
      }
  },
  processEditFeedStatus: function(feed_id, status, deleteLink) {
      if($('#js_item_feed_' + feed_id).length) {
          var parent = $('#js_item_feed_' + feed_id).find('.activity_feed_content_text:first');
          if(parent.find('.activity_feed_content_status:first').length) {
              parent.find('.activity_feed_content_status:first').html(status);
          }
          else {
              parent.prepend('<div class="activity_feed_content_status">' + status + '</div>');
          }
          if(typeof deleteLink !== 'undefined') {
              $("#js_item_feed_" + feed_id).find(".activity_feed_content_link").remove();
          }
      }
  },

  //check edit thumbnail
  blockPhotoReloadValidation: {
    parentFormId: 'js_form_groups_crop_me',
    changedEleData: [],
    init: function (zoom, rotation) {
      var parentObject = this;
      if (!$Core.reloadValidation.initEleData.hasOwnProperty(parentObject.parentFormId)) {
        $Core.reloadValidation.initEleData[parentObject.parentFormId] = {};
      }

      $Core.reloadValidation.initEleData[parentObject.parentFormId]['zoom'] = zoom;
      $Core.reloadValidation.initEleData[parentObject.parentFormId]['rotation'] = rotation;

      $Core.Groups.blockPhotoReloadValidation.changedEleData['zoom'] = zoom;
      $Core.Groups.blockPhotoReloadValidation.changedEleData['rotation'] = rotation;
    },
    validate: function () {
      var parentObject = this;
      if (typeof parentObject.changedEleData !== "undefined" && Object.keys(parentObject.changedEleData).length) {
        if (!$Core.reloadValidation.changedEleData.hasOwnProperty(parentObject.parentFormId)) {
          $Core.reloadValidation.changedEleData[parentObject.parentFormId] = {};
        }

        let bIsChange = false;

        if (parentObject.changedEleData.hasOwnProperty('zoom')) {
          bIsChange = !$Core.reloadValidation.initEleData.hasOwnProperty(parentObject.parentFormId)
            || !$Core.reloadValidation.initEleData[parentObject.parentFormId].hasOwnProperty('zoom')
            || parentObject.changedEleData['zoom'] !== $Core.reloadValidation.initEleData[parentObject.parentFormId]['zoom'];
        }

        if (!bIsChange && parentObject.changedEleData.hasOwnProperty('rotation')) {
          bIsChange = !$Core.reloadValidation.initEleData.hasOwnProperty(parentObject.parentFormId)
            || !$Core.reloadValidation.initEleData[parentObject.parentFormId].hasOwnProperty('rotation')
            || parentObject.changedEleData['rotation'] !== $Core.reloadValidation.initEleData[parentObject.parentFormId]['rotation'];
        }

        if (!bIsChange) {
          bIsChange = parentObject.changedEleData.hasOwnProperty('upload_new');
        }

        if (bIsChange) {
          $Core.reloadValidation.changedEleData[parentObject.parentFormId]['crop_me'] = true;
        } else if ($Core.reloadValidation.changedEleData[parentObject.parentFormId].hasOwnProperty('crop_me')) {
          delete $Core.reloadValidation.changedEleData[parentObject.parentFormId]['crop_me'];
        }

        $Core.reloadValidation.preventReload();
      }
    },
    reset: function() {
      this.changedEleData = {};
      if ($Core.reloadValidation.changedEleData.hasOwnProperty(this.parentFormId)
        && $Core.reloadValidation.changedEleData[this.parentFormId].hasOwnProperty('crop_me')) {
        delete $Core.reloadValidation.changedEleData[this.parentFormId]['crop_me'];
        $Core.reloadValidation.preventReload();
      }
    },
  },

  blockInviteReloadValidation: {
    init: function () {
      this.validate();
    },

    validate: function () {
      function _checkValidate(parentFormId) {
        if (!empty(parentFormId) && parseInt(trim($('#js_form_groups_add #deselect_all_friends').children().text())) > 0) {
          if (!$Core.reloadValidation.changedEleData.hasOwnProperty(parentFormId)) {
            $Core.reloadValidation.changedEleData[parentFormId] = {};
          }

          $Core.reloadValidation.changedEleData[parentFormId]['invite_friend'] = true;
        } else {
          delete $Core.reloadValidation.changedEleData[parentFormId]['invite_friend'];
        }

        $Core.reloadValidation.preventReload();
      }

      $(document).on('click', '#js_form_groups_add #js_friend_search_content input[type="checkbox"], #js_form_groups_add #deselect_all_friends, #js_form_groups_add #selected_friends_list li[data-id]',function () {
        _checkValidate('js_form_groups_add');
      })
    }
  },

  blockInviteAdminReloadValidation: {
    parentFormId: 'js_form_groups_add',
    eleName: 'admins[]',
    init: function () {
      this.store();
      this.validate();
    },

    store: function () {
      var parentObject = this;

      if (!$Core.reloadValidation.initEleData.hasOwnProperty(parentObject.parentFormId)) {
        $Core.reloadValidation.initEleData[parentObject.parentFormId] = {};
      }

      if (!$Core.reloadValidation.initEleData[parentObject.parentFormId].hasOwnProperty(parentObject.eleName)) {
        $Core.reloadValidation.initEleData[parentObject.parentFormId][parentObject.eleName] = [];
      }

      $('#js_form_groups_add #js_custom_search_friend_placement input[name="admins[]"]').each(function () {
        var _this = $(this);
        $Core.reloadValidation.initEleData[parentObject.parentFormId][parentObject.eleName].push(trim(_this.val()));
      });
    },

    validate: function () {
      var parentObject = this;

      function reBindEventRemove() {
        $('#js_form_groups_add #js_custom_search_friend_placement .friend_search_remove').each(function () {
          $(this).attr('onclick', '$Core.Groups.blockInviteAdminReloadValidation.validateWhenRemove(this); return false;')
        });
      }

      $('#js_form_groups_add .js_temp_friend_search_form').on('click', function () {
        if (parentObject._checkValidate(parentObject.parentFormId)) {
          reBindEventRemove();
        }
      });

      reBindEventRemove();
    },

    _checkValidate: function (parentFormId) {
      var parentObject = this;
      var changedEleData = [];
      if (!empty(parentFormId)) {
        $('#js_form_groups_add #js_custom_search_friend_placement input[name="admins[]"]').each(function () {
          changedEleData.push(trim($(this).val()));
        });

        if (parentObject.arr_diff(changedEleData, $Core.reloadValidation.initEleData[parentFormId][parentObject.eleName]).length) {
          if (!$Core.reloadValidation.changedEleData.hasOwnProperty(parentFormId)) {
            $Core.reloadValidation.changedEleData[parentFormId] = {};
          }

          $Core.reloadValidation.changedEleData[parentFormId][parentObject.eleName] = true;
        } else {
          delete $Core.reloadValidation.changedEleData[parentFormId][parentObject.eleName];
        }

        $Core.reloadValidation.preventReload();

        return true;
      }

      return false;
    },

    validateWhenRemove: function(ele)   {
      var parentObject = this;
      $Core.searchFriendsInput.removeSelected(ele, $(ele).next('input[type="hidden"]').val());
      parentObject._checkValidate(parentObject.parentFormId);
    },

    arr_diff: function (arr1, arr2) {
      var arr = [], diff = [];

      for (var i = 0; i < arr1.length; i++) {
        arr[arr1[i]] = true;
      }

      for (var i = 0; i < arr2.length; i++) {
        if (arr[arr2[i]]) {
          delete arr[arr2[i]];
        } else {
          arr[arr2[i]] = true;
        }
      }

      for (var k in arr) {
        diff.push(k);
      }

      return diff;
    }
  },
  actionModeration: function() {
    let moderationContainer = $('.moderation_placeholder');
    if (moderationContainer.length) {
      if ($('.moderation_row').length) {
        moderationContainer.removeClass('hide');
      } else {
        moderationContainer.addClass('hide');
      }
    }
  }
};

$(document).on('click', '[data-app="core_groups"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'click' && $Core.Groups.cmds.hasOwnProperty(action) &&
      typeof $Core.Groups.cmds[action] === 'function') {
    $Core.Groups.cmds[action]($(this), evt);
  }
});

$(document).on('change', '[data-app="core_groups"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'change' && $Core.Groups.cmds.hasOwnProperty(action) &&
      typeof $Core.Groups.cmds[action] === 'function') {
    $Core.Groups.cmds[action]($(this), evt);
  }
});

$(document).on('submit', '[data-app="core_groups"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'submit' && $Core.Groups.cmds.hasOwnProperty(action) &&
      typeof $Core.Groups.cmds[action] === 'function') {
    $Core.Groups.cmds[action]($(this), evt);
  }
});

$(document).on('keyup', '[data-app="core_groups"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'keyup' && $Core.Groups.cmds.hasOwnProperty(action) &&
      typeof $Core.Groups.cmds[action] === 'function') {
    $Core.Groups.cmds[action]($(this), evt);
  }
});

$(document).on('click', '#js_groups_add_change_photo', function() {
  $('#js_event_current_image').hide();
  $('#js_event_upload_image').fadeIn();
});

$Behavior.groupsInitElements = function() {
  $('[data-app="core_groups"][data-action-type="init"]').each(function() {
    var t = $(this);
    if (t.data('action-type') === 'init' &&
        $Core.Groups.cmds.hasOwnProperty(t.data('action')) &&
        typeof $Core.Groups.cmds[t.data('action')] === 'function') {
      $Core.Groups.cmds[t.data('action')](t);
    }
  });
};

$Behavior.contentHeight = function() {
  $('#content').height($('.main_timeline').height());
};

$Behavior.fixSizeTinymce = function() {
  //The magic code to add show/hide custom event triggers
  (function($) {
    $.each(['show', 'hide'], function(i, ev) {
      var el = $.fn[ev];
      $.fn[ev] = function() {
        this.trigger(ev);
        return el.apply(this, arguments);
      };
    });
  })(jQuery);

  $('#js_groups_block_info').on('show', function() {
    $('.mceIframeContainer.mceFirst.mceLast iframe').height('275px');
  });
};

PF.event.on('on_document_ready_end', function() {
  $Core.Groups.initGroupCategory();
  $Core.Groups.actionModeration();

  if ($('#js_form_groups_add').length) {
    $Core.Groups.blockInviteAdminReloadValidation.init();
    $Core.Groups.blockInviteReloadValidation.init();
  }

  $Core.Groups.profilePhoto.init();
});

PF.event.on('on_page_change_end', function() {
  $Core.Groups.initGroupCategory();
  $Core.Groups.actionModeration();

  if ($('#js_form_groups_add').length) {
    $Core.Groups.blockInviteAdminReloadValidation.init();
    $Core.Groups.blockInviteReloadValidation.init();
  }

  $Core.Groups.profilePhoto.init();
});

$Behavior.initEventsForCropme = function() {
  $(document).off('click', '#' + $Core.Groups.profilePhoto.holderId + ' .js_box_close a').on('click', '#' + $Core.Groups.profilePhoto.holderId + ' .js_box_close a', function () {
    if ($Core.Groups.profilePhoto.isModified()) {
      $Core.jsConfirm({
        'title': oTranslations['close'],
        'message': oTranslations['close_without_save_your_changes'],
        'btn_yes': oTranslations['close']
      }, function () {
        $Core.Groups.profilePhoto.closeHiddenBox();
      }, function () {
      });
    } else {
      $Core.Groups.profilePhoto.closeHiddenBox();
    }
  });
}