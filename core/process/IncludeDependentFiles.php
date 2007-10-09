<?php

/** 
	Allows copying of files (CSS, images, whatever) to the conversion result directory
***/

class IncludeDependentFiles extends PipelineProcess 
	{
	
	public function process($currentXml)
		{
		if(!array_key_exists('withFiles', $this->elementAttributes)) webServiceError('A pipeline\'s IncludeDependentFiles stage doesn\'t name the files to copy. It should have a withFiles attribute containing a command separated list of filenames (wildcards permitted).');
		$pathsToCopy = explode(',', $this->elementAttributes['withFiles']);
		foreach($pathsToCopy as $pathToCopy)
			{
			$trimmedPathToCopy = trim($pathToCopy);
			if($trimmedPathToCopy)
				{
				$pathWildCard = $this->pipelineDirectory.$trimmedPathToCopy;
				$pathMatches = glob($pathWildCard);
				foreach($pathMatches as $pathMatch)
					{
					$destinationPath = $this->contentDirectory.DIRECTORY_SEPARATOR.basename($pathMatch);
					$this->copyRecursively($pathMatch, $destinationPath);
					}
				}
			}
		return $currentXml;
		}

	function copyRecursively($source, $destination)
		{
		if(is_dir($source))
			{
			mkdir($destination, 0777);
			$folder = opendir($source);
			while($file = readdir($folder))
				{
				if($file != '.' && $file != '..')
					{
					$this->copyRecursively($source.DIRECTORY_SEPARATOR.$file, $destination.DIRECTORY_SEPARATOR.$file);
					}
				}
			closedir($folder);
			}
		else
			{
			copy($source, $destination);
			}
		}
	}
			
?>
