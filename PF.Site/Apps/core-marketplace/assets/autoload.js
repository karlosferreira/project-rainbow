PF.event.on('on_document_ready_end', function () {
    $Core.marketplace.initAddListing();
    $Core.marketplace.reloadValidation.init();
});

PF.event.on('on_page_change_end', function () {
    $Core.marketplace.initAddListing();
    $Core.marketplace.reloadValidation.init();
});

$Behavior.marketplaceAdd = function () {
    $('.js_mp_category_list').change(function () {
        var iParentId = parseInt(this.id.replace('js_mp_id_', ''));
        $('.js_mp_category_list').each(function () {
            if (parseInt(this.id.replace('js_mp_id_', '')) > iParentId) {
                $('#js_mp_holder_' + this.id.replace('js_mp_id_', '')).hide();
                this.value = 0;
            }
        });

        $('#js_mp_holder_' + $(this).val()).show();
    });
    if ($('#js_marketplace_form').length > 0 && typeof($Core.dropzone.instance['marketplace']) != 'undefined') {
        $Core.dropzone.instance['marketplace'].files = [];
    }
}

$Core.marketplace =
{
    sUrl: '',
    url: function (sUrl) {
        this.sUrl = sUrl;
    },
    canBuy: function(obj) {
        let _this = $(obj),
          listingId = _this.data('id') ? _this.data('id') : 0,
          invoiceId = _this.data('invoice') ? _this.data('invoice') : 0;
        if (listingId && invoiceId) {
            _this.attr('style', 'pointer-events: none');
            $.fn.ajaxCall('marketplace.canBuy', $.param({
                id: listingId,
                invoice_id: invoiceId,
            }), null, null, function() {
                _this.attr('style', '');
            });
        }
        return false;
    },
    action: function (oObj, sAction) {
        aParams = $.getParams(oObj.href);

        $('.dropContent').hide();

        switch (sAction) {
            case 'edit':
                window.location.href = this.sUrl + 'add/id_' + aParams['id'] + '/';
                break;
            case 'delete':
                var url = this.sUrl;
                $Core.jsConfirm({}, function () {
                    window.location.href = url + 'delete_' + aParams['id'] + '/';
                }, function () {
                });
                break;
            default:

                break;
        }

        return false;
    },

    dropzoneOnSending: function (data, xhr, formData) {
        $('#js_marketplace_form').find('input[type="hidden"]').each(function () {
            formData.append($(this).prop('name'), $(this).val());
        });
    },

    dropzoneOnSuccess: function (ele, file, response) {
        $Core.marketplace.processResponse(ele, file, response);
    },

    dropzoneOnError: function (ele, file) {

    },

    dropzoneQueueComplete: function () {
        $('#js_listing_done_upload').show();
    },
    processResponse: function (t, file, response) {
        response = JSON.parse(response);
        if (typeof response.id !== 'undefined') {
            file.item_id = response.id;
            if (typeof t.data('submit-button') !== 'undefined') {
                var ids = '';
                if (typeof $(t.data('submit-button')).data('ids') !== 'undefined') {
                    ids = $(t.data('submit-button')).data('ids');
                }
                $(t.data('submit-button')).data('ids', ids + ',' + response.id);
            }
        }
        // show error message
        if (typeof response.errors != 'undefined') {
            for (var i in response.errors) {
                if (response.errors[i]) {
                    $Core.dropzone.setFileError('marketplace', file, response.errors[i]);
                    return;
                }
            }
        }
        return file.previewElement.classList.add('dz-success');
    },
    toggleUploadSection: function (id) {
        var parent = $('#js_mp_block_customize'),
            show_upload = 1;
        if (parent.hasClass('show_form')) {
            parent.removeClass('show_form');
            show_upload = 0;
            $Core.dropzone.instance['marketplace'].files = [];
        }
        else {
            parent.addClass('show_form');
        }

        parent.html('<div class="js_loading_form text-center "><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>');
        $.ajaxCall('marketplace.toggleUploadSection', 'show_upload=' + show_upload + '&id=' + id);
    },
    deleteListing: function (ele) {

        if (!ele.data('id')) return false;

        $Core.jsConfirm({message: ele.data('message')}, function () {
            $.ajaxCall('marketplace.delete', 'id=' + ele.data('id') + '&is_detail=' + ele.data('is-detail'));
        }, function () {
        });

        return false;
    },
    contactSeller: function($aParams) {
        tb_show('', $.ajaxBox('mail.compose', 'height=300&width=500&no_remove_box=true&' + $.param($aParams)));
    },
    initAddListing: function () {
        var oForm = $('#js_marketplace_form'), oSell = $('#js-marketplace-is-sell'), oPoint = $('#js-marketplace-activity-point'),
        oCurrency = oForm.find('select[name="val[currency_id]"]');
        if (!oForm.length || !oSell.length) return false;
        var bHaveGateway = oSell.data('have-gateway'),
        bCanSell = oSell.data('can-sell'),
        bAllowAP = oSell.data('allow-ap');
        if (bAllowAP && typeof marketplace_valid_convert_rate === "string") {
            var validRate = JSON.parse(marketplace_valid_convert_rate);
            oCurrency.addClass('dont-unbind').off('change').on('change', function () {
                var value = $(this).val();
                if (validRate.indexOf(value) === -1) {
                    var checked = oPoint.find('.item_is_active_holder').find('input[name="val[allow_point_payment]"]:checked');
                    if (checked.val() !== '0') {
                        oPoint.find('.item_is_active_holder .item_is_active').trigger('click');
                    }
                    oPoint.hide();
                } else {
                    oPoint.show();
                }
            });
            oCurrency.trigger('click');
        }
        //Do nothing if user have gateway config
        if (bCanSell && bHaveGateway) return false;

        if (!bHaveGateway && bAllowAP) {
            //Related
            oSell.addClass('dont-unbind-children').find('.item_is_active_holder').on('click', function () {
               var checked = $(this).find('input[name="val[is_sell]"]:checked'), value = checked.val();
               console.log('Value', value);
               if (value !== '0') {
                   oPoint.find('.item_is_active > input:radio').prop('checked', true);
                   oPoint.find('.item_is_active_holder').removeClass('item_selection_not_active').addClass('item_selection_active');
               }
            });
            oPoint.addClass('dont-unbind-children').find('.item_is_active_holder').on('click', function () {
               var checked = $(this).find('input[name="val[allow_point_payment]"]:checked'), value = checked.val();
               if (value === '0') {
                   //Disable point too
                   oSell.find('.item_is_not_active > input:radio').prop('checked', true);
                   oSell.find('.item_is_active_holder').removeClass('item_selection_active').addClass('item_selection_not_active');
               }
            });
        }
        return true;
    }
};

