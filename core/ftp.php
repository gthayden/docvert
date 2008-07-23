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
		webServiceError('&error-ftp-authentication;', 500, Array('username'=>$username, 'host'=>$host));
		}
	return $connectionId;
	}


function copyFileViaFtp($sourcePath, $destinationPath, $connectionId)
	{
	$errorMessage = '';
	$sourcePath = str_replace(" ", "-", $sourcePath);
	$destinationPath = sanitiseFtpPath($destinationPath);
	if(@!ftp_mkdir($connectionId, $destinationPath))
		{
		$errorMessage .= "&error-ftp-unable-to-create-directory; ".$destinationPath."\n";
		}
	@ftp_site($connectionId, 'CHMOD 0777 '.$destinationPath); // non-Unix-based servers may respond with "Command not implemented for that parameter" as they don't support chmod, so don't display any errors of this command.
	ftp_chdir($connectionId, $destinationPath);
	//print $sourcePath.' to '.$destinationPath."<br />";
	if(is_dir($sourcePath))
		{
		chdir($sourcePath);
		$handle=opendir('.');
		while(($file = readdir($handle))!==false)
			{
			if($file != "." && $file != "..")
				{
				if(is_dir($file))
					{
					$errorMessage .= copyFileViaFtp($sourcePath.DIRECTORY_SEPARATOR.$file, $file, $connectionId);
					chdir($sourcePath);
					if(!ftp_cdup($connectionId))
						{
						$errorMessage .= '&error-unable-ftp-cd-up;';
						}
					}
				else
					{
					if(substr($file, strlen($file) - 4, 4) != '.zip')
						{
						$fp = fopen($file, 'r');
						if(!ftp_fput($connectionId, sanitiseFtpPath($file), $fp, FTP_BINARY))
							{
							$errorMessage .= '&error-unable-ftp-fput;';
							}
						@ftp_site($connectionId, 'CHMOD 0755 '.sanitiseFtpPath($file));
						fclose($fp);
						}
					}
				}
			}
		closedir($handle);
		}

	return $errorMessage;
	}

function sanitiseFtpPath($path)
	{
	return str_replace(' ', '-', str_replace('\\', '/', $path));
	}
?>
