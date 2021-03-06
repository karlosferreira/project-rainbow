<?php
/**
 *
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		Poll
 *
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
    <script type="text/javascript">
        oTranslations['{$sPhraseKey}'] = '{$sPhraseValue}';
    </script>

    <div class="global_attachment_holder_section" id="global_attachment_poll">
        <div class="table form-group">
            <div class="table_left">
                {_p var='question'}:
            </div>
            <div class="table_right">
                <input type="text" name="val[poll_question]" value="" style="width:90%;" onchange="if (empty(this.value)) {l} $bButtonSubmitActive = false; $('.activity_feed_form_button .button').addClass('button_not_active'); {r} else {l} $bButtonSubmitActive = true; $('.activity_feed_form_button .button').removeClass('button_not_active'); {r}" />
            </div>
        </div>
        <div class="table form-group">
            <div class="table_left">
                {_p var='answers'}:
            </div>
            <div class="table_right">
                <ol id="js_poll_feed_answer" class="js_poll_feed_answer poll_feed_answer">
                {for $i = 1; $i <= 2; $i++}
                <li>
                    <input type="text" name="val[answer][][answer]" value="" size="30" class="js_feed_poll_answer v_middle" />
                </li>
                {/for}
                </ol>
                <a href="#" onclick="return $Core.addNewPollOption({$iMaxAnswers});" class="poll_feed_answer_add">{_p var='add_another_answer'}</a>
            </div>
        </div>
    </div>