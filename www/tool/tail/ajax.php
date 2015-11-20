<?php
// Starts session before header is sent
session_start();

require_once('../../../init/initLog.inc.php');

$currentDay = DateTool::getTimeString(DateTool::FORMAT_MYSQL_DATE);
define('TAIL_LOG_FILE', LOG_DIR . '/' . $currentDay . '-web.log');

$grep = RequestTool::getParameter('grep', RequestTool::PARAM_TYPE_FREE, false);
$logFile = file_exists(TAIL_LOG_FILE) ? file(TAIL_LOG_FILE) : array();

$starterLine = SessionTool::getInstance()->getParameter('startLine', false, 0);
$lineCount = count($logFile);

for ($index = $starterLine; $index < $lineCount; ++$index)
{
	// Filter lines per grep
	if($grep && !StringTool::strpos($logFile[$index], $grep))
		continue;

	$truncate = true;
	switch (StringTool::substr($logFile[$index], 0, 7))
	{
		case "\033[1;32m":
			$color = '#5F5'; // Green
			break;
		case "\033[1;33m":
			$color = '#FF5'; // Yellow
			break;
		case "\033[0;31m":
			$color = '#F55'; // Red
			break;
		case "\033[1;34m":
			$color = '#55F'; // Blue
			break;
		case "\033[1;36m":
			$color = '#5FF'; // Cyan
			break;
		case "\033[1;35m":
			$color = '#F5F'; // Purple
			break;
		case "\033[0;02m":
			$color = '#BBB'; // White
		default:
			$color = isset($color) ? $color : '#bbb';
			$truncate = false;
			break;
	}
?>
<p id="msg<?php echo $index ?>" class="chatLine" style="color: <?php echo $color; ?>"><?php echo htmlspecialchars($truncate ? StringTool::truncateFirstChars($logFile[$index], 7) : $logFile[$index]); ?></p>
<?php
}

SessionTool::getInstance()->setParameter('startLine', $lineCount);
?>