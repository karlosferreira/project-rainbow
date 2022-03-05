<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div id="js_p_statusbg_collection_list" style="display: none;">
    <input type="hidden" name="val[status_background_id]" id="js_p_statusbg_background_id" value="0">
	<div class="p-statusbg-collection-container">
	    <ul class="nav p-statusbg-collection-header" {if $iTotalCollection == 1}style="display:none"{/if}>
            {foreach from=$aCollections item=aCollection}
			    <li class="{if !$aCollection.is_default || $iTotalCollection == 1}active{/if} js_switch_collection_li "><a class="item-collection js_switch_collection" data-toggle="tab" href="#collection_{$aCollection.collection_id}">{_p var=$aCollection.title}</a></li>
            {/foreach}
            <div class="collection-header-nav-button js_p_statusbg_header_nav" style="display: none">
            	<div class="item-prev disabled" ><span class="ico ico-angle-left"></span></div>
            	<div class="item-next" ><span class="ico ico-angle-right"></span></div>
            </div>
	    </ul>
	     <div class="tab-content p-statusbg-collection-content">
             {foreach from=$aCollections item=aCollection}
                 <div class="tab-pane{if !$aCollection.is_default || $iTotalCollection == 1} active{/if}" id="collection_{$aCollection.collection_id}">
                     <div class="p-statusbg-collection-listing">
                         <div class="collection-item{if !$aCollection.is_default || $iTotalCollection == 1} active{/if}" data-background_id="0" data-image_url="" onclick="PStatusBg.selectBackground(this);">
                             <div class="item-outer">
                                 <span class="item-bg" style="background-color:#fff"></span>
                             </div>
                         </div>
                         {foreach from=$aCollection.backgrounds_list key=iKey item=aBackground}
                             <div class="collection-item {if $iKey > 13}hide js_bg_hide_{$aCollection.collection_id}{/if}" data-background_id="{$aBackground.background_id}" data-image_url="{$aBackground.full_path}" onclick="PStatusBg.selectBackground(this);">
                                 <div class="item-outer">
                                     <span class="item-bg" style="background-image: url({$aBackground.full_path})"></span>
                                 </div>
                             </div>
                         {/foreach}
                         {if $iKey > 13}
                             <div class="collection-item js_bg_show_full" data-collection_id="{$aCollection.collection_id}" onclick="PStatusBg.showFullCollection(this); return false;">
                                 <div class="item-outer">
                                     <div class="item-bg-more"><span class="ico ico-dottedmore-o"></span></div>
                                 </div>
                             </div>
                         {/if}
                     </div>
                 </div>
             {/foreach}
	     </div>
	 </div>
</div>
