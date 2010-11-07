<?php

$test = '
<?xml version="1.0" encoding="UTF-8"?>
<pipeline>
	<stage process="ConvertImages" formats="wmf2png, wmf2svg, bmp2png" deleteOriginals="true" autoCrop="false" autoCropThreshold="20"/>
	<stage process="TransformOpenDocumentToDocBook"/>

	<!-- <stage process="ValidateAgainstSchema" withFile="docbook.rng"/> -->
	<stage process="GeneratePostConversionEditorFiles"/>

	<stage process="Test" withFile="internal://test-docbook.xsl"/>
	
	<stage process="Loop" numberOfTimes="xpathCount://db:chapter">
		<stage process="SplitPages"/>
		<!-- <stage process="ValidateAgainstSchema" withFile="docbook.rng"/> -->
		<stage process="DocBookToXHTML"/>

		<!-- <stage process="ValidateAgainstSchema" withFile="xhtml.rng"/> -->
		{{custom-stages}}
		<!-- <stage process="ValidateAgainstSchema" withFile="xhtml.rng"/> -->
		<stage process="Serialize" toFile="{customSection}"/>
	</stage>

	<stage process="GetPreface"/>

	<!-- <stage process="ValidateAgainstSchema" withFile="docbook.rng"/> -->

	<stage process="DocBookToXHTML" withTableOfContents="true"/>

	<!-- <stage process="ValidateAgainstSchema" withFile="xhtml.rng"/> -->
	{{custom-stages}}
	<!-- <stage process="ValidateAgainstSchema" withFile="xhtml.rng"/> -->
	<stage process="Serialize" toFile="{customIndex}"/>
</pipeline>
';

//print_r(xmlStringToArray($test));
//print "\n\n\n----------------------\n\n\n";
//print_r(deprecated_xmlStringToArray($test));


function xmlStringToArray($xmlString)
	{
	return xmlStringWithRootToArray('<root>'.$xmlString.'</root>');
	}

function xmlStringWithRootToArray($xmlString)
	{
	$simpleXml = simplexml_load_string(trim($xmlString));
	return simpleXmlToArray($simpleXml);
	}

function simpleXmlToArray($simpleXml)
	{
	$xmlArray = Array();
	foreach($simpleXml as $child)
		{
		$childElement = Array();
		$childElement['__nodeName'] = (string) $child->getName();
		$childElement['__attributes'] = Array();
		foreach($child->attributes() as $key => $value)
			{
			$childElement['__attributes'][(string)$key] = (string)$value;
			}
		if($child->count())
			{
			$childElement['__children'] = simpleXmlToArray($child);
			}
		$xmlArray[] = $childElement;
		}
	return $xmlArray;
	}

function deprecated_xmlStringToArray($xmlString)
	{
	$xmlString = preg_replace('/<!--.*?-->/s','',$xmlString);
	$exitAfterManyLoops = 0;
	$xmlArray = array();
	$currentNode = &$xmlArray;
	$currentHierarchy = array();
	$currentDepth = 0;
	while($xmlString != '')
		{
		$exitAfterManyLoops++;
		if($exitAfterManyLoops > 300)
			{
			print "BREAK";
			break;
			}
		$xmlString = trim(substr($xmlString, strpos($xmlString, '<')));
		$thisNodeAscends = (substr($xmlString, 1, 1) == '/');
		$thisNodeDescends = (substr($xmlString, strpos($xmlString, '>') - 1, 1) != '/');
		$nodeName = substr($xmlString, 1, strpos($xmlString, ' ') -1);
		$openElement = substr($xmlString, strpos($xmlString, ' ') + 1);
		$openElement = substr($openElement, 0, strpos($openElement, '>') );
		if(substr($openElement, strlen($openElement) - 1, 1) == "/")
			{
			$openElement = substr($openElement, 0, strlen($openElement) - 1);
			}

		if($thisNodeAscends)
			{
			$currentDepth--;
			$currentNode = &$currentHierarchy[$currentDepth];
			}
		else
			{
			if($thisNodeDescends)
				{
				$currentNode[] = array('__attributes' => parseXmlAttributesString($openElement), '__children' => array(), '__nodeName' => $nodeName);
				$currentHierarchy[$currentDepth] = &$currentNode;
				$currentDepth++;
				$lastItem = &$currentNode[count($currentNode) - 1];
				$currentNode = &$lastItem['__children'];
				}
			else //this node is at the same level
				{
				$currentNode[] = array('__attributes' => parseXmlAttributesString($openElement), '__nodeName' => $nodeName);
				}

			}
		$xmlString = substr($xmlString, strpos($xmlString, '>') + 1);
		}
	return $xmlArray;
	}



function parseXmlAttributesString($xmlElementString)
	{
	$exitAfter100Loops = 0;
	$xmlElementArray = array();
	while($xmlElementString != '')
		{
		$exitAfter100Loops++;
		if($exitAfter100Loops > 100)
			{
			print "BREAK";
			break;
			}
		$equalsCharacterPos = strpos($xmlElementString, '=');
		$key = trim(substr($xmlElementString, 0, $equalsCharacterPos));
		$xmlElementString = substr($xmlElementString, $equalsCharacterPos + 1);
		$openBracket = substr($xmlElementString, 0, 1);
		$xmlElementString = substr($xmlElementString, 1);
		$endBracketPos = strpos($xmlElementString, $openBracket);
		$value = substr($xmlElementString, 0, $endBracketPos);
		$xmlElementString = substr($xmlElementString, $endBracketPos + 1);
		if($key)
			{
			$xmlElementArray[$key]=$value;
			}
		}
	return $xmlElementArray;
	}

?>
