<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<article class="p-saveditems js_saved_item_{$item.saved_id}" data-target="saved_item" data-added="{if !empty($item.collections)}1{else}0{/if}" data-collection="{if !empty($searchByCollection) && !empty($searchCollection)}{$searchCollection.collection_id}{/if}" data-id="{$item.saved_id}">
    <div class="p-saveditems-outer">
        <div class="p-saveditems-media-wrapper">
            {if empty($item.item_display_photo) && !(($item.item_type_id == 'link' && !empty($item.item_photo)) || !empty($item.user_image))}
            {img user=$item max_width='160' href=$item.saved_link}
            {else}
            <a class="p-saveditems-media-link" href="{$item.saved_link}" target="_blank">
                <span class="p-saveditems-media-src" style="background-image: url({if $item.item_type_id == 'link' && !empty($item.item_photo)}{$item.item_photo}{elseif !empty($item.item_display_photo)}{$item.item_display_photo}{else}{img user=$item suffix='_200_square' return_url=true}{/if});"></span>
            </a>
            {/if}
        </div>
        <div class="p-saveditems-inner">
            <div class="p-saveditems-title">
                {if !$bInCollectionView}
                    <span class="p-saveditems-status {if !$item.unopened}hide{/if}" data-toggle="tooltip" title="{_p var='saveditems_unopened'}" data-target="saveditems_status"></span>
                {/if}
                <a href="{$item.saved_link}" title="{$item.item_title}" target="_blank">{if !empty($item.item_title)}{$item.item_title}{else}{$item.full_name}{/if}</a>
                {if $item.canUnsaved}
                <span class="p-saveditems-btn-close" onclick="return appSavedItem.unsave(this);" title="{if !empty($searchByCollection) && !empty($searchCollection)}{_p var='saveditems_remove_from_the_collection'}{else}{_p var='saveditems_unsave'}{/if}" {if !empty($searchByCollection) && !empty($searchCollection)}data-remove-from-collection="1"{/if}><i class="ico ico-close"></i></span>
                {/if}
            </div>
            <div class="p-saveditems-minor-info p-saveditems-seperate-dot-wrapper js_collection_information">
                <span class="p-saveditems-seperate-dot-item">{if in_array($item.item_type_id, array('user_status', 'link'))}{_p var=$item.item_type_id}{else}{$item.item_name}{/if}</span>
                <span class="p-saveditems-seperate-dot-item p-item-author"><span class="p-saveditems-user">{_p var='posted_by'}</span> {$item|user}</span>
                {if $bInCollectionView}
                    <span class="p-saveditems-seperate-dot-item p-item-author"><span class="p-saveditems-user">{_p var='added_by'}</span> {$item.added_user|user}
                {/if}
                    </span>
                {assign var=itemCollections value=$item.collections}
                {template file='saveditems.block.collection.list'}
            </div>
            {if !empty($item.extra.additional_information)}
            <div class="p-saveditems-additional-info">
                <p class="p-saveditems-additional-info-wrapper item_view_content">
                    {if $item.extra.additional_information.type == 'price'}
                    <span class="p-saveditems-listing-price">{$item.extra.additional_information.value}</span>
                    {elseif $item.extra.additional_information.type == 'link'}
                    <a href="{$item.extra.additional_information.value}" {if !empty($item.extra.additional_information.target)}target="{$item.extra.additional_information.target}"{/if}>{if !empty($item.extra.additional_information.title)}{$item.extra.additional_information.title}{else}{$item.extra.additional_information.value}{/if}</a>
                    {elseif $item.extra.additional_information.type == 'date_time'}
                        {if is_numeric($item.extra.additional_information.value)}
                        {$item.extra.additional_information.value|convert_time}
                        {else}
                        {$item.extra.additional_information.value}
                        {/if}
                    {else}
                    {$item.extra.additional_information.value|striptag|stripbb|clean}
                    {/if}
                </p>
            </div>
            {/if}
            <div class="p-saveditems-group-btn">
                {if $item.canShare}
                <button class="btn btn-default btn-sm" onclick="tb_show('{_p var='share' phpfox_squote=true}', $.ajaxBox('share.popup', 'height=300&amp;width=550&amp;type=feed&amp;url={$item.link_parsed}&amp;title={$item.item_title_parsed}&amp;feed_id={$item.item_id}&amp;sharemodule={$item.item_type_id}')); return false;"><i class="ico ico-share-o"></i><span class="p-saveditems-btn-text">{_p var='share'}</span></button>
                {/if}

                {assign var=savedId value=$item.saved_id}
                {assign var=collectionIds value=$item.collections.id}
                {template file='saveditems.block.collection.add-to-collection'}

                {if $item.canUnsaved}
                <div class="dropdown">
                    <a class="btn btn-default btn-sm p-saveditems-btn-more" role="button" data-toggle="dropdown">
                        <i class="ico ico-dottedmore-o"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">

                        <li>
                            <a href="javascript:void(0);" onclick="return appSavedItem.unsave(this);">{_p var='saveditems_unsave'}</a>
                        </li>
                        {if !$bInCollectionView}
                            <li data-id="{$item.saved_id}">
                                <a class="{if !$item.unopened}hide{/if} js_saveditems_status" href="javascript:void(0);" onclick="return appSavedItem.processItemStatus(this);" data-status="0">{_p var='saveditems_mark_as_opened'}</a>
                                <a class="{if $item.unopened}hide{/if} js_saveditems_status" href="javascript:void(0);" onclick="return appSavedItem.processItemStatus(this);" data-status="1">{_p var='saveditems_mark_as_unopened'}</a>
                            </li>
                        {/if}
                    </ul>
                </div>
                {/if}
            </div>
        </div>
        {unset var=$savedId}
        {unset var=$collectionIds}
    </div>
</article>
