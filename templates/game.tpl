<div class="progress">
	<div class="progress-bar" role="progressbar" aria-valuenow="{$currentQuestionNumber - 1}" aria-valuemin="0" aria-valuemax="{$totalQuestionNumber}" style="width: {$percentCompleted}%;">
		<span class="sr-only">{$percentCompleted}% Complete</span>
	</div>
</div>
      
<h1>Question {$currentQuestionNumber}/{$totalQuestionNumber}</h1>
<form method="post" action="/play.php" id="game">
{if $question.categoryId === 1}
	<p class="question">{$question.text}</p>
	{if $question.imageUrl}
		<p>
			<img src="{$question.imageUrl}" alt="" />
		</p>
	{/if}
	{foreach $question.answerList as $answer name="answer"}
	<p class="checkbox">
		<label>
			<input type="{if $question.correctQuestionNumber > 1}checkbox{else}radio{/if}" value="{if $question.correctQuestionNumber > 1}1{else}{$smarty.foreach.answer.iteration}{/if}" name="answer{if $question.correctQuestionNumber > 1}{$smarty.foreach.answer.iteration}{/if}" />
			{$answer.text}
		</label>
	</p>
	{/foreach}
{else if $question.categoryId === 2}
	<p class="form-inline">{$question.text}</p>
	{if $question.imageUrl}
		<p>
			<img src="{$question.imageUrl}" alt="" />
		</p>
	{/if}
{else if $question.categoryId === 3}
	<p class="form-inline">{$question.text}</p>
	{if $question.imageUrl}
		<div id="schemaImageContainer" class="form-inline">
			<img src="{$question.imageUrl}" alt="" />
		{foreach $question.answerList as $answer name="answer"}
			<input name="answer{$smarty.foreach.answer.iteration}" class="form-control" type="text" style="top:{$answer.y}px; {if $answer.isCorrect === 1}left: -20px;{else}right: -70px;{/if}" />
			<span style="top:{$answer.y}px; {if $answer.isCorrect === 1}left: -50px; width: {$answer.x + 50}px;{else}left: {$answer.x}px; right:100px;{/if}"></span>
		{/foreach}
		</div>
	{/if}
{else if $question.categoryId === 4}
	<p class="form-inline">{$question.text}</p>
	{if $question.imageUrl}
	<div id="clickImageContainer" class="form-group">
		<img id="clickImage" src="{$question.imageUrl}" alt="" />
		<div id="clickImageOverlay"></div>
	</div>
	<script type="text/javascript">
		function setGraphicAnswer(e) {
			var x = e.offsetX;
			var y = e.offsetY;
			var defaultRadius = 5;
			var $graphicContainer = document.getElementById('clickImageContainer');
			var answer = document.getElementById('graphicAnswer');
			if(!answer) {
				answer = document.createElement('span');
				answer.classList.add('graphicAnswer');
				answer.id = 'graphicAnswer';
				answer.style.width = (2 * defaultRadius) + 'px';
				answer.style.height = (2 * defaultRadius) + 'px';
			}
			answer.style.left = (x - defaultRadius) + 'px';
			answer.style.top = (y - defaultRadius) + 'px';
			$graphicContainer.appendChild(answer);

			var answerInput = document.getElementById('imageAnswer');
			if(!answerInput) {
				var answerInput = document.createElement('input');
				answerInput.id = 'imageAnswer';
				answerInput.type = 'hidden';
				answerInput.name = 'answer';
			}
			answerInput.value = x + '/' + y;
			$graphicContainer.appendChild(answerInput);
		}
		document.getElementById('clickImageOverlay').addEventListener('click', setGraphicAnswer, false);
	</script>
	{/if}
{/if}
	<p class="label">
		<input type="submit" value="Valider" class="btn btn-lg btn-primary" />
	</p>
</form>
