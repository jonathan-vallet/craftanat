<?php
require_once '../../init/class_loader.inc.php';
require_once '../../config/constants.php';

$globalsDir = '../../globals';

// Creates folder if needed
if(!is_dir($globalsDir))
	if(!mkdir($globalsDir))
		exit('Le dossier n\'a pas pu �tre cr��');

if(!is_writable($globalsDir . '/globals.php'))
	exit('Le fichier ne peut pas être lu');
		
$globalsFile = fopen($globalsDir . '/globals.php', 'w');

// Adds anchors
fwrite($globalsFile, "<?php\n");


foreach($constantList as $constantName => $constantValue)
{
	if($constantValue === true)
		$value = 'true';
	else if($constantValue === false)
		$value = 'false';
	else
		$value = (StringTool::isFloat($constantValue) || StringTool::isInt($constantValue)) ? $constantValue : '\'' . $constantValue . '\'';

	fwrite($globalsFile, 'define(\'' . $constantName . '\', ' . $value . ");\n");
}

// Adds anchors
fwrite($globalsFile, '?>');

fclose($globalsFile);

echo 'Globals generated<br>';
echo '<a href="../game.php">Back to game</a>';
?>