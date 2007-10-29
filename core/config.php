<?php

include_once('lib.php');

function getConfigPath()
	{
	$configPath = null;
	if(DIRECTORY_SEPARATOR == '/')
		{
		$configPath = '/etc/docvert/docvert.conf';
		}
	else
		{
		$configPath = dirname(dirname(__file__)).'/writable/docvert.conf';
		}
	if(file_exists($configPath))
		{
		return $configPath;
		}
	// doesn't exist, initialize file
	if(!is_writable(dirname($configPath)))
		{
		webServiceError('The configuration directory is not writable at <tt>'.$configPath.'</tt>.');
		}
	$configHeader = '; Docvert web service configuration.'."\n".'; Project homepage at <http://docvert.org>';
	file_put_contents($configPath, $configHeader);
	return $configPath;
	}

function setConfigItem($key, $value)
	{
	//todo sanitise key/value
	$currentValue = getConfigItem($key);
	$configPath = getConfigPath();
	$newConfigItemLine = $key.'="'.$value.'"';
	if($currentValue === null) // no previous value, just append
		{
		$iniFilePointer = fopen($configPath, 'a');
		fwrite($iniFilePointer, "\n".$newConfigItemLine);
		fclose($iniFilePointer);
		return;
		}
	$iniLines = file($configPath);
	$newIniLines = Array();
	$replaceExistingLine = False;
	foreach($iniLines as $iniLine)
		{
		if(stringStartsWith($iniLine, $key) && containsString($iniLine, $currentValue))
			{
			$newIniLines[] = $newConfigItemLine."\n";
			}
		elseif(trim($iniLine) != '')
			{
			$newIniLines[] = $iniLine;
			}
		}
	file_put_contents($configPath, implode('', $newIniLines));
	}

function getConfigItem($key)
	{
	$options = parse_ini_file(getConfigPath());
	if(array_key_exists($key, $options))
		{
		return $options[$key];
		}
	return null;
	}

if(isset($arg) and count($arg) > 0) // if called from command line
	{
	//
	}

?>
