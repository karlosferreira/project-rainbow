$Behavior.editMobileMenuPage = function () {
// Colorpicker
    $('#core_mobile_add_menu_form ._colorpicker:not(.built)').each(function () {
        var t = $(this),
            h = t.parent().find('._colorpicker_holder');

        t.addClass('built');
        if (h.hasClass('is_span')) {
            h.css('color', t.val());
        } else {
            h.css('background-color', t.val());
        }

        h.colpick({
            layout: 'hex',
            submit: false,
            onChange: function (hsb, hex) {
                t.val('#' + hex);
                if (h.hasClass('is_span')) {
                    h.css('color', '#' + hex);
                } else {
                    h.css('background-color', '#' + hex);
                }
            },
            onHide: function () {
                t.trigger('change');
            }
        });

    });
};
$Ready(function () {
    var adConfigForm = $('#js_add_ad_config_form');
    if (adConfigForm.length && !adConfigForm.hasClass('built')) {
        adConfigForm.addClass('built dont-unbind-children');
        //Init extra config
        var typeInput = adConfigForm.find('[name="val[type]"]'),
            extraSection = $('#js_ad_config_extra_config'),
            cappingType = adConfigForm.find('#frequency_capping'),
            checkedType = adConfigForm.find('[name="val[type]"]:checked'),
            type = checkedType.length ? checkedType.val() : 'banner';
        if (type === 'banner') {
            extraSection.show();
        }
        typeInput.on('click', function () {
            if ($(this).val() === 'banner') {
                extraSection.show();
            } else {
                extraSection.hide();
            }
        });
        cappingType.on('change', function () {
            var cappingViews = $('#js_ad_config_capping_views'),
                cappingTime = $('#js_ad_config_capping_time');
            switch ($(this).val()) {
                case 'views':
                    cappingViews.show();
                    cappingTime.hide();
                    break;
                case 'time':
                    cappingViews.hide();
                    cappingTime.show();
                    break;
                default:
                    cappingViews.hide();
                    cappingTime.hide();
                    break;
            }
        });
        $('#js_ad_config_add_more_location').on('click', function () {
            var oLocation = $('.js_ad_location_priority:first'),
                oLastLocation = $('.js_ad_location_priority:last');
            if (!oLocation.length) {
                return false;
            }
            var oHtml = oLocation.clone().html();
            oLastLocation.after('<div class="form-inline js_ad_location_priority" style="padding-bottom: 16px;">' + oHtml +
                '<div class="form-group" style="padding: 16px;margin-top: 20px;"><i class="ico ico-minus-circle" style="color: red;font-size: 18px;" onclick="$(this).parents(\'.js_ad_location_priority\').remove();"></i></div>' +
                '</div>');
        });
    }
    var manageAdConfig = $('#js_mobile_manage_ad_config');
    if (manageAdConfig.length) {
        $('.js_manage_ad_enable_config').off('click').on('click',function(){
            var ele = $(this);
            if (ele.prop('confirmed') === true) {
                ele.prop('confirmed', false);
                return true;
            } else {
                $Core.ajax('mobile.checkExistedConfig', {
                    type: 'POST',
                    async: true,
                    params: {
                        id: ele.data('id')
                    },
                    success: function (data) {
                        var oData = JSON.parse(data);
                        if (typeof oData.error !== 'undefined') {
                            ele.prop('confirmed', false);
                        }
                        if (typeof oData.ids !== 'undefined' && oData.ids.length) {
                            $Core.jsConfirm({'message': oTranslations['mobile_enable_ad_config_warning']}, function () {
                                ele.prop('confirmed', true);
                                ele.trigger('click');
                            }, function () {
                                ele.prop('confirmed', false);
                            })
                        } else {
                            ele.prop('confirmed', true);
                            ele.trigger('click');
                        }
                    }
                });
                return false;
            }
        });
    }
});