<!doctype html>
<html lang="fr">
	<head>
		<title>Osteo!</title>
		<meta charset="UTF-8">
        <link href="{$smarty.const.CSS_DIR}bootstrap.min.css" rel="stylesheet">
        <link href="{$smarty.const.CSS_DIR}bootstrap-theme.min.css" rel="stylesheet">
		<link rel="stylesheet" title="Default" href="{$smarty.const.CSS_DIR}admin.css" type="text/css" />
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
                    <a class="navbar-brand" href="#">Craft'anat Admin</a>
                </div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
						<li><a href="/admin/structureList.php" title="">Gérer les structures</a></li>
						<li><a href="/admin/questionList.php" title="">Gérer les questions</a></li>
						<li><a href="/" title="">Retour au site</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>
		<div class="container">
			{include file="$page.tpl"}
		</div>
{if isset($javascript)}
		<script type="text/javascript">
		IMAGE_PATH = '{$smarty.const.IMAGE_PATH}';
		</script>
	{foreach $javascript as $jsFile}
		<script type="text/javascript" src="{$smarty.const.JS_DIR}{$jsFile}.js"></script>
	{/foreach}
{/if}
	</body>
</html>