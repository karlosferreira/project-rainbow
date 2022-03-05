<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:39 am */ ?>
<?php

?>

<div class="sub_section_menu core-block-categories">
    <ul <?php if (isset ( $this->_aVars['sUlClass'] )): ?>class="<?php echo $this->_aVars['sUlClass']; ?>"<?php else: ?>class="action category-list"<?php endif; ?>>
<?php if (count((array)$this->_aVars['aCategories'])):  foreach ((array) $this->_aVars['aCategories'] as $this->_aVars['iCategoryCount'] => $this->_aVars['aCategory']): ?>
    <li class="<?php if (isset ( $this->_aVars['iCurrentCategory'] ) && $this->_aVars['iCurrentCategory'] == $this->_aVars['aCategory']['category_id']): ?>active<?php endif; ?> <?php if (isset ( $this->_aVars['iParentCategoryId'] ) && $this->_aVars['iParentCategoryId'] == $this->_aVars['aCategory']['category_id']): ?>open<?php endif; ?> <?php if (isset ( $this->_aVars['sModule'] )):  echo $this->_aVars['sModule']; ?>_<?php endif; ?>category">
        <div <?php if (isset ( $this->_aVars['aCategory']['sub'] ) && count ( $this->_aVars['aCategory']['sub'] ) > 0): ?>class="no_ajax_link category_show_more_less_link category-item"<?php else: ?>class="category-item"<?php endif; ?> >
            <a class="name" href="<?php echo $this->_aVars['aCategory']['url'];  if (Phpfox_Request ::instance()->get('view') != ''): ?>view_<?php echo urlencode(Phpfox::getLib('request')->get('view')); ?>/<?php endif; ?>" id="<?php if (isset ( $this->_aVars['sModule'] )):  echo $this->_aVars['sModule']; ?>_<?php endif; ?>category_<?php echo $this->_aVars['aCategory']['category_id']; ?>">
<?php echo _p($this->_aVars['aCategory']['name']); ?>
            </a>
<?php if (isset ( $this->_aVars['aCategory']['sub'] ) && count ( $this->_aVars['aCategory']['sub'] ) > 0): ?>
            <span class="category-toggle core-btn-collapse" data-toggle="collapse" data-target="#<?php if (isset ( $this->_aVars['sModule'] )):  echo $this->_aVars['sModule']; ?>_<?php endif; ?>sub_list_category_<?php echo $this->_aVars['aCategory']['category_id']; ?>" <?php if (isset ( $this->_aVars['iParentCategoryId'] ) && $this->_aVars['iParentCategoryId'] == $this->_aVars['aCategory']['category_id']): ?>aria-expanded="true"<?php endif; ?>>
                <i class="ico ico-angle-down"></i>
            </span>
<?php endif; ?>
        </div>

<?php if (isset ( $this->_aVars['aCategory']['sub'] ) && count ( $this->_aVars['aCategory']['sub'] )): ?>
        <ul class="collapse <?php if (isset ( $this->_aVars['iParentCategoryId'] ) && $this->_aVars['iParentCategoryId'] == $this->_aVars['aCategory']['category_id']): ?>in<?php endif; ?>" id="<?php if (isset ( $this->_aVars['sModule'] )):  echo $this->_aVars['sModule']; ?>_<?php endif; ?>sub_list_category_<?php echo $this->_aVars['aCategory']['category_id']; ?>">
<?php if (count((array)$this->_aVars['aCategory']['sub'])):  foreach ((array) $this->_aVars['aCategory']['sub'] as $this->_aVars['iKey'] => $this->_aVars['aSubCategory']): ?>
            <li class="<?php if (isset ( $this->_aVars['iCurrentCategory'] ) && $this->_aVars['iCurrentCategory'] == $this->_aVars['aSubCategory']['category_id']): ?>active<?php endif; ?> <?php if (isset ( $this->_aVars['sModule'] )):  echo $this->_aVars['sModule']; ?>_<?php endif; ?>subcategory_<?php echo $this->_aVars['aCategory']['category_id']; ?> special_subcategory">
                <a href="<?php echo $this->_aVars['aSubCategory']['url'];  if (Phpfox_Request ::instance()->get('view') != ''): ?>view_<?php echo urlencode(Phpfox::getLib('request')->get('view')); ?>/<?php endif; ?>" id="<?php if (isset ( $this->_aVars['sModule'] )):  echo $this->_aVars['sModule']; ?>_<?php endif; ?>subcategory_<?php echo $this->_aVars['aSubCategory']['category_id']; ?>">
<?php echo _p($this->_aVars['aSubCategory']['name']); ?>
                </a>
            </li>
<?php endforeach; endif; ?>
        </ul>
<?php endif; ?>
    </li>
<?php endforeach; endif; ?>
    </ul>
</div>
