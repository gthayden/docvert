<?php

include_once('lib.php');

function getConfigDirectory()
	{
	$configDirectory = null;
	if(DIRECTORY_SEPARATOR == '/')
		{
		$configDirectory = '/etc/docvert/';
		}
	else
		{
		$configDirectory = dirname(dirname(__file__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.'docvert.conf';
		}
	if(!is_writable($configDirectory))
		{
		webServiceError('The configuration directory is not writable at <tt>'.$configDirectory.'</tt>. ');
		}
	return $configDirectory;
	}

function getGlobalConfigPath()
	{
	$configPath = getConfigDirectory().'docvert.conf';
	if(file_exists($configPath))
		{
		if(!is_readable($configPath))
			{
			webServiceError('The configuration file is not readable at <tt>'.$configPath.'</tt>. Ask your administrator to check the permissions on that file.');
			}
		return $configPath;
		}
	initializeIniFile($configPath);
	return $configPath;
	}

function initializeIniFile($path)
	{
	$header = '; Docvert web service configuration.'."\n".'; Project homepage at <http://docvert.org>';
	file_put_contents($path, $header);
	}

function setConfigItem($configPath, $key, $value)
	{
	//todo sanitise key/value
	$currentValue = getConfigItem($configPath, $key);
	if(!is_writable($configPath))
		{
		webServiceError('The configuration file is not writable at <tt>'.$configPath.'</tt>. This may be intentional, ask your administrator.');
		}
	
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

function getConfigItem($configPath, $key)
	{
	$options = parse_ini_file(getGlobalConfigPath());
	if(array_key_exists($key, $options))
		{
		return $options[$key];
		}
	return null;
	}

function getGlobalConfigItem($key)
	{
	return getConfigItem(getGlobalConfigPath(), $key);
	}

function setGlobalConfigItem($key, $value)
	{
	return getConfigItem(getGlobalConfigPath(), $key, $value);
	}

?>
