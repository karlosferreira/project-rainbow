var $bIsSearching = false;
var $bKeepSending = true;
var $iJobId = null;
var iCurrentPage = 0;
$Behavior.user_inactivereminder_init = function()
{
	$('#btnSearch').off('click').on('click', function() {
		var $iDays = $('#inactive_days').val();
		if (Math.ceil($iDays) != Math.floor($iDays) || (parseInt($iDays) < 1) || $iDays == '')
		{
			alert(oTranslations['enter_a_number_of_days']);
			return false;
		}
		var $iBatchSize = $('#mails_per_batch').val();
		if (Math.ceil($iBatchSize) != Math.floor($iBatchSize) || (parseInt($iBatchSize) < 0) || $iBatchSize == '')
		{
			alert(oTranslations['enter_a_number_to_size_each_batch']);
			return false;
		}
		if ($bIsSearching == true)
		{
			//return false;
		}
		$bIsSearching = true;
		$.ajaxCall('user.getInactiveMembersCount','iDays=' + $iDays);
		return true;
	});

	$('#btnProcess').off('click').on('click',function() {
		if ($bKeepSending == false && $iJobId != null)
		{
			$bKeepSending = true;
			processJob($iJobId);
			return true;
		}
		else
		{
			return addJob();
		}

		return true;
	});

	$("#btnStop").off('click').on('click',function() {
		$bKeepSending = false;
		$("#progress").html($("#progress").html() + '. '+oTranslations['stopped']);
		$("#inactive_days").attr('disabled','');
		$("#mails_per_batch").attr('disabled','');
		$("#btnProcess").show();
		$("#btnStop").hide();
	});
	$("#btnSendAll").off('click').on('click',function() {
		var iDays = $('#inactive_days').val();
		if (parseInt(iDays) < 0) {
			return false;
		}
		$Core.jsConfirm({message: getPhrase($(this).data('sms')
				? 'are_you_sure_you_want_send_mail_sms_to_all_inactive_members_who_have_not_logged_in_for_days_days'
				: 'are_you_sure_you_want_send_mail_to_all_inactive_members_who_have_not_logged_in_for_days_days').replace('{days}',iDays)}, function() {
			$.ajaxCall('user.addInactiveJob', $.param({
				'all': 1,
				'days': iDays
			}));
		}, function(){});

		return false;
	})
};

function addJob()
{
	if ($iJobId != null)
	{
		alert("There is a reminder job in progress.");
		return false;
	}
	// how many users do we have?
	var $iDays = $('#inactive_days').val();
	if ($iDays < 1)
	{
		alert(oTranslations['not_enough_users_to_mail']);
		return false;
	}
	var $iBatchSize = $('#mails_per_batch').val();
	$.ajaxCall('user.addInactiveJob', 'iDays='+$iDays+'&iBatchSize='+$iBatchSize);
	return true;
}

function startJob(iJobId)
{
	$("#btnProcess").hide();
	$("#btnStop").show();
	$("#inactive_days").attr('disabled','disabled');
	$("#mails_per_batch").attr('disabled','disabled');
	$iJobId = iJobId;
	$bKeepSending = true;
}

function processJob($iJobId)
{
	if ($bKeepSending == false)
	{
		return false;
	}
	$("#btnProcess").hide();
	$("#btnStop").show();
	$("#inactive_days").attr('disabled','disabled');
	$("#mails_per_batch").attr('disabled','disabled');
	if ($bKeepSending)
	{
		$.ajaxCall('user.processJob','iJobId='+$iJobId);
	}
	return true;
}

function jobCompleted()
{
	$("#inactive_days").attr('disabled','');
	$("#mails_per_batch").attr('disabled','');
	$("#btnProcess").show().attr('disabled','disabled');
	$("#btnStop").hide();
	setTimeout('$("#btnProcess").attr("disabled","");',3000);
	$iJobId = null;
}
