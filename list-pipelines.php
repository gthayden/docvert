<?php

header("Content-type: text/plain");

print "autopipeline=";
$directoryHandler = dir('core'.DIRECTORY_SEPARATOR.'auto-pipelines');
while (false !== ($entry = $directoryHandler->read()))
	{
	if($entry != ".." && $entry != ".")
		{
		print $entry.";";
		}
	}

print "\r\n";

print "pipeline=";
$directoryHandler = dir('pipeline');
while (false !== ($entry = $directoryHandler->read()))
	{
	if($entry != ".." && $entry != ".")
		{
		print $entry.";";
		}
	}

print "\r\n";

?>