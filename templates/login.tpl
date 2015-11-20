<form action="/login.php" role="form" method="post" class="col-md-6">
	<h1>Connexion</h1>
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
		<input type="password" name="password" value="" placeholder="Mot de passe" class="form-control" />
	</p>
	<p class="form-group">
		<input type="submit" value="Se connecter" class="btn btn-primary" />
	</p>
</form>