<?php

/** 
Validates against Schemas supported by Trang.
***/

class ValidateAgainstSchema extends PipelineProcess 
	{
	
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
		if(!file_exists($commandTemplateVariable['schemaScriptPath'])) webServiceError('Unable to find the script at <tt>'.$commandTemplateVariable['schemaScriptPath'].'</tt>');
		if(!is_executable($commandTemplateVariable['schemaScriptPath'])) webServiceError('Although the <tt>'.basename($commandTemplateVariable['schemaScriptPath']).'</tt> is present it\'s not set as executable (permissions problem). Set this script as executable.');
		if(!file_exists($commandTemplateVariable['schemaPath'])) webServiceError('The schema was not found at <tt>'.$commandTemplateVariable['schemaPath'].'</tt>');

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
			webServiceError('<p>Use of ValidateAgainstSchema failed when running the command</p><blockquote><tt>'.$command.'</tt></blockquote><p>This was returned...</p><blockquote><tt>'.$output.'</tt></blockquote>'.$possibleError);
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
