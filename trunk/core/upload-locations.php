<?php
//[uploadid] => {{upload-id}} [protocol] => webdav [defaultPort] => on
//[customPort] => [username] => [password] => [basedirectory] => /var/www/

include_once('lib.php');

function getUploadLocationsPath()
	{
	$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
	$docvertWritableDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable';
	$uploadLocationsPath = $docvertWritableDir.DIRECTORY_SEPARATOR.'uploadlocations.php';
	return $uploadLocationsPath;
	}

function getUploadLocations()
	{
	$uploadLocationsPath = getUploadLocationsPath();
	$uploadLocations = array();
	if(file_exists($uploadLocationsPath))
		{
		if(is_readable($uploadLocationsPath))
			{
			include($uploadLocationsPath);
			}
		else
			{
			webServiceError('The upload locations configuration exists but is unreadable. Change the permissions of the file at <tt>"'.$uploadLocationsPath.'"</tt>');
			}
		}
	return $uploadLocations;
	}


function addUploadLocation($name, $protocol, $host, $port, $username, $password, $baseDirectory)
	{
	$uploadLocations = getUploadLocations();
	$uploadLocations[] = array('name' => $name, 'protocol' => $protocol, 'host' => $host, 'port' => $port, 'username' => $username, 'password' => $password, 'baseDirectory' => $baseDirectory);
	saveUploadLocations($uploadLocations);
	}

function saveUploadLocations($uploadLocations)
	{
	//print "<pre>";
	//print_r($uploadLocations);
	//print "</pre>";  
	$uploadLocationsPath = getUploadLocationsPath();
	$docvertWritableDir = dirname($uploadLocationsPath);
	if(!is_writable($docvertWritableDir))
		{
		$errorMessage = 'Cannot save upload location because the <tt>/writable</tt> directory is not actually writable. The writable directory is at <tt>"'.$docvertWritableDir.'"</tt>.';
		if(function_exists('webServiceError'))
			{
			webServiceError($errorMessage);
			}
		else
			{
			die($errorMessage);
			}
		}
	$uploadTemplate = '<'.'?'.'php'."\n$"."uploadLocations = array".'();'."\n{{body}}?".'>';
	$uploadTemplateItem = '$'."uploadLocations[]".' = array('."{{body}});\n";
	
	$fileBody = '';
	foreach($uploadLocations as $uploadLocation)
		{
		$uploadLocationArray = array();
		foreach($uploadLocation as $key => $value)
			{
			$uploadLocationArray[] = '"'.escapeValue($key).'" => "'.escapeValue($value).'"';
			}
		$uploadLocationValues = implode(', ', $uploadLocationArray);
		$fileBody .= str_replace('{{body}}', $uploadLocationValues, $uploadTemplateItem);
		}
	$fileData = str_replace('{{body}}', $fileBody, $uploadTemplate);
	file_put_contents($uploadLocationsPath, $fileData);
	chmod($uploadLocationsPath, 0600);
	}

function escapeValue($value)
	{
	$value = str_replace("\n", '', $value);
	$value = str_replace("\r", '', $value);
	$value = str_replace('\\', '\\\\', $value);
	$value = str_replace('"', '\\"', $value);
	return $value;
	}

?>
