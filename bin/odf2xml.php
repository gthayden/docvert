<?php
$appCalledWithPath = $argv[0];

define('DOCVERT_DIR', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('DOCVERT_CLIENT_TYPE', 'command line');
$libPath = DOCVERT_DIR.'core/lib.php';
if(!file_exists($libPath))
	{
	die('Docvert internal error. Unable to determine path of core/lib.php due to programming error. Can you please email docvert@holloway.co.nz with the command you typed to make this happen, your platform and versions of PHP? Cheers bro.');
	}
include_once($libPath);
$commandLineLibrary = DOCVERT_DIR.'core/command-line.php';
include_once($commandLineLibrary);

function inNestedArray($key, $haystack)
	{
	if(array_key_exists($key, $haystack))
		{
		return $haystack[$key];
		}
	foreach ($haystack as $value)
		{
		if(is_array($value))
			{
			$work = inNestedArray($key, $value);
			if($work)
				{
        			return $work;
				}
			}
		}
	return FALSE;
	}
?>
