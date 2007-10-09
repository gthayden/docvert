<?php

/**
 * serializes the current pipeline content to a file
 */
class Serialize extends PipelineProcess 
	{
	
	public function process($currentXml)
		{
		if(!array_key_exists('toFile', $this->elementAttributes)) webServiceError('The pipeline\'s serialize stage doesn\'t name a file to save to. It should contain a toFile attribute and a filename (no directory).');
		$toFile = $this->elementAttributes['toFile'];
		$configFilenamesPath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'custom-filenames.php';
		include_once($configFilenamesPath);
		$toFile = replaceCustomFilenamePlaceholders($toFile, $this->depthArray);
		$destinationFilename = processDepthTemplate($toFile, $this->depthArray);
		$destinationPath = $this->contentDirectory.DIRECTORY_SEPARATOR.$destinationFilename;
		file_put_contents($destinationPath, $currentXml);
		return $currentXml;
		}
	
	}

?>
