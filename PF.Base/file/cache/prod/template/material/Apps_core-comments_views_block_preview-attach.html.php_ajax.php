<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 2:19 am */ ?>
<?php

 if ($this->_aVars['sType'] == 'photo'): ?>
    <div class="item-edit-photo js_comment_attach_preview">
        <div class="item-photo">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('server_id' => $this->_aVars['aForms']['server_id'],'path' => 'core.url_pic','file' => "comment/".$this->_aVars['aForms']['path'],'suffix' => '')); ?>
        </div>
        <a class="item-delete" onclick="$Core.Comment.deleteAttachment($(this),<?php echo $this->_aVars['aForms']['file_id']; ?>,'photo', <?php if (isset ( $this->_aVars['bIsEdit'] )): ?>true<?php else: ?>false<?php endif; ?>); return false;"><span class="ico ico-close"></span></a>
    </div>
<?php elseif ($this->_aVars['sType'] == 'sticker'): ?>
    <div class="item-edit-sticker js_comment_attach_preview">
        <div class="item-sticker">
<?php echo $this->_aVars['aForms']['full_path']; ?>
        </div>
        <a class="item-delete" onclick="$Core.Comment.deleteAttachment($(this),<?php echo $this->_aVars['aForms']['sticker_id']; ?>,'sticker', <?php if (isset ( $this->_aVars['bIsEdit'] )): ?>true<?php else: ?>false<?php endif; ?>); return false;"><span class="ico ico-close"></span></a>
    </div>
<?php endif; ?>

