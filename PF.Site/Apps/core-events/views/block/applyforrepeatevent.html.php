<div id="event_option_repeat_block" class="pt-2 mb-2">
    <label for="">{_p var='event.apply_edits_for'}</label>
    <div class=" ml--1 mr--1">
        <div class="form-group pl-1 pr-1">
            <label class="mb-0 fw-normal cursor-point">
                <input class="hidden" type="radio" name="val[event_editconfirmboxoption_value]" id="only_in_block" value="only_this_event" checked="checked" />
                <i class="ico ico-circle-o text-gray-dark mr-1"></i>
                <span>{_p var='event.only_this_event'}</span>
            </label>
            <div class="help-block ml-3">
                {_p var='event.only_this_event_desc'}
            </div>
        </div>

        <div class="form-group pl-1 pr-1">
            <label class="mb-0 fw-normal cursor-point">
                <input class="hidden" type="radio" name="val[event_editconfirmboxoption_value]" id="all_in_block" value="all_events_uppercase" />
                <i class="ico ico-circle-o text-gray-dark mr-1"></i>
                <span>{_p var='event.all_events_uppercase'}</span>
            </label>
            <div class="help-block ml-3">
                {_p var='event.all_events_desc'}
            </div>
        </div>
    </div>
</div>

{literal}
<script type="text/javascript">
    $Behavior.CEEditOptionRepeat = function(){
        $('#event_option_repeat_block').change(function () {
            if($("#only_in_block").is(':checked')){
                $(".yn_edit_event_apply #only_in_form").prop('checked',true)
            }
            if($("#all_in_block").is(':checked')){
                $(".yn_edit_event_apply #all_in_form").prop('checked',true)
            }
        });
    }
</script>
{/literal}
