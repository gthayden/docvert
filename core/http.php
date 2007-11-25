<?php

function copyViaWebDAVRecursively($uploadLocation, $previewPath, $remoteDirectory)
	{
	$sourcePath = $previewPath;
	
	if(substr($uploadLocation['baseDirectory'], strlen($uploadLocation['baseDirectory']) - 1, 1) != "/")
		{
		$uploadLocation['baseDirectory'] .= '/';
		}
	$destinationPath = $uploadLocation['baseDirectory'].$remoteDirectory;

	$host = $uploadLocation['host'];

	switch($uploadLocation['protocol'])
		{
		case 'webdav-ssl':
			$host = 'ssl://'.$host;
			break;
		case 'webdav-tls':
			$host = 'tls://'.$host;
			break;
		}
	$port = $uploadLocation['port'];
	$username = $uploadLocation['username'];
	$password = $uploadLocation['password'];
	$proxyUsername = '';
	$proxyPassword = '';

	$errorMessage = '';

	$httpCode = pullpage('MKCOL', $host, $port, $uploadLocation['baseDirectory'], $username, $password, $proxyUsername, $proxyPassword);
	switch($httpCode)
		{
		case '201':
		case '405':
		case '301':
			break;
		default:
			$errorMessage .= "&error-error-label; ".$httpCode.": &error-problem-creating-directory-at; ".$uploadLocation['baseDirectory'];
		}

	$errorMessage .= copyFileViaWebDav($sourcePath, $destinationPath, $host, $port, $username, $password, $proxyUsername, $proxyPassword);


	$errorHtml = '';
	if($errorMessage)
		{
		$errorHtml = nl2br($errorMessage);
		}
	return $errorMessage;
	
	}

function copyFileViaWebDav($sourcePath, $destinationPath, $host, $port, $username, $password, $proxyUsername, $proxyPassword)
	{
	$errorMessage = '';
	$sourcePath = str_replace(" ", "-", $sourcePath);
	$destinationPath = str_replace(" ", "-", $destinationPath);
	
	$httpCode = pullpage('MKCOL', $host, $port, $destinationPath, $username, $password, $proxyUsername, $proxyPassword);
	switch($httpCode)
		{
		case '201':
		case '405':
		case '301':
			break;
		default:
			$errorMessage .= "&error-error-label; ".$httpCode.": &error-problem-creating-directory-at; ".$destinationPath;
		}
		
	
	if(is_dir($sourcePath))
		{
		$handle=opendir($sourcePath);
		while(($file = readdir($handle))!==false)
			{
			if(($file != ".") && ($file != ".."))
				{
				$currentSourcePath = $sourcePath.DIRECTORY_SEPARATOR.$file;
				$currentDestinationPath = $destinationPath.'/'.$file;
				//print $currentSourcePath.' ';

				if(is_dir($currentSourcePath))
					{
					//print "Directory<br />";
					$errorMessage .= copyFileViaWebDav($currentSourcePath, $currentDestinationPath, $host, $port, $username, $password, $proxyUsername, $proxyPassword);
					chdir($sourcePath);
					}
				else
					{
					//print "File<br />";
					if(substr($file, strlen($file) - 4, 4) != ".zip")
						{
						$binaryArray = array(file_get_contents($currentSourcePath));
						$httpCode = pullpage('PUT', $host, $port, $currentDestinationPath, $username, $password, $proxyUsername, $proxyPassword, null, $binaryArray);
						switch($httpCode)
							{
							case '201':
							case '204':
								break;
							default:
								$errorMessage .= "Error #".$httpCode.": &error-problem-creating-file-at; ".$currentDestinationPath;
							}
						}
					}
				}
			}
		closedir($handle);
		}
	return $errorMessage;
	}


function isHttpCodeError($httpCode)
	{
	return '';
	}

