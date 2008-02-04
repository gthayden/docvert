<?php
/*
	Take from

	http://code.google.com/apis/blogger/developers_guide_protocol.html#create_public_post

	...and example of draft blog post (remove the app:control branch to make it post a non-draft),
	
	<entry xmlns='http://www.w3.org/2005/Atom'>
		<title type='text'>Marriage!</title>
		<app:control xmlns:app="http://purl.org/atom/app#">
			<app:draft>yes</app:draft>
		</app:control>
		<content type='xhtml'>
			<div xmlns="http://www.w3.org/1999/xhtml">
				<p>Mr. Darcy has <em>proposed marriage</em> to me!</p>
				<p>He is the last man on earth I would ever desire to marry.</p>
				<p>Whatever shall I do?</p>
			</div>
		</content>
		<author>
			<name>Elizabeth Bennet</name>
	    		<email>liz@gmail.com</email>
		</author>
	</entry>
*/

include_once('http.php');

function copyViaBloggerAPI($uploadLocation, $previewDirectory, $remoteDirectory)
	{
	$host = $uploadLocation['host'];

	//if($uploadLocation['protocol'] == 'bloggerapi-ssl')
	//	{
	//	$host = 'ssl://'.$host;
	//	}

	$port = $uploadLocation['port'];
	$username = $uploadLocation['username'];
	$password = $uploadLocation['password'];


	$title = 'my test title'; //TODO: Extract title from <head><title> HERE
	$body = 'my test body';
	$authorName = '';
	$authorEmail = '';
	$draft = true;
	$proxyUsername = null;
	$proxyPassword = null;
	
	$httpCode = postToBloggerApiBlog($host, $port, $uploadLocation['baseDirectory'], $username, $password, $title, $body, $authorName, $authorEmail, $draft);

	$errorMessage = '';
	if($httpCode)
		{
		$errorMessage = nl2br($httpCode);
		}
	return $errorMessage;
	}

function postToBloggerApiBlog($host, $port, $xmlRpcPath, $username, $password, $title, $body, $authorName, $authorEmail, $draft, $proxyUsername=null, $proxyPassword=null)
	{
	$postData = "<entry xmlns='http://www.w3.org/2005/Atom'>\n";
	$postData .= "\t<title type='text'>".revealXml($title)."</title>\n";
	if($draft)
		{
		$postData .= "\t<app:control xmlns:app='http://purl.org/atom/app#'>\n";
		$postData .= "\t\t<app:draft>yes</app:draft>\n";
		$postData .= "\t</app:control>\n";
		}
	$postData .= "\t<content type='xhtml'>\n";
	$postData .= $body; //must be valid XML
	$postData .= "\t</content>\n";
	$postData .= "\t<author>\n";
	$postData .= "\t\t<name>".revealXml($authorName)."</name>\n";
	$postData .= "\t\t<email>".revealXml($authorEmail)."</email>\n";
	$postData .= "\t</author>\n";
	$postData .= "</entry>";

	$textArray['xmlrpc'] = $postData; //TODO: how to do submit this to the xmlrpc interface?

	return pullpage('POST', $host, $port, $xmlRpcPath, $username, $password, $proxyUsername, $proxyPassword, $textArray = null, $binaryArray = null, $debug = false, $timeOutInSeconds = null);

	}

?>
