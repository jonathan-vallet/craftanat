<p>
	<a href="/admin/questionList.php" title="" class="btn btn-default">Retour à la liste</a>
</p>
<h1>{if $isEditMode}Editer{else}Ajouter{/if} une question</h1>
{if !empty($errorList)}
<ul class="error">
	{foreach $errorList as $error}
	<li>{$error}</li>		
	{/foreach}
</ul>
{/if}

{if !$isEditMode}
<p class="form-group">
	<label for="questionCategorySelect">Type de question: </label>
	<select id="questionCategorySelect" name="questionCategory" class="form-control">
		<option value=""></option>
	{foreach $questionCategoryList as $questionCategory}
		<option value="{$questionCategory.id}"{if isset($question) && $question.categoryId === $questionCategory.id} selected="selected"{/if}>{$questionCategory.name}</option>
	{/foreach}
	</select>
</p>
{/if}
{if !isset($question) || $question.categoryId === 1}
<form method="post" action="{$formAction}" enctype="multipart/form-data" class="col-md-6{if !$isEditMode} question{/if}" id="questionCategory_1">
	<h2>QCM</h2>
	<input type="hidden" name="questionCategoryId" value="1" />
	<p class="form-group">
		<input type="text" name="question" placeholder="Question"{if isset($question)} value="{$question.text}"{/if} class="form-control" />
	</p>
	<p class="form-group">
		<label for="qcmImage">Image</label>
		<input type="file" id="qcmImage" name="image" />
	{if isset($question) && $question.imageUrl}
		<img src="{$question.imageUrl}" alt="" />
	{/if}
	</p>
	{assign var="emptyQuestionNumber" value=4 nocache}
	{if isset($question)}
		{assign var="emptyQuestionNumber" value=4-count($question.answerList) nocache}
		{foreach $question.answerList as $answer name="answer"}
	<p class="form-inline">
		<input type="checkbox" value="1" name="answer{$smarty.foreach.answer.iteration}Correct"{if $answer.isCorrect} checked="checked"{/if} />
		<input type="text" name="answer{$smarty.foreach.answer.iteration}" placeholder="Réponse {$smarty.foreach.answer.iteration}" value="{$answer.text}" class="form-control" />
	</p>
		{/foreach}
	{/if}
	{section name="answer" start=5-$emptyQuestionNumber loop=5}
	<p class="form-inline">
		<input type="checkbox" value="1" name="answer{$smarty.section.answer.index}Correct" />
		<input type="text" name="answer{$smarty.section.answer.index}" placeholder="Réponse {$smarty.section.answer.index}" class="form-control" />
	</p>
	{/section}
	<p class="form-group">
		<label for="structureSelect">Structure associée : </label>
		<select id="structureSelect" name="structure" class="form-control">
			<option value=""></option>
		{foreach $structureList as $structure}
			<option value="{$structure.id}"{if isset($question) && $question.structureId === $structure.id} selected="selected"{/if}>{$structure.name}</option>
		{/foreach}
		</select>
	</p>
	<p class="form-group">
		<label>
			<input type="checkbox" name="isInCraftQuizz" value="1"{if isset($question) && $question.isInCraftQuizz} checked="checked"{/if} />
			Fait partie du quizz de craft
		</label>
	</p>
	<p class="form-group">
		<input type="submit" value="{$submitText}" class="btn btn-primary" />
	</p>
</form>
{/if}

