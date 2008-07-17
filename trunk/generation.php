<?php
$appDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
define('DOCVERT_DIR', $appDir);
define('DOCVERT_CLIENT_TYPE', 'web');
include_once(DOCVERT_DIR.'core/webpage.php');
$themes = new Themes;
$themes->drawTheme();
?>
