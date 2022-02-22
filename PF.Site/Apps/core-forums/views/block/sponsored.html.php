<?php
	defined('PHPFOX') or exit('NO DICE!');
?>
<div class="item-container forum-app recent-discussion">
    <div class="sticky-label-icon sticky-sponsored-icon">
        <span class="flag-style-arrow"></span>
        <i class="ico ico-sponsor"></i>
    </div>
    <div class="item-container-sponsor">
        {foreach from=$aSponsoredThreads item=aThread}
            {template file='forum.block.thread-entry'}
        {/foreach}
    </div>
</div>