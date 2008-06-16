<?php

/** 
  * converts images found
  */
class ConvertImages extends PipelineProcess
	{
	public $formatsConverted = null;
	
	public function process($currentXml)
		{

		if(!array_key_exists('formats', $this->elementAttributes)) webServiceError('&error-process-convertimages-formats;');
		if(!array_key_exists('deleteOriginals', $this->elementAttributes)) webServiceError('&error-process-convertimages-deleteoriginals-required;');

		$jpegQuality = 75;
		if(isset($this->elementAttributes['jpegQuality']))
			{
			$jpegQuality = $this->elementAttributes['jpegQuality'];
			}
		if($jpegQuality <= 0 || $jpegQuality >= 100)
			{
			webServiceError('&error-process-convertimages-jpegquality;');
			}

		if(!isset($this->elementAttributes['jpegQuality'])) $this->elementAttributes['jpegQuality'] = 75;
		$imageConversions = explode(',', $this->elementAttributes['formats']);
		foreach($imageConversions as $imageConversion)
			{
			$conversionRequest = trim($imageConversion);
			$fromFormat = substr($conversionRequest, 0, strpos($conversionRequest, '2'));
			$toFormat = substr($conversionRequest, strpos($conversionRequest, '2') + 1);
			$deleteOriginals = (strtolower($this->elementAttributes['deleteOriginals']) == "true");
			$currentXml = $this->convertImageFormat($fromFormat, $toFormat, $this->contentDirectory, $deleteOriginals, $currentXml, $jpegQuality);
			}
		$this->losslesslyOptimiseImages();
		//add xml references to the newly created image files...
		//do it using OpenDocument... ugh... assume OpenDocument for now...
		$this->formatsConverted = null;

		if(isset($this->elementAttributes['autoCrop']) && strtolower($this->elementAttributes['autoCrop']) == 'true')
			{
			webServiceError('&error-disabled-crop-canvas;');
			}

		return $currentXml;
		}

	function losslesslyOptimiseImages()
		{
		$jpegPaths = glob($this->contentDirectory.DIRECTORY_SEPARATOR.'*.jp*');
		foreach($jpegPaths as $jpegPath)
			{
			$this->losslesslyOptimiseImage($jpegPath, 'jpeg');
			}
		$pngPaths = glob($this->contentDirectory.DIRECTORY_SEPARATOR.'*.png');
		foreach($pngPaths as $pngPath)
			{
			$this->losslesslyOptimiseImage($pngPath, 'png');
			}
		}

	function losslesslyOptimiseImage($path, $jpegOrPng)
		{
		$originalModifiedTime = filemtime($path);
		$originalFilesize = filesize($path);
		if($jpegOrPng == 'jpeg')
			{
			$response = shellCommand('jpegoptim -o '.$path);
			}
		elseif($jpegOrPng == 'png')
			{
			$response = shellCommand('optipng -o7 '.$path);
			}
		clearstatcache();
		$optimisedFilesize = filesize($path);
		$optimisedModifiedTime = filemtime($path);
		if($originalModifiedTime != $optimisedModifiedTime || $originalFilesize != $optimisedFilesize)
			{
			$this->logError(basename($path).' &image-optimised-from; '.formatFileSize($originalFilesize).' &to; '.formatFileSize($optimisedFilesize).' ('.(round(($optimisedFilesize/$originalFilesize)*100)).'&percent;)', 'note');
			}
		else if(strpos($response, 'not found') !== false)
			{
			if($jpegOrPng == 'jpeg')
				{
				$this->logError('&jpegoptim-not-available;', 'warning');
				}
			elseif($jpegOrPng == 'png')
				{
				$this->logError('&optipng-not-available;', 'warning');
				}
			}
		else //jpegoptim made no optimisations, stay quiet about it
			{
			}
		}

	function convertImageFormat($fromFormat, $toFormat, $insideDirectory, $deleteOriginals, &$currentXml, $jpegQuality)
		{
		if(!function_exists('imagecreatefromstring'))
			{
			$this->logError('&error-process-convertimages-gd;', 'warning');
			return $currentXml;
			}
		$operatingSystemFamily = getOperatingSystemFamily();
		$imagePathMask = $insideDirectory.DIRECTORY_SEPARATOR.'*.'.$fromFormat;
		$fromImagePaths = glob($imagePathMask);
		//debug_ensureDirectoryReadable($insideDirectory);
		if($fromImagePaths === false) return $currentXml;
		foreach($fromImagePaths as $fromImagePath)
			{
			$escapedFromImagePath = escapeshellarg($fromImagePath);
			$toImagePath = $insideDirectory.DIRECTORY_SEPARATOR.basename($fromImagePath, '.'.$fromFormat).'.'.$toFormat;
			$escapedToImagePath = escapeshellarg($toImagePath);
			$imageResource = null;
			switch($fromFormat)
				{
				case 'bmp':
					webServiceError('&error-process-convertimages-convert-bmp;');
					break;
				case 'gif':
				case 'jpg':
				case 'jpeg':
				case 'png':
				case 'xbm':
				case 'xpm':
					$imageResource = @imagecreatefromstring(file_get_contents($fromImagePath));
					$this->saveImageByResource($imageResource, $toImagePath, $toFormat);
					break;
				case 'wmf':
				case 'emf':
					$command = DOCVERT_DIR;
					if($operatingSystemFamily == 'Windows')
						{
						$command .= 'core\\config\\windows-specific\\convert-using-';
						}
					elseif($operatingSystemFamily == 'Unix')
						{
						$command .= 'core/config/unix-specific/convert-using-';					
						}
	
					$toImageType = $this->isImageBitmapOrVector($toFormat);
					switch($toImageType)
						{
						case 'bitmap':
							if($operatingSystemFamily == 'Windows')
								{
								$command .= 'wmf2gd.bat';
								}
							elseif($operatingSystemFamily == 'Unix')
								{
								$command .= 'wmf2gd.sh';
								}
							$gdImagePath = $insideDirectory.DIRECTORY_SEPARATOR.basename($fromImagePath, '.'.$fromFormat).'.gd';
							$escapedGdImagePath = escapeshellarg($gdImagePath);
							$command .= ' \''.$insideDirectory.'\' '.$escapedFromImagePath.' '.$escapedGdImagePath;
							$wmf2gdResult = shellCommand($command);
							if(!file_exists($gdImagePath))
								{
								webServiceError('&error-process-convertimages-nofile;', 500, Array('command'=>$command, 'output'=>$wmf2gdResult));
								}
							$gdImageContents = file_get_contents($gdImagePath);
							$imageResource = imagecreatefromstring($gdImageContents);
							silentlyUnlink($gdImagePath);
							$this->saveImageByResource($imageResource, $toImagePath, $toFormat);
							break;
						case 'vector':
							if($toFormat != 'svg')
								{
								webServiceError('&error-process-convertimages-onlysvg;');
								}
							if($operatingSystemFamily == 'Windows')
								{
								$command .= 'wmf2svg.bat';
								}
							elseif($operatingSystemFamily == 'Unix')
								{
								$command .= 'wmf2svg.sh';
								}
							$command .= ' '.$escapedFromImagePath.' '.$escapedToImagePath;
							//die($command);
							$wmf2svgResult = shellCommand($command);
							if(!file_exists($toImagePath))
								{
								webServiceError('&error-process-convertimages-nosvg;', 500, Array('command'=>$command , 'output'=>$wmf2svgResult) );
								}
							$this->fixSvgDocument($toImagePath, $insideDirectory);
							break;
						}
					break;
				case 'svg':
					webServiceError('&error-process-convertimages-unable-to-convert-svg;');
					break;
				default:
					webServiceError('&error-process-convertimages-unable-to-convert-from-x;', 500, Array('fromFormat'=>$fromFormat) );
					break;
				}

			if($deleteOriginals)
				{
				silentlyUnlink($fromImagePath);
				}

			if(!isset($this->formatsConverted[$fromFormat]))
				{
				$currentXml = str_replace(basename($fromImagePath), basename($toImagePath), $currentXml);
				//die($fromImagePath.' '.$toImagePath);
				displayXmlString($currentXml);
				}
			}

		$this->formatsConverted[$fromFormat] = "done";
		return $currentXml;
		}

	function fixSvgDocument($svgPath, $insideDirectory)
		{
		$svgContents = file_get_contents($svgPath);
		$svgContents = str_replace($insideDirectory.DIRECTORY_SEPARATOR, '', $svgContents); //fix image references to point to local directory
		$namespaces = array
			(
			'xmlns:svg="http://www.w3.org/2000/svg"',
			'xmlns="http://www.w3.org/2000/svg"',
			'xmlns:xlink="http://www.w3.org/1999/xlink"',
			'xmlns:sodipodi="http://inkscape.sourceforge.net/DTD/sodipodi-0.dtd"',
			'xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"'
			);
		foreach($namespaces as $namespace)
			{
			$namespacePrefix = substr($namespace, 0, strpos($namespace, '=') + 1);
			if(strpos($svgContents, $namespacePrefix) === false)
				{
				$svgContents = str_replace('<svg ', '<svg '.$namespace.' ', $svgContents);
				}
			}
		file_put_contents($svgPath, $svgContents);
		}
	
	function saveImageByResource($imageResource, $toImagePath, $toFormat)
		{
		switch($toFormat)
			{
			// these imagegif function calls are builtin. See http://php.net/imagegif , for example.
			case 'gif':
				//TODO: ensure GIF transparency is maintained. See notes on php.net/imagegif
				imagegif($imageResource, $toImagePath);
				break;
			case 'jpg':
			case 'jpeg':
				imagejpeg($imageResource, $toImagePath, $jpegQuality);
				break;
			case 'png':
				//TODO: ensure PNG transparency is maintained. See notes on php.net/imagepng
				imagepng($imageResource, $toImagePath);
				break;
			case 'xbm':
				imagexbm($imageResource, $toImagePath);
				break;
			case 'xpm':
				webServiceError('&error-process-convertimages-no-xpm;');
				break;
			default:
				webServiceError('&error-process-convertimages-unsupported-image;', 500, Array('toFormat'=>$toFormat) );
			}
		}

	function isImageBitmapOrVector($fileExtension)
		{
		$imageType = null;
		switch($fileExtension)
			{
			case 'bmp':
			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'xbm':
			case 'xpm':
				$imageType = 'bitmap';
				break;
			case 'wmf':
			case 'emf':
			case 'svg':
				$imageType = 'vector';
				break;
			default:
				webServiceError('&error-process-convertimages-unrecognised-file-extension;', 500, Array('fileExtension'=>$fileExtension) );
				break;
			}
		return $imageType;
		}

	}

?>