function pullpage($method, $host, $port, $usepath, $username, $password, $proxyUsername, $proxyPassword, $textArray = null, $binaryArray = null, $debug = false, $timeOutInSeconds = null)
	{
	//Used as pullpage ("GET" || "POST", "localhost", "80", "/blah.php", $textArray['key'] = $value, $binaryArray['key'] = $value['type' || 'binary']);

	//TODO: use parse_url rather than asking for url components
	$output = null;
	if($binaryArray && $method == 'GET') die('Can\'t method=GET with binaries.');
	$userAgent = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:bignumber) Docvert";
	if($timeOutInSeconds == null)
		{
		$timeOutInSeconds = 30;
		}
	$protocol = 'http';
	if($port == 443)
		{
		$protocol = 'https';
		}
	$fp = fsockopen($host, $port, $errno, $errstr, $timeOutInSeconds);
	$response = null;
	if(!$fp)
		{
		return false;
		//webServiceError($errstr.' ('.$errno.') at host "'.$host.'", port "'.$port.'", <br>'."\n", 502);
		}
	stream_set_timeout($fp, $timeOutInSeconds);

	$request = null;
	$request['head'] = null;
	$request['body'] = null;
	switch($method)
		{
		case 'GET':
		case 'POST':
		case 'PULL':
		case 'MKCOL':
		case 'PUT':
			$request['head'] .= httpLine($method.' '.$usepath.' HTTP/1.1');
			break;
		case 'HEAD':
			$request['head'] .= httpLine($method.' '.$protocol.'://'.$host.$usepath.' HTTP/1.1');
			break;
		default:
			webServiceError('&error-http-unknown-method;', 500, ('method'=>$method));
		}
	$request['head'] .= httpLine('User-Agent: '.$userAgent);
	$request['head'] .= httpLine('Accept: */*');
	$request['head'] .= httpLine('Host: '.$host); // does this need ':'.$port
	$request['head'] .= httpLine('Keep-Alive: 300');
	$request['head'] .= httpLine('Connection: keep-alive');
	if($username.$password)
		{
		$request['head'] .= httpLine('Authorization: Basic '.base64_encode($user.':'.$pass));
		}
	if($proxyUsername.$proxyPassword)
		{
		$request['head'] .= httpLine('Proxy-Authorization: Basic '.base64_encode($proxyUsername.':'.$proxyPassword));
		}

	if($binaryArray && $method == "POST")
		{
		srand((double)microtime()*1000000);
		$boundary = "---------------------------".substr(md5(rand(0,32000)),0,10);
		$request['body'] .= '--'.$boundary;
		$request['head'] .= httpLine('Content-type: multipart/form-data; boundary='.$boundary);
		$arrayKeys = array_keys($binaryArray);
		$formItemCount = 0;
		foreach($binaryArray as $key => $value)
			{
			$formItemCount++;
			$request['body'] .= "\r\n";
			$request['body'] .= httpLine('Content-Disposition: form-data; name="'.$key.'"; filename="'.$value['name'].'"');
			$request['body'] .= httpLine('Content-Type: '.$value['type']);
			$request['body'] .= httpLine('Content-Transfer-Encoding: binary');

			$request['body'] .= "\r\n";
			$request['body'] .= $value['binary'];
			$request['body'] .= "\r\n--".$boundary;
			}
		$arrayKeys = array_keys($textArray);
		foreach($textArray as $key => $value)
			{
			$request['body'] .= "\r\n";
			$request['body'] .= httpLine('Content-Disposition: form-data; name="'.$key.'"');
			$request['body'] .= "\r\n";
			$request['body'] .= $value;
			$request['body'] .= "\r\n--".$boundary;
			}

		$request['body'] .= "\r\n\r\n";
		}
	if($binaryArray && $method == "PUT")
		{
		if(count($binaryArray) != 1)
			{
			webServiceError("&error-http-put-multiple-files", 500, Array('numberOfFiles'=>count($binaryArray));
			}
		$request['head'] .= httpLine('Content-type: application/octet-stream');
		$request['body'] = $binaryArray[0];
		}
	elseif($textArray)// which is basically $textArray && !$binaryArray
		{
		$request['head'] .= httpLine('Content-type: application/x-www-form-urlencoded');
		$arrayKeys = array_keys($textArray);
		foreach($textArray as $key => $value)
			{
			if($request['body'] != null) $request['body'] .= "&";
			$request['body'] .= $key.'='.urlencode($value);
			}
		}	

	$request['head'] .= httpLine('Content-length: '.strlen($request['body']));	
	$request['head'] .= "\r\n";
	$request['body'] .= "\r\n";


	fputs($fp, $request['head'].$request['body']);

	$divisionString = "\r\n\r\n";

	switch($method)
		{
		case 'MKCOL':
		case 'PUT':
			$exitAfterLoops = 0;
			while(stripos($output, "\n") === False)
				{
				$output .= fgets($fp, 8);
				$exitAfterLoops++;
				if($exitAfterLoops > 30)
					{
					break;
					}
				}
			fclose($fp);
			$output = trim($output);
			$outputParts = explode(' ', $output);
			$httpCode = $outputParts[1];
			$httpMessage = '';
			for($i = 2; $i < count($outputParts); $i++)
				{
				$httpMessage .= trim(' '.$outputParts[$i]);
				}
			return $httpCode;
			break;
			//print $method.' = '.$httpCode.":".$httpMessage."<br />";
		case 'HEAD':
			while(stripos($output, $divisionString) === False && !feof($fp))
				{
				$output .= fgets($fp, 8);
				}
			fclose($fp);
			return $output;
			break;
		default:
			if($debug) print "Started feof at ".date(DATE_RFC822).'<br />';
			while(!feof($fp))
				{
				$info = stream_get_meta_data($fp);
				if($info['timed_out'])
					{
					break;
					}
				$output .= fgets($fp, 4096);
				}
			if($debug) print "Ended feof at ".date(DATE_RFC822).'<br />';
		}
	fclose($fp);
	if($output)
		{
		$divisionPosition = strpos($output,$divisionString);
		if(!$divisionPosition) webServiceError("&error-http-segment;", 500, Array('output'=>revealXml($output)));
		$httpHeader = substr($output,0,$divisionPosition);
		$httpHeader = explode("\n",$httpHeader);
		$httpBody = substr($output,$divisionPosition + strlen($divisionString));
		$response['head'] = $httpHeader;
		$response['body'] = $httpBody;
		}

	if($debug)
		{
		print '<h1>Debug</h1>';
		print '<p>Connection is '.$method.' to '.$host.':'.$port.$usepath.'. Scroll down to see request and response.</p>';
		print '<h2>Request</h2>';
		print '<div style="margin:0px 3%">';
		print nl2br(textEncodingToHtmlEncoding($request['head'].$request['body']));
		print "</div>";
		print '<h2>Response</h2>';
		print '<h3>Head</h3>';
		print '<div style="margin:0px 3%">';
		foreach($response['head'] as $eachHeader) print $eachHeader."<br />";
		print "</div>";
		print "<h3>Body</h3>";
		print '<div style="margin:0px 3%">';
		print nl2br(textEncodingToHtmlEncoding($response['body']));
		print "</div>";
		die();
		}
	return $response;
	}

