<?php
$format = "text";
if(isset($_GET['format']) && $_GET['format'] == "json")
    {
    $format = "json";
    }

switch($format)
    {
    case "text":
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
        break;
    case "json":
        header("Content-type: text/plain");
        $auto_pipelines_directory = 'core'.DIRECTORY_SEPARATOR.'auto-pipelines'.DIRECTORY_SEPARATOR;
        $auto_pipelines = remove_prefix(glob($auto_pipelines_directory.'*'), strlen($auto_pipelines_directory));
        $sanitised_auto_pipelines = Array();
        foreach($auto_pipelines as $auto_pipeline) {
            $sanitised_auto_pipelines[] = basename($auto_pipeline, '.xml');
        }
        $pipelines_directory = 'pipeline'.DIRECTORY_SEPARATOR;
        $pipelines = remove_prefix(glob($pipelines_directory.'*'), strlen($pipelines_directory));
        $pipelines_and_types = Array();
        foreach($pipelines as $pipeline) {
            $pipeline_xml = file_get_contents($pipelines_directory.$pipeline.DIRECTORY_SEPARATOR.'pipeline.xml');

            if(strpos($pipeline_xml, '<autopipeline>') !== false)
                {
                $pipelines_and_types[$pipeline] = 'autopipeline';
                }
            else
                {
                $pipelines_and_types[$pipeline] = 'regularpipeline';
                }
            }
        $response = Array(
            'autopipelines' => $sanitised_auto_pipelines,
            'pipelines' => $pipelines_and_types);
        print json_encode($response);   
        break;
    }

function remove_prefix($array, $prefix_length)
    {
    $copy = Array();
    foreach($array as $item)
        {
        $copy[] = substr($item, $prefix_length);
        }
    return $copy;
    }
?>
