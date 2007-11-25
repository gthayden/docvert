<?php

class Test extends PipelineProcess
	{
	/*
	Provides unit tests that assert document structure with XSLT.
	*/

	function process($currentXml)
		{
		$this->performTest($currentXml);
		return $currentXml;
		}

	function performTest(&$currentXml)
		{
		if(!array_key_exists('withFile', $this->elementAttributes)) webServiceError('&error-process-test-withfile;');
		$xslPath = null;
		if(stripos($this->elementAttributes['withFile'], 'internal://') !== FALSE)
			{
			$xslPath = $this->docvertTransformDirectory.str_ireplace('internal://', '', $this->elementAttributes['withFile']);
			}
		else
			{
			$xslPath = $this->pipelineDirectory.$this->elementAttributes['withFile'];
			}
		if(!file_exists($xslPath)) webServiceError('&error-process-test-missing-xsl;', 500, Array('xslPath'=>$xslPath) );
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
