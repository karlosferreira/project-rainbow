<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !Phpfox::getUserBy('profile_page_id') && isset($aPage)}
    {if empty($aPage.is_reg)}
        {if !empty($aPage.is_liked)}
            <div class="dropdown">
                <a data-toggle="dropdown" class="btn btn-default btn-icon item-icon-joined btn-round">
                    <span class="ico ico-check"></span>
                    {_p var='joined'}
                    <span class="ml-1 ico ico-caret-down"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a role="button" onclick="$.ajaxCall('like.delete', 'type_id=groups&amp;item_id={$aPage.page_id}&amp;reload=1'); return false;">
                            <span class="ico ico-close"></span>
                            {_p var='unjoin'}
                        </a>
                    </li>
                </ul>
            </div>
        {else}
            <button class="btn btn-primary btn-round btn-gradient btn-icon item-icon-join" onclick="$(this).remove(); {if $aPage.reg_method == '1' && !isset($aPage.is_invited)} $.ajaxCall('groups.signup', 'page_id={$aPage.page_id}'); {else}$.ajaxCall('like.add', 'type_id=groups&amp;item_id={$aPage.page_id}&amp;reload=1');{/if} return false;">
                <span class="ico ico-plus"></span>{_p var='join'}
            </button>
        {/if}
    {else}
        <div class="dropdown">
            <a data-toggle="dropdown" class="btn btn-default btn-icon item-icon-joined btn-round">
                <span class="ico ico-sandclock-goingon-o"></span>
                {_p var='groups_pending_membership_request'}
                <span class="ml-1 ico ico-caret-down"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li>
                    <a role="button" onclick="$.ajaxCall('groups.deleteRequest', 'page_id={$aPage.page_id}'); return false;">
                        <span class="ico ico-close"></span>
                        {_p var='delete_request'}
                    </a>
                </li>
            </ul>
        </div>
    {/if}
{/if}
