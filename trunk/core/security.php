<?php

class Security
	{
	static function setAdminPassword($password)
		{
		$password = Security::filterPassword($password);
		$passwordFilePath = 'writable/adminpassword.php';
		if(!file_put_contents($passwordFilePath, '<'.'?'.'php $adminPassword = \''.trim($password).'\'; ?'.'>'))
			{
			print '<div class="docvertError">';
			print '<h1>Unable to update password file.</h1>';
			print '<p>I\'m unable to write to the <tt>"writable/adminpassword.php"</tt> file and so I\'m unable to update the password. Please check file permissions on this file.</p>';
			print '</div>';
			}
		chmod($passwordFilePath, 0777);
		}

	static function getAdminPassword()
		{
		$adminPassword = FALSE;
		$passwordFilePath = 'writable/adminpassword.php';
		if(file_exists($passwordFilePath))
			{
			include($passwordFilePath);
			}
		return $adminPassword;
		}

	static function filterPassword($toxicPassword)
		{
		if(get_magic_quotes_gpc())
			{
			$toxicPassword = stripslashes($toxicPassword);
			}
		$toxicPassword = str_replace('\\', '', $toxicPassword);
		$toxicPassword = str_replace("'", "", $toxicPassword);
		$toxicPassword = str_replace("\n", "", $toxicPassword);
		$toxicPassword = str_replace("\r", "", $toxicPassword);
		$toxicPassword = str_replace("\t", "", $toxicPassword);
		$cleanPassword = str_replace('"', '', $toxicPassword);
		return trim($cleanPassword);
		}
	}


?>