function httpLine($line)
	{
	return $line."\r\n";
	}

function textEncodingToHtmlEncoding($text)
	{
	return str_replace('>','&gt;',str_replace('<','&lt;',str_replace('&','&amp;',$text)));
	}

function followUrlRedirects($url, $maximumNumberOfOfRedirects=false)
	{
	if($maximumNumberOfOfRedirects === false)
		{
		$maximumNumberOfOfRedirects = 10;
		}
	$finalRedirectionUrl = false;
	$redirectPath = Array();
	$numberOfRedirectsRemaining = $maximumNumberOfOfRedirects;
	$timeOutInSeconds = 5;
	while($finalRedirectionUrl == false)
		{
		$previousUrl = $url;
		$wasRedirectedThisTime = false;
		$domainAndPort = getUrlDomainAndPortPart($url);
		$redirectPath[] = $url;
		$localPart = getUrlLocalPart($url);
						
		$result = pullpage('HEAD', $domainAndPort[0], $domainAndPort[1], $localPart, false, false, false, false, null, null, false, $timeOutInSeconds);
		if($result === false)
			{
			return null;
			}

		$result = explode("\n", $result);
		foreach($result as $line)
			{
			$headParts = explode(':', $line);
			if(count($headParts) > 1)
				{
				$subject = array_shift($headParts);
				$value = implode(':', $headParts);
				if(strtolower(trim($subject)) == "location")
					{
					$url = trim($value);
					if(!stringStartsWith($url, 'http://') && !stringStartsWith($url, 'https://'))
						{
						$url = 'http://'.$domainAndPort[0].$url;
						}
					//print "Was redirected to ".$url."<br />";
					if($previousUrl != $url)
						{
						$wasRedirectedThisTime = true;
						}
					}
				}
			}
		if($wasRedirectedThisTime == false)
			{
			$finalRedirectionUrl = true;
			}
		if($numberOfRedirectsRemaining <= 0)
			{
			webServiceError("&error-maximum-number-of-redirects-followed;", 500, Array('redirectPaths'=>implode("</li><li>", $redirectPath)));
			}
		$numberOfRedirectsRemaining--;
		}
	return $url;
	}

?>
