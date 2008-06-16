<?php

class TransformOpenDocumentToDocBook extends PipelineProcess
	{

	function process($currentXml)
		{
		$currentDirectory = dirname($this->docvertTransformDirectory);
		$documentType = detectDocumentType($currentXml);
		switch($documentType)
			{
			case 'OpenDocument1.0':
				//text-colon-section text-colon-style-name="Sect2" text-colon-name="Section2">
				$styles = $this->getStyles();
				$currentXml = preg_replace('/<office:document-content[^>]*?>/s', "$0".$styles, $currentXml);
				//displayXmlString($currentXml);
				$currentXml = xsltTransform($currentXml, $this->docvertTransformDirectory.'fix-opendocument-content.xsl');
				//displayXmlString($currentXml);
				$currentXml = xsltTransform($currentXml, $this->docvertTransformDirectory.'fix-opendocument-content-stage2.xsl');
				//displayXmlString($currentXml);
				$currentXml = xsltTransform($currentXml, $currentDirectory.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'sun-xsl'.DIRECTORY_SEPARATOR.'opendocument-content-to-docbook.xsl');
				//displayXmlString($this->docvertTransformDirectory.'opendocument-content-to-docbook.xsl');
				//displayXmlString($currentXml);
				$metaData = $this->getMetaData();
				$currentXml = preg_replace('/<db:book[^>]*?>/s', "$0".$metaData, $currentXml);
				//displayXmlString($currentXml);
				$currentXml = xsltTransform($currentXml, $this->docvertTransformDirectory.'fix-docbook.xsl');
				//displayXmlString($currentXml);
				break;
			case 'OpenOffice1.x':
				webServiceError('&error-process-transformopendocumenttodocbook-openofficefile;');
				break;
			default:
				webServiceError('&error-process-transformopendocumenttodocbook-unsupported-file; ['.revealXml($currentXml).']', 500, Array('documentType'=>$documentType) );
				break;
			}
		return $currentXml;
		}

	/**
	 appends metadata content
	*/
	function getMetaData()
		{
		$metaDataPath = $this->contentDirectory.DIRECTORY_SEPARATOR.'docvert-meta.xml';
		if(!file_exists($metaDataPath))
			{
			$this->saveTestResult('Warning: This Word Processing file lacked a meta.xml file. This means that some semantic information is not available. To be fair though, this also may not be a problem.', 'warning');
			return '';
			}
		else
			{
			$metaData = file_get_contents($metaDataPath);
			$metaData = xsltTransform($metaData, $this->docvertTransformDirectory.'opendocument-meta-to-docbook.xsl');
			// '<docvert:external-file xmlns:docvert="urn:holloway.co.nz:names:docvert:2" docvert:name="meta.xml">'
			// $metaData = $metaData;
			// '</docvert:external-file>'
			return $metaData;
			}
		}

	/**
	 appends Styles
	*/
	function getStyles()
		{
		$stylesPath = $this->contentDirectory.DIRECTORY_SEPARATOR.'docvert-styles.xml';
		if(!file_exists($stylesPath))
			{
			$this->saveTestResult('Warning: This Word Processing file lacked a separate styles.xml file. This means that it\'s possible for some document structure to be lost. To be fair though, this also may not be a problem.', 'warning');
			return '';
			}
		else
			{
			$styles = file_get_contents($stylesPath);
			preg_match('/<office:document-styles[^>]*?>(.*?)<\\/office:document-styles>/s', $styles, $matches);
			if(count($matches) > 0)
				{
				$styles = $matches[1];
				$styles = '<docvert:external-file xmlns:docvert="urn:holloway.co.nz:names:docvert:2" docvert:name="styles.xml">'.$styles.'</docvert:external-file>';
				return $styles;
				}
			}
		}

	function saveTestResult($errorText, $testType=null)
		{
		if($testType == null)
			{
			$typeType = 'error';
			}
		$errorText = '<div class="'.$testType.'"><p>'.$errorText.'</p></div>';
		$testResultsPath = $this->contentDirectory.DIRECTORY_SEPARATOR.'test.html';
		file_put_contents($testResultsPath, $errorText, FILE_APPEND);
		}

	}
?>
