<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php


?>

<?php if (defined ( 'PHPFOX_IS_USER_PROFILE' ) && Phpfox ::isUser() && Phpfox ::getUserId() == $this->_aVars['aForms']['user_id']): ?>
    <li>
        <a href="#" class="photo_make_as_cover" data-processing="false" onclick="$.ajaxCallOne(this, 'photo.makeCoverPicture', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>'); return false;">
            <i class="ico ico-photo"></i><?php echo _p('make_cover_photo'); ?>
        </a>
    </li>
    <li>
        <a href="#" class="photo_make_as_profile" data-processing="false" onclick="$.ajaxCallOne(this, 'photo.makeProfilePicture', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>'); return false;">
            <i class="ico ico-user-circle"></i><?php echo _p('make_profile_picture'); ?>
        </a>
    </li>
    <li role="separator" class="divider"></li>
<?php endif; ?>

<?php if (isset ( $this->_aVars['aParentModule'] ) && defined ( 'PHPFOX_IS_PAGES_VIEW' ) && $this->_aVars['aForms']['canSetCover']): ?>
    <li>
        <a href="#" class="photo_make_as_cover" onclick="$Core.Photo.setCoverPhoto(<?php echo $this->_aVars['aForms']['photo_id']; ?>, <?php echo $this->_aVars['aParentModule']['item_id']; ?>, '<?php echo $this->_aVars['aParentModule']['module_id']; ?>'); return false;" >
            <i class="ico ico-photo"></i><?php if ($this->_aVars['aParentModule']['module_id'] == 'groups'):  echo _p('set_as_group_s_cover_photo');  else:  echo _p('set_as_page_s_cover_photo');  endif; ?>
        </a>
    </li>
    <li role="separator" class="divider"></li>
<?php endif; ?>

<?php if ($this->_aVars['aForms']['canApprove']): ?>
    <li><a href="#" onclick="$(this).hide(); $('#js_item_bar_approve_image').show(); $.ajaxCall('photo.approve', 'id=<?php echo $this->_aVars['aForms']['photo_id'];  if (! isset ( $this->_aVars['bIsDetail'] )): ?>&amp;inline=true<?php endif; ?>'); return false;" title="<?php echo _p('approve'); ?>"><i class="ico ico-check-square-alt mr-1"></i><?php echo _p('approve'); ?></a></li>
<?php endif; ?>

<?php if ($this->_aVars['aForms']['canEdit']): ?>
    <li><a href="#" onclick="if ($Core.exists('.js_box_image_holder_full')) { js_box_remove($('.js_box_image_holder_full').find('.js_box_content')); } $Core.box('photo.editPhoto', 700, 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>'); $('#js_tag_photo').hide();return false;"><i class="ico ico-pencilline-o mr-1"></i><?php echo _p('edit_this_photo'); ?></a></li>
<?php endif; ?>

<?php if ($this->_aVars['aForms']['canSponsorInFeed']): ?>
    <li>
<?php if ($this->_aVars['aForms']['iSponsorInFeedId']): ?>
            <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('ad.sponsor', array('where' => 'feed','section' => 'photo','item' => $this->_aVars['aForms']['photo_id']), false, false); ?>">
                <i class="ico ico-sponsor mr-1"></i><?php echo _p('sponsor_in_feed'); ?>
            </a>
<?php else: ?>
            <a href="#" onclick="$.ajaxCall('ad.removeSponsor', 'type_id=photo&item_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>', 'GET'); return false;">
                <i class="ico ico-sponsor mr-1"></i><?php echo _p("Unsponsor In Feed"); ?>
            </a>
<?php endif; ?>
    </li>
<?php endif; ?>

<?php if ($this->_aVars['aForms']['canSponsor']): ?>
    <li id="js_sponsor_<?php echo $this->_aVars['aForms']['photo_id']; ?>" class="" style="<?php if ($this->_aVars['aForms']['is_sponsor']): ?>display:none;<?php endif; ?>">
        <a href="#" onclick="$('#js_sponsor_<?php echo $this->_aVars['aForms']['photo_id']; ?>').hide();$('#js_unsponsor_<?php echo $this->_aVars['aForms']['photo_id']; ?>').show();$.ajaxCall('photo.sponsor','photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>&type=1'); return false;">
            <i class="ico ico-sponsor mr-1"></i><?php echo _p('sponsor_this_photo'); ?>
        </a>
    </li>
    <li id="js_unsponsor_<?php echo $this->_aVars['aForms']['photo_id']; ?>" class="" style="<?php if ($this->_aVars['aForms']['is_sponsor'] != 1): ?>display:none;<?php endif; ?>">
        <a href="#" onclick="$('#js_sponsor_<?php echo $this->_aVars['aForms']['photo_id']; ?>').show();$('#js_unsponsor_<?php echo $this->_aVars['aForms']['photo_id']; ?>').hide();$.ajaxCall('photo.sponsor','photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>&type=0'); return false;">
            <i class="ico ico-sponsor mr-1"></i><?php echo _p('unsponsor_this_photo'); ?>
        </a>
    </li>
