<?php

/** 
  * Dumps content and halts pipeline execution. As the name says, it's for debugging.
  * In order to display as an XML tree in Firefox there must be some mangling of the XML
  * so please read the docs on displayXmlString in ~/core/lib.php to see what it's doing.
  */
class Debug extends PipelineProcess 
	{
	public function process($currentXml)
		{
		if(DOCVERT_CLIENT_TYPE == 'web')
			{
			displayXmlString($currentXml);
			}
		else
			{
			webServiceError($currentXml, 200);
			}
		}
	}
			
?>
