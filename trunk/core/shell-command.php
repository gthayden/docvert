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

function pipeToShellCommand($command, $dataToStdIn=null, $timeoutInSeconds=120, $haltOnError=true)
	{
	$descriptor = array( 0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w")	);
	$currentWorkingDirectory = getOperatingSystemsTemporaryDirectory();
	$envionmentVariables = array();
	$process = proc_open($command, $descriptor, $pipes, $currentWorkingDirectory, $envionmentVariables);
	if(!is_resource($process))
		{
		if($haltOnError) webServiceError($command);
		return Array('stdOut'=>null, 'statusCode'=>-1, 'stdErr'=>null);
		}
	if($dataToStdIn) fwrite($pipes[0], $dataToStdIn);
	fclose($pipes[0]);
	$stdOut = stream_get_contents($pipes[1]);
	$stdErr = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$statusCode = proc_close($process);
	if($statusCode != 0 && $haltOnError) webServiceError($statusCode.' '.$stdErr);
	return Array('stdOut'=>$stdOut, 'statusCode'=>$statusCode, 'stdErr'=>$stdErr);
	}

?>
