$Core.Ads = {
  oPlan: {},
  isCPM: false,
  iCost: 0,

  recalculate: function() {
    var total = $('#total_cost'), placement = $('option:selected', '#better_ads_location');
    if(typeof placement !== 'undefined' && placement.data('is-cpm') == 0) {
        total.val(($('#total_view').val() * total.data('cost')).toFixed(2));
    }
    else {
        total.val((($('#total_view').val() / 1000) * total.data('cost')).toFixed(2));
    }
  },

  loadImage: function(input, id) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();

      reader.onload = function(e) {
        $('#' + id).removeClass('hide').attr('src', e.target.result);
      };

      reader.readAsDataURL(input.files[0]);
    }
  },

  setPlan: function(
    location_id, block_id, default_cost, width, height, is_cpm) {
    $('#location').val(location_id);
    $('#js_block_id').val(block_id);
    $Core.Ads.oPlan.default_cost = default_cost;
    $Core.Ads.blockPlacementCallback(width, height, location_id, is_cpm);

    if (is_cpm == 1) {
      $('#js_ad_info_cost').
        html(oTranslations['amount_currency_per_1000_impressions'].replace(
          '{amount}', default_cost).
          replace('{currency}', oCore['core.default_currency']));
    }
    else {
      $('#js_ad_info_cost').
        html(oTranslations['amount_currency_per_click'].replace('{amount}',
          default_cost).
          replace('{currency}', oCore['core.default_currency']));
    }

    tb_remove();
  },

  blockPlacementCallback: function(iWidth, iHeight, sBlock, isCPM) {
    $('.js_ad_holder').hide();

    $('#js_ad_position_selected').
      find('span').
      html('Block ' + sBlock + ' (' + iWidth + 'x' + iHeight + ')');
    $('#js_ad_position_selected').show();
    $('#js_ad_position_select').hide();

    sGlobalAdHolder = (iWidth > iHeight
      ? 'js_sample_ad_form_728_90'
      : 'js_sample_ad_form_160_600');
    sId = '#' + sGlobalAdHolder;

    $(sId).show();
    $(sId).css('width', iWidth + 'px');
    $(sId).css('height', iHeight + 'px');

    if ($('#type_id').val() == '2') {
      var oLocation = $('#js_image_holder').offset();

      $('#js_upload_image_holder').css('left', oLocation.left + 'px');
      $('#js_upload_image_holder').css('top', oLocation.top + 'px');
      $('#js_upload_image_holder').show();
    }

    if (typeof isCPM != 'undefined') {
      $Core.Ads.isCPM = isCPM;
      if ($Core.Ads.isCPM == false && $('#total_view').val() == 1000) {
        $('#total_view').val(500);
      }
    }
    else {
      $Core.Ads.isCPM = false;
    }
    $('#js_is_cpm').val($Core.Ads.isCPM);
  },

  /* This function is triggered when the selector for countries changes, so we can display a list of provinces/states*/
  handleStates: function() {
    var aChosenCountry = $('#country_iso_custom').val();
    $('.tbl_province, .select_child_country').hide();

    for (var i in aChosenCountry) {
      if ($('#sct_country_' + aChosenCountry[i]).length > 0 &&
        $('#sct_country_' + aChosenCountry[i] + ' option').length > 0) {
        $('.tbl_province, #country_' + aChosenCountry[i]).show();
      }
    }
  },

  toggleSelectedCountries: function(jCountries) {
    $('#country_iso_custom > option').each(function() {
      if (this.value.length > 0 && (jCountries.indexOf(this.value) > (-1)) ||
        (jCountries == this.value)) {
        $(this).attr('selected', 'selected');
        $('.tbl_province, #country_' + jCountries).show();
      }
    });
  },

  toggleSelectedProvinces: function(oProvinces) {
    $('.sct_child_country > option').each(function() {
      if (isset(oProvinces[$(this).val()])) {
        $(this).attr('selected', 'selected');
      }
    });
  },

  roundNumber: function(iNum) {
    return Math.round(iNum * 100) / 100;
  },

  confirmSubmitForm: function(button, formSelector) {
    var jForm = $(formSelector),
      action = $(button).data('action') != 'undefined'
        ? $(button).data('action')
        : '',
      message = '';

    if (action == 'deny') {
      message = oTranslations['are_you_sure_you_want_to_deny_selected_ads'];
    }
    else if (action == 'delete') {
      message = oTranslations['are_you_sure_you_want_to_delete_selected_ads_permanently'];
    }
    else if (action == 'approve') {
      message = oTranslations['are_you_sure_you_want_to_approve_selected_ads'];
    }

    if (typeof $(button).data('message') !== 'undefined') {
      message = $(button).data('message');
    }

    if (message) {
      $Core.jsConfirm({
        message: message,
      }, function() {
        if (action != '') {
          $('<input />').
            attr('type', 'hidden').
            attr('name', 'val[' + action + ']').
            attr('value', action).
            appendTo(jForm);
        }
        jForm.submit();
      }, function() {});
    }
    else {
      jForm.submit();
    }
  },

  validateStep1AddAdForm: function(form) {
    var tempFileInput = $('#js_upload_form_file_ad');

    if ($('#url_link', form).val() == '' || tempFileInput.length === 0 ||
      tempFileInput.val() == ''
      || ($('#type_html').prop('checked') &&
        ($('#title', form).val() == '' || $('#body', form).val() == ''))
    ) {
      return false;
    }

    return true;
  },

  disableEndOption: function(bDisable) {
    var endOptionHolder = $('#end_option').closest('.custom-checkbox-wrapper');

    $('input, select', endOptionHolder.find('.select_date')).
      attr('disabled', bDisable);
  },

  alertThenReload: function(message) {
    $Core.jsConfirm({
      message: message + '.',
      no_yes: true,
      btn_no: 'OK',
    }, function() {}, function() {
      window.location.reload();
    });

    setTimeout(function() {
      window.location.reload();
    }, 2e3);
  },

  processImport: function(form) {
    form = $(form);
    $.ajaxCall('ad.processImportAd', form.serialize());

    return false;
  },

  massImport: function(formId) {
    var form = $(formId);
    tb_show('',
      $.ajaxBox('ad.migrateAd', 'height=400&width=600&' + form.serialize()));

    return false;
  },

  hideAd: function(adId) {
    var ad = $('#ads_item_' + adId);
    // check if not have any ad => remove ad block
    if (ad.parent().children().length === 1) {
      ad.closest('.block').remove();
      ad.closest('.js_ad_space_parent').remove();
    }
    else {
      ad.slideUp('fast', function() {
        ad.remove();
      });
    }
  },

  recommendDimension: function() {
    if (typeof betteradsRecommendSizes !== 'undefined' && ($Core.exists('body#page_ad_add') || $Core.exists('body.ad-admincp-add'))) {
      var blockId = $('#better_ads_location option:selected').data('block-id'),
        typeId = $('.type_id:checked').val();
      if($('#recommended-demension').length) {
          $('#recommended-demension').text(betteradsRecommendSizes[blockId][typeId]);
      }
    }
  },

  resizePreview: function() {
    var iframe = $('iframe', '.js_box_fullmode');
    iframe.height($(window).height());
  }
};

