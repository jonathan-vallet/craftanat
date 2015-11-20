<h1>Liste des questions</h1>
<p class="form-inline">
	<a href="/admin/manageQuestion.php" class="btn btn-default">Ajouter une question</a>
	<select id="structureList" class="form-control">
		<option value="">Filtrer par structure</option>
{foreach $structureList as $structure}
		<option value="{$structure.id}">{$structure.name}</option>
{/foreach}
	</select>
</p>
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th>Structure</th>
			<th>Type</th>
			<th>Question</th>
			<th>Craft?</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
{foreach $questionList as $question}
		<tr>
			<td>{$question.structureName}</td>
			<td>{$question.categoryName}</td>
			<td>{htmlspecialchars($question.text)}{if $question.imageUrl}<img src="{$question.imageUrl}" alt="" />{/if}</td>
			<td><input type="checkbox" value="1"{if $question.isInCraftQuizz} checked="checked"{/if} disabled="disabled" /></td>
			<td><a href="/admin/manageQuestion.php?questionId={$question.id}" title="" class="btn btn-default btn-sm">Editer</a></td>
		</tr>
{/foreach}
	</tbody>
</table>
<script type="text/javascript">
	document.getElementById('structureList').addEventListener('change', function() {
		window.location = '{$currentUrl}?structureId=' + this.value;
	}, false);
</script>

