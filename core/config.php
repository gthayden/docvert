<?php

include_once('lib.php');

function getConfigDirectory()
	{
	$configDirectory = null;
	if(DIRECTORY_SEPARATOR == '/') //if(this is a unix)
		{
		$configDirectory = '/etc/docvert/';
		if(!file_exists($configDirectory)) $configDirectory = dirname(__file__).'/config/unix-specific/config/';
		}
	else
		{
		$configDirectory = dirname(__file__).'\\config\\windows-specific\\config\\';
		}
	return $configDirectory;
	}

function getGlobalConfigPath()
	{
	$configDirectory = getConfigDirectory();
	$configPath = $configDirectory.'docvert.conf';
	if(file_exists($configPath))
		{
		if(!is_readable($configPath))
			{
			webServiceError('&error-config-file-not-readable;', 500, Array('path'=>$configDirectory) );
			}
		return $configPath;
		}
	initializeIniFile($configPath);
	return $configPath;
	}

function initializeIniFile($path)
	{
	$configDirectory = getConfigDirectory();
	if(!file_exists($configDirectory))
		{
		webServiceError('&error-config-directory-not-available;', 500, Array('path'=>$configDirectory));
		}
	if(!is_writable($configDirectory))
		{
		webServiceError('&error-config-file-not-writable;', 500, Array('path'=>$configDirectory));
		}
	$header = '; Docvert web service configuration.'."\n".'; Project homepage at <http://docvert.org>';
	file_put_contents($path, $header);
	chmod($path, 0600); //security!
	}

function setConfigItem($configPath, $key, $value)
	{
	$key = sanitiseStringToAlphaNumeric($key);
	$value = sanitiseToIniValue($value);

	$currentValue = getConfigItem($configPath, $key);
	if(!is_writable($configPath))
		{
		webServiceError('&error-config-file-not-writable;', 500, Array('path'=>$configPath));
		}
	
	$newConfigItemLine = $key.'="'.$value.'"';
	if($currentValue === null) // no previous value, just append
		{
		$iniFilePointer = fopen($configPath, 'a');
		fwrite($iniFilePointer, "\n".$newConfigItemLine."\n");
		fclose($iniFilePointer);
		chmod($configPath, 0600); //security!
		return;
		}
	$iniLines = file($configPath);
	$newIniLines = Array();
	$replaceExistingLine = False;
	foreach($iniLines as $iniLine)
		{
		if(stringStartsWith($iniLine, $key) && ($currentValue == null || containsString($iniLine, $currentValue)))
			{
			$newIniLines[] = $newConfigItemLine."\n";
			}
		elseif(trim($iniLine) != '')
			{
			$newIniLines[] = $iniLine;
			}
		}
	file_put_contents($configPath, implode('', $newIniLines));
	chmod($configPath, 0600); //security!
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
	return setConfigItem(getGlobalConfigPath(), $key, $value);
	}


function getWritableDirectory()
	{
	$defaultWritableDirectory = dirname(dirname(__file__)).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR;
	if(is_writable($defaultWritableDirectory)) return $defaultWritableDirectory;
	return getOperatingSystemsTemporaryDirectory();
	}

function getOperatingSystemsTemporaryDirectory()
	{
	if(defined('OPERATING_SYSTEM_TEMPORARY_DIRECTORY')) return OPERATING_SYSTEM_TEMPORARY_DIRECTORY;
	$directoriesToCheck = Array();
	if(isset($_ENV))
		{
		if(isset($_ENV['TMPDIR'])) $directoriesToCheck[] = $_ENV['TMPDIR'];
		if(isset($_ENV['TMP'])) $directoriesToCheck[] = $_ENV['TMP'];
		}
	if(DIRECTORY_SEPARATOR == '/') //if(this is a unix)
		{
		$directoriesToCheck[] = '/tmp/';
		}
	else
		{
		$directoriesToCheck[] = '\\temp\\';
		$directoriesToCheck[] = '\\windows\\temp\\';
		}

	foreach($directoriesToCheck as $directoryToCheck)
		{
		if(is_writable($directoryToCheck))
			{
			define('OPERATING_SYSTEM_TEMPORARY_DIRECTORY', $directoryToCheck);
			return OPERATING_SYSTEM_TEMPORARY_DIRECTORY;
			}
		}
	webServiceError('&error-config-file-not-writable;', 500, Array('path'=>implode(', ', $directoriesToCheck)) );
	}

function getSuperUserPreference()
	{
	$superUserPreference = 'sudo';
	$customSuperUserPreference = getGlobalConfigItem('superUserPreference');
	if($customSuperUserPreference) $superUserPreference = $customSuperUserPreference;
	return $superUserPreference;
	}

?>
