<?php

include_once('config.php');

class Security
	{
	static function setAdminPassword($password)
		{
		return setGlobalConfigItem('adminPassword', $password);
		}

	static function getAdminPassword()
		{
		return getGlobalConfigItem('adminPassword');
		}

	}


?>
