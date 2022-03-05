<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php

?>

<?php if (! isset ( $this->_aVars['sHidden'] )):  $this->assign('sHidden', '');  endif; ?>

<?php if (( isset ( $this->_aVars['sHeader'] ) && ( ! PHPFOX_IS_AJAX || isset ( $this->_aVars['bPassOverAjaxCall'] ) || isset ( $this->_aVars['bIsAjaxLoader'] ) ) ) || ( defined ( "PHPFOX_IN_DESIGN_MODE" ) && PHPFOX_IN_DESIGN_MODE )): ?>

<div class="<?php echo $this->_aVars['sHidden']; ?> block<?php if (( defined ( 'PHPFOX_IN_DESIGN_MODE' ) ) && ( ! isset ( $this->_aVars['bCanMove'] ) || ( isset ( $this->_aVars['bCanMove'] ) && $this->_aVars['bCanMove'] == true ) )): ?> js_sortable<?php endif;  if (isset ( $this->_aVars['sCustomClassName'] )): ?> <?php echo $this->_aVars['sCustomClassName'];  endif; ?>"<?php if (isset ( $this->_aVars['sBlockBorderJsId'] )): ?> id="js_block_border_<?php echo $this->_aVars['sBlockBorderJsId']; ?>"<?php endif;  if (defined ( 'PHPFOX_IN_DESIGN_MODE' ) && Phpfox_Module ::instance()->blockIsHidden('js_block_border_' . $this->_aVars['sBlockBorderJsId'] . '' )): ?> style="display:none;"<?php endif; ?> data-toggle="<?php echo $this->_aVars['sToggleWidth']; ?>">
<?php if (! empty ( $this->_aVars['sHeader'] ) || ( defined ( "PHPFOX_IN_DESIGN_MODE" ) && PHPFOX_IN_DESIGN_MODE )): ?>
		<div class="title <?php if (defined ( 'PHPFOX_IN_DESIGN_MODE' )): ?>js_sortable_header<?php endif; ?>">
<?php if (isset ( $this->_aVars['sBlockTitleBar'] )): ?>
<?php echo $this->_aVars['sBlockTitleBar']; ?>
<?php endif; ?>
<?php if (( isset ( $this->_aVars['aEditBar'] ) && Phpfox ::isUser())): ?>
			<div class="js_edit_header_bar">
				<a href="#" title="<?php echo _p('edit_this_block'); ?>" onclick="$.ajaxCall('<?php echo $this->_aVars['aEditBar']['ajax_call']; ?>', 'block_id=<?php echo $this->_aVars['sBlockBorderJsId'];  if (isset ( $this->_aVars['aEditBar']['params'] )):  echo $this->_aVars['aEditBar']['params'];  endif; ?>'); return false;">
					<span class="ico ico-pencilline-o"></span>
				</a>
			</div>
<?php endif; ?>
<?php if (empty ( $this->_aVars['sHeader'] )): ?>
<?php echo $this->_aVars['sBlockShowName']; ?>
<?php else: ?>
<?php echo $this->_aVars['sHeader']; ?>
<?php endif; ?>
		</div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aEditBar'] )): ?>
	<div id="js_edit_block_<?php echo $this->_aVars['sBlockBorderJsId']; ?>" class="edit_bar hidden"></div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aMenu'] ) && count ( $this->_aVars['aMenu'] )): ?>
<?php unset($this->_aVars['aMenu']); ?>
<?php endif; ?>
	<div class="content"<?php if (isset ( $this->_aVars['sBlockJsId'] )): ?> id="js_block_content_<?php echo $this->_aVars['sBlockJsId']; ?>"<?php endif; ?>>
<?php endif; ?>
		<?php



?>
<div class="pf-front-site-statistics">
<?php if ($this->_aVars['bShowOnlineMember']): ?>
        <div class="online-members">
            <span class="ico ico-user-man"></span>
            <div class="online-members-text">
                <div id="online-members-value"><?php echo Phpfox::getService('core.helper')->shortNumber($this->_aVars['iTotalOnlineMember']); ?></div>
                <span class="member-label"><?php echo _p('online_user_s'); ?></span>
            </div>
        </div>
