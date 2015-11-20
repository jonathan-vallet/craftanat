<p>
	<a href="/admin/structureList.php" title="" class="btn btn-default">Retour à la liste</a>
</p>
<h1>{if $isEditMode}Editer{else}Ajouter{/if} une structure</h1>
{if !empty($errorList)}
<ul class="error">
	{foreach $errorList as $error}
	<li>{$error}</li>		
	{/foreach}
</ul>
{/if}
<form method="post" action="{$formAction}" enctype="multipart/form-data" role="form" class="col-md-6">
	<p class="form-group">
		<input type="text" name="name" placeholder="Nom"{if isset($structure)} value="{$structure.name}"{/if} class="form-control" />
	</p>
	<p class="form-group">
		<label>Ordre de tri
			<input type="text" name="order" placeholder="Ordre de tri"{if isset($structure)} value="{$structure.order}"{/if} class="form-control" />
		</label>
	</p>
	<p class="form-group">
		<label>Nombre de composants pour le craft
			<input type="text" name="componentQuantity" placeholder="Nombre de composants pour le craft"{if isset($structure)} value="{$structure.componentQuantity}"{/if} class="form-control" />
		</label>
	</p>
	<p class="form-group">
		<label>Nombre de questions pour un quizz {if isset($structure)}({$structure.totalQuestionNumber} max){/if}
			<input type="text" name="questionNumber" placeholder="Nombre de questions pour un quizz"{if isset($structure)} value="{$structure.questionNumber}"{/if} class="form-control" />
		</label>
	</p>
	<p class="form-group">
		<label for="structureCategorySelect">Type de structure: </label>
		<select id="structureCategorySelect" name="structureCategory" class="form-control">
			<option value=""></option>
		{foreach $structureCategoryList as $structureCategory}
			<option value="{$structureCategory.id}"{if isset($structure) && $structure.categoryId === $structureCategory.id} selected="selected"{/if}>{$structureCategory.name}</option>
		{/foreach}
		</select>
	</p>
	<p class="form-group">
		<label for="requiredStructure">Pré-requis: </label>
		<select id="requiredStructure" name="requiredStructure[]" multiple="multiple" class="form-control">
		{foreach $structureList as $requiredStructure}
			<option value="{$requiredStructure.id}"{if isset($structure) && in_array($requiredStructure.id, $structure.requiredStructureIdList)} selected="selected"{/if}>{$requiredStructure.name}</option>
		{/foreach}
		</select>
	</p>
	<p class="form-group">
		<label for="structureImage">Image</label>
		<input type="file" id="structureImage" name="image" />
	{if isset($structure) && $structure.imageUrl}
		<img src="{$structure.imageUrl}" alt="" />
	{/if}
	</p>
	<p class="form-group">
		<input type="submit" value="{$submitText}" class="btn btn-primary" />
	</p>
</form>
