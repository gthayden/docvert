<?php

if(substr(PHP_VERSION, 0, 1) == "4")
	{
	die('<html><body><table border="0" cellpadding="10"><tr><td><img src="core/lib/huffle.bin" alt=""/></td><td><h1>Install error</h1><p>Your version of PHP is '.PHP_VERSION.' but Docvert needs PHP 5 or later.</p><p>See <a href="doc/install.txt">install.txt</a> for the installation requirements.</p><p>And have a nice day</p></td></tr></table></body></html>');
	}

?>
