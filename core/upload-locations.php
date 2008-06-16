<?php
include_once('lib.php');
include_once('config.php');

function getUploadLocations()
	{
	$uploadLocations = Array();
	$uploadConfigsPathPattern = getConfigDirectory().'upload-*.conf';
	$uploadConfigs = glob($uploadConfigsPathPattern);
	if($uploadConfigs === False) return $uploadLocations;
	foreach($uploadConfigs as $uploadConfig)
		{
		$uploadId = basename($uploadConfig, '.conf');
		$uploadId = substr($uploadId, 7);
		$uploadLocations[$uploadId] = parse_ini_file($uploadConfig);
		}
	return $uploadLocations;
	}

function getUploadLocation($uploadId)
	{
	//todo sanitise $name
	$uploadId = sanitiseStringToAlphaNumeric($uploadId);
	$uploadLocationPath = getConfigDirectory().'upload-'.$uploadId.'.conf';
	if(!file_exists($uploadLocationPath)) return null;
	return parse_ini_file($uploadLocationPath);
	}

function addUploadLocation($name, $protocol, $host, $port, $username, $password, $baseDirectory)
	{
	$name = sanitiseStringToAlphaNumeric($name);
	//todo sanitise $name
	$configDirectory = getConfigDirectory();
	$uploadLocationPath = $configDirectory.'upload-'.$name.'.conf';
	
	while(file_exists($uploadLocationPath))
		{
		$uploadLocationPath = $configDirectory.'upload-'.$name.'-'.rand(1, 1000).'.conf';
		}

	initializeIniFile($uploadLocationPath);
	setConfigItem($uploadLocationPath, 'name', $name);
	setConfigItem($uploadLocationPath, 'protocol', $protocol);
	setConfigItem($uploadLocationPath, 'host', $host);
	setConfigItem($uploadLocationPath, 'port', $port);
	setConfigItem($uploadLocationPath, 'username', $username);
	setConfigItem($uploadLocationPath, 'password', $password);
	setConfigItem($uploadLocationPath, 'baseDirectory', $baseDirectory);
	}

function deleteUploadLocation($uploadId)
	{
	//todo sanitise $nam
	$uploadId = sanitiseStringToAlphaNumeric($uploadId);
	$uploadLocationPath = getConfigDirectory().'upload-'.$uploadId.'.conf';
	if(file_exists($uploadLocationPath))
		{
		unlink($uploadLocationPath);
		}
	}

?>
