<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:39 am */ ?>
<?php 

 

?>
<form method="post" action="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('current', [], false, false); ?>" id="js_global_multi_form_holder" class="form">
<?php if (! empty ( $this->_aVars['sCustomModerationFields'] )): ?>
<?php echo $this->_aVars['sCustomModerationFields']; ?>
<?php endif; ?>
	<div id="js_global_multi_form_ids"><?php echo $this->_aVars['sInputFields']; ?></div>
    <div class="moderation_placeholder">
        <span class="moderation_process"><?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => 'ajax/add.gif')); ?></span>
        <button class="btn btn-sm btn-select-all" data-cmd="core.toggle_check_all" type="button" data-txt1="<?php echo _p('select_all'); ?>" data-txt2="<?php echo _p('un_select_all'); ?>">
<?php echo _p('select_all'); ?>
        </button>
        
        <div class="dropup moderation-dropdown hide" id="moderation_drop_down">
            <button type="button" class="btn btn-sm" data-toggle="dropdown">
                <span id="moderation_badge" class="mr-1">8</span> 
                <i class="fa fa-caret-up"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-right" id="moderation_menu">
<?php if (count((array)$this->_aVars['aModerationParams']['menu'])):  foreach ((array) $this->_aVars['aModerationParams']['menu'] as $this->_aVars['aModerationMenu']): ?>
                <li>
                    <a data-cmd="core.moderation_action" href="#<?php echo $this->_aVars['aModerationMenu']['action']; ?>" class="moderation_process_action" rel="<?php echo $this->_aVars['aModerationParams']['ajax']; ?>" <?php if (! empty ( $this->_aVars['aModerationMenu']['message'] )): ?>data-message="<?php echo $this->_aVars['aModerationMenu']['message']; ?>"<?php endif; ?> <?php if (! empty ( $this->_aVars['aModerationParams']['extra'] )): ?>data-extra="<?php echo $this->_aVars['aModerationParams']['extra']; ?>"<?php endif; ?>><?php echo $this->_aVars['aModerationMenu']['phrase']; ?></a>
                </li>
<?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
	<div class="moderation_holder hide btn-group dropup <?php if (! $this->_aVars['iTotalInputFields']): ?> not_active<?php endif; ?>">
		<a role="button" class="btn btn-sm moderation_drop pull-left"><span> (<strong class="js_global_multi_total"><?php echo $this->_aVars['iTotalInputFields']; ?></strong>)</span></a>
		<a role="button" class="moderation_action moderation_action_select btn btn-sm pull-right"
		   rel="select"><?php echo _p('select_all'); ?>
		</a>
	</div>

</form>

