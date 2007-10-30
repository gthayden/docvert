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
$appDir = dirname($_SERVER["SCRIPT_FILENAME"]).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');
include_once('core/lib.php');
include_once('core/ftp.php');
include_once('core/http.php');
include_once('core/upload-locations.php');

if(isset($_POST['uploadto']) && isset($_POST['id']))
	{
	$previewId = $_POST['id'];

	$uploadId = $_POST['uploadto'];
	$remoteDirectory = '';
	if(isset($_POST['remoteDirectory']))
		{
		$remoteDirectory = $_POST['remoteDirectory'];
		}

	$uploadLocation = getUploadLocation($uploadId);
	if($uploadLocation === null)
		{
		webServiceError('This upload location does not exist.');
		}

	$previewDirectory = realpath('writable'.DIRECTORY_SEPARATOR.$previewId);
	if(!file_exists($previewDirectory))
		{
		die('No preview directory at "'.$previewDirectory.'". The directory may have been cleaned away.');
		}
	$errorHtml = uploadToUploadLocation($uploadLocation, $previewDirectory, $remoteDirectory);
	if(!$errorHtml)
		{
		print "<h1>Upload successful</h1><p>No errors reported.</p>";
		}
	else	
		{
		webServiceError($errorHtml);
		}
	// include webpage.php and display an upload completed webpage
	}
else
	{
	webServiceError('upload-results.php needs post variables of "uploadto", "remoteDirectory", and "id".<hr /> Was: '.print_r($_POST, True));
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
			return copyViaWebDAVRecursively($uploadLocation, $previewDirectory, $remoteDirectory);
			break;
		case 'webdav-ssl':
			return copyViaWebDAVRecursively($uploadLocation, $previewDirectory, $remoteDirectory);
			break;
		case 'webdav-tls':
			return copyViaWebDAVRecursively($uploadLocation, $previewDirectory, $remoteDirectory);
			break;
		default:
			die('Unknown protocol '.$uploadLocation['protocol']);
			break;
		}
	}

?>
</body>
</html>
