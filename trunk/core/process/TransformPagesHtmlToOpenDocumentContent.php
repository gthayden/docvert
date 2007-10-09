<?php

class TransformPagesHtmlToOpenDocumentContent extends PipelineProcess
	{

	function process($currentXml)
		{
		return xsltTransform($currentXml, $this->docvertTransformDirectory.'html-to-opendocument.xsl');
		}

	}
?>
