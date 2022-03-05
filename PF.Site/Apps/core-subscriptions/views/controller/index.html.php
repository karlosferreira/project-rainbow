<?php
    defined('PHPFOX') or exit('NO DICE!');
?>

{if Phpfox::getParam('subscribe.enable_subscription_packages')}
    {if !empty($aPackages)}
        <div class="item-container membership-package">
            <div class="membership-package__outer membership-package-content-js">
                {foreach from=$aPackages item=aPackage name=packages}
                    <article class="membership-package__item">
                        <div class="item-outer text-center">
                            {if $aPackage.is_popular}
                                <div class="item-popular text-uppercase">{_p var='most_popular'}
                                    <span class="left"></span>
                                    <span class="right"></span>
                                </div>
                            {/if}
                            <div class="item-image">
                                {if !empty($aPackage.image_path)}
                                    <span style="background-image: url({img server_id=$aPackage.server_id title=$aPackage.title path='subscribe.url_image' file=$aPackage.image_path suffix='_120' max_width='120' max_height='120' return_url=true})"></span>
                                {else}
                                    <span style="background-image: url({img server_id=0 title=$aPackage.title file=$sDefaultImagePath max_width='120' max_height='120' return_url=true})"></span>
                                {/if}
                            </div>
                            <p class="item-title mb-0" title="{$aPackage.title|convert|clean}">{$aPackage.title|convert|clean}</p>
                            {if $aPackage.show_price}
                                {if isset($aPackage.default_cost) && $aPackage.default_cost != '0.00'}
                                    <p class="item-price mb-0 fw-bold">
                                        {$aPackage.default_cost|currency:$aPackage.default_currency_id}
                                    </p>
                                    <p class="mb-0 recurring">
                                        {if isset($aPackage.default_recurring_cost)}
                                            {$aPackage.default_recurring_cost}
                                        {else}
                                            {_p var='subscribe_one_time'}
                                        {/if}
                                    </p>
                                {elseif isset($aPackage.price)}
                                    {foreach from=$aPackage.price item=sCurrency name=iCost}
                                        <span>{$sCurrency.currency_id}: <span class="subscription-price">{$sCurrency.cost}</span></span>
                                    {/foreach}
                                {else}
                                    <p class="item-price mb-0 fw-bold">
                                        {_p var='free'}
                                    </p>
                                    <p class="mb-0 recurring">
                                        {if isset($aPackage.default_recurring_cost)}
                                            {$aPackage.default_recurring_cost}
                                        {else}
                                            {_p var='subscribe_one_time'}
                                        {/if}
                                    </p>
                                {/if}
                            {/if}
                            {if !empty($aPackage.description)}
                                <p class="item-desc mb-0" title="{$aPackage.description|convert}">{$aPackage.description|convert}</p>
                            {/if}
                            <div class="item-sub text-center mt-1">
                                {if Phpfox::isUser()}
                                    {if !empty($aPackage.purchased_by_current_user)}
                                        <p class="mb-0">{_p var='subscribe_you_are_currently_using_this'}</p>
                                    {else}
                                        <a class="btn btn-lg btn-primary" href="#" onclick="{if (int)$aPackage.recurring_period > 0}tb_show('{_p var='subscribe_select_renew_method_title' phpfox_squote=true}', $.ajaxBox('subscribe.renew', 'height=400&amp;width=650&amp;id={$aPackage.package_id}'));{else}tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;id={$aPackage.package_id}'));{/if}">{_p var='subscribe_select_plan'}</a>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </article>
                {/foreach}
            </div>
        </div>
    {else}
        <div class="extra_info">
            {_p var='no_packages_available'}
        </div>
    {/if}
{else}
    <div class="extra_info">
        {_p var='subscribe_membership_package_not_available'}
    </div>
{/if}