{if $isPlayerConnected}
<div class="col-md-8" id="gameList">
	{foreach $structureList as $structure}
	   {if $structure@first || $structureCategory != $structure.categoryId}
	       {if !$structure@first}
       </ul>
           {/if}
	   <ul class="{if !$structure@first}col-md-4{/if} {$structure.categoryCodeName}">
       {/if}
       {assign "structureCategory" $structure.categoryId}
    		<li class="animated fadeInLeft {if $structure.isCrafted}crafted{else if !$structure.isAvailable}notAvailable{else if $structure.component.quantity < $structure.componentQuantity}notCraftable{else}craftable{/if}" data-structure="{$structure.id}">
    			<h2>
        {if !$structure.isAvailable}
                <span data-toggle="tooltip" data-placement="top" title="Requis" data-content="{foreach $structure.requiredStructureList as $requiredStructure}
                    {$requiredStructure.name}
            {/foreach}
                ">?</span>
        {/if}
    			    {$structure.name}
			    </h2>
		{if isset($structure.bestScore)}
			    <p>Meilleur score: {$structure.bestScore}</p>
		{/if}
		{if !$structure.isCrafted && $structure.isCraftable && $structure.isAvailable}
		         <p class="component{if $structure.component.quantity < $structure.componentQuantity} disabled{/if}">
		              <span class="icon {$structure.component.codeName}">{$structure.component.name}</span>
		              {$structure.component.quantity}/{$structure.componentQuantity}
		         </p>
     	{/if}
     	{if $structure.isAvailable && !$structure.isPlayable}
			     <span class="btn btn-default">Pas encore disponible</a>
     	{else if $structure.isAvailable}
			     <a href="play.php?structureId={$structure.id}" title="" class="btn btn-default">{if $structure.hasPlayed}Rej{else}J{/if}ouer</a>
		{/if}
		{if !$structure.isCrafted && $structure.isCraftable && $structure.isAvailable && $structure.component.quantity >= $structure.componentQuantity}
	                  <a href="play.php?structureId={$structure.id}&amp;craft=1" title="" class="btn btn-default">Crafter</a>
        {/if}
        {if $structure.isCrafted}
        
        {/if}
		      </li>
	{/foreach}
	</ul>
</div>
<div class="col-xs-4">
	<table class="table table-striped table-hover table-condensed animated fadeInDown delayp5">
		<thead>
			<tr>
				<th>Joueur</th>
				<th>Crafté</th>
				<th>Score</th>
				<th>Temps</th>
			</tr>
		</thead>
		<tbody>
	{foreach $scoreList as $score}
			<tr>
				<td>{$score.player_name}</td>
				<td>{$score.crafted}</td>
				<td>{$score.score}</td>
				<td>{$score.time}</td>
			</tr>
	{/foreach}
		</tbody>
	</table>
	<ul class="componentList animated fadeInRight delayp1">
	{foreach $componentList as $component}
		<li class="col-xs-2{if $component@iteration % 3 !== 1} col-xs-offset-3{/if}"><span class="icon {$component.codeName}" data-toggle="tooltip" data-placement="top" title="{$component.name}">{$component.name}</span> {$component.quantity}</li>
	{/foreach}
	</ul>
	<div id="skeleton">
	{foreach $structureList as $structure}
		{if $structure.isCrafted}
		<img src="{$structure.image}" alt="" data-structure="{$structure.id}" />
		{/if}
	{/foreach}
	</div>
{else}
	<div class="col-md-8" id="presentation">
		<h1 class="title animated fadeInUp delayp1">Évaluez vos connaissances en anatomie en vous amusant!</h1>
		<ul class="animated fadeInUp delayp5">
			<li>Testez vos connaissances via des quizz composés de divers mini-jeux: QCM, textes à trous, schémas à légender, éléments à trouver...</li> 
			<li>En réussissant les défis, récupérez divers composants pour crafter les différentes parties du corps: os, ligaments, muscles... et reproduire un corps dans son intégralité</li>
			<li>Améliorez votre score et votre temps à chaque nouvel essai</li>
			<li>Comparez vos résultats avec vos amis, et parvenez au top du classement!</li>
		</ul>
	</div>
	<div class="col-xs-4" id="presentationCorpse">
		<img src="/images/corpse_1.png" alt="" class="animated fadeIn delayp5 long" />
		<img src="/images/corpse_2.png" alt="" class="animated fadeIn delayp1s long" />
		<img src="/images/corpse_3.png" alt="" class="animated fadeIn delayp15s long"/>
		<img src="/images/corpse_4.png" alt="" class="animated fadeIn delayp2s long" />
	</div>
</section>
{/if}
</div>

{if $isPlayerConnected && $player.isNew}
<!-- Modal -->
<div class="modal fade" id="tutorial" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Bienvenue sur Craft'anat</h4>
      </div>
      <div class="modal-body">
      	<p>Ce site vous permettra de vous entrainer et valider vos connaissances en anatomie par le biais de mini-jeux.</p>
      	<p>Lorsque vous remportez une épreuve, vous gagnez des composants en fonction de la structure sur laquelle vous vous entrainez et votre taux de réussite.</p>
		<ul class="componentList animated fadeInRight delayp1">
	{foreach $componentList as $component}
			<li><span class="icon {$component.codeName}">{$component.name}</span></li>
	{/foreach}
		</ul>
      	<p>Pour réussir une épreuve il faut obtenir au moins 60% de bonnes réponses.</p>
      	<p>Une fois que vous avez accumulé assez de composants vous pouvez crafter (fabriquer) des pièces du corps humain. Vous verrez alors le corps humain se dessiner petit à petit, et débloquerez de nouvelles structures et de nouveaux défis.</p>
      	<p>Vous pouvez rejouer à tous les jeux pour améliorer votre score et votre meilleur temps pour figurer dans le top du classement!</p>
      	<p></p>
      	<p>Toutes les structures ne sont pas encore disponible, le projet va continuer d'évoluer durant les prochains mois! N'hésitez pas à faire des retours ou des suggestions pour m'aider à améliorer ce site et le rendre encore plus ludique!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Commencer à jouer</button>
      </div>
    </div>
  </div>
</div>
{/if}
