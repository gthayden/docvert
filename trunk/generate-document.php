<?php
ob_start();
$appDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');
include('core/lib.php');
if(!isset($_REQUEST['pages'])) webServiceError('Docvert expected an array/list of "pages" form values listing URLs.');
elseif(count($_REQUEST['pages']) == 0) webServiceError('Docvert expected at least one item in an array/list of "pages" form values.');

if(!isset($_REQUEST['generatorPipeline']))
	{
	webServiceError('Docvert expected a "generatorPipeline" form value.');
	}
return generateDocument($_REQUEST['pages'], $_REQUEST['generatorPipeline']);
?>