<?php endif; ?>
<?php if ($this->_aVars['bShowTodayStats'] && $this->_aVars['bShowAllTimeStats'] && ( ! empty ( $this->_aVars['aTodayStats'] ) || ! empty ( $this->_aVars['aAllTimeStats'] ) )): ?>
        <div class="page_section_menu" <?php if (! $this->_aVars['bShowOnlineMember']): ?>style="border-top:0;"<?php endif; ?>>
            <ul class="nav nav-tabs">
<?php if (! empty ( $this->_aVars['aTodayStats'] )): ?>
                    <li class="active">
                        <a data-cmd="core.tab_item" href="#fe_today_stats"><?php echo _p('today_normal'); ?></a>
                    </li>
<?php endif; ?>
                <li <?php if (empty ( $this->_aVars['aTodayStats'] )): ?>class="active"<?php endif; ?>>
                    <a data-cmd="core.tab_item" href="#fe_all_time_stats"><?php echo _p('all_time_site_stats'); ?></a>
                </li>
            </ul>
        </div>
        <div class="tab-content clearfix">
<?php if (! empty ( $this->_aVars['aTodayStats'] )): ?>
                <div class="tab-pane active" id="fe_today_stats">
<?php if (count((array)$this->_aVars['aTodayStats'])):  foreach ((array) $this->_aVars['aTodayStats'] as $this->_aVars['aStat']): ?>
                    <div class="stat-info">
                        <div class="stat-label">
<?php echo $this->_aVars['aStat']['phrase']; ?>:
                        </div>
                        <div class="stat-value">
<?php if (isset ( $this->_aVars['aStat']['link'] )): ?><a href="<?php echo $this->_aVars['aStat']['link']; ?>"><?php endif;  echo Phpfox::getService('core.helper')->shortNumber($this->_aVars['aStat']['value']);  if (isset ( $this->_aVars['aStat']['link'] )): ?></a><?php endif; ?>
                        </div>
                    </div>
<?php endforeach; endif; ?>
                </div>
<?php endif; ?>
            <div class="tab-pane <?php if (empty ( $this->_aVars['aTodayStats'] )): ?>active<?php endif; ?>" id="fe_all_time_stats">
<?php if (count((array)$this->_aVars['aAllTimeStats'])):  foreach ((array) $this->_aVars['aAllTimeStats'] as $this->_aVars['aStat']): ?>
                    <div class="stat-info">
                        <div class="stat-label">
<?php echo $this->_aVars['aStat']['phrase']; ?>:
                        </div>
                        <div class="stat-value">
<?php if (isset ( $this->_aVars['aStat']['link'] )): ?><a href="<?php echo $this->_aVars['aStat']['link']; ?>"><?php endif;  echo Phpfox::getService('core.helper')->shortNumber($this->_aVars['aStat']['value']);  if (isset ( $this->_aVars['aStat']['link'] )): ?></a><?php endif; ?>
                        </div>
                    </div>
<?php endforeach; endif; ?>
            </div>
        </div>
<?php elseif ($this->_aVars['bShowTodayStats']): ?>
        <div class="page_section_menu" <?php if (! $this->_aVars['bShowOnlineMember']): ?>style="border-top:0;"<?php endif; ?>>
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-cmd="core.tab_item" href="#fe_today_stats"><?php echo _p('today_normal'); ?></a>
                </li>

            </ul>
        </div>
<?php if (count((array)$this->_aVars['aTodayStats'])):  foreach ((array) $this->_aVars['aTodayStats'] as $this->_aVars['aStat']): ?>
            <div class="stat-info">
                <div class="stat-label">
<?php echo $this->_aVars['aStat']['phrase']; ?>:
                </div>
                <div class="stat-value">
<?php if (isset ( $this->_aVars['aStat']['link'] )): ?><a href="<?php echo $this->_aVars['aStat']['link']; ?>"><?php endif;  echo Phpfox::getService('core.helper')->shortNumber($this->_aVars['aStat']['value']);  if (isset ( $this->_aVars['aStat']['link'] )): ?></a><?php endif; ?>
                </div>
            </div>
<?php endforeach; endif; ?>
<?php elseif ($this->_aVars['bShowAllTimeStats']): ?>
        <div class="page_section_menu" <?php if (! $this->_aVars['bShowOnlineMember']): ?>style="border-top:0;"<?php endif; ?>>
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-cmd="core.tab_item" href="#fe_all_time_stats"><?php echo _p('all_time_site_stats'); ?></a>
                </li>

            </ul>
        </div>
