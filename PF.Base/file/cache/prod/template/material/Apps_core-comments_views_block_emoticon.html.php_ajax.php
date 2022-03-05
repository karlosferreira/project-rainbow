<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 2:19 am */ ?>
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

<div class="comment-emoji-container js_comment_emoticon_container js_emoticon_container_<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>[FEED-ID]_[PARENT-ID]_[EDIT-ID]<?php else:  echo $this->_aVars['iFeedId']; ?>_<?php echo $this->_aVars['iParentId']; ?>_<?php echo $this->_aVars['iEditId'];  endif; ?>">
    <ul class="nav comment-emoji-header">
<?php if (count ( $this->_aVars['aRecentEmoticons'] )): ?>
            <li class="active"><a href="#3a_<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>[FEED-ID]_[PARENT-ID]_[EDIT-ID]<?php else:  echo $this->_aVars['iFeedId']; ?>_<?php echo $this->_aVars['iParentId']; ?>_<?php echo $this->_aVars['iEditId'];  endif; ?>" data-toggle="tab"><?php echo _p('recent'); ?></a></li>
<?php endif; ?>
        <li <?php if (! count ( $this->_aVars['aRecentEmoticons'] )): ?>class="active"<?php endif; ?>><a href="#4a_<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>[FEED-ID]_[PARENT-ID]_[EDIT-ID]<?php else:  echo $this->_aVars['iFeedId']; ?>_<?php echo $this->_aVars['iParentId']; ?>_<?php echo $this->_aVars['iEditId'];  endif; ?>" data-toggle="tab"><?php echo _p('all'); ?></a></li>
        <span class="item-hover-info js_hover_emoticon_info"></span>
        <a class="item-close" onclick="$Core.Comment.hideEmoticon($(this),<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>'[EMOJI-IS-REPLY]'<?php else:  echo $this->_aVars['bIsReply'];  endif; ?>);return false;"><span class="ico ico-close"></span></a>
    </ul>
    <div class="tab-content comment-emoji-content">
<?php if (count ( $this->_aVars['aRecentEmoticons'] )): ?>
            <div class="tab-pane active" id="3a_<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>[FEED-ID]_[PARENT-ID]_[EDIT-ID]<?php else:  echo $this->_aVars['iFeedId']; ?>_<?php echo $this->_aVars['iParentId']; ?>_<?php echo $this->_aVars['iEditId'];  endif; ?>">
                <div class="<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>[COMMENT-EMOTICON-LIST-CLASS]<?php else: ?>comment-emoji-list<?php endif; ?>">
                    <div class="item-container">
<?php if (count((array)$this->_aVars['aRecentEmoticons'])):  foreach ((array) $this->_aVars['aRecentEmoticons'] as $this->_aVars['aRecent']): ?>
                            <div class="item-emoji" onmouseover="$Core.Comment.showEmojiTitle($(this), '<?php echo $this->_aVars['aRecent']['code']; ?>')"
                                 onclick="return $Core.Comment.selectEmoji($(this), '<?php echo $this->_aVars['aRecent']['code']; ?>', <?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>'[EMOJI-IS-REPLY]'<?php else:  echo $this->_aVars['bIsReply'];  endif; ?>, <?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>'[EMOJI-IS-EDIT]'<?php else:  echo $this->_aVars['bIsEdit'];  endif; ?>);" title="<?php echo _p($this->_aVars['aRecent']['title']); ?> <?php echo $this->_aVars['aRecent']['code']; ?>">
                                <div class="item-outer">
                                    <img src="<?php echo Phpfox::getParam('core.path_actual'); ?>PF.Site/Apps/core-comments/assets/images/emoticons/<?php echo $this->_aVars['aRecent']['image']; ?>"
                                         border="0"
                                         data-code="<?php echo $this->_aVars['aRecent']['code']; ?>"
                                         alt="<?php echo $this->_aVars['aRecent']['image']; ?>">
                                </div>
                            </div>
<?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
<?php endif; ?>
        <div class="tab-pane <?php if (! count ( $this->_aVars['aRecentEmoticons'] )): ?>active<?php endif; ?>" id="4a_<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>[FEED-ID]_[PARENT-ID]_[EDIT-ID]<?php else:  echo $this->_aVars['iFeedId']; ?>_<?php echo $this->_aVars['iParentId']; ?>_<?php echo $this->_aVars['iEditId'];  endif; ?>">
            <div class="<?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>[COMMENT-EMOTICON-LIST-CLASS]<?php else: ?>comment-emoji-list<?php endif; ?>">
                <div class="item-container">
<?php if (count((array)$this->_aVars['aEmoticons'])):  foreach ((array) $this->_aVars['aEmoticons'] as $this->_aVars['aEmoji']): ?>
                        <div class="item-emoji" onmouseover="$Core.Comment.showEmojiTitle($(this), '<?php echo $this->_aVars['aEmoji']['code']; ?>')"
                             onclick="return $Core.Comment.selectEmoji($(this), '<?php echo $this->_aVars['aEmoji']['code']; ?>', <?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>'[EMOJI-IS-REPLY]'<?php else:  echo $this->_aVars['bIsReply'];  endif; ?>, <?php if (! empty ( $this->_aVars['bIsGlobal'] )): ?>'[EMOJI-IS-EDIT]'<?php else:  echo $this->_aVars['bIsEdit'];  endif; ?>);" title="<?php echo _p($this->_aVars['aEmoji']['title']); ?> <?php echo $this->_aVars['aEmoji']['code']; ?>">
                            <div class="item-outer">
                                <img src="<?php echo Phpfox::getParam('core.path_actual'); ?>PF.Site/Apps/core-comments/assets/images/emoticons/<?php echo $this->_aVars['aEmoji']['image']; ?>"
                                     border="0"
                                     data-code="<?php echo $this->_aVars['aEmoji']['code']; ?>"
                                     alt="<?php echo $this->_aVars['aEmoji']['image']; ?>">
                            </div>
                        </div>
<?php endforeach; endif; ?>
                </div>
            </div>
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
