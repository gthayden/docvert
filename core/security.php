<?php

include_once(dirname(__FILE__).'/config.php');

class Security
	{
	static function hashPassword($password)
		{
		$salt = Security::getSaltValue();
		return hash('sha256', $salt . $password);
		}

	static function setAdminPassword($password)
		{
		return setGlobalConfigItem('adminPassword', Security::hashPassword($password));
		}

	static function getAdminPassword()
		{
		return getGlobalConfigItem('adminPassword');
		}

	static function getSaltValue()
		{
		$configDirectory = getConfigDirectory();
		$saltPath = $configDirectory.'salt';
		if(file_exists($saltPath) && is_readable($saltPath))
			{
			return file_get_contents($saltPath);
			}
		$saltValue = hash('sha256', mt_rand() * microtime());
		file_put_contents($saltPath, $saltValue);
		return $saltValue;
		}

	}


?>
