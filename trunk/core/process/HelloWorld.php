<?php
/*
 *	A small class demonstrates the Pipeline Process implementation
**/
class HelloWorld extends PipelineProcess 
	{
	
	public function process($currentXml)
		{
		return $currentXml; //you could modify the $currentXml and return it
		}

	}
?>
