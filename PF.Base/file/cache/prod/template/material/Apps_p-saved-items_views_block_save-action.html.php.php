<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php
 if ($this->_aVars['saveItemParams']['is_saved']): ?>
<a href="javascript:void(0);" onclick="return appSavedItem.processItem({type_id: '<?php echo $this->_aVars['saveItemParams']['type_id']; ?>', item_id: <?php echo $this->_aVars['saveItemParams']['item_id']; ?>, link: '<?php echo $this->_aVars['saveItemParams']['link']; ?>', is_save: 0, feed_id: <?php echo $this->_aVars['saveItemParams']['id']; ?>});">
    <span class="ico ico-bookmark-o"></span><?php echo _p('saveditems_unsave'); ?>
</a>
<?php else: ?>
<a href="javascript:void(0);" onclick="return appSavedItem.processItem({type_id: '<?php echo $this->_aVars['saveItemParams']['type_id']; ?>', item_id: <?php echo $this->_aVars['saveItemParams']['item_id']; ?>, link: '<?php echo $this->_aVars['saveItemParams']['link']; ?>', is_save: 1, feed_id: <?php echo $this->_aVars['saveItemParams']['id']; ?>});">
    <span class="ico ico-bookmark-o"></span><?php echo _p('save'); ?>
</a>
<?php endif; ?>

