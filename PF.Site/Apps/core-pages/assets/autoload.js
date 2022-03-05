var Core_Pages = {
  canCheckReloadValidate: true,
  cropmeImgSrc: '',
  reassignOwner: function(obj) {
    let _this = $(obj),
      pageId = _this.data('id'),
      confirmMessage = _this.data('message') ? _this.data('message') : null;

    $Core.jsConfirm({message: confirmMessage}, function () {
      $('#js_page_reassign_submit').addClass('disabled').attr('disabled', true);
      $('#js_page_reassign_loading').show();
      $.fn.ajaxCall('pages.reassignOwner', 'page_id=' + pageId + '&user_id='+ $('#js_reassign_owner_page #search_friend_single_input').val(), null, null, function() {
        $('#js_page_reassign_submit').removeClass('disabled').removeAttr('disabled');
        $('#js_page_reassign_loading').hide();
      });
    }, function () {
      $('#js_page_reassign_submit').removeClass('disabled').removeAttr('disabled');
      $('#js_page_reassign_loading').hide();
    });

    return false;
  },
  profilePhoto: {
    holderId: 'pages_photo_form',
    parentContainer: null,
    dropzoneId: null,
    currentRotation: 0,
    reset: function() {
      this.currentRotation = 0;
    },
    submit: function(obj) {
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

      _form.ajaxCall('pages.processCropme');
      _form.find('.js_submit_btn').prop('disabled', true);

      return false;
    },
    isModified: function() {
      return $Core.reloadValidation.changedEleData.hasOwnProperty('js_form_pages_crop_me')
        && $Core.reloadValidation.changedEleData['js_form_pages_crop_me'].hasOwnProperty('crop_me');
    },
    init: function() {
      let _this = this;
      if (typeof currentPageId !== "undefined" && $('._is_pages_view').length > 0 && $('#' + this.holderId).length === 0) {
        this.dropzoneId = 'pages-dropzone_' + currentPageId;
        tb_show('', $.ajaxBox('pages.cropme', $.param({width: '500', allow_upload: 1, id: currentPageId})), null, null, null, null, true, this.holderId);
      }
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
      Core_Pages.blockPhotoReloadValidation.reset();
    },
    update: function (hasPhoto) {
      if (hasPhoto === false) {
        $(this.parentContainer).removeClass('profile-image-error');
        $('.dropzone-button', '#' + this.dropzoneId).trigger('click');
      } else {
        this.showHiddenBox();
        Core_Pages.initCropMe();
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
        Core_Pages.crop({
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
          $('#js_upload_avatar_action').attr('onclick', 'return Core_Pages.profilePhoto.update(true);');
        }
      } else if (typeof response.run !== 'undefined') {
        eval(response.run);
      }
      Core_Pages.blockPhotoReloadValidation.changedEleData['upload_new'] = 1;
      Core_Pages.blockPhotoReloadValidation.validate();
      this.actionCropFormButtons(false);
      $('.rotate_button', $(this.parentContainer)).removeClass('hide');
      $('.cropit-image-zoom-input', $(this.parentContainer)).removeClass('hide');
    },
    onAddedFile: function () {
      $(this.parentContainer).addClass('profile-image-uploading').removeClass('profile-image-error');
      this.showHiddenBox();
      this.actionCropFormButtons(true);
      $(this.parentContainer).find('#js_upload_form_pages_wrapper').parent().removeClass('hide');
    },
    onError: function () {
      $(this.parentContainer).removeClass('profile-image-uploading').addClass('profile-image-error');
      this.actionCropFormButtons(false);
    },
  },
  crop: function(params) {
    if (typeof params === 'object' && params.hasOwnProperty('imagePath')) {
      var imagePath = params['imagePath'];
      $('.image-editor').cropit('destroy');
    } else {
      var imagePath = Core_Pages.cropmeImgSrc;
    }

    $('.image-editor').cropit({
      imageState: {
        src: imagePath,
      },
      smallImage: 'allow',
      maxZoom: 2,
      allowDragNDrop: false,
      onImageLoaded: function () {
        Core_Pages.blockPhotoReloadValidation.init($('.image-editor').cropit('zoom'), 0);
      },
      onZoomChange: function () {
        if (typeof $Core.reloadValidation.initEleData['js_form_pages_crop_me'] !== 'undefined') {
          Core_Pages.blockPhotoReloadValidation.changedEleData['zoom'] = $('.image-editor').cropit('zoom');
          Core_Pages.blockPhotoReloadValidation.validate();
        }
      }
    });
  },
  initCropMe: function() {
    if (this.cropmeImgSrc === '') {
      return;
    }
    this.crop();
    function checkRotateReload(is_minus) {
      if (is_minus) {
        Core_Pages.blockPhotoReloadValidation.changedEleData['rotation']++;
      } else {
        Core_Pages.blockPhotoReloadValidation.changedEleData['rotation']--;
      }

      if (Math.abs(Core_Pages.blockPhotoReloadValidation.changedEleData['rotation']) % 4 === 0) {
        Core_Pages.blockPhotoReloadValidation.changedEleData['rotation'] = 0;
      }

      Core_Pages.blockPhotoReloadValidation.validate();
    }

    $('.js-pages-rotate-cw').off('click').click(function() {
      $('.image-editor').cropit('rotateCW');
      Core_Pages.profilePhoto.currentRotation = (Core_Pages.profilePhoto.currentRotation + 90) % 360;
      checkRotateReload(false);
    });

    $('.js-pages-rotate-ccw').off('click').click(function() {
      $('.image-editor').cropit('rotateCCW');
      Core_Pages.profilePhoto.currentRotation = (Core_Pages.profilePhoto.currentRotation + 270) % 360;
      checkRotateReload(true)
    });

    $('.export').click(function() {
      var imageData = $('.image-editor').cropit('export');
      window.open(imageData);
    });
  },
  cmds: {
    add_new_page: function(ele, evt) {
      tb_show(oTranslations['add_new_page'],
          $.ajaxBox('pages.addPage', 'height=400&width=550&type_id=' +
              ele.data('type-id')));
      return false;
    },

    select_category: function(ele, evt) {
      $('[class^=select-category-]').hide();
      $('.select-category-' + ele.val()).show();
      $('#select_sub_category_id').val(0);
    },

    add_page_process: function(ele, evt) {
      evt.preventDefault();
      ele.ajaxCall('pages.add');
      // disable submit button
      var submit = $('input[type="submit"]', ele);
      submit.prop('disabled', true).addClass('submitted');
    },

    init_google_map: function(ele) {
      /* Load Google */
      if (($('body#page_pages_add').length === 0 &&
              $('body#page_pages_view').length === 0) ||
          typeof oParams['core.google_api_key'] === 'undefined') {
        return;
      }

      if (!$Core.PagesLocation.bGoogleReady &&
          typeof oParams['core.google_api_key'] !== 'undefined') {
        ele.hide();
        $Core.PagesLocation.loadGoogle(ele);
      }

      $Core.PagesLocation.sMapId = ele.attr('id');

      var initPagesLocation = setInterval(function() {
        if (typeof google !== 'undefined') {
          $Core.PagesLocation.init();
          clearInterval(initPagesLocation);
        }
      }, 500);

      $('a[rel^=js_pages_block_]').click(function() {
        if ($(this).attr('rel') == 'js_pages_block_location') {
          ele.show();
          google.maps.event.trigger($Core.PagesLocation.gMap, 'resize');
          if (typeof $Core.PagesLocation.gMap.panTo === 'function') {
            $Core.PagesLocation.gMap.panTo($Core.PagesLocation.gMyLatLng);
          }
          $($Core.PagesLocation).on('mapCreated', function() {
            if ($('#' + $Core.PagesLocation.sMapId).data('location-set') ==
                'false') {
              $Core.PagesLocation.gMap.setCenter($Core.PagesLocation.gMyLatLng);
            }
          });
        }
        else {
          ele.hide();
        }
      });
    },

    admin_delete_category_image: function(ele) {
      $Core.jsConfirm(
          {message: oTranslations['are_you_sure_you_want_to_delete_this_category_image']},
          function() {
            $.ajaxCall('pages.deleteCategoryImage',
                'type_id=' + ele.data('type-id'));
          }, function() {});
    },

    admin_edit_category_change: function(ele) {
      ele.val() != 0 ? $('#image_select').hide() : $('#image_select').show();
    },

    check_url: function(ele) {
      if ($('#js_vanity_url_new').val() != $('#js_vanity_url_old').val()) {
        $Core.processForm('#js_pages_vanity_url_button');
        $(ele.parents('form:first')).ajaxCall('pages.changeUrl');
      }
      return false;
    },

    init_drag: function(ele) {
      Core_drag.init({table: ele.data('table'), ajax: ele.data('ajax')});
    },

    search_member: function(ele, evt) {
      var parentBlock = $('.pages-block-members'),
          activeTab = $('li.active a', parentBlock),
          container = $(ele.data('container')),
          resultContainer = ele.val() ? ele.data('result-container') : ele.data('listing-container');

      setTimeout(function() {
        if($('.no-members-found').length) {
          $('.moderation_placeholder').addClass('hide');
        } else {
          $('.moderation_placeholder').removeClass('hide');
        }
      }, 100);

      container.addClass('hide');
      $.ajaxCall('pages.getMembers', 'tab=' + activeTab.data('tab') + '&container=' + resultContainer + '&page_id=' + ele.data('page-id') + '&search=' + ele.val());
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
      $.ajaxCall('pages.getMembers', 'tab=' + ele.data('tab') + '&container=' + ele.data('container') + '&page_id=' + ele.data('page-id'));
    },

    remove_admin: function(ele, evt) {
      $Core.jsConfirm({
        message: ele.data('message')
      }, function() {
        $.ajaxCall('pages.removeAdmin', 'page_id=' + ele.data('page-id') + '&user_id=' + ele.data('user-id'))
      }, function() {});
    },

    remove_member: function(ele, evt) {
      $Core.jsConfirm({
        message: ele.data('message')
      }, function() {
        $.ajaxCall('pages.removeMember', 'page_id=' + ele.data('page-id') + '&user_id=' + ele.data('user-id'))
      }, function() {});
    },

    disable_submit: function(form) {
      $('input[type="submit"]', form).prop('disabled', true).addClass('submitted');
    },

    like_page: function(ele, evt) {
      ele.fadeOut('fast', function() {
        ele.prev().fadeIn('fast');
      });
      $.ajaxCall('like.add', 'type_id=pages&pages_not_reload=true&item_id=' + ele.data('id')); return false;
    },

    toggleActivePageMenu: function (ele, evt) {
      if ($(ele).length) {
        $(ele).ajaxCall('pages.toggleActivePageMenu', $.param({
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

  searchingDone: function(searchingDone) {
    if (typeof searchingDone === 'boolean' && searchingDone) {
      $('.search-member-result').removeClass('hide');
      $('.pages-member-listing').empty();
      $('.btn-select-all.active').trigger('click');
    }
  },

  hideSearchResults: function() {
    $('.search-member-result').empty();
    $('.btn-select-all.active').trigger('click');
  },

  checkVal: function() {
    return $('#add_select').val() == '0';
  },
  readyAdd: function() {
    $('#add_select').change(function() {
      if (Core_Pages.checkVal()) {
        $('#is_group').hide();
      }
      else {
        $('#is_group').show();
      }
    });
  },

  resetSubmit: function() {
    $('input[type="submit"].submitted').each(function() {
      $(this).prop('disabled', false).removeClass('submitted');
    });
  },

  updateCounter: function(selector) {
    var ele = $(selector),
        counter = ele.html().substr(1, ele.html().length - 2);

    ele.html('('+ (parseInt(counter) - 1) +')');
  },
  processPageFeed: function(oObj){
      var oThis = $(oObj);
      var type = oThis.data('type');
      var opposite_type = (type == 'like' ? 'unlike' : 'like');
      var sAjaxCall = (type == 'like' ? 'like.add' : 'like.delete');
      var oActionContent = oThis.closest('.js_page_feed_action_content');
      $.ajaxCall(sAjaxCall, 'type_id=pages&item_id=' + oActionContent.data('page-id') + '&is_browse_like=1');
      oThis.parent().addClass('hide');
      oActionContent.find('[data-type="' + opposite_type + '"]').parent().removeClass('hide');
  },
  redirectToDetailPage: function(oObj)
  {
      var sUrl = $(oObj).data('url');
      if(sUrl)
      {
          window.location.href = sUrl;
      }
  },
  processJoinPage: function(obj,page_id,type)
  {
    var replacedContent = '';
    var oParent = $(obj).closest('.js_join_page_action');
    if(type == 'like')
    {
        $.ajaxCall('like.add', 'type_id=pages&item_id=' + page_id + '&reload=1');
        replacedContent = "<div class=\"dropdown\">" +
            "<a role=\"button\" class=\"btn btn-round btn-default btn-icon item-icon-liked pages_like_join pages_unlike_unjoin\" data-toggle=\"dropdown\">" +
            "<span class=\"ico ico-thumbup\"></span>" + oParent.data('text-liked') + "<span class=\"ml-1 ico ico-caret-down\"></span>" +
            "</a><ul class=\"dropdown-menu dropdown-menu-right\"><li>" +
            "<a role=\"button\" onclick=\"Core_Pages.processJoinPage(this," + page_id + ",'unlike');return false;\">" +
            "<span class=\"mr-1 ico ico-thumbdown\"></span>" + oParent.data('text-unlike') + "</a></li></ul></div>";
    }
    else if (type == 'unlike')
    {
        $.ajaxCall('like.delete', 'type_id=pages&item_id=' + page_id);
        replacedContent = "<button class=\"btn btn-round btn-primary btn-gradient btn-icon item-icon-like\" onclick=\"Core_Pages.processJoinPage(this," + page_id + ",'like');return false;\"><span class=\"ico ico-thumbup-o\"></span>" + oParent.data('text-like') + "</button>";
    }
    oParent.html(replacedContent);
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

  blockInviteReloadValidation: {
    init: function () {
      this.validate();
    },

    validate: function () {
      function _checkValidate(parentFormId) {
        if (!empty(parentFormId) && parseInt(trim($('#js_form_pages_add #deselect_all_friends').children().text())) > 0) {
          if (!$Core.reloadValidation.changedEleData.hasOwnProperty(parentFormId)) {
            $Core.reloadValidation.changedEleData[parentFormId] = {};
          }

          $Core.reloadValidation.changedEleData[parentFormId]['invite_friend'] = true;
        } else {
          delete $Core.reloadValidation.changedEleData[parentFormId]['invite_friend'];
        }

        $Core.reloadValidation.preventReload();
      }

      $(document).on('click', '#js_form_pages_add #js_friend_search_content input[type="checkbox"], #js_form_pages_add #deselect_all_friends, #js_form_pages_add #selected_friends_list li[data-id]', function () {
        _checkValidate('js_form_pages_add');
      })
    }
  },

  //check edit thumbnail
  blockPhotoReloadValidation: {
    parentFormId: 'js_form_pages_crop_me',
    changedEleData: [],
    init: function (zoom, rotation) {
      var parentObject = this;
      if (!$Core.reloadValidation.initEleData.hasOwnProperty(parentObject.parentFormId)) {
        $Core.reloadValidation.initEleData[parentObject.parentFormId] = {};
      }

      $Core.reloadValidation.initEleData[parentObject.parentFormId]['zoom'] = zoom;
      $Core.reloadValidation.initEleData[parentObject.parentFormId]['rotation'] = rotation;

      Core_Pages.blockPhotoReloadValidation.changedEleData['zoom'] = zoom;
      Core_Pages.blockPhotoReloadValidation.changedEleData['rotation'] = rotation;
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
    }
  },
  blockInviteAdminReloadValidation: {
    parentFormId: 'js_form_pages_add',
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

      $('#js_form_pages_add #js_custom_search_friend_placement input[name="admins[]"]').each(function () {
        var _this = $(this);
        $Core.reloadValidation.initEleData[parentObject.parentFormId][parentObject.eleName].push(trim(_this.val()));
      });
    },

    validate: function () {
      var parentObject = this;

      function reBindEventRemove() {
        $('#js_form_pages_add #js_custom_search_friend_placement .friend_search_remove').each(function () {
          $(this).attr('onclick', 'Core_Pages.blockInviteAdminReloadValidation.validateWhenRemove(this); return false;')
        });
      }

      $('#js_form_pages_add .js_temp_friend_search_form').on('click', function () {
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
        $('#js_form_pages_add #js_custom_search_friend_placement input[name="admins[]"]').each(function () {
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
};

PF.event.on('on_document_ready_end', function () {
  if ($('#js_form_pages_add').length) {
    Core_Pages.blockInviteAdminReloadValidation.init();
    Core_Pages.blockInviteReloadValidation.init();
  }
  Core_Pages.profilePhoto.init();
});

PF.event.on('on_page_change_end', function () {
  if ($('#js_form_pages_add').length) {
    Core_Pages.blockInviteAdminReloadValidation.init();
    Core_Pages.blockInviteReloadValidation.init();
  }
  Core_Pages.profilePhoto.init();
})

$(document).on('click', '[data-app="core_pages"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'click' && Core_Pages.cmds.hasOwnProperty(action) &&
      typeof Core_Pages.cmds[action] === 'function') {
    Core_Pages.cmds[action]($(this), evt);
  }
});

$(document).on('change', '[data-app="core_pages"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'change' && Core_Pages.cmds.hasOwnProperty(action) &&
      typeof Core_Pages.cmds[action] === 'function') {
    Core_Pages.cmds[action]($(this), evt);
  }
});

$(document).on('submit', '[data-app="core_pages"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'submit' && Core_Pages.cmds.hasOwnProperty(action) &&
      typeof Core_Pages.cmds[action] === 'function') {
    Core_Pages.cmds[action]($(this), evt);
  }
});

$(document).on('keyup', '[data-app="core_pages"]', function(evt) {
  var action = $(this).data('action'),
      type = $(this).data('action-type');
  if (type === 'keyup' && Core_Pages.cmds.hasOwnProperty(action) &&
      typeof Core_Pages.cmds[action] === 'function') {
    Core_Pages.cmds[action]($(this), evt);
  }
});

$Behavior.pagesInitElements = function() {
  $('[data-app="core_pages"]').each(function() {
    var t = $(this);
    if (t.data('action-type') === 'init' &&
        Core_Pages.cmds.hasOwnProperty(t.data('action')) &&
        typeof Core_Pages.cmds[t.data('action')] === 'function') {
      Core_Pages.cmds[t.data('action')](t);
    }
  });
};

$Core.Pages = {
  setAsCover: function(iPageId, iPhotoId) {
    $.ajaxCall('pages.setCoverPhoto', 'page_id=' + iPageId + '&photo_id=' +
        iPhotoId);
  },

  removeCover: function(iPageId) {
    $Core.jsConfirm({}, function() {
      $.ajaxCall('pages.removeCoverPhoto', 'page_id=' + iPageId);
    }, function() {
    });
  },
};

$Behavior.pagesBuilder = function() {
  // Creating/Editing pages
  if ($Core.exists('#js_pages_add_holder')) {
    $('.pages_add_category select').change(function() {
      $(this).parent().parent().find('.js_pages_add_sub_category').hide();
      $(this).
          parent().
          parent().
          find('#js_pages_add_sub_category_' + $(this).val()).
          show();
      $('#js_category_pages_add_holder').
          val($(this).
              parent().
              parent().
              find('#js_pages_add_sub_category_' + $(this).val() +
                  ' option:first').
              val());
    });

    $('.js_pages_add_sub_category select').change(function() {
      $('#js_category_pages_add_holder').val($(this).val());
    });
  }
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

  $('#js_pages_block_info').on('show', function() {
    $('.mceIframeContainer.mceFirst.mceLast iframe').height('275px');
  });
};

/* Implements Google Places into Pages */
$Core.PagesLocation = {
  bGoogleReady: false,
  /* Here we store the places gotten from Google and Pages. This array is reset as the user moves away from the found place */

  aPlaces: [],

  /* The id of the div that will display the map of the current location */
  sMapId: '',

  /* Google requires the key to be passed so we store it here*/
  sGoogleKey: '',

  /* Google's Geocoder object */
  gGeoCoder: undefined,

  /* Google's marker in the map */
  gMarker: undefined,

  /* If the browser does not support Navigator we can get the latitude and longitude using the IPInfoDBKey */
  sIPInfoDbKey: '',

  /* Google object holding my location*/
  gMyLatLng: undefined,

  /* This is the google map object, we can control the map from this variable */
  gMap: {},

  /* This function is triggered by the callback from loading the google api*/
  loadGoogle: function() {
    if ($Core.PagesLocation.bGoogleReady) {
      return false;
    }
    if (typeof google !== 'undefined') {
      $Core.PagesLocation.bGoogleReady = true;
      return false;
    }
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://maps.google.com/maps/api/js?libraries=places&key=' +
        oParams['core.google_api_key'] +
        '&callback=$Core.PagesLocation.init';
    document.body.appendChild(script);
    $Core.PagesLocation.bGoogleReady = true;
  },

  init: function() {
    var map = $('#' + $Core.PagesLocation.sMapId);
    if (typeof map === 'undefined') {
      return;
    }

    $($Core.PagesLocation).on('gotVisitorLocation', function() {
      $Core.PagesLocation.updateLatLngField($Core.PagesLocation.gMyLatLng.lat(),
          $Core.PagesLocation.gMyLatLng.lng());
      $Core.PagesLocation.createMap();
      $Core.PagesLocation.createSearch();
    });

    if (typeof map.data('lat') !== 'undefined') {
      $Core.PagesLocation.gMyLatLng = new google.maps.LatLng(
          map.data('lat'), map.data('lng'));
      // update location name
      $('#txt_location_name').val(map.data('lname'));
      $($Core.PagesLocation).trigger('gotVisitorLocation');
    }
    else {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
              $Core.PagesLocation.gMyLatLng = new google.maps.LatLng(
                  position.coords.latitude, position.coords.longitude);
              $($Core.PagesLocation).trigger('gotVisitorLocation');
            },
            function() {
              $Core.PagesLocation.getLocationWithoutHtml5();
            });
      }
      else {
        $Core.PagesLocation.getLocationWithoutHtml5();
      }
    }

    $('#js_add_location_suggestions').
        css({'max-height': '150px', 'overflow-y': 'auto'});
    $($Core.PagesLocation).trigger('mapCreated');
  },

  updateLatLngField: function(lat, lng) {
    if ($('#txt_location_latlng').val() == '') {
      $('#txt_location_latlng').val(lat + ',' + lng);
    }
  },

  /* Ready the input for the search */
  createSearch: function() {
    if ($.isEmptyObject($Core.PagesLocation.gMap)) {
      return;
    }
    $Core.PagesLocation.gSearch = new google.maps.places.PlacesService(
        $Core.PagesLocation.gMap);

    /* Prepare the input field so the user can type in locations */
    $('#txt_location_name').on('keyup', function() {
      var sName = $(this).val();
      if (sName.length < 3 || sName == $Core.PagesLocation.sLastName) {
        $('#js_add_location_suggestions').hide();
        return;
      }
      $Core.PagesLocation.sLastName = sName;
      $Core.PagesLocation.gSearch.nearbySearch
      (
          {
            location: $Core.PagesLocation.gMyLatLng,
            radius: 6000,
            keyword: sName,
          },
          function(results, status) {
            if (status == google.maps.places.PlacesServiceStatus.OK) {
              $Core.PagesLocation.aPlaces = results;
              $Core.PagesLocation.displaySuggestions();
            }
          }
      );
    });
  },

  createMap: function() {
    var oMapOptions = {
          zoom: 13,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          center: $Core.PagesLocation.gMyLatLng,
        },
        map = $('#' + $Core.PagesLocation.sMapId);

    $Core.PagesLocation.gMap = new google.maps.Map(
        document.getElementById($Core.PagesLocation.sMapId), oMapOptions);
    $Core.PagesLocation.gSearch = new google.maps.places.PlacesService(
        $Core.PagesLocation.gMap);

    var height;
    if ($('#main').hasClass('empty-right') || $('#main').hasClass('empty-left')) {
      height = '300px';
    } else {
      height = '250px';
    }
    map.css({height: height, width: '100%', display: 'block'});

    google.maps.event.trigger($Core.PagesLocation.gMap, 'resize');

    /* Build the marker */
    $Core.PagesLocation.gMarker = new google.maps.Marker({
      map: $Core.PagesLocation.gMap,
      position: $Core.PagesLocation.gMyLatLng,
      draggable: true,
      animation: google.maps.Animation.DROP,
    });
    $Core.PagesLocation.gMap.panTo($Core.PagesLocation.gMyLatLng);

    /* Now attach an event for the marker */
    google.maps.event.addListener($Core.PagesLocation.gMarker, 'mouseup',
        function() {
          /* Refresh gMyLatLng*/
          $Core.PagesLocation.gMyLatLng = new google.maps.LatLng(
              $Core.PagesLocation.gMarker.getPosition().lat(),
              $Core.PagesLocation.gMarker.getPosition().lng());

          /* Refresh the hidden input */
          $('#txt_location_latlng').
              val($Core.PagesLocation.gMyLatLng.lat() + ',' +
                  $Core.PagesLocation.gMyLatLng.lng());

          /* Center the map */
          $Core.PagesLocation.gMap.panTo($Core.PagesLocation.gMyLatLng);

          /* Get the establishments near the new location */
          $Core.PagesLocation.getEstablishments(
              $Core.PagesLocation.displaySuggestions);
        });
    $($Core.PagesLocation).trigger('mapCreated');
    $($Core.PagesLocation.gMarker).trigger('mouseup');
  },

  getEstablishments: function(oObj) {
    $Core.PagesLocation.gSearch.nearbySearch({
      location: $Core.PagesLocation.gMyLatLng,
      radius: '500',
    }, function(aResults, iStatus) {
      if (iStatus == google.maps.places.PlacesServiceStatus.OK) {
        $Core.PagesLocation.aPlaces = aResults;
        if (typeof oObj == 'function') {
          oObj();
        }
        $($Core.PagesLocation).trigger('gotEstablishments');
      }
    });

  },

  displaySuggestions: function() {
    var sOut = '';
    $Core.PagesLocation.aPlaces.map(function(oPlace) {
      sOut += '<div class="js_div_place" onmouseover="$Core.PagesLocation.hintPlace(\'' +
          oPlace['id'] +
          '\');" onclick="$Core.PagesLocation.chooseLocation(\'' +
          oPlace['id'] + '\');">';
      sOut += '<div class="js_div_place_name">' + oPlace['name'] + '</div>';
      if (typeof oPlace['vicinity'] != 'undefined') {
        sOut += '<div class="js_div_place_vicinity">, ' +
            oPlace['vicinity'] + '</div>';
      }
      sOut += '</div>';
    });

    $('#js_add_location_suggestions').
        html(sOut).
        css({'z-index': 900, 'max-height': '150px'}).
        show();
  },

  hintPlace: function(sId) {
    $Core.PagesLocation.aPlaces.map(function(oPlace) {
      if (oPlace.id == sId) {
        $Core.PagesLocation.gMap.panTo(oPlace['geometry']['location']);
        $Core.PagesLocation.gMarker.setPosition(
            oPlace['geometry']['location']);
      }
    });
  },

  chooseLocation: function(sId) {
    $Core.PagesLocation.aPlaces.map(function(oPlace) {
      if (oPlace.id == sId) {
        $('#txt_location_name').val(oPlace.name);
        $('#txt_location_latlng').
            val(oPlace.geometry.location.lat() + ',' +
                oPlace.geometry.location.lng());
        $('#js_add_location_suggestions').hide();
      }
    });
  },

  getLocationWithoutHtml5: function() {
    /* Get visitor's city  */
    var sCookieLocation = getCookie('core_places_location');
    if (sCookieLocation != null) {
      var aLocation = sCookieLocation.split(',');
      $Core.PagesLocation.gMyLatLng = new google.maps.LatLng(aLocation[0],
          aLocation[1]);
      $($Core.PagesLocation).trigger('gotVisitorLocation');
    }
    else {
      $.ajaxCall('pages.getMyCity');
    }
  }
};

$Core.Pages.Claim = {
  approve: function(iClaimId) {
    $Core.jsConfirm(
        {message: oTranslations['are_you_sure_you_want_to_transfer_ownership']},
        function() {
          $('#global_ajax_message').html('<i class="fa fa-spin fa-circle-o-notch"></i>').show();
          $.ajaxCall('pages.approveClaim', 'claim_id=' + iClaimId);
        }, function() {
        });
    if ($('.pages-admincp-claim').length) {
      if ($('#js-confirm-popup-wrapper').length) {
        $('.js_box_buttonpane').find('button.btn-primary').addClass('pull-right');
        $('.js_box_buttonpane').find('button.btn-default').addClass('pull-left');
      }
    }
  },

  deny: function(iClaimId) {
    $Core.jsConfirm(
        {message: oTranslations['are_you_sure_you_want_to_deny_this_claim_request']},
        function() {
          $.ajaxCall('pages.denyClaim', 'claim_id=' + iClaimId);
        }, function() {
        });
    if ($('.pages-admincp-claim').length) {
      if ($('#js-confirm-popup-wrapper').length) {
        $('.js_box_buttonpane').find('button.btn-primary').addClass('pull-right');
        $('.js_box_buttonpane').find('button.btn-default').addClass('pull-left');
      }
    }
  },
};

$(document).ready(function() {
  if (Core_Pages.checkVal()) {
    $('#is_group').hide();
  }
  Core_Pages.readyAdd();
});

$Behavior.crop_pages_image_photo = function() {
  Core_Pages.initCropMe();
  $(document).off('click', '#' + Core_Pages.profilePhoto.holderId + ' .js_box_close a').on('click', '#' + Core_Pages.profilePhoto.holderId + ' .js_box_close a', function () {
    if (Core_Pages.profilePhoto.isModified()) {
      $Core.jsConfirm({
        'title': oTranslations['close'],
        'message': oTranslations['close_without_save_your_changes'],
        'btn_yes': oTranslations['close']
      }, function () {
        Core_Pages.profilePhoto.closeHiddenBox();
      }, function () {
      });
    } else {
      Core_Pages.profilePhoto.closeHiddenBox();
    }
  });
};