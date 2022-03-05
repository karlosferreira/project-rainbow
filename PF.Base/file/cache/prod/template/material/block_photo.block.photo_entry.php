<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:39 am */ ?>
<article class="photo-listing-item <?php if (! $this->_aVars['aForms']['can_view']): ?>photo-mature<?php endif; ?>" data-url="<?php echo $this->_aVars['aForms']['link']; ?>" data-photo-id="<?php echo $this->_aVars['aForms']['photo_id']; ?>" id="js_photo_id_<?php echo $this->_aVars['aForms']['photo_id']; ?>" data-class="<?php if (! $this->_aVars['aForms']['can_view']): ?>photo-mature<?php endif; ?>">
    <div class="item-outer">
        <a class="item-media <?php if (! $this->_aVars['aForms']['can_view']): ?>no_ajax_link photo-mature<?php endif; ?>" <?php if (! $this->_aVars['aForms']['can_view']): ?> onclick="tb_show('<?php echo _p('warning'); ?>', $.ajaxBox('photo.warning', 'height=300&width=350&link=<?php echo $this->_aVars['aForms']['link']; ?>')); return false;" href="javascript:;" <?php else: ?> href="<?php echo $this->_aVars['aForms']['link']; ?>" <?php endif; ?>
<?php if (! empty ( $this->_aVars['aForms']['destination'] )): ?>style="background-image: url(<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('server_id' => $this->_aVars['aForms']['server_id'],'path' => 'photo.url_photo','file' => $this->_aVars['aForms']['destination'],'suffix' => '_500','return_url' => true)); ?>)"<?php endif; ?>>
<?php if (! empty ( $this->_aVars['aForms']['destination'] )): ?> <img src="<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('server_id' => $this->_aVars['aForms']['server_id'],'path' => 'photo.url_photo','file' => $this->_aVars['aForms']['destination'],'suffix' => '_500','return_url' => true)); ?>" alt="<?php echo $this->_aVars['aForms']['title']; ?>"> <?php endif; ?>
        </a>
        <div class="item-inner <?php if ($this->_aVars['aForms']['hasPermission']): ?>has-permission<?php endif; ?>">
            <div class="item-stats text-uppercase mb-1">
<?php if ($this->_aVars['aForms']['total_like'] > 0): ?><span class="mr-2"><?php echo Phpfox::getService('core.helper')->shortNumber($this->_aVars['aForms']['total_like']);  if ($this->_aVars['aForms']['total_like'] == 1): ?> <?php echo _p('like');  else: ?> <?php echo _p('likes');  endif; ?></span><?php endif; ?>
<?php if ($this->_aVars['aForms']['total_view'] > 0): ?><span><?php echo Phpfox::getService('core.helper')->shortNumber($this->_aVars['aForms']['total_view']);  if ($this->_aVars['aForms']['total_view'] == 1): ?> <?php echo _p('view');  else: ?> <?php echo _p('views');  endif; ?></span><?php endif; ?>
            </div>
<?php if (Phpfox ::getParam('photo.photo_show_title')): ?>
                <a class="item-title fw-bold" <?php if (! $this->_aVars['aForms']['can_view']): ?> onclick="tb_show('<?php echo _p('warning'); ?>', $.ajaxBox('photo.warning', 'height=300&width=350&link=<?php echo $this->_aVars['aForms']['link']; ?>')); return false;" href="javascript:;" <?php else: ?> href="<?php echo $this->_aVars['aForms']['link']; ?>" <?php endif; ?>>
<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['title'])); ?>
                </a>
<?php endif; ?>
<?php if (! isset ( $this->_aVars['bNotShowOwner'] ) || ! $this->_aVars['bNotShowOwner']): ?>
                <span class="item-author"><?php echo _p('posted_by'); ?> <?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aForms']['user_id'], $this->_aVars['aForms']['user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aForms']['user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aForms']['user_name'], ((empty($this->_aVars['aForms']['user_name']) && isset($this->_aVars['aForms']['profile_page_id'])) ? $this->_aVars['aForms']['profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aForms']['user_id'], $this->_aVars['aForms']['full_name'])), 0) . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aForms']['user_id']) ? '' : '</a>') . '</span>'; ?></span>
<?php endif; ?>
        </div>
        <div class="item-media-flag">
<?php if (( isset ( $this->_aVars['sView'] ) && $this->_aVars['sView'] == 'my' || isset ( $this->_aVars['bIsDetail'] ) ) && $this->_aVars['aForms']['view_id'] == 1): ?>
            <div class="sticky-label-icon sticky-pending-icon">
                <span class="flag-style-arrow"></span>
                <i class="ico ico-clock-o"></i>
            </div>
<?php endif; ?>
<?php if ($this->_aVars['aForms']['is_sponsor']): ?>
                <div class="sticky-label-icon sticky-sponsored-icon">
                    <span class="flag-style-arrow"></span>
                    <i class="ico ico-sponsor"></i>
                </div>
<?php endif; ?>
<?php if ($this->_aVars['aForms']['is_featured']): ?>
                <div class="sticky-label-icon sticky-featured-icon">
                    <span class="flag-style-arrow"></span>
                    <i class="ico ico-diamond"></i>
                </div>
<?php endif; ?>
        </div>
        
<?php if ($this->_aVars['bShowModerator']): ?>
            <div class="<?php if ($this->_aVars['bShowModerator']): ?> moderation_row<?php endif; ?>">
<?php if (! empty ( $this->_aVars['bShowModerator'] )): ?>
                   <label class="item-checkbox">
                       <input type="checkbox" class="js_global_item_moderate" name="item_moderate[]" value="<?php echo $this->_aVars['aForms']['photo_id']; ?>" id="check<?php echo $this->_aVars['aForms']['photo_id']; ?>" />
                       <i class="ico ico-square-o"></i>
                   </label>
<?php endif; ?>
            </div>
<?php endif; ?>
<?php if ($this->_aVars['aForms']['hasPermission']): ?>
            <div class="item-option photo-button-option">
                <div class="dropdown">
                    <span role="button" class="row_edit_bar_action" data-toggle="dropdown">
                        <i class="ico ico-gear-o"></i>
                    </span>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <?php
						Phpfox::getLib('template')->getBuiltFile('photo.block.menu');
						?>
                    </ul>
                </div>
            </div>
<?php endif; ?>
    </div>
</article>
