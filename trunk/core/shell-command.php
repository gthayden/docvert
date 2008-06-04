<?php

/**
 * Run a shell command. This function returns STD_ERR as well unlike
 * PHPs inbuilt shell commands like shell_exec() or passthru() or system().
 * @return string
*/
function shellCommand($command, $timeoutInSeconds=null, $dataToStdIn=null, $haltOnError=false)
	{
	if($timeoutInSeconds === null) $timeoutInSeconds=120;
	$pipes = null;
	if($dataToStdIn)
		{
		$descriptor = array(0=>array("pipe", "r"), 1=>array("pipe", "w"), 2=>array("pipe", "w") );
		$currentWorkingDirectory = getOperatingSystemsTemporaryDirectory();
		$envionmentVariables = array();
		$process = proc_open($command, $descriptor, $pipes, $currentWorkingDirectory, $envionmentVariables);
		fwrite($pipes[0], $dataToStdIn);
		fclose($pipes[0]);
		stream_set_timeout($pipes[1], $timeoutInSeconds);
		stream_set_timeout($pipes[2], $timeoutInSeconds);
		}
	else
		{
		$process = popen("($command)2>&1&","r");
		//stream_set_timeout($process, $timeoutInSeconds);		
		if($timeoutInSeconds == 0) return;
		$pipes[] = $process;
		}


	if(!is_resource($process))
		{
		if($haltOnError) webServiceError($command);
		if(!$dataToStdIn) return null;
		return Array('stdOut'=>null, 'statusCode'=>-1, 'stdErr'=>null);
		}

	$response = Array();
	$endTime = microtime(true) + (float) $timeoutInSeconds;
	foreach($pipes as $pipe)
		{
		if(!is_resource($pipe)) continue;
		$returnValue = null;
		while (!feof($pipe))
			{
			$returnValue .= fgets($pipe, 8);
			$streamInfo = stream_get_meta_data($pipe);
			if($streamInfo['timed_out'] === true || microtime(true) > $endTime)
				{
				$returnValue .= 'Docvert timeout';
				break;
				}
			}
		$response[] = $returnValue;
		pclose($pipe);
		}
	
	if(!$dataToStdIn)
		{
		return $response[0];
		}
	else
		{
		$statusCode = proc_close($process);
		if($statusCode !== 0 && $haltOnError) webServiceError($statusCode.' '.implode(' ', $pipes) );
		return Array('stdOut'=>$response[0], 'statusCode'=>$statusCode, 'stdErr'=>$response[1]);
		}
	}

?>
