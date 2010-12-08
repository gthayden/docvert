<?php
if(!isset($_GET['path']))
	{
	die('This script is for displaying an HTML frameset and must be called with a URL parameter. It\'s not for direct access, it\'s called on document previews.');
	}

$pathToUse = ensureOnlyValidCharacters(urlDecode($_GET['path']));
$pathToUse = str_replace('\\', '/', $pathToUse).'/';
$pathToUse = str_replace('/', DIRECTORY_SEPARATOR, $pathToUse);
$thereIsAPreview = file_exists($pathToUse.'test.html');


$configFilenamesPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'custom-filenames.php';
include_once($configFilenamesPath);
$customFileNames = getCustomFilenames();


$chosenFile = null;
$filesToDisplay = Array($customFileNames[0], 'index.*', 'default.*', '*.odt');
foreach($filesToDisplay as $fileToDisplay)
	{
	$possibleFile = getFirstByPattern($pathToUse.$fileToDisplay);
	if($possibleFile)	
		{
		$chosenFile = $possibleFile;
		break;
		}
	}

if(!$chosenFile)
	{
	$filesToDisplayAsString = null;
	foreach($filesToDisplay as $fileToDisplay)
		{
		$filesToDisplayAsString .= '"'.$fileToDisplay.'", ';
		}
	$filesToDisplayAsString = trim($filesToDisplayAsString);
	$filesToDisplayAsString = substr($filesToDisplayAsString, 0, strlen($filesToDisplayAsString) - 1);
	$filesInPreviewDirectory = glob($pathToUse.'*');
	die('Docvert or pipeline error: Unable to determine the file to preview. I searched for the filename patterns '.$filesToDisplayAsString.' were tested but do not exist. Was given pathToUse of <tt>"'.$pathToUse.'"</tt> which contained <pre>'.revealXml(print_r($filesInPreviewDirectory, true)).'</pre>');
	}
$chosenFile = str_replace('\\', '/', $chosenFile);
if($thereIsAPreview)
	{
	print '<html>';
	print '<head>';
	print '<frameset cols="80%, *" border="10" bordercolor="#333333" frameborder="15">';
	print '<frame src="'.$chosenFile.'" id="contentFrame"/>';
	print '<frame src ="'.dirname($chosenFile).'/test.html"/>';
	print '</frameset>';
	print '</head>';
	print '<body><noframes>Docvert requires a frames-compatible browser</noframes></body>';
	print '</html>';
	}
else
	{
	print '<html>';
	print '<head>';
	print '<frameset cols="*">';
	print '<frame src="'.$chosenFile.'" id="contentFrame"/>';
	print '</frameset>';
	print '</head>';
	print '<body><noframes>Docvert requires a frames-compatible browser</noframes></body>';
	print '</html>';
	}

function getFirstByPattern($pattern)
	{
	$results = glob($pattern);
	//print $pattern.' = '.print_r($results, true).'<hr />';
	if(count($results))
		{
		return $results[0];
		}
	return null;
	}

function ensureOnlyValidCharacters($input)
	{
	$copyOfInput = $input;
	$copyOfInput = preg_replace('/[A-Za-z0-9]?/', '', $copyOfInput);
	$otherValidCharacters = array('_', '-', '(', ')', '/', '\\', '%20', '.', ',', '[', ']', '{', '}', '"', "'", '.', '&');
	$copyOfInput = trim(str_replace($otherValidCharacters, '', $copyOfInput));
	if($copyOfInput != '') die('Unable to display a path due to invalid characters: '.htmlentities($copyOfInput). '(from "'.htmlentities($input).'")');
	return $input;			
	}

?>
