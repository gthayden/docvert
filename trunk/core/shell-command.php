<?php

/**
 * Run a shell command. This function returns STD_ERR as well unlike
 * PHPs inbuilt shell commands like shell_exec() or passthru() or system().
 * @return string
*/
function shellCommand($command, $timeoutInSeconds=120)
	{
	$out = null;

	//print __LINE__.': '.time().'<hr />';
	if (!($p=popen("($command)2>&1&","r")))
		{
     		return "";
		}
	//print __LINE__.': '.time().'<hr />';

	$endTime = microtime(true) + (float) $timeoutInSeconds;
	//print __LINE__.': '.time().'<hr />';
	while (!feof($p))
		{
		stream_set_timeout($p, $timeoutInSeconds);
		$line = fgets($p, 1024);
		$lastOut = $out;
		$out .= $line;
		if(microtime(true) > $endTime)
			{
			break;
			}
		}
	pclose($p);
	return $out;
	}


?>
