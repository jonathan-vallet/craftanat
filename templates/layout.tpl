<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Craft'anat</title>

        <!-- Bootstrap core CSS -->
        <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic,700italic' rel='stylesheet' type='text/css'>
        <link href="{$smarty.const.CSS_DIR}bootstrap.min.css" rel="stylesheet">
        <link href="{$smarty.const.CSS_DIR}bootstrap-theme.min.css" rel="stylesheet">
        <link href="{$smarty.const.CSS_DIR}game.css" rel="stylesheet">
        <link href="{$smarty.const.CSS_DIR}animate.css" rel="stylesheet">
        <link href="{$smarty.const.CSS_DIR}theme.css" rel="stylesheet">
    </head>
    <body>
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/">Craft'anat</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
				{if $isPlayerConnected}
						<li><a href="/logout.php" title="">Se déconnecter</a></li>
					{if $player.isAdmin}
						<li><a href="/admin" title="">Administrer</a></li>
					{/if}
				{else}
						<li><a href="/register.php" title="">S'inscrire</a></li>
						<li><a href="/login.php" title="">Se connecter</a></li>
				{/if}
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>
		<section id="{$page}">
	        <div class="container">
	            {include file="$page.tpl"}
	        </div><!-- /.container -->
		</section>

        <!-- Bootstrap core JavaScript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src="{$smarty.const.JS_DIR}jquery.min.js"></script>
	<script src="{$smarty.const.JS_DIR}bootstrap.min.js"></script>
	<script src="{$smarty.const.JS_DIR}main.js"></script>
	<script src="{$smarty.const.JS_DIR}animations.js"></script>
	
{if isset($javascript)}
 	{foreach $javascript as $jsFile}
		<script type="text/javascript" src="{$smarty.const.JS_DIR}{$jsFile}.js"></script>
	{/foreach}
{/if}
        <script type="text/javascript">
           $('#gameList h2 span').popover({
                trigger: 'hover'
           });
           $('.componentList span.icon').tooltip({
                trigger: 'hover'
           });
           
			$('li.crafted').on('click', function(e) {
				$('#skeleton img').css('opacity', 0.2);
				$('#skeleton img[data-structure="' + $(this).attr('data-structure') + '"]').css('opacity', 1);
			});
			$('#tutorial').modal();

        </script>
    </body>
</html>