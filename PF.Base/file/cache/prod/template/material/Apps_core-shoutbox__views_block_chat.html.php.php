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
<div class="hidden" id="current_user_avatar">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aUser'],'suffix' => '_120_square','class' => "img-responsive",'title' => $this->_aVars['aUser']['full_name'])); ?>
</div>
<div class="panel-body msg_container_base shoutbox-container " id="msg_container_base" data-error-quote-message="<?php echo _p('shoutbox_you_only_quote_once'); ?>">
<?php if (count((array)$this->_aVars['aShoutboxes'])):  foreach ((array) $this->_aVars['aShoutboxes'] as $this->_aVars['sKey'] => $this->_aVars['aShoutbox']): ?>
        <div class="row msg_container <?php if ($this->_aVars['aShoutbox']['type'] == 's'): ?> base_sent <?php else: ?> base_receive <?php endif; ?>" id="shoutbox_message_<?php echo $this->_aVars['aShoutbox']['shoutbox_id']; ?>" data-value="<?php echo $this->_aVars['aShoutbox']['shoutbox_id']; ?>">
            <div class="msg_container_row shoutbox-item <?php if ($this->_aVars['aShoutbox']['type'] == 's'): ?> item-sent <?php else: ?> item-receive <?php endif; ?>">
                <div class="shoutbox_action">
<?php if ($this->_aVars['aShoutbox']['canEdit'] || ( $this->_aVars['aShoutbox']['canDeleteOwn'] || $this->_aVars['aShoutbox']['canDeleteAll'] ) || Phpfox ::isUser()): ?>
<?php if (Phpfox ::isUser()): ?>
                            <div class="shoutbox-like">
                                <a class="btn-shoutbox-like js_shoutbox_like <?php if ($this->_aVars['aShoutbox']['is_liked']): ?>liked<?php else: ?>unlike<?php endif; ?>" title="<?php if ($this->_aVars['aShoutbox']['is_liked']):  echo _p('unlike');  else:  echo _p('like');  endif; ?>" data-type="<?php if ($this->_aVars['aShoutbox']['is_liked']): ?>unlike<?php else: ?>like<?php endif; ?>" data-id="<?php echo $this->_aVars['aShoutbox']['shoutbox_id']; ?>" onclick="appShoutbox.processLike(this);"></a>
                            </div>
<?php endif; ?>
<?php if ($this->_aVars['bCanShare'] || $this->_aVars['aShoutbox']['canEdit'] || $this->_aVars['aShoutbox']['canDeleteOwn'] || $this->_aVars['aShoutbox']['canDeleteAll']): ?>
                            <div class="dropdown item-action-more js-shoutbox-action-more dont-unbind">
                                <a role="button" data-toggle="dropdown" href="#" class="" aria-expanded="true">
                                    <span class="ico ico-dottedmore"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right dont-unbind">
<?php if ($this->_aVars['bCanShare']): ?>
                                        <li>
                                            <a href="javascript:void();" onclick="appShoutbox.quote(this);" data-value="<?php echo $this->_aVars['aShoutbox']['shoutbox_id']; ?>" title="<?php echo _p('quote'); ?>"><i class="ico ico-quote-circle-alt-left-o" aria-hidden="true"></i> <?php echo _p('quote'); ?></a>
                                        </li>
<?php endif; ?>
<?php if ($this->_aVars['aShoutbox']['canEdit']): ?>
                                        <li>
                                            <a href="javascript:void();" onclick="appShoutbox.openEditPopup(this);" data-phrase="<?php echo _p('shoutbox_edit_message'); ?>" data-value="<?php echo $this->_aVars['aShoutbox']['shoutbox_id']; ?>" title="<?php echo _p('edit'); ?>"><i class="ico ico-pencil" aria-hidden="true"></i> <?php echo _p('edit'); ?></a>
                                        </li>
<?php endif; ?>
<?php if ($this->_aVars['aShoutbox']['canDeleteOwn'] || $this->_aVars['aShoutbox']['canDeleteAll']): ?>
                                        <li>
                                            <a href="javascript:void();"  onclick="appShoutbox.dismiss(this);" data-value="<?php echo $this->_aVars['aShoutbox']['shoutbox_id']; ?>" title="<?php echo _p('delete'); ?>"><i class="ico ico-trash-o" aria-hidden="true"></i> <?php echo _p('delete'); ?></a>
                                        </li>
