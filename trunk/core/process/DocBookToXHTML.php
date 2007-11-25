<?php

class DocBookToXHTML extends PipelineProcess
	{

	function process($currentXml)
		{
		$currentXml = xsltTransform($currentXml, $this->docvertTransformDirectory.'docbook-to-html.xsl', $this->elementAttributes);
		return $currentXml;
		}

	}
			
?>
