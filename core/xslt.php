<?php

function getXsltEnabledStatus()
	{
	$xsltEnabledStatus = null;
	if(function_exists('xslt_create'))
		{
		$xsltEnabledStatus = "php4";
		}
	elseif(class_exists('XSLTProcessor'))
		{
		$xsltEnabledStatus = "php5";
		}
	return $xsltEnabledStatus;
	}

function xsltTransformWithXsltString($xmlString, $xsltString, $xsltArguments = null)
	{
	$xsltEnabledStatus = getXsltEnabledStatus();
	switch ($xsltEnabledStatus)
		{
		case 'php5':
			$xslt = new XSLTProcessor;
			$xsltDocument = new DOMDocument();
			$xsltDocument->loadXML($xsltString);
			$xslt->importStyleSheet($xsltDocument);
			if(is_array($xsltArguments))
				{
				foreach($xsltArguments as $key => $value)
					{
					$xslt->setParameter('', $key, $value);
					}
				}
			$xmlDocument = new DOMDocument();
			$xmlDocument->loadXML($xmlString);
			$result = $xslt->transformToXML($xmlDocument);
			break;
		default:
			$commandLineMessage = null;
			$phpVersion = getPhpVersion();
			if($phpVersion >= 5)
				{
				webServiceError('&error-xslt-not-available;');
				}
			else
				{
				webServiceError('&error-php5-required;', 500, Array('phpVersion'=>$phpVersion));
				}
			break;
		}
	return $result;
	}

function xsltTransform($xmlString, $xsltPath, $xsltArguments = null)
	{
	if(!file_exists($xsltPath)) webserviceError('&error-xslt-path-not-found;', 500, Array('path'=>$xsltPath) );
	$result = null;
	$xsltEnabledStatus = getXsltEnabledStatus();
	switch ($xsltEnabledStatus)
		{
		case 'php5':
			$xslt = new XSLTProcessor;
			$xsltDocument = new DOMDocument();
			$xsltDocument->load($xsltPath);
			$xslt->importStyleSheet($xsltDocument);
			if(is_array($xsltArguments))
				{
				foreach($xsltArguments as $key => $value)
					{
					$xslt->setParameter('', $key, $value);
					}
				}
			$errorLevelToDescribeMerelyDeprecatedWarnings = 999999;
			$xmlDocument = new DOMDocument();
			$xmlDocument->loadXML($xmlString);
			$result = $xslt->transformToXML($xmlDocument);
			break;
		case 'php4':
			$xsltproc = xslt_create();
			$xmlString = array('/_xml' => $xmlString);
			$xsltPath = 'file://'.$xsltPath;
			$result = @xslt_process($xsltproc, 'arg:/_xml', $xsltPath, NULL, $xmlString, $xsltArguments) or webServiceError('&error-xslt-processor-error;', 500, Array('path'=>$xsltPath, 'errorMessage'=>xslt_error($xsltproc)));
			if (empty($result) or xslt_error($xsltproc) != null)
				{
				webServiceError('&error-xslt-processor-error;', 500, Array('path'=>$xsltPath, 'errorMessage'=>xslt_error($xsltproc)));
				}
	   		xslt_free($xsltproc);
			break;
		default:
			$commandLineMessage = '';
			$phpVersion = getPhpVersion();
			if($phpVersion >= 5)
				{
				webServiceError('&error-xslt-not-available;');
				}
			else
				{
				webServiceError('&error-php5-required;', 500, Array('phpVersion'=>$phpVersion));
				}
		}
	return $result;		
	}
?>
