<div class="core-feed-item quizzes-app feed {if empty($aQuiz.image_path)}no-photo{/if}">
	<div class="item-wrapper">
		<div class="item-outer-info">
			<div class="item-inner">
				<a href="{if $bIsFeedSponsor && $iSponsorId}{url link='ad.sponsor' view=$iSponsorId}{else}{permalink title=$aQuiz.title id=$aQuiz.quiz_id module='quiz'}{/if}" class="core-feed-title line-1">{$aQuiz.title|clean}</a>
				<div class="quizzes-answer-list">
					<ol>
                        {foreach from=$aInitQuestions item=aInitQuestion}
                            <li>{$aInitQuestion.question}</li>
                        {/foreach}
					</ol>
				</div>
			</div>
			{if !empty($aQuiz.image_path)}
			<div class="item-media">
	            <a class="item-media-bg" href="{if $bIsFeedSponsor && $iSponsorId}{url link='ad.sponsor' view=$iSponsorId}{else}{permalink title=$aQuiz.title id=$aQuiz.quiz_id module='quiz'}{/if}"
	               style="background-image: url({img server_id=$aQuiz.server_id path='quiz.url_image' file=$aQuiz.image_path suffix='' return_url=true})">
	            </a>
	        </div>
	        {/if}
		</div>
		<div class="item-outer-action">
			<div class="action-vote-bottom">
                {if !empty($iRestQuestion)}
				<a class="item-show-more" href="{$sUrl}">
					{if (int)$iRestQuestion > 1}{_p var='quiz_see_more_questions' number=$iRestQuestion}{else}{_p var='quiz_see_more_question' number=$iRestQuestion}{/if} <i class="ico ico-angle-right"></i>
				</a>
                {/if}
				<div class="quizz-group-submit">
					<a href="javascript:void(0);" {if $aQuiz.total_play > 0} onclick="tb_show('{_p('quiz_total_plays_title')}', $.ajaxBox('quiz.showTotalPlays', 'quiz_id={$aQuiz.quiz_id}')); return false;" {/if} class="item-vote-number">{$aQuiz.total_play|short_number} {if $aQuiz.total_play == 1}{_p('quiz_total_play')}{else}{_p('quiz_total_plays')}{/if}</a>
					<div class="item-submit">
						<a href="{$sUrl}" class="btn btn-sm btn-default">{if !empty($iHasTaken)}{_p var='quiz_view_result'}{else}{_p var='quiz_answer_now'}{/if}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>