<?php elseif ($this->_aVars['aForms']['canPurchaseSponsor']): ?>
<?php if ($this->_aVars['aForms']['is_sponsor'] == 1): ?>
    <li id="js_sponsor_purchase_<?php echo $this->_aVars['aForms']['photo_id']; ?>">
        <a href="#" onclick="$.ajaxCall('photo.sponsor','photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>&type=0'); return false;">
            <i class="ico ico-sponsor mr-1"></i><?php echo _p('unsponsor_this_photo'); ?>
        </a>
    </li>
<?php else: ?>
    <li>
        <a href="<?php echo Phpfox::permalink('ad.sponsor', $this->_aVars['aForms']['photo_id'], null, false, null, (array) array (
)); ?>section_photo/">
            <i class="ico ico-sponsor mr-1"></i><?php echo _p('sponsor_this_photo'); ?>
        </a>
    </li>
<?php endif;  endif; ?>

<?php if ($this->_aVars['aForms']['canFeature']): ?>
    <li id="js_photo_feature_<?php echo $this->_aVars['aForms']['photo_id']; ?>">
<?php if ($this->_aVars['aForms']['is_featured']): ?>
            <a href="#" title="<?php echo _p('un_feature_this_photo'); ?>" onclick="$.ajaxCall('photo.feature', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>&amp;type=0', 'GET'); return false;"><i class="ico ico-diamond-o mr-1"></i><?php echo _p('un_feature'); ?></a>
<?php else: ?>
            <a href="#" title="<?php echo _p('feature_this_photo'); ?>" onclick="$.ajaxCall('photo.feature', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>&amp;type=1', 'GET'); return false;"><i class="ico ico-diamond mr-1"></i><?php echo _p('feature'); ?></a>
<?php endif; ?>
    </li>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('photo.template_block_menu')) ? eval($sPlugin) : false); ?>

<?php if ($this->_aVars['aForms']['canDelete']): ?>
<?php if (defined ( 'PHPFOX_IS_THEATER_MODE' )): ?>
        <li class="item_delete"><a href="#" onclick="$Core.jsConfirm({}, function() { $.ajaxCall('photo.deleteTheaterPhoto', 'photo_id=<?php echo $this->_aVars['aForms']['photo_id']; ?>'); }, function(){}); return false;"><?php echo _p('delete_this_photo'); ?></a></li>
<?php else: ?>
        <li class="item_delete">
            <a href="javascript:void(0);" data-message=<?php if (isset ( $this->_aVars['iAvatarId'] ) && $this->_aVars['iAvatarId'] == $this->_aVars['aForms']['photo_id']): ?>"<?php echo _p('are_you_sure_you_want_to_delete_this_photo_permanently_this_will_delete_your_current_profile_picture_also'); ?>"<?php elseif (isset ( $this->_aVars['iCover'] ) && $this->_aVars['iCover'] == $this->_aVars['aForms']['photo_id']): ?>"<?php echo _p('are_you_sure_you_want_to_delete_this_photo_permanently_this_will_delete_your_current_cover_photo_also'); ?>"<?php else: ?>"<?php echo _p('are_you_sure_you_want_to_delete_this_photo_permanently'); ?>"<?php endif; ?> data-id="<?php echo $this->_aVars['aForms']['photo_id']; ?>" data-is-detail="<?php if (isset ( $this->_aVars['bIsDetail'] ) && $this->_aVars['bIsDetail'] && ! isset ( $this->_aVars['bIsAlbumDetail'] )): ?>1<?php else: ?>0<?php endif; ?>" onclick="$Core.Photo.deletePhoto($(this));"><i class="ico ico-trash-alt-o mr-1"></i><?php echo _p('delete_this_photo'); ?></a>
        </li>
<?php endif;  endif; ?>

