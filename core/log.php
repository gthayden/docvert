<?php

$temporaryFile = tempnam('xxx', 'docvert');
$temporaryDirectoryPath = dirname($temporaryFile);
@unlink($temporaryFile);
$logTypes = Array('error', 'warning');
$firstLog = true;

if(isset($_GET['clear-logs']))
	{
	if($files = glob($temporaryDirectoryPath.DIRECTORY_SEPARATOR.'docvert*.txt'))
		{
		foreach($files as $file)
			{
			unlink($file);
			}
		}
	}

foreach($logTypes as $logType)
	{
	$logFilePath = $temporaryDirectoryPath.DIRECTORY_SEPARATOR.'docvert-'.$logType.'.txt';
	if(file_exists($logFilePath))
		{
		$contentOfLog = file_get_contents($logFilePath);
		if(trim($contentOfLog))
			{
			if($firstLog == true)
				{
				print '<div id="logs">';
				print '<h2>Docvert logs from previous conversions <span style="font-weight:normal">(<a href="?clear-logs" onclick="return confirm(\'Really erase logs?\')" style="color:blue">clear logs</a>)</span></h2>';
				$firstLog = false;
				}
			print '<h3>'.$logType.' log</h3>';
			print '<div class="logEntry">';
			print nl2br($contentOfLog);
			print '</div>';
			}
		}
	}
if($firstLog == false)
	{
	print '</div>';
	}

?>
