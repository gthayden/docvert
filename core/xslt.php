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
			if(DOCVERT_CLIENT_TYPE == 'command line')
				{
				$commandLineMessage = '<p>Command Line users: Are you running with a user that has file-system permissions to use the XSLT library?</p><h1>However if it\'s not installed for any user then...</h1>';
				}
			$phpVersion = getPhpVersion();
			if($phpVersion >= 5)
				{
				webServiceError('<p>Your PHP5 doesn\'t have XSLT enabled. You\'ll need to enable this to use Docvert.</p>'.$commandLineMessage.'<h2>Windows</h2><p>You may just have to uncomment the line that reads "extension=php_xsl.dll" in your php.ini, change "extension_dir" to point at your "/ext" extensions directory in your PHP install (a full path), and then restart your web server.</p><h2>Linux, OSX, Unixes</h2><p>Use your distribution\'s PHP5 XSLT install first, then if that doesn\'t work try following <a href="http://php.net/xsl">PHP.Net\'s XSL instructions</a>.</p>');
				}
			else
				{
				webServiceError('PHP5 is only supported right now, and your version of PHP is '.$phpVersion);
				}
			break;
		}
	return $result;
	}

function xsltTransform($xmlString, $xsltPath, $xsltArguments = null)
	{
	if(!file_exists($xsltPath)) webserviceError('I couldn\'t find the requested XSLT file at <tt>'.$xsltPath.'</tt>. You can either add this file, or remove the need from it in your pipeline file.');
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
			$result = @xslt_process($xsltproc, 'arg:/_xml', $xsltPath, NULL, $xmlString, $xsltArguments) or webServiceError('I was unable to use the transform the XML. The error occured when using the XSLT file <tt>'.$xsltPath.'</tt>.<br />The specific error reported was<br /><tt>'.xslt_error($xsltproc).'</tt>');
			if (empty($result) or xslt_error($xsltproc) != null)
				{
       				die('There was a problem using PHP\'s XSLT module (Sablotron) to transform this stage of the pipeline. The processing error was: <tt>'.xslt_error($xsltproc).'</tt><hr />The XSLT file used sits in '.$xsltPath.' and the source XML was: <tt>'.revealXml($xmlString['/_xml']).'</tt>');
				}
	   		xslt_free($xsltproc);
			break;
		default:
			$commandLineMessage = '';
			if(DOCVERT_CLIENT_TYPE == 'command line')
				{
				$commandLineMessage = '<p>Command Line users: If XSL is installed then it\'s possible that command line PHP may have its own php.ini. Be sure XSL is enabled in there and see "doc/troubleshooting.txt" for more tips.</p><h1>However if it\'s not installed then...</h1>';
				}
			$phpVersion = getPhpVersion();
			if($phpVersion >= 5)
				{
				webServiceError('<p>Your PHP doesn\'t have XSLT enabled. You\'ll need to enable this to use Docvert.</p>'.$commandLineMessage.'<h2>Windows</h2><p>You may just have to uncomment the line that reads "extension=php_xsl.dll" in your php.ini, change "extension_dir" to point at your "/ext" extensions directory in your PHP install (a full path), and then restart your web server.</p><h2>Linux, OSX, Unixes</h2><p>Use your distribution\'s PHP5 "XSL" install first, then if that doesn\'t work try following <a href="http://php.net/xsl">PHP.Net\'s XSL instructions</a>.</p>');
				}
			else
				{
				webServiceError('PHP5 is only supported right now, and your version of PHP is '.$phpVersion);
				}
		}
	return $result;		
	}
?>