$Behavior.marketplaceShowImage = function () {

    $('.listing_view_images ._thumbs img').each(function () {
        var t = $(this),
            src = t.attr('src').replace('_120_square', '_400_square'),
            img = new Image();

        if (src == $('.listing_view_images ._main img').attr('src')) {
            t.addClass('active');
        }

        img.src = src;
    });

    $('.listing_view_images ._thumbs img').click(function () {
        var t = $(this),
            src = t.attr('src').replace('_120_square', '_400_square');

        $('.listing_view_images ._thumbs img.active').removeClass('active');
        $('.listing_view_images ._main img').attr('src', src);
        t.addClass('active');
    });
}
var core_marketplace_onchangeDeleteCategoryType = function (type) {
    if (type == 2)
        $('#category_select').show();
    else
        $('#category_select').hide();
};

$Core.marketplace.reloadValidation = {
    bCanUseCoreReloadValidation: $Core.hasOwnProperty('reloadValidation') && typeof $Core.reloadValidation !== "undefined",

    init: function() {
        if(!$('#js_marketplace_form').length || !$Core.marketplace.reloadValidation.bCanUseCoreReloadValidation){
            return false;
        }

        $('#currency_id').addClass('close_warning');
        $('#js_find_friend').addClass('close_warning');
        $Core.reloadValidation.init();

        $('#js_marketplace_form #js_friend_search_content input[type="checkbox"], #js_marketplace_form #deselect_all_friends').on('click', function () {
            if (parseInt(trim($('#js_marketplace_form #deselect_all_friends').children().text())) > 0) {
                $Core.reloadValidation.changedEleData.js_marketplace_form['invite_friend'] = true;
            } else {
                delete $Core.reloadValidation.changedEleData.js_marketplace_form['invite_friend'];
            }
            $Core.reloadValidation.preventReload();
        });
    }
}