//manage.js
var sGlobalAdHolder = 'js_sample_ad_form_728_90';

$Behavior.creatingAnAds = function() {
  if (!$('#page_ad_add').length &&
    !$('.ad-admincp-add').length &&
    !$('#page_ad_sponsor').length) {
    return;
  }

  $('#js_upload_image_holder').hide();

  if ($('.tbl_province').length > 0) {
    $('#country_iso_custom').change($Core.Ads.handleStates);
  }

  $('#js_is_user_group').change(function() {
    if (this.value == 1) {
      $('#js_user_group').hide();
    }
    else if (this.value == 2) {
      $('#js_user_group').show();
    }
  });

  if ($('#type_id').val() == 1) {
  }
  else if ($('#type_id').val() == 2) {
    $('#js_type_html').show();
  }

  if ($('#js_is_user_group').val() == 2) {
    $('#js_user_group').show();
  }

  $('#country_iso_custom').change($Core.Ads.handleStates);

  if ($Core.exists($Core.Ads) == false) {
    $Core.Ads = {

      handleStates: function() {
        var aChosenCountry = $('#country_iso_custom').val();
        $('.tbl_province, .select_child_country').hide();

        for (var i in aChosenCountry) {
          if ($('#sct_country_' + aChosenCountry[i]).length > 0 &&
            $('#sct_country_' + aChosenCountry[i] + ' option').length > 0) {
            $('.tbl_province, #country_' + aChosenCountry[i]).show();
          }
        }
      },
    };
  }

  if ($('#end_option').attr('checked') === undefined ||
    !$('#end_option').attr('checked')) {
    $Core.Ads.disableEndOption(true);
  }

  if($Core.exists('body#page_ad_add')) {
    var placement = $('option:selected', '#better_ads_location'), placementTypePhrase = 'better_ads_impressions';
    $('#total_cost').val(placement.data('cost')).data('cost', placement.data('cost'));
    if (placement.data('is-cpm') == 0) {
      placementTypePhrase = 'better_ads_clicks';
      $('#total_view').attr('min', 1);
      $('#js_is_cpm').val(0);
    } else {
      $('#total_view').attr('min', 1000);
      $('#js_is_cpm').val(1);
    }
    $('#js_ads_cpm').text(getPhrase(placementTypePhrase));
    $Core.Ads.recalculate();
  }
};

