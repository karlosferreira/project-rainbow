<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php

?>
<div class="item_view " id="photo-detail-view">
    <div class="core-photos-view-content-collapse-container">
        <div class="core-photos-view-content-collapse js_core_photos_view_content_collapse ">
<?php if ($this->_aVars['aForms']['description']): ?>
                <div class="item_description item_view_content">
<?php echo Phpfox::getLib('phpfox.parse.output')->parse($this->_aVars['aForms']['description']); ?>
                </div>
<?php endif; ?>
            <div class="item-extra-info">
<?php if (! empty ( $this->_aVars['aForms']['album_id'] )): ?>
                    <div class="item-album-info">
<?php echo _p('in_album'); ?>: <a href="<?php echo $this->_aVars['aForms']['album_url']; ?>"><?php echo Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->split(Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getLib('locale')->convert($this->_aVars['aForms']['album_title']))), 45), 75, '...'); ?></a>
                    </div>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aForms']['sCategories'] )): ?>
                    <div class="item-category">
<?php echo _p("Categories"); ?>: <?php echo $this->_aVars['aForms']['sCategories']; ?>
                    </div>
<?php endif; ?>
<?php if (Phpfox ::isModule('tag') && isset ( $this->_aVars['aForms']['tag_list'] )): ?>
<?php Phpfox::getBlock('tag.item', array('sType' => 'photo','sTags' => $this->_aVars['aForms']['tag_list'],'iItemId' => $this->_aVars['aForms']['photo_id'],'iUserId' => $this->_aVars['aForms']['user_id'])); ?>
<?php endif; ?>
                <div class="item-size">
                    <div class="item-size-stat">
                        <span class="item-title"><?php echo _p('dimension'); ?>:</span>
                        <span class="item-number"><?php echo $this->_aVars['aForms']['width']; ?> x <?php echo $this->_aVars['aForms']['height']; ?></span>
                    </div>
                    <div class="item-size-stat">
                        <span class="item-title"><?php echo _p('file_size'); ?>:</span>
                        <span class="item-number"><?php echo Phpfox::getLib('phpfox.file')->filesize($this->_aVars['aForms']['file_size']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="core-photos-view-action-collapse js-core-photo-action-collapse">
            <a class="item-viewmore-btn js-item-btn-toggle-collapse"><?php echo _p("view_more"); ?> <i class="ico ico-caret-down"></i></a>
            <a class="item-viewless-btn js-item-btn-toggle-collapse"><?php echo _p("view_less"); ?> <i class="ico ico-caret-up"></i></a>
        </div>
    </div>
    <div class="js_moderation_on">
        <div class="item-addthis mb-3 pt-2"><?php Phpfox::getBlock('share.addthis', array('url' => $this->_aVars['aForms']['link'],'title' => $this->_aVars['aForms']['title'],'description' => $this->_aVars['sShareDescription'])); ?></div>
<?php if (Phpfox ::isModule('feed') && Phpfox ::getParam('feed.enable_check_in') && Phpfox ::getParam('core.google_api_key') != '' && ! empty ( $this->_aVars['aForms']['location_name'] )): ?>
            <div class="activity_feed_location">
                <span class="activity_feed_location_at"><?php echo _p('at'); ?> </span>
                <span class="js_location_name_hover activity_feed_location_name" <?php if (isset ( $this->_aVars['aForms']['location_latlng'] ) && isset ( $this->_aVars['aForms']['location_latlng']['latitude'] )): ?>onmouseover="$Core.Feed.showHoverMap('<?php echo $this->_aVars['aForms']['location_latlng']['latitude']; ?>','<?php echo $this->_aVars['aForms']['location_latlng']['longitude']; ?>', this);"<?php endif; ?>>
                <span class="ico ico-checkin"></span>
                <a href="https://maps.google.com/maps?daddr=<?php echo $this->_aVars['aForms']['location_latlng']['latitude']; ?>,<?php echo $this->_aVars['aForms']['location_latlng']['longitude']; ?>" target="_blank"><?php echo $this->_aVars['aForms']['location_name']; ?></a>
                </span>
            </div>
<?php endif; ?>
        <div class="item-detail-feedcomment">
<?php Phpfox::getBlock('feed.comment', array()); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    var bChangePhoto = true;
    var aPhotos = <?php echo $this->_aVars['sPhotos']; ?>;
    var oPhotoTagParams =  {<?php echo $this->_aVars['sPhotoJsContent']; ?>};
    $Behavior.tagPhoto = function()
    {
        setTimeout(function() {
            $Core.photo_tag.init(oPhotoTagParams);
        }, 500);
        $("#page_photo_view input.v_middle" ).focus(function() {
            $(this).parent('.table_right').addClass('focus');
            $(this).parents('.table').siblings('.cancel_tagging').addClass('focus');
        });
        $("#page_photo_view input.v_middle" ).focusout(function() {
            $(this).parent('.table_right').removeClass('focus');
            $(this).parents('.table').siblings('.cancel_tagging').removeClass('focus');
        });
    };

    $Behavior.removeImgareaselectBox = function()
    {
        <?php echo '
            if ($(\'body#page_photo_view\').length == 0 || ($(\'body#page_photo_view\').length > 0 && bChangePhoto == true)) {
                bChangePhoto = false;
                $(\'.imgareaselect-outer\').hide();
                $(\'.imgareaselect-selection\').each(function() {
                    $(this).parent().hide();
                });
            }
        '; ?>

    };
</script>

<?php if ($this->_aVars['bLoadCheckin']): ?>
    <script type="text/javascript">
        var bCheckinInit = false;
        $Behavior.prepareInit = function()
        {
            if($Core.Feed !== undefined)
            {
                $Core.Feed.sIPInfoDbKey = '';
                $Core.Feed.sGoogleKey = '<?php echo Phpfox::getParam('core.google_api_key'); ?>';
<?php if (isset ( $this->_aVars['aVisitorLocation'] )): ?>
                    $Core.Feed.setVisitorLocation(<?php echo $this->_aVars['aVisitorLocation']['latitude']; ?>, <?php echo $this->_aVars['aVisitorLocation']['longitude']; ?> );
<?php else: ?>

<?php endif; ?>
                $Core.Feed.googleReady('<?php echo Phpfox::getParam('core.google_api_key'); ?>');
            }
        }
    </script>
<?php endif; ?>
