<?php

include_once(dirname(__FILE__).'/config.php');

function getCustomFilenames()
	{
	$defaultFilenameForIndex = "index.html";
	$customFilenameIndex = getGlobalConfigItem('customFilenameIndex');
	if($customFilenameIndex === null)
		{
		$customFilenameIndex = $defaultFilenameForIndex;
		}

	$defaultFilenameForSection = "section#.html";
	$customFilenameSection = getGlobalConfigItem('customFilenameSection');
	if($customFilenameSection === null)
		{
		$customFilenameSection = $defaultFilenameForSection;
		}
	return array($defaultFilenameForIndex, $customFilenameSection);
	}

function replaceCustomFilenamePlaceholders($filename, $depthArray)
	{
	if(stripos($filename, '{custom') !== false)
		{
		$customFilesnames = getCustomFilenames();
		$custom_filename_index = $customFilesnames[0];
		$custom_filename_section = $customFilesnames[1];

		if(stripos($filename, '{customIndex}') !== false)
			{
			$filename = str_replace('{customIndex}', $custom_filename_index, $filename);
			}
		elseif(stripos($filename, '{customSection}') !== false)
			{
			$sectionString = "";
			for($i = count($depthArray); $i > 0; $i--)
				{
				if($sectionString != "")
					{
					$sectionString .= "-()-";
					}
				$sectionString .= "{";
				$numberOfLevels = $i;
				while($i > 1)
					{
					$sectionString .= "../";
					}
				$sectionString .= "LoopIndex}";
				}
			$custom_filename_section = str_replace("#", $sectionString, $custom_filename_section);
			//die($custom_filename_section );
			$filename = str_replace('{customSection}', $custom_filename_section, $filename);
			}
		}
	return $filename;
	}


?>
