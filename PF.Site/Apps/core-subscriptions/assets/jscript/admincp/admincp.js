
$Behavior.core_subscriptions_admincp = function () {
    $('#is-free').click(function () {
        if($(this).prop('checked'))
        {
            $('.currency input:text').val('0');
        }
    });
    $('#user_group_id').on('change', function () {
        var id = $(this).val();
        $('.visible-group').prop('disabled', false);
        $('#group-' + id).prop({
            checked : false,
            disabled: true
        });

    });
    $('#select-color').on('change', function () {
        $('#background_color').val($(this).val());
    });

    $('.is_recurring').click(function () {
        $('.js_recurring_body').hide();
        $('.recurring-cost input:text').val(0);
        $('#recurring_period').val(1);
    });
    $('.is_not_recurring').click(function () {
        $('.js_recurring_body').show();
    });

    if(typeof bDisableField !== "undefined" && parseInt(bDisableField) == 1)
    {
        $('.recurring-cost input:text').prop('disabled',true);
        setTimeout(function() {
            $('.is_recurring').off('click').on('click',function () {
                return false;
            });
            $('.is_not_recurring').off('click').on('click',function () {
                return false;
            });
        }, 1);
    }
}

$Behavior.addPackagePage = function () {
    $('.js_background_color ._colorpicker:not(.built)').each(function () {
        var t = $(this),
            h = t.parent().find('._colorpicker_holder');

        t.addClass('built');
        h.css('background-color', '#' + t.val());

        h.colpick({
            layout: 'hex',
            submit: false,
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                t.val(hex);
                h.css('background-color', '#' + hex);
            },
            onHide: function () {
                t.trigger('change');
            }
        });

    });
    if($('.subscribe-admincp-add').length) {
        $('.toolbar-top .btn-group').find('a.popup').hide();
    }
};

$Behavior.add_compare = function () {
    var default_language = $('#default_language').val();
    $('.core-subscriptions-admincp-add-compare input:radio').on('click', function () {
        var value = parseInt($(this).val());
        var id = $(this).data('id');
        if(value == 1 || value == 2)
        {
            $('.text-selection-' + id).hide();
            $('#text_field_' + id + '_' + default_language).val('');
        }
        else
        {
            $('.text-selection-' + id).show();
        }
    });
};

$Behavior.deletereason = function () {
    $('.core-subscriptions-admincp-delete-reason .delete-option input:radio').on('change', function () {
        var value = parseInt($(this).val());
        if(value == 1)
        {
            $('.extra-option').hide();
        }
        else
        {
            $('.extra-option').show();
        }
    });
};