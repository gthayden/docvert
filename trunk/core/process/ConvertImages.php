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
		if($jpegPaths)
			{
			foreach($jpegPaths as $jpegPath)
				{
				$this->losslesslyOptimiseImage($jpegPath, 'jpeg');
				}
			}
		$pngPaths = glob($this->contentDirectory.DIRECTORY_SEPARATOR.'*.png');
		if($pngPaths)
			{
			foreach($pngPaths as $pngPath)
				{
				$this->losslesslyOptimiseImage($pngPath, 'png');
				}
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
					$pdf = $this->wmfOrEmfToPdf($fromImagePath, $currentXml); //first convert to PDF (so that we have a mainstream format)
					//header("Content-type: application/pdf");
					//die(file_get_contents($pdfPath));
					$svgPath = $this->pdfToSvg($pdf['path'], true);
					//header("Content-type: image/svg+xml");
					//die(file_get_contents($svgPath));
					$pngPath = $this->svgToPng($svgPath, realWorldMeasurementsToPixels($pdf['width']), realWorldMeasurementsToPixels($pdf['height']));
					//header("Content-type: image/png");
					//die(file_get_contents($pngPath));
					$imageResource = @imagecreatefromstring(file_get_contents($pngPath));
					$this->saveImageByResource($imageResource, $toImagePath, $toFormat);
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
				//displayXmlString($currentXml);
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

	function pdfToSvg($pdfPath, $deleteOriginal=false)
		{
		$pdfToSvgConverterPath = DOCVERT_DIR.'core'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR;
		$operatingSystemFamily = getOperatingSystemFamily();
		if($operatingSystemFamily == 'Windows')
			{
			$pdfToSvgConverterPath .= 'windows-specific'.DIRECTORY_SEPARATOR.'convert-using-pdf2svg.bat';
			}
		elseif($operatingSystemFamily == 'Unix')
			{
			$pdfToSvgConverterPath .= 'unix-specific'.DIRECTORY_SEPARATOR.'convert-using-pdf2svg.sh';
			}
		if(!file_exists($pdfToSvgConverterPath)) webServiceError('&error-process-convertimages-no-converter;', 500, Array('path'=>$pdfToSvgConverterPath));
		$pdfPathInfo = pathinfo($pdfPath);
		$svgPath = dirname($pdfPath).DIRECTORY_SEPARATOR.basename($pdfPath,'.'.$pdfPathInfo['extension']).'.svg';
		$command = escapeshellarg($pdfToSvgConverterPath).' '.escapeshellarg($pdfPath).' '.escapeshellarg($svgPath);
		shellCommand($command);
		if(!file_exists($svgPath))  webServiceError('&error-process-convertimages-no-svg;', 500, Array('command'=>$command));
		if($deleteOriginal) silentlyUnlink($pdfPath);
		return $svgPath;
		}

	function svgToPng($svgPath, $widthInPixels, $heightInPixels, $deleteOriginal=false)
		{
		$svgToPngConverterPath = DOCVERT_DIR.'core'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR;
		$operatingSystemFamily = getOperatingSystemFamily();
		if($operatingSystemFamily == 'Windows')
			{
			$svgToPngConverterPath .= 'windows-specific'.DIRECTORY_SEPARATOR.'convert-using-svg2png.bat';
			}
		elseif($operatingSystemFamily == 'Unix')
			{
			$svgToPngConverterPath .= 'unix-specific'.DIRECTORY_SEPARATOR.'convert-using-svg2png.sh';
			}
		if(!file_exists($svgToPngConverterPath)) webServiceError('&error-process-convertimages-no-converter;', 500, Array('path'=>$svgToPngConverterPath));
		$svgPathInfo = pathinfo($svgPath);
		$pngPath = dirname($svgPath).DIRECTORY_SEPARATOR.basename($svgPath,'.'.$svgPathInfo['extension']).'.png';
		$command = escapeshellarg($svgToPngConverterPath).' '.escapeshellarg($svgPath).' '.escapeshellarg($pngPath).' '.escapeshellarg($widthInPixels).' '.escapeshellarg($heightInPixels);
		shellCommand($command);
		if(!file_exists($pngPath))  webServiceError('&error-process-convertimages-no-png;', 500, Array('command'=>$command));
		if($deleteOriginal) silentlyUnlink($svgPath);
		return $pngPath;
		}

	function wmfOrEmfToPdf($imagePath, &$currentXml)
		{
		//Step 1. Detect width/height of image.
		$imageOffset = strpos($currentXml, basename($imagePath));
		if($imageOffset === False) return False; //image not in document, don't worry about it.
		$lookBackChars = 200;
		if($imageOffset < $lookBackChars) $lookBackChars = $imageOffset;
		$previousChars = substr($currentXml, $imageOffset-$lookBackChars, $lookBackChars);
		$positionOfWidth = strrpos($previousChars, 'svg:width');
		$positionOfHeight = strrpos($previousChars, 'svg:height');
		if($positionOfWidth === False || $positionOfHeight === False) die("Unable to detect width/height of wmf/emf image.");
		$width = substringBefore(substringAfter(substr($previousChars,$positionOfWidth), '"'), '"'); //TODO use an XML parser
		$height = substringBefore(substringAfter(substr($previousChars,$positionOfHeight), '"'), '"');
		//Step 2. Make an ODT file containing only the WMF/EMF (ugh.. I know, but it works and it's reliable
		// because we benefit from OpenOffice's years of reverse-engineering so get over it)
		//step 2a -- make a working directory for our OpenDocument file and copy the files in
		$workingDirectory = getTemporaryDirectoryInsideDirectory($this->contentDirectory);
		mkdir($workingDirectory.DIRECTORY_SEPARATOR.'Pictures');
		$destinationImagePath = $workingDirectory.DIRECTORY_SEPARATOR.'Pictures'.DIRECTORY_SEPARATOR.basename($imagePath);
		copy($imagePath, $destinationImagePath);
		$odtTemplateDirectory = DOCVERT_DIR.'core'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR;
		$stylesXml = file_get_contents($odtTemplateDirectory.'styles.xml');
		$stylesXml = str_replace('{{page-width}}', $width, $stylesXml);
		$stylesXml = str_replace('{{page-height}}', $height, $stylesXml);
		file_put_contents($workingDirectory.DIRECTORY_SEPARATOR.'styles.xml', $stylesXml);
		copy($odtTemplateDirectory.'settings.xml', $workingDirectory.DIRECTORY_SEPARATOR.'settings.xml');
		copy($odtTemplateDirectory.'meta.xml', $workingDirectory.DIRECTORY_SEPARATOR.'meta.xml');
		copy($odtTemplateDirectory.'manifest.rdf', $workingDirectory.DIRECTORY_SEPARATOR.'manifest.rdf');
		copy($odtTemplateDirectory.'mimetype', $workingDirectory.DIRECTORY_SEPARATOR.'mimetype');
		$contentXml = file_get_contents($odtTemplateDirectory.'content.xml');
		$imageTemplate = '<text:p><draw:frame text:anchor-type="as-char" svg:width="{{width}}" svg:height="{{height}}" draw:z-index="1"><draw:image xlink:href="{{path}}" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame></text:p>';
		$imageString = str_replace('{{width}}', $width, $imageTemplate);
		$imageString = str_replace('{{height}}', $height, $imageString);
		$imageString = str_replace('{{path}}', 'Pictures/'.basename($destinationImagePath), $imageString);
		$contentXml = str_replace('<!--{{content}}-->', $imageString, $contentXml);
		file_put_contents($workingDirectory.DIRECTORY_SEPARATOR.'content.xml', $contentXml);
		mkdir($workingDirectory.DIRECTORY_SEPARATOR.'META-INF');
		$manifestXml = file_get_contents($odtTemplateDirectory.'manifest.xml');
		$manifestItemTemplate = ' <manifest:file-entry manifest:media-type="" manifest:full-path="{{path}}"/>';
		$manifestItem = str_replace('{{path}}', 'Pictures/'.basename($imagePath), $manifestItemTemplate);
		$manifestXml = str_replace('<!--{{content}}-->', $manifestItem, $manifestXml);
		file_put_contents($workingDirectory.DIRECTORY_SEPARATOR.'META-INF'.DIRECTORY_SEPARATOR.'manifest.xml', $manifestXml);
		//step 2b zip it into an ODT
		$zipPath = $this->contentDirectory.DIRECTORY_SEPARATOR.basename($imagePath).'.odt';
		$zipPath = zipFiles($workingDirectory, $zipPath);
		$zipData = file_get_contents($zipPath);
		silentlyUnlink($zipPath);
		silentlyUnlink($workingDirectory);
		//Step 3 . Stream it to PyODConverter. Make a PDF and save it.
		$pyodConverterPath = DOCVERT_DIR.'core'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'pyodconverter'.DIRECTORY_SEPARATOR.'pyodconverter.py';
		if(!file_exists($pyodConverterPath)) die("Can't find PyODconverter at ".htmlentities($pyodConverterPath));
		$command = $pyodConverterPath.' --stream --pdf';
		$response = shellCommand($command, 20, $zipData, false);
		$pdfMagicBytes = '%PDF';
		if(substr($response['stdOut'],0,strlen($pdfMagicBytes)) != $pdfMagicBytes) die("Expected a PDF response was didn't receive one. Received back ".htmlentities(print_r($response, true)));
		$imagePathInfo = pathinfo($imagePath);
		$pdfPath = dirname($imagePath).DIRECTORY_SEPARATOR.basename($imagePath,'.'.$imagePathInfo['extension']).'.pdf';
		file_put_contents($pdfPath, $response['stdOut']);
		return Array(
			'width' => $width,
			'height' => $height,
			'path' => $pdfPath);
		}

	}

?>
