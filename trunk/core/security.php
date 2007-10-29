<?php

include_once('config.php');

class Security
	{
	static function setAdminPassword($password)
		{
		return setConfigItem('adminPassword', $password);
		}

	static function getAdminPassword()
		{
		return getConfigItem('adminPassword');
		}

	}


?>
