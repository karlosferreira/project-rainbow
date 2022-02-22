<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if empty($infoItems)}
    {_p var='n_a'}
{else}
    {if count($infoItems) > 3}
        {$infoItems.0}, {$infoItems.1} {_p var='and'}
        <span>
            <a role="button" data-toggle="tooltip" data-placement="bottom" >{php}echo (count($this->_aVars['infoItems']) - 2) {/php} {_p var='others'}</a>
            <div class="hide tooltip-html">
                {foreach from=$infoItems key=infoItemKey item=info}
                    {if $infoItemKey != 0 && $infoItemKey != 1}
                        <p>{$info}</p>
                    {/if}
                {/foreach}
            </div>
        </span>
    {else}
        {', '|implode:$infoItems}
    {/if}
{/if}
{unset var=$infoItems}
