<?php
include_once("ensure-php5.php");
include_once("shell-command.php");
include_once("lib.php");
ob_start();
Themes::cleanUpOldPreviews(getExpireSessionsAfterDays());

function getExpireSessionsAfterDays()
	{
	// just a function so that I can later make it a config variable elsewhere -- matthew@holloway.co.nz
	$expireAfterDays = 0.5;
	return $expireAfterDays;
	}

class Themes
	{
	public $chosenTheme;
	public $themeDirectory;
	public $page;
	public $allowedAdminAccess;
	public $previewDirectory;
	public $destinationZip;
	public $converters = Array(
		'openofficeorg'=>'OpenOffice.org 2+',
		'abiword'=>'Abiword',
		'jodconverter' => 'JODConverter',
		'pyodconverter' => 'PyODConverter');


	function drawTheme()
		{
		$this->chosenTheme = 'docvert';
		$this->docvertRootDirectory = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$this->themeDirectory = dirname(__FILE__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$this->chosenTheme.DIRECTORY_SEPARATOR;
		$this->page = basename($_SERVER['SCRIPT_FILENAME'], '.php');
		$this->allowedAdminAccess = false;
		include_once('security.php');
		$adminPassword = Security::getAdminPassword();
		if($adminPassword !== FALSE)
			{
			if(isset($_POST['password']) && $_POST['password'] == $adminPassword || isset($_SESSION['docvert_p']) && $_SESSION['docvert_p'] == $adminPassword)
				{
				if(isset($_POST['password']))
					{
					//print 'password';
					$this->allowedAdminAccess = true;
					$_SESSION['docvert_p'] = trim($_POST['password']);
					}
				elseif(isset($_POST['changepassword']))
					{
					//print 'changepassword';
					$this->allowedAdminAccess = true;
					Security::setAdminPassword($_POST['changepassword']);
					$_SESSION['docvert_p'] = trim($_POST['password']);
					}
				elseif(isset($_POST['disablexvfb']) || isset($_POST['enablexvfb']))
					{
					$this->allowedAdminAccess = true;
					$disallowXVFB = 'writable'.DIRECTORY_SEPARATOR.'do-not-use-xvfb-on-unix.txt';
					if(isset($_POST['disablexvfb']))
						{
						touch($disallowXVFB);
						chmod($disallowXVFB, 0777);
						}
					else
						{
						if(file_exists($disallowXVFB))
							{
							unlink($disallowXVFB);
							}
						}
					}
				elseif(isset($_POST['logout']))
					{
					//print 'logout';
					$_SESSION['docvert_p'] = '';
					}
				else
					{
					$this->allowedAdminAccess = true;
					}
				}
			}
		elseif(isset($_POST['createpassword']))
			{
			//print 'createpassword';
			Security::setAdminPassword($_POST['createpassword']);
			$_SESSION['docvert_p'] = trim($_POST['createpassword']);
			$this->allowedAdminAccess = true;
			}

		$htmlTemplate = file_get_contents($this->themeDirectory.'template.html');
		$htmlTemplate = str_replace('{{content}}', $this->choosePage(), $htmlTemplate);
		$htmlTemplate = str_replace('{{menu-items}}', $this->menuItems(), $htmlTemplate);
		switch($this->page)
			{
			case 'sample-use':
				$htmlTemplate = str_replace('{{list-pipelines}}', $this->drawPipelines(), $htmlTemplate);
				$htmlTemplate = str_replace('{{autopipelines}}', $this->drawAutoPipelines(), $htmlTemplate);
				$htmlTemplate = str_replace('{{msword-to-opendocument-converter}}', $this->mswordToOpenDocumentConverter(), $htmlTemplate);
				$htmlTemplate = str_replace('{{after-conversion}}', $this->afterConversion(), $htmlTemplate);
				$htmlTemplate = str_replace('{{sample-document}}', $this->sampleDocument(), $htmlTemplate);
				break;
			case 'admin':
				$htmlTemplate = str_replace('{{login}}', $this->login(), $htmlTemplate);
				$htmlTemplate = str_replace('{{logout}}', $this->logout(), $htmlTemplate);
				$htmlTemplate = str_replace('{{change-password}}', $this->changePassword(), $htmlTemplate);
				$htmlTemplate = str_replace('{{choose-converters}}', $this->chooseConverters(), $htmlTemplate);
				$htmlTemplate = str_replace('{{unix-only-use-xvfb}}', $this->unixOnly_useXVFB(), $htmlTemplate);
				$htmlTemplate = str_replace('{{configure-upload-locations}}', $this->configureUploadLocations(), $htmlTemplate);
				$htmlTemplate = str_replace('{{non-opendocument-uploads}}', $this->nonOpenDocumentUploads(), $htmlTemplate);
				$htmlTemplate = str_replace('{{setup-openofficeorg}}', $this->setupOpenOfficeOrg(), $htmlTemplate);
				$htmlTemplate = str_replace('{{create-password}}', $this->createPassword(), $htmlTemplate);
				$htmlTemplate = str_replace('{{allow-webdav}}', $this->allowWebdavUploads(), $htmlTemplate);
				$htmlTemplate = str_replace('{{allow-ftp}}', $this->allowFtpUploads(), $htmlTemplate);
				$htmlTemplate = str_replace('{{configure-filenames}}', $this->configureFilenames(), $htmlTemplate);
				$htmlTemplate = str_replace('{{protocol-message}}', $this->protocolMessage(), $htmlTemplate);
				$htmlTemplate = str_replace('{{document-generation}}', $this->documentGeneration(), $htmlTemplate);
				$htmlTemplate = str_replace('{{php-info}}', $this->showPhpInfo(), $htmlTemplate);
				break;
			case 'generation':
				$htmlTemplate = str_replace('{{step}}', $this->showGenerationStep(), $htmlTemplate);
				break;
			case 'web-service':
				$htmlTemplate = str_replace('{{list-of-converted-documents}}', $this->listOfConvertedDocuments(), $htmlTemplate);
				$htmlTemplate = str_replace('{{first-conversion-url}}', $this->firstConversionUrl(), $htmlTemplate);
				$htmlTemplate = str_replace('{{converted-document-names}}', $this->convertedDocumentNames(), $htmlTemplate);
				$htmlTemplate = str_replace('{{upload-id}}', $this->uploadId(), $htmlTemplate);
				$htmlTemplate = str_replace('{{upload-results}}', $this->uploadResults(), $htmlTemplate);
				$htmlTemplate = str_replace('{{upload-locations}}', $this->uploadLocations(), $htmlTemplate);
				$htmlTemplate = str_replace('{{download-url}}', $this->downloadUrl(), $htmlTemplate);
				$htmlTemplate = str_replace('{{download-size}}', $this->downloadSize(), $htmlTemplate);
				break;
			}
		print $htmlTemplate;
		die();
		}

	function unzipConversionResults($sourceZipPath, $previewDirectory)
		{
		chmod($previewDirectory, 0777);
		$destinationZipPath = $previewDirectory.DIRECTORY_SEPARATOR.basename($sourceZipPath);
		$this->destinationZip = $destinationZipPath;
		if(!moveFile($sourceZipPath, $destinationZipPath)) webServiceError('Unable to move "'.$sourceZipPath.'" to "'.$destinationZipPath.'"');
		chmod($destinationZipPath, 0777);
		include_once('./core/lib/pclzip-2-6/pclzip.lib.php');
		$archive = new PclZip($destinationZipPath);
		if (($archivedFiles = $archive->listContent()) == 0)
			{
			webServiceError('Error unzipping conversion zip file: '.$archive->errorInfo(true));
			}
		foreach ($archivedFiles as $archivedFile)
			{
			$extractedFileMetaData = $archive->extractByIndex($archivedFile['index'], PCLZIP_OPT_PATH, $previewDirectory);
			$extractedFileMetaData = $extractedFileMetaData[0];
			$extractedDestinationPath = substr($extractedFileMetaData['filename'], 2);
			if(file_exists($extractedDestinationPath))
				{
				chmod($extractedDestinationPath, 0777);
				}
			}
		return $this->destinationZip;
		}

	function previewConversionResults($sourceZipPath, $previewDirectory)
		{
		if(!file_exists($sourceZipPath))
			{
			webServiceError('Docvert internal error: zip file does not exist at path "'.$sourceZipPath.'"');
			}
		$this->destinationZip = $sourceZipPath;
		//print $previewDirectory.'<br />';
		$publicPreviewDirectory = 'writable/'.basename($previewDirectory);
		//print $publicPreviewDirectory.'<br />';

		$this->previewDirectory = $publicPreviewDirectory;
		$this->drawTheme();
		}

	function listOfConvertedDocuments()
		{
		$listString = null;
		if(!$this->previewDirectory) webServiceError('Unable to list converted documents because a previewDirectory was not given.');
		$convertedDocumentPaths = glob($this->previewDirectory.DIRECTORY_SEPARATOR.'*');
		$firstConvertedDocument = true;
		foreach($convertedDocumentPaths as $convertedDocumentPath)
			{
			if(substr($convertedDocumentPath, strlen($convertedDocumentPath) - 4) != '.zip')
				{
				$listString .= '<li';
				if($firstConvertedDocument)
					{
					$listString .= ' class="current"';
					$firstConvertedDocument = false;
					}
				$listString .= '>';
				$listString .= '<a href="frameset.php?path='.str_replace('%2F', '/', rawurlencode(str_replace('\\', '/', $convertedDocumentPath))).'" target="previewIFrame" onclick="changeTab(this)"';
				$thumbnailPathPattern = $convertedDocumentPath.DIRECTORY_SEPARATOR.'docvert-thumbnail.*';
				$thumbnails = glob($thumbnailPathPattern);
				$thumbnailPath = "";
				$thumbnailId = "";
				if(count($thumbnails) >= 1)
					{
					$thumbnailPath = $thumbnails[0];
					//$thumbnailPath = str_replace($convertedDocumentPath, '', $thumbnailPath);
					$thumbnailId = md5($thumbnailPath);
					$listString .= ' id="thumbnaillink'.$thumbnailId.'"';
					}
				$listString .= '>';
				$listString .= basename($convertedDocumentPath);
				$listString .= '</a>';
				if(count($thumbnails) >= 1)
					{
					$listString .= '<img src="'.str_replace('\\', '/', $thumbnailPath).'" id="thumbnail'.$thumbnailId.'" style="display:none"/>';
					}
				$listString .= '</li>'."\n";
				}
			}
		return $listString;
		}

	function firstConversionUrl()
		{
		$listString = null;
		if(!$this->previewDirectory) webServiceError('Unable to show the first converted document because a previewDirectory was not given.');
		if(!file_exists($this->previewDirectory))
			{
			$formattedExpireInHours = '';
			$expireInHours = round(getExpireSessionsAfterDays() * 24);
			if($expireInHours == 0)
				{
				$formattedExpireInHours = ' less than one hour.';
				}
			elseif($expireInHours == 1)
				{
				$formattedExpireInHours = ' about one hour.';
				}
			else
				{
				$formattedExpireInHours = ' about '.$expireInHours.' hours.';
				}
			webServiceError('Preview directory does not exist at "'.$this->previewDirectory.'". Perhaps your session has expired? Docvert sessions expire after '.$formattedExpireInHours);
			}
		$convertedDocumentPaths = glob($this->previewDirectory.DIRECTORY_SEPARATOR.'*');
		
		foreach($convertedDocumentPaths as $convertedDocumentPath)
			{
			if(substr($convertedDocumentPath, strlen($convertedDocumentPath) - 4) != '.zip')
				{
				return 'frameset.php?path='.str_replace('\\', '/', $convertedDocumentPath);
				}
			}
		webServiceError('Unable to display previewDirectory of "'.$this->previewDirectory.'"');
		}

	function choosePage()
		{
		if($this->page == 'sample-use')
			{
			return file_get_contents($this->themeDirectory.'sampleuse-content.htmlf');
			}
		elseif($this->page == 'web-service')
			{
			return file_get_contents($this->themeDirectory.'conversionpreview-content.htmlf');
			}
		elseif($this->page == 'admin')
			{
			return file_get_contents($this->themeDirectory.'admin-content.htmlf');
			}
		elseif($this->page == 'generation')
			{
			return file_get_contents($this->themeDirectory.'generation-content.htmlf');
			}
		else
			{
			webServiceError('Unknown page "'.$this->page.'"');
			}
		}

	function downloadUrl()
		{
		$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$docvertDirWithForwardSlashes = str_replace('\\', '/', $docvertDir);
		$publicDownloadZip = str_replace('\\', '/', $this->destinationZip);
		return str_replace($docvertDirWithForwardSlashes, '', $publicDownloadZip);
		}

	function downloadSize()
		{
		$fileSize = filesize($this->destinationZip);
		$fileSizeName = array(' B&#160;', " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		return round($fileSize/pow(1024, ($i = floor(log($fileSize, 1024))))) . $fileSizeName[$i];
		}


	function afterConversion()
		{
		if(!is_writable('writable'))
			{
			return file_get_contents($this->themeDirectory.'sampleuse-no-preview-until-writable.htmlf');
			}
		else
			{
			return file_get_contents($this->themeDirectory.'sampleuse-after-conversion.htmlf');
			}
		}

	function menuItems()
		{
		if($this->page == 'admin')
			{
			return file_get_contents($this->themeDirectory.'menu-admin.htmlf');
			}
		if($this->page == 'web-service')
			{
			return file_get_contents($this->themeDirectory.'menu-webservice.htmlf');
			}
		if($this->page == 'generation')
			{
			return file_get_contents($this->themeDirectory.'menu-generation.htmlf');
			}

		else
			{
			return file_get_contents($this->themeDirectory.'menu-sampleuse.htmlf');
			}
		}

	function drawAutoPipelines()
		{
		$pipelinesString = null;
		$directoryHandler = dir('core'.DIRECTORY_SEPARATOR.'auto-pipelines');
		while (false !== ($entry = $directoryHandler->read()))
			{
			if($entry != ".." && $entry != ".")
				{
				$autopipeline = str_replace('.xml', '', $entry);
				$autopipelineId = 'autopipeline_'.md5($autopipeline);
				$defaultOption = false;
				if(stripos($autopipeline, '.default'))
					{
					$defaultOption = true;
					}
				$pipelinesString .= "\t".'<label for="'.$autopipelineId.'"><input type="radio" value="'.$autopipeline.'" name="autopipeline"';
				if($defaultOption == true)
					{
					$pipelinesString .= ' checked="checked" ';
					}
				$pipelinesString .= 'id="'.$autopipelineId.'"/>&#160;'.str_replace('.default', '', $autopipeline).'</label>'."\n";
				}
			}
		return $pipelinesString;
		}


	function drawPipelines()
		{
		$pipelinesString = null;
		$pipelinesString .= '<select name="pipeline" id="pipeline" onchange="checkForAutoPipeline(this);" onblur="checkForAutoPipeline(this);">'."\n";
		$directoryHandler = dir('pipeline');
		while (false !== ($entry = $directoryHandler->read()))
			{
			if($entry != ".." && $entry != ".")
				{
				$pipelinesString .= "\t".'<option ';
				$pipelineContents = file_get_contents('pipeline'.DIRECTORY_SEPARATOR.$entry.DIRECTORY_SEPARATOR.'pipeline.xml');
				if(strpos($pipelineContents, '<autopipeline') !== FALSE)
					{
					$pipelinesString .= ' class="autopipeline" ';
					$pipelinesString .= 'value="autopipeline';
					}
				else
					{
					$pipelinesString .= ' class="regularpipeline" ';
					$pipelinesString .= 'value="regularpipeline';
					}
				$pipelinesString .= ":".$entry."\" >".$entry."</option>\n";
				}
			}
		$pipelinesString .= '<option value="regularpipeline:none" class="regularpipeline">none (just return .ODT)</option>'."\n";
		$pipelinesString .= '</select>'."\n";
		return $pipelinesString;
		}
	
	function login()
		{
		if(!$this->allowedAdminAccess && Security::getAdminPassword() !== FALSE)
			{
			return file_get_contents($this->themeDirectory.'admin-login.htmlf');
			}
		}

	function logout()
		{
		if($this->allowedAdminAccess)
			{
			return file_get_contents($this->themeDirectory.'admin-logout.htmlf');
			}
		}

	function setupOpenOfficeOrg()
		{
		if($this->allowedAdminAccess)
			{
			$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
			$docvertWritableDir = $docvertDir.'writable'.DIRECTORY_SEPARATOR;
			$template = file_get_contents($this->themeDirectory.'admin-setupopenofficeorg.htmlf');
			$openOfficeServerStatusPath = $docvertWritableDir.'openofficeorg-server.txt';
			$toggleStatus = '';
			if(DIRECTORY_SEPARATOR == '/')
				{
				$disallowXVFB = $docvertWritableDir.'do-not-use-xvfb-on-unix.txt';
				if(isset($_POST['startOpenOfficeOrgServerLinux']))
					{
					$shellCommandTemplate = '{{elevate-privledges}} {{bash-script}} {{xvfb}}';
					$bashScript = '';
					$xvfbCommand = '';
					$elevatePrivledges = '';

					if(!file_exists($disallowXVFB))
						{
						$xvfbCommand = 'true';
						}

					$bashScript = dirname(__FILE__).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'unix-specific'.DIRECTORY_SEPARATOR.'start-openoffice-server.sh';
					$elevatePrivledges = 'sudo';

					if(!file_exists($bashScript))
						{
						die("Can't find bash script at ".$bashScript);
						}

					$shellCommandTemplate = str_replace('{{xvfb}}', $xvfbCommand, $shellCommandTemplate);
					$shellCommandTemplate = str_replace('{{elevate-privledges}}', $elevatePrivledges, $shellCommandTemplate);
					$shellCommandTemplate = str_replace('{{bash-script}}', $bashScript, $shellCommandTemplate);
					$output = shellCommand($shellCommandTemplate, 3);
					include_once('lib.php');
					$diagnostics = suggestFixesToCommandLineErrorMessage($output, Array(), false);
					if($diagnostics)
						{
						if(!is_executable($bashScript))
							{
							$diagnostics = '<p>The script is not set as executable, so "<tt>chmod +x</tt>" it. So, the problem is that, or...</p>'.$diagnostics;
							}						
						$diagnostics = '<div style="background:#ffff99;border: solid 1px #ffff99;"><h1 style="font-size:small;padding-left:1%;color:red">Diagnostics</h1> <p>There were problems opening up JODConverter OpenOffice.org Server</p><p>I ran this command,</p><blockquote><tt>'.$shellCommandTemplate.'</tt></blockquote><p>But I don\'t think I was able to start OpenOffice.org because the script returned.</p><blockquote><tt>'.$output.'</tt></blockquote>'.$diagnostics.'</div>';
						$toggleStatus = $diagnostics.$toggleStatus;
						}
					else
						{
						touch($openOfficeServerStatusPath);
						}
					}
				else if(isset($_POST['stopOpenOfficeOrgServerLinux']))
					{
					if(!file_exists($openOfficeServerStatusPath))
						{
						$processId = file_get_contents($openOfficeServerStatusPath);
						shellCommand('kill -TERM '.$processId);
						}
					unlink($openOfficeServerStatusPath);
					}

				$openOfficeServer = file_get_contents($this->themeDirectory.'admin-setupopenofficeorg-linux.htmlf');
				if(file_exists($openOfficeServerStatusPath))
					{
					$toggleStatus = $toggleStatus.file_get_contents($this->themeDirectory.'admin-setupopenofficeorg-jodconverter-stop.htmlf');
					}
				else
					{
					$toggleStatus = $toggleStatus.file_get_contents($this->themeDirectory.'admin-setupopenofficeorg-jodconverter-start.htmlf');
					}

				}
			else
				{
				$openOfficeServer = file_get_contents($this->themeDirectory.'admin-setupopenofficeorg-windows.htmlf');
				}
			$template = str_replace('{{openoffice-server}}', $openOfficeServer, $template);
			$template = str_replace('{{toggle}}', $toggleStatus, $template);
			return $template;
			}
		}

	function documentGeneration()
		{
		if($this->allowedAdminAccess)
			{
			$template = file_get_contents($this->themeDirectory.'admin-documentgeneration-content.htmlf');
			$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
			$docvertWritableDir = $docvertDir.'writable'.DIRECTORY_SEPARATOR;
			$disallowDocumentGeneration = $docvertWritableDir.'do-not-allow-document-generation.txt';

			if(isset($_REQUEST['disableDocumentGeneration']))
				{
				touch($disallowDocumentGeneration);
				}
			elseif(isset($_REQUEST['enableDocumentGeneration']))
				{
				unlink($disallowDocumentGeneration);
				}


			if(file_exists($disallowDocumentGeneration))
				{
				$template = str_replace('{{toggle-document-generation}}', file_get_contents($this->themeDirectory.'admin-documentgeneration-disabled.htmlf'), $template);
				}
			else
				{
				$template = str_replace('{{toggle-document-generation}}', file_get_contents($this->themeDirectory.'admin-documentgeneration-enabled.htmlf'), $template);
				}
			return $template;
			}
		}

	function nonOpenDocumentUploads()
		{
		if($this->allowedAdminAccess)
			{
			$disallowNonOpenDocumentUploads = 'writable'.DIRECTORY_SEPARATOR.'disallow-nonopendocumentuploads.txt';
			if(isset($_POST['disablenonopendocument']))
				{
				touch($disallowNonOpenDocumentUploads);
				chmod($disallowNonOpenDocumentUploads, 0777);
				}
			elseif(isset($_POST['enablenonopendocument']))
				{
				unlink($disallowNonOpenDocumentUploads);
				}
			if(file_exists($disallowNonOpenDocumentUploads))
				{
				return file_get_contents($this->themeDirectory.'admin-allow-nonopendocument.htmlf');
				}
			else
				{
				return file_get_contents($this->themeDirectory.'admin-disallow-nonopendocument.htmlf');
				}
			}
		}

	function mswordToOpenDocumentConverter()
		{
		$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$docvertWritableDir = $docvertDir.'writable'.DIRECTORY_SEPARATOR;
		$disallowNonOpenDocumentUploads = $docvertWritableDir.'disallow-nonopendocumentuploads.txt';
		if(!file_exists($disallowNonOpenDocumentUploads))
			{
			$converterTemplatePath = $this->themeDirectory.'sampleuse-converter-content.htmlf';
			if(file_exists($converterTemplatePath))
				{
				$template = file_get_contents($this->themeDirectory.'admin-converter-content.htmlf');
				$numberOfConvertersThatAreDisallowed = 0;
				foreach($this->converters as $converterId => $converterName)
					{
					$doNotUseConverterPath = $docvertWritableDir.'do-not-use-'.$converterId.'.txt';
					if(file_exists($doNotUseConverterPath))
						{
						$numberOfConvertersThatAreDisallowed++;
						}
					}

				if( $numberOfConvertersThatAreDisallowed+1 == count($this->converters) )
					{
					// There's only one choice, so don't bother asking the user
					return '';
					}

				$template = file_get_contents($converterTemplatePath);
				$templateConverter = '';

				$optionTemplatePath = $this->themeDirectory.'sampleuse-converter-option.htmlf';
				$optionTemplate = file_get_contents($optionTemplatePath);

				$converterIndex = 0;
				foreach($this->converters as $converterId => $converterName)
					{
					
					$doNotUseConverterPath = $docvertWritableDir.'do-not-use-'.$converterId.'.txt';
					if(!file_exists($doNotUseConverterPath))
						{
						$option = $optionTemplate;
						$checkedContent = '';
						if($converterIndex == 0)
							{
							$checkedContent = ' checked="checked" ';
							}
						$option = str_replace('{{checked}}', $checkedContent, $option);
						$option = str_replace('{{converterName}}', $converterName, $option);
						$option = str_replace('{{converterId}}', $converterId, $option);
						$option = str_replace('{{converterIdHash}}', 'id'.md5($converterId), $option);
						$option = str_replace('{{converterIdLowercase}}', strtolower($converterId), $option);
						$templateConverter .= $option;
						$converterIndex++;
						}

					}
				$template = str_replace('{{converters}}', $templateConverter, $template);
				return $template;
				}
			else
				{
				return 'ERROR: Outdated theme. File did not exist at '.$converterOptionsPath;
				}
			}
		else
			{
			return file_get_contents($this->themeDirectory.'sampleuse-msword-to-opendocument-converter~off.htmlf');
			}
		}

	function sampleDocument()
		{
		$disallowNonOpenDocumentUploads = 'writable'.DIRECTORY_SEPARATOR.'disallow-nonopendocumentuploads.txt';
		if(file_exists($disallowNonOpenDocumentUploads))
			{
			return file_get_contents($this->themeDirectory.'sampleuse-sampledocument-odt.htmlf');
			}
		else
			{
			return file_get_contents($this->themeDirectory.'sampleuse-sampledocument-msword.htmlf');
			}
		}

	function createPassword()
		{
		if(!is_writable('writable'))
			{
			return file_get_contents($this->themeDirectory.'admin-not-writable.htmlf');
			}
		else
			{
			include_once('security.php');
			$changePassword = null;
			if(isset($_POST['changepassword'])) $changePassword = $_POST['changepassword'];
			if(Security::getAdminPassword($changePassword) === FALSE)
				{
				return file_get_contents($this->themeDirectory.'admin-createpassword.htmlf');
				}
			}
		}

	function unixOnly_useXVFB()
		{
		if($this->allowedAdminAccess)
			{
			if(DIRECTORY_SEPARATOR == '/') //Unix
				{
				$disallowXVFB = 'writable'.DIRECTORY_SEPARATOR.'do-not-use-xvfb-on-unix.txt';
				if(file_exists($disallowXVFB))
					{
					return file_get_contents($this->themeDirectory.'admin-unix-only-use-xvfb~off.htmlf');
					}
				else
					{
					return file_get_contents($this->themeDirectory.'admin-unix-only-use-xvfb~on.htmlf');
					}
				}
			else
				{
				return null;
				}
			}
		}

	function changePassword()
		{
		if($this->allowedAdminAccess)
			{
			return file_get_contents($this->themeDirectory.'admin-changepassword.htmlf');
			}
		}

	function drawAdminPage()
		{
		if(!$this->allowedAdminAccess && isset($_POST['logout']))
			{
			}
		else
			{
			return file_get_contents($this->themeDirectory.'admin-options.htmlf');
			}
		}

	static function cleanUpOldPreviews($expireAfterDays)
		{
		$oneDayInSeconds = 60 * 60 * 24;
		$currentTime = time();
		$deleteIfPriorTo = $currentTime - (getExpireSessionsAfterDays() * $oneDayInSeconds);
		$previewDirectories = glob('writable'.DIRECTORY_SEPARATOR.'*');
		
		foreach($previewDirectories as $previewDirectory)
			{
			if(is_dir($previewDirectory))
				{
				if(substr(basename($previewDirectory), 0, 7) == 'preview')
					{
					@$previewDirectoryDetails = stat($previewDirectory);
					if($previewDirectoryDetails)
						{
						$previewCreationTime = $previewDirectoryDetails['ctime'];
						if($previewCreationTime < $deleteIfPriorTo)
							{
							Themes::deleteDirectoryRecursively($previewDirectory);
							}
						}
					}
				}
			}
		
		}

	static function listDirectoryContents($directory)
		{
		$contents = Array();
		if($directoryHandler = opendir($directory))
			{
			while (($file = readdir($directoryHandler)) !== false)
				{
				if($file != '.' && $file != '..')
					{
					$contents[] = $directory.DIRECTORY_SEPARATOR.$file;
					}
				}
			closedir($directoryHandler);
			}
		return $contents;
		}

	function uploadResults()
		{
		include_once('upload-locations.php');
		$uploadHtml = '';
		$uploadLocations = getUploadLocations();
		if(count($uploadLocations))
			{
			$uploadHtml = file_get_contents($this->themeDirectory.'conversionpreview-upload-results.htmlf');
			}
		return $uploadHtml;
		}

	function uploadLocations()
		{
		include_once('upload-locations.php');
		$uploadHtml = '';
		$uploadLocations = getUploadLocations();
		$uploadId = 0;
		foreach($uploadLocations as $uploadLocation)
			{
			$uploadHtml .= '<option value="'.$uploadId.'|'.$uploadLocation["name"].'">'.$uploadLocation["name"].'</option>';
			$uploadId++;
			}
		return $uploadHtml;
		}


	function configureUploadLocations()
		{
		if($this->allowedAdminAccess)
			{
			//[uploadid] => {{upload-id}} [protocol] => webdav [defaultPort] => on
			//[customPort] => [username] => [password] => [basedirectory] => /var/www/

			include_once('upload-locations.php');
			if(isset($_POST['host']) && trim($_POST['host']) != '')
				{
				//print 'Add because post protocol<br />';
				$port = $_POST['customPort'];
				if(isset($_POST["defaultPort"]))
					{
					switch($_POST['protocol'])
						{
						case 'ftp':
						case 'ftp-pasv':
							$port = "21";
							break;
						case 'webdav':
							$port = '80';
							break;
						case 'webdav-ssl':
						case 'webdav-tls':
							$port = "443";
						}
					
					}
				addUploadLocation($_POST['name'], $_POST['protocol'],  $_POST['host'], $port, $_POST['username'], $_POST['uploadpassword'], $_POST['basedirectory']);
				}
			$uploadLocations = getUploadLocations();
			if(isset($_POST['deleteuploadid']))
				{
				$uploadIndex = $_POST['deleteuploadid'] - 1;
				//print 'delete upload id '.$uploadIndex.'<br />';
				$uploadLocations = array_merge(
					array_slice($uploadLocations, 0, $uploadIndex),
					array_slice($uploadLocations, $uploadIndex + 1, count($uploadLocations) - $uploadIndex)
					);
				saveUploadLocations($uploadLocations);
				}

			$uploadLocationsTemplate = file_get_contents($this->themeDirectory.'admin-configure-upload-locations.htmlf');
			
			$existingUploadLocationsHtml = '';

			if(count($uploadLocations))
				{
				$existingUploadLocationsHtml = file_get_contents($this->themeDirectory.'admin-existing-upload-table.htmlf');
				$existingUploadTemplateRow = file_get_contents($this->themeDirectory.'admin-existing-uploads.htmlf');

				$existingUploadLocationsRows = '';
				$uploadId = 1;
				foreach($uploadLocations as $uploadLocation)
					{
					$thisRow = $existingUploadTemplateRow;
					foreach($uploadLocation as $key => $value)
						{
						$thisRow = str_replace('{{'.$key.'}}', $value, $thisRow);
						}

					$rowStyle = '';
					if(($uploadId % 2) != 1)
						{
						$rowStyle = 'background: #eeeeee;';
						}
					$thisRow = str_replace('{{rowStyle}}', $rowStyle, $thisRow);
					$thisRow = str_replace('{{uploadId}}', $uploadId, $thisRow);
					$thisRow = preg_replace('/{{.*?}}/', '', $thisRow);
					$existingUploadLocationsRows .= $thisRow;
					$uploadId++;
					}
				$existingUploadLocationsHtml = str_replace('{{existing-upload-rows}}', $existingUploadLocationsRows, $existingUploadLocationsHtml);
				}
			else
				{
				$existingUploadLocationsHtml = file_get_contents($this->themeDirectory.'admin-existing-uploads-none.htmlf');
				}

			$uploadsTemplate = str_replace('{{existing-uploads}}', $existingUploadLocationsHtml, $uploadLocationsTemplate);
			return $uploadsTemplate;
			}
		}

	static function deleteDirectoryRecursively($path)
		{
		$stopIfErrorDuringDelete = false;
		if(is_dir($path))
			{
			$pathContainsItems = Themes::listDirectoryContents($path);
			foreach($pathContainsItems as $pathContainsItem)
				{
				Themes::deleteDirectoryRecursively($pathContainsItem);
				}
			$listOfItems = null;
			if(!@rmdir($path))
				{
				if($stopIfErrorDuringDelete)
					{
					$pathContainsItems = Themes::listDirectoryContents($directory);
					foreach($pathContainsItems as $pathContainsItem)
						{
						$listOfItems .= $pathContainsItem.'; ';
						}
					die('Error in cleaning up previews directory. Tried to delete '.$path.'. Contains: "'.$listOfItems.'"');
					}
				}
			}
		else
			{
			if(@!unlink($path))
				{
				if($stopIfErrorDuringDelete)
					{
					die('Error in cleaning up previews directory. Tried to delete '.$path);	
					}
				}
			}
		}



	function convertedDocumentNames()
		{
		if(!$this->previewDirectory) webServiceError('Unable to list converted documents because a previewDirectory was not given.');
		return '';

		$directoryNames = null;


		if($directoryHandler = opendir($this->previewDirectory))
			{
			while (($file = readdir($directoryHandler)) !== false)
				{
				if($file != '.' && $file != '..' && stripos($file, '.zip') === False )
					{
					if(trim($file))
						{
						$directoryNames[] = trim($file);
						}
					}
				}
			closedir($directoryHandler);
			}
		$formattedNames = "";
		for ($i = 0; $i < count($directoryNames); $i++)
			{
			$formattedNames .= $directoryNames[$i];
			if($i < count($directoryNames) - 1)
				{
				$formattedNames .= '--';
				}
			}
		return $formattedNames;
		}

	function showPhpInfo()
		{
		if($this->allowedAdminAccess)
			{
			ob_start();
			phpinfo();
			$phpinfo = ob_get_contents();
			ob_end_clean();
			ob_start();
			$phpinfo = substr($phpinfo, strpos($phpinfo, "<body") + 5);
			$phpinfo = substr($phpinfo, strpos($phpinfo, ">") + 1);
			$phpinfo = substr($phpinfo, 0, strpos($phpinfo, "</body>"));
			return '<h2>Your <a href="http://www.php.net/phpinfo" style="color:#666677">phpinfo()</a></h2><p>This is the configuration of your server. Please copy and paste the text below into any bug reports...</p><div id="phpinfo">'.$phpinfo.'</div>';
			}
		}

	function uploadId()
		{
		if(!$this->previewDirectory) webServiceError('Unable to list converted documents because a previewDirectory was not given.');
		return substr($this->previewDirectory, strpos($this->previewDirectory, '/') + 1);
		}

	function allowFtpUploads()
		{
		if(function_exists('ftp_connect'))
			{
			return '<option value="ftp">FTP</option><option value="ftp-pasv">FTP (passive mode)</option>';
			}
		return '';
		}

	function allowWebdavUploads()
		{
		if(function_exists('fsockopen'))
			{
			return '<option value="webdav">WebDAV (http)</option><option value="webdav-ssl">WebDAV+SSL (https)</option><option value="webdav-tls">WebDAV+TLS (https)</option>';
			}
		return '';
		}



	function configureFilenames()
		{
		if($this->allowedAdminAccess)
			{
			$custom_filename_index = "index.html";
			$custom_filename_section = "section#.html";

			$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
			$docvertWritableDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'writable';
			$customFilenamesPath = $docvertWritableDir.DIRECTORY_SEPARATOR.'customfilenames.php';
			if(isset($_POST['custom_filename_index']))
				{
				$fileData = array("index" => $_POST['custom_filename_index'], "section" => $_POST['custom_filename_section']);
				$phpFileData = generatePhpDataFileContents('custom', $fileData);
				file_put_contents($customFilenamesPath, $phpFileData);
				}
			if(file_exists($customFilenamesPath))
				{
				include($customFilenamesPath);
				$custom_filename_index = $custom['index'];
				$custom_filename_section = $custom['section'];
				}

			$template = file_get_contents($this->themeDirectory.'admin-configure-filenames.htmlf');
			$template = str_replace('{{custom_filename_index}}', $custom_filename_index, $template);
			$template = str_replace('{{custom_filename_section}}', $custom_filename_section, $template);

			return $template;
			}
		return '';
		}

	function protocolMessage()
		{
		$protocolMessage = '';
		if(!function_exists('ftp_connect'))
			{
			$protocolMessage .= 'This PHP does not have FTP available [ftp_connect() is not available].';
			}
		if(!function_exists('fsockopen'))
			{
			$protocolMessage .= 'This PHP does not have socket acccess available and so WebDAV is not available (fsockopen() is not available).';
			}
		if($protocolMessage)
			{
			$protocolMessage = '<p style="margin-left:30px">'.$protocolMessage.'</p>';
			}
		return $protocolMessage;
		}

	function showGenerationStep()
		{
		$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$docvertWritableDir = $docvertDir.'writable'.DIRECTORY_SEPARATOR;
		$disallowDocumentGeneration = $docvertWritableDir.'do-not-allow-document-generation.txt';
		if(file_exists($disallowDocumentGeneration))
			{
			return file_get_contents($this->themeDirectory.'generation-disabled.htmlf');
			}

		if(isset($_REQUEST['step']))
			{
			switch($_REQUEST['step'])
				{
				case '4':
					if(!isset($_REQUEST['pages'])) webServiceError('There were no pages submitted. Please back up your browser and try again and ensure that your browser has JavaScript enabled.');
					$template = file_get_contents($this->themeDirectory.'generation-step4.htmlf');
					$hiddenFormChosenPages = Array();
					$listItems = Array();
					foreach($_REQUEST['pages'] as $page)
						{
						$listItems[] = "\n\t\t\t\t".'<li>'.$page.'</li>';
						$hiddenFormChosenPages[] = "\n\t\t\t\t".'<input type="hidden" name="pages[]" value="'.$page.'"/>';
						}
					$template = str_replace('{{page-order}}', implode($listItems), $template);
					$template = str_replace('{{hidden-form-chosen-pages}}', implode($hiddenFormChosenPages), $template);



					$generatorPipelines = glob($this->docvertRootDirectory.'generator-pipeline'.DIRECTORY_SEPARATOR.'*');
					$generatorPipelinesArray = Array();
					foreach($generatorPipelines as $generatorPipeline)
						{
						$generatorName = basename($generatorPipeline);
						$generatorPipelinesArray[] = '<option value="'.$generatorName.'">'.$generatorName.'</option>';
						}
					
					return str_replace('{{generator-pipelines}}', implode('', $generatorPipelinesArray), $template);

				case '3':
					$template = file_get_contents($this->themeDirectory.'generation-step3.htmlf');
					$listItems = Array();
					foreach($_REQUEST['pages'] as $page)
						{
						$listItems[] = "\n\t\t\t\t".'<option value="'.$page.'">'.$page.'</option>';
						}
					return str_replace('{{chosen-scrape-urls}}', implode($listItems), $template);
				case '2':
					if(!isset($_REQUEST['url']))
						{			
						webServiceError('Expected URL form submission.');
						}
					$originalUrl = $_REQUEST['url'];
					if(trim($originalUrl) == '')
						{
						webServiceError('<h1>No URL given</h1><p>I expected a URL but (I think) you didn\'t give me one. Back up your browser and try again.</p>');
						}
					if(!stringStartsWith($originalUrl, 'http'))
						{
						$originalUrl = 'http://'.$originalUrl;
						}
					$originalUrl = str_replace(Array("\n","\r", "\t", " "), '', $originalUrl);

					include_once('http.php');
					if(trim(getUrlLocalPart($originalUrl)) == '')
						{
						$originalUrl = followUrlRedirects($originalUrl.'/');
						}
					else
						{
						$originalUrl = followUrlRedirects($originalUrl);
						}
					if($originalUrl === false)
						{
						webServiceError('Cannot access '.revealXml($originalUrl));
						}
					$page = file_get_contents($originalUrl);

					$baseTagPattern = "/<base[^>]*?href=([^>]*?)>/is";
					preg_match($baseTagPattern, $page, $matches);
					if(count($matches) > 0)
						{
						$originalUrl = trim($matches[1]);
						$originalUrl = substr($originalUrl, 1, strlen($originalUrl) - 2);

						}
					$url = $originalUrl;		
					$connectionPart = getUrlConnectionPart($url);
					$getUrlLocalPart = getUrlLocalPart($url);
					$localPartDirectory = getUrlLocalPartDirectory($url);

					$links = Array();
					$matches = null;
					preg_match_all('/href="(.*?)"/', $page, $matches);
					$matches = $matches[1];
					$urls = array();
					$urls[$originalUrl] = 'value that does not matter';

					foreach($matches as $match)
						{
						$link = $match;
						if(stringStartsWith($link, '/'))
							{
							$link = $connectionPart.$link;
							}
						elseif(stringStartsWith($link, "http://") || stringStartsWith($link, "https://"))
							{
							
							}
						elseif(stringStartsWith($link, "mailto:"))
							{
							}
						else
							{
							$link = $connectionPart.resolveRelativeUrl($localPartDirectory.$link);
							}

						if(containsString($link, '#'))
							{
							$link = substringBefore($link, '#');
							}
						if(stringEndsWith($link, '?'))
							{
							$link = substringBefore($link, '?');
							}

						if(stringStartsWith($link, 'http'))
							{
							$fileExtension = substr($link, strrpos($link, '.') + 1);
							switch($fileExtension)
								{
								case 'avi':
								case 'mov':
								case 'mpg':

								case 'css':

								case 'jpeg':
								case 'jpg':
								case 'gif':
								case 'png':
								case 'bmp':
								case 'apng':
								case 'tiff':
								case 'ico':

								case 'js':

								case 'gz':
								case 'tar':
								case 'zip':
								case 'bin':
								case 'sit':

								case 'mp3':
								case 'mp4':
								case 'wav':
								case 'swf':
								case 'fla':

								case 'rss':
								case 'atom':

								case 'pdf':
								case 'xls':
								case 'doc':
								case 'txt':
								case 'pps':
									break;
								default:
									$urls[$link] = 'value that does not matter';
								}
							}

						}
				
					$urls = array_keys($urls);

					$mostLikelyUrls = array();
					$possibleUrls = array();
					$unlikelyUrls = array();
					$numberOfSlashesInOriginalUrl = strlen($originalUrl) - strlen(str_replace('/', '', $originalUrl));
					foreach($urls as $url)
						{
						$url = followUrlRedirects($url);
						if(trim($url) != '')
							{
							$numberOfSlashesInUrl = strlen($url) - strlen(str_replace('/', '', $url));
							if(stringStartsWith($url, $connectionPart.$localPartDirectory) && $numberOfSlashesInUrl == $numberOfSlashesInOriginalUrl)
								{
								$mostLikelyUrls[] = $url;
								}
							elseif(stringStartsWith($url, $connectionPart))
								{
								$possibleUrls[] = $url;
								}
							else
								{
								$unlikelyUrls[] = $url;
								}
							}
						}

					asort($unlikelyUrls);
					
					$itemId = 0;
					
					foreach($mostLikelyUrls as $url)
						{
						$links[] = '<li class="orderingItem"><label for="urlId'.$itemId.'"><input type="checkbox" name="pages[]" value="'.$url.'" id="urlId'.$itemId.'" checked="checked"/><span class="title">'.$url.'</label></span></li>'."\n";
						$itemId++;
						}
					foreach($possibleUrls as $url)
						{
						$links[] = '<li class="orderingItem"><label for="urlId'.$itemId.'"><input type="checkbox" name="pages[]" value="'.$url.'" id="urlId'.$itemId.'"/><span class="title">'.$url.'</label></span></li>'."\n";
						$itemId++;
						}
					foreach($unlikelyUrls as $url)
						{
						$links[] = '<li class="orderingItem"><label for="urlId'.$itemId.'"><input type="checkbox" name="pages[]" value="'.$url.'" id="urlId'.$itemId.'"/><span class="title">'.$url.'</label></span></li>'."\n";
						$itemId++;
						}

					$step2Template = file_get_contents($this->themeDirectory.'generation-step2.htmlf');
					$step2Template = str_replace('{{scrape-results}}', implode('', $links), $step2Template);
					$step2Template = str_replace('{{scrape-url}}', $url, $step2Template);
					return $step2Template;
				default:
					return file_get_contents($this->themeDirectory.'generation-step1.htmlf');
				}
			}
		else
			{
			return file_get_contents($this->themeDirectory.'generation-step1.htmlf');
			}
		}

	function chooseConverters()
		{
		if($this->allowedAdminAccess)
			{
			$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
			$docvertWritableDir = $docvertDir.'writable'.DIRECTORY_SEPARATOR;
			
			$template = file_get_contents($this->themeDirectory.'admin-converter-content.htmlf');

			foreach($this->converters as $converterId => $converterName)
				{
				$doNotUseConverterPath = $docvertWritableDir.'do-not-use-'.$converterId.'.txt';
				$interfacePath = null;
			
				if(isset($_POST['converter-'.$converterId.'-enable']) && file_exists($doNotUseConverterPath))
					{
					@unlink($doNotUseConverterPath);
					}
				elseif(isset($_POST['converter-'.$converterId.'-disable']))
					{
					touch($doNotUseConverterPath);
					}

				if(!file_exists($doNotUseConverterPath))
					{
					$interfacePath = $this->themeDirectory.'admin-converter-'.$converterId.'-enabled.htmlf';
					}
				else
					{
					$interfacePath = $this->themeDirectory.'admin-converter-'.$converterId.'-disabled.htmlf';
					}
				$converterPlaceholder = '{{toggle-'.$converterId.'}}';
				if(stripos($template, $converterPlaceholder) === false)
					{
					$template .=  '<br/><br />&#160; Cannot find placeholder of '.$converterPlaceholder.' and so cannot display an interface for '.$converterName.'<br/>';
					}
				else if(!file_exists($interfacePath))
					{
					$template = str_replace($converterPlaceholder, 'Cannot find interface file at '.$interfacePath.'<br /> ', $template);
					}
				else if(!is_readable($interfacePath))
					{
					$template = str_replace($converterPlaceholder, 'Interface file <tt>'.$interfacePath.'</tt> was not readable.', $template);
					}
				else
					{
					$template = str_replace($converterPlaceholder, file_get_contents($interfacePath), $template);
					}
				}
			return $template;
			}
		return '';
		}
	}

?>
