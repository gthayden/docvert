<?php

if(substr(PHP_VERSION, 0, 1) == "4")
	{
	webServiceError('&error-ensure-php5;', 500, Array('phpVersion'=>PHP_VERSION) );
	}

?>
