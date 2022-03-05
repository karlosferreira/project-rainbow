<div class="renew-payment-method">
    <div class="introduce">
        {_p var='subscribe_renew_title'}
    </div>
    <div class="selection pt-2">
        {foreach from=$aPaymentMethods item=aMethod}
        <div><label><input type="radio" class="mr-1" value="{$aMethod.value}" name="renew-method" {if $aMethod.checked}checked="checked"{/if}>{$aMethod.title}</label></div>
        {/foreach}
    </div>

    <div class="selection-button mt-1">
        <button type="button" class="btn btn-primary" onclick="tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;id={$iPackageId}&amp;renew_type='+ $('input[name=renew-method]:checked').val())); js_box_remove(this);">{_p var='next'}</button>
    </div>
</div>
