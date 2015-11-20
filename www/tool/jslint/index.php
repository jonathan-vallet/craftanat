<?php
require_once('init/init.inc.php');

require_once('private/JSLEngine.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<style type="text/css">
		body
		{
			background: black;
			color: white;
		}
	</style>
</head>
<body>
<?php
$lint=new JSLEngine();

function readDirectory($dirName)
{	
	$fileList = array();
	$dir = opendir($dirName);
	while($file = readdir($dir))
	{
		if($file != '.' && $file != '..' && $file != 'ext')
		{
			if(is_dir($dirName . '/' . $file))
				$fileList = array_merge($fileList, readDirectory($dirName. '/' . $file));
			else if(StringTool::endsWith($file, '.js'))
				$fileList[] = $dirName . '/' . $file;
		}
	}
	return $fileList;
}
echo '<pre>';
$fileList = readDirectory(dirname(__FILE__) . '/../../js');
foreach($fileList as $file)
{
	echo '<h2>' . $file . '</h2>';
	$js = file_get_contents($file);
	if (!$lint->Lint($js))
	{
	    echo "bad js code! full output:\n";
	    echo $lint->output();
	}
	else
		 echo $lint->output();
}
?>
</body>
</html>