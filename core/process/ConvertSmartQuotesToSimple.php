<?php

/** 
  Replaces quotes in the supposed extended ascii range with simple ascii quotes.
	“  becomes  "
	”  becomes  "
	’  becomes  '

  At least that's the idea. PHP strings are just arbitary byte sequences (especially when you go beyond code 127)
  so we'd need to know the source encoding, perhaps even the font (if they were using "Maori Fonts") in order to
  do it safely. This is a hack.

  See,
	http://www.joelonsoftware.com/articles/Unicode.html
	http://nz2.php.net/chr

***/

class ConvertSmartQuotesToSimple extends PipelineProcess 
	{
	
	public function process($currentXml)
		{
		$smartQuotes = array (chr(147), chr(148), chr(146));
		$simpleQuotes = array(chr(34),  chr(34),  chr(39));
		$text = str_replace($smartQuotes, $simpleQuotes, $currentXml);
		return $currentXml;
		}

	}
			
?>
