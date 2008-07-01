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

	public function logError($errorMessage, $errorType='error')
		{
		if($errorType != 'error' && $errorType != 'warning' && $errorType != 'raw' && $errorType != 'note') die('Error type must only be either "error" or "warning" or "raw" (for when the message is appended as is). Was "'.$errorType.'".');
		if(!function_exists('replaceLanguagePlaceholder')) include_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'webpage.php');
		$dynamicFields = Array();
		if(is_array($errorMessage))
			{
			$dynamicFields = $errorMessage[1];
			$errorMessage = $errorMessage[0];
			}
		$errorMessage = preg_replace_callback('/\&(.*?)\;/s', 'replaceLanguagePlaceholder', $errorMessage);
		foreach($dynamicFields as $key => $value)
			{
			$errorMessage = str_replace('&dynamic-'.$key.';', $value, $errorMessage);
			}
		if($errorType == 'error' || $errorType == 'warning' || $errorType == 'note')
			{
			$errorMessage = '<div class="'.$errorType.'">'.$errorMessage."</div>\n\n";
			}
		$testResultsPath = $this->contentDirectory.DIRECTORY_SEPARATOR.'test.html';
		file_put_contents($testResultsPath, $errorMessage, FILE_APPEND);
		}

	/**
	 * process the current pipeline content and pass it on to the next stage
	 * @return string
	 * @param $currentXml string
	 */
	abstract public function process($currentXml);
	
	}

?>
