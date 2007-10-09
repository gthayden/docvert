		case 'ParseOpenDocument':
			$destinationFilename = processDepthTemplate($elementAttributes['toFile'], $depthArray);
			$documentReader = new XMLReader();
			$documentReader->XML($currentXml);
			//$odt = array();
			while($documentReader->read()) {
			   print str_repeat(" ", $xml->depth * $indent).$xml->name."\n";
			   if ($xml->hasAttributes) {
			       $attCount = $xml->attributeCount;
			       print str_repeat(" ", $xml->depth * $indent)." Number of Attributes: ".$xml->attributeCount."\n";
			   }
			}
			//print_r($odt);
			die();
			break;