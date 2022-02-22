<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="sub_section_menu core-block-categories p-saveditems-type-category">
    <ul class="action category-list">
    {if $allTotalItem > 0 && is_array($aSaveItemTypes) && count($aSaveItemTypes) > 1}
        <li class="{if $currentType == 'all'}active{/if} category" >
            <div class="category-item">
                <a class="name" href="{url link='saved' type='all'}">
                    <span>{_p var='all'}</span>
                    <span class="pull-right">{$allTotalItem}</span>
                </a>
            </div>
        </li>
    {/if}
    {foreach from=$aSaveItemTypes item=aSaveItemType}
        {if $aSaveItemType.total_item > 0 }
        <li class="{if $currentType == $aSaveItemType.type_id}active{/if} category" >
            <div class="category-item">
                <a class="name" href="{$aSaveItemType.url}">
                    <span>{$aSaveItemType.type_name}</span>
                    <span class="pull-right">{$aSaveItemType.total_item}</span>
                </a>
            </div>
        </li>
        {/if}
    {/foreach}
    </ul>
</div>
