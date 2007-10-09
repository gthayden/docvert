<?php

class Test extends PipelineProcess
	{

	function process($currentXml)
		{
		$this->performTest($currentXml);
		return $currentXml;
		}

	function performTest(&$currentXml)
		{
		if(!array_key_exists('withFile', $this->elementAttributes)) webServiceError('A pipeline\'s test stage doesn\'t name an XSLT file. It should have a withFile attribute containing a filename relative to the pipeline directory.');
		$xslPath = null;
		if(stripos($this->elementAttributes['withFile'], 'internal://') !== FALSE)
			{
			$xslPath = $this->docvertTransformDirectory.str_ireplace('internal://', '', $this->elementAttributes['withFile']);
			}
		else
			{
			$xslPath = $this->pipelineDirectory.$this->elementAttributes['withFile'];
			}
		if(!file_exists($xslPath)) webServiceError('A pipeline\'s transform stage refers to an XSL file that doesn\'t exist. There is no file (or I don\'t have permissions to read it) at: <tt>'.$xslPath.'</tt>');
		$xslAttributes = array
			(
			'loopdepth' => $this->loopDepth,
			);
		$testResults = xsltTransform($currentXml, $xslPath, $xslAttributes);
		if(trim($testResults))
			{
			$testResultsPath = $this->contentDirectory.DIRECTORY_SEPARATOR.'test.html';
			file_put_contents($testResultsPath, $testResults, FILE_APPEND);
			}
		}

	}

?>
