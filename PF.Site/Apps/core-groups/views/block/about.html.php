<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="block">
    <div class="title">
        {_p('Group Info')}
    </div>
    <div class="content item-group-about {if !isset($aUser)}group-hide-founder{/if}">
        {if isset($aUser)}
        <div class="item-outer">
            <div class="user_rows_image">
                {img user=$aUser suffix='_120_square'}
            </div>
            <div class="item-inner">
                <div class="item-title">{$aUser|user}</div>
                <div class="item-info">{_p var='Founder'}</div>
            </div>
            
        </div>
        {/if}

        {if $hasPermToViewPublishDate}
        <div class="item-publish-date">
            <span class="item-title">{_p var='groups_publish_date_title'}: </span>
            <span class="item-date">{$aPage.time_stamp|convert_time}</span>
        </div>
        {/if}

        {if !empty($page.text_parsed)}
        <div class="item-desc item_view_content">
        {$page.text_parsed|parse|shorten:170:'more':true}
        </div>
        {/if}
    </div>
</div>