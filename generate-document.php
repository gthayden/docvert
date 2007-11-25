<?php
ob_start();
$appDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');
include('core/lib.php');
if(!isset($_REQUEST['pages'])) webServiceError('&error-generator-expected-pages-parameter;');
elseif(count($_REQUEST['pages']) == 0) webServiceError('&error-generator-expected-pages-parameter;');

if(!isset($_REQUEST['generatorPipeline']))
	{
	webServiceError('&error-generator-expected-generatorpipeline-parameter;');
	}
return generateDocument($_REQUEST['pages'], $_REQUEST['generatorPipeline']);
?>
