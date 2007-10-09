<?php
/*
 * Various scripts used by command line scripts
 *
 * included from command line scripts in /bin/...
*/

$files = null;
$converter = null;
$pipeline = null;
$autoPipeline = null;
$afterConversion = 'saveZip';
$setupOpenOfficeOrg = false;
$outputZip = null;
$extraParameters = null;

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
			$extraParameters .= "ERROR: output directory does not exist";
			}
		//print $outputZip."\n";
		}
	elseif(stringStartsWith($argument, '--pipeline='))
		{
		//die(substringAfter($argument, '--pipeline='));
		$pipelineName = rawurldecode(substringAfter($argument, '--pipeline='));
		if(!file_exists(DOCVERT_DIR.'pipeline'.DIRECTORY_SEPARATOR.$pipelineName))
			{
			$extraParameters .= 'Requested pipeline "'.$pipelineName.'" doesn\'t exist.'."\n";
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
			$extraParameters .= '. Did you forget the double-dash "--" prefix?';
			}
		$extraParameters .= "\n\n";
		}
	}

if(!$files || !$converter || !$pipeline || !$outputZip || $extraParameters)
	{
	$commandLineHelp = "\n".$extraParameters;
	$commandLineHelp .= 'Was not given these required parameters: :';
	if(!$files) $commandLineHelp .= ' --files ';
	if(!$converter) $commandLineHelp .= ' --converter';
	if(!$pipeline) $commandLineHelp .= ' --pipeline';
	if(!$outputZip) $commandLineHelp .= ' --output-zip';
	$commandLineHelp .= "\n";
	$commandLineHelp .= 'Here\'s the syntax you\'ll need to use: '."\n";
	$commandLineHelp .= ' --input-files=paths seperated by commas (URL encoded, aside from the comma)'."\n";
	$commandLineHelp .= ' --output-zip=path (URL encoded)'."\n";
	$commandLineHelp .= ' --pipeline=name of pipeline (URL encoded)'."\n";
	$commandLineHelp .= ' --converter=(openofficeorg | abiword)'."\n";
	$commandLineHelp .= 'For autopipelines this is also required:'."\n";
	$commandLineHelp .= ' --autopipeline=name of autopipeline (URL encoded)'."\n";
	$commandLineHelp .= 'Example:'."\n";
	$commandLineHelp .= '   /var/www/docvert/bin/odt2html --input-files=fullpath1,fullpath2 --output-zip=/home/marco/result.zip --pipeline=s5%20slideshow --converter=openofficeorg '."\n";
	webServiceError($commandLineHelp, 400);
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
