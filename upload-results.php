<html>
<head>
<style type="text/css">
body {font-family: Helvetica, sans-serif;}
h1 {font-size:small;}
p {font-size:x-small;}
.errorMessages {margin:5px;padding:5px;border: solid 2px #cccccc;font-size:x-small;background:#eeeeee}
</style>
</head>
<body>
<?php
$appDir = dirname(__file__).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');
include_once('core'.DIRECTORY_SEPARATOR.'lib.php');
include_once('core'.DIRECTORY_SEPARATOR.'ftp.php');
include_once('core'.DIRECTORY_SEPARATOR.'http.php');
include_once('core'.DIRECTORY_SEPARATOR.'blogger-api.php');
include_once('core'.DIRECTORY_SEPARATOR.'upload-locations.php');

if(isset($_POST['uploadto']) && isset($_POST['id']))
	{
	$previewId = sanitiseStringToAlphaNumeric($_POST['id']);

	$uploadId = $_POST['uploadto'];
	$remoteDirectory = '';
	if(isset($_POST['remoteDirectory']))
		{
		$remoteDirectory = $_POST['remoteDirectory'];
		}

	$uploadLocation = getUploadLocation($uploadId);
	if($uploadLocation === null)
		{
		webServiceError('&error-upload-location-does-not-exist;');
		}

	$previewDirectory = realpath('writable'.DIRECTORY_SEPARATOR.$previewId);
	if(!file_exists($previewDirectory))
		{
		webServiceError('&error-upload-no-preview-directory;', 500, Array('previewDirectory'=>$previewDirectory) );
		}
	$errorHtml = uploadToUploadLocation($uploadLocation, $previewDirectory, $remoteDirectory);
	if(!$errorHtml)
		{
		webServiceError('&upload-successful;', 200);
		}
	else	
		{
		webServiceError($errorHtml);
		}
	// include webpage.php and display an upload completed webpage
	}
else
	{
	webServiceError('&error-upload-value-needs;', 500, Array('actualValue'=> print_r($_POST, True)) );
	}


function uploadToUploadLocation($uploadLocation, $previewDirectory, $remoteDirectory)
	{
	switch($uploadLocation['protocol'])
		{
		case 'ftp':
			return copyViaFtpRecursively($uploadLocation, $previewDirectory, $remoteDirectory, "active");
			break;
		case 'ftp-pasv':
			return copyViaFtpRecursively($uploadLocation, $previewDirectory, $remoteDirectory, "passive");
			break;
		case 'webdav':
		case 'webdav-tls':
			return copyViaWebDAVRecursively($uploadLocation, $previewDirectory, $remoteDirectory);
			break;
		case 'bloggerapi':
		case 'bloggerapi-ssl':
			return copyViaBloggerAPI($uploadLocation, $previewDirectory, $remoteDirectory);
			break;
		default:
			webServiceError('&error-unknown-protocol; '.$uploadLocation['protocol']);
			break;
		}
	}

?>
</body>
</html>
