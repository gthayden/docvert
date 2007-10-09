<?php

function copyViaFtpRecursively($uploadLocation, $previewPath, $remoteDirectory, $ftpType)
	{
	$errorMessage = '';

	$connectionId = getFtpConnection($uploadLocation['host'], $uploadLocation['username'], $uploadLocation['password'], $uploadLocation['port']);
	switch($ftpType)
		{
		case 'active':
			ftp_pasv($connectionId, False);
			break;
		case 'passive':
			ftp_pasv($connectionId, True);
			break;
		}

	$baseDirectory = $uploadLocation['baseDirectory'];
	if(substr($baseDirectory, strlen($baseDirectory) - 1, 1) != '/')
		{
		$baseDirectory .= '/';
		}
	ftp_mkdir($connectionId, $baseDirectory); // No point showing an error message if the directory exists (most likely cause of error) because it will exist (at least) after the first time.

	$remoteBaseDirectory = $baseDirectory.$remoteDirectory;
	if(substr($remoteBaseDirectory, strlen($remoteBaseDirectory) - 1, 1) == '/')
		{
		$remoteBaseDirectory = substr($remoteBaseDirectory, 0, strlen($remoteBaseDirectory) - 1);
		}

	$remoteBaseDirectory .= '/';
	$errorMessage .= copyFileViaFtp($previewPath, $remoteBaseDirectory, $connectionId);

	ftp_close($connectionId);

	$errorHtml = '';
	if($errorMessage)
		{
		$errorHtml = nl2br($errorMessage);
		}
	return $errorHtml;
	}

function getFtpConnection($host, $username, $password, $port)
	{
	$connectionId = ftp_connect($host);
	if(!@ftp_login($connectionId, $username, $password))
		{
		webServiceError('FTP error. Unable to connect to "'.$host.'" with username "'.$username.'"');
		}
	return $connectionId;
	}


function copyFileViaFtp($sourcePath, $destinationPath, $connectionId)
	{
	$errorMessage = '';
	$sourcePath = str_replace(" ", "-", $sourcePath);
	$destinationPath = str_replace(" ", "-", $destinationPath);
	if(!ftp_mkdir($connectionId, $destinationPath))
		{
		$errorMessage .= "Unable to create directory at ".$destinationPath." (it may already exist) \n";
		}
	ftp_site($connectionId, 'CHMOD 0777 '.$destinationPath);
	ftp_chdir($connectionId, $destinationPath);
	//print $sourcePath.' to '.$destinationPath."<br />";
	if(is_dir($sourcePath))
		{
		chdir($sourcePath);
		$handle=opendir('.');
		while(($file = readdir($handle))!==false)
			{
			if(($file != ".") && ($file != ".."))
				{
				if(is_dir($file))
					{
					$errorMessage .= copyFileViaFtp($sourcePath.DIRECTORY_SEPARATOR.$file, $file, $connectionId);
					chdir($sourcePath);
					if(!ftp_cdup($connectionId))
						{
						$errorMessage .= "Unable to ftp_cdup.\n";
						}
					}
				else
					{
					if(substr($file, strlen($file) - 4, 4) != ".zip")
						{
						$fp = fopen($file,"r");
						if(!ftp_fput($connectionId, str_replace(" ", "_", $file), $fp, FTP_BINARY))
							{
							$errorMessage .= "Unable to ftp_fput().\n";
							}
						ftp_site($connectionId, 'CHMOD 0755 '.str_replace(" ", "_", $file));
						}
					}
				}
			}
		closedir($handle);
		}

	return $errorMessage;
	}
?>
