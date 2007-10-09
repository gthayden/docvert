<?php

class DocBookToXHTML extends PipelineProcess
	{

	function process($currentXml)
		{
		if(!file_exists($this->docvertTransformDirectory.'docbook-to-html.xsl')) webServiceError(500, $this->docvertTransformDirectory.'docbook-to-html.xsl can\'t be found.');
		$currentXml = xsltTransform($currentXml, $this->docvertTransformDirectory.'docbook-to-html.xsl', $this->elementAttributes);
		return $currentXml;
		}

	}
			
?>
