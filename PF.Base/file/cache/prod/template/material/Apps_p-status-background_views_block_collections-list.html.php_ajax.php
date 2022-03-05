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
<div id="js_p_statusbg_collection_list" style="display: none;">
    <input type="hidden" name="val[status_background_id]" id="js_p_statusbg_background_id" value="0">
	<div class="p-statusbg-collection-container">
	    <ul class="nav p-statusbg-collection-header" <?php if ($this->_aVars['iTotalCollection'] == 1): ?>style="display:none"<?php endif; ?>>
<?php if (count((array)$this->_aVars['aCollections'])):  foreach ((array) $this->_aVars['aCollections'] as $this->_aVars['aCollection']): ?>
			    <li class="<?php if (! $this->_aVars['aCollection']['is_default'] || $this->_aVars['iTotalCollection'] == 1): ?>active<?php endif; ?> js_switch_collection_li "><a class="item-collection js_switch_collection" data-toggle="tab" href="#collection_<?php echo $this->_aVars['aCollection']['collection_id']; ?>"><?php echo _p($this->_aVars['aCollection']['title']); ?></a></li>
<?php endforeach; endif; ?>
            <div class="collection-header-nav-button js_p_statusbg_header_nav" style="display: none">
            	<div class="item-prev disabled" ><span class="ico ico-angle-left"></span></div>
            	<div class="item-next" ><span class="ico ico-angle-right"></span></div>
            </div>
	    </ul>
	     <div class="tab-content p-statusbg-collection-content">
<?php if (count((array)$this->_aVars['aCollections'])):  foreach ((array) $this->_aVars['aCollections'] as $this->_aVars['aCollection']): ?>
                 <div class="tab-pane<?php if (! $this->_aVars['aCollection']['is_default'] || $this->_aVars['iTotalCollection'] == 1): ?> active<?php endif; ?>" id="collection_<?php echo $this->_aVars['aCollection']['collection_id']; ?>">
                     <div class="p-statusbg-collection-listing">
                         <div class="collection-item<?php if (! $this->_aVars['aCollection']['is_default'] || $this->_aVars['iTotalCollection'] == 1): ?> active<?php endif; ?>" data-background_id="0" data-image_url="" onclick="PStatusBg.selectBackground(this);">
                             <div class="item-outer">
                                 <span class="item-bg" style="background-color:#fff"></span>
                             </div>
                         </div>
<?php if (count((array)$this->_aVars['aCollection']['backgrounds_list'])):  foreach ((array) $this->_aVars['aCollection']['backgrounds_list'] as $this->_aVars['iKey'] => $this->_aVars['aBackground']): ?>
                             <div class="collection-item <?php if ($this->_aVars['iKey'] > 13): ?>hide js_bg_hide_<?php echo $this->_aVars['aCollection']['collection_id'];  endif; ?>" data-background_id="<?php echo $this->_aVars['aBackground']['background_id']; ?>" data-image_url="<?php echo $this->_aVars['aBackground']['full_path']; ?>" onclick="PStatusBg.selectBackground(this);">
                                 <div class="item-outer">
                                     <span class="item-bg" style="background-image: url(<?php echo $this->_aVars['aBackground']['full_path']; ?>)"></span>
                                 </div>
                             </div>
<?php endforeach; endif; ?>
<?php if ($this->_aVars['iKey'] > 13): ?>
                             <div class="collection-item js_bg_show_full" data-collection_id="<?php echo $this->_aVars['aCollection']['collection_id']; ?>" onclick="PStatusBg.showFullCollection(this); return false;">
                                 <div class="item-outer">
                                     <div class="item-bg-more"><span class="ico ico-dottedmore-o"></span></div>
                                 </div>
                             </div>
<?php endif; ?>
                     </div>
                 </div>
<?php endforeach; endif; ?>
	     </div>
	 </div>
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