<?php if (count((array)$this->_aVars['aAllTimeStats'])):  foreach ((array) $this->_aVars['aAllTimeStats'] as $this->_aVars['aStat']): ?>
            <div class="stat-info">
                <div class="stat-label">
<?php echo $this->_aVars['aStat']['phrase']; ?>:
                </div>
                <div class="stat-value">
<?php if (isset ( $this->_aVars['aStat']['link'] )): ?><a href="<?php echo $this->_aVars['aStat']['link']; ?>"><?php endif;  echo Phpfox::getService('core.helper')->shortNumber($this->_aVars['aStat']['value']);  if (isset ( $this->_aVars['aStat']['link'] )): ?></a><?php endif; ?>
                </div>
            </div>
<?php endforeach; endif; ?>
<?php endif; ?>
</div>




<?php if (( isset ( $this->_aVars['sHeader'] ) && ( ! PHPFOX_IS_AJAX || isset ( $this->_aVars['bPassOverAjaxCall'] ) || isset ( $this->_aVars['bIsAjaxLoader'] ) ) ) || ( defined ( "PHPFOX_IN_DESIGN_MODE" ) && PHPFOX_IN_DESIGN_MODE )): ?>
	</div>
<?php if (isset ( $this->_aVars['aFooter'] ) && count ( $this->_aVars['aFooter'] )): ?>
	<div class="bottom">
<?php if (count ( $this->_aVars['aFooter'] ) == 1): ?>
<?php if (count((array)$this->_aVars['aFooter'])):  $this->_aPhpfoxVars['iteration']['block'] = 0;  foreach ((array) $this->_aVars['aFooter'] as $this->_aVars['sPhrase'] => $this->_aVars['sLink']):  $this->_aPhpfoxVars['iteration']['block']++; ?>

<?php if ($this->_aVars['sLink'] == '#'): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => 'ajax/add.gif','class' => 'ajax_image')); ?>
<?php endif; ?>
<?php if (is_array ( $this->_aVars['sLink'] )): ?>
            <a class="btn btn-block <?php if (! empty ( $this->_aVars['sLink']['class'] )): ?> <?php echo $this->_aVars['sLink']['class'];  endif; ?>" href="<?php if (! empty ( $this->_aVars['sLink']['link'] )):  echo $this->_aVars['sLink']['link'];  else: ?>#<?php endif; ?>" <?php if (! empty ( $this->_aVars['sLink']['attr'] )):  echo $this->_aVars['sLink']['attr'];  endif; ?> id="js_block_bottom_link_<?php echo $this->_aPhpfoxVars['iteration']['block']; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
<?php else: ?>
            <a class="btn btn-block" href="<?php echo $this->_aVars['sLink']; ?>" id="js_block_bottom_link_<?php echo $this->_aPhpfoxVars['iteration']['block']; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
<?php endif; ?>
<?php endforeach; endif; ?>
<?php else: ?>
		<ul>
<?php if (count((array)$this->_aVars['aFooter'])):  $this->_aPhpfoxVars['iteration']['block'] = 0;  foreach ((array) $this->_aVars['aFooter'] as $this->_aVars['sPhrase'] => $this->_aVars['sLink']):  $this->_aPhpfoxVars['iteration']['block']++; ?>

				<li id="js_block_bottom_<?php echo $this->_aPhpfoxVars['iteration']['block']; ?>"<?php if ($this->_aPhpfoxVars['iteration']['block'] == 1): ?> class="first"<?php endif; ?>>
<?php if ($this->_aVars['sLink'] == '#'): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => 'ajax/add.gif','class' => 'ajax_image')); ?>
<?php endif; ?>
					<a href="<?php echo $this->_aVars['sLink']; ?>" id="js_block_bottom_link_<?php echo $this->_aPhpfoxVars['iteration']['block']; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
				</li>
<?php endforeach; endif; ?>
		</ul>
<?php endif; ?>
	</div>
<?php endif; ?>
</div>
<?php endif;  unset($this->_aVars['sHeader'], $this->_aVars['sComponent'], $this->_aVars['aFooter'], $this->_aVars['sBlockBorderJsId'], $this->_aVars['bBlockDisableSort'], $this->_aVars['bBlockCanMove'], $this->_aVars['aEditBar'], $this->_aVars['sDeleteBlock'], $this->_aVars['sBlockTitleBar'], $this->_aVars['sBlockJsId'], $this->_aVars['sCustomClassName'], $this->_aVars['aMenu']); ?>
