<?php

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="breadcrumbs_right_section" id="breadcrumbs_menu">
{if (!defined('PHPFOX_IS_PAGES_VIEW') || !PHPFOX_IS_PAGES_VIEW) && (!defined('PHPFOX_IS_USER_PROFILE') || !PHPFOX_IS_USER_PROFILE)}
    {template file='core.block.actions-buttons'}
{/if}
</div>
{if !empty($aBreadCrumbs) && count($aBreadCrumbs) >= 2 && !Phpfox::isAdminPanel()}
    <div class="container" id="js_block_border_core_breadcrumb">
        <div class="content">
            <div class="row breadcrumbs-holder">
                <div class="clearfix breadcrumbs-top">
                    <div class="breadcrumbs-container">
                        <div class="breadcrumbs-list">
                            <ol class="breadcrumb" data-component="breadcrumb">
                                {foreach from=$aBreadCrumbs key=sLink item=sCrumb name=link}
                                    <li>
                                        <a {if !empty($sLink)}href="{$sLink}" {/if} class="ajax_link">
                                            {$sCrumb|clean}
                                        </a>
                                    </li>
                                {/foreach}
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}