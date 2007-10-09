<?php

function xmlStringToArray($xmlString)
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
