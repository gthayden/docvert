<?php


class ValidateAgainstSchema extends PipelineProcess 
	{
	/*
	Validates against Schemas supported by Trang.
	*/
	
	public function process($currentXml)
		{
		$commandTemplate = '{elevatePermissions} {schemaScriptPath} {schemaPath} {pathToValidate}';
		
		$docvertRootPath = DOCVERT_DIR;
		$operatingSystemFamily = getOperatingSystemFamily();
		$commandTemplateVariable = Array();
		if($operatingSystemFamily == 'Windows')
			{
			$commandTemplateVariable['elevatePermissions'] = '';
			$commandTemplateVariable['schemaScriptPath'] = $docvertRootPath.'core'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'windows-specific'.DIRECTORY_SEPARATOR.'validate-against-schema.bat';
			}
		elseif($operatingSystemFamily == 'Unix')
			{
			$commandTemplateVariable['elevatePermissions'] = 'sudo';
			$commandTemplateVariable['schemaScriptPath'] = $docvertRootPath.'core'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'unix-specific'.DIRECTORY_SEPARATOR.'validate-against-schema.sh';
			}
		$commandTemplateVariable['schemaPath'] = $docvertRootPath.'core'.DIRECTORY_SEPARATOR.'schemas'.DIRECTORY_SEPARATOR.$this->elementAttributes['withFile'];
		if(!file_exists($commandTemplateVariable['schemaScriptPath'])) webServiceError('&error-process-validateagainstschema-script-path;', 500, Array('path'=>basename($commandTemplateVariable['schemaScriptPath'])) );
		if(!is_executable($commandTemplateVariable['schemaScriptPath'])) webServiceError('$error-process-validateagainstschema-path-not-executable;', 500, Array('path'=>basename($commandTemplateVariable['schemaScriptPath'])));
		if(!file_exists($commandTemplateVariable['schemaPath'])) webServiceError('&error-process-validateagainstschema-schema-path;', 500, Array('path'=>$commandTemplateVariable['schemaPath']));

		$commandTemplateVariable['pathToValidate'] = getTemporaryFile();
		file_put_contents($commandTemplateVariable['pathToValidate'], $currentXml);
		
		$command = $commandTemplate;
		foreach($commandTemplateVariable as $key => $value)
			{
			$replaceValue = $value;
			if($replaceValue)
				{
				if($operatingSystemFamily == 'Windows')
					{
					$replaceValue = '"'.$replaceValue.'"';
					}
				elseif($operatingSystemFamily == 'Unix')
					{
					$replaceValue = escapeshellcmd($replaceValue);
					}
				}
			$command = str_replace('{'.$key.'}', $replaceValue, $command);
			}
		$output = shellCommand($command);
		silentlyUnlink($commandTemplateVariable['pathToValidate']);
		$possibleError = suggestFixesToCommandLineErrorMessage($output, $commandTemplateVariable, false);
		if($possibleError)
			{
			webServiceError('&error-process-validateagainst-schema-failed;', 500, Array('command'=>$command, 'output'=>$output, 'possibleError'=>$possibleError)) );
			}
		if(trim($output))
			{
			$output = str_replace($commandTemplateVariable['pathToValidate'], '<b>'.$this->elementAttributes['withFile'].'</b>:', $output);
			$output = str_replace(':2:', '', $output);
			$output = str_replace(':3:', '', $output);
			$output = str_replace(':4:', '', $output);
			$outputLines = explode("\n", $output);
			$formattedOutput = null;
			foreach($outputLines as $outputLine)
				{
				if(trim($outputLine))
					{
					$formattedOutput .= '<div class="validation"><p>'.$outputLine.'</p></div>';
					}
				}
			$testResultsPath = $this->contentDirectory.DIRECTORY_SEPARATOR.'test.html';
			file_put_contents($testResultsPath, $formattedOutput, FILE_APPEND);
			}

		return $currentXml;
		}

	}
			
?>
