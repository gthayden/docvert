<?php

class SplitPages extends PipelineProcess
	{

	function process($currentXml)
		{
		$configFilenamesPath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'custom-filenames.php';
		include_once($configFilenamesPath);
		$customFileNames = getCustomFilenames();
		$xslAttributes = array
			(
			'loopDepth' => $this->loopDepth,
			'process' => $this->elementAttributes['process'],
			'customFilenameIndex' => $customFileNames[0],
			'customFilenameSection' => $customFileNames[1]
			);
		$currentXml = xsltTransform($currentXml, $this->docvertTransformDirectory.'each-page.xsl', $xslAttributes);
		return $currentXml;
		}
	}
			
?>
