<?php
include_once("ensure-php5.php");
include_once("shell-command.php");
include_once("lib.php");
ob_start();
Themes::cleanUpOldPreviews(getExpireSessionsAfterDays());

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
		'pyodconverter' => 'PyODConverter');

	function __construct()
		{
		$this->converters = getConverters();
		}

	function drawTheme()
		{
		$this->page = basename($_SERVER['SCRIPT_FILENAME'], '.php');
		$this->allowedAdminAccess = false;
		include_once('security.php');
		$adminPassword = Security::getAdminPassword();
		if($adminPassword !== null)
			{
			if(isset($_POST['password']) && Security::hashPassword($_POST['password']) == $adminPassword || isset($_SESSION['docvert_p']) && Security::hashPassword($_SESSION['docvert_p']) == $adminPassword)
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
					$_SESSION['docvert_p'] = trim($_POST['changepassword']);
					}
				if(isset($_POST['disablexvfb']) || isset($_POST['enablexvfb']))
					{
					$this->allowedAdminAccess = true;
					if(isset($_POST['disablexvfb']))
						{
						setGlobalConfigItem('disallowXVFB', 'true');
						}
					else
						{
						setGlobalConfigItem('disallowXVFB', 'false');
						}
					}
				if(isset($_POST['chooseTheme']))
					{
					setGlobalConfigItem('theme', $_POST['chooseTheme']);
					}
				if(isset($_POST['forcePipeline']))
					{
					setGlobalConfigItem('forcePipeline', $_POST['forcePipeline']);
					}
				if(isset($_POST['freelyChoosePipelinesButton']))
					{
					setGlobalConfigItem('forcePipeline', '');
					}

				if(isset($_POST['chooseLanguage']))
					{
					setGlobalConfigItem('language', $_POST['chooseLanguage']);
					}

				if(isset($_POST['logout']))
					{
					$_SESSION['docvert_p'] = '';
					}
				else //they have security access
					{
					$this->allowedAdminAccess = true;
					}
				}
			}
		elseif(isset($_POST['createpassword']))
			{
			Security::setAdminPassword($_POST['createpassword']);
			$_SESSION['docvert_p'] = trim($_POST['createpassword']);
			$this->allowedAdminAccess = true;
			}

		$this->chosenTheme = getGlobalConfigItem('theme');
		if($this->chosenTheme == null)
			{
			$this->chosenTheme = 'docvert';
			}

		$this->docvertRootDirectory = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$this->themeDirectory = dirname(__FILE__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$this->chosenTheme.DIRECTORY_SEPARATOR;

		$htmlTemplate = $this->getThemeFragment('template.html');
		$htmlTemplate = str_replace('{{content}}', $this->choosePage(), $htmlTemplate);
		$htmlTemplate = str_replace('{{menu-items}}', $this->menuItems(), $htmlTemplate);
		switch($this->page)
			{
			case 'sample-use':
				$htmlTemplate = str_replace('{{autopipelines}}', $this->drawAutoPipelines(), $htmlTemplate);
				$htmlTemplate = str_replace('{{msword-to-opendocument-converter}}', $this->mswordToOpenDocumentConverter(), $htmlTemplate);
				$htmlTemplate = str_replace('{{after-conversion}}', $this->afterConversion(), $htmlTemplate);
				$htmlTemplate = str_replace('{{sample-document}}', $this->sampleDocument(), $htmlTemplate);
				$htmlTemplate = str_replace('{{choose-pipeline}}', $this->choosePipelines(), $htmlTemplate);
				break;
			case 'admin':
				$htmlTemplate = str_replace('{{login}}', $this->login(), $htmlTemplate);
				$htmlTemplate = str_replace('{{logout}}', $this->logout(), $htmlTemplate);
				$htmlTemplate = str_replace('{{change-password}}', $this->changePassword(), $htmlTemplate);
				$htmlTemplate = str_replace('{{choose-language}}', $this->chooseLanguage(), $htmlTemplate);
				$htmlTemplate = str_replace('{{choose-theme}}', $this->chooseTheme(), $htmlTemplate);
				$htmlTemplate = str_replace('{{choose-converters}}', $this->chooseConverters(), $htmlTemplate);
				$htmlTemplate = str_replace('{{unix-only-use-xvfb}}', $this->unixOnly_useXVFB(), $htmlTemplate);
				$htmlTemplate = str_replace('{{configure-upload-locations}}', $this->configureUploadLocations(), $htmlTemplate);
				$htmlTemplate = str_replace('{{non-opendocument-uploads}}', $this->nonOpenDocumentUploads(), $htmlTemplate);
				$htmlTemplate = str_replace('{{setup-openofficeorg}}', $this->setupOpenOfficeOrg(), $htmlTemplate);
				$htmlTemplate = str_replace('{{run-as-user}}', $this->runAsUser(), $htmlTemplate);
				$htmlTemplate = str_replace('{{setup-openofficeorg-server}}', $this->setupOpenOfficeOrgServer(), $htmlTemplate);
				$htmlTemplate = str_replace('{{create-password}}', $this->createPassword(), $htmlTemplate);
				$htmlTemplate = str_replace('{{allow-webdav}}', $this->allowWebdavUploads(), $htmlTemplate);
				$htmlTemplate = str_replace('{{allow-ftp}}', $this->allowFtpUploads(), $htmlTemplate);
				$htmlTemplate = str_replace('{{allow-blogger-api}}', $this->allowBloggerAPI(), $htmlTemplate);
				$htmlTemplate = str_replace('{{force-pipeline}}', $this->forcePipeline(), $htmlTemplate);
				$htmlTemplate = str_replace('{{configure-filenames}}', $this->configureFilenames(), $htmlTemplate);
				$htmlTemplate = str_replace('{{protocol-message}}', $this->protocolMessage(), $htmlTemplate);
				$htmlTemplate = str_replace('{{document-generation}}', $this->documentGeneration(), $htmlTemplate);
				$htmlTemplate = str_replace('{{super-user-method}}', $this->superUserMethod(), $htmlTemplate);
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

	function getThemeFragment($path)
		{
		return getThemeFragmentByPath($path, $this->themeDirectory);
		}

	function unzipConversionResults($sourceZipPath, $previewDirectory)
		{
		chmod($previewDirectory, 0777);
		$destinationZipPath = $previewDirectory.DIRECTORY_SEPARATOR.basename($sourceZipPath);
		$this->destinationZip = $destinationZipPath;
		if(!moveFile($sourceZipPath, $destinationZipPath)) webServiceError('&error-webpage-unable-to-move;', 500, Array('source'=>$sourceZipPath, 'destination'=>$destinationZipPath) );
		chmod($destinationZipPath, 0777);
		include_once('./core/lib/pclzip-2-6/pclzip.lib.php');
		$archive = new PclZip($destinationZipPath);
		if (($archivedFiles = $archive->listContent()) == 0)
			{
			webServiceError('&error-webpage-unzipping-files;', 500, Array('errorMessage'=>$archive->errorInfo(true)));
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
			webServiceError('&error-internal-error-zip-path-not-found;', 500, Array('path'=>$sourceZipPath) );
			}
		$this->destinationZip = $sourceZipPath;
		//print $previewDirectory.'<br />';

		// 'writable' hardcoded here because it's the public web preview directory. This should not change. Ever.
		$publicPreviewDirectory = 'writable/'.basename($previewDirectory);

		//print $publicPreviewDirectory.'<br />';
		$this->previewDirectory = $publicPreviewDirectory;
		$this->drawTheme();
		}

	function listOfConvertedDocuments()
		{
		$listString = null;
		if(!$this->previewDirectory) webServiceError('&error-webpage-no-preview-directory-given;');
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
		if(!$this->previewDirectory) webServiceError('&error-webpage-no-preview-directory-given;');
		if(!file_exists($this->previewDirectory))
			{
			webServiceError('&error-webpage-no-preview-directory;');
			}
		$convertedDocumentPaths = glob($this->previewDirectory.DIRECTORY_SEPARATOR.'*');
		
		foreach($convertedDocumentPaths as $convertedDocumentPath)
			{
			if(substr($convertedDocumentPath, strlen($convertedDocumentPath) - 4) != '.zip')
				{
				return 'frameset.php?path='.str_replace('\\', '/', $convertedDocumentPath);
				}
			}
		webServiceError('&error-webpage-unable-to-display-preview-directory;', 500, Array('path'=>$this->previewDirectory) );
		}


	function choosePage()
		{
		if($this->page == 'sample-use')
			{
			return $this->getThemeFragment('sampleuse-content.htmlf');
			}
		elseif($this->page == 'web-service')
			{
			return $this->getThemeFragment('conversionpreview-content.htmlf');
			}
		elseif($this->page == 'admin')
			{
			return $this->getThemeFragment('admin-content.htmlf');
			}
		elseif($this->page == 'generation')
			{
			return $this->getThemeFragment('generation-content.htmlf');
			}
		else
			{
			webServiceError('&error-webpage-unknown-page;', 500, Array('pageName'=>$this->page));
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
		$language = getGlobalConfigItem('language');
		if($language == null)
			{
			$language = 'english';
			}
		
		$fileSize = filesize($this->destinationZip);
		$fileSizeName = array(
			'english' => array(' B&#160;', " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"),
			'french' => array(' B&#160;', " ko", " Mo", " Go", " To", " Po", " Eo", " Zo", " Yo")
			);
		return round($fileSize/pow(1024, ($i = floor(log($fileSize, 1024))))) . $fileSizeName[$language][$i];
		}


	function afterConversion()
		{
		if(!is_writable(getWritableDirectory()))
			{
			return $this->getThemeFragment('sampleuse-no-preview-until-writable.htmlf');
			}
		else
			{
			return $this->getThemeFragment('sampleuse-after-conversion.htmlf');
			}
		}

	function menuItems()
		{
		if($this->page == 'admin')
			{
			return $this->getThemeFragment('menu-admin.htmlf');
			}
		if($this->page == 'web-service')
			{
			return $this->getThemeFragment('menu-webservice.htmlf');
			}
		if($this->page == 'generation')
			{
			return $this->getThemeFragment('menu-generation.htmlf');
			}

		else
			{
			return $this->getThemeFragment('menu-sampleuse.htmlf');
			}
		}

	function drawAutoPipelines()
		{
		$pipelinesString = null;
		$directoryHandler = dir('core'.DIRECTORY_SEPARATOR.'auto-pipelines');
		while (false !== ($entry = $directoryHandler->read()))
			{
			if(substr($entry, 0, 1) != ".")
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


	function choosePipelines()
		{
		$pipelinesString = null;
		$pipelinesString .= '<select name="pipeline" id="pipeline" onchange="checkForAutoPipeline(this);" onblur="checkForAutoPipeline(this);">'."\n";
		$directoryHandler = dir('pipeline');
		while (false !== ($entry = $directoryHandler->read()))
			{
			if(substr($entry, 0, 1) != ".")
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
		$forcedPipeline = getGlobalConfigItem('forcePipeline');
		$template = $this->getThemeFragment('choose-pipelines.htmlf');
		$template = str_replace('{{list-pipelines}}', $pipelinesString, $template);
		if($forcedPipeline == null)
			{
			$template = str_replace('{{forcePipeline}}', '', $template);
			}
		else
			{
			$template = str_replace('{{forcePipeline}}', 'style="display:none"', $template);
			}

		return $template;
		}
	
	function login()
		{
		if(!$this->allowedAdminAccess && Security::getAdminPassword() !== null)
			{
			return $this->getThemeFragment('admin-login.htmlf');
			}
		}

	function logout()
		{
		if(!$this->allowedAdminAccess) return;
		return $this->getThemeFragment('admin-logout.htmlf');
		}

	function setupOpenOfficeOrgServer()
		{
		$operatingSystemFamily = getOperatingSystemFamily();
		if(!$this->allowedAdminAccess || $operatingSystemFamily != 'Unix') return;

		$userMessage = null;
		$pidFile = '/tmp/openoffice.org-server.pid';
		$output = '';

		if(isset($_REQUEST['openofficeorg-server-on']) || isset($_REQUEST['openofficeorg-server-off']) )
			{
			$unixConfigPath = dirname(__FILE__).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'unix-specific'.DIRECTORY_SEPARATOR;
			$startStopPythonScript = $unixConfigPath.'openoffice.org-server-init.py';
			$startStopBashScript = $unixConfigPath.'openoffice.org-server-init.sh';
			$startOrStop = null;
			if(isset($_REQUEST['openofficeorg-server-on']))
				{
				if(file_exists($pidFile) && filesize($pidFile) > 0)
					{
					$userMessage = 'existing-pid';
					}
				else
					{
					$startOrStop = 'start';
					}
				}
			elseif(isset($_REQUEST['openofficeorg-server-off']))
				{
				if(file_exists($pidFile) && filesize($pidFile) > 0)
					{
					$startOrStop = 'stop';
					}
				else
					{
					$userMessage = 'no-existing-pid';
					}
				}
			if($startOrStop)
				{
				include_once('config.php');
				$runAsUser = 'root';
				$sudo = '';
				$customUser = getGlobalConfigItem('runExternalApplicationAsUser');
				if($customUser)
					{
					$runAsUser = $customUser;
					}
				$startStopScript = null;
				$superUserPreference = getSuperUserPreference();
				if($superUserPreference == 'sudo')
					{
					$startStopScript = $startStopBashScript;
					$sudo = 'sudo';
					}
				elseif($superUserPreference == 'setuid')
					{
					$startStopScript = $startStopPythonScript;
					}

				$commandTemplate = '{sudo} {startStopScript} {startOrStop} {runAsUser}';
				$commandTemplate = str_replace('{sudo}', $sudo, $commandTemplate);
				$commandTemplate = str_replace('{startStopScript}', $startStopScript, $commandTemplate);
				$commandTemplate = str_replace('{startOrStop}', $startOrStop, $commandTemplate);
				$commandTemplate = str_replace('{runAsUser}', $runAsUser, $commandTemplate);

				$output = shellCommand($commandTemplate, 1);
				//sleep(); // due to OOo delay in startup/shutdown we'll just twiddle our thumbs for a bit
				if(isset($_REQUEST['openofficeorg-server-on']) || isset($_REQUEST['openofficeorg-server-off']))
					{
					if(trim($output))
						{
						$output = '<blockquote><tt>'.revealXml($output).'</tt></blockquote>';
						}
					else
						{
						$output = '<i>nothing</i> (no response)';
						}
					if(isset($_REQUEST['openofficeorg-server-on']) && !file_exists($pidFile))
						{
						webServiceError('Unable to start PyODConverter OOo server. Output was '.$output);
						}
					elseif(isset($_REQUEST['openofficeorg-server-off']) && file_exists($pidFile) )
						{
						webServiceError('Unable to stop PyODConverter OOo server. Output was '.$output);
						}
					}
				}
			}

		if(file_exists($pidFile) && filesize($pidFile) > 0)
			{
			$response = $this->getThemeFragment('admin-setupopenofficeorg-server-button-disabled.htmlf');
			}
		else
			{
			$response = $this->getThemeFragment('admin-setupopenofficeorg-server-button-enabled.htmlf');
			}
		if($userMessage)
			{
			$response = $response.$this->getThemeFragment('error-setupopenofficeorg-server-'.$userMessage.'.htmlf');
			}
		if($output)
			{
			$response = $response.'<p>Output:</p><pre style="padding:0px 0.5em;font-size:small;background:#cccccc">'.$output.'</pre>';
			}
		return $response;
		}


	function setupOpenOfficeOrg()
		{
		if(!$this->allowedAdminAccess) return;

		$numberOfOpenOfficeBasedConvertersFound = 0;
		$openOfficeBasedConverters = Array('openofficeorg', 'jodconverter', 'pyodconverter');
		foreach($this->converters as $converterId => $converterName)
			{
			if(!in_array($converterId, $openOfficeBasedConverters)) continue;
			$hideConverter = getGlobalConfigItem('hideAdminOption'.$converterId);
			if($hideConverter == 'true')
				{
				$numberOfOpenOfficeBasedConvertersFound++;
				}
			}
		if($numberOfOpenOfficeBasedConvertersFound == count($openOfficeBasedConverters)) return;

		$docvertDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$docvertWritableDir = getWritableDirectory();
		$template = $this->getThemeFragment('admin-setupopenofficeorg.htmlf');


		$toggleStatus = '';
		include_once('config.php');
		if(DIRECTORY_SEPARATOR == '/') //Unix, sudo is available
			{

			$disallowXVFB = getGlobalConfigItem('disallowXVFB');
			if(isset($_POST['startOpenOfficeOrgServerLinux']))
				{
				$shellCommandTemplate = '{{elevate-privledges}} {{bash-script}} {{xvfb}}';
				$xvfbCommand = '';
				$elevatePrivledges = '';

				if($disallowXVFB)
					{
					$xvfbCommand = 'true';
					}

				$shellCommandTemplate = str_replace('{{xvfb}}', $xvfbCommand, $shellCommandTemplate);
				$shellCommandTemplate = str_replace('{{elevate-privledges}}', $elevatePrivledges, $shellCommandTemplate);
				$output = shellCommand($shellCommandTemplate, 3);
				include_once('lib.php');
				$diagnostics = suggestFixesToCommandLineErrorMessage($output, Array(), false);
				if($diagnostics)
					{
					$diagnostics .= '<div style="background:#ffff99;border: solid 1px #ffff99;"><h1 style="font-size:small;padding-left:1%;color:red">Diagnostics</h1> <p>There were problems opening up JODConverter OpenOffice.org Server</p><p>I ran this command,</p><blockquote><tt>'.$shellCommandTemplate.'</tt></blockquote><p>But I don\'t think I was able to start OpenOffice.org because the script returned.</p><blockquote><tt>'.$output.'</tt></blockquote>'.$diagnostics.'</div>';
					$toggleStatus = $diagnostics.$toggleStatus;
					}
				}
			};

		$template = str_replace('{{toggle}}', $toggleStatus, $template);
		return $template;
		}

	function runAsUser()
		{
		if(!$this->allowedAdminAccess) return;
		$runAsCustomUser = '';
		include_once('config.php');	
		if(isset($_REQUEST['setcustomuser']) && isset($_REQUEST['runasuser']))
			{
			setGlobalConfigItem('runExternalApplicationAsUser', $_REQUEST['runasuser']);
			}
		$customUser = getGlobalConfigItem('runExternalApplicationAsUser');

		$runAsCustomUser = $this->getThemeFragment('admin-configure-runexternalapplicationasuser.htmlf');
		$runAsCustomUser = str_replace('{{username}}', $customUser, $runAsCustomUser);
		return $runAsCustomUser;
		}

	function documentGeneration()
		{
		if(!$this->allowedAdminAccess) return;

		$template = $this->getThemeFragment('admin-documentgeneration-content.htmlf');

		if(isset($_REQUEST['disableDocumentGeneration']))
			{
			setGlobalConfigItem('doNotAllowDocumentGeneration', 'true');
			}
		elseif(isset($_REQUEST['enableDocumentGeneration']))
			{
			setGlobalConfigItem('doNotAllowDocumentGeneration', 'false');
			}

		$disallowDocumentGeneration = getGlobalConfigItem('doNotAllowDocumentGeneration');
		if($disallowDocumentGeneration === null || $disallowDocumentGeneration == 'true')
			{
			$template = str_replace('{{toggle-document-generation}}', $this->getThemeFragment('admin-documentgeneration-disabled.htmlf'), $template);
			}
		else
			{
			$template = str_replace('{{toggle-document-generation}}', $this->getThemeFragment('admin-documentgeneration-enabled.htmlf'), $template);
			}
		return $template;
		}

	function nonOpenDocumentUploads()
		{
		if(!$this->allowedAdminAccess) return;

		if(isset($_POST['disablenonopendocument']))
			{
			setGlobalConfigItem('disallowNonOpenDocumentUploads', 'true');
			}
		elseif(isset($_POST['enablenonopendocument']))
			{
			setGlobalConfigItem('disallowNonOpenDocumentUploads', 'false');
			}
		$disallowNonOpenDocumentUploads = getGlobalConfigItem('disallowNonOpenDocumentUploads');
		if($disallowNonOpenDocumentUploads === null || $disallowNonOpenDocumentUploads == 'true')
			{
			return $this->getThemeFragment('admin-allow-nonopendocument.htmlf');
			}
		else
			{
			return $this->getThemeFragment('admin-disallow-nonopendocument.htmlf');
			}
		}

	function mswordToOpenDocumentConverter()
		{
		$disallowNonOpenDocumentUploads = getGlobalConfigItem('disallowNonOpenDocumentUploads');
		if($disallowNonOpenDocumentUploads === null || $disallowNonOpenDocumentUploads == 'false')
			{
			$template = $this->getThemeFragment('sampleuse-converter-content.htmlf');
			$numberOfConvertersThatAreDisallowed = 0;
			foreach($this->converters as $converterId => $converterName)
				{
				$doNotUseConverter = 'doNotUseConverter'.$converterId;
				$doNotUseConverterConfig = getGlobalConfigItem($doNotUseConverter);
				if($doNotUseConverterConfig == 'true')
					{
					$numberOfConvertersThatAreDisallowed++;
					}
				}

			if( $numberOfConvertersThatAreDisallowed+1 == count($this->converters) )
				{
				// There's only one choice, so don't bother asking the user
				return '';
				}

			$optionTemplate = $this->getThemeFragment('sampleuse-converter-option.htmlf');
			$templateConverter = '';
			$converterIndex = 0;
			foreach($this->converters as $converterId => $converterName)
				{
				$doNotUseConverter = 'doNotUseConverter'.$converterId;
				$doNotUseConverterConfig = getGlobalConfigItem($doNotUseConverter);
				if($doNotUseConverterConfig === null || $doNotUseConverterConfig == 'false')
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
			return $this->getThemeFragment('sampleuse-msword-to-opendocument-converter~off.htmlf');
			}

		}

	function sampleDocument()
		{
		$disallowNonOpenDocumentUploads = getGlobalConfigItem('disallowNonOpenDocumentUploads');
		if($disallowNonOpenDocumentUploads === null || $disallowNonOpenDocumentUploads == 'true')
			{
			return $this->getThemeFragment('sampleuse-sampledocument-odt.htmlf');
			}
		else
			{
			return $this->getThemeFragment('sampleuse-sampledocument-msword.htmlf');
			}
		}

	function createPassword()
		{
		if(!is_writable(getWritableDirectory()))
			{
			return $this->getThemeFragment('admin-not-writable.htmlf');
			}
		else
			{
			include_once('security.php');
			if(Security::getAdminPassword() === null)
				{
				return $this->getThemeFragment('admin-createpassword.htmlf');
				}
			}
		}

	function unixOnly_useXVFB()
		{
		if(!$this->allowedAdminAccess) return;
		if(DIRECTORY_SEPARATOR == '\\') return; //windows
		
		$disallowXVFB = getGlobalConfigItem('disallowXVFB');
		if($disallowXVFB === null || $disallowXVFB === 'false')
			{
			return $this->getThemeFragment('admin-unix-only-use-xvfb~on.htmlf');
			}
		else
			{
			return $this->getThemeFragment('admin-unix-only-use-xvfb~off.htmlf');
			}
		}

	function changePassword()
		{
		if($this->allowedAdminAccess)
			{
			return $this->getThemeFragment('admin-changepassword.htmlf');
			}
		}

	function drawAdminPage()
		{
		if(!$this->allowedAdminAccess && isset($_POST['logout']))
			{
			}
		else
			{
			return $this->getThemeFragment('admin-options.htmlf');
			}
		}

	static function cleanUpOldPreviews($expireAfterDays)
		{
		$oneDayInSeconds = 60 * 60 * 24;
		$currentTime = time();
		$deleteIfPriorTo = $currentTime - (getExpireSessionsAfterDays() * $oneDayInSeconds);
		$previewDirectories = glob(getWritableDirectory().'*');
		
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
			$uploadHtml = $this->getThemeFragment('conversionpreview-upload-results.htmlf');
			}
		return $uploadHtml;
		}

	function uploadLocations()
		{
		include_once('upload-locations.php');
		$uploadHtml = '';
		$uploadLocations = getUploadLocations();
		foreach($uploadLocations as $uploadId => $uploadLocation)
			{
			$uploadHtml .= '<option value="'.$uploadId.'">'.$uploadLocation["name"].'</option>';
			}
		return $uploadHtml;
		}


	function configureUploadLocations()
		{
		if(!$this->allowedAdminAccess) return;

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
					case 'bloggerapi':
						$port = '80';
						break;
					case 'bloggerapi-ssl':
					case 'webdav-ssl':
					case 'webdav-tls':
						$port = "443";
					}
				
				}
			addUploadLocation($_POST['name'], $_POST['protocol'],  $_POST['host'], $port, $_POST['username'], $_POST['uploadpassword'], $_POST['basedirectory']);
			}

		if(isset($_POST['deleteuploadid']))
			{
			deleteUploadLocation($_POST['deleteuploadid']);			
			}

		$uploadLocations = getUploadLocations();
		$uploadLocationsTemplate = $this->getThemeFragment('admin-configure-upload-locations.htmlf');
		
		$existingUploadLocationsHtml = '';

		if(count($uploadLocations))
			{
			$existingUploadLocationsHtml = $this->getThemeFragment('admin-existing-upload-table.htmlf');
			$existingUploadTemplateRow = $this->getThemeFragment('admin-existing-uploads.htmlf');

			$existingUploadLocationsRows = '';
			$uploadIndex = 0;
			foreach($uploadLocations as $uploadId => $uploadLocation)
				{
				$thisRow = $existingUploadTemplateRow;
				foreach($uploadLocation as $key => $value)
					{
					$thisRow = str_replace('{{'.$key.'}}', $value, $thisRow);
					}

				$rowStyle = '';
				if(($uploadIndex % 2) != 1)
					{
					$rowStyle = 'background: #eeeeee;';
					}
				$thisRow = str_replace('{{rowStyle}}', $rowStyle, $thisRow);
				$thisRow = str_replace('{{uploadId}}', $uploadId, $thisRow);
				$thisRow = preg_replace('/{{.*?}}/', '', $thisRow);
				$existingUploadLocationsRows .= $thisRow;
				$uploadIndex++;
				}
			$existingUploadLocationsHtml = str_replace('{{existing-upload-rows}}', $existingUploadLocationsRows, $existingUploadLocationsHtml);
			}
		else
			{
			$existingUploadLocationsHtml = $this->getThemeFragment('admin-existing-uploads-none.htmlf');
			}

		$uploadsTemplate = str_replace('{{existing-uploads}}', $existingUploadLocationsHtml, $uploadLocationsTemplate);
		return $uploadsTemplate;
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
		return '';
		}

	function showPhpInfo()
		{
		if(!$this->allowedAdminAccess) return;
		$template = $this->getThemeFragment('admin-phpinfo.htmlf');
		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();
		ob_start();
		$phpinfo = substr($phpinfo, strpos($phpinfo, "<body") + 5);
		$phpinfo = substr($phpinfo, strpos($phpinfo, ">") + 1);
		$phpinfo = substr($phpinfo, 0, strpos($phpinfo, "</body>"));
		$template = str_replace('{{phpinfo}}', $phpinfo, $template);
		return $template;
		}

	function uploadId()
		{
		if(!$this->previewDirectory) webServiceError('&error-webpage-no-preview-directory-given;');
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

	function allowBloggerAPI()
		{
		if(function_exists('fsockopen'))
			{
			return '<option value="bloggerapi">Blogger API</option><option value="bloggerapi-ssl">Blogger API+SSL (https)</option>';
			}
		return '';
		}

	function configureFilenames()
		{
		if(!$this->allowedAdminAccess) return;

		$defaultCustomFilenameIndex = "index.html";
		$defaultCustomFilenameSection = "section#.html";

		if(isset($_POST['custom_filename_index']) && isset($_POST['custom_filename_section']))
			{
			setGlobalConfigItem('customFilenameIndex', $_POST['custom_filename_index']);
			setGlobalConfigItem('customFilenameSection', $_POST['custom_filename_section']);
			}
		
		$customFilenameIndex = getGlobalConfigItem('customFilenameIndex');
		if($customFilenameIndex === null)
			{
			$customFilenameIndex = $defaultCustomFilenameIndex;
			}

		$customFilenameSection = getGlobalConfigItem('customFilenameSection');
		if($customFilenameSection === null)
			{
			$customFilenameSection = $defaultCustomFilenameSection;
			}

		$template = $this->getThemeFragment('admin-configure-filenames.htmlf');
		$template = str_replace('{{custom_filename_index}}', $customFilenameIndex, $template);
		$template = str_replace('{{custom_filename_section}}', $customFilenameSection, $template);

		return $template;
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
		
		$disallowDocumentGeneration = getGlobalConfigItem('doNotAllowDocumentGeneration');
		if($disallowDocumentGeneration == 'true')
			{
			return $this->getThemeFragment('generation-disabled.htmlf');
			}

		if(isset($_REQUEST['step']))
			{
			switch($_REQUEST['step'])
				{
				case '4':
					if(!isset($_REQUEST['pages'])) webServiceError('&error-webpage-generation-no-pages;');
					$template = $this->getThemeFragment('generation-step4.htmlf');
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
					$template = $this->getThemeFragment('generation-step3.htmlf');
					$listItems = Array();
					foreach($_REQUEST['pages'] as $page)
						{
						$listItems[] = "\n\t\t\t\t".'<option value="'.$page.'">'.$page.'</option>';
						}
					return str_replace('{{chosen-scrape-urls}}', implode($listItems), $template);
				case '2':
					if(!isset($_REQUEST['url']))
						{			
						webServiceError('&error-webpage-generation-url;');
						}
					$originalUrl = $_REQUEST['url'];
					if(trim($originalUrl) == '')
						{
						webServiceError('&error-webpage-generation-no-url-given;');
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
						webServiceError('&error-webpage-cannot-get-url;', 500, Array('url'=>$originalUrl));
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

					$step2Template = $this->getThemeFragment('generation-step2.htmlf');
					$step2Template = str_replace('{{scrape-results}}', implode('', $links), $step2Template);
					$step2Template = str_replace('{{scrape-url}}', $url, $step2Template);
					return $step2Template;
				default:
					return $this->getThemeFragment('generation-step1.htmlf');
				}
			}
		else
			{
			return $this->getThemeFragment('generation-step1.htmlf');
			}
		}

	function chooseTheme()
		{
		if(!$this->allowedAdminAccess) return;
		$themeDirectory = dirname(__file__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR;
		$themeDirectories = glob($themeDirectory.'*');
		$themes = Array();

		$chosenTheme = getGlobalConfigItem('theme');
		if($chosenTheme == null)
			{
			$chosenTheme = 'docvert';
			}

		foreach($themeDirectories as $themeDirectory)
			{
			$themeName = basename($themeDirectory);
			if($themeName != 'language')
				{
				$themes[] = $themeName;
				}
			}
		$themeHtml = '';
		foreach($themes as $theme)
			{
			$themeHtml .= '<option value="'.$theme.'"';
			if($theme == $chosenTheme)
				{
				$themeHtml .= ' selected="selected"';
				}
			$themeHtml .= '>'.$theme.'</option>';
			}

		$pageTemplate = $this->getThemeFragment('admin-choose-theme.htmlf');
		$pageTemplate = str_replace('{{list-of-themes}}', $themeHtml, $pageTemplate);
		return $pageTemplate;
		}




	function chooseLanguage()
		{
		if(!$this->allowedAdminAccess) return;
		$languageDirectory = dirname(__file__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR;
		$languageDirectories = glob($languageDirectory.'*');
		$languages = Array();

		$chosenLanguage = getGlobalConfigItem('language');
		if($chosenLanguage == null)
			{
			$chosenLanguage = 'english';
			}

		foreach($languageDirectories as $languageDirectory)
			{
			$languages[] = basename($languageDirectory);
			}
		$languages[] = getFakeLanguageForTranslators();

		$languageHtml = '';
		foreach($languages as $language)
			{
			$languageHtml .= '<option value="'.$language.'"';
			if($language == $chosenLanguage)
				{
				$languageHtml .= ' selected="selected"';
				}
			$languageHtml .= '>'.$language.'</option>';
			}

		$pageTemplate = $this->getThemeFragment('admin-choose-language.htmlf');
		$pageTemplate = str_replace('{{list-of-languages}}', $languageHtml, $pageTemplate);
		return $pageTemplate;
		}

	function superUserMethod()
		{
		if(!$this->allowedAdminAccess) return;
		$hideAdminSuperUserMethodInterface = getGlobalConfigItem('hideAdminSuperUserMethodUserInterface');
		if($hideAdminSuperUserMethodInterface == 'true' || $hideAdminSuperUserMethodInterface == true) return;
		include_once('config.php');
		if(isset($_POST['preferSetuid']))
			{
			setGlobalConfigItem('superUserPreference', 'setuid');			
			}
		elseif(isset($_POST['preferSudo']))
			{
			setGlobalConfigItem('superUserPreference', 'sudo');
			}
		$template = $this->getThemeFragment('admin-superusermethod-content.htmlf');
		$superUserPreference = getSuperUserPreference();
		$template = str_replace('&superUserPreference;', $superUserPreference, $template);
		switch($superUserPreference)
			{
			case 'sudo':
				$template = str_replace('&disableSetuid;', '', $template);
				$template = str_replace('&disableSudo;', 'disabled="disabled"', $template);
				break;
			case 'setuid':
				$template = str_replace('&disableSetuid;', 'disabled="disabled"', $template);
				$template = str_replace('&disableSudo;', '', $template);
				break;
			}
		return $template;
		}


	function forcePipeline()
		{
		if(!$this->allowedAdminAccess) return;
		$template = $this->getThemeFragment('admin-force-pipeline.htmlf');
		$pipelinesDirectory = dirname(dirname(__file__)).DIRECTORY_SEPARATOR.'pipeline'.DIRECTORY_SEPARATOR;
		$pipelines = glob($pipelinesDirectory.'*');
		$optionsHtmlString = '';
		$forcedPipeline = getGlobalConfigItem('forcePipeline');
		foreach($pipelines as $pipeline)
			{
			$optionsHtmlString .= '<option value="'.basename($pipeline).'" ';
			if(basename($pipeline) == $forcedPipeline)
				{
				$optionsHtmlString .= ' selected="selected" ';
				}
			$optionsHtmlString .= '>'.basename($pipeline).'</option>';
			}
		$template = str_replace('{{pipelines}}', $optionsHtmlString, $template);
		$forcePipeline = getGlobalConfigItem('forcePipeline');
		if($forcePipeline == null)
			{
			$template = str_replace('{{forcePipelineEnabled}}', '', $template);
			$template = str_replace('{{freelyChoosePipelinesEnabled}}', 'disabled="disabled" style="background:#99ff99;border:none"', $template);
			}
		else
			{
			$template = str_replace('{{forcePipelineEnabled}}', ' style="background:#99ff99;color:black"', $template);
			$template = str_replace('{{freelyChoosePipelinesEnabled}}', ' ', $template);
			}
		return $template;
		}

	function chooseConverters()
		{
		if(!$this->allowedAdminAccess) return;
		$template = $this->getThemeFragment('admin-converter-content.htmlf');
		$thereWasAtLeastOneConverterAvailable = false;
		$template = preg_replace_callback('/{{toggle-(.*?)}}/s', 'chooseConvertersCallback', $template);
		//if($thereWasAtLeastOneConverterAvailable == false) return;
		return $template;
		}
	}

function chooseConvertersCallback($match)
	{
	$converterId = $match[1];
	$converters = getConverters();
	if(!array_key_exists($converterId, $converters)) return; //'Not found '.$converterId;
	$converterPlaceholder = '{{toggle-'.$converterId.'}}';

	$hideConverterConfigurationKey = 'hideAdminOption'.$converterId;
	$hideConverter = getGlobalConfigItem($hideConverterConfigurationKey);

	if($hideConverter == 'true')
		{
		$template = str_replace($converterPlaceholder, '', $template);
		continue;
		}
	else
		{
		$thereWasAtLeastOneConverterAvailable = true;
		}

	$doNotUseConverter = 'doNotUseConverter'.$converterId;
	if(isset($_POST['converter-'.$converterId.'-enable']))
		{
		setGlobalConfigItem($doNotUseConverter, 'true');
		}
	elseif(isset($_POST['converter-'.$converterId.'-disable']))
		{
		setGlobalConfigItem($doNotUseConverter, 'false');
		}
	$interfacePath = null;
	$convertConfig = getGlobalConfigItem($doNotUseConverter);
	if($convertConfig === null || $convertConfig == 'false' || $convertConfig == false)
		{
		$interfacePath = 'admin-converter-enabled.htmlf';
		}
	else
		{
		$interfacePath = 'admin-converter-disabled.htmlf';
		}

	$themeDirectory = getGlobalConfigItem('theme');
	$interfacePart = getThemeFragmentByPath($interfacePath, $themeDirectory);
	$interfacePart = str_replace('&dynamic-converterName;', str_replace('"', "'", $converters[$converterId]), $interfacePart);
	$interfacePart = str_replace('&dynamic-converterId;', $converterId, $interfacePart);
	//print $interfacePath.' |'.revealXml($convertConfig).'|'.$doNotUseConverter.': '.revealXml($interfacePart).'<hr />';
	return $interfacePart;
	}

function replaceLanguagePlaceholder($match)
	{
	$language = 'english';
	if(!defined('DOCVERT_ERROR_OCCURED'))
		{
		$language = getGlobalConfigItem('language');
		if($language == null)
			{
			$language = 'english';
			}
		}

	if($language == getFakeLanguageForTranslators())
		{
		return $match[0];
		}
	$languagePlaceholderId = $match;
	if(is_array($match)) $languagePlaceholderId = $match[1];

	$placeholderPath = getLanguagePlaceholderPath($languagePlaceholderId, $language);

	//if($languagePlaceholderId == 'disable-pipe-openofficeorg-button.htmlf') die(print_r($match, true).'<hr />'.$languagePlaceholderId.'<hr />'.$placeholderPath);
	if(file_exists($placeholderPath))
		{
		return trim(file_get_contents($placeholderPath));
		}
	elseif($language != 'english') //fallback on English for foreign languages
		{
		$placeholderPath = getLanguagePlaceholderPath($languagePlaceholderId, 'english');
		if(file_exists($placeholderPath))
			{
			return trim(file_get_contents($placeholderPath));
			}
		}
	return $match[0];
	}

function getLanguagePlaceholderPath($languagePlaceholderId, $language)
	{
	return dirname(__file__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$languagePlaceholderId.'.htmlf';
	} 

function languageToISO639($language)
	{
	$languages = Array(
		"english"=>"en",
		"french"=>"fr"
		);
	return $languages[$language];
	}

function getFakeLanguageForTranslators()
	{
	return '(for translators)';
	}

function displayLocalisedErrorPage($message, $errorNumber, $errorData)
	{
	if(defined('DOCVERT_ERROR_OCCURED'))
		{
		define('DOCVERT_RECURSIVE_ERROR', true);
		}
	define('DOCVERT_ERROR_OCCURED', true);
	if(!headers_sent())
		{
		header('HTTP/1.1 '.$errorNumber);
		header('Status: '.$errorNumber);
		}
	$pageType = 'unknown';
	if(substr($errorNumber, 0, 1) == '2')
		{
		$title = '&error-ok;';
		$pageType = 'good';
		}
	else
		{
		$title = '&error-error;';
		$pageType = 'bad';
		}
	if(!defined('DOCVERT_CLIENT_TYPE'))
		{
		$errorMessage = '&error-programming-error-docvert-client-type;';
		$errorMessage = preg_replace_callback('/\&(.*?)\;/s', 'replaceLanguagePlaceholder', $errorMessage);
		die($errorMessage);
		}
	switch(DOCVERT_CLIENT_TYPE)
		{
		case 'web':
			$head = '<style type="text/css">body{font-family:sans-serif;} h1{font-size:large;} h2{font-size:medium} h3{font-size:small} .windowTitle{color:white;margin:0px;padding:5px;font-size:small} .bad {background:#ffeeee; border:solid 2px red} .bad .windowTitle {background:red} .good {background:#eeffee;border: solid 2px #bbccbb} .good .windowTitle {background:#006600} .footer {margin-top:0px;padding:4px;font-size:small} .bad .footer {background:#ffcccc} .bad .footer .divider {color:#ffcccc} .good .footer {background:#ccffcc} .good .footer .divider {color:#ccffccc} .standardAdvice {margin:30px 0px 0px 0px; padding: 0px 0px 10px 15px;} </style>';
			$body = '<div class="'.$pageType.'">'."\n";
			$body .= '    <h1 class="windowTitle">Docvert: '.$title.' '.$errorNumber.'</h1>'."\n";
			$body .= '    <div style="padding:10px">'."\n";
			$body .= '	'.$message."\n";
			$body .= '    </div>'."\n";
			$body .= '    &error-footer;'."\n";
			$body .= '</div>'."\n";
			$template = getXHTMLTemplate();
			$template = str_replace('{{title}}', $title, $template);
			$template = str_replace('{{head}}', $head, $template);
			$template = str_replace('{{body}}', $body, $template);
			$template = preg_replace_callback('/\&(.*?)\;/s', 'replaceLanguagePlaceholder', $template);
			if($errorData)
				{
				foreach($errorData as $key => $value)
					{	
					$template = str_replace('&dynamic-'.$key.';', revealXml($value), $template);
					}	
				}
			die($template);
			break;
		case 'command line':
			$message = preg_replace_callback('/\&(.*?)\;/s', 'replaceLanguagePlaceholder', $message);
			if($errorData)
				{
				foreach($errorData as $key => $value)
					{	
					$message = str_replace('&dynamic-'.$key.';', revealXml($value), $message);
					}	
				}
			$endOfBlockElements = array('</p>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '</li>', '</blockquote>');
			$message = str_replace($endOfBlockElements, "\n", $message);
			$message = preg_replace('/<.*?>/s','',$message);
			$message = str_replace('&lt;','<', $message);
			$message = str_replace('&gt;','>', $message);
			$message = str_replace('&amp;','&', $message);
			$message .= "\n";
			$message = trim($message)."\n";

			if($pageType == 'bad')
				{
				file_put_contents("php://stderr", $message);
				}
			else
				{
				print $message;
				}
			die();
			break;
		}
	}

function getExpireSessionsAfterDays()
	{
	// just a function so that I can later make it a config variable elsewhere -- matthew@holloway.co.nz
	$expireAfterDays = 0.5;
	return $expireAfterDays;
	}

function getThemeFragmentByPath($path, $themeDirectory)
	{
	$themePath = $themeDirectory.$path;
	if(!file_exists($themePath)) //instead use default directory
		{
		$themePath = dirname(__file__).DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'docvert'.DIRECTORY_SEPARATOR.$path;
		}
	$themeFragment = file_get_contents($themePath);
	return preg_replace_callback('/\&(.*?)\;/s', 'replaceLanguagePlaceholder', $themeFragment);
	}

?>
