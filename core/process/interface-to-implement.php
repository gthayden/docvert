<?php

/**
 * base class for process plugins to implement
 */
abstract class PipelineProcess
	{

	public $elementAttributes;
	public $pipelineDirectory;
	public $contentDirectory;
	public $docvertTransformDirectory;
	public $loopDepth;
	public $depthArray;

	/**
	 * loads required configuration settings for the process
	 */
	public function __construct($elementAttributes, $pipelineDirectory, $contentDirectory, $docvertTransformDirectory, $loopDepth, $depthArray, $previewDirectory, $pipelineSettings)
		{
		$this->elementAttributes = $elementAttributes;
		$this->pipelineDirectory = $pipelineDirectory;
		$this->contentDirectory = $contentDirectory;
		$this->docvertTransformDirectory = $docvertTransformDirectory;
		$this->loopDepth = $loopDepth;
		$this->depthArray = $depthArray;
		$this->previewDirectory = $previewDirectory;
		$this->pipelineSettings = $pipelineSettings;
		}


	/**
	 * process the current pipeline content and pass it on to the next stage
	 * @return string
	 * @param $currentXml string
	 */
	abstract public function process($currentXml);
	
	}

?>
