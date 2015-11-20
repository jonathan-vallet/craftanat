<form action="/register.php" role="form" method="post" class="col-md-6">
	<h1>Inscription</h1>
{if !empty($errorList)}
	<ul class="error">
	{foreach $errorList as $error}
		<li>{$error}</li>		
	{/foreach}
	</ul>
{/if}
	<p class="form-group">
		<input type="text" name="login" value="{$login}" placeholder="Login" class="form-control" />
	</p>
	<p class="form-group">
		<input type="email" name="email" value="{$email}" placeholder="Adresse e-mail" class="form-control" />
	</p>
	<p class="form-group">
		<input type="password" name="password" value="" placeholder="Mot de passe" class="form-control" />
	</p>
	<p class="form-group">
		<input type="password" name="passwordConfirmation" value="" placeholder="Confirmez le mot de passe" class="form-control" />
	</p>
	<p class="form-group">
		<input type="text" name="name" value="{$name}" placeholder="Votre nom" class="form-control" />
	</p>
	<p class="checkbox">
		<label>
			Êtes-vous étudiant en ostéopathie?
			<input type="checkbox" name="isStudent" value="1"{if $isStudent} checked="checked"{/if} />
		</label>
	</p>
	<p class="form-group">
		<input type="submit" value="Valider l'inscription" class="btn btn-primary" />
	</p>
</form>