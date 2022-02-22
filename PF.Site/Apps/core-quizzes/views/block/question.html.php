<?php
/**
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		Quiz
 * @version 		4.5.3
 *
 */

defined('PHPFOX') or exit('NO DICE!');

?>

<div class="form-group full_question_holder quizzes-app">
	<label class="question_number_title">
		{if isset($phpfox.iteration.question) && $phpfox.iteration.question <= Phpfox::getUserParam('quiz.min_questions')}
			{required}
		{/if}
		{_p var='question'} {if isset($phpfox.iteration.question)}{$phpfox.iteration.question}{/if}
	</label>
		{if (isset($phpfox.iteration.question) && $phpfox.iteration.question <= Phpfox::getUserParam('quiz.min_questions')) ||
		    (isset($Question.iQuestionIndex) && $Question.iQuestionIndex <= Phpfox::getUserParam('quiz.min_questions')) ||
			(!isset($phpfox.iteration.question) && !isset($Question.iQuestionIndex))}
		<div id="removeQuestion">
		{else}
		<div id="removeQuestion">
        {/if}
			<a href="#" onclick="return $Core.quiz.removeQuestion(this);"><i class="ico ico-trash-o"></i></a>
		</div>

		<input type="text" class="form-control question_title close_warning" placeholder="{_p var='enter_question_here'}" name="val[q][{if isset($Question.question_id)}{$Question.question_id}{elseif isset($phpfox.iteration.question)}{$phpfox.iteration.question}{else}0{/if}][question]" value="{if isset($Question.question)}{$Question.question}{/if}" maxlength="200" size="30" />

		<div class="form-group quiz-answer">
			<div class="answer_holder answers_holder" id="">
				{if isset($Question.answers)}
					{foreach from=$Question.answers item=aAnswer name=iAnswer}
						<div class="p_2 answer_parent {if isset($aAnswer.is_correct) && $aAnswer.is_correct == 1}correctAnswer{/if}">
							<input type="hidden" class="hdnCorrectAnswer" name="val[q][{if isset($Question.question_id)}{$Question.question_id}{elseif isset($phpfox.iteration.question)}{$phpfox.iteration.question}{else}0{/if}][answers][{if isset($aAnswer.answer_id) && $aAnswer.answer_id != ''}{$aAnswer.answer_id}{else}{$phpfox.iteration.iAnswer}{/if}][is_correct]" value="{if isset($aAnswer.is_correct)}{$aAnswer.is_correct}{else}found none{/if}">
							{if isset($aAnswer.answer_id)}
<!--								{* On error when adding this should not be set but when editing yes *}-->
								<input type="hidden" name="val[q][{if isset($Question.question_id)}{$Question.question_id}{elseif isset($phpfox.iteration.question)}{$phpfox.iteration.question}{else}0{/if}][answers][{if isset($aAnswer.answer_id) && $aAnswer.answer_id != ''}{$aAnswer.answer_id}{else}{$phpfox.iteration.iAnswer}{/if}][answer_id]" class="hdnAnswerId"  value="{if !isset($bErrors) || $bErrors == false}{$aAnswer.answer_id}{/if}">
								<input type="hidden" name="val[q][{if isset($Question.question_id)}{$Question.question_id}{elseif isset($phpfox.iteration.question)}{$phpfox.iteration.question}{else}0{/if}][answers][{if isset($aAnswer.answer_id) && $aAnswer.answer_id != ''}{$aAnswer.answer_id}{else}{$phpfox.iteration.iAnswer}{/if}][question_id]" class="hdnQuestionId"  value="{if isset($aAnswer.question_id)}{$aAnswer.question_id}{/if}">
							{else}
<!--								{* This happens when there is an error submitting (forgot to add a question title maybe) *}-->
							{/if}
							<input type="text" name="val[q][{if isset($Question.question_id)}{$Question.question_id}{elseif isset($phpfox.iteration.question)}{$phpfox.iteration.question}{else}0{/if}][answers][{if isset($aAnswer.answer_id) && $aAnswer.answer_id != ''}{$aAnswer.answer_id}{elseif isset($phpfox.iteration.iAnswer)}{$phpfox.iteration.iAnswer}{else}{$phpfox.iteration.iAnswer}{/if}][answer]" tabindex="" class="form-control close_warning answer " value="{$aAnswer.answer}" maxlength="100" onblur="{literal}if(this.value == ''){ this.value = '{/literal}{$aAnswer.answer}{literal}';}{/literal}" onfocus="{literal}if ( (this.value.length == 'Answer X...'.length || this.value.length == 'Answer XY...'.length) && (this.value.substr(0,'Answer '.length) == 'Answer ') && (this.value.substr('Answer X'.length, 3) == '...')){this.value = '';}{/literal}" />

							<a href="#" class="a_addAnswer" onclick="return $Core.quiz.appendAnswer(this);">
								<i class="ico ico-plus-circle-o"></i>
							</a>
							<a href="#" class="a_removeAnswer_{$phpfox.iteration.iAnswer}" id="a_removeAnswer" onclick="return $Core.quiz.deleteAnswer(this);">
								<i class="ico ico-minus-circle-o"></i>
							</a>
							<a href="#" class="a_setCorrect_{$phpfox.iteration.iAnswer}" id="a_setCorrect" onclick="return $Core.quiz.setCorrect(this);">
								<i class="ico ico-circle-o"></i>
							</a>
						</div>
					{/foreach}
				{else}
					{for $i=1; $i <= $iDefaultAnswers; $i++}
						<div id="answer_[iQuestionId]_{$i}" class="p_2 answer_parent form-group">
							<input name="val[q][{if isset($Question.question_id)}{$Question.question_id}{elseif isset($phpfox.iteration.question)}{$phpfox.iteration.question}{else}0{/if}][answers][{$i}][is_correct]" type="hidden" class="hdnCorrectAnswer" value="0">
							<input type="hidden" class="hdnAnswerId"  value="">
							<input type="hidden" class="hdnQuestionId"  value="">
							<input class="form-control answer close_warning" placeholder="{_p var='answer'}" type="text" name="" tabindex="{$i}" class="answer answer_{$i}" value="" maxlength="100" />
							<a href="#" class="a_addAnswer" onclick="return $Core.quiz.appendAnswer(this);">
								<i class="ico ico-plus-circle-o"></i>
							</a>
							<a href="#" class="a_removeAnswer_{$i}" id="a_removeAnswer" onclick="return $Core.quiz.deleteAnswer(this);">
								<i class="ico ico-minus-circle-o"></i>
							</a>
							<a href="#" class="a_setCorrect_{$i}" id="a_setCorrect" onclick="return $Core.quiz.setCorrect(this);">
								<i class="ico ico-circle-o"></i>
							</a>
						</div>
					{/for}
				{/if}
			</div>
		</div>
</div>