$(document).
  on('change', '.ad-admincp-add .type_id, #page_ad_add .type_id',
    function() {
      if (this.value == 1) {
        $('[data-type="image"]').removeClass('hide');
        $('[data-type="html"]').addClass('hide');
      }
      else if (this.value == 2) {
        $('#js_type_html').show();
        $('[data-type="html"]').removeClass('hide');
        $('[data-type="image"]').addClass('hide');
      }
    }).
  on('keyup', '[data-character-limit]', function() {
    var th = $(this),
      limit = th.data('character-limit'),
      holder = th.closest('div.form-group'),
      countSpan = $('.character-count', holder);

    countSpan.text(limit - th.val().length);
  }).
  on('click', '#betterads-add-continue', function() {
    if ($Core.Ads.validateStep1AddAdForm($(this).closest('form'))) {
      $(this).closest('.form-group').remove();
      $('#ad_details').removeClass('hide');
      var placement = $('option:selected', '#better_ads_location');
      $('#total_cost').
        val(placement.data('cost')).
        data('cost', placement.data('cost'));

      if (placement.data('is-cpm') == 0) {
          $('#total_view').val(100).attr('min', 1);
          $('#js_is_cpm').val(0);
      }
      else {
          $('#total_view').val(1000).attr('min', 1000);
          $('#js_is_cpm').val(1);
      }
      $Core.Ads.recalculate();
    }
    else {
      $Core.jsConfirm({
        no_yes: true,
        message: getPhrase('please_input_all_required_fields'),
        title: getPhrase('alert'),
        btn_no: 'OK',
      }, function() {}, function() {});
    }

    return false;
  }).
  on('click', '.betterads-preview', function() {
    var form = $(this).closest('form');

    tb_show('', $.ajaxBox('ad.preview', $.param(form.serializeArray())) +'&fullmode=true');

    return false;
  }).
  on('click', '.js_betterads_preview_exist_ad', function() {
    tb_show('', $.ajaxBox('ad.preview', 'ad_id=' + $(this).data('ad-id') + '&location=' + $(this).data('location')) +'&fullmode=true');

    return false;
  }).
  on('change', '#view_unlimited', function() {
    if (!this.checked) {
      $('#total_view').attr('disabled', true).addClass('disabled');
    }
    else {
      $('#total_view').attr('disabled', false).removeClass('disabled').focus();
    }
  }).
  on('change', '#click_unlimited', function() {
    if (!this.checked) {
      $('#total_click').attr('disabled', true).addClass('disabled');
    }
    else {
      $('#total_click').attr('disabled', false).removeClass('disabled').focus();
    }
  }).
  on('change', '#end_option', function() {
    $Core.Ads.disableEndOption(!this.checked);
  }).
  on('change', '#better_ads_location', function() {
    var placement = $('option:selected', '#better_ads_location');
    if($Core.exists('body#page_ad_add'))
    {
        var placementTypePhrase = 'better_ads_impressions';
        $('#total_cost').
        val(placement.data('cost')).
        data('cost', placement.data('cost'));
        if (placement.data('is-cpm') == 0) {
            placementTypePhrase = 'better_ads_clicks';
            $('#total_view').val(100).attr('min', 1);
            $('#js_is_cpm').val(0);
        }
        else {
            $('#total_view').val(1000).attr('min', 1000);
            $('#js_is_cpm').val(1);
        }

        $('#js_ads_cpm').text(getPhrase(placementTypePhrase));
        $Core.Ads.recalculate();
    }
    else if($Core.exists('body.ad-admincp-add'))
    {
      var oClick = $('#js_total_click');
      var oView = $('#js_total_view');
      if(placement.data('cpm') == 0)
      {
          oView.addClass('hide');
          oView.find('input[type="checkbox"]#view_unlimited').prop('checked', false);
          oView.find('input[type="text"]#total_view').val('').prop('disabled', true);
          oClick.removeClass('hide');
      }
      else
      {
          oClick.addClass('hide');
          oClick.find('input[type="checkbox"]#view_unlimited').prop('checked', false);
          oClick.find('input[type="text"]#total_view').val('').prop('disabled', true);
          oView.removeClass('hide');
      }
    }

    // recommended dimension
    $Core.Ads.recommendDimension();
  }).
  on('change', '#total_view', function() {
    $Core.Ads.recalculate();
  }).
  on('keyup', '#total_view', function() {
    $Core.Ads.recalculate();
  }).
  on('click', '#set_total_view', function() {
    $('#total_view').prop('readonly', !$(this).is(':checked'));
  }).
  on('change', '#filter-apps', function() {
    if ($(this).val()) {
      window.location.href = $(this).val();
    }
  });