{if !isset($question) || $question.categoryId === 2}
<form method="post" action="{$formAction}" enctype="multipart/form-data" class="col-md-6{if !$isEditMode} question{/if}" id="questionCategory_2">
	<h2>Texte à trou</h2>
	<input type="hidden" name="questionCategoryId" value="2" />
	<p class="form-group">
		Taper "&lt;champ1&gt;" pour ajouter un champ à saisir<br />
		<textarea name="question" placeholder="Texte" class="form-control" rows="5">{if isset($question)}{$question.text}{/if}</textarea>
	</p>
	<p class="form-group">
		<label for="holeTextImage">Image</label>
		<input type="file" id="holeTextImage" name="image" />
	{if isset($question) && $question.imageUrl}
		<img src="{$question.imageUrl}" alt="" />
	{/if}
	</p>
	<p class="form-group">
		<label for="structureSelect">Structure associée : </label>
		<select id="structureSelect" name="structure" class="form-control">
			<option value=""></option>
		{foreach $structureList as $structure}
			<option value="{$structure.id}"{if isset($question) && $question.structureId === $structure.id} selected="selected"{/if}>{$structure.name}</option>
		{/foreach}
		</select>
	</p>
	{if isset($question)}
		{foreach $question.answerList as $answer name="answer"}
	<p class="form-group">
		<input type="text" id="holeTextAnswer_{$smarty.foreach.answer.iteration}" name="answer{$smarty.foreach.answer.iteration}" placeholder="Valeur du champ {$smarty.foreach.answer.iteration}" value="{$answer.text}" class="form-control" />
	</p>
		{/foreach}
	{/if}
	<p id="holeTextSubmit" class="form-group">
		<input type="submit" value="{$submitText}" class="btn btn-primary" />
	</p>
</form>
{/if}


{if !isset($question) || $question.categoryId === 3}
<form method="post" action="{$formAction}" enctype="multipart/form-data" class="col-md-6{if !$isEditMode} question{/if}" id="questionCategory_3">
	<h2>Schéma à légender</h2>
	<input type="hidden" name="questionCategoryId" value="3" />
	<p class="form-group">
		<input type="text" name="question" placeholder="Question" value="{if isset($question)}{$question.text}{/if}" class="form-control" />
	</p>
	<p class="form-group" id="schemaImageContainer">
		<label for="schemaImage">Image</label>
		<input type="file" id="schemaImage" name="image" />
	{if isset($question) && $question.imageUrl}
		<img src="{$question.imageUrl}" alt="" />
	{/if}
	</p>
	<p class="form-group">
		<label for="structureSelect">Structure associée : </label>
		<select id="structureSelect" name="structure" class="form-control">
			<option value=""></option>
		{foreach $structureList as $structure}
			<option value="{$structure.id}"{if isset($question) && $question.structureId === $structure.id} selected="selected"{/if}>{$structure.name}</option>
		{/foreach}
		</select>
	</p>
	<p class="form-group">
		<label>
			<input type="checkbox" name="isInCraftQuizz" value="1"{if isset($question) && $question.isInCraftQuizz} checked="checked"{/if} />
			Fait partie du quizz de craft
		</label>
	</p>
	<p class="form-group">
		<input type="submit" value="{$submitText}" class="btn btn-primary" />
	</p>
</form>

{/if}

{if !isset($question) || $question.categoryId === 4}
<form method="post" action="{$formAction}" enctype="multipart/form-data" class="col-md-6{if !$isEditMode} question{/if}" id="questionCategory_4">
	<h2>Visualisation graphique</h2>
	<input type="hidden" name="questionCategoryId" value="4" />
	<p class="form-group">
		<input type="text" name="question" placeholder="Question" value="{if isset($question)}{$question.text}{/if}" class="form-control" />
	</p>
	<p class="form-group" id="graphicImageContainer">
		<label for="graphicImage">Image</label>
		<input type="file" id="graphicImage" name="image" />
	{if isset($question) && $question.imageUrl}
		<img src="{$question.imageUrl}" alt="" />
	{/if}
	</p>
	<p class="form-group">
		<label for="structureSelect">Structure associée : </label>
		<select id="structureSelect" name="structure" class="form-control">
			<option value=""></option>
		{foreach $structureList as $structure}
			<option value="{$structure.id}"{if isset($question) && $question.structureId === $structure.id} selected="selected"{/if}>{$structure.name}</option>
		{/foreach}
		</select>
	</p>
	<p class="form-group">
		<label>
			<input type="checkbox" name="isInCraftQuizz" value="1"{if isset($question) && $question.isInCraftQuizz} checked="checked"{/if} />
			Fait partie du quizz de craft
		</label>
	</p>
	<p class="form-group">
		<input type="submit" value="{$submitText}" class="btn btn-primary" />
	</p>
</form>
{/if}