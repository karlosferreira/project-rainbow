<div class="core-subscription-renew-payment-method" id="js_subscribe_renew_payment_method">
    <input type="hidden" id="js_subscription_id" value="{$iPurchaseId}">
    <input type="hidden" id="js_subscription_redirect_url" value="{$sPaymentGatewayUrl}">
    {if !empty($bFromSignup)}
    <input type="hidden" name="login" value="1">
    {/if}
    <div class="introduce">
        {_p var='subscribe_select_method_for_renewing_subscription'}
    </div>
    <div class="selection pt-2">
        {foreach from=$aPaymentMethods item=aMethod}
        <div><label><input type="radio" class="mr-1" value="{$aMethod.value}" name="core-subscription-renew-method" {if $aMethod.checked}checked="checked"{/if}>{$aMethod.title}</label></div>
        {/foreach}
    </div>

    <div class="selection-button mt-1">
        <button id="js_renew_method_action" type="button" class="btn btn-primary">{_p var='next'}</button>
    </div>
</div>
