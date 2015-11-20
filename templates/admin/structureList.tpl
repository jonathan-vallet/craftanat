<h1>Liste des structures</h1>
<p>
	<a href="/admin/manageStructure.php" class="btn btn-default">Ajouter une structure</a>
</p>
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th>Nom</th>
			<th>Catégorie</th>
			<th>Composants</th>
			<th>Prérequis</th>
			<th>Nb. de questions</th>
			<th>Pour craft</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
{foreach $structureList as $structure}
		<tr>
			<td>
				<a href="/admin/questionList.php?structureId={$structure.id}">{$structure.name}</a>
				{if $structure.imageUrl}<img src="{$structure.imageUrl}" alt="" />{/if}
			</td>
			<td>
				{$structure.categoryName}
			</td>
			<td>
				{$structure.componentQuantity} {$structure.componentName}
			</td>
			<td>
	{foreach $structure.requiredStructureList as $requiredStructure}
				{$requiredStructure.name}<br />
	{/foreach}
			</td>
			<td>{$structure.questionNumber}</td>
			<td>{$structure.craftQuestionNumber}</td>
			<td><a href="/admin/manageStructure.php?structureId={$structure.id}" title="" class="btn btn-default btn-sm">Editer</a></td>
		</tr>
{/foreach}
	</tbody>
</table>