PF.cmd('cancel_invoice', function(btn, evt) {
  // ajax
  $.ajaxCall('ad.cancelInvoice', 'id=' + btn.data('invoice-id'));
  // process after ajax
  $('.betterads-invoice-status', btn.closest('tr')).
    text(getPhrase('cancelled'));
  btn.closest('td').html(getPhrase('n_a'));
});

if ($Core.exists('#page_ad_sponsor') || $Core.exists('.ad-admincp-add')) {
  var endDate = $('#end_option');

  if (endDate.is(':checked')) {
    $('input:not(#end_option), select', endDate.closest('.custom-checkbox-wrapper')).
      prop('disabled', false);
  }
  else {
    $('input:not(#end_option), select', endDate.closest('.custom-checkbox-wrapper')).
      prop('disabled', true);
  }
}

// ADMINCP: manage sponsor settings page
if ($Core.exists('.ad-admincp-sponsor-setting')) {
  $(document).on('change', '#choose-user-group-form select', function() {
    $('#choose-user-group-form').submit();
  });

  // search
  var options = {
    keys: ['name'],
    includeScore: false,
    sort: true,
    threshold: 0.2,
    location: 0,
    distance: 100,
    minMatchCharLength: 2,
    maxPatternLength: 100,
  };
  $Core.Ads.fuse = new Fuse(betterAdsApps, options);

  $(document).on('keyup', '#search-app', function() {
    var search = $(this).val();

    if (!search) {
      $('#app-listing').show();
      $('#app-search').hide();
      return;
    }
    var results = $Core.Ads.fuse.search(search);
    if (results.length == 0) {
      var searchHtml = '';
    }
    else {
      var searchHtml = '<ul>';
      for (var i = 0; i < results.length; i++) {
        var result = results[i];
        searchHtml += '<li><a href="' + result.link + '">' + result.name +
          '</a></li>';
      }
      searchHtml += '</ul>';
    }
    $('#app-listing').hide();
    $('#app-search').html(searchHtml).show();
  });
}

