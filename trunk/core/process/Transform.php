<?php

/** 
  * apply an XSLT transform to pipeline content
  */
class Transform extends PipelineProcess 
	{
	
	public function process($currentXml)
		{
		if(!array_key_exists('withFile', $this->elementAttributes)) webServiceError('&error-process-transform-lacks-withfile;');
		$xslPath = $this->pipelineDirectory.$this->elementAttributes['withFile'];
		if(!file_exists($xslPath)) webServiceError('&error-process-transform-withfile-missing-file;', 500, Array('xslPath'=>$xslPath));
		$this->elementAttributes['loopdepth'] = $this->loopDepth;
		return xsltTransform($currentXml, $xslPath, $this->elementAttributes);
		}

	}
			
?>
