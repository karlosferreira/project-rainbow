<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php

?>

<?php if ($this->_aVars['aForms']['view_id'] == 1): ?>
    <?php
						Phpfox::getLib('template')->getBuiltFile('core.block.pending-item-action');
						 endif; ?>

<div class="photos_view dont-unbind js_photos_view" data-photo-id="<?php echo $this->_aVars['aForms']['photo_id']; ?>">
    <div class="photos_view_loader">
        <i class="fa fa-spin fa-circle-o-notch"></i>
    </div>
	<div class="image_load_holder dont-unbind" data-image-src="<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('id' => 'js_photo_view_image','server_id' => $this->_aVars['aForms']['server_id'],'path' => 'photo.url_photo','suffix' => '','file' => $this->_aVars['aForms']['destination'],'title' => $this->_aVars['aForms']['title'],'return_url' => true)); ?>" data-image-src-alt="<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('id' => 'js_photo_view_image','server_id' => $this->_aVars['aForms']['server_id'],'path' => 'photo.url_photo','suffix' => '_1024','file' => $this->_aVars['aForms']['destination'],'title' => $this->_aVars['aForms']['title'],'return_url' => true)); ?>"></div>
<?php if (PHPFOX_IS_AJAX_PAGE): ?>
	     <span id="js_back_btn" class="_a_back hide"><i class="ico ico-arrow-left"></i><?php echo _p('back'); ?></span>
<?php endif; ?>
	
    <?php echo '
        <script>
            var preLoadImages = false,
                preSetActivePhoto = false,
                photoBackBtn = document.getElementById(\'js_back_btn\');
            if (typeof checkFirstAccessToPhotoDetailByAjaxMode === "undefined" && photoBackBtn !== null) {
                photoBackBtn.classList.remove(\'hide\');
                checkFirstAccessToPhotoDetailByAjaxMode = 1;
            }
        </script>
    '; ?>

</div>
<div class="core-photos-view-action-container">
    <div class="photo_tag_in_photo js_tagged_section" style="display: none">
        <p>-- <?php echo _p('with'); ?></p> <span id="js_photo_in_this_photo" class="ml-1"></span>
    </div>
    
    <div class="photos-action-wrapper">
<?php if (( Phpfox ::getUserParam('photo.can_tag_own_photo') && $this->_aVars['aForms']['user_id'] == Phpfox ::getUserId() && Phpfox ::getUserParam('photo.how_many_tags_on_own_photo') > 0 ) || ( Phpfox ::getUserParam('photo.can_tag_other_photos') && Phpfox ::getUserParam('photo.how_many_tags_on_other_photo'))): ?>
            <div class="photos_tag">
                <a class="btn btn-default btn-sm btn-icon" href="#" id="js_tag_photo" onclick="$('.js_tagged_section').addClass('edit'); $(this).parent().addClass('active');">
                    <span><i class="ico ico-price-tags"></i></span>
                    <b class="text-capitalize"><?php echo _p("tag_photo"); ?></b>
                </a>
            </div>
<?php endif; ?>

<?php if ($this->_aVars['aForms']['hasAction']): ?>
            <div class="photos-action-more dropdown">
                <span role="button" data-toggle="dropdown" class="item_bar_action">
                    <i class="ico ico-dottedmore-vertical-o"></i>
                </span>
                <ul class="dropdown-menu dropdown-menu-right">
<?php if ($this->_aVars['aForms']['user_id'] == Phpfox ::getUserId() || ( Phpfox ::getUserParam('photo.can_download_user_photos') && $this->_aVars['aForms']['allow_download'] )): ?>
                        <li class="photos_download">
                            <a href="<?php echo Phpfox::permalink('photo', $this->_aVars['aForms']['photo_id'], $this->_aVars['aForms']['title'], false, null, (array) array (
  'action' => 'download',
)); ?>" id="js_download_photo" class="no_ajax">
                                <i class="ico ico-download-alt"></i><?php echo _p("download"); ?>
                            </a>
                        </li>
