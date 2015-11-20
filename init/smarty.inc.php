<?php
// Inits Smarty template with correct paths
require_once('Smarty.class.php');

// Initialize smarty
$smarty = new Smarty();
$smarty->setCompileCheck(ENV_LEVEL < ENV_LEVEL_PROD ? TRUE : FALSE);
$smarty->debugging = FALSE;
$smarty->template_dir = SMARTY_TEMPLATES_DIR;
$smarty->compile_dir = SMARTY_TEMPLATES_C_DIR;
$smarty->setCacheDir(SMARTY_CACHE_DIR);
?>
