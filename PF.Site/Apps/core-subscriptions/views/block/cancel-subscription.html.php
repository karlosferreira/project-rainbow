<div class="cancel-subscription-block">
    <div class="my-subscription-items">
        <ul class="my-subscription-list">
            <li class="my-subscription-list-wapper">
                <div class="item-title fw-bold mb-2"><a href="{url link='subscribe.view'}?id={$aPurchase.purchase_id}">{$aPurchase.title_parse}</a></div>
                <div class="my-subscription-list-inner">
                    <a class="item-media" href="{url link='subscribe.view'}?id={$aPurchase.purchase_id}">
                        {if !empty($aPurchase.image_path)}
                            {img server_id=$aPurchase.server_id path='subscribe.url_image' file=$aPurchase.image_path suffix='_120' max_width='120' max_height='120'}
                        {else}
                            {img server_id=0 path='subscribe.app_url' file=$sDefaultPhoto max_width='120' max_height='120'}
                        {/if}
                    </a>
                    <div class="item-body">
                        {template file='subscribe.block.entry-info'}
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <div class="content"><strong>{$sContent}</strong></div>
    <ul class="sub-reason mt-2">
        {foreach from=$aReasons item=aReason key=reasonkey}
        <li>
            <div class="radio">
                <label>
                    <input type="radio" name="reason" value="{$aReason.reason_id}" {if (int)$reasonkey == 0}checked="checked"{/if}>
                    {$aReason.title_parsed}
                </label>
            </div>
        </li>
        {/foreach}
    </ul>
    
    {if !empty($sWarning)}
        <div class="sub-warning-alert mt-2">
            <span class="icon"><i class="ico ico-warning-circle-o"></i></span>
            <p class="text mb-0">{$sWarning}</p>
        </div>
    {/if}

    <div class="sub-bottom pt-2 text-right">
        <a href="javascript:void(0);" class="btn btn-default" onclick="js_box_remove(this)">{_p var='subscribe_keep_subscription'}</a>
        <a href="javascript:void(0);" class="btn btn-danger ml-1" onclick="$.ajaxCall('subscribe.cancelSubscription','purchase_id={$aPurchase.purchase_id}&user_id={$aPurchase.user_id}&user_group_failure={$aPurchase.fail_user_group}&package_id={$aPurchase.package_id}&reason_id=' + $('input[name=reason]:checked').val())">{_p var='subscribe_cancel_subscription'}</a>
    </div>
</div>

