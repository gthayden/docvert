<?php
/*
	Docvert 4.0 - Copyright (C) 2005-2010
	by Matthew Cruickshank and the smart people in the CREDITS file.
	One day I'll release them from that file.
	
	Licenced for use under the GPL version 3. See the LICENCE file.

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
ob_start();
$appDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');
include_once(DOCVERT_DIR.'core'.DIRECTORY_SEPARATOR.'lib.php');
set_time_limit(120);
$files = $_FILES;
$files = null;
if(isset($_FILES))
	{
	$files = $_FILES;
	}
if(isset($_POST['base64file']))
	{
	$base64data = $_POST['base64file'];
	$fileData = base64_decode($base64data);
	$temporaryFile = tempnam('xxx', 'docvert').'.doc'; // 'xxx' to give a directory that doesn't exist as empty string to tempnam on Windows gives the wrong directory
	$temporaryFile = str_replace('.tmp', '', $temporaryFile);
	file_put_contents($temporaryFile, $fileData);
	if(!file_exists($temporaryFile))
		{
		header('HTTP/1.1 500');
		header('Status: 500');
		die('Was unable to save upload from MSWord (probably due to permissions problems)');
		}
	$files[] = array
		(
		'name' => basename($temporaryFile),
		'size' => strlen($fileData),
		'type' => 'application/msword',
		'tmp_name' => $temporaryFile
		);
	}

$autoPipeline = null;
if(isset($_REQUEST['autopipeline']))
	{
	$autoPipeline = $_REQUEST['autopipeline'];
	}
$afterConversion = null;
if(isset($_POST['afterconversion']))
	{
	$afterConversion = $_POST['afterconversion'];
	}
$pipeline = null;
if(isset($_REQUEST['pipeline']))
	{
	$pipeline = $_REQUEST['pipeline'];
	}
$converter = null;
if(isset($_POST['converter']))
	{
	$converter = $_POST['converter'];
	}
$setupOpenOfficeOrg = null;
if(isset($_POST['setupOpenOfficeOrg']))
	{
	$setupOpenOfficeOrg = $_POST['setupOpenOfficeOrg'];
	}

$justShowPreviewDirectory = null;
if(isset($_GET['preview']))
	{
	$justShowPreviewDirectory = $_GET['preview'];
	$afterConversion = "preview";
	}

processConversion($files, $converter, $pipeline, $autoPipeline, $afterConversion, $setupOpenOfficeOrg, false, $justShowPreviewDirectory);

?>
