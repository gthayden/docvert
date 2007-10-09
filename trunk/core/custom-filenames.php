<?php

function getCustomFilenames()
	{
	$custom_filename_index = "index.html";
	$custom_filename_section = "section#.html";
	$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
	$docvertWritableDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable';
	$customFilenamesPath = $docvertWritableDir.DIRECTORY_SEPARATOR.'customfilenames.php';
	if(file_exists($customFilenamesPath))
		{
		include($customFilenamesPath);
		$custom_filename_index = $custom['index'];
		$custom_filename_section = $custom['section'];
		}
	return array($custom_filename_index, $custom_filename_section);
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
		else
			{
			webServiceError("There's a custom filename that's unrecognised: ".$filename);
			}
		}
	return $filename;
	}


?>
