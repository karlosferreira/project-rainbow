<?php
defined('PHPFOX') or exit('NO DICE!');

?>
<div id="profile_activity_statistics_block" class="activity-point-container">
    <div class="item-total">
        <div class="item-total-info"><i class="ico ico-info-circle-alt-o"></i> <div class="item-number"><span>{$iTotalItems}</span> {_p var='total_items'}</div></div>
    </div>
    <div class="item-detail-container">
        {foreach from=$aActivites key=sPhrase item=sValue}
        <div class="item-info">
            <div class="item-info-outer">
                <div class="item-title">
                    <?php if (isset($this->_aVars['aIcons'][$this->_aVars['sPhrase']])): ?>
                        <i class="<?php echo $this->_aVars['aIcons'][$this->_aVars['sPhrase']]; ?>"></i>
                    <?php endif; ?>
                    {$sPhrase}:
                </div>
                <div class="item-count">
                    <span class="item-number">{if $sValue < 2}{_p var='point_item' point=$sValue}{else}{_p var='point_items' point=$sValue}{/if}</span>
                </div>
            </div>
        </div>
        {/foreach}
    </div>
</div>
