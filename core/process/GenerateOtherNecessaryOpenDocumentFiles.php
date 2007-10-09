<?php

class GenerateOtherNecessaryOpenDocumentFiles extends PipelineProcess
	{

	function process($currentXml)
		{
		//generate mimetype file
		$mimeType = "application/vnd.oasis.opendocument.text";
		$destinationPath = $this->contentDirectory.DIRECTORY_SEPARATOR.'mimetype';
		file_put_contents($destinationPath, $mimeType);
		$filesDirectory = dirname(dirname(__file__)).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR;

		copy($filesDirectory.'meta.xml', $this->contentDirectory.DIRECTORY_SEPARATOR.'meta.xml');
		copy($filesDirectory.'settings.xml', $this->contentDirectory.DIRECTORY_SEPARATOR.'settings.xml');
		copy($filesDirectory.'styles.xml', $this->contentDirectory.DIRECTORY_SEPARATOR.'styles.xml');

		//generate manifest
		$manifestItemTemplate = "\n\t".'<manifest:file-entry manifest:media-type="{{type}}" manifest:full-path="{{path}}"/>';
		$manifest = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$manifest .= '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">';
		$manifest .= "\n\t".'<manifest:file-entry manifest:media-type="application/vnd.oasis.opendocument.text" manifest:full-path="/"/>';
		$files = glob($this->contentDirectory.DIRECTORY_SEPARATOR.'*');
		$picturesDirectory = $this->contentDirectory.DIRECTORY_SEPARATOR.'Pictures';
		if(file_exists($picturesDirectory))
			{
			$files = array_merge($files, glob($picturesDirectory.DIRECTORY_SEPARATOR.'*'));
			}
		foreach($files as $file)
			{
			$fileType = '';
			if(stringEndsWith($file, '.xml'))
				{
				$fileType = 'text/xml';
				}
			elseif(stringEndsWith($file, '.jpg') || stringEndsWith($file, '.jpeg') || stringEndsWith($file, '.gif') || stringEndsWith($file, '.png'))
				{
				$fileType = 'image/jpeg';
				}

			$filePath = str_replace($this->contentDirectory.DIRECTORY_SEPARATOR, '', $file);

			switch($filePath)
				{
				case 'mimetype':
					break;
				default:
					$manifestItem = str_replace('{{type}}', $fileType, $manifestItemTemplate);
					$manifestItem = str_replace('{{path}}', $filePath, $manifestItem);
					$manifest .= $manifestItem;
				}
			}
		$manifest .= "\n".'</manifest:manifest>';
		$manifestDirectory = $this->contentDirectory.DIRECTORY_SEPARATOR.'META-INF';
		mkdir($manifestDirectory);
		$manifestPath = $manifestDirectory.DIRECTORY_SEPARATOR.'manifest.xml';
		file_put_contents($manifestPath, $manifest);
		return $currentXml;
		}

	}
?>