<?php endif; ?>
                                </ul>
                            </div>
<?php endif; ?>
<?php endif; ?>
                </div>
                <div class="item-outer <?php if ($this->_aVars['aIsAdmin'] || $this->_aVars['iUserId'] == $this->_aVars['aShoutbox']['user_id']): ?>can-delete<?php endif; ?>">
                    <div class="item-media-source">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aShoutbox'],'suffix' => '_120_square','width' => 32,'height' => 32,'class' => "img-responsive",'title' => $this->_aVars['aShoutbox']['full_name'])); ?>
                    </div>
                    <div class="item-inner">
                        <div class="title_avatar item-shoutbox-body <?php if ($this->_aVars['aShoutbox']['type'] == 'r'): ?> msg_body_receive <?php elseif ($this->_aVars['aShoutbox']['type'] == 's'): ?> msg_body_sent <?php endif; ?> " title="<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aShoutbox']['full_name'])); ?>">
                            <div class=" item-title">
                                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aShoutbox']['user_name'], [], false, false); ?>" title="<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aShoutbox']['full_name'])); ?>">
<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aShoutbox']['full_name'])); ?>
                                </a>
                            </div>
                            <div class="messages_body item-message">
                                <div class="item-message-info item_view_content">
<?php if (isset ( $this->_aVars['aShoutbox']['quoted_text'] )): ?>
                                        <div class="item-quote-content">
                                            <div class="quote-user"><?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aShoutbox']['quoted_full_name'])); ?></div>
                                            <div class="quote-message"><?php echo Phpfox::getLib('phpfox.parse.output')->parse($this->_aVars['aShoutbox']['quoted_text']); ?></div>
                                        </div>
<?php endif; ?>
<?php echo Phpfox::getLib('phpfox.parse.output')->parse($this->_aVars['aShoutbox']['text']); ?>
                                </div>
                            </div>
                            
                        </div>
                        <span class="js_shoutbox_text_total_like item-count-like"><?php if (( int ) $this->_aVars['aShoutbox']['total_like'] > 0): ?><a href="javascript:void(0);" onclick="appShoutbox.showLikedMembers(<?php echo $this->_aVars['aShoutbox']['shoutbox_id']; ?>);"><?php echo $this->_aVars['aShoutbox']['total_like']; ?> <?php if (( int ) $this->_aVars['aShoutbox']['total_like'] > 1):  echo _p('likes');  else:  echo _p('like');  endif;  endif; ?></a></span>
                         <div class="item-time">
                            <span class="message_convert_time" data-id="<?php echo $this->_aVars['aShoutbox']['timestamp']; ?>"><?php echo Phpfox::getLib('date')->convertTime($this->_aVars['aShoutbox']['timestamp']); ?></span><span class="item-edit-info js_edited_text <?php if (! $this->_aVars['aShoutbox']['is_edited']): ?>hide<?php endif; ?>"><?php if ($this->_aVars['aShoutbox']['is_edited']):  echo _p('shoutbox_edited');  endif; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php endforeach; endif; ?>
</div>
<?php if ($this->_aVars['bCanShare']): ?>
    <div class="panel-footer">
        <form onsubmit="return false;">
            <div class="input-group">
                <textarea rows='1' data-toggle="shoutbox" data-name="text" maxlength="255" id="shoutbox_text_message_field" type="text" class="form-control chat_input" placeholder="<?php echo _p('write_message'); ?>"/></textarea>
            </div>
            <div class="item-footer-sent">
                <div class="item-count"><span id="pf_shoutbox_text_counter">0</span>/255</div>
                <span class="item-btn-sent">
                <ul class="global_attachment_list" data-id="shoutbox_text_message_field"></ul>
                <button data-name="shoutbox-submit" class="btn btn-primary btn-xs" id="btn-chat"><i class="ico ico-paperplane" aria-hidden="true"></i></button>
            </span>
            </div>
        
</form>

    </div>
<?php endif; ?>
<input type="hidden" value="<?php echo $this->_aVars['sModuleId']; ?>" data-toggle="shoutbox" data-name="parent_module_id">
<input type="hidden" value="<?php echo $this->_aVars['iItemId']; ?>" data-toggle="shoutbox" data-name="parent_item_id">
<div id="shoutbox_error_notice" data-title="<?php echo _p('notice'); ?>" data-message="<?php echo _p('type_something_to_chat'); ?>"></div>




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
