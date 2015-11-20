<div class="progress">
	<div class="progress-bar" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="1" style="width: 100%;">
		<span class="sr-only">100% Complete</span>
	</div>
</div>

<div id="result">
	<h1>Résultat</h1>
	<p>Score: {$correctAnswerNumber}/{$totalQuestionNumber} en {$totalTime}</p>
{if $isSuccess && $previousBestScore && $isBestScore || $isBestTime}
	<p>Nouveau record!</p>	
{/if}
	<p>Epreuve {if $isSuccess}réussie{else}échouée{/if}</p>
{if !$isCraft && $isSuccess}
	<p>Gain: {$gainQuantity} <span class="icon {$gainComponentCode}">{$gainComponent}</span></p>
{/if}
{if $isCraft}
	<p>Craft réussi!</p>
{/if}
	<a class="btn btn-primary" href="/" title="">Retour aux quizz</a>
</div>
{if !$isCraft}
	{foreach $questionList as $question}
	
	<p class="question"><span class="questionNumber{if $question.answerCorrect} correct{/if}">{$question@iteration}</span>{$question.text}</p>
		{if $question.categoryId === 1}
			{if $question.imageUrl}
	<p>
		<img src="{$question.imageUrl}" alt="" />
	</p>
			{/if}
			{foreach $question.answerList as $answer name="answer"}
	<p class="checkbox">
		<label>
			<input disabled="disabled"{if $answer.isCorrect} checked="checked"{/if} type="{if $question.correctQuestionNumber > 1}checkbox{else}radio{/if}" value="{if $question.correctQuestionNumber > 1}1{else}{$smarty.foreach.answer.iteration}{/if}" />
			{$answer.text}
		</label>
	</p>
			{/foreach}
		{elseif $question.categoryId === 2}
			{if $question.imageUrl}
	<p>
		<img src="{$question.imageUrl}" alt="" />
	</p>
			{/if}
		{elseif $question.categoryId === 3}
		{elseif $question.categoryId === 4}
	<div class="graphicContainer">
		<p>
			<img src="{$question.imageUrl}" alt="" />
		</p>
			{foreach $question.answerList as $answer name="answer"}
		<span class="graphicAnswer" style="left: {$answer.x - $answer.text / 2}px; top: {$answer.y - $answer.text / 2}px; width: {$answer.text * 2}px; height: {$answer.text * 2}px; "></span>
			{/foreach}
	</div>
		{/if}
	{/foreach}
{/if}

