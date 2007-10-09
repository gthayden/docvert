<?php

class DownloadImagesAndSetLinks extends PipelineProcess
	{

	function process($currentXml)
		{
		$extractImagesPath = $this->docvertTransformDirectory.'extract-pages-html-images-and-links.xsl';
		$htmlUrls = trim(xsltTransform($currentXml, $extractImagesPath));
		$htmlUrlLines = explode("\n", $htmlUrls);
	
		$imageUrls = Array();
		foreach($htmlUrlLines as $htmlUrlLine)
			{
			if(trim($htmlUrlLine) == '') continue;

			$urlLineParts = explode("\t", $htmlUrlLine);
			$urlType = $urlLineParts[0];
			$baseUrl = $urlLineParts[1];
			$possiblyRelativeUrl = $urlLineParts[2];
			$fullUrl = '';
			if(stringStartsWith($possiblyRelativeUrl, "http://") || stringStartsWith($possiblyRelativeUrl, "https://") || stringStartsWith($possiblyRelativeUrl, "mailto:"))
				{
				$fullUrl = $possiblyRelativeUrl;
				}
			else
				{
				$connectionPart = getUrlConnectionPart($baseUrl);
				$getUrlLocalPart = getUrlLocalPart($baseUrl);
				$localPartDirectory = getUrlLocalPartDirectory($baseUrl);
				if(stringStartsWith($possiblyRelativeUrl, '/'))
					{
					$fullUrl = $connectionPart.$possiblyRelativeUrl;
					}
				else
					{
					$relativePath = resolveRelativeUrl($localPartDirectory.$possiblyRelativeUrl);
					if(!stringStartsWith($relativePath, '/')) $relativePath = '/'.$relativePath;
					$fullUrl = $connectionPart.$relativePath;
					}
				}
			$missingImagePlaceholderImagePath = dirname(dirname(__file__)).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'404image.gif';
			if(!file_exists($missingImagePlaceholderImagePath)) webServiceError('Can\'t find the missing image placeholder at '.$fourOhFourImagePath);
			$fullUrl = html_entity_decode($fullUrl);
			switch($urlType)
				{
				case 'image':
					$imageData = file_get_contents($fullUrl);

					if($imageData == null)
						{
						$imageData = file_get_contents($missingImagePlaceholderImagePath);
						}
					
					$picturesDirectory = $this->contentDirectory.DIRECTORY_SEPARATOR.'Pictures';
					if(!file_exists($picturesDirectory))
						{
						mkdir($picturesDirectory);
						}

					if(!function_exists('imagecreatefromstring')) webServiceError('<p>Unable to load images into OpenDocument file as your PHP doesn\'t have the GD library. See <a href="http://php.net/gd">http://php.net/gd</a> for install instructions.</p>');
					$imageResource = imagecreatefromstring($imageData);
					if(!$imageResource) //when there is an image but it's an unknown format / corrupt then we replace it with a placeholder
						{
						$imageResource = imagecreatefromstring(file_get_contents($missingImagePlaceholderImagePath));
						}
					$imageWidth = imagesx($imageResource);
					$imageHeight = imagesy($imageResource);
					$fileExtension = substr($fullUrl, strrpos($fullUrl, '.')+1);
					switch($fileExtension)
						{
						case 'jpg':
						case 'jpeg':
						case 'gif':
						case 'png':
							break;
						default:
							$fileExtension = 'jpg';
						}
					
					$openDocumentPath = 'Pictures/'.md5($fullUrl).'.'.$fileExtension;
					file_put_contents($this->contentDirectory.DIRECTORY_SEPARATOR.$openDocumentPath, $imageData);
					$imageUrlReplacement = $openDocumentPath.'" c:width="'.$imageWidth.'" c:height="'.$imageHeight; //FIXME: assumes image @src has double-quote and not single
					$currentXml = str_replace('"'.$possiblyRelativeUrl.'"', '"'.$imageUrlReplacement.'"', $currentXml);
					break;
				case 'link':
					$linkUrl = $urlLineParts[2];
					//print '"'.$possiblyRelativeUrl.'  vs  '.$fullUrl.'<hr />';
					$currentXml = str_replace('"'.$possiblyRelativeUrl.'"', '"'.htmlentities($fullUrl).'"', $currentXml);
					break;
				}
			}
		return $currentXml;
		}

	}
?>
