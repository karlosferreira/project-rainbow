<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="js_comment_sticker_collection_holder">
    <div class="js_comment_preview_sticker_set_holder hide"></div>
    <div class="comment-sticker-store js_comment_sticker_sets_holder" >
        <div class="page_section_menu comment-sticker-store-header" >
            <ul class="nav nav-tabs nav-justified">
                <li class="active"><a data-toggle="tab" id="js_comment_sticker_all" href="#core_comment_sticker_all" rel="core_comment_sticker_all">{_p var='all_stickers'} <span class="item-number">({$iTotalSets})</span></a></li>
                <li><a data-toggle="tab" href="#core_comment_sticker_my" rel="core_comment_sticker_my" onclick="setTimeout(function(){l}$Core.Comment.initCanvasForSticker('.core_comment_gif:not(.comment_built)'){r},100); return true;">{_p var='my_stickers'} <span class="item-number js_comment_my_sticker_set_total" data-total="{$iTotalMy}">({$iTotalMy})</span></a></li>
            </ul>
        </div>
        <div class="tab-content">
            <div id="core_comment_sticker_all" class="page_section_menu_holder core_comment_sticker_all">
                <div class="comment-store-list">
                    <div class="item-container">
                        {foreach from=$aAllStickers key=iKey item=aSet}
                            {template file='comment.block.sticker-set'}
                        {/foreach}
                    </div>
                </div>
            </div>
            <div id="core_comment_sticker_my" class="page_section_menu_holder core_comment_sticker_my" style="display: none;">
                <div class="comment-store-list">
                    <div class="item-container">
                        {if $iTotalMy}
                            {foreach from=$aUserStickers key=iKey item=aSet}
                                {template file='comment.block.sticker-set'}
                            {/foreach}
                        {/if}
                        <div class="comment-none-sticker-store js_comment_none_sticker_set" {if $iTotalMy}style="display:none"{/if}>
                            <div class="none-sticker-icon"><i class="ico "><svg class="sticker-o" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                         viewBox="0 0 24 24" style="enable-background:new 0 0 24 24;" xml:space="preserve">
                                                    <g>
                                                        <path  d="M12,24C5.4,24,0,18.6,0,12S5.4,0,12,0h0.4L24,11.6l0,0.4C24,18.6,18.6,24,12,24z M10.9,2.1C5.8,2.6,2,6.9,2,12
                                                            c0,5.5,4.5,10,10,10c5.1,0,9.4-3.8,9.9-8.9c-0.2,0-0.4,0-0.5,0c-3.4,0-6-0.9-7.8-2.6C11.7,8.6,10.8,5.8,10.9,2.1z M13,3.4
                                                            c0.1,2.5,0.8,4.3,2,5.6c1.2,1.2,3.1,1.9,5.6,2L13,3.4z"/>
                                                        <g>
                                                            <path d="M10.2,12.3c-0.5,0.3-1.1,0.1-1.4-0.4c-0.3-0.5-0.9-0.7-1.4-0.4c-0.5,0.3-0.7,0.9-0.4,1.4c0.3,0.5,0.1,1.1-0.4,1.4
                                                                c-0.5,0.3-1.1,0.1-1.4-0.4c-0.8-1.5-0.2-3.3,1.3-4.1s3.3-0.2,4.1,1.3C10.9,11.5,10.7,12.1,10.2,12.3z"/>
                                                            <path d="M16.6,13.8c0.8,1.5,0.2,3.3-1.3,4.1c-1.5,0.8-3.3,0.2-4.1-1.3S15.9,12.3,16.6,13.8z"/>
                                                        </g>
                                                    </g>
                                                    </svg>
                                                </i></div>
                            <div class="none-sticker-info">{_p var='no_stickers_found_add_some_stickers_to_your_library_now'}</div>
                            <a onclick="$('#js_comment_sticker_all').trigger('click'); return false;" class="btn btn-primary btn-sm mt-2">{_p var='add'}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>