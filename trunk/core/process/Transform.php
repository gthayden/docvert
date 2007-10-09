<?php

/** 
  * apply an XSLT transform to pipeline content
  */
class Transform extends PipelineProcess 
	{
	
	public function process($currentXml)
		{
		if(!array_key_exists('withFile', $this->elementAttributes)) webServiceError('A pipeline\'s transform stage doesn\'t name an XSLT file. It should have a withFile attribute containing a filename relative to the pipeline directory.');
		$xslPath = $this->pipelineDirectory.$this->elementAttributes['withFile'];
		if(!file_exists($xslPath)) webServiceError('A pipeline\'s transform stage refers to an XSL file that doesn\'t exist. There is no file (or I don\'t have permissions to read it) at: <tt>'.$xslPath.'</tt>');
		$this->elementAttributes['loopdepth'] = $this->loopDepth;
		return xsltTransform($currentXml, $xslPath, $this->elementAttributes);
		}

	}
			
?>
