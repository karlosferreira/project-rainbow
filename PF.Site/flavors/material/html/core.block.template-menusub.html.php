<?php

defined('PHPFOX') or exit('NO DICE!');

?>
{if isset($aFilterMenus) && is_array($aFilterMenus) && count($aFilterMenus)}
<div class="block" id="js_block_border_core_menusub">
    <div class="title">
        {if isset($sMenuBlockTitle)}
            {$sMenuBlockTitle}
        {else}
            {_p var='menu'}
        {/if}
    </div>
    <div class="content">
        <div class="sub-section-menu header_display">
            {if !empty($aMainSelectedMenu) && !empty($aMainSelectedMenu.var_name)}
            <div class="app-name">{_p var=$aMainSelectedMenu.var_name}{if isset($aMainSelectedMenu.suffix)}{$aMainSelectedMenu.suffix}{/if}</div>
            {/if}
            <ul class="action" {if !isset($sMenuBlockTitle)}data-component="menu"{/if}>
                <div class="overlay"></div>
                {foreach from=$aFilterMenus name=filtermenu item=aFilterMenu}
                    {if !isset($aFilterMenu.name)}
                    <li class="menu_line"></li>
                    {else}
                    <li class="{if $aFilterMenu.active}active{/if}">
                        <?php
                        if (!empty($this->_aVars['aFilterMenusIcons'][$this->_aVars['aFilterMenu']['name']])):
                            echo sprintf("<span class='%s'></span>", $this->_aVars['aFilterMenusIcons'][$this->_aVars['aFilterMenu']['name']]);
                        endif;
                        ?>
                        <a href="{$aFilterMenu.link}">
                            {$aFilterMenu.name}
                        </a>
                    </li>
                    {/if}
                {/foreach}
                <li class="hide explorer">
                    <a data-toggle="dropdown" role="button">
                        <span class="ico ico-dottedmore-o"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                    </ul>
                </li>
            </ul>
        </div>

        <div class="sub-section-menu-mobile dropdown {if !empty($aSubMenus) || !empty($aCustomMenus)}has-btn-addnew{/if}">
            <span class="btn-toggle" data-toggle="dropdown" aria-expanded="false" role="button">
                <span class="ico ico-angle-down"></span>
            </span>
            <ul class="dropdown-menu">
                {foreach from=$aFilterMenus name=filtermenu item=aFilterMenu}
                {if !isset($aFilterMenu.name)}
                <li class="menu_line"></li>
                {else}
                <li class="{if $aFilterMenu.active}active{/if}">
                    <a href="{$aFilterMenu.link}">
                        {$aFilterMenu.name}
                    </a>
                </li>
                {/if}
                {/foreach}
            </ul>
        </div>
    </div>
</div>
{/if}


							