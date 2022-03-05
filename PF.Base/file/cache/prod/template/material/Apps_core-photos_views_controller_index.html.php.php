<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:39 am */ ?>
<?php
    
?>

<?php if (! PHPFOX_IS_AJAX): ?>
<?php if (isset ( $this->_aVars['bSpecialMenu'] ) && $this->_aVars['bSpecialMenu'] == true): ?>
        <?php
						Phpfox::getLib('template')->getBuiltFile('photo.block.specialmenu');
						?>
<?php endif; ?>
<?php if (! isset ( $this->_aVars['bIsEditMode'] ) && count ( $this->_aVars['aPhotos'] )): ?>
        <div class="photo-mode-view-container core-photos-js<?php if (count ( $this->_aVars['aModeViews'] ) < 2): ?> hide<?php endif; ?>" id="<?php echo $this->_aVars['sView']; ?>-photos">
            <span class="photo-mode-view-btn grid" data-mode="grid" title="<?php echo _p('grid_view'); ?>"><i class="ico ico-th-large"></i></span>
            <span class="photo-mode-view-btn casual" data-mode="casual" title="<?php echo _p('casual_view'); ?>"><i class="ico ico-casual"></i></span>
        </div>
<?php endif; ?>
    <div id="js_actual_photo_content" class="photo-mode-view-content photo-view-modes-js" data-mode-views="<?php echo $this->_aVars['sModeViews']; ?>" data-mode-view="grid" data-mode-view-default="<?php echo $this->_aVars['sDefaultModeView']; ?>">
        <div id="js_album_outer_content">
<?php endif; ?>
<?php if (count ( $this->_aVars['aPhotos'] )): ?>
<?php if (isset ( $this->_aVars['bIsEditMode'] )): ?>
<?php if (! PHPFOX_IS_AJAX): ?>
                        <form class="form photo-app-manage" id="js_form_mass_edit_photo" method="post" action="#" onsubmit="$('#js_photo_multi_edit_image').show(); $('#js_photo_multi_edit_submit').hide(); $(this).ajaxCall('photo.massUpdate'<?php if ($this->_aVars['bIsMassEditUpload']): ?>, 'is_photo_upload=1<?php endif;  if ($this->_aVars['bIsMassEdit']): ?>&mass_edit=1<?php endif; ?>'); return false;">
                        <div class="clearfix item-photo-edit">
<?php endif; ?>
<?php if (count((array)$this->_aVars['aPhotos'])):  foreach ((array) $this->_aVars['aPhotos'] as $this->_aVars['aForms']): ?>
                                <?php
						Phpfox::getLib('template')->getBuiltFile('photo.block.edit-photo');
						?>
<?php endforeach; endif; ?>
<?php if ($this->_aVars['photoPagingMode'] == 'loadmore'): ?>
<?php if (!isset($this->_aVars['aPager'])): Phpfox::getLib('pager')->set(array('page' => Phpfox::getLib('request')->getInt('page'), 'size' => Phpfox::getLib('search')->getDisplay(), 'count' => Phpfox::getLib('search')->getCount())); endif;  $this->getLayout('pager'); ?>
<?php endif; ?>
<?php if (! PHPFOX_IS_AJAX): ?>
                        </div>
                        <div class="photo_table_clear">
                            <div id="js_photo_multi_edit_image" style="display:none;">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => 'ajax/add.gif')); ?>
                            </div>
                            <div id="js_photo_multi_edit_submit" class="pull-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ico ico-check-circle-alt hide mr-1"></i>
<?php echo _p('update_photo_s'); ?>
                                </button>
                            </div>
                        </div>
                    
</form>

<?php endif; ?>
<?php else: ?>
<?php if (! PHPFOX_IS_AJAX): ?>
                        <div class="item-container photo-listing photo-init-pinto-js clearfix" id="photo_collection">
<?php endif; ?>
<?php if (count((array)$this->_aVars['aPhotos'])):  foreach ((array) $this->_aVars['aPhotos'] as $this->_aVars['aForms']): ?>
                            <?php
						Phpfox::getLib('template')->getBuiltFile('photo.block.photo_entry');
						?>
<?php endforeach; endif; ?>
<?php if ($this->_aVars['photoPagingMode'] == 'loadmore'): ?>
<?php if (!isset($this->_aVars['aPager'])): Phpfox::getLib('pager')->set(array('page' => Phpfox::getLib('request')->getInt('page'), 'size' => Phpfox::getLib('search')->getDisplay(), 'count' => Phpfox::getLib('search')->getCount())); endif;  $this->getLayout('pager'); ?>
<?php endif; ?>
<?php if (! PHPFOX_IS_AJAX): ?>
                        </div>
<?php endif; ?>
<?php endif; ?>
<?php if ($this->_aVars['photoPagingMode'] != 'loadmore'): ?>
<?php if (!isset($this->_aVars['aPager'])): Phpfox::getLib('pager')->set(array('page' => Phpfox::getLib('request')->getInt('page'), 'size' => Phpfox::getLib('search')->getDisplay(), 'count' => Phpfox::getLib('search')->getCount())); endif;  $this->getLayout('pager'); ?>
<?php endif; ?>
<?php if ($this->_aVars['bShowModerator']): ?>
<?php Phpfox::getBlock('core.moderation'); ?>
<?php endif; ?>
<?php else: ?>
<?php if (! PHPFOX_IS_AJAX): ?>
                    <div class="extra_info">
<?php echo _p('no_photos_found'); ?>
                    </div>
<?php endif; ?>
<?php endif; ?>
<?php if (! PHPFOX_IS_AJAX): ?>
        </div>
    </div>
<?php endif; ?>