if ($Core.exists('#page_ad_report')) {
  $Behavior.applyTooltips = function() {
    $('[data-toggle="tooltip"]').each(function() {
      $(this).tooltip({
          html: true,
          title: $(this).next('.tooltip-html').html(),
        }
      );
    });
  };
}

if ($Core.exists('#page_ad_preview')) {
  $('<div/>', {
    class: 'betterads-preview-overlay'
  }).prependTo('body');

  // remove fixed header
  setTimeout(function() {
    $('body#page_ad_preview').attr('data-header', '');
  }, 10);

  // move block to fixed
  var block = $('#js_block_border_apps_core_betterads_block_display');
  if (block.length === 0) {
    block = $('.js_ad_space_parent');
  }

  if (block.length > 0) {
    var blockLocation = block.parent().data('location');

    if (blockLocation == 5) {
      $Behavior.resetContentStage = function() {
        $('#content-stage').css('min-height', 'auto');
      };
    } else if (blockLocation == 2 || blockLocation == 4) {
      $('.betterads-preview-overlay').css('z-index', 1);
    }else if( blockLocation == 11){
      $('#page_ad_preview ._block.location_11 ').css('z-index', 'auto');
    }

    $Behavior.previewAd = function() {
      // fix bootstrap case
      var middle = $('.layout-middle'),
        right = $('.layout-right'),
        contentStage = $('#content-stage');
      if (middle.offset().left == right.offset().left) {
        if (blockLocation == 3 || blockLocation == 10) {
          right.css('min-height', block.height());
          contentStage.css('min-height', parseFloat(contentStage.css('min-height')) - block.height() - 100);
        } else if (blockLocation == 8) {
          $('.location_8').css('min-height', block.height());
          contentStage.css('min-height', parseFloat(contentStage.css('min-height')) - block.height());
        }
      }

      var top = block.offset().top,
        left = block.offset().left,
        width = block.width(),
        paddingTop = block.css('padding-top'),
        paddingRight = block.css('padding-right'),
        paddingBottom = block.css('padding-bottom'),
        paddingLeft = block.css('padding-left');

      block.css({
        position: 'fixed',
        top: top,
        left: left,
        width: width + parseFloat(paddingLeft.replace('px', '')) + parseFloat(paddingRight.replace('px', '')),
        'z-index': 99999,
        'padding-top': paddingTop,
        'padding-right': paddingRight,
        'padding-bottom': paddingBottom,
        'padding-left': paddingLeft,
      });
    }
  }
}

$Behavior.applyRecommendDimension = function() {
  $Core.Ads.recommendDimension();
};

$Behavior.removeImageTooltipText = function() {
    if($('.ad-admincp-add').length) {
      if ($('input[type=hidden]#type_id').val()==2){
          $('[data-type="image"]').addClass('hide');
      }
    }

    if($Core.exists('body.ad-admincp-add'))
    {
        var placement = $('option:selected', '#better_ads_location');
        if(placement.data('cpm') == 0)
        {
          $('#js_total_view').addClass('hide');
        }
        else
        {
          $('#js_total_click').addClass('hide');
        }
    }
};