<?php endif; ?>
<?php if (Phpfox ::isUser() && Phpfox ::getUserId() == $this->_aVars['aForms']['user_id']): ?>
                        <li>
                            <a href="#" class="photo_make_as_profile" data-processing="false" onclick="$.ajaxCallOne(this, 'photo.makeProfilePicture', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>'); return false;">
                                <i class="ico ico-user-circle"></i><?php echo _p('make_profile_picture'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="photo_make_as_cover" data-processing="false" onclick="$.ajaxCallOne(this, 'photo.makeCoverPicture', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>'); return false;">
                                <i class="ico ico-photo"></i><?php echo _p('make_cover_photo'); ?>
                            </a>
                        </li>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aCallback'] ) && ( $this->_aVars['aCallback']['module_id'] == 'pages' || $this->_aVars['aCallback']['module_id'] == 'groups' ) && $this->_aVars['aForms']['canSetCover']): ?>
                        <li>
                            <a href="#" class="photo_make_as_cover" onclick="$Core.Photo.setCoverPhoto(<?php echo $this->_aVars['aForms']['photo_id']; ?>, <?php echo $this->_aVars['aCallback']['item_id']; ?>, '<?php echo $this->_aVars['aCallback']['module_id']; ?>'); return false;" >
                                <i class="ico ico-photo"></i>
<?php if (isset ( $this->_aVars['aCallback']['set_default_phrase'] )): ?>
<?php echo $this->_aVars['aCallback']['set_default_phrase']; ?>
<?php else: ?>
<?php echo _p('set_as_page_s_cover_photo'); ?>
<?php endif; ?>
                            </a>
                        </li>
<?php endif; ?>

<?php if (empty ( $this->_aVars['aForms']['noRotation'] ) && ( ( Phpfox ::getUserParam('photo.can_edit_own_photo') && $this->_aVars['aForms']['user_id'] == Phpfox ::getUserId()) || Phpfox ::getUserParam('photo.can_edit_other_photo'))): ?>
                        <li role="separator" class="divider"></li>
                        <li class="rotate-left">
                            <a href="#" onclick="$('#photo_view_ajax_loader').show(); $('#menu').remove(); $('#noteform').hide(); $('#js_photo_view_image').imgAreaSelect({ hide: true }); $('#js_photo_view_holder').hide(); $.ajaxCall('photo.rotate', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>&amp;photo_cmd=left&amp;currenturl=' + $('#js_current_page_url').html()); return false;">
                                <i class="ico ico-rotate-left"></i><?php echo _p('rotate_left'); ?>
                            </a>
                        </li>
                        <li class="rotate-right">
                            <a href="#" onclick="$('#photo_view_ajax_loader').show(); $('#menu').remove(); $('#noteform').hide(); $('#js_photo_view_image').imgAreaSelect({ hide: true }); $('#js_photo_view_holder').hide(); $.ajaxCall('photo.rotate', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>&amp;photo_cmd=right&amp;currenturl=' + $('#js_current_page_url').html()); return false;">
                                <i class="ico ico-rotate-right"></i><?php echo _p('rotate_right'); ?>
                            </a>
                        </li>
<?php endif; ?>
                </ul>
            </div>
<?php endif; ?>
    </div>
</div>
<div class="core-photos-view-title header-page-title item-title <?php if (isset ( $this->_aVars['aTitleLabel']['total_label'] ) && $this->_aVars['aTitleLabel']['total_label'] > 0): ?>header-has-label-<?php echo $this->_aVars['aTitleLabel']['total_label'];  endif; ?>">
<?php if (Phpfox ::getParam('photo.photo_show_title')): ?>
        <a href="<?php echo $this->_aVars['aForms']['link']; ?>" class="ajax_link"><?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['title'])); ?></a>
<?php endif; ?>
    <div class="photo-icon">
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
</div>
<div class="item_view">
    <div class="item_info">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aForms'],'suffix' => '_120_square')); ?>
        <div class="item_info_author">
            <div class="photo-author"><?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aForms']['user_id'], $this->_aVars['aForms']['user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aForms']['user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aForms']['user_name'], ((empty($this->_aVars['aForms']['user_name']) && isset($this->_aVars['aForms']['profile_page_id'])) ? $this->_aVars['aForms']['profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aForms']['user_id'], $this->_aVars['aForms']['full_name'])), 50, '...') . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aForms']['user_id']) ? '' : '</a>') . '</span>'; ?> <?php echo _p('on'); ?> <?php echo Phpfox::getLib('date')->convertTime($this->_aVars['aForms']['time_stamp']); ?></div>
            <div><span><?php echo number_format($this->_aVars['aForms']['total_view']); ?></span><?php if ($this->_aVars['aForms']['total_view'] == 1): ?> <?php echo _p('view_lowercase');  else: ?> <?php echo _p('views_lowercase');  endif; ?>
            </div>
        </div>
    </div>
<?php if ($this->_aVars['aForms']['hasPermission']): ?>
        <div class="item_bar">
            <div class="dropdown">
                <span role="button" data-toggle="dropdown" class="item_bar_action">
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

