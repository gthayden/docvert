<?php
/*
 * Various scripts used by command line scripts
 *
 * included from command line scripts in /bin/...
*/
if(DOCVERT_CLIENT_TYPE != 'command line')
	{
	die('Only available via command line. Was accessed via '.DOCVERT_CLIENT_TYPE);
	}

$files = null;
$converter = null;
$pipeline = null;
$autoPipeline = null;
$afterConversion = 'saveZip';
$setupOpenOfficeOrg = false;
$outputZip = null;
$extraParameters = null;

$errorPlaceholders = Array();

$arguments = $argv;
array_shift($arguments);

foreach($arguments as $argument)
	{
	if(stringStartsWith($argument, '--input-files='))
		{
		$fileListSeperationCharacter = ',';
		$fileList = substringAfter($argument, '--input-files=');
		$givenFiles = explode($fileListSeperationCharacter, $fileList);
		foreach($givenFiles as $givenFile)
			{
			$files[] = array
				(
				'name' => basename($givenFile),
				'size' => filesize($givenFile),
				'type' => NULL,
				'tmp_name' => $givenFile
				);
			}
		}
	elseif(stringStartsWith($argument, '--output-zip='))
		{
		$outputZipPath = substringAfter($argument, '--output-zip=');
		//print $outputZipPath."\n";
		$outputZip = rawurldecode($outputZipPath);
		//print $outputZipPath."\n";
		if(!file_exists(dirname($outputZip)))
			{
			$extraParameters .= "&error-commandline-output-directory-does-not-exist;";
			}
		//print $outputZip."\n";
		}
	elseif(stringStartsWith($argument, '--pipeline='))
		{
		//die(substringAfter($argument, '--pipeline='));
		$pipelineName = rawurldecode(substringAfter($argument, '--pipeline='));
		if(!file_exists(DOCVERT_DIR.'pipeline'.DIRECTORY_SEPARATOR.$pipelineName))
			{
			$extraParameters .= '&error-commandline-no-pipeline;';
			$errorPlaceholders['pipeline'] = $pipelineName;
			}
		else
			{
			$pipeline = 'regularpipeline:'.$pipelineName;
			}
		}
	elseif(stringStartsWith($argument, '--autopipeline='))
		{
		$autoPipeline = rawurldecode(substringAfter($argument, '--autopipeline='));
		}
	elseif(stringStartsWith($argument, '--converter='))
		{
		$converter = substringAfter($argument, '--converter=');
		}
	else
		{
		$extraParameters .= 'Unknown argument: '.$argument;
		if(!stringStartsWith($argument, '--'))
			{
			$extraParameters .= '&error-commandline-forget-double-dash;';
			}
		$extraParameters .= "\n\n";
		}
	}

if(!$files || !$converter || !$pipeline || !$outputZip || $extraParameters)
	{
	$commandLineHelp = "\n".$extraParameters;
	$commandLineHelp .= '&error-command-line-help;';
	$errorPlaceholders['commandLineFiles'] = '';
	$errorPlaceholders['commandLineConverter'] = '';
	$errorPlaceholders['commandLinePipeline'] = '';
	$errorPlaceholders['commandLineOutputZip'] = '';
	if(!$files) $errorPlaceholders['commandLineFiles'] = ' --input-files ';
	if(!$converter) $errorPlaceholders['commandLineConverter'] = ' --converter';
	if(!$pipeline) $errorPlaceholders['commandLinePipeline'] = ' --pipeline';
	if(!$outputZip) $errorPlaceholders['commandLineOutputZip'] = ' --output-zip';
	webServiceError($commandLineHelp, 400, $errorPlaceholders);
	}


//print_r($files);
//print $converter;
//print $pipeline;
//print $autoPipeline;
//print $afterConversion;
//print $setupOpenOfficeOrg;
//print $outputZip;

processConversion($files, $converter, $pipeline, $autoPipeline, $afterConversion, $setupOpenOfficeOrg, $outputZip);

?>
