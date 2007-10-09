<?php

$appDir = dirname($_SERVER["SCRIPT_FILENAME"]).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');

include_once('core/xslt.php');
include_once('core/lib.php');

$unescapedPosts = array();

foreach($_POST as $key => $value)
	{
	$unescapedPosts[$key] = $value;
	if(get_magic_quotes_gpc())
		{
		$unescapedPosts[$key] = stripslashes($unescapedPosts[$key]);
		}
	}
$badCharactersPattern = '/^[ A-Za-z0-9-_\\.\\(\\)\\[\\]]+$/s';

$unescapedPosts["allhtml"] = str_replace($unescapedPosts["pathToRemove"], '', $unescapedPosts["allhtml"]);

$docbookTitle = $unescapedPosts["documentTitle"];

$allhtml = $unescapedPosts["allhtml"];

$documentPath = $unescapedPosts["documentPath"];
$documentPath = str_replace('/', DIRECTORY_SEPARATOR, $documentPath);
$documentPath = str_replace('\\', DIRECTORY_SEPARATOR, $documentPath);
$documentPath = str_replace('..', '.', $documentPath);
$documentPathParts = explode(DIRECTORY_SEPARATOR, $documentPath);
if(count($documentPathParts) != 2)
	{
	webServiceError('documentPath contains too many fragments');
	}
if(!preg_match($badCharactersPattern, $documentPathParts[0]) || !preg_match($badCharactersPattern, $documentPathParts[1]) )
	{
	webServiceError('documentPath contains bad characters. Was "'.revealXml($documentPath).'"');
	}

$pipeline = $unescapedPosts["pipeline"];
if(!preg_match($badCharactersPattern, $pipeline))
	{
	webServiceError('pipeline contains bad characters. Was "'.revealXml($pipeline).'"');
	}
$autopipeline = $unescapedPosts["autopipeline"];
if(!preg_match($badCharactersPattern, $autopipeline))
	{
	webServiceError('autopipeline contains bad characters. Was "'.revealXml($autopipeline).'"');
	}

$allhtml = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><body>'.$allhtml.'</body></html>';

$transformDirectory = dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'transform'.DIRECTORY_SEPARATOR;

$htmlPagesXsltPath = $transformDirectory.'htmlpages-to-html.xsl';

$allhtml = characterEntityToNCR($allhtml);

$html = xsltTransform($allhtml, $htmlPagesXsltPath);

if(!$html)
	{
	webServiceError("Unable to rebuild document, either because I was given invalid XML or there was a programming error.<hr />Document was:<blockquote>".str_replace("\n", "<br />", str_replace(" ", '&nbsp;', revealXml($allhtml)))."</blockquote>");
	}

//displayXmlString($html);

$docbookBodyXsltPath = $transformDirectory.'html-to-docbook-body.xsl';
$docbookBody = xsltTransform($html, $docbookBodyXsltPath);
$docbookBody = removeXmlDeclaration($docbookBody);



$docbookBody = preg_replace("/<docvert-remove-me[^>]*?>/", '', $docbookBody);
$docbookBody = preg_replace("/<\\/docvert-remove-me[^>]*?>/", '', $docbookBody);

//displayXmlString($docbookBody);
//displayXmlString(file_get_contents($docbookBodyXsltPath));

$allDocumentsPreviewDirectory = dirname(__FILE__).DIRECTORY_SEPARATOR.'writable'.DIRECTORY_SEPARATOR.$documentPathParts[0].DIRECTORY_SEPARATOR;

$previewDirectory = $allDocumentsPreviewDirectory.$documentPathParts[1].DIRECTORY_SEPARATOR;




$unitTestResults = $previewDirectory.'test.html';
if(file_exists($unitTestResults))
	{
	silentlyUnlink($unitTestResults);
	}

$zipsInPreviewDirectory = glob($allDocumentsPreviewDirectory.'*.zip');
if(count($zipsInPreviewDirectory))
	{
	foreach($zipsInPreviewDirectory as $zipInPreviewDirectory)
		{
		silentlyUnlink($zipInPreviewDirectory);
		if(file_exists($zipInPreviewDirectory))
			{
			webServiceError('Docvert internal error: unable to remove ZIP file at "'.$zipInPreviewDirectory.'"');
			}
		}
	$zipFilePath = $zipsInPreviewDirectory[0];
	}
else
	{
	$zipFileName = chooseNameOfZipFile($allDocumentsPreviewDirectory);
	$zipFilePath = $allDocumentsPreviewDirectory.$zipFileName;
	}

$filesInPreviewDirectory = glob($previewDirectory.'*');
foreach($filesInPreviewDirectory as $fileInPreviewDirectory)
	{
	if	(
		!stringStartsWith(basename($fileInPreviewDirectory), "docvert") &&
		!stringEndsWith(basename($fileInPreviewDirectory), "wmf") &&
		!stringEndsWith(basename($fileInPreviewDirectory), "gif") &&
		!stringEndsWith(basename($fileInPreviewDirectory), "png") &&
		!stringEndsWith(basename($fileInPreviewDirectory), "jpeg") &&
		!stringEndsWith(basename($fileInPreviewDirectory), "jpg") &&
		!stringEndsWith(basename($fileInPreviewDirectory), "svg")
		)
			{
			//print 'Delete: '.$fileInPreviewDirectory.'<br />';
			silentlyUnlink($fileInPreviewDirectory);
			}
		else
			{
			//print 'Retain: '.$fileInPreviewDirectory.'<br />';
			}
	}



$docbookPath = $previewDirectory.'docvert--all-docbook.xml';
$docbook = file_get_contents($docbookPath);
$docbook = str_replace('{{body}}', $docbookBody, $docbook);
$docbook = str_replace('{{title}}', $docbookTitle, $docbook);

$contentPath = $previewDirectory.'content.xml';
file_put_contents($contentPath, $docbook);

$pipelineToUse = $pipeline;
$autoPipeline = $autopipeline;
$skipAheadToDocbook = true;

$pipelinePreviewDirectory = 'writable'.DIRECTORY_SEPARATOR.$documentPathParts[0];

applyPipeline($contentPath, $pipelineToUse, $autoPipeline, $pipelinePreviewDirectory, $skipAheadToDocbook);

zipFiles($allDocumentsPreviewDirectory, $zipFilePath);

$urlParamsArray = array(
	'preview' => $documentPathParts[0]
	);

//'pipeline' => $pipelineToUse,
//'autopipeline' => $autopipeline

$urlParams = "";
foreach($urlParamsArray as $key => $value)
	{
	if($urlParams)
		{
		$urlParams .= '&';
		}
	$urlParams .= rawurlencode($key).'='.rawurlencode($value);
	}

header("Location: web-service.php?".$urlParams);

?>
