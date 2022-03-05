<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="form-group">
    <p class="help-block">
        {_p var='google_2step_verify_description'}
    </p>
    {if $sQRCodeUrl && $iPassValidate}
        <p class="help-block">
            {_p var='use_google_authenticator_app_to_scan_this_qr_code_or_enter_setup_key'}
        </p>
        <div class="t_center">
            <div id="js_passcode_qr_code_wrapper">
                <img src="{$sQRCodeUrl}" width="300" height="300" />
            </div>
            <div>
                <div class="mb-1">
                    <a href="#" id="js_passcode_manual_key" onclick="$(this).showManualPasscodeKey(); return false;">{_p var='cant_scan_the_qr_code'}</a>
                </div>
                <div id="js_passcode_manual_key_wrapper" class="mt-4 mb-4" style="display: none">
                    <div class="mb-1">
                        <a href="#" id="js_passcode_qr_code" onclick="$(this).showManualPasscodeKey(true); return false;">{_p var='use_qr_code'}</a>
                    </div>
                    <div class="mb-1">{_p var='enter_this_secret_key_into_your_authenticator_app'}</div>
                    <div class="input-group" style="justify-content: center">
                        <div class="form-inline">
                            <input type="text" id="js_passcode_hex_key" class="form-control" readonly value="{$sHexKey}" style="text-overflow: inherit"/>
                        </div>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary ml-1 js_passcode_key_copy" style="display: none" data-clipboard-target="#js_passcode_hex_key" data-copied-text="{_p var='copied'}" data-text="{_p var='copy'}">{_p var='copy'}</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div>
            <div class="mb-1">{_p var='passcode_from_this_qr_code_is_only_associated'}:</div>
            {if !empty($sEmail)}
                <div>
                    <strong>{_p var='email'}:</strong> {$sEmail}
                </div>
            {/if}
            {if !empty($sPhone) && Phpfox::getParam('core.enable_register_with_phone_number')}
                <div>
                    <strong>{_p var='phone_number'}:</strong> {$sPhone}
                </div>
            {/if}
        </div>
        <div class="help-block mt-1">
            {if Phpfox::getParam('core.enable_register_with_phone_number')}
                {_p var='get_new_google_authenticator_barcode_when_you_change_email_phone'}
            {else}
                {_p var='get_new_google_authencator_barcode_when_you_change_email'}
            {/if}
        </div>
    {/if}
</div>
{if !$iPassValidate}
    <form method="post" class="form">
    <div class="form-group">
        <label for="email">{required}{_p var='enter_your_password'}</label>
        <input class="form-control" id="password" type="password" required name="val[password]" value="" placeholder="{_p var='password'}"/>
        <div class="help-block">{_p var='you_need_to_enter_your_password_to_see_the_qr_code'}</div>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">
            {_p var='submit'}
        </button>
    </div>
</form>
{/if}

{literal}
    <script>
        $Behavior.onLoadPasscodePage = function () {
            $.fn.showManualPasscodeKey = function (hide) {
                if (!hide) {
                    $('#js_passcode_manual_key_wrapper').show();
                    $('#js_passcode_qr_code_wrapper').hide();
                    $(this).hide();
                } else {
                    $('#js_passcode_manual_key_wrapper').hide();
                    $('#js_passcode_qr_code_wrapper').show();
                    $('#js_passcode_manual_key').show();
                }
            }
            if (typeof ClipboardJS !== 'undefined' && ClipboardJS.isSupported()) {
                var timeOutCopied = null;
                window.setTimeout(function () {
                    new ClipboardJS('.js_passcode_key_copy').on('success', function(e) {
                        var ele = $(e.trigger);
                        if (ele.length && e.action === 'copy') {
                            timeOutCopied && clearTimeout(timeOutCopied);
                            ele.html(ele.data('copied-text'));
                            timeOutCopied = setTimeout(function() {
                                ele.html(ele.data('text'));
                            }, 1500);
                        }
                    });
                    $('.js_passcode_key_copy').show();
                }, 2000);
            }
        }
    </script>
{/literal}